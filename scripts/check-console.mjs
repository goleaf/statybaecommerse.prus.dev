import { chromium } from 'playwright';

const url = process.env.TARGET_URL || 'https://statybaecommerse.prus.dev/';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  const errors = [];

  page.on('console', (msg) => {
    const type = msg.type();
    if (type === 'error') {
      errors.push({ type: 'console', text: msg.text() });
      console.error(`[console.${type}] ${msg.text()}`);
    } else {
      // Log warnings too for visibility
      if (type === 'warning' || type === 'warn') {
        console.warn(`[console.${type}] ${msg.text()}`);
      }
    }
  });

  page.on('pageerror', (err) => {
    errors.push({ type: 'pageerror', text: err.message });
    console.error(`[pageerror] ${err.message}`);
  });

  page.on('requestfailed', (request) => {
    const failure = request.failure();
    const url = request.url();
    const method = request.method();
    const resourceType = request.resourceType();
    errors.push({ type: 'requestfailed', url, method, resourceType, errorText: failure?.errorText });
    console.error(`[requestfailed] ${method} ${url} (${resourceType}) -> ${failure?.errorText}`);
  });

  try {
    console.log(`[info] Visiting ${url}`);
    const response = await page.goto(url, { waitUntil: 'load', timeout: 30000 });
    if (!response) {
      console.error('[info] No response received');
    } else {
      console.log(`[info] Status: ${response.status()} ${response.statusText()}`);
    }

    // Wait for network to be idle-ish
    await page.waitForTimeout(3000);

    // Check if stylesheets are applied (rudimentary check on computed styles)
    const bgColor = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    console.log(`[info] Body backgroundColor: ${bgColor}`);

    // Dump all stylesheet links
    const styles = await page.evaluate(() => Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l => l.href));
    console.log(`[info] Stylesheets: ${JSON.stringify(styles, null, 2)}`);

    // Dump script srcs for visibility
    const scripts = await page.evaluate(() => Array.from(document.scripts).map(s => s.src).filter(Boolean));
    console.log(`[info] Scripts: ${JSON.stringify(scripts, null, 2)}`);

  } catch (e) {
    console.error(`[fatal] ${e?.message || e}`);
    errors.push({ type: 'fatal', text: e?.message || String(e) });
  } finally {
    await browser.close();
    const hasErrors = errors.length > 0;
    console.log(`[result] errors=${hasErrors ? 'yes' : 'no'} count=${errors.length}`);
    if (hasErrors) {
      process.exitCode = 1;
    }
  }
})();
