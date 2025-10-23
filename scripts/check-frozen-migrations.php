<?php

declare(strict_types=1);

$baseRef = getenv('MIGRATIONS_BASE_REF');
$githubBase = getenv('GITHUB_BASE_REF');

if ($baseRef === false || $baseRef === '') {
    $baseRef = $githubBase ? 'origin/' . $githubBase : 'origin/main';
}

$mergeBase = trim((string) shell_exec(sprintf('git merge-base HEAD %s 2>/dev/null', escapeshellarg($baseRef))));

if ($mergeBase === '') {
    $mergeBase = 'HEAD^';
}

$diffCommand = sprintf(
    'git diff --name-status %s...HEAD -- database/migrations',
    escapeshellarg($mergeBase),
);

exec($diffCommand, $output, $status);

if ($status !== 0) {
    fwrite(STDERR, "Unable to determine migration changes. Ensure the base branch is available.\n");
    exit(1);
}

$violations = [];

foreach ($output as $line) {
    if ($line === '') {
        continue;
    }

    [$changeType] = explode("\t", $line);
    $changeType = strtoupper($changeType);

    if (! str_starts_with($changeType, 'A')) {
        $violations[] = $line;
    }
}

if ($violations !== []) {
    fwrite(
        STDERR,
        "Historical migrations are frozen. Modify them only by introducing new forward-only migrations.\n" .
        "The following changes violate the policy:\n" .
        implode("\n", $violations) . "\n",
    );

    exit(1);
}

exit(0);
