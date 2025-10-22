# Contributing

We expect every collaborator to follow the workflow below so changes land cleanly and safely. When in doubt, ask a maintainer before pushing large or breaking updates.

## Branching & workflow

- **Main is protected.** Always branch from an up-to-date `main` (`git fetch origin && git checkout main && git pull --ff-only`).
- **Name branches descriptively.** Use `feature/<slug>` for enhancements, `fix/<slug>` for bug fixes, and `chore/<slug>` for maintenance tasks.
- **Keep history linear.** Rebase your branch before opening a PR (`git pull --rebase origin main`) and avoid merge commits unless requested by a maintainer.
- **Sync early and often.** Push work-in-progress commits to the remote branch so reviewers can follow along and CI can surface regressions quickly.

## Commit guidelines

- Write imperative subject lines (`Add product feed import`), keep them under 72 characters, and include a blank line before the body.
- Use the body to explain the "why", reference tickets, and list any skipped checks or follow-up tasks.
- Group related changes into focused commits; do not mix refactors with feature work in the same commit.
- If your change alters behaviour, mention the validation you performed (tests, manual checks) in the commit body.

## Pull requests

- Use the template in `.github/PULL_REQUEST_TEMPLATE.md` and fill in every section before requesting review.
- Provide screenshots or terminal output for UI or DX-affecting changes whenever possible.
- Re-request review after addressing feedback; resolve conversations only when the underlying issue is fixed.
- Link to relevant docs, tickets, or architectural decisions in the PR description so reviewers have context.

## Local environment

- Run `make setup` after cloning to install Composer/NPM dependencies, generate `.env`, and prepare the SQLite database.
- `make dev` launches PHP, the queue listener, Pail logs, and Vite in one terminal. Use `QUEUE_CONNECTION=sync` if Redis is unavailable locally.
- Horizon is accessible at `http://127.0.0.1:8000/horizon` when the queue worker is running.

## Quality workflow

Run the following commands before committing and again before requesting review:

1. `make format` (wraps Pint) or `composer fix:php` for verbose output.
2. `make analyse` or `composer analyze` to run PHPStan with the repo configuration.
3. `composer rector` on targeted paths when performing framework upgrades.
4. When editing Blade templates, execute `php artisan view:clear && php artisan view:cache`.

Document any intentional deviations (e.g., skipped static analysis) in the commit body and PR checklist.

## Testing expectations

- Run `composer test` to execute the default PHPUnit suite (artifacts live in `storage/app/coverage`).
- Continuous integration uses `composer test:ci` to emit JUnit and Clover reports in `build/`; run it locally when you need those artefacts or when debugging CI-only issues.
- For scoped runs, prefer `php artisan test --filter=<ClassName>` or target directories (see `tests/README.md` for layout).
- Browser/Dusk tests require ChromeDriver (`composer dusk:chrome`) and a running `php artisan serve` instance.
- Include new or updated tests alongside behaviour changes, and ensure seed data remains multilingual (Lithuanian default with English equivalents).

## Documentation & knowledge base

- Update `docs/CHANGELOG.md` with user-visible features, migrations, or dependency bumps.
- For new architectural areas, add a short pointer to `docs/INDEX.md` to keep the navigation fresh.
- Align cache additions with the [Cache Policy](docs/runbooks/CachePolicy.md) and mention notable cache keys in PR descriptions when relevant.
- Record any deferred follow-up work in the memory bank utilities under `memory-bank/` so the team can prioritise them later.
