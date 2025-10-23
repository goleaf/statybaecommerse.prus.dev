echo "husky - DEPRECATED

Please remove the following two lines from $0:

#!/usr/bin/env sh
# This shim bootstraps Husky Git hooks so that repository scripts run consistently across environments.
[ "$HUSKY" = "2" ] && set -x
n=$(basename "$0")
s=$(dirname "$(dirname "$0")")/$n

They WILL FAIL in v10.0.0
"