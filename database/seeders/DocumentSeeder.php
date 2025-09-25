<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $templates = DocumentTemplate::query()->get();
        $users = User::query()->get();
        $orders = Order::query()->get();

        if ($templates->isEmpty() || $users->isEmpty() || $orders->isEmpty()) {
            return;
        }

        $this->seedInvoices($templates, $users, $orders);
        $this->seedReceipts($templates, $users, $orders);
        $this->seedDrafts($templates, $users, $orders);
        $this->seedContracts($templates, $users, $orders);
        $this->seedReports($templates, $users, $orders);
    }

    private function seedInvoices(Collection $templates, Collection $users, Collection $orders): void
    {
        Document::factory()
            ->count(5)
            ->invoice()
            ->for($templates->firstWhere('type', 'invoice') ?? $templates->random(), 'template')
            ->for($orders->random(), 'documentable')
            ->for($users->random(), 'creator')
            ->create();
    }

    private function seedReceipts(Collection $templates, Collection $users, Collection $orders): void
    {
        Document::factory()
            ->count(5)
            ->receipt()
            ->for($templates->firstWhere('type', 'receipt') ?? $templates->random(), 'template')
            ->for($orders->random(), 'documentable')
            ->for($users->random(), 'creator')
            ->create();
    }

    private function seedDrafts(Collection $templates, Collection $users, Collection $orders): void
    {
        Document::factory()
            ->count(3)
            ->draft()
            ->for($templates->random(), 'template')
            ->for($orders->random(), 'documentable')
            ->for($users->random(), 'creator')
            ->create();
    }

    private function seedContracts(Collection $templates, Collection $users, Collection $orders): void
    {
        Document::factory()
            ->count(3)
            ->contract()
            ->for($templates->firstWhere('type', 'contract') ?? $templates->random(), 'template')
            ->for($orders->random(), 'documentable')
            ->for($users->random(), 'creator')
            ->create();
    }

    private function seedReports(Collection $templates, Collection $users, Collection $orders): void
    {
        Document::factory()
            ->count(3)
            ->state([
                'title' => 'Report '.fake()->unique()->numerify('#RPT-###'),
                'status' => 'generated',
                'format' => 'pdf',
            ])
            ->for($templates->firstWhere('type', 'report') ?? $templates->random(), 'template')
            ->for($orders->random(), 'documentable')
            ->for($users->random(), 'creator')
            ->create();
    }
}
