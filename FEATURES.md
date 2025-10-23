# Features & Recent Enhancements

## Developer Experience
- Restored the Husky bootstrap shim so local Git hooks once again run with the repository-provided Node toolchain while still displaying the upstream deprecation notice for future Husky releases.

## Reference
- Review `.husky/_/husky.sh` for the shim implementation and hook execution comments that explain how the toolchain hand-off works.
