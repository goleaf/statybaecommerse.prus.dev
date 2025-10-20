# PHP Runtime Configuration

This project previously tracked `php.ini` and `.php-unlimited.ini` files in Git. To keep runtime configuration reproducible without committing server-level overrides, apply the directives below through your Docker or devcontainer setup.

## Standard profile

Use these directives for the default web and CLI processes:

| Directive | Value | Purpose |
| --- | --- | --- |
| `memory_limit` | `512M` | Prevent runaway memory usage while keeping enough headroom for artisan tasks. |
| `max_execution_time` | `300` | Allow long running imports/exports without hanging the terminal forever. |
| `max_input_time` | `300` | Match the execution limit for symmetry on large uploads. |
| `output_buffering` | `Off` | Stream output immediately to avoid terminal freezes. |
| `implicit_flush` | `On` | Forces PHP to send output as soon as it is produced, further reducing perceived freezes. |
| `pcntl.async_signals` | `On` | Ensures signal handlers run promptly so Ctrl+C reliably stops processes. |
| `display_errors` | `On` | Surface exceptions during development. |
| `log_errors` | `On` | Capture errors in addition to displaying them. |
| `error_log` | `/tmp/php_errors.log` | Location for collected logs inside containers. |
| `session.gc_maxlifetime` | `3600` | Keep admin sessions alive for an hour. |
| `upload_max_filesize` | `64M` | Support larger media uploads during catalog work. |
| `post_max_size` | `64M` | Align with the upload size limit. |
| `date.timezone` | `Europe/Vilnius` | Match the business's canonical timezone. |

## Unlimited memory profile

For rare maintenance scripts that need to bypass the memory cap, create a secondary INI fragment with:

| Directive | Value | Purpose |
| --- | --- | --- |
| `memory_limit` | `-1` | Allow PHP to use all available memory for critical one-off fixes. |
| `date.timezone` | `Europe/Vilnius` | Keep timestamps consistent with the rest of the stack. |

Mount this file only where required (for example, `docker-compose.override.yml` or `.devcontainer/devcontainer.json`). Avoid committing the file to Git.

## Local development notes

1. Copy the tables above into your container/VM provisioning (e.g. `Dockerfile`, `php.ini` bind mounts, or `ini` overrides passed to the PHP-FPM service).
2. If you need both profiles, mount the unlimited file under a distinct name and load it explicitly when invoking the script (`php -c /path/to/unlimited.ini artisan ...`).
3. Keep these values synchronized with CI images to guarantee consistent error handling and upload behaviour across environments.

With this approach the repository stays free of server-level INI files while the expected runtime behaviour remains documented and repeatable.
