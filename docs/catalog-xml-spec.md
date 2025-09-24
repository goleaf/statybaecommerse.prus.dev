## XML katalogo specifikacija (produktai ir kategorijos) — LT

Šis dokumentas apibrėžia XML katalogo struktūrą eksportui / importui. Numatytoji kalba: lietuvių (lt). Visos piniginės reikšmės pateikiamos eurais (EUR), su tašku kaip dešimtainiu skirtuku (pvz., `199.99`). Loginės reikšmės: `true` / `false`.

- **Šaknis**: `<catalog>`
- **Poskyriai**: `<categories>` ir `<products>` (pasirenkami, galite siųsti vieną arba abu)

### Tipai ir konvencijos
- **string**: UTF-8 eilutė (be HTML; jei reikia, naudokite CDATA)
- **decimal**: dešimtainis su tašku, valiuta – EUR (pvz., `12.50`)
- **int**: sveikasis skaičius
- **bool**: `true` arba `false`
- **date/datetime**: ISO-8601 (pvz., `2025-09-23T14:25:00+00:00`), jeigu taikoma

### Kategorijos

Struktūra:
```xml
<categories>
  <category>
    <slug>...</slug>
    <parent_slug>...</parent_slug> <!-- pasirenkama -->

    <base>
      <is_enabled>true|false</is_enabled>
      <is_visible>true|false</is_visible>
      <sort_order>int</sort_order>
      <show_in_menu>true|false</show_in_menu>
      <product_limit>int</product_limit>
    </base>

    <translations>
      <translation locale="lt">
        <name>...</name>
        <description>...</description>
        <short_description>...</short_description>
        <seo_title>...</seo_title>
        <seo_description>...</seo_description>
      </translation>
      <translation locale="en">...</translation>
    </translations>
  </category>
</categories>
```

- **Privaloma**: bent vienas iš `slug` arba `translations/translation[@locale='lt']/name` (jei `slug` tuščias, sistema jį sugeneruos iš LT pavadinimo)
- **tėvinė kategorija**: `parent_slug` — galima nurodyti bet kada (pririšimas pagal `slug`)

### Produktai

Struktūra:
```xml
<products>
  <product>
    <sku>...</sku>        <!-- rekomenduojama -->
    <slug>...</slug>       <!-- rekomenduojama -->

    <base>
      <price>decimal</price>
      <compare_price>decimal</compare_price>
      <cost_price>decimal</cost_price>
      <sale_price>decimal</sale_price>
      <weight>decimal</weight>
      <length>decimal</length>
      <width>decimal</width>
      <height>decimal</height>
      <status>draft|published|archived</status>
      <type>simple|variable</type>
      <brand_id>int</brand_id>
      <tax_class>string</tax_class>
      <shipping_class>string</shipping_class>
      <manage_stock>true|false</manage_stock>
      <track_stock>true|false</track_stock>
      <allow_backorder>true|false</allow_backorder>
      <stock_quantity>int</stock_quantity>
      <low_stock_threshold>int</low_stock_threshold>
      <minimum_quantity>int</minimum_quantity>
      <is_visible>true|false</is_visible>
      <is_featured>true|false</is_featured>
      <is_requestable>true|false</is_requestable>
    </base>

    <categories>
      <category_slug>kategorija-a</category_slug>
      <category_slug>kategorija-b</category_slug>
    </categories>

    <translations>
      <translation locale="lt">
        <name>...</name>
        <slug>...</slug>
        <description>...</description>
        <short_description>...</short_description>
        <seo_title>...</seo_title>
        <seo_description>...</seo_description>
      </translation>
      <translation locale="en">...</translation>
    </translations>

    <images>
      <image src="/storage/product-images/1/image-1.jpg" alt="..." />
      <image src="https://example.com/image.jpg" />
    </images>
  </product>
</products>
```

- **Identifikavimas**: rekomenduojama pateikti bent vieną iš `sku` arba `slug` (jei abu tušti, sistema bandys sukurti produktą iš LT `translations/translation[@locale='lt']/name`)
- **Kategorijos**: `category_slug` turi atitikti esamos kategorijos `slug`
- **Paveikslėliai**: `image` atributai — `src` (privalomas), `alt` (pasirenkamas)

### Minimalūs pavyzdžiai

Kategorija:
```xml
<catalog>
  <categories>
    <category>
      <slug>statybine-medziaga</slug>
      <base><is_enabled>true</is_enabled><is_visible>true</is_visible></base>
      <translations>
        <translation locale="lt"><name>Statybinė medžiaga</name></translation>
        <translation locale="en"><name>Building material</name></translation>
      </translations>
    </category>
  </categories>
</catalog>
```

Produktas:
```xml
<catalog>
  <products>
    <product>
      <sku>SKU-1001</sku>
      <slug>cementas-42-5r</slug>
      <base>
        <price>4.99</price>
        <is_visible>true</is_visible>
        <stock_quantity>100</stock_quantity>
      </base>
      <categories>
        <category_slug>statybine-medziaga</category_slug>
      </categories>
      <translations>
        <translation locale="lt">
          <name>Cementas 42.5R</name>
          <short_description>Kokybiškas cementas</short_description>
        </translation>
        <translation locale="en">
          <name>Cement 42.5R</name>
        </translation>
      </translations>
      <images>
        <image src="/storage/product-images/1001/image-1.jpg" alt="Cementas" />
      </images>
    </product>
  </products>
</catalog>
```

---

## XML Catalog Specification (products and categories) — EN

This document defines the XML catalog structure for export/import. Default language is Lithuanian (lt). All money values are in Euros (EUR) with a dot as decimal separator (e.g., `199.99`). Booleans are `true` / `false`.

- **Root**: `<catalog>`
- **Sections**: `<categories>` and `<products>` (optional, you may include either or both)

### Types & Conventions
- **string**: UTF-8 string (no HTML; use CDATA if needed)
- **decimal**: dot-separated decimal, currency EUR (e.g., `12.50`)
- **int**: integer
- **bool**: `true` or `false`
- **date/datetime**: ISO-8601 (e.g., `2025-09-23T14:25:00+00:00`), if applicable

### Categories

Structure:
```xml
<categories>
  <category>
    <slug>...</slug>
    <parent_slug>...</parent_slug> <!-- optional -->

    <base>
      <is_enabled>true|false</is_enabled>
      <is_visible>true|false</is_visible>
      <sort_order>int</sort_order>
      <show_in_menu>true|false</show_in_menu>
      <product_limit>int</product_limit>
    </base>

    <translations>
      <translation locale="lt">
        <name>...</name>
        <description>...</description>
        <short_description>...</short_description>
        <seo_title>...</seo_title>
        <seo_description>...</seo_description>
      </translation>
      <translation locale="en">...</translation>
    </translations>
  </category>
</categories>
```

- **Required**: at least one of `slug` or `translations/translation[@locale='lt']/name` (if `slug` is empty, it is generated from the LT name)
- **parent**: `parent_slug` binds by existing category `slug`

### Products

Structure:
```xml
<products>
  <product>
    <sku>...</sku>        <!-- recommended -->
    <slug>...</slug>       <!-- recommended -->

    <base>
      <price>decimal</price>
      <compare_price>decimal</compare_price>
      <cost_price>decimal</cost_price>
      <sale_price>decimal</sale_price>
      <weight>decimal</weight>
      <length>decimal</length>
      <width>decimal</width>
      <height>decimal</height>
      <status>draft|published|archived</status>
      <type>simple|variable</type>
      <brand_id>int</brand_id>
      <tax_class>string</tax_class>
      <shipping_class>string</shipping_class>
      <manage_stock>true|false</manage_stock>
      <track_stock>true|false</track_stock>
      <allow_backorder>true|false</allow_backorder>
      <stock_quantity>int</stock_quantity>
      <low_stock_threshold>int</low_stock_threshold>
      <minimum_quantity>int</minimum_quantity>
      <is_visible>true|false</is_visible>
      <is_featured>true|false</is_featured>
      <is_requestable>true|false</is_requestable>
    </base>

    <categories>
      <category_slug>category-a</category_slug>
      <category_slug>category-b</category_slug>
    </categories>

    <translations>
      <translation locale="lt">
        <name>...</name>
        <slug>...</slug>
        <description>...</description>
        <short_description>...</short_description>
        <seo_title>...</seo_title>
        <seo_description>...</seo_description>
      </translation>
      <translation locale="en">...</translation>
    </translations>

    <images>
      <image src="/storage/product-images/1/image-1.jpg" alt="..." />
      <image src="https://example.com/image.jpg" />
    </images>
  </product>
</products>
```

- **Identification**: provide at least one of `sku` or `slug` (if both are empty, the system attempts to create using the LT translation name)
- **Categories**: `category_slug` must match an existing category `slug`
- **Images**: `image` attributes — `src` (required), `alt` (optional)

### Minimal examples

Category:
```xml
<catalog>
  <categories>
    <category>
      <slug>building-material</slug>
      <base><is_enabled>true</is_enabled><is_visible>true</is_visible></base>
      <translations>
        <translation locale="lt"><name>Statybinė medžiaga</name></translation>
        <translation locale="en"><name>Building material</name></translation>
      </translations>
    </category>
  </categories>
  <!-- products block may follow here -->
</catalog>
```

Product:
```xml
<catalog>
  <products>
    <product>
      <sku>SKU-1001</sku>
      <slug>cement-42-5r</slug>
      <base>
        <price>4.99</price>
        <is_visible>true</is_visible>
        <stock_quantity>100</stock_quantity>
      </base>
      <categories>
        <category_slug>building-material</category_slug>
      </categories>
      <translations>
        <translation locale="lt"><name>Cementas 42.5R</name></translation>
        <translation locale="en"><name>Cement 42.5R</name></translation>
      </translations>
      <images>
        <image src="/storage/product-images/1001/image-1.jpg" alt="Cementas" />
      </images>
    </product>
  </products>
</catalog>
```


