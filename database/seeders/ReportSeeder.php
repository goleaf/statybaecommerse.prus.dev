<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

final class ReportSeeder extends Seeder
{
    public function run(): void
    {
        Report::factory()->create([
            'name' => 'Pardavimų ataskaita',
            'type' => 'sales',
            'date_range' => 'last_30_days',
        ]);

        Report::factory()->create([
            'name' => 'Produktų veiklos ataskaita',
            'type' => 'products',
            'date_range' => 'last_30_days',
        ]);

        Report::factory()->create([
            'name' => 'Klientų analizės ataskaita',
            'type' => 'customers',
            'date_range' => 'last_30_days',
        ]);

        Report::factory()->create([
            'name' => 'Atsargų ataskaita',
            'type' => 'inventory',
            'date_range' => 'last_30_days',
        ]);
    }
}
