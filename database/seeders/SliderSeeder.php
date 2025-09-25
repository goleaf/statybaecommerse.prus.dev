<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\SliderTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

use function collect;

final class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $supportedLocales = $this->getSupportedLocales();
        $sliderDefinitions = $this->getSliderDefinitions();

        $this->resetExistingSliders($sliderDefinitions->pluck('sort_order')->all());

        $sliderDefinitions->each(function (array $definition) use ($supportedLocales): void {
            $translations = $definition['translations'] ?? [];
            unset($definition['translations']);

            $slider = Slider::factory()
                ->state($definition)
                ->create();

            SliderTranslation::factory()
                ->count(count($supportedLocales))
                ->sequence(...$this->buildTranslationStates($supportedLocales, $translations, $definition))
                ->for($slider)
                ->create();

            $this->ensureSampleImages($slider, (int) $definition['sort_order']);
        });
    }

    private function getSupportedLocales(): array
    {
        $supportedLocales = config('app.supported_locales', ['lt', 'en']);

        if (is_string($supportedLocales)) {
            $supportedLocales = array_filter(array_map('trim', explode(',', $supportedLocales)));
        }

        if (! is_array($supportedLocales) || $supportedLocales === []) {
            $supportedLocales = ['lt', 'en'];
        }

        $supportedLocales = array_values(array_unique($supportedLocales));

        if (! in_array('lt', $supportedLocales, true)) {
            array_unshift($supportedLocales, 'lt');
        }

        if (! in_array('en', $supportedLocales, true)) {
            $supportedLocales[] = 'en';
        }

        return array_values(array_unique($supportedLocales));
    }

    private function getSliderDefinitions(): Collection
    {
        return collect([
            [
                'title' => 'Sveiki atvykę į Statybae Commerce',
                'description' => 'Atraskite puikius statybos produktus ir paslaugas vienoje vietoje. Kokybė, patikimumas ir konkurencingi kainos.',
                'button_text' => 'Pradėti apsipirkinėti',
                'button_url' => '/products',
                'background_color' => '#f8fafc',
                'text_color' => '#1e293b',
                'sort_order' => 1,
                'is_active' => true,
                'settings' => [
                    'animation' => 'fade',
                    'duration' => 5000,
                    'autoplay' => true,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Sveiki atvykę į Statybae Commerce',
                        'description' => 'Atraskite puikius statybos produktus ir paslaugas vienoje vietoje. Kokybė, patikimumas ir konkurencingi kainos.',
                        'button_text' => 'Pradėti apsipirkinėti',
                    ],
                    'en' => [
                        'title' => 'Welcome to Statybae Commerce',
                        'description' => 'Discover great construction products and services in one place. Quality, reliability and competitive prices.',
                        'button_text' => 'Start Shopping',
                    ],
                    'ru' => [
                        'title' => 'Добро пожаловать в Statybae Commerce',
                        'description' => 'Откройте лучшие строительные товары и услуги в одном месте. Качество, надежность и конкурентные цены.',
                        'button_text' => 'Начать покупки',
                    ],
                    'de' => [
                        'title' => 'Willkommen bei Statybae Commerce',
                        'description' => 'Entdecken Sie hochwertige Bauprodukte und Dienstleistungen an einem Ort. Qualität, Zuverlässigkeit und faire Preise.',
                        'button_text' => 'Einkauf starten',
                    ],
                ],
            ],
            [
                'title' => 'Kokybės garantija',
                'description' => 'Visos prekės patikrintos ir sertifikuotos. Mes garantuojame aukščiausią kokybę ir ilgalaikį tarnavimą.',
                'button_text' => 'Sužinoti daugiau',
                'button_url' => '/about',
                'background_color' => '#1e293b',
                'text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => true,
                'settings' => [
                    'animation' => 'slide',
                    'duration' => 6000,
                    'autoplay' => true,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Kokybės garantija',
                        'description' => 'Visos prekės patikrintos ir sertifikuotos. Mes garantuojame aukščiausią kokybę ir ilgalaikį tarnavimą.',
                        'button_text' => 'Sužinoti daugiau',
                    ],
                    'en' => [
                        'title' => 'Quality Guarantee',
                        'description' => 'All products are tested and certified. We guarantee the highest quality and long-term service.',
                        'button_text' => 'Learn More',
                    ],
                    'ru' => [
                        'title' => 'Гарантия качества',
                        'description' => 'Все товары проверены и сертифицированы. Мы гарантируем высочайшее качество и долгий срок службы.',
                        'button_text' => 'Узнать больше',
                    ],
                    'de' => [
                        'title' => 'Qualitätsgarantie',
                        'description' => 'Alle Produkte sind geprüft und zertifiziert. Wir garantieren höchste Qualität und lange Lebensdauer.',
                        'button_text' => 'Mehr erfahren',
                    ],
                ],
            ],
            [
                'title' => 'Specialūs pasiūlymai',
                'description' => 'Nepraleiskite galimybės sutaupyti. Iki 50% nuolaida pasirinktoms prekėms!',
                'button_text' => 'Žiūrėti akcijas',
                'button_url' => '/sales',
                'background_color' => '#dc2626',
                'text_color' => '#ffffff',
                'sort_order' => 3,
                'is_active' => true,
                'settings' => [
                    'animation' => 'zoom',
                    'duration' => 4000,
                    'autoplay' => true,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Specialūs pasiūlymai',
                        'description' => 'Nepraleiskite galimybės sutaupyti. Iki 50% nuolaida pasirinktoms prekėms!',
                        'button_text' => 'Žiūrėti akcijas',
                    ],
                    'en' => [
                        'title' => 'Special Offers',
                        'description' => "Don't miss the opportunity to save. Up to 50% discount on selected items!",
                        'button_text' => 'View Sales',
                    ],
                    'ru' => [
                        'title' => 'Специальные предложения',
                        'description' => 'Не упустите шанс сэкономить. Скидки до 50% на выбранные товары!',
                        'button_text' => 'Посмотреть акции',
                    ],
                    'de' => [
                        'title' => 'Sonderangebote',
                        'description' => 'Verpassen Sie nicht die Chance zu sparen. Bis zu 50 % Rabatt auf ausgewählte Artikel!',
                        'button_text' => 'Angebote ansehen',
                    ],
                ],
            ],
            [
                'title' => 'Profesionalūs įrankiai',
                'description' => 'Aukščiausios kokybės statybos įrankiai profesionalams ir savininkams. Platus asortimentas, greitas pristatymas.',
                'button_text' => 'Peržiūrėti įrankius',
                'button_url' => '/products?category=tools',
                'background_color' => '#059669',
                'text_color' => '#ffffff',
                'sort_order' => 4,
                'is_active' => true,
                'settings' => [
                    'animation' => 'fade',
                    'duration' => 5500,
                    'autoplay' => true,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Profesionalūs įrankiai',
                        'description' => 'Aukščiausios kokybės statybos įrankiai profesionalams ir savininkams. Platus asortimentas, greitas pristatymas.',
                        'button_text' => 'Peržiūrėti įrankius',
                    ],
                    'en' => [
                        'title' => 'Professional Tools',
                        'description' => 'Highest quality construction tools for professionals and homeowners. Wide range, fast delivery.',
                        'button_text' => 'Browse Tools',
                    ],
                    'ru' => [
                        'title' => 'Профессиональные инструменты',
                        'description' => 'Строительные инструменты высокого качества для профессионалов и домовладельцев. Широкий ассортимент и быстрая доставка.',
                        'button_text' => 'Смотреть инструменты',
                    ],
                    'de' => [
                        'title' => 'Professionelle Werkzeuge',
                        'description' => 'Hochwertige Bauwerkzeuge für Profis und Hausbesitzer. Breites Sortiment, schnelle Lieferung.',
                        'button_text' => 'Werkzeuge ansehen',
                    ],
                ],
            ],
            [
                'title' => 'Nemokamas pristatymas',
                'description' => 'Nemokamas pristatymas visoje Lietuvoje užsakymams virš 100€. Greitas ir saugus pristatymas.',
                'button_text' => 'Sužinoti daugiau',
                'button_url' => '/shipping',
                'background_color' => '#7c3aed',
                'text_color' => '#ffffff',
                'sort_order' => 5,
                'is_active' => true,
                'settings' => [
                    'animation' => 'slide',
                    'duration' => 7000,
                    'autoplay' => true,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Nemokamas pristatymas',
                        'description' => 'Nemokamas pristatymas visoje Lietuvoje užsakymams virš 100€.',
                        'button_text' => 'Sužinoti daugiau',
                    ],
                    'en' => [
                        'title' => 'Free Delivery',
                        'description' => 'Free delivery throughout Lithuania for orders over €100. Fast and secure delivery.',
                        'button_text' => 'Learn More',
                    ],
                    'ru' => [
                        'title' => 'Бесплатная доставка',
                        'description' => 'Бесплатная доставка по всей Литве для заказов свыше 100 €. Быстрая и надежная доставка.',
                        'button_text' => 'Узнать больше',
                    ],
                    'de' => [
                        'title' => 'Kostenlose Lieferung',
                        'description' => 'Kostenlose Lieferung in ganz Litauen für Bestellungen über 100 €. Schnelle und sichere Zustellung.',
                        'button_text' => 'Mehr erfahren',
                    ],
                ],
            ],
            [
                'title' => 'Nauji produktai',
                'description' => 'Atraskite naujausius statybos produktus ir technologijas. Būkite pirmieji, kurie išbandys naujoves.',
                'button_text' => 'Peržiūrėti naujoves',
                'button_url' => '/products?new=true',
                'background_color' => '#ea580c',
                'text_color' => '#ffffff',
                'sort_order' => 6,
                'is_active' => false,
                'settings' => [
                    'animation' => 'zoom',
                    'duration' => 5000,
                    'autoplay' => false,
                ],
                'translations' => [
                    'lt' => [
                        'title' => 'Nauji produktai',
                        'description' => 'Atraskite naujausius statybos produktus ir technologijas. Būkite pirmieji, kurie išbandys naujoves.',
                        'button_text' => 'Peržiūrėti naujoves',
                    ],
                    'en' => [
                        'title' => 'New Products',
                        'description' => 'Discover the latest construction products and technologies. Be the first to try new innovations.',
                        'button_text' => 'Browse New Items',
                    ],
                    'ru' => [
                        'title' => 'Новые продукты',
                        'description' => 'Откройте самые последние строительные товары и технологии. Будьте первыми, кто попробует новинки.',
                        'button_text' => 'Посмотреть новинки',
                    ],
                    'de' => [
                        'title' => 'Neue Produkte',
                        'description' => 'Entdecken Sie die neuesten Bauprodukte und Technologien. Seien Sie die Ersten, die Innovationen ausprobieren.',
                        'button_text' => 'Neuheiten ansehen',
                    ],
                ],
            ],
        ]);
    }

    private function buildTranslationStates(array $locales, array $translations, array $sliderAttributes): array
    {
        return array_map(static function (string $locale) use ($translations, $sliderAttributes): array {
            $translation = $translations[$locale] ?? [
                'title' => $sliderAttributes['title'],
                'description' => $sliderAttributes['description'],
                'button_text' => $sliderAttributes['button_text'],
            ];

            return array_merge(['locale' => $locale], $translation);
        }, $locales);
    }

    private function resetExistingSliders(array $sortOrders): void
    {
        if ($sortOrders === []) {
            return;
        }

        Slider::with('translations')
            ->whereIn('sort_order', $sortOrders)
            ->get()
            ->each(function (Slider $slider): void {
                $slider->translations()->delete();
                $slider->clearMediaCollection('slider_images');
                $slider->clearMediaCollection('slider_backgrounds');
                $slider->delete();
            });
    }

    private function ensureSampleImages(Slider $slider, int $sortOrder): void
    {
        if ($slider->hasImage()) {
            return;
        }

        $this->addSampleImages($slider, $sortOrder);
    }

    private function addSampleImages(Slider $slider, int $sortOrder): void
    {
        $imagePaths = [
            1 => 'sliders/sample-1.jpg',
            2 => 'sliders/sample-2.jpg',
            3 => 'sliders/sample-3.jpg',
            4 => 'sliders/sample-4.jpg',
            5 => 'sliders/sample-5.jpg',
            6 => 'sliders/sample-6.jpg',
        ];

        $imagePath = $imagePaths[$sortOrder] ?? null;

        if ($imagePath && file_exists(public_path($imagePath))) {
            $slider
                ->addMediaFromDisk($imagePath, 'public')
                ->toMediaCollection('slider_images');

            return;
        }

        $this->createPlaceholderImage($slider, $sortOrder);
    }

    private function createPlaceholderImage(Slider $slider, int $sortOrder): void
    {
        $colors = [
            1 => '#3B82F6',
            2 => '#10B981',
            3 => '#F59E0B',
            4 => '#EF4444',
            5 => '#8B5CF6',
            6 => '#06B6D4',
        ];

        $color = $colors[$sortOrder] ?? '#6B7280';

        $image = imagecreate(1200, 600);
        $bgColor = $this->hexToRgb($color);
        $backgroundColor = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($image, 0, 0, $backgroundColor);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        $font = 5;
        $text = "Slider {$sortOrder}";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = (int) ((1200 - $textWidth) / 2);
        $y = (int) ((600 - $textHeight) / 2);
        imagestring($image, $font, $x, $y, $text, $textColor);

        $tempPath = sys_get_temp_dir()."/slider-{$sortOrder}.jpg";
        imagejpeg($image, $tempPath, 90);
        imagedestroy($image);

        $slider
            ->addMedia($tempPath)
            ->usingName("Slider {$sortOrder} Placeholder")
            ->usingFileName("slider-{$sortOrder}.jpg")
            ->toMediaCollection('slider_images');

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
