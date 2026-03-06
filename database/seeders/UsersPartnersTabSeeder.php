<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Collection;

final class UsersPartnersTabSeeder extends BaseSeeder
{
    private const USER_COUNT = 12;

    public function run(): void
    {
        $companies = $this->ensureCompanies();
        $partners = $this->ensurePartners();

        for ($index = 1; $index <= self::USER_COUNT; $index++) {
            /** @var Company $company */
            $company = $companies[($index - 1) % $companies->count()];
            $user = $this->upsertUser($index, $company);

            /** @var Partner $primaryPartner */
            $primaryPartner = $partners[($index - 1) % $partners->count()];
            /** @var Partner $secondaryPartner */
            $secondaryPartner = $partners[$index % $partners->count()];

            $user->partners()->sync([
                (int) $primaryPartner->getKey(),
                (int) $secondaryPartner->getKey(),
            ]);
        }

        $this->command?->info('UsersPartnersTabSeeder: partners tab users were seeded.');
    }

    /**
     * @return Collection<int, Company>
     */
    private function ensureCompanies(): Collection
    {
        $companies = Company::query()->withoutGlobalScopes()->orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->call(CompanySeeder::class);
            $companies = Company::query()->withoutGlobalScopes()->orderBy('id')->get();
        }

        if ($companies->isEmpty()) {
            $companies = Company::factory()->count(6)->create();
        }

        return $companies;
    }

    /**
     * @return Collection<int, Partner>
     */
    private function ensurePartners(): Collection
    {
        $partners = Partner::query()->withoutGlobalScopes()->orderBy('id')->get();

        if ($partners->isEmpty()) {
            $partners = Partner::factory()->count(6)->create();
        }

        return $partners;
    }

    private function upsertUser(int $index, Company $company): User
    {
        $email = sprintf('info@egisstatyba.lt', $index);

        $user = User::query()->withoutGlobalScopes()->firstOrNew([
            'email' => $email,
        ]);

        $user->fill([
            'name'              => sprintf('Partner User %02d', $index),
            'first_name'        => 'Partner',
            'last_name'         => sprintf('User %02d', $index),
            'account_type'      => 'company',
            'company_id'        => (int) $company->getKey(),
            'company'           => (string) $company->name,
            'job_title'         => 'Business Development Manager',
            'preferred_locale'  => 'lt',
            'phone_number'      => sprintf('+370620%04d', 3000 + $index),
            'is_active'         => true,
            'is_admin'          => false,
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        $user->save();

        return $user;
    }
}
