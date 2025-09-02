CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar,
  "preferred_locale" varchar,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "first_name" varchar,
  "last_name" varchar not null,
  "gender" varchar not null default 'male',
  "phone_number" varchar,
  "birth_date" date,
  "avatar_type" varchar not null default 'avatar_ui',
  "avatar_location" varchar,
  "timezone" varchar,
  "opt_in" tinyint(1) not null default '0',
  "last_login_at" datetime,
  "last_login_ip" varchar,
  "two_factor_secret" text,
  "two_factor_recovery_codes" text
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "group_name" varchar,
  "display_name" varchar,
  "description" varchar,
  "can_be_removed" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "display_name" varchar,
  "description" text,
  "can_be_removed" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "sh_countries"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "name_official" varchar not null,
  "region" varchar not null,
  "subregion" varchar,
  "cca2" varchar not null,
  "cca3" varchar not null,
  "flag" varchar not null,
  "latitude" numeric not null,
  "longitude" numeric not null,
  "phone_calling_code" text not null,
  "currencies" text not null,
  "deleted_at" datetime
);
CREATE TABLE IF NOT EXISTS "sh_settings"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "key" varchar not null,
  "display_name" varchar,
  "value" text,
  "locked" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "sh_settings_key_unique" on "sh_settings"("key");
CREATE TABLE IF NOT EXISTS "sh_user_addresses"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "last_name" varchar not null,
  "first_name" varchar not null,
  "company_name" varchar,
  "street_address" varchar not null,
  "street_address_plus" varchar,
  "postal_code" varchar not null,
  "city" varchar not null,
  "phone_number" varchar,
  "shipping_default" tinyint(1) not null default '0',
  "billing_default" tinyint(1) not null default '0',
  "type" varchar,
  "country_id" integer,
  "user_id" integer not null,
  "metadata" text,
  foreign key("country_id") references "sh_countries"("id") on delete set null,
  foreign key("user_id") references "users"("id") on delete CASCADE
);
CREATE INDEX "sh_user_addresses_country_id_index" on "sh_user_addresses"(
  "country_id"
);
CREATE INDEX "sh_user_addresses_user_id_index" on "sh_user_addresses"(
  "user_id"
);
CREATE TABLE IF NOT EXISTS "sh_channels"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "timezone" varchar,
  "url" varchar,
  "is_default" tinyint(1) not null default '0',
  "metadata" text,
  "is_enabled" tinyint(1) not null default '0',
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "sh_channels_slug_unique" on "sh_channels"("slug");
CREATE TABLE IF NOT EXISTS "sh_inventories"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "code" varchar not null,
  "description" text,
  "email" varchar not null,
  "street_address" varchar not null,
  "street_address_plus" varchar,
  "postal_code" varchar not null,
  "city" varchar not null,
  "phone_number" varchar,
  "priority" integer not null default '0',
  "latitude" numeric,
  "longitude" numeric,
  "is_default" tinyint(1) not null default '0',
  "country_id" integer,
  foreign key("country_id") references "sh_countries"("id") on delete set null
);
CREATE UNIQUE INDEX "sh_inventories_code_unique" on "sh_inventories"("code");
CREATE UNIQUE INDEX "sh_inventories_email_unique" on "sh_inventories"("email");
CREATE INDEX "sh_inventories_country_id_index" on "sh_inventories"(
  "country_id"
);
CREATE TABLE IF NOT EXISTS "sh_inventory_histories"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "stockable_type" varchar not null,
  "stockable_id" integer not null,
  "reference_type" varchar,
  "reference_id" integer,
  "quantity" integer not null,
  "old_quantity" integer not null default '0',
  "event" varchar,
  "description" text,
  "inventory_id" integer not null,
  "user_id" integer not null,
  foreign key("inventory_id") references "sh_inventories"("id") on delete CASCADE,
  foreign key("user_id") references "users"("id") on delete CASCADE
);
CREATE INDEX "sh_inventory_histories_stockable_type_stockable_id_index" on "sh_inventory_histories"(
  "stockable_type",
  "stockable_id"
);
CREATE INDEX "sh_inventory_histories_reference_type_reference_id_index" on "sh_inventory_histories"(
  "reference_type",
  "reference_id"
);
CREATE INDEX "sh_inventory_histories_inventory_id_index" on "sh_inventory_histories"(
  "inventory_id"
);
CREATE INDEX "sh_inventory_histories_user_id_index" on "sh_inventory_histories"(
  "user_id"
);
CREATE TABLE IF NOT EXISTS "sh_categories"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "position" integer not null default '0',
  "is_enabled" tinyint(1) not null default '0',
  "seo_title" varchar,
  "seo_description" varchar,
  "parent_id" integer,
  "metadata" text,
  foreign key("parent_id") references "sh_categories"("id") on delete set null
);
CREATE UNIQUE INDEX "sh_categories_slug_unique" on "sh_categories"("slug");
CREATE INDEX "sh_categories_parent_id_index" on "sh_categories"("parent_id");
CREATE TABLE IF NOT EXISTS "sh_brands"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "website" varchar,
  "description" text,
  "position" integer not null default '0',
  "is_enabled" tinyint(1) not null default '0',
  "seo_title" varchar,
  "seo_description" varchar,
  "metadata" text
);
CREATE UNIQUE INDEX "sh_brands_slug_unique" on "sh_brands"("slug");
CREATE TABLE IF NOT EXISTS "sh_collections"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "type" varchar not null,
  "sort" varchar,
  "match_conditions" varchar,
  "published_at" datetime not null default '2025-09-01 19:15:54',
  "seo_title" varchar,
  "seo_description" varchar,
  "metadata" text
);
CREATE UNIQUE INDEX "sh_collections_slug_unique" on "sh_collections"("slug");
CREATE TABLE IF NOT EXISTS "sh_collection_rules"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "rule" varchar not null,
  "operator" varchar not null,
  "value" varchar not null,
  "collection_id" integer not null,
  foreign key("collection_id") references "sh_collections"("id") on delete CASCADE
);
CREATE INDEX "sh_collection_rules_collection_id_index" on "sh_collection_rules"(
  "collection_id"
);
CREATE TABLE IF NOT EXISTS "sh_attributes"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "description" varchar,
  "type" varchar not null,
  "is_enabled" tinyint(1) not null default '0',
  "is_searchable" tinyint(1) not null default '0',
  "is_filterable" tinyint(1) not null default '0',
  "icon" varchar
);
CREATE UNIQUE INDEX "sh_attributes_slug_unique" on "sh_attributes"("slug");
CREATE TABLE IF NOT EXISTS "sh_attribute_values"(
  "id" integer primary key autoincrement not null,
  "value" varchar not null,
  "key" varchar not null,
  "position" integer default '1',
  "attribute_id" integer not null,
  foreign key("attribute_id") references "sh_attributes"("id") on delete CASCADE
);
CREATE UNIQUE INDEX "sh_attribute_values_key_unique" on "sh_attribute_values"(
  "key"
);
CREATE INDEX "sh_attribute_values_attribute_id_index" on "sh_attribute_values"(
  "attribute_id"
);
CREATE TABLE IF NOT EXISTS "sh_carriers"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "logo" varchar,
  "link_url" varchar,
  "description" varchar,
  "shipping_amount" integer,
  "is_enabled" tinyint(1) not null default '0',
  "metadata" text
);
CREATE UNIQUE INDEX "sh_carriers_slug_unique" on "sh_carriers"("slug");
CREATE TABLE IF NOT EXISTS "sh_payment_methods"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "title" varchar not null,
  "slug" varchar,
  "logo" varchar,
  "link_url" varchar,
  "description" text,
  "instructions" text,
  "is_enabled" tinyint(1) not null default '0',
  "metadata" text
);
CREATE UNIQUE INDEX "sh_payment_methods_slug_unique" on "sh_payment_methods"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_discountables"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "condition" varchar,
  "total_use" integer not null default '0',
  "discountable_type" varchar not null,
  "discountable_id" integer not null,
  "discount_id" integer not null,
  foreign key("discount_id") references "sh_discounts"("id") on delete CASCADE
);
CREATE INDEX "sh_discountables_discountable_type_discountable_id_index" on "sh_discountables"(
  "discountable_type",
  "discountable_id"
);
CREATE INDEX "sh_discountables_discount_id_index" on "sh_discountables"(
  "discount_id"
);
CREATE TABLE IF NOT EXISTS "sh_reviews"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "is_recommended" tinyint(1) not null default '0',
  "rating" integer not null,
  "title" text,
  "content" text,
  "approved" tinyint(1) not null default '0',
  "reviewrateable_type" varchar not null,
  "reviewrateable_id" integer not null,
  "author_type" varchar not null,
  "author_id" integer not null,
  "locale" varchar
);
CREATE INDEX "sh_reviews_reviewrateable_type_reviewrateable_id_index" on "sh_reviews"(
  "reviewrateable_type",
  "reviewrateable_id"
);
CREATE INDEX "sh_reviews_author_type_author_id_index" on "sh_reviews"(
  "author_type",
  "author_id"
);
CREATE TABLE IF NOT EXISTS "sh_order_shipping"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "shipped_at" datetime not null,
  "received_at" datetime,
  "returned_at" datetime,
  "tracking_number" varchar,
  "tracking_url" varchar,
  "voucher" text,
  "order_id" integer not null,
  "carrier_id" integer,
  foreign key("order_id") references "sh_orders"("id") on delete CASCADE,
  foreign key("carrier_id") references "sh_carriers"("id") on delete set null
);
CREATE INDEX "sh_order_shipping_order_id_index" on "sh_order_shipping"(
  "order_id"
);
CREATE INDEX "sh_order_shipping_carrier_id_index" on "sh_order_shipping"(
  "carrier_id"
);
CREATE TABLE IF NOT EXISTS "sh_users_geolocation_history"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ip_api" text,
  "extreme_ip_lookup" text,
  "user_id" integer not null,
  "order_id" integer,
  foreign key("user_id") references "users"("id") on delete CASCADE,
  foreign key("order_id") references "sh_orders"("id") on delete set null
);
CREATE INDEX "sh_users_geolocation_history_user_id_index" on "sh_users_geolocation_history"(
  "user_id"
);
CREATE INDEX "sh_users_geolocation_history_order_id_index" on "sh_users_geolocation_history"(
  "order_id"
);
CREATE TABLE IF NOT EXISTS "sh_order_refunds"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "reason" text,
  "amount" integer not null,
  "currency" varchar not null,
  "status" varchar not null,
  "notes" text,
  "order_id" integer not null,
  "user_id" integer,
  "metadata" text,
  foreign key("order_id") references "sh_orders"("id") on delete CASCADE,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "sh_order_refunds_order_id_index" on "sh_order_refunds"(
  "order_id"
);
CREATE INDEX "sh_order_refunds_user_id_index" on "sh_order_refunds"("user_id");
CREATE TABLE IF NOT EXISTS "sh_legals"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "slug" varchar,
  "content" text,
  "is_enabled" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "sh_legals_slug_unique" on "sh_legals"("slug");
CREATE TABLE IF NOT EXISTS "sh_product_has_relations"(
  "product_id" integer not null,
  "productable_type" varchar not null,
  "productable_id" integer not null,
  foreign key("product_id") references "sh_products"("id") on delete CASCADE
);
CREATE INDEX "sh_product_has_relations_productable_type_productable_id_index" on "sh_product_has_relations"(
  "productable_type",
  "productable_id"
);
CREATE INDEX "sh_product_has_relations_product_id_index" on "sh_product_has_relations"(
  "product_id"
);
CREATE TABLE IF NOT EXISTS "sh_attribute_product"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "product_id" integer not null,
  "attribute_value_id" integer,
  "attribute_custom_value" text,
  foreign key("attribute_id") references "sh_attributes"("id") on delete CASCADE,
  foreign key("product_id") references "sh_products"("id") on delete CASCADE,
  foreign key("attribute_value_id") references "sh_attribute_values"("id") on delete CASCADE
);
CREATE INDEX "sh_attribute_product_attribute_id_index" on "sh_attribute_product"(
  "attribute_id"
);
CREATE INDEX "sh_attribute_product_product_id_index" on "sh_attribute_product"(
  "product_id"
);
CREATE INDEX "sh_attribute_product_attribute_value_id_index" on "sh_attribute_product"(
  "attribute_value_id"
);
CREATE TABLE IF NOT EXISTS "sh_zones"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "code" varchar,
  "is_enabled" tinyint(1) not null default '0',
  "metadata" text,
  "currency_id" integer not null,
  "tax_rate" numeric not null default '0',
  "shipping_rate" numeric not null default '0',
  "is_default" tinyint(1) not null default '0',
  foreign key("currency_id") references "sh_currencies"("id") on delete CASCADE
);
CREATE UNIQUE INDEX "sh_zones_name_unique" on "sh_zones"("name");
CREATE UNIQUE INDEX "sh_zones_slug_unique" on "sh_zones"("slug");
CREATE UNIQUE INDEX "sh_zones_code_unique" on "sh_zones"("code");
CREATE INDEX "sh_zones_currency_id_index" on "sh_zones"("currency_id");
CREATE TABLE IF NOT EXISTS "sh_zone_has_relations"(
  "zone_id" integer not null,
  "zonable_type" varchar not null,
  "zonable_id" integer not null,
  foreign key("zone_id") references "sh_zones"("id") on delete CASCADE
);
CREATE INDEX "sh_zone_has_relations_zonable_type_zonable_id_index" on "sh_zone_has_relations"(
  "zonable_type",
  "zonable_id"
);
CREATE INDEX "sh_zone_has_relations_zone_id_index" on "sh_zone_has_relations"(
  "zone_id"
);
CREATE TABLE IF NOT EXISTS "sh_carrier_options"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "description" varchar,
  "is_enabled" tinyint(1) not null default '0',
  "price" integer not null,
  "carrier_id" integer not null,
  "zone_id" integer not null,
  "metadata" text
);
CREATE UNIQUE INDEX "sh_carrier_options_name_unique" on "sh_carrier_options"(
  "name"
);
CREATE TABLE IF NOT EXISTS "sh_order_addresses"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "customer_id" integer,
  "last_name" varchar not null,
  "first_name" varchar not null,
  "company" varchar,
  "street_address" varchar not null,
  "street_address_plus" varchar,
  "postal_code" varchar not null,
  "city" varchar not null,
  "phone" varchar,
  "country_name" varchar,
  foreign key("customer_id") references "users"("id") on delete set null
);
CREATE INDEX "sh_order_addresses_customer_id_index" on "sh_order_addresses"(
  "customer_id"
);
CREATE TABLE IF NOT EXISTS "sh_orders"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "number" varchar not null,
  "price_amount" integer,
  "status" varchar not null,
  "currency_code" varchar not null,
  "notes" text,
  "parent_order_id" integer,
  "payment_method_id" integer,
  "channel_id" integer,
  "customer_id" integer,
  "metadata" text,
  "zone_id" integer,
  "billing_address_id" integer,
  "shipping_address_id" integer,
  "shipping_option_id" integer,
  "canceled_at" datetime,
  "payment_method" varchar,
  "payment_status" varchar not null default 'pending',
  "transactions" text,
  "timeline" text,
  "subtotal_amount" numeric not null default '0',
  "discount_total_amount" numeric not null default '0',
  "tax_total_amount" numeric not null default '0',
  "shipping_total_amount" numeric not null default '0',
  "grand_total_amount" numeric not null default '0',
  foreign key("customer_id") references users("id") on delete set null on update no action,
  foreign key("channel_id") references sh_channels("id") on delete set null on update no action,
  foreign key("payment_method_id") references sh_payment_methods("id") on delete set null on update no action,
  foreign key("parent_order_id") references sh_orders("id") on delete set null on update no action,
  foreign key("zone_id") references "sh_zones"("id") on delete set null,
  foreign key("billing_address_id") references "sh_order_addresses"("id") on delete set null,
  foreign key("shipping_address_id") references "sh_order_addresses"("id") on delete set null,
  foreign key("shipping_option_id") references "sh_carrier_options"("id") on delete set null
);
CREATE INDEX "sh_orders_channel_id_index" on "sh_orders"("channel_id");
CREATE INDEX "sh_orders_customer_id_index" on "sh_orders"("customer_id");
CREATE INDEX "sh_orders_parent_order_id_index" on "sh_orders"(
  "parent_order_id"
);
CREATE INDEX "sh_orders_payment_method_id_index" on "sh_orders"(
  "payment_method_id"
);
CREATE INDEX "sh_orders_zone_id_index" on "sh_orders"("zone_id");
CREATE INDEX "sh_orders_billing_address_id_index" on "sh_orders"(
  "billing_address_id"
);
CREATE INDEX "sh_orders_shipping_address_id_index" on "sh_orders"(
  "shipping_address_id"
);
CREATE INDEX "sh_orders_shipping_option_id_index" on "sh_orders"(
  "shipping_option_id"
);
CREATE TABLE IF NOT EXISTS "sh_discounts"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "is_active" tinyint(1) not null default('0'),
  "code" varchar not null,
  "type" varchar not null,
  "value" integer not null,
  "apply_to" varchar not null,
  "min_required" varchar not null,
  "min_required_value" varchar,
  "eligibility" varchar not null,
  "usage_limit" integer,
  "usage_limit_per_user" tinyint(1) not null default('0'),
  "total_use" integer not null default('0'),
  "start_at" datetime not null,
  "end_at" datetime,
  "metadata" text,
  "zone_id" integer,
  "priority" integer not null default '100',
  "exclusive" tinyint(1) not null default '0',
  "applies_to_shipping" tinyint(1) not null default '0',
  "free_shipping" tinyint(1) not null default '0',
  "first_order_only" tinyint(1) not null default '0',
  "per_customer_limit" integer,
  "per_code_limit" integer,
  "per_day_limit" integer,
  "channel_restrictions" text,
  "currency_restrictions" text,
  "weekday_mask" varchar,
  "time_window" text,
  foreign key("zone_id") references "sh_zones"("id") on delete set null
);
CREATE UNIQUE INDEX "sh_discounts_code_unique" on "sh_discounts"("code");
CREATE INDEX "sh_discounts_zone_id_index" on "sh_discounts"("zone_id");
CREATE TABLE IF NOT EXISTS "sh_products"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "name" varchar not null,
  "slug" varchar,
  "sku" varchar,
  "barcode" varchar,
  "description" text,
  "security_stock" integer default('0'),
  "featured" tinyint(1) not null default('0'),
  "is_visible" tinyint(1) not null default('0'),
  "type" varchar,
  "published_at" datetime not null default('2025-09-01 19:15:54'),
  "seo_title" varchar,
  "seo_description" varchar,
  "weight_unit" varchar not null default('kg'),
  "weight_value" numeric default('0'),
  "height_unit" varchar not null default('cm'),
  "height_value" numeric default('0'),
  "width_unit" varchar not null default('cm'),
  "width_value" numeric default('0'),
  "depth_unit" varchar not null default('cm'),
  "depth_value" numeric default('0'),
  "volume_unit" varchar not null default('l'),
  "volume_value" numeric default('0'),
  "brand_id" integer,
  "metadata" text,
  "summary" text,
  "external_id" varchar,
  "warehouse_quantity" integer,
  foreign key("brand_id") references sh_brands("id") on delete set null on update no action
);
CREATE UNIQUE INDEX "sh_products_barcode_unique" on "sh_products"("barcode");
CREATE INDEX "sh_products_brand_id_index" on "sh_products"("brand_id");
CREATE UNIQUE INDEX "sh_products_sku_unique" on "sh_products"("sku");
CREATE UNIQUE INDEX "sh_products_slug_unique" on "sh_products"("slug");
CREATE TABLE IF NOT EXISTS "sh_product_variants"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar not null,
  "sku" varchar,
  "barcode" varchar,
  "ean" varchar,
  "upc" varchar,
  "allow_backorder" tinyint(1) not null default '0',
  "position" integer not null default '1',
  "product_id" integer,
  "weight_unit" varchar not null default 'kg',
  "weight_value" numeric default '0',
  "height_unit" varchar not null default 'cm',
  "height_value" numeric default '0',
  "width_unit" varchar not null default 'cm',
  "width_value" numeric default '0',
  "depth_unit" varchar not null default 'cm',
  "depth_value" numeric default '0',
  "volume_unit" varchar not null default 'l',
  "volume_value" numeric default '0',
  "metadata" text,
  "status" varchar not null default 'active',
  foreign key("product_id") references "sh_products"("id") on delete set null
);
CREATE INDEX "sh_product_variants_name_index" on "sh_product_variants"("name");
CREATE UNIQUE INDEX "sh_product_variants_sku_unique" on "sh_product_variants"(
  "sku"
);
CREATE UNIQUE INDEX "sh_product_variants_barcode_unique" on "sh_product_variants"(
  "barcode"
);
CREATE UNIQUE INDEX "sh_product_variants_ean_unique" on "sh_product_variants"(
  "ean"
);
CREATE UNIQUE INDEX "sh_product_variants_upc_unique" on "sh_product_variants"(
  "upc"
);
CREATE INDEX "sh_product_variants_product_id_index" on "sh_product_variants"(
  "product_id"
);
CREATE TABLE IF NOT EXISTS "sh_attribute_value_product_variant"(
  "id" integer primary key autoincrement not null,
  "value_id" integer not null,
  "variant_id" integer not null,
  foreign key("value_id") references "sh_attribute_values"("id") on delete CASCADE,
  foreign key("variant_id") references "sh_product_variants"("id") on delete CASCADE
);
CREATE INDEX "sh_attribute_value_product_variant_value_id_index" on "sh_attribute_value_product_variant"(
  "value_id"
);
CREATE INDEX "sh_attribute_value_product_variant_variant_id_index" on "sh_attribute_value_product_variant"(
  "variant_id"
);
CREATE TABLE IF NOT EXISTS "sh_currencies"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "symbol" varchar not null,
  "format" varchar not null,
  "exchange_rate" numeric,
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "decimal_places" integer not null default '2',
  "deleted_at" datetime
);
CREATE INDEX "sh_currencies_code_index" on "sh_currencies"("code");
CREATE UNIQUE INDEX "sh_currencies_code_unique" on "sh_currencies"("code");
CREATE TABLE IF NOT EXISTS "media"(
  "id" integer primary key autoincrement not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  "uuid" varchar,
  "collection_name" varchar not null,
  "name" varchar not null,
  "file_name" varchar not null,
  "mime_type" varchar,
  "disk" varchar not null,
  "conversions_disk" varchar,
  "size" integer not null,
  "manipulations" text not null,
  "custom_properties" text not null,
  "generated_conversions" text not null,
  "responsive_images" text not null,
  "order_column" integer,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "media_model_type_model_id_index" on "media"(
  "model_type",
  "model_id"
);
CREATE UNIQUE INDEX "media_uuid_unique" on "media"("uuid");
CREATE INDEX "media_order_column_index" on "media"("order_column");
CREATE TABLE IF NOT EXISTS "telescope_entries"(
  "sequence" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "batch_id" varchar not null,
  "family_hash" varchar,
  "should_display_on_index" tinyint(1) not null default '1',
  "type" varchar not null,
  "content" text not null,
  "created_at" datetime
);
CREATE UNIQUE INDEX "telescope_entries_uuid_unique" on "telescope_entries"(
  "uuid"
);
CREATE INDEX "telescope_entries_batch_id_index" on "telescope_entries"(
  "batch_id"
);
CREATE INDEX "telescope_entries_family_hash_index" on "telescope_entries"(
  "family_hash"
);
CREATE INDEX "telescope_entries_created_at_index" on "telescope_entries"(
  "created_at"
);
CREATE INDEX "telescope_entries_type_should_display_on_index_index" on "telescope_entries"(
  "type",
  "should_display_on_index"
);
CREATE TABLE IF NOT EXISTS "telescope_entries_tags"(
  "entry_uuid" varchar not null,
  "tag" varchar not null,
  foreign key("entry_uuid") references "telescope_entries"("uuid") on delete cascade,
  primary key("entry_uuid", "tag")
);
CREATE INDEX "telescope_entries_tags_tag_index" on "telescope_entries_tags"(
  "tag"
);
CREATE TABLE IF NOT EXISTS "telescope_monitoring"(
  "tag" varchar not null,
  primary key("tag")
);
CREATE INDEX "sh_discounts_status_window_index" on "sh_discounts"(
  "status",
  "starts_at",
  "ends_at"
);
CREATE INDEX "sh_discounts_priority_index" on "sh_discounts"("priority");
CREATE TABLE IF NOT EXISTS "sh_discount_conditions"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer not null,
  "type" varchar not null,
  "operator" varchar not null,
  "value" text,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_id") references "sh_discounts"("id") on delete cascade
);
CREATE INDEX "sh_discount_conditions_discount_id_type_index" on "sh_discount_conditions"(
  "discount_id",
  "type"
);
CREATE TABLE IF NOT EXISTS "sh_discount_codes"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer not null,
  "code" varchar not null,
  "expires_at" datetime,
  "max_uses" integer,
  "usage_count" integer not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_id") references "sh_discounts"("id") on delete cascade
);
CREATE INDEX "sh_discount_codes_discount_id_index" on "sh_discount_codes"(
  "discount_id"
);
CREATE UNIQUE INDEX "sh_discount_codes_code_unique" on "sh_discount_codes"(
  "code"
);
CREATE TABLE IF NOT EXISTS "sh_discount_redemptions"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer not null,
  "code_id" integer,
  "order_id" integer not null,
  "user_id" integer,
  "amount_saved" numeric not null,
  "currency_code" varchar not null,
  "redeemed_at" datetime not null,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_id") references "sh_discounts"("id"),
  foreign key("code_id") references "sh_discount_codes"("id")
);
CREATE INDEX "sh_discount_redemptions_order_id_index" on "sh_discount_redemptions"(
  "order_id"
);
CREATE INDEX "sh_discount_redemptions_user_id_index" on "sh_discount_redemptions"(
  "user_id"
);
CREATE INDEX "sh_discount_redemptions_discount_id_code_id_index" on "sh_discount_redemptions"(
  "discount_id",
  "code_id"
);
CREATE TABLE IF NOT EXISTS "sh_discount_campaigns"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "starts_at" datetime,
  "ends_at" datetime,
  "channel_id" integer,
  "zone_id" integer,
  "status" varchar not null default 'active',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "sh_discount_campaigns_slug_unique" on "sh_discount_campaigns"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_campaign_discount"(
  "campaign_id" integer not null,
  "discount_id" integer not null,
  primary key("campaign_id", "discount_id")
);
CREATE TABLE IF NOT EXISTS "sh_brand_translations"(
  "id" integer primary key autoincrement not null,
  "brand_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_brand_translations_locale_index" on "sh_brand_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_brand_translations_brand_id_locale_unique" on "sh_brand_translations"(
  "brand_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_brand_translations_locale_slug_unique" on "sh_brand_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_category_translations"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_category_translations_locale_index" on "sh_category_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_category_translations_category_id_locale_unique" on "sh_category_translations"(
  "category_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_category_translations_locale_slug_unique" on "sh_category_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_collection_translations"(
  "id" integer primary key autoincrement not null,
  "collection_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_collection_translations_locale_index" on "sh_collection_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_collection_translations_collection_id_locale_unique" on "sh_collection_translations"(
  "collection_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_collection_translations_locale_slug_unique" on "sh_collection_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_attribute_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_attribute_translations_locale_index" on "sh_attribute_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_attribute_translations_attribute_id_locale_unique" on "sh_attribute_translations"(
  "attribute_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "sh_attribute_value_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_value_id" integer not null,
  "locale" varchar not null,
  "value" varchar not null,
  "key" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_attribute_value_translations_locale_index" on "sh_attribute_value_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_attribute_value_translations_attribute_value_id_locale_unique" on "sh_attribute_value_translations"(
  "attribute_value_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "sh_product_translations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "summary" text,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_product_translations_locale_index" on "sh_product_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_product_translations_product_id_locale_unique" on "sh_product_translations"(
  "product_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_product_translations_locale_slug_unique" on "sh_product_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_legal_translations"(
  "id" integer primary key autoincrement not null,
  "legal_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "slug" varchar not null,
  "content" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_legal_translations_locale_index" on "sh_legal_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_legal_translations_legal_id_locale_unique" on "sh_legal_translations"(
  "legal_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_legal_translations_locale_slug_unique" on "sh_legal_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "sh_customer_groups"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "discount_rate" numeric not null default '0',
  "is_enabled" tinyint(1) not null default '1'
);
CREATE UNIQUE INDEX "sh_customer_groups_code_unique" on "sh_customer_groups"(
  "code"
);
CREATE TABLE IF NOT EXISTS "sh_customer_group_user"(
  "group_id" integer not null,
  "user_id" integer not null,
  primary key("group_id", "user_id")
);
CREATE TABLE IF NOT EXISTS "sh_partners"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "tier" varchar check("tier" in('gold', 'silver', 'bronze', 'custom')) not null default 'custom',
  "user_id" integer,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "sh_partners_code_unique" on "sh_partners"("code");
CREATE TABLE IF NOT EXISTS "sh_partner_users"(
  "partner_id" integer not null,
  "user_id" integer not null,
  primary key("partner_id", "user_id")
);
CREATE TABLE IF NOT EXISTS "sh_partner_tiers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "priority" integer not null default '100',
  "default_discount_pct" numeric not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "sh_price_lists"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "currency_id" integer not null,
  "zone_id" integer,
  "priority" integer not null default '100',
  "is_enabled" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "sh_price_list_items"(
  "id" integer primary key autoincrement not null,
  "price_list_id" integer not null,
  "product_id" integer,
  "variant_id" integer,
  "net_amount" numeric not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_price_list_items_price_list_id_index" on "sh_price_list_items"(
  "price_list_id"
);
CREATE INDEX "sh_price_list_items_product_id_index" on "sh_price_list_items"(
  "product_id"
);
CREATE INDEX "sh_price_list_items_variant_id_index" on "sh_price_list_items"(
  "variant_id"
);
CREATE TABLE IF NOT EXISTS "sh_group_price_list"(
  "group_id" integer not null,
  "price_list_id" integer not null,
  primary key("group_id", "price_list_id")
);
CREATE TABLE IF NOT EXISTS "sh_partner_price_list"(
  "partner_id" integer not null,
  "price_list_id" integer not null,
  primary key("partner_id", "price_list_id")
);
CREATE INDEX sh_products_published_at_index ON sh_products(published_at);
CREATE INDEX sh_products_visible_published_index ON sh_products(
  is_visible,
  published_at
);
CREATE INDEX sh_prod_trans_product_id_index ON sh_product_translations(
  product_id
);
CREATE INDEX sh_prod_trans_locale_index ON sh_product_translations(locale);
CREATE INDEX sh_price_lists_currency_zone_priority ON sh_price_lists(
  currency_id,
  zone_id,
  priority
);
CREATE INDEX sh_price_lists_is_enabled ON sh_price_lists(is_enabled);
CREATE INDEX idx_gpl_group_price ON sh_group_price_list(
  group_id,
  price_list_id
);
CREATE INDEX idx_ppl_partner_price ON sh_partner_price_list(
  partner_id,
  price_list_id
);
CREATE INDEX idx_cgu_group_user ON sh_customer_group_user(group_id,user_id);
CREATE INDEX idx_cgu_user ON sh_customer_group_user(user_id);
CREATE INDEX idx_pu_partner_user ON sh_partner_users(partner_id,user_id);
CREATE INDEX idx_pu_user ON sh_partner_users(user_id);
CREATE INDEX idx_pli_price_product ON sh_price_list_items(
  price_list_id,
  product_id
);
CREATE INDEX idx_currencies_code ON sh_currencies(code);
CREATE INDEX "sh_reviews_locale_index" on "sh_reviews"("locale");
CREATE TABLE IF NOT EXISTS "sh_order_shippings"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "carrier_name" varchar,
  "tracking_number" varchar,
  "tracking_url" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_order_shippings_order_id_index" on "sh_order_shippings"(
  "order_id"
);
CREATE INDEX "idx_orders_number_created" on "sh_orders"(
  "number",
  "created_at"
);
CREATE INDEX "idx_inventories_country_default" on "sh_inventories"(
  "country_id",
  "is_default"
);
CREATE INDEX "idx_zones_code" on "sh_zones"("code");
CREATE TABLE IF NOT EXISTS "sh_order_items"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar,
  "sku" varchar,
  "product_type" varchar not null,
  "product_id" integer not null,
  "quantity" integer not null,
  "unit_price_amount" integer not null,
  "order_id" integer not null,
  foreign key("order_id") references sh_orders("id") on delete cascade on update no action,
  foreign key("order_id") references "sh_orders"("id") on delete cascade
);
CREATE INDEX "idx_order_items_order_product" on "sh_order_items"(
  "order_id",
  "product_id"
);
CREATE INDEX "sh_order_items_order_id_index" on "sh_order_items"("order_id");
CREATE INDEX "sh_order_items_product_type_product_id_index" on "sh_order_items"(
  "product_type",
  "product_id"
);
CREATE INDEX "sh_order_items_sku_index" on "sh_order_items"("sku");
CREATE TABLE IF NOT EXISTS "sh_prices"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "priceable_type" varchar not null,
  "priceable_id" integer not null,
  "amount" integer,
  "compare_amount" integer,
  "cost_amount" integer,
  "currency_id" integer not null,
  foreign key("currency_id") references sh_currencies("id") on delete cascade on update no action,
  foreign key("currency_id") references "sh_currencies"("id")
);
CREATE INDEX "idx_prices_currency_amount" on "sh_prices"(
  "currency_id",
  "amount"
);
CREATE INDEX "idx_prices_priceable_currency" on "sh_prices"(
  "priceable_type",
  "priceable_id",
  "currency_id"
);
CREATE INDEX "sh_prices_amount_index" on "sh_prices"("amount");
CREATE INDEX "sh_prices_currency_id_index" on "sh_prices"("currency_id");
CREATE INDEX "sh_prices_priceable_type_priceable_id_index" on "sh_prices"(
  "priceable_type",
  "priceable_id"
);
CREATE INDEX "sh_products_warehouse_qty_idx" on "sh_products"(
  "warehouse_quantity"
);
CREATE INDEX "sh_product_variants_status_index" on "sh_product_variants"(
  "status"
);
CREATE TABLE IF NOT EXISTS "sh_variant_inventories"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "inventory_id" integer not null,
  "stock" integer not null,
  "reserved" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("variant_id") references "sh_product_variants"("id") on delete cascade,
  foreign key("inventory_id") references "sh_inventories"("id") on delete cascade
);
CREATE UNIQUE INDEX "variant_inventory_unique" on "sh_variant_inventories"(
  "variant_id",
  "inventory_id"
);
CREATE INDEX "sh_variant_inventories_inventory_id_index" on "sh_variant_inventories"(
  "inventory_id"
);
CREATE TABLE IF NOT EXISTS "sh_country_zone"(
  "zone_id" integer not null,
  "country_id" integer not null,
  foreign key("zone_id") references "sh_zones"("id") on delete cascade,
  foreign key("country_id") references "sh_countries"("id") on delete cascade
);
CREATE UNIQUE INDEX "country_zone_unique" on "sh_country_zone"(
  "zone_id",
  "country_id"
);
CREATE TABLE IF NOT EXISTS "brands"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "website" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "brands_is_enabled_name_index" on "brands"("is_enabled", "name");
CREATE UNIQUE INDEX "brands_slug_unique" on "brands"("slug");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "parent_id" integer,
  "sort_order" integer not null default '0',
  "is_visible" tinyint(1) not null default '1',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("parent_id") references "categories"("id") on delete set null
);
CREATE INDEX "categories_is_visible_sort_order_index" on "categories"(
  "is_visible",
  "sort_order"
);
CREATE INDEX "categories_parent_id_sort_order_index" on "categories"(
  "parent_id",
  "sort_order"
);
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "short_description" text,
  "sku" varchar not null,
  "price" numeric,
  "sale_price" numeric,
  "manage_stock" tinyint(1) not null default '0',
  "stock_quantity" integer not null default '0',
  "low_stock_threshold" integer not null default '0',
  "weight" numeric,
  "length" numeric,
  "width" numeric,
  "height" numeric,
  "is_visible" tinyint(1) not null default '1',
  "is_featured" tinyint(1) not null default '0',
  "published_at" datetime,
  "seo_title" varchar,
  "seo_description" text,
  "brand_id" integer,
  "status" varchar check("status" in('draft', 'published', 'archived')) not null default 'draft',
  "type" varchar check("type" in('simple', 'variable')) not null default 'simple',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("brand_id") references "brands"("id") on delete set null
);
CREATE INDEX "products_is_visible_published_at_index" on "products"(
  "is_visible",
  "published_at"
);
CREATE INDEX "products_status_is_visible_index" on "products"(
  "status",
  "is_visible"
);
CREATE INDEX "products_brand_id_is_visible_index" on "products"(
  "brand_id",
  "is_visible"
);
CREATE INDEX "products_is_featured_is_visible_index" on "products"(
  "is_featured",
  "is_visible"
);
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
CREATE UNIQUE INDEX "products_sku_unique" on "products"("sku");
CREATE TABLE IF NOT EXISTS "product_categories"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "category_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("category_id") references "categories"("id") on delete cascade
);
CREATE UNIQUE INDEX "product_categories_product_id_category_id_unique" on "product_categories"(
  "product_id",
  "category_id"
);
CREATE TABLE IF NOT EXISTS "collections"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "is_visible" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "is_automatic" tinyint(1) not null default '0',
  "rules" text,
  "max_products" integer
);
CREATE INDEX "collections_is_visible_sort_order_index" on "collections"(
  "is_visible",
  "sort_order"
);
CREATE UNIQUE INDEX "collections_slug_unique" on "collections"("slug");
CREATE TABLE IF NOT EXISTS "product_collections"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "collection_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("collection_id") references "collections"("id") on delete cascade
);
CREATE UNIQUE INDEX "product_collections_product_id_collection_id_unique" on "product_collections"(
  "product_id",
  "collection_id"
);
CREATE TABLE IF NOT EXISTS "reviews"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "user_id" integer,
  "reviewer_name" varchar not null,
  "reviewer_email" varchar not null,
  "rating" integer not null,
  "title" varchar,
  "content" text not null,
  "is_approved" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "reviews_product_id_is_approved_index" on "reviews"(
  "product_id",
  "is_approved"
);
CREATE INDEX "reviews_is_approved_created_at_index" on "reviews"(
  "is_approved",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "coupons"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "description" text,
  "type" varchar check("type" in('percentage', 'fixed')) not null,
  "value" numeric not null,
  "minimum_amount" numeric,
  "usage_limit" integer,
  "used_count" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "starts_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "coupons_code_is_active_index" on "coupons"("code", "is_active");
CREATE INDEX "coupons_is_active_starts_at_expires_at_index" on "coupons"(
  "is_active",
  "starts_at",
  "expires_at"
);
CREATE UNIQUE INDEX "coupons_code_unique" on "coupons"("code");
CREATE TABLE IF NOT EXISTS "sh_locations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "address_line_1" varchar not null,
  "address_line_2" varchar,
  "city" varchar not null,
  "state" varchar not null,
  "postal_code" varchar not null,
  "country_code" varchar not null,
  "phone" varchar,
  "email" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "type" varchar check("type" in('warehouse', 'store', 'pickup_point')) not null default 'warehouse',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "sh_locations_code_unique" on "sh_locations"("code");
CREATE TABLE IF NOT EXISTS "sh_product_attributes"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "attribute_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("attribute_id") references "sh_attributes"("id") on delete cascade
);
CREATE UNIQUE INDEX "sh_product_attributes_product_id_attribute_id_unique" on "sh_product_attributes"(
  "product_id",
  "attribute_id"
);
CREATE TABLE IF NOT EXISTS "sh_product_variant_attributes"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "attribute_value_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("variant_id") references "sh_product_variants"("id") on delete cascade,
  foreign key("attribute_value_id") references "sh_attribute_values"("id") on delete cascade
);
CREATE UNIQUE INDEX "variant_attribute_value_unique" on "sh_product_variant_attributes"(
  "variant_id",
  "attribute_value_id"
);
CREATE TABLE IF NOT EXISTS "sh_addresses"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "type" varchar check("type" in('billing', 'shipping', 'both')) not null default 'both',
  "first_name" varchar not null,
  "last_name" varchar not null,
  "company" varchar,
  "address_line_1" varchar not null,
  "address_line_2" varchar,
  "city" varchar not null,
  "state" varchar not null,
  "postal_code" varchar not null,
  "country_code" varchar not null,
  "phone" varchar,
  "is_default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("country_code") references "sh_countries"("code") on delete restrict
);
CREATE INDEX "sh_addresses_user_id_type_index" on "sh_addresses"(
  "user_id",
  "type"
);
CREATE INDEX "sh_addresses_user_id_is_default_index" on "sh_addresses"(
  "user_id",
  "is_default"
);
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "number" varchar not null,
  "user_id" integer,
  "status" varchar not null default('pending'),
  "subtotal" numeric not null,
  "tax_amount" numeric not null default('0'),
  "shipping_amount" numeric not null default('0'),
  "discount_amount" numeric not null default('0'),
  "total" numeric not null,
  "currency" varchar not null default('EUR'),
  "billing_address" text,
  "shipping_address" text,
  "notes" text,
  "shipped_at" datetime,
  "delivered_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "channel_id" integer,
  "zone_id" integer,
  "partner_id" integer,
  "payment_status" varchar not null default('pending'),
  "payment_method" varchar,
  "payment_reference" varchar,
  foreign key("user_id") references users("id") on delete set null on update no action,
  foreign key("channel_id") references "sh_channels"("id") on delete set null,
  foreign key("zone_id") references "sh_zones"("id") on delete set null
);
CREATE UNIQUE INDEX "orders_number_unique" on "orders"("number");
CREATE INDEX "orders_status_created_at_index" on "orders"(
  "status",
  "created_at"
);
CREATE INDEX "orders_status_created_idx" on "orders"("status", "created_at");
CREATE INDEX "orders_user_id_created_at_index" on "orders"(
  "user_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "cart_items"(
  "id" integer primary key autoincrement not null,
  "session_id" varchar not null,
  "user_id" integer,
  "product_id" integer not null,
  "quantity" integer not null,
  "price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  "variant_id" integer,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("variant_id") references "sh_product_variants"("id") on delete set null
);
CREATE INDEX "cart_items_session_id_index" on "cart_items"("session_id");
CREATE INDEX "cart_items_user_id_index" on "cart_items"("user_id");
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer not null,
  "product_name" varchar not null,
  "product_sku" varchar not null,
  "quantity" integer not null,
  "price" numeric not null,
  "total" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  "variant_id" integer,
  "variant_name" varchar,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("order_id") references orders("id") on delete cascade on update no action,
  foreign key("variant_id") references "sh_product_variants"("id") on delete set null
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2019_05_03_000001_create_customer_columns',1);
INSERT INTO migrations VALUES(5,'2019_11_18_220125_create_permission_tables',1);
INSERT INTO migrations VALUES(6,'2020_00_00_000001_create_countries_table',1);
INSERT INTO migrations VALUES(7,'2020_00_00_000001_create_currencies_table',1);
INSERT INTO migrations VALUES(8,'2020_00_00_000001_create_settings_table',1);
INSERT INTO migrations VALUES(9,'2020_00_01_000000_create_user_addresses_table',1);
INSERT INTO migrations VALUES(10,'2020_00_02_000001_add_two_factor_columns_to_users_table',1);
INSERT INTO migrations VALUES(11,'2020_00_02_000001_create_channels_table',1);
INSERT INTO migrations VALUES(12,'2020_00_02_000002_create_inventories_table',1);
INSERT INTO migrations VALUES(13,'2020_00_02_000003_create_categories_table',1);
INSERT INTO migrations VALUES(14,'2020_00_02_000004_create_brands_table',1);
INSERT INTO migrations VALUES(15,'2020_00_02_000005_create_collections_table',1);
INSERT INTO migrations VALUES(16,'2020_00_02_000006_create_products_table',1);
INSERT INTO migrations VALUES(17,'2020_00_02_000007_create_attributes_table',1);
INSERT INTO migrations VALUES(18,'2020_00_02_000007_create_carriers_table',1);
INSERT INTO migrations VALUES(19,'2020_00_02_000008_create_payment_methods_table',1);
INSERT INTO migrations VALUES(20,'2020_00_02_000009_create_discounts_table',1);
INSERT INTO migrations VALUES(21,'2020_00_02_000010_create_reviews_table',1);
INSERT INTO migrations VALUES(22,'2020_00_03_000001_create_orders_table',1);
INSERT INTO migrations VALUES(23,'2020_00_03_000002_create_order_shippings_table',1);
INSERT INTO migrations VALUES(24,'2020_00_03_000002_create_users_geolocation_history_table',1);
INSERT INTO migrations VALUES(25,'2020_00_03_000003_create_order_items_table',1);
INSERT INTO migrations VALUES(26,'2020_00_03_000004_create_order_refunds_table',1);
INSERT INTO migrations VALUES(27,'2021_01_18_174504_create_legals_table',1);
INSERT INTO migrations VALUES(28,'2021_02_10_153239_create_product_has_relations_table',1);
INSERT INTO migrations VALUES(29,'2023_07_25_044432_add_icon_column',1);
INSERT INTO migrations VALUES(30,'2023_07_28_095404_create_attribute_product_table',1);
INSERT INTO migrations VALUES(31,'2023_09_21_063717_rename_requires_shipping_columns_on_products_table',1);
INSERT INTO migrations VALUES(32,'2024_03_28_084412_add_metadata-fields_table',1);
INSERT INTO migrations VALUES(33,'2024_04_23_104020_create_zones_table',1);
INSERT INTO migrations VALUES(34,'2024_06_10_071333_create_carrier_options_table',1);
INSERT INTO migrations VALUES(35,'2024_07_06_161600_create_order_addresses_table',1);
INSERT INTO migrations VALUES(36,'2024_07_06_174243_add_columns_to_orders_table',1);
INSERT INTO migrations VALUES(37,'2024_12_06_191438_add_zone_id_column_to_discounts_table',1);
INSERT INTO migrations VALUES(38,'2024_12_07_142851_create_product_variants_table',1);
INSERT INTO migrations VALUES(39,'2024_12_09_012530_create_prices_table',1);
INSERT INTO migrations VALUES(40,'2024_12_09_012533_add_is_enable_column_channels_table',1);
INSERT INTO migrations VALUES(41,'2024_12_28_164020_create_variant_attribute_value_table',1);
INSERT INTO migrations VALUES(42,'2025_02_15_103103_add_is_enabled_column_to_currencies_table',1);
INSERT INTO migrations VALUES(43,'2025_08_31_183927_create_media_table',1);
INSERT INTO migrations VALUES(44,'2025_08_31_192639_create_telescope_entries_table',1);
INSERT INTO migrations VALUES(45,'2025_08_31_200000_extend_discounts_and_create_discount_tables',1);
INSERT INTO migrations VALUES(46,'2025_08_31_200100_create_translation_tables',1);
INSERT INTO migrations VALUES(47,'2025_08_31_200200_create_groups_partners_price_lists',1);
INSERT INTO migrations VALUES(48,'2025_08_31_210000_add_performance_indexes',1);
INSERT INTO migrations VALUES(49,'2025_08_31_210100_add_price_indexes',1);
INSERT INTO migrations VALUES(50,'2025_09_01_000001_add_locale_to_shopper_reviews_table',1);
INSERT INTO migrations VALUES(51,'2025_09_01_000010_add_preferred_locale_to_users_table',1);
INSERT INTO migrations VALUES(52,'2025_09_01_000020_add_unique_slug_locale_indexes',1);
INSERT INTO migrations VALUES(53,'2025_09_01_000500_add_price_composite_index',1);
INSERT INTO migrations VALUES(54,'2025_09_01_010000_add_payment_fields_to_orders_table',1);
INSERT INTO migrations VALUES(55,'2025_09_01_010100_add_timeline_to_orders_table',1);
INSERT INTO migrations VALUES(56,'2025_09_01_010200_add_tracking_to_order_shippings_table',1);
INSERT INTO migrations VALUES(57,'2025_09_01_010400_create_order_shippings_table',1);
INSERT INTO migrations VALUES(58,'2025_09_01_010500_add_totals_columns_to_orders_table',1);
INSERT INTO migrations VALUES(59,'2025_09_01_020000_add_foreign_keys_and_more_indexes',1);
INSERT INTO migrations VALUES(60,'2025_09_01_050000_add_warehouse_quantity_to_products',1);
INSERT INTO migrations VALUES(61,'2025_09_01_190000_add_status_to_sh_product_variants_table',1);
INSERT INTO migrations VALUES(62,'2025_09_01_191000_create_variant_inventories_table',1);
INSERT INTO migrations VALUES(63,'2025_09_02_125433_create_sh_country_zone_table',2);
INSERT INTO migrations VALUES(64,'2025_09_02_130000_test_support_columns',3);
INSERT INTO migrations VALUES(66,'2025_09_02_151538_create_new_ecommerce_tables',4);
INSERT INTO migrations VALUES(67,'2025_09_02_155024_upgrade_models_and_add_missing_relations',4);
INSERT INTO migrations VALUES(68,'2025_09_02_155500_create_missing_core_tables',5);
