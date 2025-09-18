<?php

declare(strict_types=1);

namespace Database\Seeders\Data;

final class HouseBuilderCollections
{
    public static function collections(): array
    {
        return [
            'foundation-and-structure' => [
                'sort_order' => 1,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Foundation & Structure',
                'categories' => ['building-materials', 'lumber-wood', 'fasteners'],
                'translations' => [
                    'en' => [
                        'name' => 'Foundation & Structure',
                        'description' => 'Structural systems and concrete essentials engineered for reliable house building.',
                        'keywords' => ['foundation', 'structure', 'concrete'],
                    ],
                    'lt' => [
                        'name' => 'Pamatų ir konstrukcijų sprendimai',
                        'description' => 'Patikimi betoniniai ir konstrukciniai sprendimai gyvenamųjų namų statybai.',
                        'keywords' => ['pamatai', 'konstrukcijos', 'statyba'],
                    ],
                    'ru' => [
                        'name' => 'Фундаменты и конструкции',
                        'description' => 'Комплексные решения для оснований и несущих элементов жилых домов.',
                        'keywords' => ['фундамент', 'конструкция', 'строительство'],
                    ],
                    'de' => [
                        'name' => 'Fundament- & Struktur-Lösungen',
                        'description' => 'Strukturlösungen und Betonzubehör für den zuverlässigen Hausbau.',
                        'keywords' => ['fundament', 'struktur', 'beton'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'heavy-duty-concrete-mixer',
                        'sku' => 'HB-CM-001',
                        'price' => 1499.00,
                        'stock' => 18,
                        'weight' => 180,
                        'brand' => 'HausBuild Essentials',
                        'categories' => ['building-materials'],
                        'image_text' => 'Concrete Mixer',
                        'translations' => [
                            'en' => [
                                'name' => 'Heavy-Duty Concrete Mixer',
                                'short_description' => 'Mobile mixer that keeps consistent batches on every pour.',
                                'description' => '<p>Built for demanding job sites, this mixer delivers continuous, even concrete for foundations and slabs.</p><p>Includes reinforced drum, adjustable legs, and transport wheels for rapid deployment.</p>',
                            ],
                            'lt' => [
                                'name' => 'Galingas betono maišytuvas',
                                'short_description' => 'Mobilus maišytuvas, užtikrinantis vienodą betono paruošimą kiekvienam liejimui.',
                                'description' => '<p>Sukurtas intensyviems darbams, šis maišytuvas nuolat tiekia tolygų betoną pamatams ir perdangoms.</p><p>Komplekte sustiprintas būgnas, reguliuojamos kojos ir transportavimo ratai greitam darbui.</p>',
                            ],
                            'ru' => [
                                'name' => 'Мощный бетономешатель',
                                'short_description' => 'Мобильная бетономешалка для равномерных замесов прямо на объекте.',
                                'description' => '<p>Рассчитан на тяжелые условия стройплощадки и обеспечивает стабильный бетон для фундаментов и плит.</p><p>В комплекте усиленный барабан, регулируемые опоры и транспортировочные колеса.</p>',
                            ],
                            'de' => [
                                'name' => 'Leistungsstarker Betonmischer',
                                'short_description' => 'Mobiler Mischer für gleichmäßige Betonchargen auf jeder Baustelle.',
                                'description' => '<p>Für anspruchsvolle Baustellen entwickelt und liefert kontinuierlich homogenen Beton für Fundamente und Bodenplatten.</p><p>Mit verstärkter Trommel, verstellbaren Stützen und Transportrollen für schnellen Einsatz.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'structural-anchor-bolt-set',
                        'sku' => 'HB-SA-002',
                        'price' => 189.90,
                        'stock' => 120,
                        'brand' => 'HausBuild Essentials',
                        'categories' => ['fasteners'],
                        'image_text' => 'Anchor Bolts',
                        'translations' => [
                            'en' => [
                                'name' => 'Structural Anchor Bolt Set',
                                'short_description' => 'Galvanized anchors that secure sill plates and beams.',
                                'description' => '<p>High-tensile anchors tested for residential foundations and load-bearing beams.</p><p>Includes washers and nuts for immediate installation.</p>',
                            ],
                            'lt' => [
                                'name' => 'Konstrukciniai ankeriniai varžtai',
                                'short_description' => 'Cinkuoti inkarai, skirti sijų ir rostverko tvirtinimui.',
                                'description' => '<p>Aukštos atsparos inkarai, išbandyti gyvenamųjų namų pamatams ir laikančiosioms sijoms.</p><p>Komplekte poveržlės ir veržlės greitam montavimui.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект конструкционных анкеров',
                                'short_description' => 'Оцинкованные анкеры для крепления обвязочных и несущих балок.',
                                'description' => '<p>Высокопрочные элементы, испытанные для фундаментов и несущих конструкций жилых домов.</p><p>В комплект входят шайбы и гайки для немедленного монтажа.</p>',
                            ],
                            'de' => [
                                'name' => 'Strukturelles Ankerbolzen-Set',
                                'short_description' => 'Verzinkte Anker zur sicheren Befestigung von Schwellen und Trägern.',
                                'description' => '<p>Zugfeste Anker, geprüft für Wohnhausfundamente und tragende Balken.</p><p>Lieferung inklusive Scheiben und Muttern für den sofortigen Einbau.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'reinforced-steel-mesh-panels',
                        'sku' => 'HB-RM-003',
                        'price' => 329.50,
                        'stock' => 60,
                        'brand' => 'HausBuild Essentials',
                        'categories' => ['building-materials'],
                        'image_text' => 'Steel Mesh',
                        'translations' => [
                            'en' => [
                                'name' => 'Reinforced Steel Mesh Panels',
                                'short_description' => 'Prefabricated panels that strengthen slabs and walls.',
                                'description' => '<p>Hot-dip galvanized mesh provides structural reinforcement for floor slabs and retaining walls.</p><p>Cut-to-size markers simplify on-site trimming.</p>',
                            ],
                            'lt' => [
                                'name' => 'Armavimo tinklo plokštės',
                                'short_description' => 'Paruoštos plokštės, sutvirtinančios perdangas ir sienas.',
                                'description' => '<p>Karštai cinkuotas tinklas suteikia papildomą tvirtumą perdangoms ir atraminėms sienoms.</p><p>Pažymėti pjovimo taškai palengvina apdirbimą objekte.</p>',
                            ],
                            'ru' => [
                                'name' => 'Усиленные стальные сетчатые панели',
                                'short_description' => 'Готовые панели для армирования плит и стен.',
                                'description' => '<p>Горячеоцинкованная сетка повышает прочность полов и подпорных стен.</p><p>Маркировка резов облегчает подгонку на площадке.</p>',
                            ],
                            'de' => [
                                'name' => 'Verstärkte Stahlgitter-Paneele',
                                'short_description' => 'Vorfabrizierte Paneele zur Verstärkung von Platten und Wänden.',
                                'description' => '<p>Feuerverzinktes Gitter sorgt für zusätzliche Stabilität bei Decken und Stützwänden.</p><p>Schnittmarkierungen erleichtern die Anpassung direkt auf der Baustelle.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'foundation-waterproofing-membrane',
                        'sku' => 'HB-FW-004',
                        'price' => 219.00,
                        'stock' => 80,
                        'brand' => 'HausBuild Essentials',
                        'categories' => ['building-materials'],
                        'image_text' => 'Waterproofing Membrane',
                        'translations' => [
                            'en' => [
                                'name' => 'Foundation Waterproofing Membrane',
                                'short_description' => 'Self-adhesive membrane shielding basements from moisture.',
                                'description' => '<p>Multi-layer membrane with high elasticity protects foundations against groundwater and frost.</p><p>Roll format enables fast, overlap-free installation.</p>',
                            ],
                            'lt' => [
                                'name' => 'Pamatų hidroizoliacinė membrana',
                                'short_description' => 'Savaime prikimbanti membrana rūsių drėgmei sulaikyti.',
                                'description' => '<p>Daugiasluoksnė membrana su dideliu elastingumu saugo pamatus nuo gruntinio vandens ir šalčio.</p><p>Ritinio formatas leidžia greitai montuoti be persidengimų.</p>',
                            ],
                            'ru' => [
                                'name' => 'Гидроизоляционная мембрана для фундамента',
                                'short_description' => 'Самоклеящаяся мембрана, защищающая подвал от влаги.',
                                'description' => '<p>Многослойная эластичная мембрана предохраняет фундамент от грунтовых вод и промерзания.</p><p>Поставляется в рулонах для быстрого монтажа без нахлёстов.</p>',
                            ],
                            'de' => [
                                'name' => 'Abdichtungsmembran für Fundamente',
                                'short_description' => 'Selbstklebende Membran zum Schutz von Kellern vor Feuchtigkeit.',
                                'description' => '<p>Mehrlagige, hoch elastische Membran schützt Fundamente vor Grundwasser und Frost.</p><p>Rollenware ermöglicht eine schnelle, überlappungsfreie Verlegung.</p>',
                            ],
                        ],
                    ],
                ],
            ],
            'roofing-and-weatherproofing' => [
                'sort_order' => 2,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Roofing & Weatherproofing',
                'categories' => ['building-materials', 'safety-equipment', 'power-tools'],
                'translations' => [
                    'en' => [
                        'name' => 'Roofing & Weatherproofing',
                        'description' => 'Weather-tight roofing systems that protect every layer of the build.',
                        'keywords' => ['roofing', 'weatherproof', 'membrane'],
                    ],
                    'lt' => [
                        'name' => 'Stogų ir sandarumo sprendimai',
                        'description' => 'Visapusiškos stogo sistemos, apsaugančios kiekvieną namo sluoksnį.',
                        'keywords' => ['stogas', 'sandarinimas', 'hidroizoliacija'],
                    ],
                    'ru' => [
                        'name' => 'Кровля и защита от погодных условий',
                        'description' => 'Комплексные кровельные решения, защищающие дом по всей высоте.',
                        'keywords' => ['кровля', 'гидроизоляция', 'защита'],
                    ],
                    'de' => [
                        'name' => 'Dach & Witterungsschutz',
                        'description' => 'Dachsysteme mit rundum-Schutz für jedes Bauprojekt.',
                        'keywords' => ['dach', 'abdichtung', 'wetterfest'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'pneumatic-roofing-nailer-set',
                        'sku' => 'HB-RF-101',
                        'price' => 495.00,
                        'stock' => 45,
                        'brand' => 'RoofGuard Systems',
                        'categories' => ['power-tools'],
                        'image_text' => 'Roofing Nailer',
                        'translations' => [
                            'en' => [
                                'name' => 'Pneumatic Roofing Nailer Set',
                                'short_description' => 'High-speed nailer with hoses and safety case for shingle teams.',
                                'description' => '<p>Lightweight magnesium body reduces fatigue while maintaining fastening precision.</p><p>Includes depth adjustment, anti-jam magazine, and 15m air hose.</p>',
                            ],
                            'lt' => [
                                'name' => 'Pneumatinis stogo kalimo komplektas',
                                'short_description' => 'Greitaeigė viniakalė su žarnomis ir apsauginiu lagaminu stogdengiams.',
                                'description' => '<p>Lengvas magnio korpusas mažina nuovargį ir užtikrina tvirtinimo tikslumą.</p><p>Komplekte gylio reguliavimas, prieš užsikirtimą apsaugotas magazinas ir 15 m oro žarna.</p>',
                            ],
                            'ru' => [
                                'name' => 'Пневматический кровельный нейлер',
                                'short_description' => 'Высокоскоростной нейлер с шлангами и кейсом для бригад кровельщиков.',
                                'description' => '<p>Лёгкий магниевый корпус снижает усталость, сохраняя точность крепежа.</p><p>Оснащён регулировкой глубины, антизаклинивающим магазином и 15-метровым шлангом.</p>',
                            ],
                            'de' => [
                                'name' => 'Pneumatisches Dachnagler-Set',
                                'short_description' => 'Schnell arbeitender Nagler mit Schläuchen und Schutzkoffer für Dachdeckerteams.',
                                'description' => '<p>Das leichte Magnesiumgehäuse reduziert Ermüdung und hält die Befestigungsgenauigkeit hoch.</p><p>Mit Tiefeneinstellung, Anti-Stau-Magazin und 15 m Luftschlauch.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'premium-roof-underlayment-membrane',
                        'sku' => 'HB-RF-102',
                        'price' => 279.00,
                        'stock' => 70,
                        'brand' => 'RoofGuard Systems',
                        'categories' => ['building-materials'],
                        'image_text' => 'Roof Underlayment',
                        'translations' => [
                            'en' => [
                                'name' => 'Premium Roof Underlayment Membrane',
                                'short_description' => 'Breathable underlayment keeping decks dry and ventilated.',
                                'description' => '<p>Triple-layer membrane resists UV, wind uplift, and heavy rain during installation.</p><p>Self-sealing laps shorten installation time on steep pitches.</p>',
                            ],
                            'lt' => [
                                'name' => 'Premium klasės stogo paklotas',
                                'short_description' => 'Kvėpuojantis paklotas, išlaikantis sausą ir ventiliuojamą pagrindą.',
                                'description' => '<p>Trijų sluoksnių membrana atspari UV spinduliams, vėjo pakėlimui ir intensyviam lietui montavimo metu.</p><p>Savaime sandėjančios juostos pagreitina montavimą ant stačių šlaitų.</p>',
                            ],
                            'ru' => [
                                'name' => 'Премиальная подкровельная мембрана',
                                'short_description' => 'Паропроницаемый слой, сохраняющий настил сухим и вентилируемым.',
                                'description' => '<p>Трёхслойная мембрана устойчива к ультрафиолету, порывам ветра и сильным осадкам.</p><p>Самозакрывающиеся нахлёсты ускоряют монтаж на крутых скатах.</p>',
                            ],
                            'de' => [
                                'name' => 'Premium-Dachunterspannbahn',
                                'short_description' => 'Atmungsaktive Unterspannbahn für trockene, belüftete Dachböden.',
                                'description' => '<p>Dreilagige Membran, die UV-Strahlung, Windsog und Starkregen während der Montage widersteht.</p><p>Selbstklebende Überlappungen verkürzen die Verlegezeit auf steilen Dächern.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'modular-gutter-protection-kit',
                        'sku' => 'HB-RF-103',
                        'price' => 189.00,
                        'stock' => 90,
                        'brand' => 'RoofGuard Systems',
                        'categories' => ['building-materials'],
                        'image_text' => 'Gutter Protection',
                        'translations' => [
                            'en' => [
                                'name' => 'Modular Gutter Protection Kit',
                                'short_description' => 'Aluminium guard system preventing clogs and ice dams.',
                                'description' => '<p>Snap-fit modules shield gutters from debris while allowing optimal water flow.</p><p>Compatible with standard residential gutter profiles.</p>',
                            ],
                            'lt' => [
                                'name' => 'Modulinė latakų apsaugos sistema',
                                'short_description' => 'Aliumininė apsauga nuo užsikimšimų ir ledo sankaupų.',
                                'description' => '<p>Lengvai jungiami moduliai apsaugo latakus nuo šiukšlių ir užtikrina tinkamą vandens nubėgimą.</p><p>Suderinama su standartiniais gyvenamųjų namų latakais.</p>',
                            ],
                            'ru' => [
                                'name' => 'Модульная защита желобов',
                                'short_description' => 'Алюминиевые решётки предотвращают засоры и наледь.',
                                'description' => '<p>Модульная система защищает желоба от мусора и обеспечивает свободный сток воды.</p><p>Совместима со стандартными профилями желобов жилых домов.</p>',
                            ],
                            'de' => [
                                'name' => 'Modulares Rinnenschutz-System',
                                'short_description' => 'Aluminium-System gegen Verstopfungen und Eisdämme.',
                                'description' => '<p>Die steckbaren Module schützen die Dachrinne vor Schmutz und sorgen für optimalen Wasserabfluss.</p><p>Kompatibel mit gängigen Dachrinnenprofilen im Wohnbau.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'roof-edge-safety-harness-set',
                        'sku' => 'HB-RF-104',
                        'price' => 259.00,
                        'stock' => 55,
                        'brand' => 'RoofGuard Systems',
                        'categories' => ['safety-equipment'],
                        'image_text' => 'Roof Safety Harness',
                        'translations' => [
                            'en' => [
                                'name' => 'Roof Edge Safety Harness Set',
                                'short_description' => 'Fall-arrest kit with adjustable lifeline and anchor strap.',
                                'description' => '<p>Certified harness keeps crews secure while installing shingles and panels.</p><p>Weather-resistant materials withstand year-round exposure.</p>',
                            ],
                            'lt' => [
                                'name' => 'Stogo krašto saugos diržų komplektas',
                                'short_description' => 'Apsaugos sistema su reguliuojama saugos virve ir inkaro juosta.',
                                'description' => '<p>Sertifikuotas diržas užtikrina komandų saugumą montuojant čerpes ar paneles.</p><p>Atsparios oro sąlygoms medžiagos tinkamos naudoti visus metus.</p>',
                            ],
                            'ru' => [
                                'name' => 'Страховочная система для краёв кровли',
                                'short_description' => 'Комплект с регулируемой страховочной верёвкой и анкерной лентой.',
                                'description' => '<p>Сертифицированная система обеспечивает безопасность монтажников при устройстве кровли.</p><p>Материалы устойчивы к погодным условиям круглый год.</p>',
                            ],
                            'de' => [
                                'name' => 'Sicherheitsgurtsystem für Dachkanten',
                                'short_description' => 'Absturzsicherung mit verstellbarem Seil und Anschlagband.',
                                'description' => '<p>Zertifizierter Gurt hält Teams beim Verlegen von Schindeln und Paneelen sicher.</p><p>Witterungsbeständige Materialien erlauben den Einsatz das ganze Jahr.</p>',
                            ],
                        ],
                    ],
                ],
            ],
            'interior-finishing-essentials' => [
                'sort_order' => 3,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Interior Finishing Essentials',
                'categories' => ['hand-tools', 'building-materials'],
                'translations' => [
                    'en' => [
                        'name' => 'Interior Finishing Essentials',
                        'description' => 'Finishing tools and materials that deliver premium interior details.',
                        'keywords' => ['interior', 'finishing', 'renovation'],
                    ],
                    'lt' => [
                        'name' => 'Vidaus apdailos rinkiniai',
                        'description' => 'Apdailos įrankiai ir medžiagos aukštos kokybės interjerui.',
                        'keywords' => ['interjeras', 'apdaila', 'renovacija'],
                    ],
                    'ru' => [
                        'name' => 'Комплекты для внутренней отделки',
                        'description' => 'Инструменты и материалы для премиальной отделки интерьера.',
                        'keywords' => ['интерьер', 'отделка', 'ремонт'],
                    ],
                    'de' => [
                        'name' => 'Innenausbau Essentials',
                        'description' => 'Werkzeuge und Materialien für hochwertige Innenraumdetails.',
                        'keywords' => ['innenausbau', 'finish', 'renovierung'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'drywall-finishing-tool-set',
                        'sku' => 'HB-IF-201',
                        'price' => 229.00,
                        'stock' => 65,
                        'brand' => 'FinishLine Studio',
                        'categories' => ['hand-tools'],
                        'image_text' => 'Drywall Tool Set',
                        'translations' => [
                            'en' => [
                                'name' => 'Drywall Finishing Tool Set',
                                'short_description' => 'Complete knife and hawk set for smooth gypsum joints.',
                                'description' => '<p>Stainless steel blades and ergonomic grips speed up taping and mud work.</p><p>Includes corner applicator and adjustable sanding pole.</p>',
                            ],
                            'lt' => [
                                'name' => 'Gipso apdailos įrankių komplektas',
                                'short_description' => 'Pilnas glaistymo ir glaistiklių komplektas lygiam sujungimui.',
                                'description' => '<p>Nerūdijančio plieno mentelės ir ergonomiškos rankenos pagreitina glaistymo darbus.</p><p>Komplekte kampų aplikatorius ir reguliuojamas šlifavimo kotas.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект инструментов для отделки гипсокартона',
                                'short_description' => 'Полный набор шпателей и сокола для ровных стыков.',
                                'description' => '<p>Нержавеющие лезвия и удобные рукоятки ускоряют шпаклёвку и затирку.</p><p>В комплект входит угловой аппликатор и регулируемая шлифовальная штанга.</p>',
                            ],
                            'de' => [
                                'name' => 'Werkzeugset für Gipskarton-Finish',
                                'short_description' => 'Kompletter Kellen- und Gleiterset für glatte Fugen.',
                                'description' => '<p>Edelstahlklingen und ergonomische Griffe beschleunigen Spachtelarbeiten.</p><p>Mit Eckapplikator und verstellbarer Schleifstange.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'paint-prep-and-coating-bundle',
                        'sku' => 'HB-IF-202',
                        'price' => 189.50,
                        'stock' => 85,
                        'brand' => 'FinishLine Studio',
                        'categories' => ['building-materials'],
                        'image_text' => 'Paint Prep Bundle',
                        'translations' => [
                            'en' => [
                                'name' => 'Paint Prep and Coating Bundle',
                                'short_description' => 'Primer, low-VOC paint, and roller kit for interiors.',
                                'description' => '<p>Two-coat system ensures durable colour coverage with minimal odour.</p><p>Supplied with microfiber rollers, tray liners, and edging brush.</p>',
                            ],
                            'lt' => [
                                'name' => 'Dažymo paruošimo ir dangos rinkinys',
                                'short_description' => 'Gruntas, maža VOC dažai ir volelių komplektas interjerui.',
                                'description' => '<p>Dviejų sluoksnių sistema užtikrina ilgalaikę spalvą ir minimalius kvapus.</p><p>Komplekte mikropluošto voleliai, padėklų įklotai ir briauninis teptukas.</p>',
                            ],
                            'ru' => [
                                'name' => 'Набор для подготовки и покраски стен',
                                'short_description' => 'Грунт, краска с низким содержанием ЛОС и комплект валиков.',
                                'description' => '<p>Двухслойная система обеспечивает стойкое покрытие с минимальным запахом.</p><p>В комплект входят микрофибровые валики, вкладыши для ванночки и кисть для кромок.</p>',
                            ],
                            'de' => [
                                'name' => 'Anstrich- und Beschichtungspaket',
                                'short_description' => 'Grundierung, VOC-arme Farbe und Rollerset für Innenräume.',
                                'description' => '<p>Zweischichtiges System sorgt für langlebige Farbabdeckung bei geringem Geruch.</p><p>Mit Mikrofaserrollen, Wanneneinsätzen und Kantenpinsel.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'laminate-flooring-starter-pack',
                        'sku' => 'HB-IF-203',
                        'price' => 329.00,
                        'stock' => 52,
                        'brand' => 'FinishLine Studio',
                        'categories' => ['building-materials'],
                        'image_text' => 'Laminate Flooring Pack',
                        'translations' => [
                            'en' => [
                                'name' => 'Laminate Flooring Starter Pack',
                                'short_description' => 'Click-lock planks with underlayment and trims included.',
                                'description' => '<p>Scratch-resistant surface is ideal for living areas and hallways.</p><p>Comes with moisture barrier underlayment and colour-matched thresholds.</p>',
                            ],
                            'lt' => [
                                'name' => 'Laminato grindų startinis rinkinys',
                                'short_description' => 'Su spynelės sistema, paklotu ir suderinamais kampais.',
                                'description' => '<p>Atspari įbrėžimams danga puikiai tinka svetainėms ir koridoriams.</p><p>Pridedamas drėgmę stabdantis paklotas ir priderinti slenksčiai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Стартовый комплект ламината',
                                'short_description' => 'Планки с замком, подложка и доборы в одном наборе.',
                                'description' => '<p>Износостойкое покрытие подходит für гостиных и коридоров.</p><p>В наборе влагозащитная подложка и подобранные пороги.</p>',
                            ],
                            'de' => [
                                'name' => 'Laminat-Starterpaket',
                                'short_description' => 'Click-System Dielen mit Unterlage und Leisten.',
                                'description' => '<p>Kratzfeste Oberfläche eignet sich für Wohnbereiche und Flure.</p><p>Enthält Feuchtigkeitsschutzunterlage und farblich passende Übergangsleisten.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'acoustic-ceiling-panel-kit',
                        'sku' => 'HB-IF-204',
                        'price' => 259.00,
                        'stock' => 48,
                        'brand' => 'FinishLine Studio',
                        'categories' => ['building-materials'],
                        'image_text' => 'Acoustic Panels',
                        'translations' => [
                            'en' => [
                                'name' => 'Acoustic Ceiling Panel Kit',
                                'short_description' => 'Noise-reducing panels for studios, offices, and living rooms.',
                                'description' => '<p>Mineral fiber core absorbs echo for improved comfort and privacy.</p><p>Grid suspension hardware and edge trims included.</p>',
                            ],
                            'lt' => [
                                'name' => 'Akustinių lubų plokščių komplektas',
                                'short_description' => 'Triukšmą mažinančios plokštės studijoms, biurams ir svetainėms.',
                                'description' => '<p>Mineralinio pluošto šerdis sugeria aidą ir gerina komfortą.</p><p>Komplekte karkaso pakabinimo detalės ir kraštų juostos.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект акустических потолочных панелей',
                                'short_description' => 'Звукопоглощающие панели для студий, офисов и гостиных.',
                                'description' => '<p>Минеральное волокно поглощает эхо и повышает уровень комфорта.</p><p>В комплекте подвесная система и декоративные профили.</p>',
                            ],
                            'de' => [
                                'name' => 'Akustik-Deckenpaneel-Set',
                                'short_description' => 'Schallreduzierende Paneele für Studios, Büros und Wohnräume.',
                                'description' => '<p>Mineralfaser-Kern absorbiert Echo und erhöht den Komfort.</p><p>Mit Unterkonstruktion und Kantenprofilen im Lieferumfang.</p>',
                            ],
                        ],
                    ],
                ],
            ],
            'electrical-and-lighting' => [
                'sort_order' => 4,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Electrical & Lighting',
                'categories' => ['electrical', 'hand-tools'],
                'translations' => [
                    'en' => [
                        'name' => 'Electrical & Lighting',
                        'description' => 'Safe power distribution and lighting solutions tailored for home building.',
                        'keywords' => ['electrical', 'lighting', 'power'],
                    ],
                    'lt' => [
                        'name' => 'Elektros ir apšvietimo sprendimai',
                        'description' => 'Saugūs elektros paskirstymo ir apšvietimo sprendimai namų statybai.',
                        'keywords' => ['elektra', 'apšvietimas', 'energija'],
                    ],
                    'ru' => [
                        'name' => 'Электрика и освещение',
                        'description' => 'Надёжные решения для электроснабжения и освещения жилых домов.',
                        'keywords' => ['электрика', 'освещение', 'энергия'],
                    ],
                    'de' => [
                        'name' => 'Elektro & Beleuchtung',
                        'description' => 'Sichere Energieverteilung und Beleuchtungssysteme für den Hausbau.',
                        'keywords' => ['elektro', 'beleuchtung', 'energie'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'smart-distribution-panel',
                        'sku' => 'HB-EL-301',
                        'price' => 749.00,
                        'stock' => 28,
                        'brand' => 'VoltSafe Pro',
                        'categories' => ['electrical'],
                        'image_text' => 'Smart Panel',
                        'translations' => [
                            'en' => [
                                'name' => 'Smart Distribution Panel',
                                'short_description' => 'Wi-Fi enabled load center with energy monitoring.',
                                'description' => '<p>Modular breakers and app connectivity simplify load balancing and reporting.</p><p>Includes surge protection and labelled circuits for faster inspections.</p>',
                            ],
                            'lt' => [
                                'name' => 'Išmanus elektros skydas',
                                'short_description' => 'Wi-Fi valdomas skirstomasis skydas su energijos stebėsena.',
                                'description' => '<p>Moduliniai automatiniai jungikliai ir programėlės ryšys palengvina apkrovos balansavimą.</p><p>Komplekte viršįtampių apsauga ir pažymėtos grandinės greitesnei patikrai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Умный распределительный щит',
                                'short_description' => 'Центр нагрузки с Wi-Fi и мониторингом энергопотребления.',
                                'description' => '<p>Модульные автоматы и подключение к приложению упрощают балансировку нагрузок.</p><p>Встроенная защита от перенапряжений и маркировка контуров ускоряют проверки.</p>',
                            ],
                            'de' => [
                                'name' => 'Smartes Verteilerschrank-System',
                                'short_description' => 'WLAN-fähige Verteilung mit Energiemonitoring.',
                                'description' => '<p>Modulare Sicherungen und App-Anbindung erleichtern Lastmanagement und Berichte.</p><p>Inklusive Überspannungsschutz und beschrifteten Stromkreisen.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'led-construction-light-pack',
                        'sku' => 'HB-EL-302',
                        'price' => 219.00,
                        'stock' => 75,
                        'brand' => 'VoltSafe Pro',
                        'categories' => ['electrical'],
                        'image_text' => 'LED Work Lights',
                        'translations' => [
                            'en' => [
                                'name' => 'LED Construction Light Pack',
                                'short_description' => 'Job-site tripod lights with high-lumen output.',
                                'description' => '<p>Adjustable LED heads flood work areas with daylight-balanced illumination.</p><p>Foldable tripod and daisy-chain outlets included.</p>',
                            ],
                            'lt' => [
                                'name' => 'LED statybinių šviestuvų komplektas',
                                'short_description' => 'Statybiniai trikojo šviestuvai su dideliu šviesos srautu.',
                                'description' => '<p>Reguliuojamos LED galvos apšviečia darbo zonas dienos šviesos spalva.</p><p>Sulankstomas trikojis ir jungimo lizdai komplekte.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект строительных LED-прожекторов',
                                'short_description' => 'Штативные светильники с высоким световым потоком.',
                                'description' => '<p>Регулируемые LED-головки освещают рабочие зоны светом, близким к дневному.</p><p>В комплекте складной штатив и сквозные розетки.</p>',
                            ],
                            'de' => [
                                'name' => 'LED-Baustellenlicht-Set',
                                'short_description' => 'Stativleuchten mit hoher Lichtleistung für Baustellen.',
                                'description' => '<p>Verstellbare LED-Köpfe fluten den Arbeitsbereich mit tageslichtähnlicher Beleuchtung.</p><p>Mit klappbarem Stativ und durchschleifbaren Steckdosen.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'professional-wiring-toolkit',
                        'sku' => 'HB-EL-303',
                        'price' => 159.00,
                        'stock' => 90,
                        'brand' => 'VoltSafe Pro',
                        'categories' => ['hand-tools'],
                        'image_text' => 'Wiring Toolkit',
                        'translations' => [
                            'en' => [
                                'name' => 'Professional Wiring Toolkit',
                                'short_description' => 'Crimpers, testers, and cutters for residential circuits.',
                                'description' => '<p>Comprehensive toolkit with insulated handles and calibrated jaws for precision.</p><p>Includes non-contact voltage tester and cable stripping guides.</p>',
                            ],
                            'lt' => [
                                'name' => 'Profesionalus instaliacijos įrankių rinkinys',
                                'short_description' => 'Spaustuvai, testeriai ir replikliai gyvenamųjų namų instaliacijai.',
                                'description' => '<p>Išsamus rinkinys su izoliuotomis rankenomis ir kalibruotais žandikauliais tiksliai darbui.</p><p>Komplekte bekontaktis įtampos testeris ir kabelių nuėmimo šablonai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Профессиональный набор для электромонтажа',
                                'short_description' => 'Пресс-клещи, тестеры и кусачки для бытовых цепей.',
                                'description' => '<p>Полный комплект с изолированными рукоятками и калиброванными губками для точной работы.</p><p>Включает бесконтактный указатель напряжения и шаблоны для снятия изоляции.</p>',
                            ],
                            'de' => [
                                'name' => 'Professionelles Verdrahtungsset',
                                'short_description' => 'Crimpzangen, Tester und Schneider für Hausinstallationen.',
                                'description' => '<p>Umfassendes Set mit isolierten Griffen und kalibrierten Backen für präzises Arbeiten.</p><p>Enthält berührungslosen Spannungsprüfer und Kabelabisolierhilfen.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'surface-mounted-socket-kit',
                        'sku' => 'HB-EL-304',
                        'price' => 98.00,
                        'stock' => 140,
                        'brand' => 'VoltSafe Pro',
                        'categories' => ['electrical'],
                        'image_text' => 'Socket Kit',
                        'translations' => [
                            'en' => [
                                'name' => 'Surface-Mounted Socket Kit',
                                'short_description' => 'IP44 rated sockets for garages and utility rooms.',
                                'description' => '<p>Pre-wired sockets speed up installation and maintain moisture protection.</p><p>Includes matching faceplates and cable glands.</p>',
                            ],
                            'lt' => [
                                'name' => 'Paviršinių lizdų komplektas',
                                'short_description' => 'IP44 lizdai garažams ir pagalbinėms patalpoms.',
                                'description' => '<p>Preliminariai sujungti lizdai pagreitina montavimą ir išlaiko sandarumą.</p><p>Pridedamos suderintos apdailos ir kabelių įvorės.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект накладных розеток',
                                'short_description' => 'Розетки класса IP44 для гаражей и подсобных помещений.',
                                'description' => '<p>Предварительно собранные розетки ускоряют монтаж и сохраняют влагозащиту.</p><p>В комплекте лицевые панели и кабельные вводы.</p>',
                            ],
                            'de' => [
                                'name' => 'Aufputz-Steckdosen-Set',
                                'short_description' => 'IP44-Steckdosen für Garagen und Hauswirtschaftsräume.',
                                'description' => '<p>Vorkonfektionierte Steckdosen beschleunigen die Montage und erhalten den Feuchtigkeitsschutz.</p><p>Mit passenden Abdeckungen und Kabelverschraubungen.</p>',
                            ],
                        ],
                    ],
                ],
            ],
            'plumbing-and-hvac' => [
                'sort_order' => 5,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Plumbing & HVAC',
                'categories' => ['plumbing'],
                'translations' => [
                    'en' => [
                        'name' => 'Plumbing & HVAC',
                        'description' => 'Heating, water, and ventilation kits sized specifically for builders.',
                        'keywords' => ['plumbing', 'hvac', 'heating'],
                    ],
                    'lt' => [
                        'name' => 'Santechnikos ir HVAC sprendimai',
                        'description' => 'Šildymo, vandentiekio ir vėdinimo komplektai rangovams.',
                        'keywords' => ['santechnika', 'šildymas', 'ventiliacija'],
                    ],
                    'ru' => [
                        'name' => 'Сантехника и HVAC',
                        'description' => 'Комплекты отопления, водоснабжения и вентиляции для подрядчиков.',
                        'keywords' => ['сантехника', 'отопление', 'вентиляция'],
                    ],
                    'de' => [
                        'name' => 'Sanitär & Haustechnik',
                        'description' => 'Installationssets für Heizung, Wasser und Lüftung im Hausbau.',
                        'keywords' => ['sanitär', 'heizung', 'lüftung'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'pex-plumbing-installation-system',
                        'sku' => 'HB-PH-401',
                        'price' => 499.00,
                        'stock' => 58,
                        'brand' => 'FlowMaster Solutions',
                        'categories' => ['plumbing'],
                        'image_text' => 'PEX System',
                        'translations' => [
                            'en' => [
                                'name' => 'PEX Plumbing Installation System',
                                'short_description' => 'Colour-coded tubing with manifolds and crimp fittings.',
                                'description' => '<p>Flexible PEX lines reduce installation time and resist freezing in new builds.</p><p>Kit includes distribution manifold, shut-off valves, and crimping rings.</p>',
                            ],
                            'lt' => [
                                'name' => 'PEX santechnikos įrengimo sistema',
                                'short_description' => 'Spalvoti vamzdžiai su kolektoriais ir užspaudžiamomis jungtimis.',
                                'description' => '<p>Lankstūs PEX vamzdžiai mažina montavimo laiką ir atsparūs užšalimui.</p><p>Komplekte skirstymo kolektorius, uždarymo vožtuvai ir užspaudimo žiedai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Система монтажа PEX-труб',
                                'short_description' => 'Цветные трубы с коллекторами и обжимными фитингами.',
                                'description' => '<p>Гибкие PEX-линии сокращают время монтажа и устойчивы к замерзанию.</p><p>В комплекте распределительный коллектор, запорные клапаны и обжимные кольца.</p>',
                            ],
                            'de' => [
                                'name' => 'PEX-Installationssystem',
                                'short_description' => 'Farbige Schläuche mit Verteilern und Pressfittings.',
                                'description' => '<p>Flexible PEX-Leitungen verkürzen die Montagezeit und sind frostresistent.</p><p>Lieferumfang: Verteiler, Absperrventile und Pressringe.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'tankless-water-heater-kit',
                        'sku' => 'HB-PH-402',
                        'price' => 899.00,
                        'stock' => 34,
                        'brand' => 'FlowMaster Solutions',
                        'categories' => ['plumbing'],
                        'image_text' => 'Tankless Heater',
                        'translations' => [
                            'en' => [
                                'name' => 'Tankless Water Heater Kit',
                                'short_description' => 'On-demand hot water system with venting accessories.',
                                'description' => '<p>Condensing technology provides high efficiency for family homes.</p><p>Includes mounting bracket, condensate drain, and isolation valves.</p>',
                            ],
                            'lt' => [
                                'name' => 'Momentinis vandens šildytuvas',
                                'short_description' => 'Vandens šildymo sistema su išmetimo priedais.',
                                'description' => '<p>Kondensacinė technologija užtikrina aukštą efektyvumą vienbučiams namams.</p><p>Komplekte montavimo laikiklis, kondensato nuvedimas ir izoliavimo vožtuvai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект проточного водонагревателя',
                                'short_description' => 'Система мгновенного нагрева с комплектом дымохода.',
                                'description' => '<p>Конденсационная технология обеспечивает высокую эффективность для семейных домов.</p><p>В комплект входят монтажная планка, отвод конденсата и запорные краны.</p>',
                            ],
                            'de' => [
                                'name' => 'Durchlauferhitzer-Komplettset',
                                'short_description' => 'Warmwassersystem mit Abgassystem für den Sofortbetrieb.',
                                'description' => '<p>Kondensationstechnologie bietet hohe Effizienz für Familienhäuser.</p><p>Im Set: Montagekonsole, Kondensatablauf und Absperrventile.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'hvac-ductwork-insulation-pack',
                        'sku' => 'HB-PH-403',
                        'price' => 259.90,
                        'stock' => 68,
                        'brand' => 'FlowMaster Solutions',
                        'categories' => ['plumbing'],
                        'image_text' => 'HVAC Insulation',
                        'translations' => [
                            'en' => [
                                'name' => 'HVAC Ductwork Insulation Pack',
                                'short_description' => 'Pre-cut insulation for supply and return trunks.',
                                'description' => '<p>Foil-faced insulation controls condensation and energy loss.</p><p>Pressure-sensitive tape and hangers included.</p>',
                            ],
                            'lt' => [
                                'name' => 'HVAC ortakių izoliacijos paketas',
                                'short_description' => 'Iškirsta izoliacija tiekimo ir grąžinimo kanalams.',
                                'description' => '<p>Folijuota izoliacija kontroliuoja kondensaciją ir energijos nuostolius.</p><p>Komplekte lipni juosta ir tvirtinimo pakabos.</p>',
                            ],
                            'ru' => [
                                'name' => 'Набор изоляции для воздуховодов HVAC',
                                'short_description' => 'Готовые листы для приточных и обратных каналов.',
                                'description' => '<p>Фольгированная изоляция снижает конденсацию и теплопотери.</p><p>В комплекте клейкая лента und подвесы.</p>',
                            ],
                            'de' => [
                                'name' => 'Isolationspaket für HVAC-Luftkanäle',
                                'short_description' => 'Vorgeschnittene Dämmung für Zu- und Abluftkanäle.',
                                'description' => '<p>Folienkaschierte Dämmung verhindert Kondensation und Energieverluste.</p><p>Mit druckempfindlichem Klebeband und Aufhängern.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'bathroom-fixture-mounting-set',
                        'sku' => 'HB-PH-404',
                        'price' => 179.00,
                        'stock' => 72,
                        'brand' => 'FlowMaster Solutions',
                        'categories' => ['plumbing'],
                        'image_text' => 'Fixture Mounting Set',
                        'translations' => [
                            'en' => [
                                'name' => 'Bathroom Fixture Mounting Set',
                                'short_description' => 'Supports faucets, basins, and wall-hung toilets.',
                                'description' => '<p>Adjustable brackets and waterproof gaskets simplify fixture alignment.</p><p>Rated for modern concealed systems.</p>',
                            ],
                            'lt' => [
                                'name' => 'Vonios įrangos montavimo komplektas',
                                'short_description' => 'Skirtas maišytuvams, praustuvams ir pakabinamiems WC.',
                                'description' => '<p>Reguliuojami laikikliai ir sandarūs tarpikliai palengvina montavimą.</p><p>Sertifikuota naudoti su paslėptomis sistemomis.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект для монтажа сантехники',
                                'short_description' => 'Поддержка смесителей, раковин и подвесных унитазов.',
                                'description' => '<p>Регулируемые кронштейны и гидроизоляционные прокладки упрощают позиционирование.</p><p>Совместимо с современными скрытыми системами.</p>',
                            ],
                            'de' => [
                                'name' => 'Montageset für Sanitärkeramik',
                                'short_description' => 'Trägt Armaturen, Waschbecken und Wand-WCs.',
                                'description' => '<p>Verstellbare Halterungen und Dichtungen erleichtern die Ausrichtung der Sanitärkeramik.</p><p>Geeignet für moderne Unterputzsysteme.</p>',
                            ],
                        ],
                    ],
                ],
            ],
            'outdoor-and-landscaping' => [
                'sort_order' => 6,
                'display_type' => 'grid',
                'is_automatic' => false,
                'image_text' => 'Outdoor & Landscaping',
                'categories' => ['power-tools', 'hand-tools'],
                'translations' => [
                    'en' => [
                        'name' => 'Outdoor & Landscaping',
                        'description' => 'Tools and surfaces that finish the exterior living experience.',
                        'keywords' => ['outdoor', 'landscaping', 'gardening'],
                    ],
                    'lt' => [
                        'name' => 'Lauko ir aplinkos sprendimai',
                        'description' => 'Įrankiai ir dangos, užbaigiančios išorinę erdvę.',
                        'keywords' => ['laukas', 'aplinka', 'landšaftas'],
                    ],
                    'ru' => [
                        'name' => 'Ландшафт и внешние зоны',
                        'description' => 'Инструменты und покрытия для завершения внешнего пространства.',
                        'keywords' => ['ландшафт', 'сад', 'наружные работы'],
                    ],
                    'de' => [
                        'name' => 'Außen- & Landschaftsbau',
                        'description' => 'Werkzeuge und Materialien für den perfekten Außenbereich.',
                        'keywords' => ['außenbereich', 'landschaft', 'garten'],
                    ],
                ],
                'products' => [
                    [
                        'slug' => 'landscaping-prep-tool-bundle',
                        'sku' => 'HB-OL-501',
                        'price' => 249.00,
                        'stock' => 88,
                        'brand' => 'Terrascape Tools',
                        'categories' => ['hand-tools'],
                        'image_text' => 'Landscaping Tool Bundle',
                        'translations' => [
                            'en' => [
                                'name' => 'Landscaping Prep Tool Bundle',
                                'short_description' => 'Rakes, levels, and compactors for site preparation.',
                                'description' => '<p>Heavy-duty tools align soil grades and prepare patios or pathways.</p><p>Includes aluminum landscape rake, plate compactor, and finishing broom.</p>',
                            ],
                            'lt' => [
                                'name' => 'Landšafto paruošimo įrankių rinkinys',
                                'short_description' => 'Grėbliai, gulsčiukai ir tankintuvai aikštelės paruošimui.',
                                'description' => '<p>Tvirti įrankiai sulygina gruntą ir paruošia terasas ar takus.</p><p>Komplekte aliumininis grėblys, plokščias tankintuvas ir apdailos šluota.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект инструментов для подготовки ландшафта',
                                'short_description' => 'Грабли, уровни и трамбовки для подготовки площадки.',
                                'description' => '<p>Прочные инструменты выравнивают грунт и готовят площадки для террас и дорожек.</p><p>В наборе алюминиевые грабли, виброплита и финишная щётка.</p>',
                            ],
                            'de' => [
                                'name' => 'Werkzeugpaket für Landschaftsvorbereitung',
                                'short_description' => 'Rechen, Nivelliergeräte und Verdichter zur Platzvorbereitung.',
                                'description' => '<p>Robuste Werkzeuge nivellieren den Boden und bereiten Terrassen oder Wege vor.</p><p>Enthält Aluminium-Rechen, Rüttelplatte und Finish-Besen.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'composite-decking-board-pack',
                        'sku' => 'HB-OL-502',
                        'price' => 569.00,
                        'stock' => 40,
                        'brand' => 'Terrascape Tools',
                        'categories' => ['building-materials'],
                        'image_text' => 'Composite Decking',
                        'translations' => [
                            'en' => [
                                'name' => 'Composite Decking Board Pack',
                                'short_description' => 'Weather-resistant boards with hidden fasteners.',
                                'description' => '<p>Composite decking resists fading and splintering for long-term outdoor use.</p><p>Hidden fastener system creates a seamless deck surface.</p>',
                            ],
                            'lt' => [
                                'name' => 'Kompozitinių terasos lentų paketas',
                                'short_description' => 'Oro sąlygoms atsparios lentos su paslėptais tvirtinimais.',
                                'description' => '<p>Kompozitinės lentos neblunka ir neskyla, puikiai tinka lauko erdvėms.</p><p>Paslėpta tvirtinimo sistema sukuria vientisą terasos paviršių.</p>',
                            ],
                            'ru' => [
                                'name' => 'Комплект композитных террасных досок',
                                'short_description' => 'Устойчивые к погоде доски с скрытым крепежом.',
                                'description' => '<p>Композит не выцветает и не растрескивается, подходит для долгосрочного использования на улице.</p><p>Скрытая система крепления обеспечивает ровную поверхность террасы.</p>',
                            ],
                            'de' => [
                                'name' => 'Komposit-Terrassendielenpaket',
                                'short_description' => 'Wetterbeständige Dielen mit verdeckten Befestigungen.',
                                'description' => '<p>Kompositdielen sind farb- und splitterbeständig – ideal für Außenbereiche.</p><p>Verdecktes Befestigungssystem ergibt eine nahtlose Terrassenfläche.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'smart-irrigation-controller-set',
                        'sku' => 'HB-OL-503',
                        'price' => 199.00,
                        'stock' => 64,
                        'brand' => 'Terrascape Tools',
                        'categories' => ['power-tools'],
                        'image_text' => 'Irrigation Controller',
                        'translations' => [
                            'en' => [
                                'name' => 'Smart Irrigation Controller Set',
                                'short_description' => 'Weather-based watering schedules with app control.',
                                'description' => '<p>Wi-Fi controller adjusts irrigation to rainfall and temperature in real time.</p><p>Package includes moisture sensors and zone valves.</p>',
                            ],
                            'lt' => [
                                'name' => 'Išmanus laistymo valdiklio komplektas',
                                'short_description' => 'Laistymo grafikai pagal oro sąlygas ir programėlės valdymą.',
                                'description' => '<p>Wi-Fi valdiklis realiu laiku pritaiko laistymą prie kritulių ir temperatūros.</p><p>Komplekte drėgmės jutikliai ir zoniniai vožtuvai.</p>',
                            ],
                            'ru' => [
                                'name' => 'Умный контроллер полива',
                                'short_description' => 'Графики полива по погоде с управлением через приложение.',
                                'description' => '<p>Wi-Fi контроллер корректирует полив с учётом дождя и температуры.</p><p>В комплект входят датчики влажности и зонные клапаны.</p>',
                            ],
                            'de' => [
                                'name' => 'Smartes Bewässerungssteuerungs-Set',
                                'short_description' => 'Wetterabhängige Bewässerung mit App-Steuerung.',
                                'description' => '<p>Der WLAN-Controller passt die Bewässerung in Echtzeit an Regen und Temperatur an.</p><p>Lieferumfang: Feuchtigkeitssensoren und Zonenventile.</p>',
                            ],
                        ],
                    ],
                    [
                        'slug' => 'stone-paver-foundation-system',
                        'sku' => 'HB-OL-504',
                        'price' => 329.00,
                        'stock' => 58,
                        'brand' => 'Terrascape Tools',
                        'categories' => ['building-materials'],
                        'image_text' => 'Stone Paver System',
                        'translations' => [
                            'en' => [
                                'name' => 'Stone Paver Foundation System',
                                'short_description' => 'Base panels and edging for patios and walkways.',
                                'description' => '<p>Interlocking base stabilizes pavers and promotes drainage under outdoor surfaces.</p><p>Pack includes edge restraints and polymeric joint sand.</p>',
                            ],
                            'lt' => [
                                'name' => 'Akmens trinkelių pagrindo sistema',
                                'short_description' => 'Pagrindo plokštės ir bortai terasoms bei takams.',
                                'description' => '<p>Susijungiantis pagrindas stabilizuoja trinkeles ir pagerina drenažą.</p><p>Komplekte borteliai ir polimerinis siūlių smėlis.</p>',
                            ],
                            'ru' => [
                                'name' => 'Система основания для каменных плиток',
                                'short_description' => 'Основание и бордюры для террас и дорожек.',
                                'description' => '<p>Стыкующиеся панели стабилизируют плитку и улучшают дренаж.</p><p>В комплект входят бордюры и полимерный песок для швов.</p>',
                            ],
                            'de' => [
                                'name' => 'Fundamentsystem für Steinpflaster',
                                'short_description' => 'Grundplatten und Randprofile для Terrassen und Wege.',
                                'description' => '<p>Verzahnte Basis stabilisiert Pflastersteine und verbessert den Wasserabfluss.</p><p>Mit Randbegrenzungen und polymerem Fugensand.</p>',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
