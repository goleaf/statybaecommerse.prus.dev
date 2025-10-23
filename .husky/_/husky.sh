echo "husky - DEPRECATED

Please remove the following two lines from $0:

#!/usr/bin/env sh
# Legacy shim retained for backwards compatibility; modern Husky hooks run via `.husky/_/h`.
# This no-op prevents v9+ upgrade warnings when older hook scripts still source `_/husky.sh`.

return 0 2>/dev/null || exit 0
