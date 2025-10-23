echo "husky - DEPRECATED

Please remove the following two lines from $0:

#!/usr/bin/env sh
# Legacy shim retained for backwards compatibility; modern Husky hooks run via `.husky/_/h`.
# This no-op prevents v9+ upgrade warnings when older hook scripts still source `_/husky.sh`.

[ ! -f "$s" ] && exit 0

if [ -f "$HOME/.huskyrc" ]; then
    echo "husky - '~/.huskyrc' is DEPRECATED, please move your code to ~/.config/husky/init.sh"
fi
i="${XDG_CONFIG_HOME:-$HOME/.config}/husky/init.sh"
[ -f "$i" ] && . "$i"

[ "${HUSKY-}" = "0" ] && exit 0

export PATH="node_modules/.bin:$PATH"
# Ensure Git hooks execute using the repository's local toolchain so commands remain consistent.
sh -e "$s" "$@"
c=$?

[ $c != 0 ] && echo "husky - $n script failed (code $c)"
[ $c = 127 ] && echo "husky - command not found in PATH=$PATH"
exit $c
