import { chromium } from 'playwright';

const baseUrl = process.env.TARGET_URL || 'https://statybaecommerse.prus.dev';

// Safe GET routes without required params based on routes/web.php
const locales = ['', 'en', 'lt', 'de'];
const localePaths = [
  '/',
  '/brands',
  '/locations',
  '/legal/terms', // may 404 if not present
  '/categories',
  '/collections',
  '/search',
  '/cart',
];

const globalPaths = ['/health', '/sitemap.xml'];

function buildTargets() {
  const targets = [];
  for (const loc of locales) {
    for (const path of localePaths) {
      const prefix = loc ? `/${loc}` : '';
      targets.push(`${baseUrl}${prefix}${path}`);
    }
  }
  for (const path of globalPaths) targets.push(`${baseUrl}${path}`);
  // De-duplicate
  return Array.from(new Set(targets));
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  const results = [];

  for (const url of buildTargets()) {
    const errors = [];
    page.removeAllListeners();

    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(`[console.error] ${msg.text()}`);
    });
    page.on('pageerror', (err) => errors.push(`[pageerror] ${err.message}`));
    page.on('requestfailed', (req) => {
      const f = req.failure();
      errors.push(`[requestfailed] ${req.method()} ${req.url()} (${req.resourceType()}) -> ${f?.errorText}`);
    });

    let status = 0;
    try {
      const resp = await page.goto(url, { waitUntil: 'load', timeout: 30000 });
      status = resp ? resp.status() : 0;
      // Give SPA/nav extras a moment
      await page.waitForTimeout(1500);
    } catch (e) {
      errors.push(`[fatal] ${e?.message || e}`);
    }

    results.push({ url, status, errors });
    const statusStr = status ? status : 'ERR';
    console.log(`[route] ${url} -> ${statusStr} errors=${errors.length}`);
    for (const e of errors) console.log(`  ${e}`);
  }

  await browser.close();

  const failed = results.filter(r => (r.status >= 400 || r.status === 0) || r.errors.length > 0);
  console.log(`\n[result] total=${results.length} failed=${failed.length}`);
  if (failed.length > 0) process.exitCode = 1;
})();
