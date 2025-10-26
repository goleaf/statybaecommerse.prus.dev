import { expect, test } from '@playwright/test';
import type { APIRequestContext, Page } from '@playwright/test';

// Featured product used across storefront marketing sections.
const FEATURED_PRODUCT_NAME = 'Makita HR2475 smūginis perforatorius';

// Simple factory for building deterministic but unique user records.
function buildTestUser(seed: number = Date.now()) {
  const suffix = `pw-${seed}`;

  return {
    firstName: 'Playwright',
    lastName: `Tester-${suffix}`,
    email: `playwright+${suffix}@example.test`,
    password: `Pw!${suffix}`,
  };
}

// Factory to keep checkout address data consistent across the suite.
function buildCheckoutDetails(user: ReturnType<typeof buildTestUser>) {
  const fullName = `${user.firstName} ${user.lastName}`;

  return {
    fullName,
    email: user.email,
    phone: '+37060000000',
    addressLine1: '123 Test Street',
    addressLine2: 'Suite 42',
    city: 'Vilnius',
    postalCode: '01100',
    country: 'Lithuania',
    notes: 'Playwright automated checkout verification.',
  };
}

// Factory for creating review payloads with distinctive content per run.
function buildReviewDetails(seed: number = Date.now()) {
  const suffix = `#${seed}`;

  return {
    rating: 5,
    title: `End-to-end purchase feedback ${suffix}`,
    content: `Great tooling experience confirmed via Playwright flow ${suffix}.`,
  };
}

type ProductSummary = {
  id: number;
  name: string;
};

// Resolve a product from the public search API to drive deterministic cart actions.
async function resolveProduct(
  request: APIRequestContext,
  name: string,
): Promise<ProductSummary> {
  const response = await request.get('/api/products/search', {
    params: { q: name },
    headers: { Accept: 'application/json' },
  });

  expect(response.ok(), 'Product search request must succeed').toBeTruthy();

  const payload = (await response.json()) as unknown;
  const items = Array.isArray(payload)
    ? payload
    : (payload && typeof payload === 'object' && 'data' in payload && payload.data && typeof payload.data === 'object' && 'items' in (payload.data as Record<string, unknown>)
        ? ((payload.data as { items?: unknown }).items ?? [])
        : []);

  const candidates = Array.isArray(items)
    ? (items as Array<Record<string, unknown>>).map((item) => ({
        id: typeof item.id === 'number' ? item.id : Number.parseInt(String(item.id ?? 0), 10),
        name: typeof item.name === 'string' ? item.name : String(item.name ?? ''),
      }))
    : [];

  const match =
    candidates.find((item) => item.name === name) ??
    candidates.find((item) => item.name.includes(name)) ??
    candidates[0];

  expect(match?.id, 'Expected to resolve a product id for cart operations').toBeTruthy();

  return match as ProductSummary;
}

// Helper that guarantees the session cart starts empty before the flow begins.
async function clearCart(page: Page, csrfToken: string) {
  await page.evaluate(
    async ({ token }) => {
      await fetch('/cart', {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': token,
        },
        credentials: 'same-origin',
      });
    },
    { token: csrfToken },
  );
}

// Programmatically add a product to the cart to avoid brittle UI coupling.
async function addProductToCart(page: Page, csrfToken: string, productId: number) {
  const result = await page.evaluate(
    async ({ token, id }) => {
      const response = await fetch('/cart/items', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ product_id: id, quantity: 1 }),
      });

      const data = await response.json().catch(() => ({}));

      return { ok: response.ok, status: response.status, data };
    },
    { token: csrfToken, id: productId },
  );

  expect(result.ok, `Failed to add product ${productId} to cart (status ${result.status})`).toBeTruthy();
  expect(result.data?.summary?.item_count ?? 0).toBeGreaterThan(0);
}

test.describe('Checkout to review journey', () => {
  test('new customer can checkout and leave a product review', async ({ page, request }) => {
    const product = await resolveProduct(request, FEATURED_PRODUCT_NAME);

    const seed = Date.now();
    const user = buildTestUser(seed);
    const checkoutDetails = buildCheckoutDetails(user);
    const review = buildReviewDetails(seed);

    await page.goto('/register');
    await expect(page).toHaveURL(/\/register$/);

    await page.getByLabel('First name').fill(user.firstName);
    await page.getByLabel('Last name').fill(user.lastName);
    await page.getByLabel('Email address').fill(user.email);
    await page.getByLabel('Password', { exact: true }).fill(user.password);
    await page.getByLabel('Confirm password').fill(user.password);

    await page.getByRole('button', { name: 'Create account' }).click();
    await expect(page).toHaveURL(/\/account\/orders$/);

    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    expect(csrfToken, 'Authenticated sessions must expose a CSRF token').toBeTruthy();

    await clearCart(page, csrfToken!);
    await addProductToCart(page, csrfToken!, product.id);

    await page.goto('/checkout');
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();

    await page.getByLabel('Full name').fill(checkoutDetails.fullName);
    await page.getByLabel('Email').fill(checkoutDetails.email);
    await page.getByLabel('Phone').fill(checkoutDetails.phone);
    await page.getByLabel('Address line 1').fill(checkoutDetails.addressLine1);
    await page.getByLabel('Address line 2').fill(checkoutDetails.addressLine2);
    await page.getByLabel('City').fill(checkoutDetails.city);
    await page.getByLabel('Postal code').fill(checkoutDetails.postalCode);
    await page.getByLabel('Country').fill(checkoutDetails.country);
    await page.getByLabel('Order notes').fill(checkoutDetails.notes);

    await page.getByRole('button', { name: 'Place order' }).click();
    await expect(page).toHaveURL(/\/checkout\/success$/);
    await expect(page.getByRole('heading', { name: 'Thank you for your order!' })).toBeVisible();

    const orderNumberText = await page
      .locator('text=Your order number is')
      .first()
      .textContent();
    expect(orderNumberText, 'Order confirmation should show an order number').toBeTruthy();

    const orderNumber = orderNumberText?.match(/number is\s+([A-Z0-9]+)/i)?.[1] ?? null;
    expect(orderNumber, 'Unable to parse order number from confirmation page').toBeTruthy();

    await page.goto('/account/orders');
    await expect(page).toHaveURL(/\/account\/orders$/);
    await expect(page.getByRole('heading', { name: 'My orders' })).toBeVisible();
    await expect(page.locator(`text=${orderNumber}`)).toBeVisible();

    await page.getByRole('link', { name: 'View details' }).first().click();
    await expect(page).toHaveURL(new RegExp(`/account/orders/${orderNumber}`));
    await expect(page.getByRole('heading', { name: 'Details of your order' })).toBeVisible();
    await expect(page.locator(`text=${orderNumber}`)).toBeVisible();

    const reviewResult = await page.evaluate(
      async ({ token, productId, reviewPayload }) => {
        const response = await fetch(`/products/${productId}/review`, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            rating: reviewPayload.rating,
            title: reviewPayload.title,
            content: reviewPayload.content,
          }),
        });

        return { ok: response.ok, status: response.status };
      },
      { token: csrfToken, productId: product.id, reviewPayload: review },
    );

    expect(reviewResult.ok, `Review submission returned status ${reviewResult.status}`).toBeTruthy();

    await page.goto('/account/reviews');
    await expect(page).toHaveURL(/\/account\/reviews$/);
    await expect(page.getByRole('heading', { name: 'My reviews' })).toBeVisible();
    await expect(page.locator(`text=${review.title}`)).toBeVisible();
    await expect(page.locator(`text=${review.content}`)).toBeVisible();
  });
});
