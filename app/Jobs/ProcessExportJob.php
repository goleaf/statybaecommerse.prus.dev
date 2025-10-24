<?php

declare(strict_types=1);

namespace App\Jobs;

/**
 * Backwards-compatible alias for the legacy ProcessExportJob name.
 *
 * New code should depend on {@see ProcessExport}, but tests and integrations that
 * still expect the older class continue to function through this thin wrapper.
 */
final class ProcessExportJob extends ProcessExport
{
}

