<?php declare(strict_types=1);

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
                        'description' => "Don't miss the opportunity to save. Up to 50% discount on selected items!",
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
                'is_active' => false,  // This one is inactive by default
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

            // Create translations
            foreach ($translations as $locale => $translationData) {
                SliderTranslation::create([
                    'slider_id' => $slider->id,
                    'locale' => $locale,
                    ...$translationData
                ]);
            }

            // Add sample images if they exist
            $this->addSampleImages($slider, $sliderData['sort_order']);
        }
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
            $slider->addMediaFromDisk($imagePath, 'public')
                ->toMediaCollection('slider_images');
        } else {
            // Create a placeholder image using a simple colored rectangle
            $this->createPlaceholderImage($slider, $sortOrder);
        }
    }

    private function createPlaceholderImage(Slider $slider, int $sortOrder): void
    {
        $colors = [
            1 => '#3B82F6', // Blue
            2 => '#10B981', // Green  
            3 => '#F59E0B', // Yellow
            4 => '#EF4444', // Red
            5 => '#8B5CF6', // Purple
            6 => '#06B6D4', // Cyan
        ];

        $color = $colors[$sortOrder] ?? '#6B7280';
        
        // Create a simple colored image
        $image = imagecreate(1200, 600);
        $bgColor = $this->hexToRgb($color);
        $backgroundColor = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($image, 0, 0, $backgroundColor);
        
        // Add text
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $font = 5; // Built-in font
        $text = "Slider {$sortOrder}";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = (int) ((1200 - $textWidth) / 2);
        $y = (int) ((600 - $textHeight) / 2);
        imagestring($image, $font, $x, $y, $text, $textColor);
        
        // Save to temporary file
        $tempPath = sys_get_temp_dir() . "/slider-{$sortOrder}.jpg";
        imagejpeg($image, $tempPath, 90);
        imagedestroy($image);
        
        // Add to media library
        $slider->addMedia($tempPath)
            ->usingName("Slider {$sortOrder} Placeholder")
            ->usingFileName("slider-{$sortOrder}.jpg")
            ->toMediaCollection('slider_images');
        
        // Clean up
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
