echo "husky - DEPRECATED

Please remove the following two lines from $0:

#!/usr/bin/env sh
# Husky shim to bootstrap Git hooks with the repository's local toolchain and config.
# Keeping this script intact prevents Husky v10 deprecation warnings from breaking our hook execution.
[ "$HUSKY" = "2" ] && set -x
n=$(basename "$0")
s=$(dirname "$(dirname "$0")")/$n

