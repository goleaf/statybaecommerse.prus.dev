<?php

declare(strict_types=1);

namespace App\DataTransfer;

use App\DataTransfer\Contracts\DataTransferContract;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class UserProfilesDataTransfer implements DataTransferContract
{
    /**
     * @return array<int, string>
     */
    public function supportedFormats(): array
    {
        return ['json', 'csv'];
    }

    public function export(string $format, FilesystemAdapter $disk, string $path): void
    {
        $format = Str::lower($format);
        $this->guardFormat($format);

        $records = User::withoutGlobalScopes()
            ->orderBy('email')
            ->get(['email', 'name', 'preferred_locale', 'is_active', 'email_verified_at'])
            ->map(function (User $user): array {
                $rawTimestamp = $user->getAttribute('email_verified_at');
                $normalisedTimestamp = null;

                if ($rawTimestamp instanceof CarbonInterface) {
                    $normalisedTimestamp = $rawTimestamp->copy()->shiftTimezone('UTC')->toAtomString();
                }

                return [
                    'email'             => $user->email,
                    'name'              => $user->name,
                    'preferred_locale'  => $user->preferred_locale,
                    'is_active'         => $user->is_active,
                    'email_verified_at' => $normalisedTimestamp,
                ];
            })
            ->values()
            ->all();

        $payload = $this->serialize($format, $records);

        $disk->put($path, $payload);
    }

    public function import(string $format, FilesystemAdapter $disk, string $path): void
    {
        $format = Str::lower($format);
        $this->guardFormat($format);

        if (! $disk->exists($path)) {
            throw new InvalidArgumentException("Import file [{$path}] does not exist.");
        }

        $contents = $disk->get($path);
        if (! is_string($contents)) {
            throw new RuntimeException('Import file must be a string payload.');
        }

        $records = $this->deserialize($format, $contents);

        foreach ($records as $record) {
            $data = $this->validateRecord($record);

            $user = User::withoutGlobalScopes()->firstOrNew(['email' => $data['email']]);
            $user->fill([
                'name'              => $data['name'],
                'preferred_locale'  => $data['preferred_locale'],
                'is_active'         => $data['is_active'],
                'email_verified_at' => $data['email_verified_at'],
            ]);

            if (! $user->exists) {
                $user->password = Hash::make('password');
            }

            $user->save();
        }
    }

    private function guardFormat(string $format): void
    {
        if (! in_array($format, $this->supportedFormats(), true)) {
            throw new InvalidArgumentException("Unsupported format [{$format}].");
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function serialize(string $format, array $records): string
    {
        return match ($format) {
            'json'  => json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'csv'   => $this->toCsv($records),
            default => throw new InvalidArgumentException("Unsupported format [{$format}]."),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function deserialize(string $format, string $contents): array
    {
        return match ($format) {
            'json'  => $this->fromJson($contents),
            'csv'   => $this->fromCsv($contents),
            default => throw new InvalidArgumentException("Unsupported format [{$format}]."),
        };
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function toCsv(array $records): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open temporary stream for CSV export.');
        }

        $headers = $this->headers();
        fputcsv($handle, $headers);

        foreach ($records as $record) {
            $row = [];
            foreach ($headers as $header) {
                $value = $record[$header] ?? null;

                if ($value !== null && ! is_scalar($value)) {
                    throw new InvalidArgumentException("Record value for [{$header}] must be scalar or null.");
                }

                if (is_bool($value)) {
                    $row[] = $value ? '1' : '0';

                    continue;
                }

                $row[] = $value === null ? '' : (string) $value;
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        if ($csv === '') {
            throw new RuntimeException('CSV export produced an empty payload.');
        }

        return $csv;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fromJson(string $contents): array
    {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('JSON import payload must decode to an array.');
        }

        $rows = [];

        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            /** @var array<string, mixed> $item */
            $rows[] = $item;
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fromCsv(string $contents): array
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open temporary stream for CSV import.');
        }

        fwrite($handle, $contents);
        rewind($handle);

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            throw new InvalidArgumentException('CSV import payload is missing a header row.');
        }

        $headers = array_map(static fn ($header) => is_string($header) ? trim($header) : $header, $headers);
        $expected = $this->headers();
        $indexMap = [];

        foreach ($expected as $header) {
            $index = array_search($header, $headers, true);
            if ($index === false) {
                fclose($handle);

                throw new InvalidArgumentException("CSV import payload is missing required column [{$header}].");
            }

            $indexMap[$header] = $index;
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null]) {
                continue;
            }

            $values = [];
            foreach ($indexMap as $header => $index) {
                $values[$header] = $row[$index] ?? null;
            }

            $hasValue = false;
            foreach ($values as $value) {
                if ($value !== null && $value !== '') {
                    $hasValue = true;
                    break;
                }
            }

            if (! $hasValue) {
                continue;
            }

            /** @var array<string, string|null> $values */
            $rows[] = $values;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function validateRecord(array $record): array
    {
        /** @var array{email: string, name: string, preferred_locale: string|null, is_active: mixed, email_verified_at: string|null} $data */
        $data = Validator::make($record, [
            'email'             => ['required', 'string', 'email'],
            'name'              => ['required', 'string', 'max:255'],
            'preferred_locale'  => ['nullable', 'string', 'in:en,lt'],
            'is_active'         => ['nullable'],
            'email_verified_at' => ['nullable', 'string'],
        ])->validate();

        $data['preferred_locale'] = $data['preferred_locale'] ?? config('app.locale');
        $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $data['is_active'] = $data['is_active'] ?? true;

        $timestamp = $data['email_verified_at'] ?? null;
        if (is_string($timestamp)) {
            $timestamp = trim($timestamp);

            if ($timestamp === '') {
                $timestamp = null;
            }
        }

        $data['email_verified_at'] = is_string($timestamp) ? Carbon::parse($timestamp) : now();

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        return ['email', 'name', 'preferred_locale', 'is_active', 'email_verified_at'];
    }
}
