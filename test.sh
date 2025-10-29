#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default sort order
SORT_ORDER="asc"

# Parse command line arguments
for arg in "$@"; do
    case $arg in
        --sort=asc)
            SORT_ORDER="asc"
            shift
            ;;
        --sort=desc)
            SORT_ORDER="desc"
            shift
            ;;
        *)
            echo -e "${RED}Unknown argument: $arg${NC}"
            echo -e "${YELLOW}Usage: $0 [--sort=asc|--sort=desc]${NC}"
            exit 1
            ;;
    esac
done

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Collecting and Running Tests${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Find all test files
echo -e "${YELLOW}Collecting test files...${NC}"
if [ "$SORT_ORDER" = "desc" ]; then
    echo -e "${YELLOW}Sort order: Descending${NC}"
    test_files=$(find tests -type f -name "*Test.php" | sort -r)
else
    echo -e "${YELLOW}Sort order: Ascending${NC}"
    test_files=$(find tests -type f -name "*Test.php" | sort)
fi

# Count total tests
total_tests=$(echo "$test_files" | wc -l)
current_test=0
passed_tests=0
failed_tests=0

echo -e "${GREEN}Found $total_tests test files${NC}"
echo ""

# Array to store failed tests
declare -a failed_test_list=()

# Run each test individually
for test_file in $test_files; do
    current_test=$((current_test + 1))
    
    # Extract test name without .php extension and path
    test_name=$(basename "$test_file" .php)
    
    # Extract relative path for display
    relative_path="${test_file#tests/}"
    
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}[$current_test/$total_tests] Running: $relative_path${NC}"
    echo -e "${BLUE}========================================${NC}"
    
    # Run the test
    if php artisan test --filter="$test_name"; then
        passed_tests=$((passed_tests + 1))
        echo -e "${GREEN}✓ PASSED: $test_name${NC}"
    else
        failed_tests=$((failed_tests + 1))
        failed_test_list+=("$test_name ($relative_path)")
        echo -e "${RED}✗ FAILED: $test_name${NC}"
    fi
    
    echo ""
done

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Test Summary${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "Total Tests:  $total_tests"
echo -e "${GREEN}Passed:       $passed_tests${NC}"
echo -e "${RED}Failed:       $failed_tests${NC}"
echo ""

# List failed tests if any
if [ $failed_tests -gt 0 ]; then
    echo -e "${RED}Failed Tests:${NC}"
    for failed_test in "${failed_test_list[@]}"; do
        echo -e "${RED}  - $failed_test${NC}"
    done
    echo ""
    exit 1
else
    echo -e "${GREEN}All tests passed! 🎉${NC}"
    exit 0
fi

