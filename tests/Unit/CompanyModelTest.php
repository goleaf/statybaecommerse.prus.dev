<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CompanyModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('companies')) {
            Artisan::call('migrate', [
                '--database' => 'sqlite',
                '--path'     => 'database/migrations/2025_09_14_163833_create_companies_table.php',
                '--force'    => true,
            ]);
        }

        if (Schema::hasTable('companies')) {
            // Ensure each test starts from a clean slate when the schema already exists.
            Company::query()->delete();
        }
    }

    public function test_can_create_company(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);
    }

    public function test_company_factory_works(): void
    {
        $company = Company::factory()->create();

        $this->assertInstanceOf(Company::class, $company);
        $this->assertNotEmpty($company->name);
    }

    public function test_company_has_correct_fillable_attributes(): void
    {
        $company = new Company;
        $fillable = $company->getFillable();

        $expectedFillable = [
            'name',
            'email',
            'phone',
            'address',
            'website',
            'industry',
            'size',
            'description',
            'is_active',
            'metadata',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_company_has_correct_casts(): void
    {
        $company = new Company;
        $casts = $company->getCasts();

        $this->assertArrayHasKey('metadata', $casts);
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertEquals('array', $casts['metadata']);
        $this->assertEquals('boolean', $casts['is_active']);
    }

    public function test_company_can_be_created_with_metadata(): void
    {
        $metadata = [
            'founded_year' => 2020,
            'employee_count' => 50,
            'revenue' => 1000000,
        ];

        $company = Company::factory()->create([
            'metadata' => $metadata,
        ]);

        $this->assertEquals($metadata, $company->metadata);
    }

    public function test_company_active_scope_works(): void
    {
        Company::factory()->create(['is_active' => true]);
        Company::factory()->create(['is_active' => false]);

        $activeCompanies = Company::active()->get();

        $this->assertCount(1, $activeCompanies);
        $this->assertTrue($activeCompanies->first()->is_active);
    }

    public function test_company_by_industry_scope_works(): void
    {
        Company::factory()->create(['industry' => 'Technology']);
        Company::factory()->create(['industry' => 'Healthcare']);

        $techCompanies = Company::byIndustry('Technology')->get();

        $this->assertCount(1, $techCompanies);
        $this->assertEquals('Technology', $techCompanies->first()->industry);
    }

    public function test_company_by_size_scope_works(): void
    {
        Company::factory()->create(['size' => 'small']);
        Company::factory()->create(['size' => 'large']);

        $smallCompanies = Company::bySize('small')->get();

        $this->assertCount(1, $smallCompanies);
        $this->assertEquals('small', $smallCompanies->first()->size);
    }
}
