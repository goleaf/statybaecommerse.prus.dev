# Import XML Formats

This folder contains example XML payloads for importing brands, categories, and products with images, variants, options, inventories, SEO, relations, price lists, and translations (lt/en).

## Import Order
1. brands.xml
2. categories.xml
3. products.xml

This ensures brand/category references by ID/slug resolve during product import.

## Brands XML (`brands.xml` / `brands.empty.xml`)

Root: `<brands>` with multiple `<brand>` entries.

- `<id>`: External brand ID used for product `<base><brand_id>` links (optional if using slug).
- `<slug>`: Unique brand slug. Prefer to link products via `<brand><brand_slug>`.
- `<translations>` (1+):
  - `<translation locale="lt|en">`
    - `name` (string)
- Optional blocks (supported by schema if you extend importer):
  - `<images><image src="" alt="" /></images>`
  - `<seo><canonical_url/></seo>`

Example:
```xml
<brands>
  <brand>
    <id>1</id>
    <slug>generic</slug>
    <translations>
      <translation locale="lt"><name>Bendras</name></translation>
      <translation locale="en"><name>Generic</name></translation>
    </translations>
  </brand>
</brands>
```

Linking from products (two options):
- Slug (preferred): `<brand><brand_slug>generic</brand_slug></brand>`
- ID: `<base><brand_id>1</brand_id></base>` where `1` matches `<id>` above

---

## Categories XML (`categories.xml`)

Root: `<categories>` with multiple `<category>` entries.

- Identity / hierarchy
  - `<id>`: Optional external ID (for mapping only)
  - `<parent_id>`: Optional; or use `<parent_slug>`
  - `<slug>`: Required unique slug (fallback: slugify LT name)
- `<base>` (optional):
  - `is_enabled` (true|false)
  - `is_visible` (true|false)
  - `sort_order` (int)
  - `show_in_menu` (true|false)
  - `product_limit` (int)
- `<translations>` (1+):
  - `<translation locale="lt|en"> name, description, short_description, seo_title, seo_description`
- Optional relations/metadata:
  - `<seo><canonical_url/></seo>`
  - `<images><image src="" alt="" /></images>`
  - `<children><child_slug>cementas</child_slug></children>` (helper list)
  - `<tags><tag>statyba</tag></tags>`
  - `<custom><field key="menu_icon">heroicon-o-cube</field></custom>`

---

## Products XML (`products.xml` / `products.empty.xml`)

Root: `<products>` with multiple `<product>` entries.

- Identity
  - `<id>` (external), `<sku>`, `<slug>`
- Categorization
  - By slugs (preferred): `<categories><category_slug>cementas</category_slug></categories>`
  - By IDs: `<category_ids><category_id>2</category_id></category_ids>`
- Brand
  - Slug: `<brand><brand_slug>generic</brand_slug></brand>`
  - Or ID in `<base>`: `<brand_id>1</brand_id>`
- `<base>` (optional)
  - Pricing: `price`, `sale_price`, `compare_price`, `cost_price`, `currency`
  - Physical: `weight`, `length`, `width`, `height`
  - Meta: `status`, `brand_id`, `tax_class`, `shipping_class`
  - Stock flags: `manage_stock`, `track_stock`, `allow_backorder` (true|false)
  - Stock values: `stock_quantity`, `low_stock_threshold`, `minimum_quantity`
  - Visibility: `is_visible`, `is_featured`, `is_requestable` (true|false)
- `<dimensions>` (optional): `weight/length/width/height`
- `<inventory>` (optional)
  - `barcode`, `sku_supplier`
  - Warehouses: `<warehouses><warehouse code="default"><stock_quantity/></warehouse></warehouses>`
- `<translations>` (1+): `name, slug, description, short_description, seo_title, seo_description`
- `<seo>` (optional): `canonical_url` and `<meta><key/><value/></meta>`
- `<images>` (0+): `<image src="ABS_OR_REL_URL" alt="..." is_primary="true" sort_order="1"/>`
- `<files>` / `<videos>` (optional)
  - `<files><file url="" label="manual"/></files>`
  - `<videos><video url="" label="demo"/></videos>`
- `<attributes>` (0+)
  - `<attribute id="" code=""><name locale="lt|en"/> <value id="" locale="lt|en"/></attribute>`
- `<options>` (0+)
  - `<option name="spalva"><value locale="lt">raudona</value></option>`
- `<variants>` (0+)
  - `<variant><sku/><option_values><option name="spalva" locale="lt">raudona</option></option_values><price/><sale_price/><stock_quantity/><images>..</images></variant>`
- `<relations>` (optional)
  - `<related_skus><sku/></related_skus>`
  - `<upsell_skus><sku/></upsell_skus>`
  - `<cross_sell_skus><sku/></cross_sell_skus>`
- `<price_lists>` (optional)
  - `<price_list code="retail"><currency>EUR</currency><price/></price_list>`
- `<tags>` / `<custom>` (optional)
  - `<tags><tag/></tags>`, `<custom><field key="warranty_months">24</field></custom>`

### Linking Summary
- Category: `category_slug` (preferred) or `category_id`
- Brand: `brand_slug` (preferred) or `brand_id`

### Best Practices
- Always provide LT translations; add EN where available.
- Use stable slugs for brands/categories; keep consistent across imports.
- Keep image URLs absolute where possible; enable image download in admin when importing.

---

## Workflow (Admin → Data Import/Export)
1. Select provider XML.
2. Choose scope: all / categories / products.
3. Optionally enable image download.
4. Run import; review notifications for created/updated counts.

## Notes
- Currency default: EUR; Language default: lt.
- Export service: `App\Services\XmlCatalogService` already supports products/categories and can be extended to parse additional blocks shown above.
