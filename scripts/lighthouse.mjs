import { mkdir, readFile, writeFile } from 'node:fs/promises';
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

const loadBudgets = async () => {
  const budgetsPath = process.env.LH_BUDGETS_PATH || path.join(process.cwd(), 'scripts/lighthouse-budgets.json');

  try {
    const raw = await readFile(budgetsPath, 'utf8');
    const parsed = JSON.parse(raw);

    if (Array.isArray(parsed)) {
      return parsed;
    }

    console.warn(`[lighthouse] Expected budgets file to contain an array, received ${typeof parsed}. Ignoring budgets.`);
  } catch (error) {
    const isObject = typeof error === 'object' && error !== null;
    const code = isObject && 'code' in error ? error.code : undefined;

    if (code === 'ENOENT') {
      console.warn(`[lighthouse] Budgets file not found at ${budgetsPath}; skipping budget enforcement.`);
    } else {
      console.warn('[lighthouse] Failed to read Lighthouse budgets file:', error instanceof Error ? error.message : error);
    }
  }

  return [];
};

const matchesBudgetPath = (pattern = '/*', targetPath = '/') => {
  const normalizedPattern = typeof pattern === 'string' && pattern.length > 0 ? pattern : '/*';
  const normalizedTarget = typeof targetPath === 'string' && targetPath.length > 0 ? targetPath : '/';

  if (normalizedPattern === normalizedTarget) {
    return true;
  }

  if (normalizedPattern.endsWith('*')) {
    const prefix = normalizedPattern.slice(0, -1);
    return normalizedTarget.startsWith(prefix);
  }

  return false;
};

const collectBudgetsForPath = (budgets, targetPath) =>
  budgets.filter((budget) => matchesBudgetPath(budget?.path, targetPath));

const formatMilliseconds = (value) => `${Math.round(value)}ms`;
const formatKilobytes = (value) => `${(value / 1024).toFixed(0)}kb`;

const getScriptTransferBytes = (lhr) => {
  const requests = lhr?.audits?.['network-requests']?.details?.items;
  if (!Array.isArray(requests)) {
    return 0;
  }

  return requests
    .filter((request) => (request.resourceType || '').toLowerCase() === 'script')
    .reduce((total, request) => total + (Number(request.transferSize) || 0), 0);
};

const outputDir = process.env.LH_OUTPUT_DIR || 'storage/lighthouse';

const ensureDirectory = async (dir) => {
  await mkdir(dir, { recursive: true });
};

const formatScore = (score) => `${Math.round((score ?? 0) * 100)}`.padStart(3, ' ');

const sanitizeLabel = (label) => label.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'audit';

(async () => {
  await ensureDirectory(outputDir);

  const budgets = await loadBudgets();

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
            ...(budgets.length > 0 ? { budgets } : {}),
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

      const matchedBudgets = collectBudgetsForPath(budgets, target.path);

      for (const budget of matchedBudgets) {
        for (const timing of budget.timings || []) {
          const metric = timing?.metric;
          const budgetValue = Number(timing?.budget);

          if (!metric || !Number.isFinite(budgetValue)) {
            continue;
          }

          let actualValue = null;
          if (metric === 'largest-contentful-paint') {
            actualValue = lhr?.audits?.['largest-contentful-paint']?.numericValue ?? null;
          } else if (metric === 'cumulative-layout-shift') {
            actualValue = lhr?.audits?.['cumulative-layout-shift']?.numericValue ?? null;
          }

          if (actualValue === null || actualValue === undefined) {
            console.warn(`[lighthouse] Unable to determine value for timing metric ${metric}; skipping.`);
            continue;
          }

          const isWithinBudget = actualValue <= budgetValue;
          const actualFormatted = metric === 'cumulative-layout-shift' ? actualValue.toFixed(3) : formatMilliseconds(actualValue);
          const budgetFormatted = metric === 'cumulative-layout-shift' ? budgetValue.toFixed(3) : formatMilliseconds(budgetValue);
          const label = metric.replace(/-/g, ' ').toUpperCase();

          if (isWithinBudget) {
            console.log(`[lighthouse] ${target.label} ${label} ${actualFormatted} (budget ${budgetFormatted}) ✅`);
          } else {
            console.error(`[lighthouse] ${target.label} ${label} ${actualFormatted} (budget ${budgetFormatted}) ❌`);
            failures += 1;
          }
        }

        for (const resourceBudget of budget.resourceSizes || []) {
          const type = (resourceBudget?.resourceType || '').toLowerCase();
          const budgetKb = Number(resourceBudget?.budget);

          if (!type || !Number.isFinite(budgetKb)) {
            continue;
          }

          if (type !== 'script') {
            continue;
          }

          const actualBytes = getScriptTransferBytes(lhr);
          const budgetBytes = budgetKb * 1024;

          if (actualBytes <= 0) {
            console.warn('[lighthouse] No script requests were detected while evaluating JS budget.');
          }

          const actualFormatted = formatKilobytes(actualBytes);
          const budgetFormatted = `${budgetKb}kb`;

          if (actualBytes <= budgetBytes) {
            console.log(`[lighthouse] ${target.label} JS bundle ${actualFormatted} (budget ${budgetFormatted}) ✅`);
          } else {
            console.error(`[lighthouse] ${target.label} JS bundle ${actualFormatted} (budget ${budgetFormatted}) ❌`);
            failures += 1;
          }
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
