#!/bin/bash

# Safe command runner with timeout and output handling
# Usage: ./scripts/safe-run.sh "command" [timeout_seconds]

set -e

# Default timeout of 5 minutes
TIMEOUT=${2:-300}
COMMAND="$1"

if [ -z "$COMMAND" ]; then
    echo "Usage: $0 \"command\" [timeout_seconds]"
    exit 1
fi

echo "Running: $COMMAND"
echo "Timeout: ${TIMEOUT}s"
echo "Started at: $(date)"
echo "----------------------------------------"

# Create a temporary file for output
OUTPUT_FILE=$(mktemp)
PID_FILE=$(mktemp)

# Function to cleanup
cleanup() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE" 2>/dev/null || echo "")
        if [ ! -z "$PID" ] && kill -0 "$PID" 2>/dev/null; then
            echo "Terminating process $PID..."
            kill -TERM "$PID" 2>/dev/null || true
            sleep 2
            kill -KILL "$PID" 2>/dev/null || true
        fi
        rm -f "$PID_FILE"
    fi
    rm -f "$OUTPUT_FILE"
}

# Set up signal handlers
trap cleanup EXIT INT TERM

# Run command with timeout
timeout "$TIMEOUT" bash -c "$COMMAND" > "$OUTPUT_FILE" 2>&1 &
COMMAND_PID=$!
echo "$COMMAND_PID" > "$PID_FILE"

# Wait for command to complete
wait "$COMMAND_PID"
EXIT_CODE=$?

echo "----------------------------------------"
echo "Completed at: $(date)"
echo "Exit code: $EXIT_CODE"

# Display output
if [ -f "$OUTPUT_FILE" ]; then
    echo "Output:"
    cat "$OUTPUT_FILE"
fi

exit $EXIT_CODE
