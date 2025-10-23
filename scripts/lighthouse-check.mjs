import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { setTimeout as delay } from 'node:timers/promises';
import process from 'node:process';
import { chromium } from 'playwright';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const lighthouseCli = require.resolve('lighthouse/lighthouse-cli/bin/index.js');

const host = process.env.LH_HOST || '127.0.0.1';
const port = Number(process.env.LH_PORT || '8000');
const baseUrl = process.env.LH_BASE_URL || `http://${host}:${port}`;
const thresholds = {
  performance: Number(process.env.LH_PERF_THRESHOLD || '0.9'),
  accessibility: Number(process.env.LH_A11Y_THRESHOLD || '0.9'),
};

const pages = [
  { path: '/', name: 'home' },
  { path: '/categories', name: 'catalog' },
];

const waitForServer = async (url, timeoutMs = 45000) => {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) {
    try {
      const resp = await fetch(url, { method: 'GET' });
      if (resp.ok) return true;
    } catch (error) {
      // ignore until ready
    }
    await delay(500);
  }
  throw new Error(`Timed out waiting for server at ${url}`);
};

const runLighthouse = (chromePath, url) => {
  return new Promise((resolve, reject) => {
    let stdout = '';
    const args = [
      lighthouseCli,
      url,
      '--quiet',
      '--no-update-notifier',
      '--output=json',
      '--output-path=stdout',
      '--only-categories=performance,accessibility',
      `--chrome-path=${chromePath}`,
      '--preset=desktop',
      '--chrome-flags=--headless --no-sandbox --disable-dev-shm-usage',
    ];

    const runner = spawn(process.execPath, args, {
      stdio: ['ignore', 'pipe', 'inherit'],
      env: {
        ...process.env,
        NODE_ENV: 'production',
      },
    });

    runner.stdout.on('data', (chunk) => {
      stdout += chunk;
    });

    runner.on('error', (error) => reject(error));
    runner.on('close', (code) => {
      if (code !== 0) {
        return reject(new Error(`Lighthouse exited with status ${code} for ${url}`));
      }

      try {
        const report = JSON.parse(stdout);
        resolve(report);
      } catch (error) {
        reject(new Error(`Failed to parse Lighthouse JSON output for ${url}: ${error.message}`));
      }
    });
  });
};

const ensureThresholds = (name, report) => {
  const scores = report.categories;
  const accessibility = scores.accessibility?.score ?? 0;
  const performance = scores.performance?.score ?? 0;

  console.log(`\n[Lighthouse] ${name}`);
  console.log(`  Accessibility: ${(accessibility * 100).toFixed(2)}`);
  console.log(`  Performance: ${(performance * 100).toFixed(2)}`);

  const failures = [];
  if (performance < thresholds.performance) {
    failures.push(`performance ${performance.toFixed(2)} < ${thresholds.performance}`);
  }
  if (accessibility < thresholds.accessibility) {
    failures.push(`accessibility ${accessibility.toFixed(2)} < ${thresholds.accessibility}`);
  }

  return { failures };
};

const main = async () => {
  const chromePath = chromium.executablePath();
  if (!chromePath) {
    throw new Error('Unable to resolve Playwright chromium executable path.');
  }

  const artisan = spawn('php', ['artisan', 'serve', '--host', host, '--port', String(port)], {
    stdio: ['ignore', 'pipe', 'inherit'],
    env: {
      ...process.env,
      APP_ENV: process.env.APP_ENV || 'production',
      APP_DEBUG: 'false',
    },
  });

  try {
    artisan.stdout.on('data', (chunk) => {
      process.stdout.write(chunk);
    });

    await waitForServer(baseUrl);

    const failures = [];

    for (const page of pages) {
      const url = new URL(page.path, baseUrl).toString();
      const report = await runLighthouse(chromePath, url);
      const { failures: pageFailures } = ensureThresholds(page.name, report);
      if (pageFailures.length > 0) {
        failures.push(`${page.name}: ${pageFailures.join(', ')}`);
      }
    }

    if (failures.length > 0) {
      throw new Error(`Lighthouse thresholds not met:\n- ${failures.join('\n- ')}`);
    }

    console.log('\n[Lighthouse] All thresholds satisfied.');
  } finally {
    artisan.kill('SIGTERM');
    try {
      await once(artisan, 'close');
    } catch (error) {
      // ignore
    }
  }
};

main().catch((error) => {
  console.error(error.message || error);
  process.exitCode = 1;
});
