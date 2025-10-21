# Husky Hook Maintenance Tips

To keep Git hooks reliable and future-proof:

- Retain the shim script in `./_/husky.sh` so Husky can bootstrap repository hooks without tripping the v10 deprecation guardrails.
- Avoid editing the script to print deprecation banners—doing so prevents the actual hook from running.
- When upgrading Husky, re-run `npx husky init` and compare the generated shim against our commented version to ensure parity.
- Ensure the script remains executable (`chmod 755`) so Git can invoke it on all platforms.

Following these steps prevents silent hook failures during commits, pushes, and release workflows.
