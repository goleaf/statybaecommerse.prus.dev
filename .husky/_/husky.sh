#!/usr/bin/env sh
# Husky shim to bootstrap Git hooks with the repository's local toolchain and config.
# Restores the expected behaviour after accidental replacement with the deprecation banner
# so that Git hooks execute consistently while still surfacing actionable warnings for v10 upgrades.
[ "$HUSKY" = "2" ] && set -x
n=$(basename "$0")
s=$(dirname "$(dirname "$0")")/$n

Update your Git hook to source "$(dirname "$0")/h" instead of "$(dirname "$0")/husky.sh".
This repository ships with the new shim so the local Node and PHP toolchains stay on PATH.
MSG

exit 1
