#!/usr/bin/env sh
# Legacy wrapper that informs contributors Husky v9 now uses the consolidated 'h' shim.
# Exit with a failure code so outdated hooks cannot silently continue with an invalid PATH.
cat <<'MSG'
husky - The bootstrap script has moved.

Update your Git hook to source "$(dirname "$0")/h" instead of "$(dirname "$0")/husky.sh".
This repository ships with the new shim so the local Node and PHP toolchains stay on PATH.
MSG

exit 1
