<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\SliderTranslation;
use Illuminate\Database\Seeder;

final class SliderSeeder extends Seeder
{
    public function run(): void
    {
        // Create comprehensive sliders with translations
        $sliders = [
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
                    'en' => [
                        'title' => 'Welcome to Statybae Commerce',
                        'description' => 'Discover great construction products and services in one place. Quality, reliability and competitive prices.',
                        'button_text' => 'Start Shopping',
                    ]
                ]
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
                    'en' => [
                        'title' => 'Quality Guarantee',
                        'description' => 'All products are tested and certified. We guarantee the highest quality and long-term service.',
                        'button_text' => 'Learn More',
                    ]
                ]
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
                    'en' => [
                        'title' => 'Special Offers',
                        'description' => 'Don\'t miss the opportunity to save. Up to 50% discount on selected items!',
                        'button_text' => 'View Sales',
                    ]
                ]
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
                    'en' => [
                        'title' => 'Professional Tools',
                        'description' => 'Highest quality construction tools for professionals and homeowners. Wide range, fast delivery.',
                        'button_text' => 'Browse Tools',
                    ]
                ]
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
                    'en' => [
                        'title' => 'Free Delivery',
                        'description' => 'Free delivery throughout Lithuania for orders over €100. Fast and secure delivery.',
                        'button_text' => 'Learn More',
                    ]
                ]
            ],
            [
                'title' => 'Nauji produktai',
                'description' => 'Atraskite naujausius statybos produktus ir technologijas. Būkite pirmieji, kurie išbandys naujoves.',
                'button_text' => 'Peržiūrėti naujoves',
                'button_url' => '/products?new=true',
                'background_color' => '#ea580c',
                'text_color' => '#ffffff',
                'sort_order' => 6,
                'is_active' => false, // This one is inactive by default
                'settings' => [
                    'animation' => 'zoom',
                    'duration' => 5000,
                    'autoplay' => false,
                ],
                'translations' => [
                    'en' => [
                        'title' => 'New Products',
                        'description' => 'Discover the latest construction products and technologies. Be the first to try new innovations.',
                        'button_text' => 'Browse New Items',
                    ]
                ]
            ]
        ];

        foreach ($sliders as $sliderData) {
            $translations = $sliderData['translations'];
            unset($sliderData['translations']);

            $slider = Slider::create($sliderData);

            foreach ($translations as $locale => $translationData) {
                SliderTranslation::create([
                    'slider_id' => $slider->id,
                    'locale' => $locale,
                    ...$translationData
                ]);
            }
        }
    }
}
