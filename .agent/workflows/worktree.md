---
description: Manage Git worktrees integrated with Laravel Herd for isolated feature development
---

# /worktree - Laravel Herd Worktree Management

## Commands

### Create Worktree
`/worktree create <branch-name>`

### List Worktrees
`/worktree list`

### Cleanup Worktree
`/worktree cleanup <branch-name>`

---

## Create Worktree Steps

// turbo-all

1. Ensure .worktrees directory exists:
```bash
mkdir -p .worktrees
```

2. Add .worktrees/ to .gitignore if not present:
```bash
grep -q '.worktrees/' .gitignore || echo '.worktrees/' >> .gitignore
```

3. Create the Git worktree:
```bash
git worktree add .worktrees/<branch-name> -b <branch-name>
```

4. Link to Laravel Herd:
```bash
herd link .worktrees/<branch-name> --name <branch-name>
```

5. Copy environment file:
```bash
cp .env .worktrees/<branch-name>/.env
```

6. Update .env in worktree:
```bash
cd .worktrees/<branch-name>
sed -i '' 's|^APP_URL=.*|APP_URL=http://<branch-name>.test|' .env
sed -i '' 's|^SESSION_DOMAIN=.*|SESSION_DOMAIN=.<branch-name>.test|' .env
echo 'SANCTUM_STATEFUL_DOMAINS=<branch-name>.test' >> .env
```

7. Install PHP dependencies:
```bash
cd .worktrees/<branch-name> && composer install
```

8. Install Node dependencies:
```bash
cd .worktrees/<branch-name> && npm install
```

9. Report success:
- **Site URL**: `http://<branch-name>.test`
- **Path**: `.worktrees/<branch-name>`

---

## List Worktrees Steps

1. Show all worktrees:
```bash
git worktree list
```

2. Show Herd links:
```bash
herd links
```

---

## Cleanup Worktree Steps

// turbo-all

1. Navigate out of worktree directory if inside it:
```bash
cd "$(git rev-parse --show-toplevel)"
```

2. Kill any Vite processes for this worktree:
```bash
pkill -f ".worktrees/<branch-name>.*vite" || true
```

3. Unlink from Herd:
```bash
herd unlink <branch-name>
```

4. Remove the Git worktree:
```bash
git worktree remove .worktrees/<branch-name> --force
```

5. Prune stale worktree references:
```bash
git worktree prune
```

6. Report cleanup complete.

---

## Notes

- Replace `<branch-name>` with actual branch (e.g., `feature-auth`)
- Branch names should use kebab-case
- Each worktree has its own node_modules and vendor directories
- Database can be shared (SQLite) or isolated (use separate .sqlite files)
