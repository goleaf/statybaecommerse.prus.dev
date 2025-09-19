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
        // Create sample sliders with translations
        $sliders = [
            [
                'title' => 'Sveiki atvykę į Statybae Commerce',
                'description' => 'Atraskite puikius statybos produktus ir paslaugas vienoje vietoje',
                'button_text' => 'Pradėti apsipirkinėti',
                'button_url' => '/products',
                'background_color' => '#f8fafc',
                'text_color' => '#1e293b',
                'sort_order' => 1,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Welcome to Statybae Commerce',
                        'description' => 'Discover great construction products and services in one place',
                        'button_text' => 'Start Shopping',
                    ]
                ]
            ],
            [
                'title' => 'Kokybės garantija',
                'description' => 'Visos prekės patikrintos ir sertifikuotos',
                'button_text' => 'Sužinoti daugiau',
                'button_url' => '/about',
                'background_color' => '#1e293b',
                'text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Quality Guarantee',
                        'description' => 'All products are tested and certified',
                        'button_text' => 'Learn More',
                    ]
                ]
            ],
            [
                'title' => 'Specialūs pasiūlymai',
                'description' => 'Nepraleiskite galimybės sutaupyti',
                'button_text' => 'Žiūrėti akcijas',
                'button_url' => '/sales',
                'background_color' => '#dc2626',
                'text_color' => '#ffffff',
                'sort_order' => 3,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Special Offers',
                        'description' => 'Don\'t miss the opportunity to save',
                        'button_text' => 'View Sales',
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
