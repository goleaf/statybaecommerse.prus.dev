---
name: herd-worktrees
description: Automate Laravel Herd Git worktree setup, environment configuration, and cleanup. Use when creating isolated feature branches with .test domains.
triggers:
  - worktree
  - herd worktree
  - feature branch
  - isolated development
  - parallel branches
---

# Laravel Herd Worktrees Skill

> **Purpose**: Automate Git worktree creation and Laravel Herd integration for isolated parallel development.

## Overview

Git worktrees allow checking out multiple branches simultaneously in separate directories. Combined with Laravel Herd, this enables:

- **Isolated environments** per feature branch
- **Parallel development** without stashing changes
- **Automatic `.test` domains** (e.g., `http://feature-branch.test`)
- **Proper environment configuration** per worktree

## Directory Structure

```
project/
├── .worktrees/
│   ├── feature-auth/
│   ├── feature-payments/
│   └── hotfix-login/
└── (main branch files)
```

## Core Operations

### 1. Create Worktree

**Command**: `/worktree create <branch-name>`

**Steps**:
1. Create Git worktree: `git worktree add .worktrees/<branch> -b <branch>`
2. Link to Herd: `herd link .worktrees/<branch> --name <branch>`
3. Copy and configure `.env`:
   - Set `APP_URL=http://<branch>.test`
   - Set `SESSION_DOMAIN=.<branch>.test`
   - Set `SANCTUM_STATEFUL_DOMAINS=<branch>.test`
4. Install dependencies: `composer install && npm install`
5. Start Vite (optional): `npm run dev -- --host <branch>.test`

### 2. List Worktrees

**Command**: `/worktree list`

```bash
git worktree list --porcelain
```

### 3. Cleanup Worktree

**Command**: `/worktree cleanup <branch-name>`

**Steps**:
1. Stop any running processes (Vite, queue workers)
2. Unlink from Herd: `herd unlink <branch>`
3. Remove worktree: `git worktree remove .worktrees/<branch>`
4. Prune stale references: `git worktree prune`

## Environment Configuration Template

When creating a worktree, generate `.env` with these values:

```bash
# Core Settings
APP_NAME="${APP_NAME}"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://<branch-name>.test

# Session & Auth
SESSION_DRIVER=database
SESSION_DOMAIN=.<branch-name>.test
SANCTUM_STATEFUL_DOMAINS=<branch-name>.test

# Database (shared or separate)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

## Herd Commands Reference

| Action | Command |
|--------|---------|
| Link site | `herd link <path> --name <name>` |
| Unlink site | `herd unlink <name>` |
| List sites | `herd links` |
| Secure with HTTPS | `herd secure <name>` |
| Unsecure | `herd unsecure <name>` |
| PHP version | `herd isolate <name> --php=8.3` |

## Best Practices

1. **Naming Convention**: Use kebab-case for branch names (`feature-user-auth`)
2. **Separate Databases**: For migrations, consider separate SQLite files per worktree
3. **Clean Up**: Always run cleanup command before deleting branches
4. **CORS Configuration**: Update Vite config to allow `.test` domain access
5. **Git Ignore**: Add `.worktrees/` to `.gitignore`

## Vite CORS Configuration

For cross-origin Vite requests, update `vite.config.js`:

```javascript
export default defineConfig({
    server: {
        cors: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
        },
    },
});
```

## Troubleshooting

### Session Cookie Issues
- Ensure `SESSION_DOMAIN` starts with a dot (`.feature.test`)
- Clear browser cookies when switching worktrees

### Herd Not Detecting Site
- Run `herd restart`
- Verify PHP version compatibility: `herd php <path> -v`

### Stale Vite Processes
```bash
pkill -f "vite"
```

## Related Skills

- `deployment-procedures` - Production deployment workflows
- `systematic-debugging` - Debug issues in worktree environments
