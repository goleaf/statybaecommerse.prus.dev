import { chromium } from 'playwright';

const baseUrl = process.env.TARGET_URL || process.env.APP_URL || 'http://localhost:8000';

const targets = [
  '/',
  '/register',
  '/lt/login',
  '/categories',
];

const full = (path) => baseUrl.replace(/\/$/, '') + path;

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  let failures = 0;

  for (const path of targets) {
    const url = full(path);
    const errors = [];
    page.removeAllListeners();

    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(`[console.error] ${msg.text()}`);
    });
    page.on('pageerror', (err) => errors.push(`[pageerror] ${err.message}`));
    page.on('requestfailed', (req) => errors.push(`[requestfailed] ${req.method()} ${req.url()} (${req.resourceType()})`));

    let status = 0;
    try {
      const resp = await page.goto(url, { waitUntil: 'load', timeout: 30000 });
      status = resp ? resp.status() : 0;
      await page.waitForTimeout(1000);

      // Basic check: ensure styles/scripts present or styles applied
      const linkCount = await page.evaluate(() => document.querySelectorAll('link[rel="stylesheet"]').length);
      const bgColor = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);

      // Locale-specific quick check on /lt/login
      if (path.startsWith('/lt/login')) {
        const hasLtLogin = await page.evaluate(() => document.body.innerText.toLowerCase().includes('prisijungti'));
        if (!hasLtLogin) errors.push('[assertion] Expected LT localized login text');
      }

      if (!bgColor || bgColor === 'rgba(0, 0, 0, 0)') {
        errors.push('[assertion] Body computed style did not return a backgroundColor');
      }
      if (linkCount === 0) {
        // Vite dev sometimes inlines via JS, so only warn if truly no stylesheets
        // Leave as a warning-level note
        console.warn(`[warn] No stylesheet <link> tags detected on ${url}`);
      }
    } catch (e) {
      errors.push(`[fatal] ${e?.message || e}`);
    }

    const ok = status && status < 400 && errors.length === 0;
    console.log(`[e2e] ${url} -> ${status || 'ERR'} errors=${errors.length}`);
    for (const e of errors) console.log('  ' + e);
    if (!ok) failures++;
  }

  await browser.close();

  console.log(`\n[e2e:smoke] total=${targets.length} failed=${failures}`);
  if (failures > 0) process.exitCode = 1;
})();
