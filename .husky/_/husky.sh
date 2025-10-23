#!/usr/bin/env sh
# Husky shim to bootstrap Git hooks with the repository's local toolchain and config.
# This variant restores the behaviour lost when the file was replaced by the
# deprecation banner stub, ensuring hooks keep running while still printing actionable warnings.
[ "$HUSKY" = "2" ] && set -x
n=$(basename "$0")
s=$(dirname "$(dirname "$0")")/$n

Update your Git hook to source "$(dirname "$0")/h" instead of "$(dirname "$0")/husky.sh".
This repository ships with the new shim so the local Node and PHP toolchains stay on PATH.
MSG

if [ -f "$HOME/.huskyrc" ]; then
    echo "husky - '~/.huskyrc' is DEPRECATED, please move your code to ~/.config/husky/init.sh"
fi
i="${XDG_CONFIG_HOME:-$HOME/.config}/husky/init.sh"
[ -f "$i" ] && . "$i"

[ "${HUSKY-}" = "0" ] && exit 0

export PATH="node_modules/.bin:$PATH"
# Ensure Git hooks execute using the repository's local toolchain so commands remain consistent across environments.
# This guard also gives us one place to adapt whenever Husky v10 introduces its new bootstrap entrypoint semantics.
sh -e "$s" "$@"
c=$?

[ $c != 0 ] && echo "husky - $n script failed (code $c)"
[ $c = 127 ] && echo "husky - command not found in PATH=$PATH"
exit $c
