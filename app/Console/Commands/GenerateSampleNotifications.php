<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

final class GenerateSampleNotifications extends Command
{
    protected $signature = 'notifications:generate-sample {--count=10 : Number of notifications to generate}';

    protected $description = 'Generate sample notifications for testing';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $notificationService = app(NotificationService::class);

        // Get a random user or create one for testing
        $user = User::inRandomOrder()->first();

        if (! $user) {
            $this->error('No users found. Please create a user first.');

            return 1;
        }

        $this->info("Generating {$count} sample notifications for user: {$user->name}");

        $notificationTypes = [
            'order' => ['created', 'updated', 'shipped', 'delivered', 'payment_received'],
            'product' => ['created', 'updated', 'low_stock', 'out_of_stock', 'back_in_stock'],
            'user' => ['registered', 'profile_updated', 'email_verified'],
            'system' => ['maintenance_started', 'maintenance_completed', 'security_alert'],
            'payment' => ['processed', 'failed', 'refunded'],
            'shipping' => ['label_created', 'picked_up', 'in_transit', 'delivered'],
            'review' => ['submitted', 'approved', 'rejected'],
            'promotion' => ['created', 'started', 'ended'],
            'newsletter' => ['subscribed', 'sent'],
            'support' => ['ticket_created', 'ticket_updated', 'message_received'],
        ];

        for ($i = 0; $i < $count; $i++) {
            $type = array_rand($notificationTypes);
            $action = $notificationTypes[$type][array_rand($notificationTypes[$type])];

            $data = $this->generateSampleData($type, $action);

            match ($type) {
                'order' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'order'),
                'product' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'product'),
                'user' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'user'),
                'system' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'system'),
                'payment' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'payment'),
                'shipping' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'shipping'),
                'review' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'review'),
                'promotion' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'promotion'),
                'newsletter' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'newsletter'),
                'support' => $notificationService->sendToUser($user, $data['title'], $data['message'], 'support'),
                default => $notificationService->sendToUser($user, $data['title'], $data['message'], 'info'),
            };

            // Add some random delay to make timestamps more realistic
            usleep(100000); // 0.1 second
        }

        $this->info("Successfully generated {$count} sample notifications!");

        return 0;
    }

    private function generateSampleData(string $type, string $action): array
    {
        $titles = [
            'order' => [
                'created' => 'Naujas užsakymas sukurtas',
                'updated' => 'Užsakymas atnaujintas',
                'shipped' => 'Užsakymas išsiųstas',
                'delivered' => 'Užsakymas pristatytas',
                'payment_received' => 'Mokėjimas gautas',
            ],
            'product' => [
                'created' => 'Nauja prekė sukurta',
                'updated' => 'Prekė atnaujinta',
                'low_stock' => 'Mažas prekių kiekis',
                'out_of_stock' => 'Prekė išparduota',
                'back_in_stock' => 'Prekė vėl turima',
            ],
            'user' => [
                'registered' => 'Naujas vartotojas užsiregistravo',
                'profile_updated' => 'Profilis atnaujintas',
                'email_verified' => 'El. paštas patvirtintas',
            ],
            'system' => [
                'maintenance_started' => 'Priežiūros darbai pradėti',
                'maintenance_completed' => 'Priežiūros darbai baigti',
                'security_alert' => 'Saugumo įspėjimas',
            ],
            'payment' => [
                'processed' => 'Mokėjimas apdorotas',
                'failed' => 'Mokėjimas nepavyko',
                'refunded' => 'Mokėjimas grąžintas',
            ],
            'shipping' => [
                'label_created' => 'Siuntimo etiketė sukurta',
                'picked_up' => 'Siunta paimta',
                'in_transit' => 'Siunta kelyje',
                'delivered' => 'Siunta pristatyta',
            ],
            'review' => [
                'submitted' => 'Atsiliepimas pateiktas',
                'approved' => 'Atsiliepimas patvirtintas',
                'rejected' => 'Atsiliepimas atmestas',
            ],
            'promotion' => [
                'created' => 'Nauja akcija sukurta',
                'started' => 'Akcija pradėta',
                'ended' => 'Akcija baigta',
            ],
            'newsletter' => [
                'subscribed' => 'Prenumeruota naujienlaiškis',
                'sent' => 'Naujienlaiškis išsiųstas',
            ],
            'support' => [
                'ticket_created' => 'Bilietas sukurta',
                'ticket_updated' => 'Bilietas atnaujintas',
                'message_received' => 'Gautas pranešimas',
            ],
        ];

        $messages = [
            'order' => [
                'created' => 'Jūsų užsakymas #'.rand(1000, 9999).' buvo sėkmingai sukurtas.',
                'updated' => 'Užsakymo #'.rand(1000, 9999).' statusas buvo atnaujintas.',
                'shipped' => 'Jūsų užsakymas #'.rand(1000, 9999).' buvo išsiųstas.',
                'delivered' => 'Jūsų užsakymas #'.rand(1000, 9999).' buvo pristatytas.',
                'payment_received' => 'Mokėjimas už užsakymą #'.rand(1000, 9999).' buvo gautas.',
            ],
            'product' => [
                'created' => 'Nauja prekė "'.$this->getRandomProductName().'" buvo pridėta į katalogą.',
                'updated' => 'Prekės "'.$this->getRandomProductName().'" informacija buvo atnaujinta.',
                'low_stock' => 'Prekės "'.$this->getRandomProductName().'" likutis mažas.',
                'out_of_stock' => 'Prekė "'.$this->getRandomProductName().'" išparduota.',
                'back_in_stock' => 'Prekė "'.$this->getRandomProductName().'" vėl turima.',
            ],
            'user' => [
                'registered' => 'Naujas vartotojas '.$this->getRandomName().' užsiregistravo sistemoje.',
                'profile_updated' => 'Jūsų profilio informacija buvo atnaujinta.',
                'email_verified' => 'Jūsų el. pašto adresas buvo patvirtintas.',
            ],
            'system' => [
                'maintenance_started' => 'Sistemos priežiūros darbai pradėti. Galimi trumpalaikiai sutrikimai.',
                'maintenance_completed' => 'Sistemos priežiūros darbai baigti. Visos funkcijos atkurta.',
                'security_alert' => 'Aptiktas neįprastas veikimas jūsų paskyroje.',
            ],
            'payment' => [
                'processed' => 'Mokėjimas buvo sėkmingai apdorotas.',
                'failed' => 'Mokėjimo apdorojimas nepavyko. Bandykite dar kartą.',
                'refunded' => 'Mokėjimas buvo grąžintas į jūsų sąskaitą.',
            ],
            'shipping' => [
                'label_created' => 'Siuntimo etiketė buvo sukurta.',
                'picked_up' => 'Jūsų siunta buvo paimta iš sandėlio.',
                'in_transit' => 'Jūsų siunta yra kelyje.',
                'delivered' => 'Jūsų siunta buvo pristatyta.',
            ],
            'review' => [
                'submitted' => 'Jūsų atsiliepimas buvo pateiktas.',
                'approved' => 'Jūsų atsiliepimas buvo patvirtintas ir publikuotas.',
                'rejected' => 'Jūsų atsiliepimas nebuvo patvirtintas.',
            ],
            'promotion' => [
                'created' => 'Nauja akcija "'.$this->getRandomPromotionName().'" buvo sukurta.',
                'started' => 'Akcija "'.$this->getRandomPromotionName().'" prasidėjo.',
                'ended' => 'Akcija "'.$this->getRandomPromotionName().'" baigėsi.',
            ],
            'newsletter' => [
                'subscribed' => 'Sėkmingai prenumeravote naujienlaiškį.',
                'sent' => 'Naujienlaiškis buvo išsiųstas.',
            ],
            'support' => [
                'ticket_created' => 'Jūsų palaikymo bilietas buvo sukurtas.',
                'ticket_updated' => 'Jūsų palaikymo bilietas buvo atnaujintas.',
                'message_received' => 'Gautas atsakymas į jūsų palaikymo bilietą.',
            ],
        ];

        return [
            'title' => $titles[$type][$action] ?? 'Pranešimas',
            'message' => $messages[$type][$action] ?? 'Sistema praneša apie naują įvykį.',
        ];
    }

    private function getRandomProductName(): string
    {
        $products = [
            'Statybinės medžiagos',
            'Elektros įranga',
            'Vandentiekio detalės',
            'Šildymo sistema',
            'Vėdinimo įranga',
            'Saugumo sistema',
            'Automatinės durys',
            'LED apšvietimas',
            'Klimato valdymas',
            'Garso sistema',
        ];

        return $products[array_rand($products)];
    }

    private function getRandomName(): string
    {
        $names = [
            'Jonas Jonaitis',
            'Petras Petraitis',
            'Antanas Antanaitis',
            'Vytautas Vytautaitis',
            'Algirdas Algirdaitis',
            'Gediminas Gediminaitis',
            'Mindaugas Mindaugaitis',
            'Kęstutis Kęstutaitis',
        ];

        return $names[array_rand($names)];
    }

    private function getRandomPromotionName(): string
    {
        $promotions = [
            'Žiemos akcija',
            'Naujų metų nuolaida',
            'Statybų sezono pasiūlymas',
            'Ekologinių medžiagų akcija',
            'Profesionalių įrankių nuolaida',
            'Šildymo sistemų akcija',
            'Saugumo sprendimų pasiūlymas',
            'Energijos taupymo akcija',
        ];

        return $promotions[array_rand($promotions)];
    }
}
