<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use League\Csv\Bom;
use League\Csv\Writer;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadImportFailureCsvController extends Controller
{
    public function __invoke(Request $request, Import $import): StreamedResponse
    {
        $authGuard = $request->hasValidSignature(absolute: false)
            ? $request->query('authGuard')
            : null;

        $guard = auth($authGuard);

        abort_unless($guard->check(), 401);

        $user = $guard->user();

        $importPolicy = Gate::getPolicyFor($import::class);

        if (filled($importPolicy) && method_exists($importPolicy, 'view')) {
            Gate::forUser($user)->authorize('view', Arr::wrap($import));
        } else {
            abort_unless($this->canAccessImport($user, $import), 403);
        }

        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setOutputBOM(Bom::Utf8);

        /** @var ?FailedImportRow $firstFailedRow */
        $firstFailedRow = $import->failedRows()->first();

        $columnHeaders = $firstFailedRow ? array_keys($firstFailedRow->data) : [];
        $columnHeaders[] = __('filament-actions::import.failure_csv.error_header');

        $csv->insertOne($columnHeaders);

        $import->failedRows()
            ->lazyById(100)
            ->each(fn (FailedImportRow $failedImportRow) => $csv->insertOne([/** @phpstan-ignore argument.type */
                ...$failedImportRow->data,
                'error' => $failedImportRow->validation_error ?? __('filament-actions::import.failure_csv.system_error'),
            ]));

        return response()->streamDownload(function () use ($csv): void {
            foreach ($csv->chunk(1000) as $offset => $chunk) {
                echo $chunk;

                if ($offset % 1000) {
                    flush();
                }
            }
        }, __('filament-actions::import.failure_csv.file_name', [
            'import_id' => $import->getKey(),
            'csv_name'  => (string) str($import->file_name)->beforeLast('.')->remove('.'),
        ]) . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function canAccessImport(?Authenticatable $user, Import $import): bool
    {
        if (! $user instanceof Authenticatable) {
            return false;
        }

        if ($user instanceof AdminUser) {
            return true;
        }

        return $import->user()->is($user);
    }
}
