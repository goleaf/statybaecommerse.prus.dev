# Documentation Style Guide

Follow these conventions when creating or updating content inside `/docs` to keep the knowledge base predictable and easy to scan.

## Headings
- Start every document with a level-one (`#`) title that matches the file name in sentence case.
- Use sentence case for subsequent headings and increase depth gradually (`##`, `###`) without skipping levels.
- Avoid heading levels deeper than `####` unless the content is a reference specification.

## Body Content
- Favor short paragraphs and use bulleted or numbered lists for procedures or decision trees.
- Call out warnings or prerequisites with bold labels (for example, `**Warning:** restart the queue worker first`).
- Inline code, environment variables, and filenames should use backticks (`` `php artisan` ``).

## Code Blocks
- Use fenced code blocks with language hints (e.g., <code>```bash</code> or <code>```php</code>) so renderers apply syntax highlighting.
- Keep command snippets runnable as-is; include prompts (`$`) only when demonstrating interactive sessions.
- For multiline configuration examples, add contextual comments to explain why each option matters.

## File Naming
- Use uppercase snake case for narrative reports (e.g., `SYSTEM_STATUS_REPORT.md`).
- Use kebab case for procedural guides and specs (e.g., `deploy-rollback-guide.md`).
- When adding assets (CSV, XML, JSON), store them beside the guide that references them and note their purpose in the parent document.

## Update Policy
- Update affected runbooks whenever workflows, dependencies, or CLI flags change; include a changelog entry at the bottom when edits are operationally significant.
- Review analysis documents quarterly—add a brief `## Status` section noting whether the content is current, superseded, or archived.
- Keep contracts immutable after client delivery; append addenda in a new file rather than rewriting history.

## Index Maintenance
- After creating or moving any document, update [`INDEX.md`](INDEX.md) to include it in the appropriate section.
- If you add a new subdirectory, include a short description in the Directory Overview so contributors understand where content belongs.
