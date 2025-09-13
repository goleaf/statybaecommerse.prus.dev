<?php

return [
    'title' => 'Šalys',
    'subtitle' => 'Naršykite šalis pagal regioną, valiutą ar kitus kriterijus',
    
    'fields' => [
        'name' => 'Pavadinimas',
        'name_official' => 'Oficialus pavadinimas',
        'description' => 'Aprašymas',
        'code' => 'Kodas',
        'region' => 'Regionas',
        'subregion' => 'Subregionas',
        'currency' => 'Valiuta',
        'phone_code' => 'Telefono kodas',
        'flag' => 'Vėliava',
        'is_eu_member' => 'ES narė',
        'vat_rate' => 'PVM tarifas',
    ],
    
    'filters' => [
        'all' => 'Visos šalys',
        'active' => 'Aktyvios šalys',
        'eu_members' => 'ES narės',
        'by_region' => 'Filtruoti pagal regioną',
        'by_currency' => 'Filtruoti pagal valiutą',
        'with_vat' => 'Su PVM',
        'without_vat' => 'Be PVM',
        'search' => 'Paieška',
        'search_placeholder' => 'Ieškoti šalių...',
        'all_regions' => 'Visi regionai',
        'all_currencies' => 'Visos valiutos',
        'eu_members_only' => 'Tik ES narės',
        'non_eu_only' => 'Tik ne ES narės',
        'apply_filters' => 'Taikyti filtrus',
        'clear_filters' => 'Išvalyti filtrus',
    ],
    
    'actions' => [
        'view_details' => 'Peržiūrėti detales',
        'select_country' => 'Pasirinkti šalį',
        'show_on_map' => 'Rodyti žemėlapyje',
        'get_directions' => 'Gauti kryptis',
        'back_to_list' => 'Grįžti į sąrašą',
    ],
    
    'messages' => [
        'no_countries_found' => 'Šalių nerasta.',
        'loading_countries' => 'Kraunamos šalys...',
        'country_selected' => 'Šalis pasirinkta.',
        'error_loading' => 'Klaida kraunant šalis.',
        'try_different_filters' => 'Pabandykite pakeisti filtrus.',
    ],
    
    'details' => [
        'title' => 'Šalies informacija',
        'basic_info' => 'Pagrindinė informacija',
        'location_info' => 'Vietos informacija',
        'economic_info' => 'Ekonominė informacija',
        'contact_info' => 'Kontaktinė informacija',
        'additional_info' => 'Papildoma informacija',
        'major_cities' => 'Pagrindiniai miestai',
        'actions' => 'Veiksmai',
    ],

    'fields' => [
        'yes' => 'Taip',
        'no' => 'Ne',
        'capital' => 'Sostinė',
    ],
    
    'regions' => [
        'europe' => 'Europa',
        'asia' => 'Azija',
        'africa' => 'Afrika',
        'north_america' => 'Šiaurės Amerika',
        'south_america' => 'Pietų Amerika',
        'oceania' => 'Okeanija',
        'antarctica' => 'Antarktida',
    ],
    
    'currencies' => [
        'eur' => 'Euras (€)',
        'usd' => 'JAV doleris ($)',
        'gbp' => 'Svaras sterlingas (£)',
        'jpy' => 'Japonijos jena (¥)',
        'chf' => 'Šveicarijos frankas',
        'cad' => 'Kanados doleris',
        'aud' => 'Australijos doleris',
        'cny' => 'Kinijos juanis',
        'rub' => 'Rusijos rublis',
        'inr' => 'Indijos rupija',
    ],
    
    'statistics' => [
        'total_countries' => 'Iš viso šalių',
        'active_countries' => 'Aktyvių šalių',
        'eu_members' => 'ES narių',
        'countries_with_vat' => 'Šalių su PVM',
        'average_vat_rate' => 'Vidutinis PVM tarifas',
    ],
];
