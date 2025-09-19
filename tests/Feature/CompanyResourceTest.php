<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\CompanyResource\Pages\EditCompany;
use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use App\Filament\Resources\CompanyResource\Pages\ViewCompany;
use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CompanyResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_companies(): void
    {
        $companies = Company::factory()->count(3)->create();

        Livewire::test(ListCompanies::class)
            ->assertOk()
            ->assertCanSeeTableRecords($companies);
    }

    public function test_can_create_company(): void
    {
        $companyData = Company::factory()->make();

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => $companyData->name,
                'email' => $companyData->email,
                'phone' => $companyData->phone,
                'website' => $companyData->website,
                'industry' => $companyData->industry,
                'size' => $companyData->size,
                'description' => $companyData->description,
                'is_active' => $companyData->is_active,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Company::class, [
            'name' => $companyData->name,
            'email' => $companyData->email,
        ]);
    }

    public function test_can_edit_company(): void
    {
        $company = Company::factory()->create();
        $newData = Company::factory()->make();

        Livewire::test(EditCompany::class, [
            'record' => $company->id,
        ])
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'name' => $newData->name,
            'email' => $newData->email,
        ]);
    }

    public function test_can_view_company(): void
    {
        $company = Company::factory()->create();

        Livewire::test(ViewCompany::class, [
            'record' => $company->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_company(): void
    {
        $company = Company::factory()->create();

        Livewire::test(EditCompany::class, [
            'record' => $company->id,
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing(Company::class, [
            'id' => $company->id,
        ]);
    }

    public function test_can_toggle_company_active_status(): void
    {
        $company = Company::factory()->create(['is_active' => true]);

        Livewire::test(ListCompanies::class)
            ->callTableAction('toggle_active', $company)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_activate_companies(): void
    {
        $companies = Company::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListCompanies::class)
            ->callTableBulkAction('activate', $companies)
            ->assertHasNoTableBulkActionErrors();

        foreach ($companies as $company) {
            $this->assertDatabaseHas(Company::class, [
                'id' => $company->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_companies(): void
    {
        $companies = Company::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(ListCompanies::class)
            ->callTableBulkAction('deactivate', $companies)
            ->assertHasNoTableBulkActionErrors();

        foreach ($companies as $company) {
            $this->assertDatabaseHas(Company::class, [
                'id' => $company->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_filter_companies_by_size(): void
    {
        $smallCompany = Company::factory()->create(['size' => 'small']);
        $largeCompany = Company::factory()->create(['size' => 'large']);

        Livewire::test(ListCompanies::class)
            ->filterTable('size', 'small')
            ->assertCanSeeTableRecords([$smallCompany])
            ->assertCanNotSeeTableRecords([$largeCompany]);
    }

    public function test_can_filter_companies_by_active_status(): void
    {
        $activeCompany = Company::factory()->create(['is_active' => true]);
        $inactiveCompany = Company::factory()->create(['is_active' => false]);

        Livewire::test(ListCompanies::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeCompany])
            ->assertCanNotSeeTableRecords([$inactiveCompany]);
    }

    public function test_can_search_companies_by_name(): void
    {
        $company1 = Company::factory()->create(['name' => 'Test Company 1']);
        $company2 = Company::factory()->create(['name' => 'Another Company']);

        Livewire::test(ListCompanies::class)
            ->searchTable('Test Company')
            ->assertCanSeeTableRecords([$company1])
            ->assertCanNotSeeTableRecords([$company2]);
    }

    public function test_can_search_companies_by_email(): void
    {
        $company1 = Company::factory()->create(['email' => 'test@example.com']);
        $company2 = Company::factory()->create(['email' => 'other@example.com']);

        Livewire::test(ListCompanies::class)
            ->searchTable('test@example.com')
            ->assertCanSeeTableRecords([$company1])
            ->assertCanNotSeeTableRecords([$company2]);
    }

    public function test_company_validation_requires_name(): void
    {
        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => '',
                'email' => 'test@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_company_validation_requires_valid_email(): void
    {
        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Test Company',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    public function test_company_validation_requires_valid_website(): void
    {
        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Test Company',
                'website' => 'not-a-valid-url',
            ])
            ->call('create')
            ->assertHasFormErrors(['website' => 'url']);
    }
}

