<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\ProductImporter;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

final class ImportProducts extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/products';

    protected static function getImporterClass(): string
    {
        return ProductImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('translations.import') . ' ' . __('translations.products');
    }

    protected function resolveImportUser(?Authenticatable $user): ?Authenticatable
    {
        if (! $user instanceof AdminUser) {
            return $user;
        }

        if (! filled($user->email)) {
            return $user;
        }

        $existing = User::withoutGlobalScopes()

            ->where('email', $user->email)
            ->first();

        if ($existing) {
            return $existing;
        }

        return User::withoutGlobalScopes()->create([
            'name'      => $user->name ?: $user->email,
            'email'     => $user->email,
            'password'  => $this->generateSecurePassword(),
            'is_active' => true,
        ]);
    }

    protected function generateSecurePassword(): string
    {
        $lower = Str::lower(Str::random(4));
        $upper = Str::upper(Str::random(2));
        $digit = (string) random_int(0, 9);
        $special = '!';

        return str_shuffle($lower . $upper . $digit . $special . Str::random(4));
    }
}
