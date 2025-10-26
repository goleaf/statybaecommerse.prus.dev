<?php

/**
 * This script inspects Laravel controllers under app/Http/Controllers and prints a Markdown report.
 * It is intentionally heuristic-based because full static analysis would require booting Laravel.
 */
declare(strict_types=1);

// Always show errors when running from CLI so tooling can catch typos quickly.
ini_set('display_errors', '1');
error_reporting(E_ALL);

/**
 * Resolve the directory that contains controller classes.
 * The path is relative to this script's location to avoid relying on cwd.
 */
$controllerDir = realpath(__DIR__ . '/../app/Http/Controllers');

if ($controllerDir === false) {
    fwrite(STDERR, 'Unable to locate controllers directory.' . PHP_EOL);
    exit(1);
}

/**
 * Helper function to collect all PHP files inside the controller directory recursively.
 *
 * @param  string             $baseDir Directory to search.
 * @return array<int, string> List of absolute file paths.
 */
function listControllerFiles(string $baseDir): array
{
    // RecursiveDirectoryIterator keeps references to directories, so we disable dots for clarity.
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];

    foreach ($iterator as $fileInfo) {
        if ($fileInfo instanceof SplFileInfo && $fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
            $files[] = $fileInfo->getRealPath();
        }
    }

    sort($files);

    return $files;
}

/**
 * Extract public method names via token parsing so we avoid false positives in comments.
 *
 * @param  string             $code PHP source code to inspect.
 * @return array<int, string> List of method names found.
 */
function extractPublicMethods(string $code): array
{
    $tokens = token_get_all($code);
    $methodNames = [];
    $total = count($tokens);

    for ($index = 0; $index < $total; $index++) {
        $token = $tokens[$index];

        if (is_array($token) && $token[0] === T_FUNCTION) {
            // Walk backwards to find the visibility modifier for this function.
            $isPublic = false;

            for ($back = $index - 1; $back >= 0; $back--) {
                $previous = $tokens[$back];

                if (is_array($previous)) {
                    if ($previous[0] === T_PUBLIC) {
                        $isPublic = true;
                        break;
                    }

                    if (in_array($previous[0], [T_PROTECTED, T_PRIVATE], true)) {
                        // We encountered a different visibility, so this is not public.
                        break;
                    }

                    if ($previous[0] === T_VARIABLE) {
                        // Handles cases like closures assigned to properties; stop scanning.
                        break;
                    }
                } elseif (is_string($previous) && $previous === ';') {
                    break;
                }
            }

            if ($isPublic === false) {
                continue;
            }

            // Skip reference markers and whitespace to reach the function name token.
            $nameIndex = $index + 1;

            while ($nameIndex < $total) {
                $nextToken = $tokens[$nameIndex];

                if (is_array($nextToken) && $nextToken[0] === T_STRING) {
                    $methodNames[] = $nextToken[1];
                    break;
                }

                $nameIndex++;
            }
        }
    }

    return array_values(array_unique($methodNames));
}

/**
 * Assess various heuristics for a controller file so we can populate the audit table.
 *
 * @param  string               $relativePath Path relative to repository root, used for reporting.
 * @param  string               $code         Source code content.
 * @return array<string, mixed> Structured metadata describing the controller.
 */
function analyseController(string $relativePath, string $code): array
{
    // Normalize whitespace to simplify some regex checks.
    $normalizedCode = strtolower($code);

    $publicMethods = extractPublicMethods($code);

    // Validation detection heuristics.
    $hasFormRequest = preg_match('/\\formrequest\\b/i', $code) === 1
        || preg_match('/extends\\s+formrequest/i', $code) === 1
        || preg_match('/formrequest\\s+\$/i', $code) === 1;

    $hasInlineValidation = str_contains($normalizedCode, '->validate(')
        || str_contains($normalizedCode, 'validator::make(');

    $validationSummary = $hasFormRequest ? 'FormRequest' : 'None';

    if ($hasInlineValidation) {
        $validationSummary = $validationSummary === 'FormRequest' ? 'FormRequest + Inline' : 'Inline';
    }

    // Authorization heuristics (authorize calls, middleware, or policy helpers).
    $hasAuthorization = str_contains($normalizedCode, '->authorize(')
        || str_contains($normalizedCode, 'authorizeforuser(')
        || str_contains($normalizedCode, 'authorizeresource(')
        || str_contains($normalizedCode, 'gate::authorize(')
        || preg_match("/middleware\\(.*'can:/i", $code) === 1;

    // Mass assignment risk detection.
    $massAssignmentRisk = preg_match('/->(fill|update|create)\(\$request->all\(/i', $code) === 1
        || preg_match('/::create\(\$request->all\(/i', $code) === 1;

    // N+1 check: look for get()/all() without eager loading hints.
    $likelyNPlusOne = false;

    if (preg_match('/->(get|all)\(/i', $code) === 1 || preg_match('/::all\(/i', $code) === 1) {
        $likelyNPlusOne = ! str_contains($normalizedCode, '->with(')
            && ! str_contains($normalizedCode, '->load(')
            && ! str_contains($normalizedCode, 'with([');
    }

    // Transaction detection.
    $usesTransactions = str_contains($normalizedCode, 'db::transaction(')
        || str_contains($normalizedCode, 'begintransaction(');

    // HTTP status detection.
    $httpStatuses = [];
    preg_match_all('/->json\([^,]+,\s*(\d{3})/i', $code, $statusMatches);

    if (! empty($statusMatches[1])) {
        $httpStatuses = array_values(array_unique($statusMatches[1]));
    }

    if (str_contains($normalizedCode, 'response(') && empty($httpStatuses)) {
        $httpStatuses[] = 'implicit';
    }

    // Check for throw statements to gauge exception mapping.
    $throws = preg_match('/throw\s+new\s+/i', $code) === 1 ? 'Yes' : 'No';

    // Determine return type heuristics.
    $returns = [];

    if (str_contains($normalizedCode, 'return view(')) {
        $returns[] = 'View';
    }

    if (str_contains($normalizedCode, 'return response()->json')) {
        $returns[] = 'JSON';
    }

    if (str_contains($normalizedCode, 'return inertia(')) {
        $returns[] = 'Inertia';
    }

    if (empty($returns)) {
        $returns[] = 'Mixed/Other';
    }

    $hasPagination = str_contains($normalizedCode, 'paginate(');

    $rateLimited = preg_match("/middleware\\(.*'throttle:/i", $code) === 1;

    $routeModelBinding = preg_match('/function\s+\w+\s*\(([^)]*\\\\\w+\s+\$\w+)/i', $code) === 1;

    $todos = preg_match('/TODO/i', $code) === 1 ? 'Present' : 'None';

    return [
        'file'            => $relativePath,
        'methods'         => $publicMethods,
        'validation'      => $validationSummary,
        'authorization'   => $hasAuthorization ? 'Present' : 'Missing',
        'mass_assignment' => $massAssignmentRisk ? 'Risky pattern' : 'Looks safe',
        'n_plus_one'      => $likelyNPlusOne ? 'Likely' : 'Monitored',
        'transactions'    => $usesTransactions ? 'Uses DB transactions' : 'Not detected',
        'http_statuses'   => $httpStatuses,
        'throws'          => $throws,
        'returns'         => $returns,
        'pagination'      => $hasPagination ? 'Yes' : 'No',
        'rate_limited'    => $rateLimited ? 'Yes' : 'No',
        'route_binding'   => $routeModelBinding ? 'Likely' : 'Unclear',
        'todos'           => $todos,
        'code'            => $code,
    ];
}

/**
 * Turn our structured metadata into a Markdown-safe string representation.
 *
 * @param array<int, string> $values The values to join.
 */
function formatList(array $values): string
{
    if (empty($values)) {
        return '—';
    }

    return implode('<br>', array_map(static fn (string $value): string => trim($value), $values));
}

$basePath = realpath(__DIR__ . '/..');

if ($basePath === false) {
    fwrite(STDERR, 'Unable to resolve repository root.' . PHP_EOL);
    exit(1);
}

$files = listControllerFiles($controllerDir);

// Allow callers to optionally pass a list of specific controller paths so the
// audit can focus on a subset of files. Each argument is resolved relative to
// the repository root, and we bail out if nothing matches to surface mistakes.
$requestedPaths = array_slice($argv ?? [], 1);

if (! empty($requestedPaths)) {
    $filteredFiles = [];

    foreach ($requestedPaths as $inputPath) {
        // Resolve both absolute and relative paths to ensure consistent
        // comparisons when we later filter the discovered controller files.
        $candidate = realpath($inputPath);

        if ($candidate === false) {
            $candidate = realpath($basePath . DIRECTORY_SEPARATOR . ltrim($inputPath, DIRECTORY_SEPARATOR));
        }

        if ($candidate === false) {
            fwrite(STDERR, 'Warning: Unable to resolve controller path: ' . $inputPath . PHP_EOL);

            continue;
        }

        if (is_dir($candidate)) {
            // Include every controller nested inside the requested directory so
            // teams can audit entire namespaces with one command.
            $filteredFiles = array_merge(
                $filteredFiles,
                array_filter(
                    $files,
                    static fn (string $file): bool => str_starts_with($file, $candidate . DIRECTORY_SEPARATOR)
                )
            );

            continue;
        }

        if (! str_starts_with($candidate, $controllerDir . DIRECTORY_SEPARATOR)) {
            fwrite(STDERR, 'Warning: Skipping non-controller path: ' . $inputPath . PHP_EOL);

            continue;
        }

        if (is_file($candidate)) {
            $filteredFiles[] = $candidate;
        }
    }

    $filteredFiles = array_values(array_unique($filteredFiles));

    if (empty($filteredFiles)) {
        fwrite(STDERR, 'No matching controller files found for supplied arguments.' . PHP_EOL);
        exit(1);
    }

    $files = $filteredFiles;
}

$rows = [];

foreach ($files as $file) {
    $relativePath = ltrim(str_replace($basePath, '', $file), DIRECTORY_SEPARATOR);
    $code = file_get_contents($file) ?: '';
    $rows[] = analyseController($relativePath, $code);
}

// Output the Markdown table header.
printf("| File | Public Methods | Validates With | Authorization | Mass-Assignment Risk | N+1 Risk | Transactions | HTTP Statuses | Throws | Returns | Pagination? | Rate-limited? | Route Model Binding? | TODOs |\n");
printf("| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |\n");

foreach ($rows as $row) {
    printf(
        "| %s | %s | %s | %s | %s | %s | %s | %s | %s | %s | %s | %s | %s | %s |\n",
        $row['file'],
        formatList($row['methods']),
        $row['validation'],
        $row['authorization'],
        $row['mass_assignment'],
        $row['n_plus_one'],
        $row['transactions'],
        formatList($row['http_statuses']),
        $row['throws'],
        formatList($row['returns']),
        $row['pagination'],
        $row['rate_limited'],
        $row['route_binding'],
        $row['todos']
    );
}

/**
 * Rank the most pressing issues to investigate by scoring each controller.
 */
$issueCandidates = [];

foreach ($rows as $row) {
    $score = 0;
    $reasons = [];

    if (str_contains(strtolower($row['code']), 'request ') && $row['validation'] === 'None') {
        $score += 5;
        $reasons[] = 'Handles Request without explicit validation.';
    }

    if ($row['mass_assignment'] === 'Risky pattern') {
        $score += 4;
        $reasons[] = 'Uses $request->all() in mutating call.';
    }

    $mutationMethods = ['store', 'update', 'destroy', 'delete', 'create'];

    if ($row['authorization'] === 'Missing') {
        foreach ($row['methods'] as $method) {
            if (in_array(strtolower($method), $mutationMethods, true)) {
                $score += 3;
                $reasons[] = 'Mutation method without clear authorization.';
                break;
            }
        }
    }

    if ($row['n_plus_one'] === 'Likely') {
        $score += 2;
        $reasons[] = 'Potential N+1 query risk from broad get()/all().';
    }

    if ($row['pagination'] === 'No' && preg_match('/index|list|search/i', implode(' ', $row['methods'])) === 1) {
        $score += 2;
        $reasons[] = 'Listing method without pagination detection.';
    }

    if ($row['rate_limited'] === 'No' && preg_match('/login|reset|checkout|order/i', $row['file']) === 1) {
        $score += 1;
        $reasons[] = 'Sensitive endpoint missing throttle middleware.';
    }

    if ($score > 0) {
        $issueCandidates[] = [
            'file'    => $row['file'],
            'score'   => $score,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }
}

usort($issueCandidates, static function (array $left, array $right): int {
    return $right['score'] <=> $left['score'];
});

$topIssues = array_slice($issueCandidates, 0, 10);

echo PHP_EOL . 'Top 10 controller issues to investigate:' . PHP_EOL;

foreach ($topIssues as $index => $issue) {
    $position = $index + 1;
    $reasonText = implode(' ', $issue['reasons']);
    printf('%d. %s — %s' . PHP_EOL, $position, $issue['file'], $reasonText);
}
