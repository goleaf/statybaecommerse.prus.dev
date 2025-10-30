#!/bin/bash
# Laravel per-file test runner with native colors and live failed-tests.txt updates.
# Adds TOP and BOTTOM colored stats tables with percentages.

set +e  # never stop on individual test failures

# ---------- Config & Paths ----------
SORT_ORDER="asc"

# Colors (used ONLY for our stats/table, not altering PHPUnit/Pest output)
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[1;34m'
BOLD='\033[1m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
SUCCESS_FILE="$SCRIPT_DIR/success-tests.txt"
FAILED_FILE="$SCRIPT_DIR/failed-tests.txt"

touch "$SUCCESS_FILE" "$FAILED_FILE"

cd "$PROJECT_ROOT" || exit 1

# ---------- Helpers ----------
pct() {
  # pct numerator denominator -> prints percentage with 1 decimal
  local n="$1" d="$2"
  if [ "$d" -eq 0 ]; then printf "0.0"; return; fi
  awk -v n="$n" -v d="$d" 'BEGIN { printf "%.1f", (n*100.0)/d }'
}

count_unique_existing_lines() {
  # counts unique, non-empty lines; ignores whitespace-only
  local f="$1"
  if [ ! -s "$f" ]; then echo 0; return; fi
  grep -v '^[[:space:]]*$' "$f" | sort -u | wc -l | tr -d ' '
}

append_unique() {
  local file="$1" line="$2"
  grep -Fxq "$line" "$file" || echo "$line" >> "$file"
}

remove_line() {
  local file="$1" line="$2"
  [ -f "$file" ] || return 0
  # Escape / and & for sed, anchor full line
  local esc
  esc=$(printf '%s\n' "$line" | sed 's/[\/&]/\\&/g')
  sed -i.bak "/^${esc}\$/d" "$file" && rm -f "${file}.bak"
}

draw_table() {
  # args: total passed failed queued
  local total="$1" passed="$2" failed="$3" queued="$4"
  local p_passed p_failed p_queued
  p_passed=$(pct "$passed" "$total")
  p_failed=$(pct "$failed" "$total")
  p_queued=$(pct "$queued" "$total")

  local w1=18 w2=10 w3=10 w4=18
  local line="+------------------+----------+----------+------------------+"

  echo -e "$line"
  printf "| %-*s | %-*s | %-*s | %-*s |\n" "$w1" "Metric" "$w2" "Count" "$w3" "% of all" "$w4" "Note"
  echo -e "$line"

  printf "| %-*s | ${GREEN}%-*s${NC} | ${GREEN}%-*s${NC} | %-*s |\n" \
    "$w1" "Passed" "$w2" "$passed" "$w3" "$p_passed%" "$w4" "Already green"
  printf "| %-*s | ${RED}%-*s${NC} | ${RED}%-*s${NC} | %-*s |\n" \
    "$w1" "Failed" "$w2" "$failed" "$w3" "$p_failed%" "$w4" "Needs fixing"
  printf "| %-*s | ${YELLOW}%-*s${NC} | ${YELLOW}%-*s${NC} | %-*s |\n" \
    "$w1" "To Run" "$w2" "$queued" "$w3" "$p_queued%" "$w4" "In this session"
  echo -e "$line"
}

# ---------- Args ----------
RESET=false
for arg in "$@"; do
  case $arg in
    --sort=asc) SORT_ORDER="asc" ;;
    --sort=desc) SORT_ORDER="desc" ;;
    --reset) RESET=true ;;
    *)
      echo "Usage: $0 [--sort=asc|--sort=desc] [--reset]"
      exit 1
      ;;
  esac
done

if [ "$RESET" = true ]; then
  rm -f "$SUCCESS_FILE" "$FAILED_FILE"
  touch "$SUCCESS_FILE" "$FAILED_FILE"
  echo -e "${YELLOW}Reset success & failed lists.${NC}"
fi

# ---------- Build test list ----------
# Prefer previously failed tests first (if they still exist)
mapfile -t failed_paths < <(grep -v '^[[:space:]]*$' "$FAILED_FILE" 2>/dev/null | sort -u)
declare -a ordered_test_files=()
for p in "${failed_paths[@]}"; do
  [ -f "$p" ] && ordered_test_files+=("$p")
done

# Add remaining tests (excluding already passed & already queued)
# Load sets
declare -A passed_set failed_set
while IFS= read -r l; do [ -n "$l" ] && passed_set["$l"]=1; done < <(grep -v '^[[:space:]]*$' "$SUCCESS_FILE" 2>/dev/null | sort -u)
while IFS= read -r l; do [ -n "$l" ] && failed_set["$l"]=1; done < <(grep -v '^[[:space:]]*$' "$FAILED_FILE" 2>/dev/null | sort -u)

if [ "$SORT_ORDER" = "desc" ]; then
  mapfile -t all_tests < <(find tests -type f -name "*Test.php" | sort -r)
else
  mapfile -t all_tests < <(find tests -type f -name "*Test.php" | sort)
fi

for test_file in "${all_tests[@]}"; do
  if [ "${failed_set[$test_file]-0}" != "1" ] && [ "${passed_set[$test_file]-0}" != "1" ]; then
    ordered_test_files+=("$test_file")
  fi
done

total_tests=$(find tests -type f -name "*Test.php" | wc -l | tr -d ' ')
prev_passed=$(count_unique_existing_lines "$SUCCESS_FILE")
prev_failed=$(count_unique_existing_lines "$FAILED_FILE")
queue_count=${#ordered_test_files[@]}

# ---------- TOP STATS ----------
echo -e "${BOLD}${BLUE}Laravel Tests — Session Plan${NC}"
draw_table "$total_tests" "$prev_passed" "$prev_failed" "$queue_count"
echo ""

# ---------- Run ----------
current=0
passed_now=0
failed_now=0
fixed_now=0

for test_file in "${ordered_test_files[@]}"; do
  current=$((current + 1))
  test_name="$(basename "$test_file" .php)"
  echo -e "${BOLD}[${current}/${queue_count}] php artisan test --filter=${test_name}${NC}"

  php artisan test --filter="$test_name"
  code=$?

  if [ $code -ne 0 ]; then
    failed_now=$((failed_now + 1))
    append_unique "$FAILED_FILE" "$test_file"
  else
    passed_now=$((passed_now + 1))
    echo "$test_file" >> "$SUCCESS_FILE"
    # If it was previously failed, count as fixed
    if [ "${failed_set[$test_file]-0}" = "1" ]; then
      fixed_now=$((fixed_now + 1))
    fi
    remove_line "$FAILED_FILE" "$test_file"
  fi
  echo ""
done

# ---------- BOTTOM STATS ----------
new_passed=$(count_unique_existing_lines "$SUCCESS_FILE")
new_failed=$(count_unique_existing_lines "$FAILED_FILE")
remaining=$(( total_tests - new_passed - new_failed ))
[ $remaining -lt 0 ] && remaining=0

echo -e "${BOLD}${BLUE}Laravel Tests — Session Summary${NC}"
draw_table "$total_tests" "$new_passed" "$new_failed" "$remaining"
echo ""
echo -e "This session: ${GREEN}+${passed_now} passed${NC}, ${RED}+${failed_now} failed${NC}, ${GREEN}+${fixed_now} fixed from failed${NC}."
exit 0
