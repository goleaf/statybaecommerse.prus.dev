<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

final class ReportSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample reports with different types and categories
        $reports = [
            [
                'name' => [
                    'lt' => 'Pardavimų ataskaita',
                    'en' => 'Sales Report',
                ],
                'type' => 'sales',
                'category' => 'sales',
                'date_range' => 'last_30_days',
                'description' => [
                    'lt' => 'Išsamus pardavimų ataskaita su analize ir tendencijomis',
                    'en' => 'Comprehensive sales report with analysis and trends',
                ],
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => [
                    'lt' => 'Produktų veiklos ataskaita',
                    'en' => 'Product Performance Report',
                ],
                'type' => 'products',
                'category' => 'analytics',
                'date_range' => 'last_30_days',
                'description' => [
                    'lt' => 'Produktų pardavimų ir populiarumo analizė',
                    'en' => 'Product sales and popularity analysis',
                ],
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => [
                    'lt' => 'Klientų analizės ataskaita',
                    'en' => 'Customer Analysis Report',
                ],
                'type' => 'customers',
                'category' => 'analytics',
                'date_range' => 'last_30_days',
                'description' => [
                    'lt' => 'Klientų elgsenos ir segmentacijos analizė',
                    'en' => 'Customer behavior and segmentation analysis',
                ],
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => [
                    'lt' => 'Atsargų ataskaita',
                    'en' => 'Inventory Report',
                ],
                'type' => 'inventory',
                'category' => 'operations',
                'date_range' => 'last_30_days',
                'description' => [
                    'lt' => 'Atsargų būklės ir judėjimo ataskaita',
                    'en' => 'Inventory status and movement report',
                ],
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => [
                    'lt' => 'Finansinė ataskaita',
                    'en' => 'Financial Report',
                ],
                'type' => 'financial',
                'category' => 'finance',
                'date_range' => 'this_year',
                'description' => [
                    'lt' => 'Finansinių rodiklių ir pajamų analizė',
                    'en' => 'Financial metrics and revenue analysis',
                ],
                'is_active' => true,
                'is_public' => false,
            ],
            [
                'name' => [
                    'lt' => 'Rinkodaros kampanijų ataskaita',
                    'en' => 'Marketing Campaigns Report',
                ],
                'type' => 'marketing',
                'category' => 'marketing',
                'date_range' => 'last_90_days',
                'description' => [
                    'lt' => 'Rinkodaros kampanijų efektyvumo analizė',
                    'en' => 'Marketing campaign effectiveness analysis',
                ],
                'is_active' => true,
                'is_public' => true,
                'is_scheduled' => true,
                'schedule_frequency' => 'weekly',
            ],
        ];

        foreach ($reports as $reportData) {
            // Keep translations as JSON for the database
            if (!is_array($reportData['name'])) {
                $reportData['name'] = ['en' => $reportData['name'], 'lt' => $reportData['name']];
            }
            if (isset($reportData['description']) && !is_array($reportData['description'])) {
                $reportData['description'] = ['en' => $reportData['description'], 'lt' => $reportData['description']];
            }

            Report::factory()->create($reportData);
        }

        // Create additional random reports
        Report::factory(10)->create();

        // Create some popular reports
        Report::factory(3)->popular()->create();

        // Create some scheduled reports
        Report::factory(2)->scheduled()->create();
    }
}
