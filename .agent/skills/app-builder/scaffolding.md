# Project Scaffolding

> Directory structure and core files for new projects.

---

## Nuxt 3 Full-Stack Structure (2025 Optimized)

```
project-name/
├── app.vue
├── pages/
│   ├── index.vue
│   ├── login.vue
│   └── dashboard/
│       └── index.vue
├── components/
│   └── ui/                         # Reusable UI components
├── composables/                    # Shared composables
│   └── useAuth.ts
├── stores/                         # Pinia stores
│   └── user.ts
├── server/                         # Server-only code
│   ├── api/
│   │   └── [resource].ts
│   └── utils/
│       └── db.ts                   # Prisma client
├── plugins/
├── prisma/
│   └── schema.prisma
├── public/
├── .env.example
├── nuxt.config.ts
└── package.json
```

---

## Structure Principles

| Principle | Implementation |
|-----------|----------------|
| **Feature isolation** | Group by domain across `components/`, `composables/`, `stores/` |
| **Server/Client separation** | Server-only code in `server/`, prevents accidental client imports |
| **Thin routes** | `pages/` only for routing, logic lives in composables/stores |
| **Shared code** | `components/ui/` and `composables/` for reuse |

---

## Core Files

| File | Purpose |
|------|---------|
| `package.json` | Dependencies |
| `tsconfig.json` | TypeScript + path aliases (`@/features/*`) |
| `tailwind.config.ts` | Tailwind config |
| `.env.example` | Environment template |
| `README.md` | Project documentation |
| `.gitignore` | Git ignore rules |
| `prisma/schema.prisma` | Database schema |

---

## Path Aliases (tsconfig.json)

```json
{
  "compilerOptions": {
    "paths": {
      "@/*": ["./src/*"],
      "@/features/*": ["./src/features/*"],
      "@/shared/*": ["./src/shared/*"],
      "@/server/*": ["./src/server/*"]
    }
  }
}
```

---

## When to Use What

| Need | Location |
|------|----------|
| New page/route | `app/(group)/page.tsx` |
| Feature component | `features/[name]/components/` |
| Server action | `features/[name]/actions.ts` |
| Data fetching | `features/[name]/queries.ts` |
| Reusable button/input | `shared/components/ui/` |
| Database query | `server/db/` |
| External API call | `server/services/` |
