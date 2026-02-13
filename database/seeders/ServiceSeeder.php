<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;

final class ServiceSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name'        => 'Statybinių medžiagų pristatymas į objektą',
                'description' => 'Pristatome statybines medžiagas tiesiai į objektą visoje Lietuvoje, suderinę laiką su jūsų darbų grafiku.',
                'price'       => 89.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Projektinės sąmatos sudarymas',
                'description' => 'Parengiame detalų darbų ir medžiagų biudžetą, kad galėtumėte tiksliai planuoti statybos kaštus.',
                'price'       => 149.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Pamatų įrengimo darbai',
                'description' => 'Atliekame pamatų paruošimą, armavimą ir betonavimą pagal techninį projektą bei grunto sąlygas.',
                'price'       => 1290.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Mūro darbai iš blokelių',
                'description' => 'Mūrijame laikančias ir pertvarines sienas iš dujų silikato ar keraminių blokelių su kokybės kontrole.',
                'price'       => 980.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Stogo dangos montavimas',
                'description' => 'Montuojame skardinę, bituminę ar čerpių dangą su visais sandarinimo ir apdailos elementais.',
                'price'       => 1450.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Lietaus nuvedimo sistemos montavimas',
                'description' => 'Įrengiame latakų ir lietvamzdžių sistemas, užtikrinančias patikimą vandens surinkimą nuo stogo.',
                'price'       => 420.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Fasado šiltinimas ir dekoratyvinis tinkavimas',
                'description' => 'Atliekame fasado šiltinimą ir galutinę apdailą dekoratyviniu tinku, gerinant pastato energinį efektyvumą.',
                'price'       => 1650.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Langų ir lauko durų montavimas',
                'description' => 'Profesionaliai montuojame langus ir lauko duris su sandarinimo juostomis bei angokraščių sutvarkymu.',
                'price'       => 560.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Vidaus pertvarų iš gipso kartono montavimas',
                'description' => 'Įrengiame gipskartonio pertvaras, nišas ir lubų konstrukcijas pagal interjero planą.',
                'price'       => 630.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Sienų glaistymas ir dažymas',
                'description' => 'Paruošiame sienų paviršius, glaistome, šlifuojame ir dažome pasirinktų spalvų dažais.',
                'price'       => 540.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Plytelių klijavimas',
                'description' => 'Klojame sienų ir grindų plyteles vonios kambariuose, virtuvėse bei komercinėse patalpose.',
                'price'       => 470.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Betoninių grindų įrengimas',
                'description' => 'Atliekame pagrindo paruošimą, armavimą ir betoninių grindų liejimą gyvenamiesiems bei ūkiniams pastatams.',
                'price'       => 790.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Trinkelių klojimas',
                'description' => 'Klojame kiemo trinkeles su pagrindo paruošimu, bortelių montavimu ir nuolydžių suformavimu.',
                'price'       => 860.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Terasos montavimas',
                'description' => 'Projektuojame ir montuojame medines arba kompozitines terasas su tvirtu karkasu ir apsaugine apdaila.',
                'price'       => 1190.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Elektros instaliacijos įrengimas',
                'description' => 'Įrengiame vidaus elektros tinklą, skydelius, apšvietimo taškus ir rozečių grandines pagal projektą.',
                'price'       => 710.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Santechnikos taškų įrengimas',
                'description' => 'Montuojame vandentiekio ir nuotekų taškus virtuvei, vonios kambariui bei pagalbinėms patalpoms.',
                'price'       => 680.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Rekuperacijos ir vėdinimo sistemos montavimas',
                'description' => 'Įrengiame mechaninę vėdinimo sistemą su rekuperacija, ortakiais ir oro srautų balansavimu.',
                'price'       => 920.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Statybinių atliekų išvežimas',
                'description' => 'Organizuojame statybinių atliekų surinkimą, konteinerių pastatymą ir išvežimą į licencijuotas aikšteles.',
                'price'       => 190.00,
                'is_active'   => true,
            ],
            [
                'name'        => 'Avarinių pažeidimų remontas per 24 val.',
                'description' => 'Skubiai atliekame avarinių pažeidimų šalinimą po užliejimų, vėjo ar kitų netikėtų incidentų.',
                'price'       => 260.00,
                'is_active'   => false,
            ],
            [
                'name'        => 'Garantinė priežiūra po darbų',
                'description' => 'Atliekame periodinę darbų patikrą, smulkius pataisymus ir konsultacijas garantiniu laikotarpiu.',
                'price'       => 120.00,
                'is_active'   => true,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['name' => $service['name']],
                [
                    'description' => $service['description'],
                    'price'       => $service['price'],
                    'is_active'   => $service['is_active'],
                ]
            );
        }
    }
}
