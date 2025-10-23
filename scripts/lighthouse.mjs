import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

import { chromium } from 'playwright';
import lighthouse from 'lighthouse';

const baseUrl = (process.env.TARGET_URL || process.env.APP_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');

const routes = [
  { label: 'home', path: '/' },
  { label: 'catalog', path: '/categories' },
];

const toNumber = (value, fallback) => {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === 'string' && value.trim() !== '') {
    const parsed = Number.parseFloat(value);
    if (Number.isFinite(parsed)) {
      return parsed;
    }
  }

  return fallback;
};

const thresholds = {
  performance: toNumber(process.env.LH_MIN_PERFORMANCE, 0.8),
  accessibility: toNumber(process.env.LH_MIN_ACCESSIBILITY, 0.9),
};

const outputDir = process.env.LH_OUTPUT_DIR || 'storage/lighthouse';

const ensureDirectory = async (dir) => {
  await mkdir(dir, { recursive: true });
};

const formatScore = (score) => `${Math.round((score ?? 0) * 100)}`.padStart(3, ' ');

const sanitizeLabel = (label) => label.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'audit';

(async () => {
  await ensureDirectory(outputDir);

  const browser = await chromium.launch({
    headless: true,
    args: ['--remote-debugging-port=0', '--disable-dev-shm-usage', '--no-sandbox'],
  });

  const wsEndpoint = browser.wsEndpoint();
  if (!wsEndpoint) {
    console.error('[lighthouse] Unable to determine browser websocket endpoint');
    process.exitCode = 1;
    await browser.close();
    return;
  }

  const port = Number(new URL(wsEndpoint).port);
  if (!Number.isInteger(port) || port <= 0) {
    console.error(`[lighthouse] Invalid debugging port derived from endpoint: ${wsEndpoint}`);
    process.exitCode = 1;
    await browser.close();
    return;
  }

  let failures = 0;

  for (const target of routes) {
    const url = new URL(target.path, `${baseUrl}/`).toString();
    const slug = sanitizeLabel(target.label || target.path);

    console.log(`\n[lighthouse] Auditing ${target.label} -> ${url}`);

    try {
      const { lhr } = await lighthouse(
        url,
        {
          port,
          logLevel: 'error',
          output: 'json',
        },
        {
          extends: 'lighthouse:default',
          settings: {
            onlyCategories: ['performance', 'accessibility'],
            formFactor: 'desktop',
            screenEmulation: { disabled: true },
            disableStorageReset: true,
            throttling: {
              rttMs: 40,
              throughputKbps: 10240,
              cpuSlowdownMultiplier: 1,
            },
          },
        },
      );

      const performance = lhr?.categories?.performance?.score ?? 0;
      const accessibility = lhr?.categories?.accessibility?.score ?? 0;

      const reportPath = path.join(outputDir, `${slug}.json`);
      await writeFile(reportPath, JSON.stringify(lhr, null, 2), 'utf8');
      console.log(`[lighthouse] Saved report -> ${reportPath}`);

      const checks = [
        { key: 'performance', score: performance, min: thresholds.performance },
        { key: 'accessibility', score: accessibility, min: thresholds.accessibility },
      ];

      for (const check of checks) {
        const current = check.score ?? 0;
        const isPassing = current >= check.min;
        const currentPct = formatScore(current);
        const minPct = formatScore(check.min);

        const message = `[lighthouse] ${target.label} ${check.key} ${currentPct}% (min ${minPct}%)`;
        if (isPassing) {
          console.log(`${message} ✅`);
        } else {
          console.error(`${message} ❌`);
          failures += 1;
        }
      }
    } catch (error) {
      failures += 1;
      console.error(`[lighthouse] Failed to audit ${url}:`, error instanceof Error ? error.message : error);
    }
  }

  await browser.close();

  if (failures > 0) {
    console.error(`\n[lighthouse] Completed with ${failures} failing threshold check(s).`);
    process.exitCode = 1;
  } else {
    console.log('\n[lighthouse] All audits passed the configured thresholds.');
  }
})();
