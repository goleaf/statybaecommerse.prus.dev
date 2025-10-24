import { expect, test } from '@playwright/test';
import type { Page } from '@playwright/test';

const HERO_HEADING_LT = 'Mėgstamiausios prekės vienoje įkvepiančioje erdvėje';
const HERO_PRIMARY_CTA = 'Peržiūrėti išskirtinius pasiūlymus';
const FEATURED_PRODUCT_NAME = 'Makita HR2475 smūginis perforatorius';

async function clearCart(page: Page, csrfToken: string) {
  await page.evaluate(async ({ csrfToken }) => {
    await fetch('/cart', {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'same-origin',
    });
  }, { csrfToken });
}

test.describe('Storefront happy path', () => {
  test('guest can browse catalogue and add a product to the cart', async ({ page, request, baseURL }) => {
    await page.goto('/');

    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    expect(csrfToken, 'CSRF token is required for cart API calls').toBeTruthy();

    await clearCart(page, csrfToken!);

    await expect(page.getByRole('heading', { level: 1, name: HERO_HEADING_LT })).toBeVisible();

    await page.getByRole('link', { name: HERO_PRIMARY_CTA }).click();
    await expect(page).toHaveURL(/\/products(\?.*)?$/);

    const productLink = page.getByRole('link', { name: FEATURED_PRODUCT_NAME }).first();
    await expect(productLink).toBeVisible();
    await productLink.click();

    await expect(page).toHaveURL(/\/products\//);

    const productHeading = page.getByRole('heading', { level: 1 });
    await expect(productHeading).toContainText(FEATURED_PRODUCT_NAME);
    const productTitle = (await productHeading.textContent())?.trim() ?? FEATURED_PRODUCT_NAME;

    const searchResponse = await request.get('/api/products/search', {
      params: { q: productTitle },
      headers: { Accept: 'application/json' },
    });
    expect(searchResponse.ok(), `Product search failed${baseURL ? ` for ${baseURL}` : ''}`).toBeTruthy();

    const payload = await searchResponse.json() as unknown;
    const items = Array.isArray(payload)
      ? payload
      : (payload && typeof payload === 'object' && 'data' in payload && payload.data && typeof payload.data === 'object' && 'items' in payload.data
        ? (payload.data as { items?: unknown }).items
        : []);

    const matches = Array.isArray(items) ? items as Array<{ id: number; name: string }> : [];
    const matchingProduct = matches.find((item) => item.name === productTitle)
      ?? matches.find((item) => item.name.includes(FEATURED_PRODUCT_NAME))
      ?? matches[0];

    expect(matchingProduct?.id, 'Unable to resolve product id for cart request').toBeTruthy();

    const addResult = await page.evaluate(async ({ productId, csrfToken }) => {
      const response = await fetch('/cart/items', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 }),
        credentials: 'same-origin',
      });

      const data = await response.json();
      return { ok: response.ok, data };
    }, { productId: matchingProduct!.id, csrfToken });

    expect(addResult.ok, 'Add to cart endpoint returned an error response').toBeTruthy();
    expect(addResult.data?.summary?.item_count ?? 0).toBeGreaterThan(0);

    await page.goto('/cart');
    await expect(page).toHaveURL(/\/cart$/);
    await expect(page.getByRole('heading', { name: 'Shopping cart' })).toBeVisible();
    await expect(page.locator('table')).toContainText(productTitle);
    await expect(page.locator('dl')).toContainText('Subtotal');
  });
});
