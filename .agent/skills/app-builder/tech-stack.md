# Tech Stack Selection (2025)

> Default and alternative technology choices for web applications.

## Default Stack (Web App - 2025)

```yaml
Frontend:
  framework: Nuxt 3 (Stable)
  language: TypeScript 5.7+
  styling: Tailwind CSS v4
  state: Pinia / Vue composables
  bundler: Vite (default)

Backend:
  runtime: Node.js 23
  framework: Nuxt server routes / Hono (for Edge)
  validation: Zod / TypeBox

Database:
  primary: PostgreSQL
  orm: Prisma / Drizzle
  hosting: Supabase / Neon

Auth:
  provider: Auth.js (v5) / Clerk

Monorepo:
  tool: Turborepo 2.0
```

## Alternative Options

| Need | Default | Alternative |
|------|---------|-------------|
| Real-time | - | Supabase Realtime, Socket.io |
| File storage | - | Cloudinary, S3 |
| Payment | Stripe | LemonSqueezy, Paddle |
| Email | - | Resend, SendGrid |
| Search | - | Algolia, Typesense |
