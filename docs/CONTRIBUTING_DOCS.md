# Documentation Style Guide

Follow these conventions when creating or updating content inside `/docs` to keep the knowledge base predictable and easy to scan.

## Headings
- Start every document with a level-one (`#`) title that matches the file name in sentence case.
- Use sentence case for subsequent headings and increase depth gradually (`##`, `###`) without skipping levels.
- Avoid heading levels deeper than `####` unless the content is a reference specification.

## Body content
- Favor short paragraphs and use bulleted or numbered lists for procedures or decision trees.
- Call out warnings or prerequisites with bold labels (for example, `**Warning:** restart the queue worker first`).
- Inline code, environment variables, and filenames should use backticks (`` `php artisan` ``).

## Code blocks
- Use fenced code blocks with language hints (for example, <code>```bash</code> or <code>```php</code>) so renderers apply syntax highlighting.
- Keep command snippets runnable as-is; include prompts (`$`) only when demonstrating interactive sessions.
- For multiline configuration examples, add contextual comments to explain why each option matters.

## File naming
- Use uppercase snake case for narrative reports (for example, `SYSTEM_STATUS_REPORT.md`).
- Use kebab case for procedural guides and specs (for example, `deploy-rollback-guide.md`).
- When adding assets (CSV, XML, JSON), store them beside the guide that references them and note their purpose in the parent document.

## Update cadence
- Update affected runbooks whenever workflows, dependencies, or CLI flags change; include a changelog entry at the bottom when edits are operationally significant.
- Review analysis documents quarterly—add a brief `## Status` section noting whether the content is current, superseded, or archived.
- Keep contracts immutable after client delivery; append addenda in a new file rather than rewriting history.

## Index maintenance
- After creating or moving any document, update [`INDEX.md`](INDEX.md) to include it in the appropriate section.
- If you add a new subdirectory, include a short description in the Directory Overview so contributors understand where content belongs.

## Automated checks
- Keep binary assets out of the repository; the CI guard rejects documentation files larger than 2MB. Compress screenshots or split lengthy appendices to stay within the limit.
- When updating large references, run `find docs -type f -size +2M` locally so CI passes on the first attempt.
