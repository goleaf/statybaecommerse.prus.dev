### XML Importer / Exporter (Kategorijos ir Produktai)

Naudokite komandą katalogo XML importui/eksportui su daugiakalbe informacija (lt/en).

#### Komanda

```bash
php artisan catalog:xml import /path/to/catalog.xml --only=all
php artisan catalog:xml export /path/to/out.xml --only=products
```

- `action`: `import` arba `export`.
- `path`: XML failo kelias.
- `--only`: `all` (numatytasis), `categories`, `products`.

#### Palaikomi modeliai
- `App\Models\Category` su vertimais per `category_translations`.
- `App\Models\Product` su vertimais per `product_translations` ir ryšiu su kategorijomis.

#### XML struktūra (schema)

```xml
<catalog>
  <categories>
    <category>
      <slug>elektronika</slug>
      <parent_slug>prekes</parent_slug>
      <base>
        <is_enabled>true</is_enabled>
        <is_visible>true</is_visible>
        <sort_order>1</sort_order>
        <show_in_menu>true</show_in_menu>
        <product_limit>24</product_limit>
      </base>
      <translations>
        <translation locale="lt">
          <name>Elektronika</name>
          <description>Aprašymas LT</description>
          <short_description>Trumpas LT</short_description>
          <seo_title>SEO LT</seo_title>
          <seo_description>SEO aprašymas LT</seo_description>
        </translation>
        <translation locale="en">
          <name>Electronics</name>
          <description>Description EN</description>
          <short_description>Short EN</short_description>
          <seo_title>SEO EN</seo_title>
          <seo_description>SEO description EN</seo_description>
        </translation>
      </translations>
    </category>
  </categories>

  <products>
    <product>
      <sku>ABC-123</sku>
      <slug>telefonas-abc-123</slug>
      <base>
        <price>199.99</price>
        <sale_price>179.99</sale_price>
        <compare_price>249.99</compare_price>
        <weight>0.3</weight>
        <length>12.0</length>
        <width>6.0</width>
        <height>0.8</height>
        <status>published</status>
        <type>simple</type>
        <brand_id>1</brand_id>
        <tax_class>standard</tax_class>
        <shipping_class>default</shipping_class>
        <manage_stock>true</manage_stock>
        <track_stock>true</track_stock>
        <allow_backorder>false</allow_backorder>
        <stock_quantity>50</stock_quantity>
        <low_stock_threshold>5</low_stock_threshold>
        <minimum_quantity>1</minimum_quantity>
        <is_visible>true</is_visible>
        <is_featured>false</is_featured>
        <is_requestable>false</is_requestable>
      </base>
      <categories>
        <category_slug>elektronika</category_slug>
      </categories>
      <translations>
        <translation locale="lt">
          <name>Telefonas ABC</name>
          <slug>telefonas-abc</slug>
          <description>LT aprašymas</description>
          <short_description>LT trumpas</short_description>
          <seo_title>LT SEO</seo_title>
          <seo_description>LT SEO aprašymas</seo_description>
        </translation>
        <translation locale="en">
          <name>Phone ABC</name>
          <slug>phone-abc</slug>
          <description>EN description</description>
          <short_description>EN short</short_description>
          <seo_title>EN SEO</seo_title>
          <seo_description>EN SEO description</seo_description>
        </translation>
      </translations>
    </product>
  </products>
</catalog>
```

#### Taisyklės
- Pirmenybė `lt` vertei kai kuriant naujus įrašus; `en` neprivaloma.
- Kategorijų tėvai nustatomi pagal `parent_slug` antruoju praėjimu.
- Produktų kategorijos susiejamos pagal `category_slug`.
- Bool reikšmės: `true/false`, `1/0`, `yes/no`, `y/n`.

#### Patarimai
- Eksportuokite esamą katalogą, pataisykite XML ir importuokite atgal.
- Importas vykdomas tranzakcijoje; klaida anuliuos visą failą.
- Laikai ir paveikslėliai neperkeliami per šį XML.


