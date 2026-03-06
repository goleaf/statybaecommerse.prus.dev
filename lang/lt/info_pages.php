<?php

declare(strict_types=1);

return [
    'shared' => [
        'need_help_description' => 'Jei reikia patikslinti užsakymo eigą, pristatymą ar prekių parinkimą, susisiekite su mūsų komanda. Padėsime pasirinkti aiškiausią sprendimą jūsų projektui.',
        'need_help_title' => 'Reikia pagalbos?',
        'on_this_page' => 'Šiame puslapyje',
        'quick_actions' => 'Greiti veiksmai',
        'related_pages' => 'Susiję puslapiai',
    ],
    'pages' => [
        'faq' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'DUK (dažniausiai užduodami klausimai)',
            'description' => 'Trumpi ir aiškūs atsakymai apie pirkimą, pristatymą, apmokėjimą, grąžinimą ir pagalbą po užsakymo.',
            'summary' => 'Šį puslapį parengėme pagal didžiųjų statybos prekių e. parduotuvių pagalbos centrų logiką: pirmiausia pateikiame užsakymo eigą, tada apmokėjimą, pristatymą ir veiksmus po pirkimo. Taip greičiau rasite aktualų atsakymą, nesvarbu, ar užsakymą dar tik planuojate, ar jau laukiate siuntos.',
            'highlights' => [
                [
                    'title' => 'Prieš pirkimą',
                    'description' => 'Paaiškiname, kaip pasirinkti prekes, patikrinti jų prieinamumą ir suplanuoti visą užsakymą.',
                ],
                [
                    'title' => 'Pirkimo metu',
                    'description' => 'Vienoje vietoje rasite informaciją apie apmokėjimą, pristatymo būdus ir užsakymo patvirtinimą.',
                ],
                [
                    'title' => 'Po pirkimo',
                    'description' => 'Aptariame grąžinimą, garantinius atvejus ir kaip greičiausiai susisiekti su pagalbos komanda.',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Kaip sužinoti, ar prekė yra sandėlyje?',
                    'answer' => 'Prekės kortelėje rodome aktualų prieinamumą. Jei reikia didesnio kiekio projektui, susisiekite su mumis ir patikslinsime tiekimo terminą bei rezervavimo galimybes.',
                ],
                [
                    'question' => 'Kada užsakymas laikomas patvirtintu?',
                    'answer' => 'Užsakymas pradedamas vykdyti tuomet, kai sistema užfiksuoja sėkmingą apmokėjimą arba kai suderinamas išankstinis mokėjimas pagal pateiktą informaciją užsakymo lange.',
                ],
                [
                    'question' => 'Ar galima rinktis pristatymą į objektą arba atsiėmimą?',
                    'answer' => 'Taip. Atsižvelgiant į prekių pobūdį ir sandėlio likučius, galite pasirinkti pristatymą nurodytu adresu arba atsiėmimą sutartoje vietoje.',
                ],
                [
                    'question' => 'Kokie apmokėjimo būdai dažniausiai naudojami?',
                    'answer' => 'Dažniausiai pasirenkama internetinė bankininkystė, mokėjimo kortelės arba išankstinis bankinis pavedimas. Tikslius būdus rasite atskirame apmokėjimo informacijos puslapyje.',
                ],
                [
                    'question' => 'Kaip elgtis gavus pažeistą siuntą?',
                    'answer' => 'Priėmimo metu įvertinkite pakuotę ir prekių būklę. Pastebėję akivaizdų pažeidimą, kuo greičiau užfiksuokite informaciją ir susisiekite su mūsų komanda, kad galėtume pradėti sprendimą nedelsdami.',
                ],
                [
                    'question' => 'Ar galima grąžinti nepanaudotą prekę?',
                    'answer' => 'Daugeliu atvejų taip, jei prekė išlaikė prekinę išvaizdą ir yra grąžinama pagal taikomas taisykles. Detalios sąlygos pateiktos grąžinimo ir garantijos puslapyje.',
                ],
                [
                    'question' => 'Kaip vyksta garantinis aptarnavimas?',
                    'answer' => 'Garantiniais atvejais įvertiname gedimo pobūdį, patikriname įsigijimo informaciją ir pasiūlome taisymą, keitimą arba kitą taikomą sprendimą pagal prekės kategoriją.',
                ],
                [
                    'question' => 'Kur kreiptis, jei reikia pagalbos renkantis medžiagas?',
                    'answer' => 'Jei reikalinga konsultacija dėl produkto paskirties, kiekio ar suderinamumo, susisiekite su mūsų komanda. Padėsime susikomplektuoti užsakymą pagal darbų etapą.',
                ],
            ],
            'actions' => [
                [
                    'label' => 'Peržiūrėti pristatymo informaciją',
                    'type' => 'page',
                    'page' => 'shipping',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Susisiekti su komanda',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['payment-methods', 'shipping', 'returns'],
        ],
        'payment-methods' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'Apmokėjimo būdai',
            'description' => 'Informacija apie dažniausiai naudojamus atsiskaitymo būdus, mokėjimo patvirtinimą ir veiksmus, jei apmokėjimas nepavyko.',
            'summary' => 'Apmokėjimo informacijos puslapį suformavome taip, kaip tai daro didesnės e. prekybos platformos: pirmiausia paaiškinami galimi būdai, po to aprašoma užsakymo būsena ir ką daryti iškilus mokėjimo nesklandumams. Taip lengviau suprasti, kada užsakymas jau vykdomas ir kada reikia papildomo veiksmo.',
            'highlights' => [
                [
                    'title' => 'Mokėjimo pasirinkimas',
                    'description' => 'Aiškiai atskiriame momentinį atsiskaitymą, kortelių mokėjimus ir išankstinį pavedimą.',
                ],
                [
                    'title' => 'Patvirtinimas',
                    'description' => 'Paaiškiname, kada sistema užfiksuoja mokėjimą ir kaip apie tai informuojamas pirkėjas.',
                ],
                [
                    'title' => 'Nesklandumų sprendimas',
                    'description' => 'Jei mokėjimas nutrūksta ar neužsifiksuoja, turite aiškią veiksmų seką be papildomų spėlionių.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Galimi atsiskaitymo būdai',
                    'paragraphs' => [
                        'Atsiskaitymui internetu paprastai siūlome mokėjimo korteles, internetinę bankininkystę arba bankinį pavedimą, kai užsakymas apmokamas iš anksto pagal pateiktą informaciją.',
                        'Galutinį būdų sąrašą matysite atsiskaitymo žingsnyje, nes dalis variantų gali priklausyti nuo užsakymo sumos, pristatymo tipo ar konkrečios prekės.',
                    ],
                ],
                [
                    'title' => 'Kada mokėjimas laikomas gautu',
                    'paragraphs' => [
                        'Momentinio atsiskaitymo atveju užsakymo būsena dažniausiai atsinaujina iš karto po sėkmingo mokėjimo patvirtinimo.',
                        'Jei pasirenkamas išankstinis pavedimas, užsakymo vykdymas pradedamas gavus lėšas ir patvirtinus apmokėjimą sistemoje.',
                    ],
                    'list' => [
                        'Patvirtinimas matomas užsakymo santraukoje.',
                        'Apie būsenos pasikeitimą gali būti informuojama el. paštu.',
                        'Esant neaiškumams, rekomenduojama nurodyti užsakymo numerį susisiekiant su komanda.',
                    ],
                ],
                [
                    'title' => 'Jei mokėjimas nepavyko',
                    'paragraphs' => [
                        'Kartais mokėjimo procesą nutraukia banko autorizacija, per ilga sesija ar ryšio trikdžiai. Tokiais atvejais verta pirmiausia patikrinti, ar užsakymas tikrai nebuvo apmokėtas.',
                    ],
                    'list' => [
                        'Peržiūrėkite savo banko arba kortelės išrašą.',
                        'Patikrinkite, ar gavote užsakymo patvirtinimo laišką.',
                        'Jei reikia, pabandykite atlikti mokėjimą pakartotinai tik įsitikinę, kad pirmasis nebuvo užfiksuotas.',
                    ],
                ],
                [
                    'title' => 'Sąskaitos ir mokėjimo dokumentai',
                    'paragraphs' => [
                        'Po sėkmingo užsakymo patvirtinimo parengiami reikalingi dokumentai pagal jūsų pasirinktą pirkėjo tipą ir nurodytus rekvizitus.',
                        'Jei perkate projektui ar įmonei, verta iš karto pateikti tikslius rekvizitus, kad sąskaitos paruošimas vyktų sklandžiai.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Peržiūrėti DUK',
                    'type' => 'page',
                    'page' => 'faq',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Susisiekti dėl apmokėjimo',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['faq', 'shipping', 'terms'],
        ],
        'popular-products' => [
            'section' => 'Prekių katalogas ir paslaugos',
            'title' => 'Populiariausios prekės',
            'description' => 'Dažniausiai perkamos statybų, remonto ir aplinkos priežiūros prekės vienoje aiškioje apžvalgoje.',
            'summary' => 'Populiariausių prekių puslapį modeliavome pagal didžiųjų statybinių prekių e. parduotuvių katalogo logiką: aiškiai atskiriamos dažnai pasirenkamos produktų grupės, sezoniškumas ir sprendimai, kurie padeda greitai papildyti projektui reikalingus kiekius. Tai orientacinė apžvalga, padedanti greitai pradėti paiešką.',
            'highlights' => [
                [
                    'title' => 'Dažniausi pasirinkimai',
                    'description' => 'Įtraukėme produktų grupes, kurios dažniausiai naudojamos tiek smulkiam remontui, tiek didesniems darbams.',
                ],
                [
                    'title' => 'Sezoninis aktualumas',
                    'description' => 'Akcentuojame prekes, kurių poreikis dažniausiai išauga konkrečiu statybų ar sodo darbų laikotarpiu.',
                ],
                [
                    'title' => 'Greita pradžia',
                    'description' => 'Užuot pradėjus nuo viso katalogo, lengviau atsispirti nuo labiausiai ieškomų sprendimų.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Mišiniai, tvirtinimo detalės ir izoliacija',
                    'paragraphs' => [
                        'Tarp populiariausių pasirinkimų dažniausiai patenka sausieji mišiniai, klijai, sandarinimo priemonės, tvirtinimo detalės ir pagrindinės šiltinimo medžiagos.',
                        'Tai grupės, kurios reikalingos tiek naujai statybai, tiek renovacijai, todėl jos dažnai tampa pirmuoju pirkimo tašku ruošiant objekto sąmatą.',
                    ],
                ],
                [
                    'title' => 'Įrankiai kasdieniams darbams',
                    'paragraphs' => [
                        'Didelė paklausa išlieka akumuliatoriniams gręžtuvams, pjovimo įrankiams, matavimo priemonėms, kopėčioms ir darbo saugos prekėms.',
                    ],
                    'list' => [
                        'Greitam montavimui ir remonto darbams',
                        'Priežiūros užduotims dirbtuvėse ar objekte',
                        'Tiksliai kontrolei ir darbų kokybei užtikrinti',
                    ],
                ],
                [
                    'title' => 'Sodo ir aplinkos tvarkymo prekės',
                    'paragraphs' => [
                        'Šiltuoju sezonu tarp dažniausiai perkamų prekių patenka laistymo sprendimai, vejos priežiūros technika, lauko dangos ir teritorijos tvarkymo smulkioji įranga.',
                    ],
                    'note' => 'Jei perkate didesniam projektui, verta suderinti visas susijusias grupes vienu užsakymu, kad būtų lengviau planuoti tiekimą ir pristatymo terminus.',
                ],
            ],
            'actions' => [
                [
                    'label' => 'Naršyti kategorijas',
                    'type' => 'route',
                    'routes' => ['localized.categories.index', 'frontend.categories.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Peržiūrėti specialius pasiūlymus',
                    'type' => 'page',
                    'page' => 'special-offers',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['building-materials', 'tools-equipment', 'special-offers'],
        ],
        'building-materials' => [
            'section' => 'Prekių katalogas ir paslaugos',
            'title' => 'Statybinės medžiagos',
            'description' => 'Pagrindinės medžiagų grupės, jų paskirtis ir orientaciniai pasirinkimo principai įvairiems statybos darbų etapams.',
            'summary' => 'Šiame puslapyje statybines medžiagas suskirstėme taip, kaip įprasta didesniuose statybos prekių kataloguose: nuo konstrukcinių sprendimų ir šiltinimo iki vidaus apdailos. Tokia struktūra padeda greitai pereiti nuo bendro projekto poreikio prie konkrečių produktų grupių.',
            'highlights' => [
                [
                    'title' => 'Darbų etapais',
                    'description' => 'Lengviau pasirinkti, kai medžiagos pateikiamos pagal darbų seką, o ne tik pagal techninius pavadinimus.',
                ],
                [
                    'title' => 'Suderinamumas',
                    'description' => 'Svarbu vertinti ne vien pavienę prekę, bet ir visos sistemos suderinamumą objekte.',
                ],
                [
                    'title' => 'Tiekimo planavimas',
                    'description' => 'Didelių kiekių ar specifinių medžiagų užsakymus verta planuoti iš anksto, kad nebūtų darbų pertraukų.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Pamatai, mūras ir konstrukciniai sprendimai',
                    'paragraphs' => [
                        'Pradinių statybos etapų pirkiniuose dažniausiai dominuoja mūro blokai, betono produktai, armavimo sprendimai ir medžiagos, reikalingos konstrukcijų formavimui bei tvirtinimui.',
                    ],
                ],
                [
                    'title' => 'Šiltinimas, sandarinimas ir apsauga',
                    'paragraphs' => [
                        'Šiltinimo medžiagos, sandarinimo priemonės, hidroizoliacija ir garo izoliacijos sprendimai turi būti derinami pagal konkrečią konstrukciją ir eksploatavimo sąlygas.',
                    ],
                    'list' => [
                        'Fasadų ir perdangų šiltinimui',
                        'Vidinių konstrukcijų sandarumui',
                        'Apsaugai nuo drėgmės ir temperatūrinių svyravimų',
                    ],
                ],
                [
                    'title' => 'Vidaus apdaila ir užbaigimo darbai',
                    'paragraphs' => [
                        'Vėlesniuose etapuose aktualūs glaistai, tinkai, gruntai, dažymo paruošimo medžiagos, plokštės ir kitos apdailos grupės.',
                        'Svarbu vertinti ne tik kainą, bet ir tai, kaip medžiaga tinka konkrečiam pagrindui bei darbo intensyvumui.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Atverti kategorijų katalogą',
                    'type' => 'route',
                    'routes' => ['localized.categories.index', 'frontend.categories.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Gauti konsultaciją',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['popular-products', 'tools-equipment', 'services-for-craftsmen'],
        ],
        'tools-equipment' => [
            'section' => 'Prekių katalogas ir paslaugos',
            'title' => 'Įrankiai ir įranga',
            'description' => 'Elektriniai, akumuliatoriniai ir rankiniai įrankiai, matavimo priemonės bei pagalbinė įranga kasdieniams darbams.',
            'summary' => 'Įrankių puslapį sudėjome pagal aiškią darbo logiką: gręžimas ir tvirtinimas, pjovimas ir šlifavimas, matavimas ir sauga. Tai leidžia greitai orientuotis tiek profesionalui, kuris ieško konkretaus sprendimo, tiek pirkėjui, kuris tik komplektuoja darbų krepšelį.',
            'highlights' => [
                [
                    'title' => 'Kasdieniams darbams',
                    'description' => 'Akcentuojame universalius įrankius, kurie dažniausiai pasirenkami remonto ir montavimo užduotims.',
                ],
                [
                    'title' => 'Tikslumui ir kontrolei',
                    'description' => 'Matavimo bei ženklinimo priemonės padeda išvengti klaidų ir taupo darbo laiką.',
                ],
                [
                    'title' => 'Saugiam darbui',
                    'description' => 'Atskiriame apsaugos ir pagalbinės įrangos grupes, kurios reikalingos ne mažiau nei pats įrankis.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Elektriniai ir akumuliatoriniai įrankiai',
                    'paragraphs' => [
                        'Dažniausiai ieškomi sprendimai šioje grupėje yra gręžtuvai, suktuvai, perforatoriai, kampiniai šlifuokliai, pjūklai ir kita įranga, skirta montavimo bei pjovimo darbams.',
                    ],
                ],
                [
                    'title' => 'Rankiniai įrankiai ir dirbtuvių komplektacija',
                    'paragraphs' => [
                        'Replės, veržliarakčiai, atsuktuvai, plaktukai, pjūkleliai ir įrankių laikymo sprendimai išlieka svarbia bazinės komplektacijos dalimi tiek namų meistrui, tiek profesionalui.',
                    ],
                    'list' => [
                        'Greitai prieigai prie dažniausiai naudojamų įrankių',
                        'Patogiam transportavimui į objektą',
                        'Tvarkingam darbui dirbtuvėse ar sandėlyje',
                    ],
                ],
                [
                    'title' => 'Matavimo, žymėjimo ir darbo saugos priemonės',
                    'paragraphs' => [
                        'Lazeriniai matuokliai, gulsčiukai, kampainiai, apsauginės pirštinės, akiniai, kvėpavimo priemonės ir kita apsauga padeda dirbti tiksliai ir saugiai.',
                    ],
                    'note' => 'Jeigu komplektuojate daugiau nei vieno etapo darbus, verta iš karto suplanuoti ir pagrindinę įrangą, ir eksploatacines priemones, kad objektas nesustotų dėl smulkių trūkumų.',
                ],
            ],
            'actions' => [
                [
                    'label' => 'Peržiūrėti katalogą',
                    'type' => 'route',
                    'routes' => ['localized.categories.index', 'frontend.categories.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Paslaugos meistrams',
                    'type' => 'page',
                    'page' => 'services-for-craftsmen',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['popular-products', 'building-materials', 'services-for-craftsmen'],
        ],
        'special-offers' => [
            'section' => 'Prekių katalogas ir paslaugos',
            'title' => 'Specialūs pasiūlymai ir akcijos',
            'description' => 'Kaip orientuotis akciniuose pasiūlymuose, planuoti pirkinius pagal sezoną ir greitai rasti aktualias nuolaidas.',
            'summary' => 'Specialių pasiūlymų puslapį sudėliojome ne kaip atsitiktinių nuolaidų sąrašą, o kaip orientacinį vadovą: kokių tipų akcijos dažniausiai pasitaiko, kada verta planuoti didesnį pirkimą ir kaip vertinti pasiūlymų aktualumą. Tokiu modeliu vadovaujasi ir didesni statybinių prekių e. prekybos katalogai.',
            'highlights' => [
                [
                    'title' => 'Trumpalaikės akcijos',
                    'description' => 'Kai kurie pasiūlymai galioja ribotą laiką arba iki konkretaus likučio išpardavimo.',
                ],
                [
                    'title' => 'Sezoniniai ciklai',
                    'description' => 'Dalis nuolaidų sutampa su statybų sezono pradžia, sodo darbų laikotarpiu ar metų pabaigos išpardavimais.',
                ],
                [
                    'title' => 'Kompleksiniai pirkimai',
                    'description' => 'Planuojant kelių grupių užsakymą verta vertinti ne vien vienetinę kainą, bet ir visą bendrą krepšelį.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Kaip greitai atsirinkti aktualius pasiūlymus',
                    'paragraphs' => [
                        'Akcijose paprastai verta pradėti nuo prekių, kurias jau esate numatę projekte, o ne nuo atsitiktinių nuolaidų. Taip lengviau įvertinsite realią naudą ir nepersipildysite nereikalingais pirkiniais.',
                    ],
                ],
                [
                    'title' => 'Kada verta planuoti didesnį užsakymą',
                    'paragraphs' => [
                        'Jei projektui reikia kelių susijusių grupių, naudinga stebėti sezoninius pasiūlymus ir suplanuoti užsakymą vienu metu, kai palankios kainos taikomos medžiagoms, įrankiams ar pristatymui.',
                    ],
                    'list' => [
                        'Objektui reikalingi didesni kiekiai',
                        'Reikia suderinti skirtingų grupių pirkinius',
                        'Svarbu sumažinti pristatymų skaičių ir planavimo klaidas',
                    ],
                ],
                [
                    'title' => 'Ką verta pasitikrinti prieš perkant',
                    'paragraphs' => [
                        'Net ir akcinių pasiūlymų atveju verta peržiūrėti techninius parametrus, suderinamumą su kitomis medžiagomis, likučių prieinamumą ir pristatymo terminus.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Atverti nuolaidų puslapį',
                    'type' => 'route',
                    'routes' => ['frontend.discounts.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Peržiūrėti populiariausias prekes',
                    'type' => 'page',
                    'page' => 'popular-products',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['popular-products', 'payment-methods', 'faq'],
        ],
        'services-for-craftsmen' => [
            'section' => 'Prekių katalogas ir paslaugos',
            'title' => 'Paslaugos meistrams',
            'description' => 'Paslaugų ir bendradarbiavimo principų apžvalga specialistams, kurie planuoja nuolatinius ar didesnius užsakymus.',
            'summary' => 'Paslaugų meistrams puslapį suformavome pagal B2B ir profesionalų aptarnavimo logiką: aiškiai atskiriamas medžiagų parinkimas, užsakymų komplektavimas, pristatymo planavimas ir individuali konsultacija. Tokia struktūra padeda greičiau susitarti dėl darbų ritmo ir tiekimo poreikio.',
            'highlights' => [
                [
                    'title' => 'Projektinis planavimas',
                    'description' => 'Padedame susidėlioti pirkinius pagal darbų etapus ir objekto prioritetus.',
                ],
                [
                    'title' => 'Konsultacijos',
                    'description' => 'Galite kreiptis dėl suderinamumo, kiekių, pristatymo ar pasiūlymų alternatyvų.',
                ],
                [
                    'title' => 'Sklandus tiekimas',
                    'description' => 'Svarbu ne tik kaina, bet ir tai, kad prekės pasiektų objektą tinkamu laiku.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Medžiagų ir prekių parinkimas',
                    'paragraphs' => [
                        'Kai projektui reikia kelių tarpusavyje susijusių grupių, naudinga iš karto derinti ne tik pagrindines medžiagas, bet ir eksploatacinius bei montavimo priedus.',
                    ],
                ],
                [
                    'title' => 'Užsakymo komplektavimas pagal darbų eigą',
                    'paragraphs' => [
                        'Profesionalams dažnai svarbu, kad užsakymas būtų sudėliotas pagal objekto etapus, o ne vien pagal bendrą prekių sąrašą. Tai leidžia patogiau planuoti priėmimą ir sandėliavimą.',
                    ],
                    'list' => [
                        'Pagrindinių medžiagų užsakymas vienam etapui',
                        'Papildomos priemonės montažui ir apsaugai',
                        'Pakartotiniai papildymai pagal realų darbų progresą',
                    ],
                ],
                [
                    'title' => 'Pristatymo ir atsiėmimo derinimas',
                    'paragraphs' => [
                        'Jei darbai vyksta pagal grafiką, pristatymo laikas tampa tiek pat svarbus kaip ir pati prekių kaina. Todėl iš anksto suderintas tiekimo modelis padeda išvengti prastovų objekte.',
                    ],
                    'note' => 'Jeigu projektas nestandartinis arba reikalingi dideli kiekiai, rekomenduojame susisiekti dar prieš formuojant galutinį užsakymą.',
                ],
            ],
            'actions' => [
                [
                    'label' => 'Susisiekti dėl bendradarbiavimo',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Peržiūrėti statybines medžiagas',
                    'type' => 'page',
                    'page' => 'building-materials',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['building-materials', 'tools-equipment', 'shipping'],
        ],
        'privacy' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'Privatumo politika',
            'description' => 'Kaip tvarkome asmens duomenis, kokiu tikslu juos naudojame ir kokias teises turite jūs kaip klientas.',
            'summary' => 'Privatumo politikos išdėstymą suformavome aiškiai ir paprastai: pirmiausia nurodome, kokius duomenis gauname, tada paaiškiname, kam jie naudojami, kaip ilgai saugomi ir kokiais atvejais galite kreiptis dėl savo teisių įgyvendinimo.',
            'highlights' => [
                [
                    'title' => 'Duomenų apimtis',
                    'description' => 'Tvarkome tik tuos duomenis, kurie reikalingi užsakymo vykdymui, ryšiui su klientu ir paslaugų kokybei užtikrinti.',
                ],
                [
                    'title' => 'Naudojimo tikslai',
                    'description' => 'Duomenys naudojami užsakymų administravimui, pristatymui, atsiskaitymams ir klientų aptarnavimui.',
                ],
                [
                    'title' => 'Kliento teisės',
                    'description' => 'Galite prašyti susipažinti su duomenimis, juos tikslinti ar gauti daugiau informacijos apie tvarkymo pagrindą.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Kokius duomenis galime gauti',
                    'paragraphs' => [
                        'Paprastai tai yra užsakymo vykdymui reikalingi kontaktiniai duomenys, pristatymo informacija, įmonės rekvizitai ir techniniai duomenys, susiję su svetainės naudojimu.',
                    ],
                ],
                [
                    'title' => 'Kam naudojame duomenis',
                    'paragraphs' => [
                        'Duomenys reikalingi tam, kad galėtume priimti užsakymą, suorganizuoti pristatymą ar atsiėmimą, pateikti mokėjimo informaciją ir atsakyti į užklausas.',
                    ],
                ],
                [
                    'title' => 'Saugumas ir saugojimo terminas',
                    'paragraphs' => [
                        'Duomenis saugome tiek, kiek reikia teisėtiems tvarkymo tikslams, apskaitai, klientų aptarnavimui ir teisinių prievolių vykdymui.',
                    ],
                ],
                [
                    'title' => 'Jūsų teisės',
                    'paragraphs' => [
                        'Jei norite patikslinti duomenis, sužinoti apie jų naudojimą ar pateikti kitą su privatumu susijusį prašymą, susisiekite su mūsų komanda ir pateikite kuo tikslesnę užklausos informaciją.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Susisiekti dėl privatumo',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Pirkimo-pardavimo taisyklės',
                    'type' => 'page',
                    'page' => 'terms',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['terms', 'shipping', 'returns'],
        ],
        'terms' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'Pirkimo-pardavimo taisyklės',
            'description' => 'Pagrindinės nuostatos apie užsakymo sudarymą, kainas, atsiskaitymą, pristatymą ir šalių atsakomybes.',
            'summary' => 'Taisyklių puslapį suformavome taip, kad svarbiausi punktai būtų matomi iškart: bendros nuostatos, užsakymo sudarymas, kainų ir mokėjimo tvarka, pristatymas bei ginčų sprendimo principai. Tokia seka atitinka tai, ko klientai paprastai ieško pirmiausia.',
            'highlights' => [
                [
                    'title' => 'Aiškios sąlygos',
                    'description' => 'Svarbiausios pirkimo nuostatos pateikiamos be perteklinio teisinio sudėtingumo.',
                ],
                [
                    'title' => 'Užsakymo eiga',
                    'description' => 'Akcentuojame momentą, kada užsakymas tampa vykdomas ir kokią įtaką tam turi atsiskaitymas.',
                ],
                [
                    'title' => 'Praktinis pritaikymas',
                    'description' => 'Taisyklės apima ne tik dokumentinę, bet ir realią užsakymo vykdymo logiką.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Bendrosios nuostatos',
                    'paragraphs' => [
                        'Pirkimo-pardavimo taisyklės apibrėžia užsakymo sudarymo, apmokėjimo, pristatymo, grąžinimo ir kitų su pirkimu susijusių procesų principus.',
                    ],
                ],
                [
                    'title' => 'Užsakymo sudarymas',
                    'paragraphs' => [
                        'Užsakymas laikomas pateiktu tuomet, kai pirkėjas suformuoja prekių krepšelį, pateikia reikiamus duomenis ir patvirtina pasirinktą apmokėjimo bei pristatymo būdą.',
                    ],
                ],
                [
                    'title' => 'Kainos ir atsiskaitymas',
                    'paragraphs' => [
                        'Kainos bei galimos nuolaidos nurodomos užsakymo metu. Užsakymo vykdymas paprastai pradedamas po sėkmingo apmokėjimo arba kai kitaip suderinamos atsiskaitymo sąlygos.',
                    ],
                ],
                [
                    'title' => 'Atsakomybė ir ginčų sprendimas',
                    'paragraphs' => [
                        'Jei kyla klausimų dėl užsakymo vykdymo, pirmiausia siekiame sprendimą rasti tiesiogiai su klientu, aiškiai įvertinant konkrečias užsakymo aplinkybes.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Peržiūrėti apmokėjimo būdus',
                    'type' => 'page',
                    'page' => 'payment-methods',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Susisiekti dėl užsakymo',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['payment-methods', 'shipping', 'returns'],
        ],
        'shipping' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'Pristatymas ir atsiėmimas',
            'description' => 'Aiški pristatymo ir atsiėmimo tvarka: parduotuvė, paštomatai, pristatymas į namus, užnešimo paslauga ir veiksmai gavus siuntą.',
            'summary' => 'Šiame puslapyje pateikiame praktinę informaciją, kuri padeda greitai pasirinkti tinkamą pristatymo būdą pagal užsakymo dydį, svorį ir terminą.',
            'highlights' => [
                [
                    'title' => 'Atsiėmimas parduotuvėje',
                    'description' => 'Matysite, kuriose parduotuvėse galima atsiimti visą krepšelį ir kada užsakymas bus paruoštas.',
                ],
                [
                    'title' => 'Paštomatai ir kurjeris',
                    'description' => 'Pristatymo būdas priklauso nuo prekių matmenų, svorio ir konkretaus produkto prieinamumo.',
                ],
                [
                    'title' => 'Sunkiasvorių prekių tvarka',
                    'description' => 'Virš 30 kg siuntoms taikoma atskira pristatymo logika, o užnešimą reikia užsakyti papildomai.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Atsiėmimas parduotuvėje',
                    'paragraphs' => [
                        'Prie kiekvienos prekės pateikiame, ar galimas atsiėmimas parduotuvėje ir kuriose lokacijose galima atsiimti užsakymą.',
                        'Užsakymams, pasirenkamiems atsiėmimui parduotuvėje, gali būti taikomas užsakymo paruošimo mokestis (šiuo metu 0,38 €).',
                    ],
                    'list' => [
                        'Paruošus užsakymą, informuojame SMS žinute ir (ar) el. paštu.',
                        'Atsiėmimui reikalingas užsakymo numeris.',
                        'Jei atsiima kitas asmuo, jį būtina nurodyti kaip gavėją pateikiant užsakymą.',
                    ],
                ],
                [
                    'title' => 'Skubus atsiėmimas',
                    'paragraphs' => [
                        'Kai pasirinktame atsiėmimo taške yra pakankamas likutis ir užsakymas atitinka svorio ribas, gali būti siūlomas skubus paruošimas atsiėmimui.',
                        'Pateikus ir apmokėjus užsakymą darbo dienomis, paruošimo terminas gali būti trumpesnis nei įprastai.',
                    ],
                ],
                [
                    'title' => 'Pristatymas į paštomatus',
                    'paragraphs' => [
                        'Paštomatų pristatymas galimas toms prekėms, kurios atitinka siuntų operatorių taikomus matmenų ir svorio apribojimus.',
                        'Galimi LP Express, Omniva, DPD ir Itella paštomatai. Tikslūs terminai ir kaina visada pateikiami atsiskaitymo metu.',
                    ],
                    'list' => [
                        'Dažniausiai taikoma maksimali svorio riba: iki 30 kg.',
                        'Itella paštomatai dažniausiai yra prekybos centrų viduje, todėl gali galioti jų darbo laikas.',
                    ],
                ],
                [
                    'title' => 'Pristatymas į namus',
                    'paragraphs' => [
                        'Pristatymo terminas nurodomas prekių krepšelyje. Užsakius kelias prekes, jos gali būti pristatytos atskirai iš skirtingų sandėlių, netaikant papildomo transporto mokesčio.',
                        'Didžiuosiuose miestuose kurjerių darbo intervalas paprastai ilgesnis nei kituose regionuose; apie tikslesnį laiką informuoja kurjerių tarnyba.',
                    ],
                    'list' => [
                        'Siuntos iki 30 kg paprastai pristatomos iki namo ar buto durų.',
                        'Siuntos virš 30 kg paprastai pristatomos iki namo vartų ar daugiabučio laiptinės.',
                        'XL ir ant padėklo vežamos prekės pristatomos iki iškrovimo vietos.',
                    ],
                    'note' => 'Atskiroms teritorijoms gali būti taikomas papildomas pristatymo mokestis.',
                ],
                [
                    'title' => 'Užnešimo / krovos paslauga',
                    'paragraphs' => [
                        'Užnešimo paslaugą reikia užsakyti atsiskaitymo metu, iki užsakymo apmokėjimo.',
                        'Paslauga taikoma, kai vienos prekės svoris yra nuo 30 iki 80 kg, o bendras krepšelio svoris neviršija nustatytos ribos (dažniausiai iki 300 kg).',
                    ],
                    'note' => 'Paslaugos kaina apskaičiuojama pagal bendrą svorį ir rodoma atsiskaitymo žingsnyje.',
                ],
                [
                    'title' => 'Tos pačios dienos pristatymas',
                    'paragraphs' => [
                        'Kai kuriose teritorijose darbo dienomis gali būti siūlomas tos pačios dienos pristatymas į namus.',
                        'Ši paslauga taikoma tik riboto dydžio ir svorio siuntoms, todėl jos prieinamumas priklauso nuo konkretaus krepšelio.',
                    ],
                    'list' => [
                        'Dažniausiai taikoma svorio riba: iki 30 kg.',
                        'Didmiesčiuose pristatymas paprastai vykdomas vakarinėmis valandomis.',
                    ],
                ],
                [
                    'title' => 'Siuntos priėmimas ir neatitikimai',
                    'paragraphs' => [
                        'Priimdami siuntą patikrinkite pakuotės būklę, prekių kiekį ir akivaizdžius pažeidimus.',
                        'Jei pastebite trūkumų ar pažeidimų, juos užfiksuokite ir nedelsdami susisiekite su klientų aptarnavimu.',
                    ],
                    'list' => [
                        'Nurodykite užsakymo numerį.',
                        'Pridėkite nuotraukas.',
                        'Trumpai aprašykite neatitikimą ir pakuotės būklę.',
                    ],
                    'note' => 'Neatsiėmus siuntos per nustatytą terminą, ji gali būti grąžinta pardavėjui, o užsakymas atšauktas pagal galiojančias taisykles.',
                ],
            ],
            'actions' => [
                [
                    'label' => 'Peržiūrėti DUK',
                    'type' => 'page',
                    'page' => 'faq',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Apmokėjimo būdai',
                    'type' => 'page',
                    'page' => 'payment-methods',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['faq', 'payment-methods', 'returns'],
        ],
        'returns' => [
            'section' => 'Pagalba ir informacija',
            'title' => 'Prekių grąžinimas ir garantija',
            'description' => 'Kada galima grąžinti prekes, kaip vyksta garantinių atvejų vertinimas ir ką svarbu paruošti kreipiantis.',
            'summary' => 'Grąžinimo ir garantijos puslapį išdėstėme taip, kad klientui nereikėtų ieškoti atskirų informacijos gabalų: vienoje vietoje matomi bendri grąžinimo principai, pasiruošimas grąžinimui, garantinių atvejų eiga ir pinigų grąžinimo ar keitimo sprendimų logika.',
            'highlights' => [
                [
                    'title' => 'Grąžinimo eiga',
                    'description' => 'Svarbiausia aiškiai įvardyti užsakymą, prekę ir grąžinimo priežastį.',
                ],
                [
                    'title' => 'Garantiniai atvejai',
                    'description' => 'Gedimo ar defekto vertinimas vykdomas pagal prekės tipą ir pateiktas aplinkybes.',
                ],
                [
                    'title' => 'Sprendimo terminas',
                    'description' => 'Kuo tikslesnė informacija pateikiama iš karto, tuo greičiau galima priimti sprendimą dėl keitimo, taisymo ar kompensavimo.',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Kada prekė gali būti grąžinama',
                    'paragraphs' => [
                        'Grąžinimas galimas tuomet, kai laikomasi taikomų sąlygų, prekė nėra praradusi prekinės išvaizdos ir jos būklė leidžia įvertinti grąžinimo aplinkybes.',
                    ],
                ],
                [
                    'title' => 'Kaip pasiruošti grąžinimui',
                    'paragraphs' => [
                        'Prieš kreipiantis verta turėti užsakymo numerį, pirkimo dokumentą ir trumpą situacijos aprašymą. Jei tai garantinis atvejis, naudinga pateikti ir defekto požymius arba nuotraukas.',
                    ],
                ],
                [
                    'title' => 'Garantinių atvejų vertinimas',
                    'paragraphs' => [
                        'Garantinis aptarnavimas vertinamas pagal prekės pobūdį, naudojimo aplinkybes ir pateiktą informaciją. Atsižvelgiant į situaciją, gali būti siūlomas taisymas, keitimas ar kitas tinkamas sprendimas.',
                    ],
                ],
                [
                    'title' => 'Pinigų grąžinimas arba keitimas',
                    'paragraphs' => [
                        'Jei grąžinimas patvirtinamas, sprendimas dėl kompensavimo, keitimo ar kito tęstinio veiksmo įgyvendinamas pagal konkretaus atvejo aplinkybes ir taikomą tvarką.',
                    ],
                ],
            ],
            'actions' => [
                [
                    'label' => 'Susisiekti dėl grąžinimo',
                    'type' => 'route',
                    'routes' => ['localized.contact.index', 'frontend.contact.index'],
                    'style' => 'primary',
                ],
                [
                    'label' => 'Peržiūrėti pristatymo informaciją',
                    'type' => 'page',
                    'page' => 'shipping',
                    'style' => 'secondary',
                ],
            ],
            'related_pages' => ['faq', 'shipping', 'terms'],
        ],
    ],
];
