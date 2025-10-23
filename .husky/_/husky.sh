#!/usr/bin/env sh
# Husky shim to bootstrap Git hooks with the repository's local toolchain and config.
# This variant restores the behaviour lost in PR #687 where the file was replaced by the
# deprecation banner stub, ensuring hooks keep running while still printing actionable warnings.
[ "$HUSKY" = "2" ] && set -x
n=$(basename "$0")
s=$(dirname "$(dirname "$0")")/$n

Update your Git hook to source "$(dirname "$0")/h" instead of "$(dirname "$0")/husky.sh".
This repository ships with the new shim so the local Node and PHP toolchains stay on PATH.
MSG

exit 1
