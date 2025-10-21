# Platform Features Overview

## Feature Flag Management
- Admin resource now omits the active and enabled global scopes so teams can review inactive toggles alongside live ones for better governance.

## Additional Capabilities
- See [README.md](README.md) for the complete overview of storefront, operations, and analytics tooling.
- Git hooks are backed by the restored Husky shim, keeping automated formatting and QA checks aligned with the repository's Node toolchain.
- Filament analytics tooling now loads without signature mismatches because the User Product Interaction resource returns the concrete `Form`/`Table` types Filament v4 expects.
