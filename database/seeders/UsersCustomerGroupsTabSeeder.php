<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Support\Collection;

final class UsersCustomerGroupsTabSeeder extends BaseSeeder
{
    private const USER_COUNT = 12;

    public function run(): void
    {
        $companies = $this->ensureCompanies();
        $customerGroups = $this->ensureCustomerGroups();

        for ($index = 1; $index <= self::USER_COUNT; $index++) {
            /** @var Company $company */
            $company = $companies[($index - 1) % $companies->count()];
            $user = $this->upsertUser($index, $company);

            /** @var CustomerGroup $primaryGroup */
            $primaryGroup = $customerGroups[($index - 1) % $customerGroups->count()];
            /** @var CustomerGroup $secondaryGroup */
            $secondaryGroup = $customerGroups[$index % $customerGroups->count()];

            $user->customerGroups()->sync([
                (int) $primaryGroup->getKey() => [
                    'assigned_at' => now()->subDays($index),
                ],
                (int) $secondaryGroup->getKey() => [
                    'assigned_at' => now()->subDays(max($index - 1, 0)),
                ],
            ]);
        }

        $this->command?->info('UsersCustomerGroupsTabSeeder: customer_groups tab users were seeded.');
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
     * @return Collection<int, CustomerGroup>
     */
    private function ensureCustomerGroups(): Collection
    {
        $groups = CustomerGroup::query()->withoutGlobalScopes()->orderBy('id')->get();

        if ($groups->isEmpty()) {
            $groups = CustomerGroup::factory()->count(6)->create();
        }

        return $groups;
    }

    private function upsertUser(int $index, Company $company): User
    {
        $email = sprintf('info@egisstatyba.lt', $index);

        $user = User::query()->withoutGlobalScopes()->firstOrNew([
            'email' => $email,
        ]);

        $user->fill([
            'name'              => sprintf('Customer Group User %02d', $index),
            'first_name'        => 'Customer Group',
            'last_name'         => sprintf('User %02d', $index),
            'account_type'      => 'company',
            'company_id'        => (int) $company->getKey(),
            'company'           => (string) $company->name,
            'job_title'         => 'Procurement Specialist',
            'preferred_locale'  => 'lt',
            'phone_number'      => sprintf('+370610%04d', 2000 + $index),
            'is_active'         => true,
            'is_admin'          => false,
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        $user->save();

        return $user;
    }
}
