<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Database\Factories\DocumentFactory;
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

        // Coordinate a handful of dedicated document groups so we exercise
        // every template type with realistic relationships instead of letting
        // the factory spin up detached records.
        $this->seedInvoices($templates, $users, $orders);
        $this->seedReceipts($templates, $users, $orders);
        $this->seedDrafts($templates, $users, $orders);
        $this->seedContracts($templates, $users, $orders);
        $this->seedReports($templates, $users, $orders);
    }

    /**
     * @param Collection<int, DocumentTemplate> $templates
     * @param Collection<int, User>             $users
     * @param Collection<int, Order>            $orders
     */
    private function seedInvoices(Collection $templates, Collection $users, Collection $orders): void
    {
        // Ensure invoices consistently reuse the seeded invoice template and
        // existing orders/users so relationship assertions remain stable.
        $this->seedDocumentsForType(
            $templates,
            $users,
            $orders,
            'invoice',
            5,
            static fn (\Database\Factories\DocumentFactory $factory): \Database\Factories\DocumentFactory => $factory->invoice()
        );
    }

    /**
     * @param Collection<int, DocumentTemplate> $templates
     * @param Collection<int, User>             $users
     * @param Collection<int, Order>            $orders
     */
    private function seedReceipts(Collection $templates, Collection $users, Collection $orders): void
    {
        // Mirror the invoice behaviour for receipts so we attach them to
        // deterministic templates and the prepared order/user fixtures.
        $this->seedDocumentsForType(
            $templates,
            $users,
            $orders,
            'receipt',
            5,
            static fn (\Database\Factories\DocumentFactory $factory): \Database\Factories\DocumentFactory => $factory->receipt()
        );
    }

    /**
     * @param Collection<int, DocumentTemplate> $templates
     * @param Collection<int, User>             $users
     * @param Collection<int, Order>            $orders
     */
    private function seedDrafts(Collection $templates, Collection $users, Collection $orders): void
    {
        // Draft documents intentionally shuffle templates to simulate
        // exploratory authoring while still keeping links valid.
        /** @var DocumentTemplate $randomTemplate */
        $randomTemplate = $templates->random();

        $this->seedDocumentsForType(
            $templates,
            $users,
            $orders,
            $randomTemplate->type,
            3,
            static fn (\Database\Factories\DocumentFactory $factory): \Database\Factories\DocumentFactory => $factory->draft()
        );
    }

    /**
     * @param Collection<int, DocumentTemplate> $templates
     * @param Collection<int, User>             $users
     * @param Collection<int, Order>            $orders
     */
    private function seedContracts(Collection $templates, Collection $users, Collection $orders): void
    {
        // Contracts lean on the dedicated template when available and fall
        // back to any remaining template otherwise.
        $this->seedDocumentsForType(
            $templates,
            $users,
            $orders,
            'contract',
            3,
            static fn (\Database\Factories\DocumentFactory $factory): \Database\Factories\DocumentFactory => $factory->contract()
        );
    }

    /**
     * @param Collection<int, DocumentTemplate> $templates
     * @param Collection<int, User>             $users
     * @param Collection<int, Order>            $orders
     */
    private function seedReports(Collection $templates, Collection $users, Collection $orders): void
    {
        // Reports gain a bespoke state so we can validate generated PDFs with
        // predictable naming.
        $this->seedDocumentsForType(
            $templates,
            $users,
            $orders,
            'report',
            3,
            static fn (\Database\Factories\DocumentFactory $factory) => $factory->state(
                fn (): array => [
                    'title'  => 'Report ' . fake()->unique()->numerify('#RPT-###'),
                    'status' => 'generated',
                    'format' => 'pdf',
                ],
            )
        );
    }

    /**
     * Prepare a factory batch that pins every document to existing models so
     * tests asserting relationship integrity never encounter stray factories.
     *
     * @param Collection<int, DocumentTemplate>          $templates
     * @param Collection<int, User>                      $users
     * @param Collection<int, Order>                     $orders
     * @param callable(DocumentFactory): DocumentFactory $factoryConfigurator
     */
    private function seedDocumentsForType(
        Collection $templates,
        Collection $users,
        Collection $orders,
        string $templateType,
        int $count,
        callable $factoryConfigurator
    ): void {
        /** @var DocumentTemplate $template */
        $template = $templates->firstWhere('type', $templateType) ?? $templates->random();

        $eligibleUsers = $users
            ->sortByDesc('id')
            ->take(max(1, min(3, $users->count())))
            ->values();

        foreach (range(1, $count) as $_) {
            // Select fresh associations for every iteration so the seed mirrors
            // the organic mix of documents in production data.
            /** @var Order $order */
            $order = $orders->random();
            // Prioritise the most recently created users so tests that
            // provision temporary accounts (like the feature assertion suite)
            // can reliably observe the seeded relations.
            /** @var User $creator */
            $creator = $eligibleUsers->random();

            /** @var DocumentFactory $factory */
            $factory = $factoryConfigurator(Document::factory());

            $factory
                ->for($template, 'template')
                ->for($order, 'documentable')
                ->for($creator, 'creator')
                ->for($creator, 'updater')
                ->state([
                    // Normalise the stored type to the chosen template so
                    // analytics and filtering stay aligned.
                    'type' => $templateType,
                    // Persist explicit attribution to avoid the factory spinning
                    // up auxiliary users that the feature tests are not tracking.
                    'created_by' => $creator->getKey(),
                    'updated_by' => $creator->getKey(),
                ])
                ->create();
        }
    }
}
