CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "preferred_locale" varchar,
  "is_admin" tinyint(1) not null default '0',
  "remember_token" varchar,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "website" varchar,
  "bio" text,
  "company" text,
  "position" text,
  "social_links" text,
  "notification_preferences" text,
  "privacy_settings" text,
  "marketing_preferences" text,
  "login_count" integer not null default '0',
  "last_activity_at" datetime,
  "phone_verified_at" datetime,
  "api_token" varchar,
  "stripe_customer_id" varchar,
  "stripe_account_id" varchar,
  "subscription_status" varchar,
  "subscription_plan" varchar,
  "subscription_ends_at" datetime,
  "trial_ends_at" datetime,
  "status" varchar not null default 'active',
  "verification_token" varchar,
  "password_reset_token" varchar,
  "password_reset_expires_at" datetime,
  "first_name" varchar,
  "last_name" varchar,
  "date_of_birth" date,
  "gender" varchar check("gender" in('male', 'female', 'other')),
  "phone_number" varchar,
  "timezone" varchar not null default 'Europe/Vilnius',
  "job_title" varchar,
  "is_active" tinyint(1) not null default '1',
  "is_verified" tinyint(1) not null default '0',
  "accepts_marketing" tinyint(1) not null default '0',
  "last_login_at" datetime,
  "last_login_ip" varchar,
  "avatar_url" varchar,
  "preferences" text,
  "phone" varchar,
  "avatar" varchar,
  "tax_number" varchar,
  "referral_code" varchar,
  "referral_code_generated_at" datetime,
  "referral_settings" text,
  "two_factor_enabled" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
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
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  "failed_jobs" integer not null default '0',
  "failed_job_ids" text,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "exports"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "name" varchar not null,
  "format" varchar not null,
  "status" varchar not null,
  "exportable_type" varchar not null,
  "columns" text not null,
  "exportable_options" text,
  "total_rows" integer not null default '0',
  "processed_rows" integer not null default '0',
  "artifact_disk" varchar,
  "artifact_path" varchar,
  "artifact_filename" varchar,
  "requested_at" datetime not null,
  "completed_at" datetime,
  "failed_at" datetime,
  "failure_reason" text,
  "requested_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("requested_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "exports_uuid_unique" on "exports"("uuid");
CREATE TABLE IF NOT EXISTS "organizations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "type" varchar not null default 'company',
  "is_active" tinyint(1) not null default '1',
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "organizations_is_active_type_index" on "organizations"(
  "is_active",
  "type"
);
CREATE UNIQUE INDEX "organizations_slug_unique" on "organizations"("slug");
CREATE TABLE IF NOT EXISTS "projects"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "status" varchar not null default 'active',
  "type" varchar not null default 'organizational',
  "user_id" integer,
  "organization_id" integer,
  "start_date" date,
  "end_date" date,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("organization_id") references "organizations"("id") on delete cascade
);
CREATE INDEX "projects_status_type_index" on "projects"("status", "type");
CREATE INDEX "projects_user_id_type_index" on "projects"("user_id", "type");
CREATE INDEX "projects_organization_id_status_index" on "projects"(
  "organization_id",
  "status"
);
CREATE UNIQUE INDEX "projects_slug_unique" on "projects"("slug");
CREATE TABLE IF NOT EXISTS "tasks"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "description" text,
  "status" varchar not null default 'pending',
  "priority" varchar not null default 'medium',
  "project_id" integer not null,
  "created_by" integer not null,
  "parent_task_id" integer,
  "due_date" datetime,
  "completed_at" datetime,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("project_id") references "projects"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete cascade,
  foreign key("parent_task_id") references "tasks"("id") on delete cascade
);
CREATE INDEX "tasks_project_id_status_index" on "tasks"(
  "project_id",
  "status"
);
CREATE INDEX "tasks_created_by_status_index" on "tasks"(
  "created_by",
  "status"
);
CREATE INDEX "tasks_parent_task_id_index" on "tasks"("parent_task_id");
CREATE INDEX "tasks_due_date_index" on "tasks"("due_date");
CREATE TABLE IF NOT EXISTS "tags"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "color" varchar,
  "description" text,
  "type" varchar not null default 'general',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "tags_type_name_index" on "tags"("type", "name");
CREATE UNIQUE INDEX "tags_slug_unique" on "tags"("slug");
CREATE TABLE IF NOT EXISTS "comments"(
  "id" integer primary key autoincrement not null,
  "content" text not null,
  "user_id" integer not null,
  "commentable_type" varchar not null,
  "commentable_id" integer not null,
  "parent_id" integer,
  "is_approved" tinyint(1) not null default '1',
  "is_pinned" tinyint(1) not null default '0',
  "likes_count" integer not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("parent_id") references "comments"("id") on delete cascade
);
CREATE INDEX "comments_commentable_type_commentable_id_index" on "comments"(
  "commentable_type",
  "commentable_id"
);
CREATE INDEX "comments_commentable_index" on "comments"(
  "commentable_type",
  "commentable_id"
);
CREATE INDEX "comments_commentable_approved_index" on "comments"(
  "commentable_type",
  "commentable_id",
  "is_approved"
);
CREATE INDEX "comments_commentable_created_index" on "comments"(
  "commentable_type",
  "commentable_id",
  "created_at"
);
CREATE INDEX "comments_commentable_parent_index" on "comments"(
  "commentable_type",
  "commentable_id",
  "parent_id"
);
CREATE INDEX "comments_parent_id_index" on "comments"("parent_id");
CREATE INDEX "comments_user_id_created_at_index" on "comments"(
  "user_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "files"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "original_name" varchar not null,
  "path" varchar not null,
  "disk" varchar not null default 'local',
  "mime_type" varchar not null,
  "size" integer not null,
  "hash" varchar,
  "fileable_type" varchar not null,
  "fileable_id" integer not null,
  "uploaded_by" integer not null,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("uploaded_by") references "users"("id") on delete cascade
);
CREATE INDEX "files_fileable_type_fileable_id_index" on "files"(
  "fileable_type",
  "fileable_id"
);
CREATE INDEX "files_uploaded_by_index" on "files"("uploaded_by");
CREATE INDEX "files_hash_index" on "files"("hash");
CREATE INDEX "files_mime_type_index" on "files"("mime_type");
CREATE TABLE IF NOT EXISTS "organization_user"(
  "id" integer primary key autoincrement not null,
  "organization_id" integer not null,
  "user_id" integer not null,
  "role" varchar not null default 'member',
  "permissions" text,
  "is_active" tinyint(1) not null default '1',
  "joined_at" datetime not null,
  "left_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("organization_id") references "organizations"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "organization_user_organization_id_user_id_unique" on "organization_user"(
  "organization_id",
  "user_id"
);
CREATE INDEX "organization_user_user_id_role_index" on "organization_user"(
  "user_id",
  "role"
);
CREATE INDEX "organization_user_organization_id_is_active_index" on "organization_user"(
  "organization_id",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "task_user"(
  "id" integer primary key autoincrement not null,
  "task_id" integer not null,
  "user_id" integer not null,
  "responsibility" varchar not null default 'assignee',
  "assigned_at" datetime not null,
  "completed_at" datetime,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("task_id") references "tasks"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "task_user_task_id_user_id_responsibility_unique" on "task_user"(
  "task_id",
  "user_id",
  "responsibility"
);
CREATE INDEX "task_user_user_id_responsibility_index" on "task_user"(
  "user_id",
  "responsibility"
);
CREATE TABLE IF NOT EXISTS "taggables"(
  "id" integer primary key autoincrement not null,
  "tag_id" integer not null,
  "taggable_type" varchar not null,
  "taggable_id" integer not null,
  "tagged_by" integer,
  "tagged_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("tag_id") references "tags"("id") on delete cascade,
  foreign key("tagged_by") references "users"("id") on delete set null
);
CREATE INDEX "taggables_taggable_type_taggable_id_index" on "taggables"(
  "taggable_type",
  "taggable_id"
);
CREATE UNIQUE INDEX "taggables_tag_id_taggable_type_taggable_id_unique" on "taggables"(
  "tag_id",
  "taggable_type",
  "taggable_id"
);
CREATE TABLE IF NOT EXISTS "project_user"(
  "id" integer primary key autoincrement not null,
  "project_id" integer not null,
  "user_id" integer not null,
  "role" varchar not null default 'member',
  "permissions" text,
  "joined_at" datetime not null,
  "left_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("project_id") references "projects"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "project_user_project_id_user_id_unique" on "project_user"(
  "project_id",
  "user_id"
);
CREATE INDEX "project_user_user_id_role_index" on "project_user"(
  "user_id",
  "role"
);
CREATE UNIQUE INDEX "users_api_token_unique" on "users"("api_token");
CREATE TABLE IF NOT EXISTS "document_templates"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "content" text not null,
  "variables" text,
  "type" varchar not null default 'document',
  "category" varchar,
  "settings" text,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "document_templates_type_is_active_index" on "document_templates"(
  "type",
  "is_active"
);
CREATE INDEX "document_templates_category_index" on "document_templates"(
  "category"
);
CREATE UNIQUE INDEX "document_templates_slug_unique" on "document_templates"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "documents"(
  "id" integer primary key autoincrement not null,
  "document_template_id" integer not null,
  "title" varchar not null,
  "content" text not null,
  "variables" text,
  "status" varchar not null default('draft'),
  "format" varchar not null default('html'),
  "file_path" varchar,
  "documentable_type" varchar not null,
  "documentable_id" integer not null,
  "created_by" integer,
  "generated_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "updated_by" integer,
  "name" varchar,
  "type" varchar,
  "version" varchar,
  "file_size" integer,
  "mime_type" varchar,
  "is_public" tinyint(1) not null default '0',
  "is_downloadable" tinyint(1) not null default '1',
  "access_password" varchar,
  "expires_at" datetime,
  "description" text,
  "notes" text,
  "created_by_name" varchar,
  "updated_by_name" varchar,
  foreign key("created_by") references users("id") on delete set null on update no action,
  foreign key("document_template_id") references document_templates("id") on delete cascade on update no action,
  foreign key("updated_by") references "users"("id") on delete set null
);
CREATE INDEX "documents_created_by_index" on "documents"("created_by");
CREATE INDEX "documents_documentable_type_documentable_id_index" on "documents"(
  "documentable_type",
  "documentable_id"
);
CREATE INDEX "documents_status_created_at_index" on "documents"(
  "status",
  "created_at"
);
CREATE INDEX "documents_updated_by_index" on "documents"("updated_by");
CREATE TABLE IF NOT EXISTS "enhanced_settings"(
  "id" integer primary key autoincrement not null,
  "group" varchar not null default 'general',
  "key" varchar not null,
  "locale" varchar not null default 'lt',
  "value" text,
  "type" varchar not null default 'text',
  "description" text,
  "is_public" tinyint(1) not null default '0',
  "is_encrypted" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "validation_rules" text,
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "enhanced_settings_group_key_index" on "enhanced_settings"(
  "group",
  "key"
);
CREATE INDEX "enhanced_settings_is_public_index" on "enhanced_settings"(
  "is_public"
);
CREATE INDEX "enhanced_settings_locale_index" on "enhanced_settings"("locale");
CREATE UNIQUE INDEX "enhanced_settings_key_locale_unique" on "enhanced_settings"(
  "key",
  "locale"
);
CREATE TABLE IF NOT EXISTS "enhanced_settings_translations"(
  "id" integer primary key autoincrement not null,
  "enhanced_setting_id" integer not null,
  "locale" varchar not null,
  "description" text,
  "display_name" varchar,
  "help_text" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("enhanced_setting_id") references "enhanced_settings"("id") on delete cascade
);
CREATE INDEX "enhanced_settings_translations_locale_index" on "enhanced_settings_translations"(
  "locale"
);
CREATE UNIQUE INDEX "enhanced_settings_translations_setting_locale_unique" on "enhanced_settings_translations"(
  "enhanced_setting_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "media_collections"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "allowed_mime_types" text,
  "max_file_size" integer,
  "max_files" integer,
  "conversions" text,
  "is_private" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "media_collections_slug_unique" on "media_collections"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "cache_tags"(
  "id" integer primary key autoincrement not null,
  "tag" varchar not null,
  "key" varchar not null,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "cache_tags_tag_key_unique" on "cache_tags"("tag", "key");
CREATE INDEX "cache_tags_tag_index" on "cache_tags"("tag");
CREATE INDEX "cache_tags_expires_at_index" on "cache_tags"("expires_at");
CREATE TABLE IF NOT EXISTS "job_batches_extended"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "options" text,
  "progress" text,
  "results" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE INDEX "job_batches_extended_name_created_at_index" on "job_batches_extended"(
  "name",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "tenants"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "domain" varchar,
  "config" text,
  "features" text,
  "is_active" tinyint(1) not null default '1',
  "trial_ends_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "tenants_slug_unique" on "tenants"("slug");
CREATE TABLE IF NOT EXISTS "tenant_users"(
  "id" integer primary key autoincrement not null,
  "tenant_id" integer not null,
  "user_id" integer not null,
  "roles" text,
  "permissions" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("tenant_id") references "tenants"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "tenant_users_tenant_id_user_id_unique" on "tenant_users"(
  "tenant_id",
  "user_id"
);
CREATE TABLE IF NOT EXISTS "user_wishlists"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "name" varchar not null,
  "description" text,
  "is_public" tinyint(1) not null default '0',
  "is_default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "user_wishlists_user_id_index" on "user_wishlists"("user_id");
CREATE INDEX "user_wishlists_is_default_index" on "user_wishlists"(
  "is_default"
);
CREATE INDEX "users_is_active_is_verified_index" on "users"(
  "is_active",
  "is_verified"
);
CREATE INDEX "users_last_login_at_index" on "users"("last_login_at");
CREATE INDEX "users_created_at_index" on "users"("created_at");
CREATE INDEX "users_preferred_locale_index" on "users"("preferred_locale");
CREATE TABLE IF NOT EXISTS "system_setting_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "icon" varchar,
  "color" varchar default 'primary',
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "parent_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "template" varchar,
  "is_collapsible" tinyint(1) not null default '1',
  "show_in_sidebar" tinyint(1) not null default '1',
  "permission" varchar,
  "tags" text,
  "meta" text,
  foreign key("parent_id") references "system_setting_categories"("id") on delete cascade
);
CREATE INDEX "system_setting_categories_is_active_sort_order_index" on "system_setting_categories"(
  "is_active",
  "sort_order"
);
CREATE INDEX "system_setting_categories_parent_id_index" on "system_setting_categories"(
  "parent_id"
);
CREATE UNIQUE INDEX "system_setting_categories_slug_unique" on "system_setting_categories"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "system_settings"(
  "id" integer primary key autoincrement not null,
  "category_id" integer,
  "key" varchar not null,
  "name" varchar not null,
  "value" text,
  "type" varchar not null default 'string',
  "group" varchar not null default 'general',
  "description" text,
  "help_text" text,
  "is_public" tinyint(1) not null default '0',
  "is_required" tinyint(1) not null default '0',
  "is_encrypted" tinyint(1) not null default '0',
  "is_readonly" tinyint(1) not null default '0',
  "validation_rules" text,
  "options" text,
  "default_value" text,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "updated_by" integer,
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "placeholder" varchar,
  "tooltip" text,
  "metadata" text,
  "validation_message" varchar,
  "is_cacheable" tinyint(1) not null default '1',
  "cache_ttl" integer not null default '3600',
  "cache_key" varchar,
  "environment" varchar not null default 'all',
  "tags" text,
  "version" varchar not null default '1.0.0',
  "last_accessed_at" datetime,
  "access_count" integer not null default '0',
  "category" varchar,
  "unit" varchar,
  "created_by_name" varchar,
  "updated_by_name" varchar,
  foreign key("category_id") references "system_setting_categories"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE INDEX "system_settings_category_id_is_active_sort_order_index" on "system_settings"(
  "category_id",
  "is_active",
  "sort_order"
);
CREATE INDEX "system_settings_group_is_active_index" on "system_settings"(
  "group",
  "is_active"
);
CREATE INDEX "system_settings_is_public_is_active_index" on "system_settings"(
  "is_public",
  "is_active"
);
CREATE INDEX "system_settings_updated_by_index" on "system_settings"(
  "updated_by"
);
CREATE INDEX "system_settings_created_by_index" on "system_settings"(
  "created_by"
);
CREATE UNIQUE INDEX "system_settings_key_unique" on "system_settings"("key");
CREATE TABLE IF NOT EXISTS "system_setting_translations"(
  "id" integer primary key autoincrement not null,
  "system_setting_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "help_text" text,
  "created_at" datetime,
  "updated_at" datetime,
  "rich_description" text,
  "attachments" text,
  "is_active" tinyint(1) not null default '1',
  "is_public" tinyint(1) not null default '0',
  "metadata" text,
  "tags" text,
  "sort_order" integer not null default '0',
  "deleted_at" datetime,
  foreign key("system_setting_id") references "system_settings"("id") on delete cascade
);
CREATE INDEX "system_setting_locale_index" on "system_setting_translations"(
  "system_setting_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "system_setting_category_translations"(
  "id" integer primary key autoincrement not null,
  "system_setting_category_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "meta" text,
  foreign key("system_setting_category_id") references "system_setting_categories"("id") on delete cascade
);
CREATE UNIQUE INDEX "system_setting_cat_locale_unique" on "system_setting_category_translations"(
  "system_setting_category_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "system_setting_history"(
  "id" integer primary key autoincrement not null,
  "system_setting_id" integer not null,
  "old_value" text,
  "new_value" text,
  "changed_by" integer not null,
  "change_reason" varchar,
  "ip_address" varchar,
  "user_agent" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("system_setting_id") references "system_settings"("id") on delete cascade,
  foreign key("changed_by") references "users"("id") on delete cascade
);
CREATE INDEX "system_setting_history_system_setting_id_created_at_index" on "system_setting_history"(
  "system_setting_id",
  "created_at"
);
CREATE INDEX "system_setting_history_changed_by_index" on "system_setting_history"(
  "changed_by"
);
CREATE TABLE IF NOT EXISTS "system_setting_dependencies"(
  "id" integer primary key autoincrement not null,
  "setting_id" integer not null,
  "depends_on_setting_id" integer not null,
  "condition" text,
  "condition_value" varchar,
  "is_active" tinyint(1) not null default '1',
  "meta" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("setting_id") references "system_settings"("id") on delete cascade,
  foreign key("depends_on_setting_id") references "system_settings"("id") on delete cascade
);
CREATE INDEX "system_setting_dependencies_setting_id_is_active_index" on "system_setting_dependencies"(
  "setting_id",
  "is_active"
);
CREATE INDEX "system_setting_dependencies_depends_on_setting_id_index" on "system_setting_dependencies"(
  "depends_on_setting_id"
);
CREATE INDEX "system_setting_dependencies_condition_index" on "system_setting_dependencies"(
  "condition"
);
CREATE INDEX "system_settings_is_cacheable_is_active_index" on "system_settings"(
  "is_cacheable",
  "is_active"
);
CREATE INDEX "system_settings_environment_is_active_index" on "system_settings"(
  "environment",
  "is_active"
);
CREATE INDEX "system_settings_last_accessed_at_index" on "system_settings"(
  "last_accessed_at"
);
CREATE INDEX "system_settings_access_count_index" on "system_settings"(
  "access_count"
);
CREATE INDEX "system_setting_categories_is_collapsible_is_active_index" on "system_setting_categories"(
  "is_collapsible",
  "is_active"
);
CREATE INDEX "system_setting_categories_show_in_sidebar_is_active_index" on "system_setting_categories"(
  "show_in_sidebar",
  "is_active"
);
CREATE INDEX "system_setting_categories_permission_index" on "system_setting_categories"(
  "permission"
);
CREATE TABLE IF NOT EXISTS "table_settings"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "resource" varchar not null,
  "styles" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "table_settings_user_id_resource_unique" on "table_settings"(
  "user_id",
  "resource"
);
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
  "updated_at" datetime,
  "alt_text" varchar,
  "caption" text,
  "is_featured" tinyint(1) not null default '0',
  "sort_order" integer not null default '0'
);
CREATE INDEX "media_model_type_model_id_index" on "media"(
  "model_type",
  "model_id"
);
CREATE UNIQUE INDEX "media_uuid_unique" on "media"("uuid");
CREATE INDEX "media_order_column_index" on "media"("order_column");
CREATE TABLE IF NOT EXISTS "discount_campaigns"(
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
  "updated_at" datetime,
  "deprecated_at" datetime
);
CREATE INDEX "discount_campaigns_status_index" on "discount_campaigns"(
  "status"
);
CREATE INDEX "discount_campaigns_channel_id_index" on "discount_campaigns"(
  "channel_id"
);
CREATE INDEX "discount_campaigns_zone_id_index" on "discount_campaigns"(
  "zone_id"
);
CREATE UNIQUE INDEX "discount_campaigns_slug_unique" on "discount_campaigns"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "brand_translations"(
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
CREATE INDEX "sh_brand_translations_locale_index" on "brand_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_brand_translations_brand_id_locale_unique" on "brand_translations"(
  "brand_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_brand_translations_locale_slug_unique" on "brand_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "category_translations"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "short_description" text,
  "seo_keywords" varchar
);
CREATE INDEX "sh_category_translations_locale_index" on "category_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_category_translations_category_id_locale_unique" on "category_translations"(
  "category_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_category_translations_locale_slug_unique" on "category_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "collection_translations"(
  "id" integer primary key autoincrement not null,
  "collection_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "meta_title" varchar,
  "meta_description" text,
  "meta_keywords" text
);
CREATE INDEX "sh_collection_translations_locale_index" on "collection_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_collection_translations_collection_id_locale_unique" on "collection_translations"(
  "collection_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_collection_translations_locale_slug_unique" on "collection_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "attribute_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_attribute_translations_locale_index" on "attribute_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_attribute_translations_attribute_id_locale_unique" on "attribute_translations"(
  "attribute_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "attribute_value_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_value_id" integer not null,
  "locale" varchar not null,
  "value" varchar not null,
  "key" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "meta_data" text
);
CREATE INDEX "sh_attribute_value_translations_locale_index" on "attribute_value_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_attribute_value_translations_attribute_value_id_locale_unique" on "attribute_value_translations"(
  "attribute_value_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "product_translations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "short_description" text,
  "meta_keywords" text,
  "alt_text" varchar,
  "detailed_description" text
);
CREATE INDEX "sh_product_translations_locale_index" on "product_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_product_translations_product_id_locale_unique" on "product_translations"(
  "product_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_product_translations_locale_slug_unique" on "product_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "legal_translations"(
  "id" integer primary key autoincrement not null,
  "legal_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "slug" varchar not null,
  "content" text not null,
  "seo_title" varchar,
  "seo_description" text,
  "meta_data" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_legal_translations_locale_index" on "legal_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_legal_translations_legal_id_locale_unique" on "legal_translations"(
  "legal_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_legal_translations_locale_slug_unique" on "legal_translations"(
  "locale",
  "slug"
);
CREATE TABLE IF NOT EXISTS "partners"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "tier" varchar check("tier" in('gold', 'silver', 'bronze', 'custom')) not null default 'custom',
  "user_id" integer,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "tier_id" integer,
  "deleted_at" datetime,
  "contact_email" varchar,
  "contact_phone" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "discount_rate" numeric not null default '0',
  "commission_rate" numeric not null default '0'
);
CREATE UNIQUE INDEX "sh_partners_code_unique" on "partners"("code");
CREATE TABLE IF NOT EXISTS "partner_users"(
  "partner_id" integer not null,
  "user_id" integer not null,
  primary key("partner_id", "user_id")
);
CREATE TABLE IF NOT EXISTS "partner_tiers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "priority" integer not null default '100',
  "default_discount_pct" numeric not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "code" varchar not null,
  "is_enabled" tinyint(1) not null default '1',
  "discount_rate" numeric,
  "commission_rate" numeric
);
CREATE TABLE IF NOT EXISTS "price_lists"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "currency_id" integer not null,
  "zone_id" integer,
  "priority" integer not null default '100',
  "is_enabled" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "metadata" text,
  "is_default" tinyint(1) not null default '0',
  "auto_apply" tinyint(1) not null default '0',
  "min_order_amount" numeric,
  "max_order_amount" numeric,
  "deleted_at" datetime,
  "starts_at" datetime,
  "ends_at" datetime
);
CREATE TABLE IF NOT EXISTS "price_list_items"(
  "id" integer primary key autoincrement not null,
  "price_list_id" integer not null,
  "product_id" integer,
  "variant_id" integer,
  "net_amount" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" text,
  "description" text,
  "notes" text,
  "is_active" tinyint(1) not null default '1',
  "priority" integer not null default '100',
  "min_quantity" integer,
  "max_quantity" integer,
  "valid_from" datetime,
  "valid_until" datetime,
  "compare_amount" numeric,
  "is_featured" tinyint(1) not null default '0'
);
CREATE INDEX "sh_price_list_items_price_list_id_index" on "price_list_items"(
  "price_list_id"
);
CREATE INDEX "sh_price_list_items_product_id_index" on "price_list_items"(
  "product_id"
);
CREATE INDEX "sh_price_list_items_variant_id_index" on "price_list_items"(
  "variant_id"
);
CREATE TABLE IF NOT EXISTS "group_price_list"(
  "group_id" integer not null,
  "price_list_id" integer not null,
  primary key("group_id", "price_list_id")
);
CREATE TABLE IF NOT EXISTS "partner_price_list"(
  "partner_id" integer not null,
  "price_list_id" integer not null,
  primary key("partner_id", "price_list_id")
);
CREATE INDEX sh_prod_trans_product_id_index ON "product_translations"(
  product_id
);
CREATE INDEX sh_prod_trans_locale_index ON "product_translations"(locale);
CREATE INDEX sh_price_lists_currency_zone_priority ON "price_lists"(
  currency_id,
  zone_id,
  priority
);
CREATE INDEX sh_price_lists_is_enabled ON "price_lists"(is_enabled);
CREATE INDEX idx_gpl_group_price ON "group_price_list"(group_id,price_list_id);
CREATE INDEX idx_ppl_partner_price ON "partner_price_list"(
  partner_id,
  price_list_id
);
CREATE INDEX idx_pu_partner_user ON "partner_users"(partner_id,user_id);
CREATE INDEX idx_pu_user ON "partner_users"(user_id);
CREATE INDEX idx_pli_price_product ON "price_list_items"(
  price_list_id,
  product_id
);
CREATE TABLE IF NOT EXISTS "order_shippings"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "carrier_name" varchar,
  "tracking_number" varchar,
  "tracking_url" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "shipping_method" varchar,
  "carrier" varchar,
  "service" varchar,
  "service_type" varchar,
  "shipped_at" datetime,
  "estimated_delivery" datetime,
  "delivered_at" datetime,
  "weight" numeric,
  "dimensions" text,
  "base_cost" numeric,
  "insurance_cost" numeric,
  "total_cost" numeric,
  "metadata" text,
  "status" varchar not null default 'pending',
  "is_delivered" tinyint(1) not null default '0',
  "delivery_notes" varchar,
  "notes" text,
  "cost" numeric
);
CREATE INDEX "sh_order_shippings_order_id_index" on "order_shippings"(
  "order_id"
);
CREATE TABLE IF NOT EXISTS "zones"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "code" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "currency_id" integer,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "categories_legacy"(
  "id" integer primary key autoincrement not null,
  "slug" varchar not null,
  "name" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "sh_categories_slug_unique" on "categories_legacy"("slug");
CREATE TABLE IF NOT EXISTS "customers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "address" varchar,
  "postal_code" varchar,
  "country_id" integer,
  "city_id" integer,
  "company_id" integer,
  "is_active" tinyint(1) not null default '1',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "customers_is_active_index" on "customers"("is_active");
CREATE INDEX "customers_country_id_index" on "customers"("country_id");
CREATE INDEX "customers_city_id_index" on "customers"("city_id");
CREATE INDEX "customers_company_id_index" on "customers"("company_id");
CREATE UNIQUE INDEX "customers_email_unique" on "customers"("email");
CREATE TABLE IF NOT EXISTS "brands"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "website" varchar,
  "is_enabled" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "seo_title" varchar,
  "seo_description" text,
  "customer_group_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "social_links" text,
  "is_premium" tinyint(1) not null default '0',
  "is_featured" tinyint(1) not null default '0',
  "is_visible" tinyint(1) not null default '1',
  "is_active" tinyint(1) not null default '1',
  "meta_title" varchar,
  "meta_description" text,
  "contact_email" varchar,
  "contact_phone" varchar
);
CREATE INDEX "brands_is_enabled_name_index" on "brands"("is_enabled", "name");
CREATE INDEX "brands_sort_order_index" on "brands"("sort_order");
CREATE UNIQUE INDEX "brands_slug_unique" on "brands"("slug");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "short_description" text,
  "parent_id" integer,
  "sort_order" integer not null default '0',
  "is_visible" tinyint(1) not null default '1',
  "is_active" tinyint(1) not null default '1',
  "is_enabled" tinyint(1) not null default '1',
  "is_featured" tinyint(1) not null default '0',
  "color" varchar,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "show_in_menu" tinyint(1) not null default '1',
  "product_limit" integer,
  "meta_title" varchar,
  "meta_description" text,
  "icon" varchar,
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
  "sku" varchar,
  "price" numeric,
  "cost_price" numeric,
  "manage_stock" tinyint(1) not null default '0',
  "stock_quantity" integer not null default '0',
  "low_stock_threshold" integer not null default '0',
  "weight" numeric,
  "length" numeric,
  "width" numeric,
  "height" numeric,
  "is_enabled" tinyint(1) not null default '1',
  "is_featured" tinyint(1) not null default '0',
  "published_at" datetime,
  "seo_title" varchar,
  "seo_description" text,
  "brand_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "is_requestable" tinyint(1) not null default '0',
  "requests_count" integer not null default '0',
  "minimum_quantity" integer not null default '1',
  "hide_add_to_cart" tinyint(1) not null default '0',
  "request_message" text,
  "meta_title" varchar,
  "meta_description" text,
  "meta_keywords" text,
  "barcode" varchar,
  "track_inventory" tinyint(1) not null default '1',
  "video_url" varchar,
  "view_count" integer not null default '0',
  "last_viewed_at" datetime,
  "track_stock" tinyint(1) not null default '0',
  "allow_backorder" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "tax_class" varchar,
  "shipping_class" varchar,
  "download_limit" integer not null default '0',
  "download_expiry" integer not null default '0',
  "external_url" varchar,
  "button_text" varchar,
  "gallery" text,
  "available_from" datetime,
  "available_until" datetime,
  "warehouse_quantity" integer,
  "views_count" integer not null default '0',
  "status" varchar not null default 'draft',
  "detailed_description" text,
  foreign key("brand_id") references "brands"("id") on delete set null
);
CREATE INDEX "products_is_enabled_published_at_index" on "products"(
  "is_enabled",
  "published_at"
);
CREATE INDEX "products_brand_id_is_enabled_index" on "products"(
  "brand_id",
  "is_enabled"
);
CREATE INDEX "products_is_featured_is_enabled_index" on "products"(
  "is_featured",
  "is_enabled"
);
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
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
  "is_enabled" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "is_automatic" tinyint(1) not null default '0',
  "display_type" varchar not null default 'grid',
  "is_active" tinyint(1) not null default '1',
  "meta_title" varchar,
  "meta_description" text,
  "meta_keywords" varchar,
  "products_per_page" integer not null default '12',
  "show_filters" tinyint(1) not null default '1'
);
CREATE INDEX "collections_is_visible_sort_order_index" on "collections"(
  "is_visible",
  "sort_order"
);
CREATE INDEX "collections_is_enabled_index" on "collections"("is_enabled");
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
  "locale" varchar not null default 'lt',
  "approved_at" datetime,
  "rejected_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "is_verified_purchase" tinyint(1) not null default '0',
  "helpful_count" integer not null default '0',
  "reported_count" integer not null default '0',
  "is_featured" tinyint(1) not null default '0',
  "metadata" text,
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
CREATE INDEX "reviews_locale_index" on "reviews"("locale");
CREATE INDEX "products_is_requestable_requests_count_index" on "products"(
  "is_requestable",
  "requests_count"
);
CREATE INDEX "products_hide_add_to_cart_index" on "products"(
  "hide_add_to_cart"
);
CREATE TABLE IF NOT EXISTS "coupon_products"(
  "id" integer primary key autoincrement not null,
  "coupon_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("coupon_id") references "coupons"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "coupon_products_coupon_id_product_id_unique" on "coupon_products"(
  "coupon_id",
  "product_id"
);
CREATE INDEX "coupon_products_coupon_id_index" on "coupon_products"(
  "coupon_id"
);
CREATE INDEX "coupon_products_product_id_index" on "coupon_products"(
  "product_id"
);
CREATE TABLE IF NOT EXISTS "coupon_categories"(
  "id" integer primary key autoincrement not null,
  "coupon_id" integer not null,
  "category_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("coupon_id") references "coupons"("id") on delete cascade,
  foreign key("category_id") references "categories"("id") on delete cascade
);
CREATE UNIQUE INDEX "coupon_categories_coupon_id_category_id_unique" on "coupon_categories"(
  "coupon_id",
  "category_id"
);
CREATE INDEX "coupon_categories_coupon_id_index" on "coupon_categories"(
  "coupon_id"
);
CREATE INDEX "coupon_categories_category_id_index" on "coupon_categories"(
  "category_id"
);
CREATE TABLE IF NOT EXISTS "coupon_usages"(
  "id" integer primary key autoincrement not null,
  "coupon_id" integer not null,
  "user_id" integer,
  "order_id" integer,
  "discount_amount" numeric not null default '0',
  "used_at" datetime not null,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("coupon_id") references "coupons"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("order_id") references "orders"("id") on delete set null
);
CREATE INDEX "coupon_usages_coupon_id_index" on "coupon_usages"("coupon_id");
CREATE INDEX "coupon_usages_user_id_index" on "coupon_usages"("user_id");
CREATE INDEX "coupon_usages_order_id_index" on "coupon_usages"("order_id");
CREATE INDEX "coupon_usages_used_at_index" on "coupon_usages"("used_at");
CREATE TABLE IF NOT EXISTS "product_requests"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "user_id" integer not null,
  "name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "message" text,
  "requested_quantity" integer not null default '1',
  "status" varchar check("status" in('pending', 'in_progress', 'completed', 'cancelled')) not null default 'pending',
  "admin_notes" text,
  "responded_at" datetime,
  "responded_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("responded_by") references "users"("id") on delete set null
);
CREATE INDEX "product_requests_product_id_status_index" on "product_requests"(
  "product_id",
  "status"
);
CREATE INDEX "product_requests_user_id_created_at_index" on "product_requests"(
  "user_id",
  "created_at"
);
CREATE INDEX "product_requests_status_created_at_index" on "product_requests"(
  "status",
  "created_at"
);
CREATE INDEX "price_list_items_is_active_priority_index" on "price_list_items"(
  "is_active",
  "priority"
);
CREATE INDEX "price_list_items_valid_from_valid_until_index" on "price_list_items"(
  "valid_from",
  "valid_until"
);
CREATE INDEX "price_list_items_min_quantity_max_quantity_index" on "price_list_items"(
  "min_quantity",
  "max_quantity"
);
CREATE TABLE IF NOT EXISTS "price_list_translations"(
  "id" integer primary key autoincrement not null,
  "price_list_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "meta_title" varchar,
  "meta_description" text,
  "meta_keywords" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("price_list_id") references "price_lists"("id") on delete cascade
);
CREATE UNIQUE INDEX "price_list_translations_price_list_id_locale_unique" on "price_list_translations"(
  "price_list_id",
  "locale"
);
CREATE INDEX "price_list_translations_locale_index" on "price_list_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "price_list_item_translations"(
  "id" integer primary key autoincrement not null,
  "price_list_item_id" integer not null,
  "locale" varchar not null,
  "name" varchar,
  "description" text,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("price_list_item_id") references "price_list_items"("id") on delete cascade
);
CREATE UNIQUE INDEX "price_list_item_translations_unique" on "price_list_item_translations"(
  "price_list_item_id",
  "locale"
);
CREATE INDEX "price_list_item_translations_locale_index" on "price_list_item_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "country_translations"(
  "id" integer primary key autoincrement not null,
  "country_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "name_official" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "country_translations_country_id_locale_unique" on "country_translations"(
  "country_id",
  "locale"
);
CREATE INDEX "country_translations_locale_index" on "country_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "pages"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "slug" varchar not null,
  "layout" varchar not null default 'default',
  "blocks" text not null,
  "parent_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("parent_id") references "pages"("id") on delete cascade on update cascade
);
CREATE INDEX "pages_title_index" on "pages"("title");
CREATE INDEX "pages_layout_index" on "pages"("layout");
CREATE UNIQUE INDEX "pages_slug_parent_id_unique" on "pages"(
  "slug",
  "parent_id"
);
CREATE TABLE IF NOT EXISTS "admin_activity_logs"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "action" varchar not null,
  "resource_type" varchar not null,
  "resource_id" integer,
  "old_values" text,
  "new_values" text,
  "ip_address" varchar,
  "user_agent" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "admin_activity_logs_user_id_created_at_index" on "admin_activity_logs"(
  "user_id",
  "created_at"
);
CREATE INDEX "admin_activity_logs_resource_type_resource_id_index" on "admin_activity_logs"(
  "resource_type",
  "resource_id"
);
CREATE INDEX "admin_activity_logs_action_index" on "admin_activity_logs"(
  "action"
);
CREATE TABLE IF NOT EXISTS "system_notifications"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "title" varchar not null,
  "message" text not null,
  "data" text,
  "level" varchar check("level" in('info', 'success', 'warning', 'error')) not null default 'info',
  "is_read" tinyint(1) not null default '0',
  "read_at" datetime,
  "user_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "system_notifications_user_id_is_read_index" on "system_notifications"(
  "user_id",
  "is_read"
);
CREATE INDEX "system_notifications_type_level_index" on "system_notifications"(
  "type",
  "level"
);
CREATE INDEX "system_notifications_created_at_index" on "system_notifications"(
  "created_at"
);
CREATE TABLE IF NOT EXISTS "currencies"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "code" varchar not null,
  "iso_code" varchar,
  "symbol" varchar,
  "exchange_rate" numeric not null default '1',
  "base_currency" varchar not null default 'EUR',
  "decimal_places" integer not null default '2',
  "symbol_position" varchar not null default 'after',
  "thousands_separator" varchar not null default ',',
  "decimal_separator" varchar not null default '.',
  "is_active" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "is_enabled" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "auto_update_rate" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "currencies_code_index" on "currencies"("code");
CREATE INDEX "currencies_is_active_is_enabled_index" on "currencies"(
  "is_active",
  "is_enabled"
);
CREATE INDEX "currencies_is_default_index" on "currencies"("is_default");
CREATE UNIQUE INDEX "currencies_code_unique" on "currencies"("code");
CREATE UNIQUE INDEX "currencies_iso_code_unique" on "currencies"("iso_code");
CREATE TABLE IF NOT EXISTS "prices"(
  "id" integer primary key autoincrement not null,
  "priceable_id" integer not null,
  "priceable_type" varchar not null,
  "currency_id" integer not null,
  "amount" numeric not null,
  "compare_amount" numeric,
  "type" varchar check("type" in('retail', 'wholesale', 'special', 'sale')) not null default 'retail',
  "starts_at" datetime,
  "ends_at" datetime,
  "is_enabled" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "cost_amount" numeric,
  "metadata" text,
  foreign key("currency_id") references "currencies"("id") on delete cascade
);
CREATE INDEX "prices_priceable_id_priceable_type_index" on "prices"(
  "priceable_id",
  "priceable_type"
);
CREATE INDEX "prices_currency_id_is_enabled_index" on "prices"(
  "currency_id",
  "is_enabled"
);
CREATE INDEX "prices_composite_idx" on "prices"(
  "priceable_id",
  "priceable_type",
  "currency_id",
  "is_enabled"
);
CREATE INDEX "prices_type_is_enabled_index" on "prices"("type", "is_enabled");
CREATE INDEX "prices_starts_at_ends_at_index" on "prices"(
  "starts_at",
  "ends_at"
);
CREATE TABLE IF NOT EXISTS "price_translations"(
  "id" integer primary key autoincrement not null,
  "price_id" integer not null,
  "locale" varchar not null,
  "name" varchar,
  "description" text,
  "notes" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("price_id") references "prices"("id") on delete cascade
);
CREATE UNIQUE INDEX "price_translations_price_id_locale_unique" on "price_translations"(
  "price_id",
  "locale"
);
CREATE INDEX "price_translations_locale_name_index" on "price_translations"(
  "locale",
  "name"
);
CREATE INDEX "price_translations_locale_index" on "price_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "currency_translations"(
  "id" integer primary key autoincrement not null,
  "currency_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("currency_id") references "currencies"("id") on delete cascade
);
CREATE UNIQUE INDEX "currency_translations_currency_id_locale_unique" on "currency_translations"(
  "currency_id",
  "locale"
);
CREATE INDEX "currency_translations_locale_index" on "currency_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "product_variants"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "name" varchar not null,
  "sku" varchar not null,
  "barcode" varchar,
  "price" numeric not null,
  "cost_price" numeric,
  "stock_quantity" integer not null default '0',
  "weight" numeric,
  "track_inventory" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "is_enabled" tinyint(1) not null default '1',
  "attributes" text,
  "created_at" datetime,
  "updated_at" datetime,
  "size" varchar,
  "size_unit" varchar not null default 'cm',
  "size_display" varchar,
  "size_price_modifier" numeric not null default '0',
  "size_weight_modifier" numeric not null default '0',
  "variant_type" varchar check("variant_type" in('size', 'color', 'material', 'style', 'custom')) not null default 'size',
  "is_default_variant" tinyint(1) not null default '0',
  "variant_sku_suffix" varchar,
  "allow_backorder" tinyint(1) not null default '0',
  "low_stock_threshold" integer not null default '5',
  "variant_name_lt" varchar,
  "variant_name_en" varchar,
  "description_lt" text,
  "description_en" text,
  "wholesale_price" numeric,
  "member_price" numeric,
  "promotional_price" numeric,
  "is_on_sale" tinyint(1) not null default '0',
  "sale_start_date" datetime,
  "sale_end_date" datetime,
  "reserved_quantity" integer not null default '0',
  "available_quantity" integer not null default '0',
  "sold_quantity" integer not null default '0',
  "seo_title_lt" varchar,
  "seo_title_en" varchar,
  "seo_description_lt" text,
  "seo_description_en" text,
  "views_count" integer not null default '0',
  "clicks_count" integer not null default '0',
  "conversion_rate" numeric not null default '0',
  "is_featured" tinyint(1) not null default '0',
  "is_new" tinyint(1) not null default '0',
  "is_bestseller" tinyint(1) not null default '0',
  "variant_combination_hash" varchar,
  "deleted_at" datetime,
  "variant_attribute_matrix" text,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "product_variants_product_id_is_enabled_index" on "product_variants"(
  "product_id",
  "is_enabled"
);
CREATE INDEX "product_variants_sku_index" on "product_variants"("sku");
CREATE TABLE IF NOT EXISTS "attributes"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "type" varchar not null default 'text',
  "is_required" tinyint(1) not null default '0',
  "is_filterable" tinyint(1) not null default '0',
  "is_searchable" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "is_enabled" tinyint(1) not null default '1',
  "options" text,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "validation_rules" text,
  "default_value" text,
  "is_visible" tinyint(1) not null default '1',
  "is_editable" tinyint(1) not null default '1',
  "is_sortable" tinyint(1) not null default '1',
  "category_id" integer,
  "group_name" varchar,
  "icon" varchar,
  "color" varchar,
  "min_value" numeric,
  "max_value" numeric,
  "step_value" numeric,
  "placeholder" varchar,
  "help_text" text,
  "meta_data" text,
  "deleted_at" datetime,
  "is_active" tinyint(1) not null default '1',
  "min_length" integer,
  "max_length" integer
);
CREATE INDEX "attributes_is_enabled_sort_order_index" on "attributes"(
  "is_enabled",
  "sort_order"
);
CREATE UNIQUE INDEX "attributes_slug_unique" on "attributes"("slug");
CREATE TABLE IF NOT EXISTS "attribute_values"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "value" varchar not null,
  "slug" varchar not null,
  "color_code" varchar,
  "sort_order" integer not null default '0',
  "is_enabled" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "description" text,
  "hex_color" varchar,
  "image" varchar,
  "metadata" text,
  "display_value" varchar,
  "is_active" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "attribute_value_type" varchar not null default 'text',
  "valueable_type" varchar,
  "valueable_id" integer,
  "is_searchable" tinyint(1) not null default '0',
  foreign key("attribute_id") references "attributes"("id") on delete cascade
);
CREATE UNIQUE INDEX "attribute_values_attribute_id_slug_unique" on "attribute_values"(
  "attribute_id",
  "slug"
);
CREATE INDEX "attribute_values_attribute_id_is_enabled_index" on "attribute_values"(
  "attribute_id",
  "is_enabled"
);
CREATE TABLE IF NOT EXISTS "customer_group_user"(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_group_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(customer_group_id, user_id)
);
CREATE TABLE IF NOT EXISTS "wishlists"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "wishlists_user_id_product_id_unique" on "wishlists"(
  "user_id",
  "product_id"
);
CREATE TABLE IF NOT EXISTS "settings"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "value" text,
  "type" varchar not null default 'string',
  "description" text,
  "is_public" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "display_name" varchar,
  "group" varchar,
  "is_required" tinyint(1) not null default '0',
  "is_encrypted" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1'
);
CREATE INDEX "settings_key_index" on "settings"("key");
CREATE INDEX "settings_is_public_index" on "settings"("is_public");
CREATE UNIQUE INDEX "settings_key_unique" on "settings"("key");
CREATE INDEX "product_variants_product_id_variant_type_index" on "product_variants"(
  "product_id",
  "variant_type"
);
CREATE INDEX "product_variants_product_id_size_index" on "product_variants"(
  "product_id",
  "size"
);
CREATE INDEX "product_variants_is_default_variant_index" on "product_variants"(
  "is_default_variant"
);
CREATE INDEX "product_variants_variant_sku_suffix_index" on "product_variants"(
  "variant_sku_suffix"
);
CREATE TABLE IF NOT EXISTS "variant_pricing_rules"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "product_variant_id" integer,
  "customer_group_id" integer,
  "name" varchar not null,
  "type" varchar not null default 'percentage',
  "value" numeric not null default '0',
  "min_quantity" integer,
  "max_quantity" integer,
  "priority" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "is_cumulative" tinyint(1) not null default '0',
  "valid_from" datetime,
  "valid_until" datetime,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("product_variant_id") references "product_variants"("id") on delete cascade,
  foreign key("customer_group_id") references "customer_groups"("id") on delete set null
);
CREATE INDEX "variant_pricing_rules_product_id_type_is_active_index" on "variant_pricing_rules"(
  "product_id",
  "type",
  "is_active"
);
CREATE INDEX "variant_pricing_rules_product_variant_id_is_active_index" on "variant_pricing_rules"(
  "product_variant_id",
  "is_active"
);
CREATE INDEX "variant_pricing_rules_priority_index" on "variant_pricing_rules"(
  "priority"
);
CREATE TABLE IF NOT EXISTS "variant_images"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "image_path" varchar not null,
  "alt_text" varchar,
  "sort_order" integer not null default '0',
  "is_primary" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "description" text,
  "is_active" tinyint(1) not null default '1',
  "file_size" integer,
  "dimensions" varchar,
  foreign key("variant_id") references "product_variants"("id") on delete cascade
);
CREATE INDEX "variant_images_variant_id_sort_order_index" on "variant_images"(
  "variant_id",
  "sort_order"
);
CREATE INDEX "variant_images_is_primary_index" on "variant_images"(
  "is_primary"
);
CREATE TABLE IF NOT EXISTS "variant_combinations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "attribute_combinations" text not null,
  "is_available" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "combination_hash" varchar,
  "deleted_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "variant_combinations_product_id_is_available_index" on "variant_combinations"(
  "product_id",
  "is_available"
);
CREATE TABLE IF NOT EXISTS "location_translations"(
  "id" integer primary key autoincrement not null,
  "location_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "location_translations_location_id_locale_unique" on "location_translations"(
  "location_id",
  "locale"
);
CREATE UNIQUE INDEX "location_translations_locale_slug_unique" on "location_translations"(
  "locale",
  "slug"
);
CREATE INDEX "location_translations_locale_location_id_index" on "location_translations"(
  "locale",
  "location_id"
);
CREATE TABLE IF NOT EXISTS "wishlist_items"(
  "id" integer primary key autoincrement not null,
  "wishlist_id" integer not null,
  "product_id" integer not null,
  "variant_id" integer,
  "quantity" integer not null default '1',
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("wishlist_id") references "user_wishlists"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("variant_id") references "product_variants"("id") on delete cascade
);
CREATE UNIQUE INDEX "wishlist_items_wishlist_id_product_id_variant_id_unique" on "wishlist_items"(
  "wishlist_id",
  "product_id",
  "variant_id"
);
CREATE INDEX "wishlist_items_product_id_index" on "wishlist_items"(
  "product_id"
);
CREATE INDEX "product_variants_product_id_is_enabled_is_featured_index" on "product_variants"(
  "product_id",
  "is_enabled",
  "is_featured"
);
CREATE INDEX "product_variants_product_id_variant_type_size_index" on "product_variants"(
  "product_id",
  "variant_type",
  "size"
);
CREATE INDEX "product_variants_is_on_sale_sale_start_date_sale_end_date_index" on "product_variants"(
  "is_on_sale",
  "sale_start_date",
  "sale_end_date"
);
CREATE INDEX "product_variants_is_featured_is_new_is_bestseller_index" on "product_variants"(
  "is_featured",
  "is_new",
  "is_bestseller"
);
CREATE INDEX "product_variants_views_count_clicks_count_conversion_rate_index" on "product_variants"(
  "views_count",
  "clicks_count",
  "conversion_rate"
);
CREATE INDEX "product_variants_variant_combination_hash_index" on "product_variants"(
  "variant_combination_hash"
);
CREATE TABLE IF NOT EXISTS "variant_attribute_values"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "attribute_id" integer not null,
  "attribute_name" varchar,
  "attribute_value" varchar not null,
  "attribute_value_display" varchar,
  "attribute_value_lt" varchar,
  "attribute_value_en" varchar,
  "attribute_value_slug" varchar not null,
  "sort_order" integer not null default '0',
  "is_filterable" tinyint(1) not null default '1',
  "is_searchable" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("variant_id") references "product_variants"("id") on delete cascade,
  foreign key("attribute_id") references "attributes"("id") on delete cascade
);
CREATE INDEX "variant_attribute_values_attribute_id_attribute_value_index" on "variant_attribute_values"(
  "attribute_id",
  "attribute_value"
);
CREATE INDEX "variant_attribute_values_variant_id_sort_order_index" on "variant_attribute_values"(
  "variant_id",
  "sort_order"
);
CREATE INDEX "variant_attribute_values_is_filterable_is_searchable_index" on "variant_attribute_values"(
  "is_filterable",
  "is_searchable"
);
CREATE TABLE IF NOT EXISTS "variant_bundles"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "bundled_variant_id" integer not null,
  "quantity" integer not null default '1',
  "discount_percentage" numeric not null default '0',
  "fixed_discount" numeric not null default '0',
  "is_required" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("variant_id") references "product_variants"("id") on delete cascade,
  foreign key("bundled_variant_id") references "product_variants"("id") on delete cascade
);
CREATE INDEX "variant_bundles_variant_id_sort_order_index" on "variant_bundles"(
  "variant_id",
  "sort_order"
);
CREATE INDEX "variant_bundles_bundled_variant_id_index" on "variant_bundles"(
  "bundled_variant_id"
);
CREATE TABLE IF NOT EXISTS "cities"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "code" varchar not null,
  "description" text,
  "is_enabled" tinyint(1) not null default('1'),
  "is_default" tinyint(1) not null default('0'),
  "is_capital" tinyint(1) not null default('0'),
  "country_id" integer,
  "zone_id" integer,
  "region_id" integer,
  "parent_id" integer,
  "level" integer not null default('0'),
  "latitude" numeric,
  "longitude" numeric,
  "population" integer,
  "postal_codes" text,
  "sort_order" integer not null default('0'),
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "type" varchar,
  "area" numeric,
  "density" numeric,
  "elevation" numeric,
  "timezone" varchar,
  "currency_code" varchar,
  "currency_symbol" varchar,
  "language_code" varchar,
  "language_name" varchar,
  "phone_code" varchar,
  "postal_code" varchar,
  "is_active" tinyint(1) not null default '1',
  foreign key("zone_id") references "zones"("id") on delete set null,
  foreign key("parent_id") references "cities"("id") on delete cascade
);
CREATE INDEX "cities_code_is_enabled_index" on "cities"("code", "is_enabled");
CREATE UNIQUE INDEX "cities_code_unique" on "cities"("code");
CREATE INDEX "cities_country_id_is_enabled_index" on "cities"(
  "country_id",
  "is_enabled"
);
CREATE INDEX "cities_is_capital_is_enabled_index" on "cities"(
  "is_capital",
  "is_enabled"
);
CREATE INDEX "cities_is_enabled_is_default_index" on "cities"(
  "is_enabled",
  "is_default"
);
CREATE INDEX "cities_latitude_longitude_index" on "cities"(
  "latitude",
  "longitude"
);
CREATE INDEX "cities_level_sort_order_index" on "cities"(
  "level",
  "sort_order"
);
CREATE INDEX "cities_parent_id_level_index" on "cities"("parent_id", "level");
CREATE INDEX "cities_region_id_is_enabled_index" on "cities"(
  "region_id",
  "is_enabled"
);
CREATE UNIQUE INDEX "cities_slug_unique" on "cities"("slug");
CREATE INDEX "cities_zone_id_is_enabled_index" on "cities"(
  "zone_id",
  "is_enabled"
);
CREATE TABLE IF NOT EXISTS "city_translations"(
  "id" integer primary key autoincrement not null,
  "city_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("city_id") references "cities"("id") on delete cascade
);
CREATE UNIQUE INDEX "city_translations_city_id_locale_unique" on "city_translations"(
  "city_id",
  "locale"
);
CREATE INDEX "city_translations_locale_city_id_index" on "city_translations"(
  "locale",
  "city_id"
);
CREATE TABLE IF NOT EXISTS "referral_campaigns"(
  "id" integer primary key autoincrement not null,
  "name" text not null,
  "description" text,
  "is_active" tinyint(1) not null default '1',
  "start_date" datetime,
  "end_date" datetime,
  "reward_amount" numeric,
  "reward_type" varchar,
  "max_referrals_per_user" integer,
  "max_total_referrals" integer,
  "conditions" text,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deprecated_at" datetime
);
CREATE INDEX "referral_campaigns_is_active_start_date_end_date_index" on "referral_campaigns"(
  "is_active",
  "start_date",
  "end_date"
);
CREATE TABLE IF NOT EXISTS "referral_codes"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "code" varchar not null,
  "is_active" tinyint(1) not null default '1',
  "expires_at" datetime,
  "metadata" text,
  "title" text,
  "description" text,
  "usage_limit" integer,
  "usage_count" integer not null default '0',
  "reward_amount" numeric,
  "reward_type" varchar,
  "conditions" text,
  "campaign_id" integer,
  "source" varchar,
  "tags" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("campaign_id") references "referral_campaigns"("id")
);
CREATE INDEX "referral_codes_user_id_is_active_index" on "referral_codes"(
  "user_id",
  "is_active"
);
CREATE INDEX "referral_codes_code_index" on "referral_codes"("code");
CREATE INDEX "referral_codes_campaign_id_index" on "referral_codes"(
  "campaign_id"
);
CREATE INDEX "referral_codes_source_index" on "referral_codes"("source");
CREATE INDEX "referral_codes_reward_type_index" on "referral_codes"(
  "reward_type"
);
CREATE INDEX "referral_codes_is_active_expires_at_index" on "referral_codes"(
  "is_active",
  "expires_at"
);
CREATE UNIQUE INDEX "referral_codes_code_unique" on "referral_codes"("code");
CREATE TABLE IF NOT EXISTS "referrals"(
  "id" integer primary key autoincrement not null,
  "referrer_id" integer not null,
  "referred_id" integer not null,
  "referral_code" varchar not null,
  "status" varchar not null default 'pending',
  "completed_at" datetime,
  "expires_at" datetime,
  "metadata" text,
  "source" varchar,
  "campaign" varchar,
  "utm_source" varchar,
  "utm_medium" varchar,
  "utm_campaign" varchar,
  "ip_address" varchar,
  "user_agent" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "title" text,
  "description" text,
  "terms_conditions" text,
  "benefits_description" text,
  "how_it_works" text,
  "seo_title" text,
  "seo_description" text,
  "seo_keywords" text,
  foreign key("referrer_id") references "users"("id") on delete cascade,
  foreign key("referred_id") references "users"("id") on delete cascade
);
CREATE INDEX "referrals_referrer_id_status_index" on "referrals"(
  "referrer_id",
  "status"
);
CREATE INDEX "referrals_referred_id_index" on "referrals"("referred_id");
CREATE INDEX "referrals_referral_code_index" on "referrals"("referral_code");
CREATE INDEX "referrals_status_completed_at_index" on "referrals"(
  "status",
  "completed_at"
);
CREATE INDEX "referrals_source_index" on "referrals"("source");
CREATE INDEX "referrals_campaign_index" on "referrals"("campaign");
CREATE INDEX "referrals_created_at_index" on "referrals"("created_at");
CREATE INDEX "referrals_expires_at_index" on "referrals"("expires_at");
CREATE UNIQUE INDEX "referrals_referral_code_unique" on "referrals"(
  "referral_code"
);
CREATE TABLE IF NOT EXISTS "referral_rewards"(
  "id" integer primary key autoincrement not null,
  "referral_id" integer,
  "user_id" integer not null,
  "order_id" integer,
  "type" varchar not null,
  "amount" numeric not null,
  "currency_code" varchar not null default 'EUR',
  "status" varchar not null default 'pending',
  "applied_at" datetime,
  "expires_at" datetime,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "title" text,
  "description" text,
  "is_active" tinyint(1) not null default '1',
  "priority" integer not null default '0',
  "conditions" text,
  "reward_data" text,
  "deleted_at" datetime,
  foreign key("referral_id") references "referrals"("id") on delete set null,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("order_id") references "orders"("id")
);
CREATE INDEX "referral_rewards_user_id_status_index" on "referral_rewards"(
  "user_id",
  "status"
);
CREATE INDEX "referral_rewards_referral_id_index" on "referral_rewards"(
  "referral_id"
);
CREATE INDEX "referral_rewards_order_id_index" on "referral_rewards"(
  "order_id"
);
CREATE INDEX "referral_rewards_type_status_index" on "referral_rewards"(
  "type",
  "status"
);
CREATE UNIQUE INDEX "users_referral_code_unique" on "users"("referral_code");
CREATE TABLE IF NOT EXISTS "referral_reward_logs"(
  "id" integer primary key autoincrement not null,
  "referral_reward_id" integer not null,
  "user_id" integer,
  "action" varchar not null,
  "data" text,
  "ip_address" varchar,
  "user_agent" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("referral_reward_id") references "referral_rewards"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "referral_reward_logs_referral_reward_id_action_index" on "referral_reward_logs"(
  "referral_reward_id",
  "action"
);
CREATE INDEX "referral_reward_logs_user_id_created_at_index" on "referral_reward_logs"(
  "user_id",
  "created_at"
);
CREATE INDEX "referral_reward_logs_action_created_at_index" on "referral_reward_logs"(
  "action",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "order_translations"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "locale" varchar not null,
  "notes" text,
  "billing_address" text,
  "shipping_address" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE UNIQUE INDEX "order_translations_order_id_locale_unique" on "order_translations"(
  "order_id",
  "locale"
);
CREATE INDEX "order_translations_locale_index" on "order_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "sh_product_variant_attributes"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "attribute_value_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("variant_id") references "product_variants"("id") on delete cascade,
  foreign key("attribute_value_id") references "sh_attribute_values"("id") on delete cascade
);
CREATE UNIQUE INDEX "variant_attribute_value_unique" on "sh_product_variant_attributes"(
  "variant_id",
  "attribute_value_id"
);
CREATE UNIQUE INDEX "referrals_referred_id_unique" on "referrals"(
  "referred_id"
);
CREATE INDEX "attributes_category_id_index" on "attributes"("category_id");
CREATE INDEX "attributes_group_name_index" on "attributes"("group_name");
CREATE INDEX "attributes_type_index" on "attributes"("type");
CREATE INDEX "attributes_is_enabled_is_visible_index" on "attributes"(
  "is_enabled",
  "is_visible"
);
CREATE INDEX "attributes_is_filterable_is_searchable_index" on "attributes"(
  "is_filterable",
  "is_searchable"
);
CREATE TABLE IF NOT EXISTS "customer_groups"(
  "id" integer primary key autoincrement not null,
  "name" text,
  "slug" varchar not null,
  "code" varchar,
  "description" text,
  "discount_percentage" numeric not null default('0'),
  "is_active" tinyint(1) not null default('1'),
  "created_at" datetime,
  "updated_at" datetime,
  "discount_fixed" numeric not null default('0'),
  "has_special_pricing" tinyint(1) not null default('0'),
  "has_volume_discounts" tinyint(1) not null default('0'),
  "can_view_prices" tinyint(1) not null default('1'),
  "can_place_orders" tinyint(1) not null default('1'),
  "can_view_catalog" tinyint(1) not null default('1'),
  "can_use_coupons" tinyint(1) not null default('1'),
  "is_default" tinyint(1) not null default('0'),
  "sort_order" integer not null default('0'),
  "type" varchar not null default('regular'),
  "metadata" text,
  "deleted_at" datetime,
  "color" varchar,
  "icon" varchar,
  "minimum_order_amount" numeric not null default('0'),
  "credit_limit" numeric not null default('0'),
  "payment_terms" varchar not null default('net_30'),
  "is_enabled" tinyint(1) not null default('1'),
  "conditions" text
);
CREATE UNIQUE INDEX "customer_groups_code_unique" on "customer_groups"("code");
CREATE UNIQUE INDEX "customer_groups_slug_unique" on "customer_groups"("slug");
CREATE TABLE IF NOT EXISTS "review_translations"(
  "id" integer primary key autoincrement not null,
  "review_id" integer not null,
  "locale" varchar not null,
  "title" varchar,
  "comment" text,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("review_id") references "reviews"("id") on delete cascade
);
CREATE UNIQUE INDEX "review_translations_review_id_locale_unique" on "review_translations"(
  "review_id",
  "locale"
);
CREATE INDEX "review_translations_locale_review_id_index" on "review_translations"(
  "locale",
  "review_id"
);
CREATE TABLE IF NOT EXISTS "stock_reservations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer,
  "variant_inventory_id" integer,
  "quantity" integer not null,
  "status" varchar not null default('reserved'),
  "reserved_at" datetime not null default(CURRENT_TIMESTAMP),
  "expires_at" datetime,
  "released_at" datetime,
  "consumed_at" datetime,
  "meta" text,
  "reference_type" varchar,
  "reference_id" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("variant_inventory_id") references "variant_inventories"("id") on delete cascade
);
CREATE INDEX "stock_reservations_product_id_status_index" on "stock_reservations"(
  "product_id",
  "status"
);
CREATE INDEX "stock_reservations_status_expires_at_index" on "stock_reservations"(
  "status",
  "expires_at"
);
CREATE INDEX "stock_reservations_variant_inventory_id_status_index" on "stock_reservations"(
  "variant_inventory_id",
  "status"
);
CREATE TABLE IF NOT EXISTS "user_preferences"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "key" varchar not null,
  "value" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "user_preferences_user_id_key_unique" on "user_preferences"(
  "user_id",
  "key"
);
CREATE INDEX "user_preferences_key_index" on "user_preferences"("key");
CREATE INDEX "price_list_items_is_featured_index" on "price_list_items"(
  "is_featured"
);
CREATE TABLE IF NOT EXISTS "notifications"(
  "id" varchar not null,
  "type" varchar not null,
  "notifiable_type" varchar not null,
  "notifiable_id" integer not null,
  "data" text not null,
  "read_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "user_id" integer,
  primary key("id")
);
CREATE INDEX "notifications_notifiable_type_notifiable_id_index" on "notifications"(
  "notifiable_type",
  "notifiable_id"
);
CREATE TABLE IF NOT EXISTS "countries"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "cca2" varchar not null,
  "cca3" varchar not null,
  "ccn3" varchar,
  "currency_code" varchar,
  "phone_code" varchar,
  "flag" varchar,
  "svg_flag" varchar,
  "languages" text,
  "timezones" text,
  "is_enabled" tinyint(1) not null default('1'),
  "sort_order" integer not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "code" varchar,
  "iso_code" varchar,
  "currency_symbol" varchar,
  "is_active" tinyint(1) not null default('1'),
  "is_eu_member" tinyint(1) not null default('0'),
  "requires_vat" tinyint(1) not null default('0'),
  "vat_rate" numeric,
  "timezone" varchar,
  "metadata" text,
  "name_official" varchar,
  "phone_calling_code" varchar,
  "region" varchar,
  "subregion" varchar,
  "latitude" numeric,
  "longitude" numeric,
  "currencies" text,
  "description" text
);
CREATE INDEX "countries_cca2_is_enabled_index" on "countries"(
  "cca2",
  "is_enabled"
);
CREATE UNIQUE INDEX "countries_cca2_unique" on "countries"("cca2");
CREATE UNIQUE INDEX "countries_cca3_unique" on "countries"("cca3");
CREATE UNIQUE INDEX "countries_code_unique" on "countries"("code");
CREATE INDEX "countries_currency_code_index" on "countries"("currency_code");
CREATE INDEX "countries_is_active_index" on "countries"("is_active");
CREATE INDEX "countries_is_active_is_enabled_sort_order_index" on "countries"(
  "is_active",
  "is_enabled",
  "sort_order"
);
CREATE INDEX "countries_is_enabled_index" on "countries"("is_enabled");
CREATE INDEX "countries_is_enabled_sort_order_index" on "countries"(
  "is_enabled",
  "sort_order"
);
CREATE INDEX "countries_is_eu_member_index" on "countries"("is_eu_member");
CREATE UNIQUE INDEX "countries_iso_code_unique" on "countries"("iso_code");
CREATE INDEX "countries_region_index" on "countries"("region");
CREATE INDEX "countries_requires_vat_index" on "countries"("requires_vat");
CREATE INDEX "countries_sort_order_index" on "countries"("sort_order");
CREATE TABLE IF NOT EXISTS "discounts"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "type" varchar not null,
  "value" numeric not null default '0',
  "is_active" tinyint(1) not null default '1',
  "is_enabled" tinyint(1) not null default '1',
  "starts_at" datetime,
  "ends_at" datetime,
  "usage_limit" integer,
  "usage_count" integer not null default '0',
  "channel_id" integer,
  "minimum_amount" numeric,
  "maximum_amount" numeric,
  "zone_id" integer,
  "status" varchar,
  "scope" text,
  "stacking_policy" varchar,
  "metadata" text,
  "priority" integer not null default '0',
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
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("zone_id") references "zones"("id") on delete set null
);
CREATE UNIQUE INDEX "discounts_slug_unique" on "discounts"("slug");
CREATE INDEX "discounts_channel_id_index" on "discounts"("channel_id");
CREATE TABLE IF NOT EXISTS "product_images"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "path" varchar not null,
  "alt_text" varchar,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "is_default" tinyint(1) not null default '0',
  foreign key("product_id") references "products"("id") on delete cascade on update cascade
);
CREATE INDEX "product_images_product_id_sort_order_index" on "product_images"(
  "product_id",
  "sort_order"
);
CREATE INDEX "product_images_product_id_index" on "product_images"(
  "product_id"
);
CREATE INDEX "media_model_type_model_id_collection_name_index" on "media"(
  "model_type",
  "model_id",
  "collection_name"
);
CREATE INDEX "media_collection_name_order_column_index" on "media"(
  "collection_name",
  "order_column"
);
CREATE INDEX "reviews_user_idx" on "reviews"("user_id");
CREATE INDEX "reviews_product_idx" on "reviews"("product_id");
CREATE INDEX "reviews_locale_idx" on "reviews"("locale");
CREATE INDEX "reviews_approved_idx" on "reviews"("is_approved");
CREATE INDEX "user_wishlists_user_idx" on "user_wishlists"("user_id");
CREATE INDEX "customer_group_user_user_idx" on "customer_group_user"(
  "user_id"
);
CREATE INDEX "customer_group_user_group_idx" on "customer_group_user"(
  "customer_group_id"
);
CREATE INDEX "product_translations_product_idx" on "product_translations"(
  "product_id"
);
CREATE INDEX "product_translations_locale_idx" on "product_translations"(
  "locale"
);
CREATE UNIQUE INDEX "product_translations_unique" on "product_translations"(
  "product_id",
  "locale"
);
CREATE INDEX "product_categories_product_idx" on "product_categories"(
  "product_id"
);
CREATE INDEX "product_categories_category_idx" on "product_categories"(
  "category_id"
);
CREATE INDEX "product_collections_product_idx" on "product_collections"(
  "product_id"
);
CREATE INDEX "product_collections_collection_idx" on "product_collections"(
  "collection_id"
);
CREATE INDEX "prices_currency_idx" on "prices"("currency_id");
CREATE INDEX "prices_priceable_idx" on "prices"(
  "priceable_type",
  "priceable_id"
);
CREATE INDEX "documents_documentable_idx" on "documents"(
  "documentable_type",
  "documentable_id"
);
CREATE INDEX "reviews_created_idx" on "reviews"("created_at");
CREATE INDEX "users_email_verified_idx" on "users"("email_verified_at");
CREATE TABLE IF NOT EXISTS "discount_conditions"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer not null,
  "type" varchar not null,
  "operator" varchar not null,
  "value" text,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "is_active" tinyint(1) not null default '1',
  "priority" integer not null default '0',
  "metadata" text,
  foreign key("discount_id") references "discounts"("id") on delete cascade
);
CREATE INDEX "discount_conditions_discount_id_index" on "discount_conditions"(
  "discount_id"
);
CREATE INDEX "discount_conditions_type_index" on "discount_conditions"("type");
CREATE INDEX "discount_conditions_operator_index" on "discount_conditions"(
  "operator"
);
CREATE TABLE IF NOT EXISTS "discount_condition_translations"(
  "id" integer primary key autoincrement not null,
  "discount_condition_id" integer not null,
  "locale" varchar not null,
  "name" varchar,
  "description" text,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_condition_id") references "discount_conditions"("id") on delete cascade
);
CREATE UNIQUE INDEX "discount_condition_translations_discount_condition_id_locale_unique" on "discount_condition_translations"(
  "discount_condition_id",
  "locale"
);
CREATE INDEX "discount_condition_translations_locale_index" on "discount_condition_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "channels"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "code" varchar not null,
  "type" varchar not null default 'web',
  "description" text,
  "url" varchar,
  "domain" varchar,
  "timezone" varchar not null default 'UTC',
  "currency_code" varchar not null default 'EUR',
  "currency_symbol" varchar not null default '€',
  "currency_position" varchar check("currency_position" in('before', 'after')) not null default 'after',
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "ssl_enabled" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "metadata" text,
  "configuration" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "meta_title" varchar,
  "meta_description" text,
  "meta_keywords" text,
  "payment_methods" text,
  "default_payment_method" varchar,
  "shipping_methods" text,
  "default_shipping_method" varchar,
  "free_shipping_threshold" numeric,
  "default_language" varchar not null default 'lt',
  "supported_languages" text,
  "contact_email" varchar,
  "contact_phone" varchar,
  "contact_address" text,
  "social_media" text,
  "legal_documents" text
);
CREATE INDEX "channels_is_enabled_is_default_index" on "channels"(
  "is_enabled",
  "is_default"
);
CREATE INDEX "channels_type_is_active_index" on "channels"(
  "type",
  "is_active"
);
CREATE INDEX "channels_sort_order_index" on "channels"("sort_order");
CREATE UNIQUE INDEX "channels_slug_unique" on "channels"("slug");
CREATE UNIQUE INDEX "channels_code_unique" on "channels"("code");
CREATE TABLE IF NOT EXISTS "inventories"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "location_id" integer not null,
  "quantity" integer not null default '0',
  "reserved" integer not null default '0',
  "incoming" integer not null default '0',
  "threshold" integer not null default '0',
  "is_tracked" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "sku" varchar,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("location_id") references "locations"("id") on delete cascade
);
CREATE INDEX "inventories_product_id_index" on "inventories"("product_id");
CREATE INDEX "inventories_location_id_index" on "inventories"("location_id");
CREATE TABLE IF NOT EXISTS "variant_inventories"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "warehouse_code" varchar not null default('main'),
  "stock" integer not null default('0'),
  "reserved" integer not null default('0'),
  "available" integer not null default('0'),
  "reorder_point" integer not null default('0'),
  "reorder_quantity" integer not null default('0'),
  "is_tracked" tinyint(1) not null default('1'),
  "status" varchar not null default('active'),
  "last_restocked_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "location_id" integer not null default('1'),
  "threshold" integer not null default('0'),
  "notes" text,
  "incoming" integer not null default('0'),
  "last_sold_at" datetime,
  "cost_per_unit" numeric,
  "max_stock_level" integer,
  "supplier_id" integer,
  "batch_number" varchar,
  "expiry_date" date,
  "deleted_at" datetime,
  foreign key("variant_id") references product_variants("id") on delete cascade on update no action,
  foreign key("supplier_id") references "partners"("id") on delete set null
);
CREATE INDEX "variant_inventories_expiry_date_index" on "variant_inventories"(
  "expiry_date"
);
CREATE INDEX "variant_inventories_last_restocked_at_index" on "variant_inventories"(
  "last_restocked_at"
);
CREATE INDEX "variant_inventories_location_id_index" on "variant_inventories"(
  "location_id"
);
CREATE INDEX "variant_inventories_reorder_point_index" on "variant_inventories"(
  "reorder_point"
);
CREATE INDEX "variant_inventories_status_is_tracked_index" on "variant_inventories"(
  "status",
  "is_tracked"
);
CREATE INDEX "variant_inventories_supplier_id_index" on "variant_inventories"(
  "supplier_id"
);
CREATE UNIQUE INDEX "variant_inventories_variant_id_warehouse_code_unique" on "variant_inventories"(
  "variant_id",
  "warehouse_code"
);
CREATE INDEX "variant_inventories_warehouse_code_stock_index" on "variant_inventories"(
  "warehouse_code",
  "stock"
);
CREATE TABLE IF NOT EXISTS "stock_movements"(
  "id" integer primary key autoincrement not null,
  "variant_inventory_id" integer not null,
  "quantity" integer not null,
  "type" varchar check("type" in('in', 'out')) not null,
  "reason" varchar not null,
  "reference" varchar,
  "notes" text,
  "user_id" integer,
  "moved_at" datetime not null default CURRENT_TIMESTAMP,
  "created_at" datetime,
  "updated_at" datetime,
  "correlation_id" varchar,
  foreign key("variant_inventory_id") references "variant_inventories"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "stock_movements_variant_inventory_id_moved_at_index" on "stock_movements"(
  "variant_inventory_id",
  "moved_at"
);
CREATE INDEX "stock_movements_type_reason_index" on "stock_movements"(
  "type",
  "reason"
);
CREATE INDEX "stock_movements_moved_at_index" on "stock_movements"("moved_at");
CREATE INDEX "inventories_sku_index" on "inventories"("sku");
CREATE INDEX "reviews_user_created_idx" on "reviews"("user_id", "created_at");
CREATE INDEX "reviews_prod_loc_approved_created_idx" on "reviews"(
  "product_id",
  "locale",
  "is_approved",
  "created_at"
);
CREATE INDEX "reviews_prod_rating_idx" on "reviews"("product_id", "rating");
CREATE INDEX "media_model_collection_index" on "media"(
  "model_type",
  "model_id",
  "collection_name"
);
CREATE INDEX "media_collection_name_index" on "media"("collection_name");
CREATE INDEX "variant_combinations_product_hash_index" on "variant_combinations"(
  "product_id",
  "combination_hash"
);
CREATE TABLE IF NOT EXISTS "news_translations"(
  "id" integer primary key autoincrement not null,
  "news_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "slug" varchar not null,
  "summary" text,
  "content" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sh_news_translations_locale_index" on "news_translations"(
  "locale"
);
CREATE UNIQUE INDEX "sh_news_translations_news_id_locale_unique" on "news_translations"(
  "news_id",
  "locale"
);
CREATE UNIQUE INDEX "sh_news_translations_locale_slug_unique" on "news_translations"(
  "locale",
  "slug"
);
CREATE INDEX "sh_news_translations_news_id_idx" on "news_translations"(
  "news_id"
);
CREATE INDEX "sh_news_translations_created_at_idx" on "news_translations"(
  "created_at"
);
CREATE TABLE IF NOT EXISTS "menus"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "name" varchar not null,
  "location" varchar,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "description" text
);
CREATE UNIQUE INDEX "menus_key_unique" on "menus"("key");
CREATE TABLE IF NOT EXISTS "menu_items"(
  "id" integer primary key autoincrement not null,
  "menu_id" integer not null,
  "parent_id" integer,
  "label" varchar not null,
  "url" varchar,
  "route_name" varchar,
  "route_params" text,
  "icon" varchar,
  "sort_order" integer not null default '0',
  "is_visible" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("menu_id") references "menus"("id") on delete cascade,
  foreign key("parent_id") references "menu_items"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "legals"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "type" varchar not null default 'legal_document',
  "is_enabled" tinyint(1) not null default '1',
  "is_required" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "meta_data" text,
  "published_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "legals_type_is_enabled_index" on "legals"("type", "is_enabled");
CREATE INDEX "legals_is_required_is_enabled_index" on "legals"(
  "is_required",
  "is_enabled"
);
CREATE INDEX "legals_sort_order_index" on "legals"("sort_order");
CREATE UNIQUE INDEX "legals_key_unique" on "legals"("key");
CREATE INDEX "products_warehouse_qty_idx" on "products"("warehouse_quantity");
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
  "product_variant_id" integer,
  "unit_price" numeric,
  "total_price" numeric,
  "product_snapshot" text,
  "notes" text,
  "attributes" text,
  "minimum_quantity" integer not null default '1',
  "deleted_at" datetime,
  "discount_amount" numeric not null default '0',
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("product_variant_id") references "product_variants"("id") on delete set null
);
CREATE INDEX "cart_items_session_id_index" on "cart_items"("session_id");
CREATE INDEX "cart_items_session_idx" on "cart_items"("session_id");
CREATE INDEX "cart_items_user_id_index" on "cart_items"("user_id");
CREATE INDEX "cart_items_user_idx" on "cart_items"("user_id");
CREATE TABLE IF NOT EXISTS "channel_product"(
  "id" integer primary key autoincrement not null,
  "channel_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("channel_id") references "channels"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "channel_product_channel_id_product_id_unique" on "channel_product"(
  "channel_id",
  "product_id"
);
CREATE INDEX "channel_product_channel_id_index" on "channel_product"(
  "channel_id"
);
CREATE INDEX "channel_product_product_id_index" on "channel_product"(
  "product_id"
);
CREATE INDEX "notifications_user_id_index" on "notifications"("user_id");
CREATE TABLE IF NOT EXISTS "admin_users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "admin_users_email_unique" on "admin_users"("email");
CREATE TABLE IF NOT EXISTS "email_campaigns"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "subject" varchar not null,
  "content" text not null,
  "html_content" text,
  "status" varchar not null default 'draft',
  "target_audience" text,
  "total_recipients" integer not null default '0',
  "sent_count" integer not null default '0',
  "delivered_count" integer not null default '0',
  "opened_count" integer not null default '0',
  "clicked_count" integer not null default '0',
  "unsubscribed_count" integer not null default '0',
  "scheduled_at" datetime,
  "sent_at" datetime,
  "completed_at" datetime,
  "metadata" text,
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "from_email" varchar,
  "from_name" varchar,
  "reply_to" varchar,
  "is_active" tinyint(1) not null default '1',
  "settings" text,
  "deprecated_at" datetime,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE INDEX "email_campaigns_status_scheduled_at_index" on "email_campaigns"(
  "status",
  "scheduled_at"
);
CREATE INDEX "email_campaigns_created_by_index" on "email_campaigns"(
  "created_by"
);
CREATE TABLE IF NOT EXISTS "subscribers"(
  "id" integer primary key autoincrement not null,
  "email" varchar not null,
  "first_name" varchar,
  "last_name" varchar,
  "phone" varchar,
  "company" varchar,
  "job_title" varchar,
  "interests" text,
  "source" varchar not null default('website'),
  "status" varchar not null default('active'),
  "subscribed_at" datetime,
  "unsubscribed_at" datetime,
  "last_email_sent_at" datetime,
  "email_count" integer not null default('0'),
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "user_id" integer,
  "deleted_at" datetime,
  "is_verified" tinyint(1) not null default '0',
  "accepts_marketing" tinyint(1) not null default '1',
  "newsletter_subscription" tinyint(1) not null default '1',
  "unsubscribe_reason" text,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "subscribers_email_index" on "subscribers"("email");
CREATE UNIQUE INDEX "subscribers_email_unique" on "subscribers"("email");
CREATE INDEX "subscribers_source_index" on "subscribers"("source");
CREATE INDEX "subscribers_status_subscribed_at_index" on "subscribers"(
  "status",
  "subscribed_at"
);
CREATE INDEX "subscribers_user_id_index" on "subscribers"("user_id");
CREATE TABLE IF NOT EXISTS "companies"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar,
  "phone" varchar,
  "address" text,
  "website" varchar,
  "industry" varchar,
  "size" varchar check("size" in('small', 'medium', 'large')),
  "description" text,
  "is_active" tinyint(1) not null default '1',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "companies_is_active_index" on "companies"("is_active");
CREATE INDEX "companies_industry_index" on "companies"("industry");
CREATE INDEX "companies_size_index" on "companies"("size");
CREATE TABLE IF NOT EXISTS "regions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "code" varchar not null,
  "description" text,
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "country_id" integer,
  "zone_id" integer,
  "parent_id" integer,
  "level" integer not null default '0',
  "sort_order" integer not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("country_id") references "countries"("id") on delete set null,
  foreign key("zone_id") references "zones"("id") on delete set null,
  foreign key("parent_id") references "regions"("id") on delete cascade
);
CREATE INDEX "regions_is_enabled_is_default_index" on "regions"(
  "is_enabled",
  "is_default"
);
CREATE INDEX "regions_code_is_enabled_index" on "regions"(
  "code",
  "is_enabled"
);
CREATE INDEX "regions_country_id_is_enabled_index" on "regions"(
  "country_id",
  "is_enabled"
);
CREATE INDEX "regions_zone_id_is_enabled_index" on "regions"(
  "zone_id",
  "is_enabled"
);
CREATE INDEX "regions_parent_id_level_index" on "regions"(
  "parent_id",
  "level"
);
CREATE INDEX "regions_level_sort_order_index" on "regions"(
  "level",
  "sort_order"
);
CREATE UNIQUE INDEX "regions_slug_unique" on "regions"("slug");
CREATE UNIQUE INDEX "regions_code_unique" on "regions"("code");
CREATE TABLE IF NOT EXISTS "addresses"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "type" varchar not null default('shipping'),
  "first_name" varchar not null,
  "last_name" varchar not null,
  "company" varchar,
  "address_line_1" varchar not null,
  "address_line_2" varchar,
  "city" varchar not null,
  "state" varchar,
  "postal_code" varchar not null,
  "country_code" varchar not null,
  "phone" varchar,
  "is_default" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "email" varchar,
  "is_billing" tinyint(1) not null default('0'),
  "is_shipping" tinyint(1) not null default('0'),
  "notes" text,
  "apartment" varchar,
  "floor" varchar,
  "building" varchar,
  "landmark" varchar,
  "instructions" text,
  "company_name" varchar,
  "company_vat" varchar,
  "is_active" tinyint(1) not null default('1'),
  "region_id" integer,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("region_id") references "regions"("id") on delete set null
);
CREATE INDEX "addresses_country_code_city_index" on "addresses"(
  "country_code",
  "city"
);
CREATE INDEX "addresses_postal_code_index" on "addresses"("postal_code");
CREATE INDEX "addresses_type_index" on "addresses"("type");
CREATE INDEX "addresses_user_id_is_active_index" on "addresses"(
  "user_id",
  "is_active"
);
CREATE INDEX "addresses_user_id_is_billing_index" on "addresses"(
  "user_id",
  "is_billing"
);
CREATE INDEX "addresses_user_id_is_shipping_index" on "addresses"(
  "user_id",
  "is_shipping"
);
CREATE INDEX "addresses_user_id_type_index" on "addresses"("user_id", "type");
CREATE INDEX "addresses_user_type_idx" on "addresses"("user_id", "type");
CREATE INDEX "products_views_count_index" on "products"("views_count");
CREATE INDEX "brands_is_featured_is_enabled_index" on "brands"(
  "is_featured",
  "is_enabled"
);
CREATE INDEX "brands_is_visible_is_enabled_index" on "brands"(
  "is_visible",
  "is_enabled"
);
CREATE TABLE IF NOT EXISTS "sliders"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "description" text,
  "button_text" varchar,
  "button_url" varchar,
  "image" varchar,
  "background_color" varchar not null default '#ffffff',
  "text_color" varchar not null default '#000000',
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime,
  "slug" varchar,
  "button_color" varchar not null default '#007bff',
  "text_alignment" varchar not null default 'center',
  "content_position" varchar not null default 'center',
  "priority" varchar not null default 'normal',
  "tags" text,
  "custom_attributes" text,
  "target_audience" text,
  "is_featured" tinyint(1) not null default '0',
  "is_scheduled" tinyint(1) not null default '0',
  "start_date" datetime,
  "end_date" datetime,
  "slides" text
);
CREATE TABLE IF NOT EXISTS "slider_translations"(
  "id" integer primary key autoincrement not null,
  "slider_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "description" text,
  "button_text" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("slider_id") references "sliders"("id") on delete cascade
);
CREATE UNIQUE INDEX "slider_translations_slider_id_locale_unique" on "slider_translations"(
  "slider_id",
  "locale"
);
CREATE INDEX "slider_translations_locale_title_index" on "slider_translations"(
  "locale",
  "title"
);
CREATE INDEX "slider_translations_locale_index" on "slider_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "discount_condition_products"(
  "id" integer primary key autoincrement not null,
  "discount_condition_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_condition_id") references "discount_conditions"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "discount_condition_product_unique" on "discount_condition_products"(
  "discount_condition_id",
  "product_id"
);
CREATE INDEX "discount_condition_products_discount_condition_id_index" on "discount_condition_products"(
  "discount_condition_id"
);
CREATE INDEX "discount_condition_products_product_id_index" on "discount_condition_products"(
  "product_id"
);
CREATE TABLE IF NOT EXISTS "discount_condition_categories"(
  "id" integer primary key autoincrement not null,
  "discount_condition_id" integer not null,
  "category_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_condition_id") references "discount_conditions"("id") on delete cascade,
  foreign key("category_id") references "categories"("id") on delete cascade
);
CREATE UNIQUE INDEX "discount_condition_category_unique" on "discount_condition_categories"(
  "discount_condition_id",
  "category_id"
);
CREATE INDEX "discount_condition_categories_discount_condition_id_index" on "discount_condition_categories"(
  "discount_condition_id"
);
CREATE INDEX "discount_condition_categories_category_id_index" on "discount_condition_categories"(
  "category_id"
);
CREATE TABLE IF NOT EXISTS "locations"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "slug" varchar,
  "description" text,
  "type" varchar not null default 'warehouse',
  "address_line_1" varchar,
  "address_line_2" varchar,
  "city" varchar,
  "state" varchar,
  "postal_code" varchar,
  "country_code" varchar,
  "country_id" integer,
  "city_id" integer,
  "phone" varchar,
  "email" varchar,
  "latitude" numeric,
  "longitude" numeric,
  "opening_hours" text,
  "contact_info" text,
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("country_id") references "countries"("id") on delete set null,
  foreign key("city_id") references "cities"("id") on delete set null
);
CREATE INDEX "locations_is_enabled_is_default_index" on "locations"(
  "is_enabled",
  "is_default"
);
CREATE INDEX "locations_type_is_enabled_index" on "locations"(
  "type",
  "is_enabled"
);
CREATE INDEX "locations_country_code_city_index" on "locations"(
  "country_code",
  "city"
);
CREATE INDEX "locations_country_id_city_id_index" on "locations"(
  "country_id",
  "city_id"
);
CREATE INDEX "locations_sort_order_index" on "locations"("sort_order");
CREATE UNIQUE INDEX "locations_code_unique" on "locations"("code");
CREATE TABLE IF NOT EXISTS "enum_values"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "key" varchar not null,
  "value" varchar not null,
  "name" varchar not null,
  "description" text,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "enum_values_type_key_unique" on "enum_values"(
  "type",
  "key"
);
CREATE INDEX "enum_values_type_is_active_index" on "enum_values"(
  "type",
  "is_active"
);
CREATE INDEX "enum_values_type_is_default_index" on "enum_values"(
  "type",
  "is_default"
);
CREATE INDEX "enum_values_type_index" on "enum_values"("type");
CREATE INDEX "discount_conditions_is_active_index" on "discount_conditions"(
  "is_active"
);
CREATE INDEX "discount_conditions_priority_index" on "discount_conditions"(
  "priority"
);
CREATE INDEX "menus_is_active_index" on "menus"("is_active");
CREATE INDEX "menus_location_index" on "menus"("location");
CREATE INDEX "menu_items_menu_id_visible_index" on "menu_items"(
  "menu_id",
  "is_visible"
);
CREATE INDEX "menu_items_parent_sort_index" on "menu_items"(
  "parent_id",
  "sort_order"
);
CREATE TABLE IF NOT EXISTS "product_attributes"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "attribute_id" integer,
  "attribute_value_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("attribute_value_id") references attribute_values("id") on delete cascade on update no action,
  foreign key("attribute_id") references attributes("id") on delete cascade on update no action,
  foreign key("product_id") references products("id") on delete cascade on update no action
);
CREATE UNIQUE INDEX "product_attribute_unique" on "product_attributes"(
  "product_id",
  "attribute_id"
);
CREATE INDEX "product_attributes_attribute_idx" on "product_attributes"(
  "attribute_id"
);
CREATE INDEX "product_attributes_product_idx" on "product_attributes"(
  "product_id"
);
CREATE INDEX "product_attributes_value_idx" on "product_attributes"(
  "attribute_value_id"
);
CREATE TABLE IF NOT EXISTS "product_variant_attributes"(
  "id" integer primary key autoincrement not null,
  "variant_id" integer not null,
  "attribute_id" integer,
  "attribute_value_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("attribute_value_id") references attribute_values("id") on delete cascade on update no action,
  foreign key("attribute_id") references attributes("id") on delete cascade on update no action,
  foreign key("variant_id") references product_variants("id") on delete cascade on update no action
);
CREATE INDEX "product_variant_attributes_variant_id_attribute_value_id_index" on "product_variant_attributes"(
  "variant_id",
  "attribute_value_id"
);
CREATE UNIQUE INDEX "variant_attribute_unique" on "product_variant_attributes"(
  "variant_id",
  "attribute_id"
);
CREATE TABLE IF NOT EXISTS "system_setting_histories"(
  "id" integer primary key autoincrement not null,
  "system_setting_id" integer not null,
  "changed_by" integer not null,
  "change_reason" varchar,
  "old_value" text,
  "new_value" text,
  "ip_address" varchar,
  "user_agent" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("system_setting_id") references "system_settings"("id") on delete cascade,
  foreign key("changed_by") references "users"("id") on delete cascade
);
CREATE INDEX "system_setting_histories_system_setting_id_changed_by_index" on "system_setting_histories"(
  "system_setting_id",
  "changed_by"
);
CREATE TABLE IF NOT EXISTS "collection_rules"(
  "id" integer primary key autoincrement not null,
  "collection_id" integer not null,
  "field" varchar not null,
  "operator" varchar not null,
  "value" varchar,
  "position" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("collection_id") references "collections"("id") on delete cascade
);
CREATE INDEX "collection_rules_collection_id_index" on "collection_rules"(
  "collection_id"
);
CREATE INDEX "collection_rules_is_active_index" on "collection_rules"(
  "is_active"
);
CREATE TABLE IF NOT EXISTS "seo_data"(
  "id" integer primary key autoincrement not null,
  "seoable_type" varchar,
  "seoable_id" integer,
  "locale" varchar not null,
  "title" varchar,
  "description" text,
  "keywords" text,
  "canonical_url" varchar,
  "meta_tags" text,
  "structured_data" text,
  "no_index" tinyint(1) not null default('0'),
  "no_follow" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "type" varchar,
  "url" varchar,
  "is_indexed" tinyint(1) not null default '1',
  "is_canonical" tinyint(1) not null default '0',
  "deleted_at" datetime
);
CREATE INDEX "seo_data_locale_no_index_index" on "seo_data"(
  "locale",
  "no_index"
);
CREATE INDEX "seo_data_seoable_type_seoable_id_index" on "seo_data"(
  "seoable_type",
  "seoable_id"
);
CREATE UNIQUE INDEX "seo_data_seoable_type_seoable_id_locale_unique" on "seo_data"(
  "seoable_type",
  "seoable_id",
  "locale"
);
CREATE INDEX "system_settings_category_index" on "system_settings"("category");
CREATE INDEX "order_shippings_status_idx" on "order_shippings"("status");
CREATE INDEX "order_shippings_created_at_idx" on "order_shippings"(
  "created_at"
);
CREATE INDEX "order_shippings_tracking_number_idx" on "order_shippings"(
  "tracking_number"
);
CREATE INDEX "attribute_values_attribute_value_type_index" on "attribute_values"(
  "attribute_value_type"
);
CREATE INDEX "attribute_values_valueable_type_index" on "attribute_values"(
  "valueable_type"
);
CREATE INDEX "attribute_values_valueable_type_valueable_id_index" on "attribute_values"(
  "valueable_type",
  "valueable_id"
);
CREATE INDEX "attribute_values_is_searchable_index" on "attribute_values"(
  "is_searchable"
);
CREATE TABLE IF NOT EXISTS "news"(
  "id" integer primary key autoincrement not null,
  "is_visible" tinyint(1) not null default('1'),
  "is_featured" tinyint(1) not null default('0'),
  "published_at" datetime,
  "author_name" varchar,
  "author_email" varchar,
  "view_count" integer not null default('0'),
  "meta_data" text,
  "created_at" datetime,
  "updated_at" datetime,
  "moderation_state" varchar not null default 'draft',
  "submitted_for_review_at" datetime,
  "approved_at" datetime,
  "approved_by_id" integer,
  "deleted_at" datetime,
  "is_breaking" tinyint(1) not null default '0',
  foreign key("approved_by_id") references "users"("id") on delete set null
);
CREATE INDEX "news_created_at_idx" on "news"("created_at");
CREATE INDEX "news_published_at_idx" on "news"("published_at");
CREATE INDEX "news_visible_published_idx" on "news"(
  "is_visible",
  "published_at"
);
CREATE INDEX "news_moderation_state_index" on "news"("moderation_state");
CREATE TABLE IF NOT EXISTS "posts"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "slug" varchar not null,
  "content" text not null,
  "excerpt" text,
  "status" varchar not null default('draft'),
  "published_at" datetime,
  "user_id" integer not null,
  "meta_title" varchar,
  "meta_description" text,
  "featured" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "title_translations" text,
  "content_translations" text,
  "excerpt_translations" text,
  "meta_title_translations" text,
  "meta_description_translations" text,
  "tags" varchar,
  "tags_translations" text,
  "views_count" integer not null default('0'),
  "likes_count" integer not null default('0'),
  "comments_count" integer not null default('0'),
  "allow_comments" tinyint(1) not null default('1'),
  "is_pinned" tinyint(1) not null default('0'),
  "moderation_state" varchar not null default 'draft',
  "submitted_for_review_at" datetime,
  "approved_at" datetime,
  "approved_by_id" integer,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("approved_by_id") references "users"("id") on delete set null
);
CREATE INDEX "posts_featured_published_at_index" on "posts"(
  "featured",
  "published_at"
);
CREATE INDEX "posts_is_pinned_published_at_index" on "posts"(
  "is_pinned",
  "published_at"
);
CREATE UNIQUE INDEX "posts_slug_unique" on "posts"("slug");
CREATE INDEX "posts_status_featured_published_at_index" on "posts"(
  "status",
  "featured",
  "published_at"
);
CREATE INDEX "posts_status_published_at_index" on "posts"(
  "status",
  "published_at"
);
CREATE INDEX "posts_user_id_status_index" on "posts"("user_id", "status");
CREATE INDEX "posts_moderation_state_index" on "posts"("moderation_state");
CREATE TABLE IF NOT EXISTS "post_approvals"(
  "id" integer primary key autoincrement not null,
  "post_id" integer not null,
  "user_id" integer not null,
  "decision" varchar not null,
  "notes" text,
  "decided_at" datetime not null default CURRENT_TIMESTAMP,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("post_id") references "posts"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "post_approvals_post_id_decided_at_index" on "post_approvals"(
  "post_id",
  "decided_at"
);
CREATE INDEX "variant_images_is_active_index" on "variant_images"("is_active");
CREATE INDEX "stock_movements_correlation_id_index" on "stock_movements"(
  "correlation_id"
);
CREATE INDEX "menus_is_active_location_index" on "menus"(
  "is_active",
  "location"
);
CREATE INDEX "menu_items_menu_id_index" on "menu_items"("menu_id");
CREATE INDEX "menu_items_parent_id_index" on "menu_items"("parent_id");
CREATE INDEX "menu_items_visibility_sort_index" on "menu_items"(
  "is_visible",
  "sort_order"
);
CREATE TABLE IF NOT EXISTS "contact_messages"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "subject" varchar not null,
  "phone" varchar,
  "order_number" varchar,
  "message" text not null,
  "ip_address" varchar,
  "user_agent" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "feature_flags"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "key" varchar not null,
  "description" text,
  "is_active" tinyint(1) not null default('0'),
  "conditions" text,
  "rollout_percentage" text,
  "environment" varchar,
  "starts_at" datetime,
  "ends_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "is_enabled" tinyint(1) not null default('0'),
  "is_global" tinyint(1) not null default('0'),
  "start_date" datetime,
  "end_date" datetime,
  "metadata" text,
  "priority" varchar,
  "category" varchar,
  "impact_level" varchar,
  "rollout_strategy" varchar,
  "rollback_plan" text,
  "success_metrics" text,
  "approval_status" varchar,
  "approval_notes" text,
  "last_activated" datetime,
  "last_deactivated" datetime,
  "created_by_name" integer,
  "updated_by_name" integer,
  "created_by" integer,
  "updated_by" integer,
  foreign key("updated_by_name") references users("id") on delete set null on update no action,
  foreign key("created_by_name") references users("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "feature_flags_key_unique" on "feature_flags"("key");
CREATE INDEX "feature_flags_created_by_name_index" on "feature_flags"(
  "created_by_name"
);
CREATE INDEX "feature_flags_updated_by_name_index" on "feature_flags"(
  "updated_by_name"
);
CREATE TABLE IF NOT EXISTS "coupons"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "description" text,
  "type" varchar not null,
  "value" numeric not null,
  "minimum_amount" numeric,
  "maximum_discount" numeric,
  "usage_limit" integer,
  "usage_limit_per_user" integer,
  "used_count" integer not null default('0'),
  "is_active" tinyint(1) not null default('1'),
  "is_public" tinyint(1) not null default('0'),
  "is_auto_apply" tinyint(1) not null default('0'),
  "is_stackable" tinyint(1) not null default('0'),
  "starts_at" datetime,
  "expires_at" datetime,
  "applicable_products" text,
  "applicable_categories" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "is_first_time_only" tinyint(1) not null default '0',
  "customer_group_id" integer,
  foreign key("customer_group_id") references "customer_groups"("id") on delete set null
);
CREATE INDEX "coupons_code_is_active_index" on "coupons"("code", "is_active");
CREATE UNIQUE INDEX "coupons_code_unique" on "coupons"("code");
CREATE INDEX "coupons_is_active_starts_at_expires_at_index" on "coupons"(
  "is_active",
  "starts_at",
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "discount_customer_groups"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer not null,
  "customer_group_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("discount_id") references "discounts"("id") on delete cascade,
  foreign key("customer_group_id") references "customer_groups"("id") on delete cascade
);
CREATE UNIQUE INDEX "discount_customer_group_unique" on "discount_customer_groups"(
  "discount_id",
  "customer_group_id"
);
CREATE INDEX "discount_customer_groups_discount_idx" on "discount_customer_groups"(
  "discount_id"
);
CREATE INDEX "discount_customer_groups_group_idx" on "discount_customer_groups"(
  "customer_group_id"
);
CREATE TABLE IF NOT EXISTS "discount_codes"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer,
  "code" varchar not null,
  "name" varchar,
  "description" text,
  "description_lt" text,
  "description_en" text,
  "type" varchar not null default('percentage'),
  "value" numeric not null default('0'),
  "minimum_amount" numeric not null default('0'),
  "maximum_discount" numeric,
  "starts_at" datetime,
  "expires_at" datetime,
  "valid_from" datetime,
  "valid_until" datetime,
  "usage_limit" integer,
  "usage_limit_per_user" integer,
  "usage_count" integer not null default('0'),
  "is_active" tinyint(1) not null default('1'),
  "is_public" tinyint(1) not null default('0'),
  "is_auto_apply" tinyint(1) not null default('0'),
  "is_stackable" tinyint(1) not null default('0'),
  "is_first_time_only" tinyint(1) not null default('0'),
  "customer_group_id" integer,
  "status" varchar not null default('inactive'),
  "metadata" text,
  "created_by" integer,
  "updated_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "created_by_name" varchar,
  "updated_by_name" varchar,
  foreign key("created_by") references users("id") on delete set null on update no action,
  foreign key("discount_id") references discounts("id") on delete set null on update no action,
  foreign key("customer_group_id") references customer_groups("id") on delete set null on update no action,
  foreign key("updated_by") references "users"("id") on delete set null
);
CREATE INDEX "discount_codes_active_window_idx" on "discount_codes"(
  "is_active",
  "status",
  "starts_at",
  "expires_at"
);
CREATE UNIQUE INDEX "discount_codes_code_unique_new" on "discount_codes"(
  "code"
);
CREATE INDEX "discount_codes_created_by_index" on "discount_codes"(
  "created_by"
);
CREATE INDEX "discount_codes_customer_status_idx" on "discount_codes"(
  "customer_group_id",
  "status"
);
CREATE INDEX "discount_codes_discount_code_idx" on "discount_codes"(
  "discount_id",
  "code"
);
CREATE INDEX "discount_codes_updated_by_index" on "discount_codes"(
  "updated_by"
);
CREATE INDEX "discount_codes_valid_window_idx" on "discount_codes"(
  "valid_from",
  "valid_until"
);
CREATE TABLE IF NOT EXISTS "discount_redemptions"(
  "id" integer primary key autoincrement not null,
  "discount_id" integer,
  "code_id" integer,
  "order_id" integer,
  "user_id" integer,
  "amount_saved" numeric not null default '0',
  "currency_code" varchar,
  "redeemed_at" datetime,
  "status" varchar not null default 'pending',
  "notes" varchar,
  "ip_address" varchar,
  "user_agent" varchar,
  "metadata" text,
  "created_by" integer,
  "updated_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "created_by_name" varchar,
  "updated_by_name" varchar,
  foreign key("discount_id") references "discounts"("id") on delete set null,
  foreign key("code_id") references "discount_codes"("id") on delete set null,
  foreign key("order_id") references "orders"("id") on delete set null,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null
);
CREATE INDEX "discount_redemptions_discount_status_idx" on "discount_redemptions"(
  "discount_id",
  "status"
);
CREATE INDEX "discount_redemptions_code_user_idx" on "discount_redemptions"(
  "code_id",
  "user_id"
);
CREATE INDEX "discount_redemptions_user_status_idx" on "discount_redemptions"(
  "user_id",
  "status"
);
CREATE INDEX "discount_redemptions_order_status_idx" on "discount_redemptions"(
  "order_id",
  "status"
);
CREATE INDEX "discount_redemptions_redeemed_at_index" on "discount_redemptions"(
  "redeemed_at"
);
CREATE INDEX "discount_redemptions_created_by_index" on "discount_redemptions"(
  "created_by"
);
CREATE INDEX "discount_redemptions_updated_by_index" on "discount_redemptions"(
  "updated_by"
);
CREATE INDEX "email_campaigns_is_active_index" on "email_campaigns"(
  "is_active"
);
CREATE TABLE IF NOT EXISTS "region_translations"(
  "id" integer primary key autoincrement not null,
  "region_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  foreign key("region_id") references "regions"("id") on delete cascade
);
CREATE UNIQUE INDEX "region_translations_region_id_locale_unique" on "region_translations"(
  "region_id",
  "locale"
);
CREATE INDEX "region_translations_locale_index" on "region_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "email_campaign_recipients"(
  "id" integer primary key autoincrement not null,
  "email_campaign_id" integer not null,
  "email" varchar not null,
  "name" varchar,
  "status" varchar not null default 'pending',
  "metadata" text,
  "meta" text,
  "scheduled_at" datetime,
  "sent_at" datetime,
  "delivered_at" datetime,
  "opened_at" datetime,
  "clicked_at" datetime,
  "bounced_at" datetime,
  "unsubscribed_at" datetime,
  "bounce_reason" varchar,
  "error_message" varchar,
  "open_count" integer not null default '0',
  "click_count" integer not null default '0',
  "delivery_attempts" integer not null default '0',
  "is_delivered" tinyint(1) not null default '0',
  "is_opened" tinyint(1) not null default '0',
  "is_clicked" tinyint(1) not null default '0',
  "is_bounced" tinyint(1) not null default '0',
  "is_unsubscribed" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("email_campaign_id") references "email_campaigns"("id") on delete cascade
);
CREATE UNIQUE INDEX "email_campaign_recipients_email_campaign_id_email_unique" on "email_campaign_recipients"(
  "email_campaign_id",
  "email"
);
CREATE INDEX "email_campaign_recipients_status_index" on "email_campaign_recipients"(
  "status"
);
CREATE INDEX "email_campaign_recipients_is_delivered_index" on "email_campaign_recipients"(
  "is_delivered"
);
CREATE INDEX "email_campaign_recipients_is_bounced_index" on "email_campaign_recipients"(
  "is_bounced"
);
CREATE INDEX "products_status_published_at_index" on "products"(
  "status",
  "published_at"
);
CREATE TABLE IF NOT EXISTS "product_variant_translations"(
  "id" integer primary key autoincrement not null,
  "product_variant_id" integer not null,
  "locale" varchar not null,
  "name" varchar,
  "description" text,
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_variant_id") references "product_variants"("id") on delete cascade
);
CREATE UNIQUE INDEX "product_variant_locale_unique" on "product_variant_translations"(
  "product_variant_id",
  "locale"
);
CREATE INDEX "product_variant_translations_locale_idx" on "product_variant_translations"(
  "locale"
);
CREATE TABLE IF NOT EXISTS "payment_webhook_events"(
  "id" integer primary key autoincrement not null,
  "provider" varchar not null,
  "event_id" varchar not null,
  "order_id" integer,
  "status" varchar not null,
  "payload" text,
  "processed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE UNIQUE INDEX "payment_webhook_events_provider_event_unique" on "payment_webhook_events"(
  "provider",
  "event_id"
);
CREATE TABLE IF NOT EXISTS "sh_attributes"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "sh_attributes_slug_unique" on "sh_attributes"("slug");
CREATE TABLE IF NOT EXISTS "sh_attribute_values"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "value" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("attribute_id") references "sh_attributes"("id") on delete cascade
);
CREATE INDEX "cities_country_id_index" on "cities"("country_id");
CREATE UNIQUE INDEX "cities_country_id_slug_unique" on "cities"(
  "country_id",
  "slug"
);
CREATE INDEX "discount_campaigns_deprecated_at_index" on "discount_campaigns"(
  "deprecated_at"
);
CREATE INDEX "email_campaigns_deprecated_at_index" on "email_campaigns"(
  "deprecated_at"
);
CREATE INDEX "referral_campaigns_deprecated_at_index" on "referral_campaigns"(
  "deprecated_at"
);
CREATE TABLE IF NOT EXISTS "campaign_data_archive"(
  "id" integer primary key autoincrement not null,
  "table_name" varchar not null,
  "original_data" text not null,
  "archived_at" datetime not null,
  "archive_reason" varchar not null default 'feature_removal'
);
CREATE INDEX "campaign_data_archive_table_name_archived_at_index" on "campaign_data_archive"(
  "table_name",
  "archived_at"
);
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "number" varchar not null,
  "user_id" integer,
  "status" varchar not null default 'pending',
  "subtotal" numeric not null default '0',
  "tax_amount" numeric not null default '0',
  "shipping_amount" numeric not null default '0',
  "discount_amount" numeric not null default '0',
  "total" numeric not null default '0',
  "currency" varchar not null default 'EUR',
  "billing_address" text,
  "shipping_address" text,
  "notes" text,
  "shipped_at" datetime,
  "delivered_at" datetime,
  "channel_id" integer,
  "country_id" integer,
  "zone_id" integer,
  "shipping_option_id" integer,
  "partner_id" integer,
  "coupon_id" integer,
  "payment_status" varchar,
  "payment_state" varchar,
  "payment_method" varchar,
  "payment_reference" varchar,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "locale" varchar,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "orders_number_index" on "orders"("number");
CREATE INDEX "orders_user_id_index" on "orders"("user_id");
CREATE INDEX "orders_created_at_index" on "orders"("created_at");
CREATE UNIQUE INDEX "orders_number_unique" on "orders"("number");
CREATE INDEX "orders_status_index" on "orders"("status");
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer,
  "product_variant_id" integer,
  "name" varchar,
  "sku" varchar,
  "quantity" integer not null default '1',
  "unit_price" numeric not null default '0',
  "price" numeric,
  "total" numeric not null default '0',
  "discount_amount" numeric not null default '0',
  "status" varchar,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE UNIQUE INDEX "partner_tiers_code_unique" on "partner_tiers"("code");
CREATE TABLE IF NOT EXISTS "news_images"(
  "id" integer primary key autoincrement not null,
  "news_id" integer not null,
  "file_path" varchar not null,
  "alt_text" varchar,
  "caption" varchar,
  "is_featured" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "file_size" integer,
  "mime_type" varchar,
  "dimensions" text,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("news_id") references "news"("id") on delete cascade
);
CREATE INDEX "news_images_news_id_index" on "news_images"("news_id");
CREATE TABLE IF NOT EXISTS "product_features"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "feature_type" varchar not null,
  "feature_key" varchar not null,
  "feature_value" text not null,
  "weight" numeric not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "product_features_product_id_index" on "product_features"(
  "product_id"
);
CREATE INDEX "product_features_feature_type_index" on "product_features"(
  "feature_type"
);
CREATE INDEX "product_features_feature_key_index" on "product_features"(
  "feature_key"
);
CREATE TABLE IF NOT EXISTS "shipping_options"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" varchar,
  "carrier_name" varchar,
  "service_type" varchar,
  "price" numeric,
  "currency_code" varchar not null default 'EUR',
  "country_id" integer,
  "city_id" integer,
  "zone_id" integer,
  "is_enabled" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "min_weight" integer,
  "max_weight" integer,
  "min_order_amount" numeric,
  "max_order_amount" numeric,
  "estimated_days_min" integer,
  "estimated_days_max" integer,
  "metadata" text,
  "shipping_matrix" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "shipping_options_is_enabled_sort_order_index" on "shipping_options"(
  "is_enabled",
  "sort_order"
);
CREATE UNIQUE INDEX "shipping_options_slug_unique" on "shipping_options"(
  "slug"
);
CREATE UNIQUE INDEX "sliders_slug_unique" on "sliders"("slug");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
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
CREATE TABLE IF NOT EXISTS "imports"(
  "id" integer primary key autoincrement not null,
  "completed_at" datetime,
  "file_name" varchar not null,
  "file_path" varchar not null,
  "importer" varchar not null,
  "processed_rows" integer not null default '0',
  "total_rows" integer not null,
  "successful_rows" integer not null default '0',
  "user_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "column_map" text,
  "options" text,
  "file_disk" varchar,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "failed_import_rows"(
  "id" integer primary key autoincrement not null,
  "data" text not null,
  "import_id" integer not null,
  "validation_error" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("import_id") references "imports"("id") on delete cascade
);
CREATE INDEX "product_images_product_id_is_default_index" on "product_images"(
  "product_id",
  "is_default"
);
CREATE TABLE IF NOT EXISTS "product_similarities"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "similar_product_id" integer not null,
  "calculation_data" text,
  "calculated_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("similar_product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "services"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "price" numeric not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "order_service"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "service_id" integer not null,
  "price" numeric not null default '0',
  "quantity" integer not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("service_id") references "services"("id") on delete cascade
);
CREATE INDEX products_sku_index ON products(sku);
CREATE TABLE IF NOT EXISTS "import_row_results"(
  "id" integer primary key autoincrement not null,
  "import_id" integer not null,
  "row_number" integer,
  "status" varchar not null,
  "action" varchar not null,
  "message" text,
  "error_message" text,
  "changed_fields" text,
  "data" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("import_id") references "imports"("id") on delete cascade
);
CREATE INDEX "import_row_results_import_id_row_number_index" on "import_row_results"(
  "import_id",
  "row_number"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(5,'2024_05_13_000000_create_stock_reservations_table',1);
INSERT INTO migrations VALUES(6,'2024_05_23_000000_update_product_images_paths',1);
INSERT INTO migrations VALUES(7,'2024_10_21_000000_create_exports_table',1);
INSERT INTO migrations VALUES(8,'2024_12_16_000001_optimize_storefront_indexes',1);
INSERT INTO migrations VALUES(9,'2024_12_22_000001_create_organizations_table',1);
INSERT INTO migrations VALUES(10,'2024_12_22_000002_create_projects_table',1);
INSERT INTO migrations VALUES(11,'2024_12_22_000003_create_tasks_table',1);
INSERT INTO migrations VALUES(12,'2024_12_22_000004_create_tags_table',1);
INSERT INTO migrations VALUES(13,'2024_12_22_000005_create_comments_table',1);
INSERT INTO migrations VALUES(14,'2024_12_22_000006_create_files_table',1);
INSERT INTO migrations VALUES(15,'2024_12_22_000007_create_pivot_tables',1);
INSERT INTO migrations VALUES(16,'2024_12_22_200000_create_missing_pivot_tables',1);
INSERT INTO migrations VALUES(17,'2025_01_07_000001_drop_notification_templates_table',1);
INSERT INTO migrations VALUES(18,'2025_01_07_000001_drop_wishlist_tables',1);
INSERT INTO migrations VALUES(19,'2025_01_07_000001_restore_comments_composite_index',1);
INSERT INTO migrations VALUES(20,'2025_01_10_000000_create_customer_groups_table',1);
INSERT INTO migrations VALUES(21,'2025_01_15_000000_add_missing_fields_to_customer_groups_table',1);
INSERT INTO migrations VALUES(22,'2025_01_20_130000_enhance_users_table_with_translations_and_additional_fields',1);
INSERT INTO migrations VALUES(23,'2025_01_21_120000_enhance_products_table_comprehensive',1);
INSERT INTO migrations VALUES(24,'2025_01_22_000000_drop_permission_tables',1);
INSERT INTO migrations VALUES(25,'2025_01_27_000001_create_document_templates_table',1);
INSERT INTO migrations VALUES(26,'2025_01_27_000002_create_documents_table',1);
INSERT INTO migrations VALUES(27,'2025_01_27_010000_add_updated_by_to_documents_table',1);
INSERT INTO migrations VALUES(28,'2025_01_28_000001_create_enhanced_filament_system_tables',1);
INSERT INTO migrations VALUES(29,'2025_01_28_120000_create_user_wishlists_table',1);
INSERT INTO migrations VALUES(30,'2025_01_28_130000_enhance_users_table_with_comprehensive_fields',1);
INSERT INTO migrations VALUES(31,'2025_01_30_120001_add_soft_deletes_to_cart_items_table',1);
INSERT INTO migrations VALUES(32,'2025_01_31_000001_create_system_settings_tables',1);
INSERT INTO migrations VALUES(33,'2025_01_31_000002_add_additional_fields_to_system_settings_table',1);
INSERT INTO migrations VALUES(34,'2025_01_31_000002_add_enhanced_fields_to_system_setting_translations_table',1);
INSERT INTO migrations VALUES(35,'2025_01_31_000003_add_meta_columns_to_system_setting_tables',1);
INSERT INTO migrations VALUES(36,'2025_01_31_000004_add_minimum_quantity_to_cart_items_table',1);
INSERT INTO migrations VALUES(37,'2025_02_01_000001_add_indexes_to_seo_data',1);
INSERT INTO migrations VALUES(38,'2025_02_01_000002_add_condition_value_to_system_setting_dependencies_table',1);
INSERT INTO migrations VALUES(39,'2025_02_05_000000_add_condition_value_to_system_setting_dependencies_table',1);
INSERT INTO migrations VALUES(40,'2025_02_10_000000_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(41,'2025_02_15_000000_add_deleted_at_to_customer_groups_table',1);
INSERT INTO migrations VALUES(42,'2025_02_15_120000_add_created_at_indexes',1);
INSERT INTO migrations VALUES(43,'2025_02_20_120000_add_visual_and_financial_fields_to_customer_groups_table',1);
INSERT INTO migrations VALUES(44,'2025_02_24_120000_create_table_settings_table',1);
INSERT INTO migrations VALUES(45,'2025_05_16_183635_create_table_settings_table',1);
INSERT INTO migrations VALUES(46,'2025_08_31_183927_create_media_table',1);
INSERT INTO migrations VALUES(47,'2025_08_31_200000_extend_discounts_and_create_discount_tables',1);
INSERT INTO migrations VALUES(48,'2025_08_31_200100_create_translation_tables',1);
INSERT INTO migrations VALUES(49,'2025_08_31_200200_create_groups_partners_price_lists',1);
INSERT INTO migrations VALUES(50,'2025_08_31_210000_add_performance_indexes',1);
INSERT INTO migrations VALUES(51,'2025_08_31_210100_add_price_indexes',1);
INSERT INTO migrations VALUES(52,'2025_09_01_000010_add_preferred_locale_to_users_table',1);
INSERT INTO migrations VALUES(53,'2025_09_01_000020_add_unique_slug_locale_indexes',1);
INSERT INTO migrations VALUES(54,'2025_09_01_000100_create_discount_codes_table',1);
INSERT INTO migrations VALUES(55,'2025_09_01_000500_add_price_composite_index',1);
INSERT INTO migrations VALUES(56,'2025_09_01_010000_add_payment_fields_to_orders_table',1);
INSERT INTO migrations VALUES(57,'2025_09_01_010100_add_timeline_to_orders_table',1);
INSERT INTO migrations VALUES(58,'2025_09_01_010200_add_tracking_to_order_shippings_table',1);
INSERT INTO migrations VALUES(59,'2025_09_01_010400_create_order_shippings_table',1);
INSERT INTO migrations VALUES(60,'2025_09_01_010500_add_totals_columns_to_orders_table',1);
INSERT INTO migrations VALUES(61,'2025_09_01_020000_add_foreign_keys_and_more_indexes',1);
INSERT INTO migrations VALUES(62,'2025_09_01_050000_add_warehouse_quantity_to_products',1);
INSERT INTO migrations VALUES(63,'2025_09_01_190000_add_status_to_sh_product_variants_table',1);
INSERT INTO migrations VALUES(64,'2025_09_01_191000_create_variant_inventories_table',1);
INSERT INTO migrations VALUES(65,'2025_09_02_130000_test_support_columns',1);
INSERT INTO migrations VALUES(66,'2025_09_02_151200_create_customers_table',1);
INSERT INTO migrations VALUES(67,'2025_09_02_151538_create_new_ecommerce_tables',1);
INSERT INTO migrations VALUES(68,'2025_09_02_151539_add_request_fields_to_products_table',1);
INSERT INTO migrations VALUES(69,'2025_09_02_151539_create_coupon_relationships_tables',1);
INSERT INTO migrations VALUES(70,'2025_09_02_151540_create_product_requests_table',1);
INSERT INTO migrations VALUES(71,'2025_09_02_155500_create_missing_core_tables',1);
INSERT INTO migrations VALUES(72,'2025_09_02_160313_remove_sh_prefix_from_all_tables',1);
INSERT INTO migrations VALUES(73,'2025_09_02_160314_add_multi_language_fields_to_discount_codes_table',1);
INSERT INTO migrations VALUES(74,'2025_09_02_160315_enhance_discount_conditions_table',1);
INSERT INTO migrations VALUES(75,'2025_09_02_160316_add_translatable_fields_to_price_list_items_table',1);
INSERT INTO migrations VALUES(76,'2025_09_02_160317_update_price_lists_table_add_translations',1);
INSERT INTO migrations VALUES(77,'2025_09_02_160318_create_discount_redemption_translations_table',1);
INSERT INTO migrations VALUES(78,'2025_09_02_160322_create_discount_condition_translations_table',1);
INSERT INTO migrations VALUES(79,'2025_09_02_160324_create_price_list_item_translations_table',1);
INSERT INTO migrations VALUES(80,'2025_09_02_162921_create_country_translations_table',1);
INSERT INTO migrations VALUES(81,'2025_09_02_163244_add_timestamps_to_countries_table',1);
INSERT INTO migrations VALUES(82,'2025_09_02_170656_remove_remaining_sh_prefixes_from_tables',1);
INSERT INTO migrations VALUES(83,'2025_09_02_215155_create_pages_table',1);
INSERT INTO migrations VALUES(84,'2025_09_02_215156_fix_slug_unique_constraint_on_pages_table',1);
INSERT INTO migrations VALUES(85,'2025_09_03_000001_add_enhanced_filament_features',1);
INSERT INTO migrations VALUES(86,'2025_09_03_000001_create_currencies_table',1);
INSERT INTO migrations VALUES(87,'2025_09_03_000002_create_prices_table',1);
INSERT INTO migrations VALUES(88,'2025_09_03_000003_add_translation_support_to_prices_table',1);
INSERT INTO migrations VALUES(89,'2025_09_03_000004_create_currency_translations_table',1);
INSERT INTO migrations VALUES(90,'2025_09_03_000005_enhance_filament_tables',1);
INSERT INTO migrations VALUES(91,'2025_09_03_000006_create_attribute_value_translations_table',1);
INSERT INTO migrations VALUES(92,'2025_09_03_000007_enhance_product_variants_system',1);
INSERT INTO migrations VALUES(93,'2025_09_03_000010_create_location_translations_table',1);
INSERT INTO migrations VALUES(94,'2025_09_03_000011_create_enhanced_filament_ecommerce_system',1);
INSERT INTO migrations VALUES(95,'2025_09_03_000013_enhance_product_variants_comprehensive',1);
INSERT INTO migrations VALUES(96,'2025_09_03_000014_create_cities_table',1);
INSERT INTO migrations VALUES(97,'2025_09_03_000015_create_city_translations_table',1);
INSERT INTO migrations VALUES(98,'2025_09_03_000017_update_addresses_table_for_locations',1);
INSERT INTO migrations VALUES(99,'2025_09_03_000018_create_referral_system_tables',1);
INSERT INTO migrations VALUES(100,'2025_09_03_000021_add_translation_fields_to_referral_rewards',1);
INSERT INTO migrations VALUES(101,'2025_09_03_000022_add_translation_fields_to_referrals_table',1);
INSERT INTO migrations VALUES(102,'2025_09_03_000023_create_referral_reward_logs_table',1);
INSERT INTO migrations VALUES(103,'2025_09_03_000025_enhance_product_translations_table',1);
INSERT INTO migrations VALUES(104,'2025_09_03_000026_create_order_translations_table',1);
INSERT INTO migrations VALUES(105,'2025_09_03_000027_create_location_translations_table',1);
INSERT INTO migrations VALUES(106,'2025_09_03_000028_add_missing_columns_to_locations_table',1);
INSERT INTO migrations VALUES(107,'2025_09_03_000029_upgrade_models_and_add_missing_relations',1);
INSERT INTO migrations VALUES(108,'2025_09_03_100000_update_referrals_indexes',1);
INSERT INTO migrations VALUES(109,'2025_09_03_120000_enhance_filament_tables',1);
INSERT INTO migrations VALUES(110,'2025_09_03_120001_add_enhanced_fields_to_attributes_table',1);
INSERT INTO migrations VALUES(111,'2025_09_03_120001_add_translations_to_customer_groups_table',1);
INSERT INTO migrations VALUES(112,'2025_09_03_223900_add_deleted_at_to_product_variants_table',1);
INSERT INTO migrations VALUES(113,'2025_09_03_230000_add_missing_review_columns',1);
INSERT INTO migrations VALUES(114,'2025_09_03_230100_create_order_items_table',1);
INSERT INTO migrations VALUES(115,'2025_09_03_230200_create_review_translations_table',1);
INSERT INTO migrations VALUES(116,'2025_09_03_230500_add_foreign_keys_to_stock_reservations_table',1);
INSERT INTO migrations VALUES(117,'2025_09_04_000000_enhance_filament_system_final',1);
INSERT INTO migrations VALUES(118,'2025_09_04_120000_add_is_featured_to_price_list_items_table',1);
INSERT INTO migrations VALUES(119,'2025_09_04_121000_add_deleted_at_to_price_lists_table',1);
INSERT INTO migrations VALUES(120,'2025_09_04_163608_create_notifications_table',1);
INSERT INTO migrations VALUES(121,'2025_09_04_170000_create_countries_table',1);
INSERT INTO migrations VALUES(122,'2025_09_04_170001_add_missing_fields_to_countries_table',1);
INSERT INTO migrations VALUES(123,'2025_09_04_170001_enhance_countries_table',1);
INSERT INTO migrations VALUES(124,'2025_09_04_175155_add_missing_columns_to_order_shippings_table',1);
INSERT INTO migrations VALUES(125,'2025_09_04_180000_update_countries_table_structure',1);
INSERT INTO migrations VALUES(126,'2025_09_04_200000_add_multilanguage_support_to_enhanced_settings',1);
INSERT INTO migrations VALUES(127,'2025_09_05_000000_add_is_active_to_enhanced_settings_table',1);
INSERT INTO migrations VALUES(128,'2025_09_05_000001_add_is_enabled_to_categories',1);
INSERT INTO migrations VALUES(129,'2025_09_08_193000_align_order_items_columns',1);
INSERT INTO migrations VALUES(130,'2025_09_08_194200_add_price_column_to_order_items',1);
INSERT INTO migrations VALUES(131,'2025_09_08_225900_add_missing_columns_to_settings_table',1);
INSERT INTO migrations VALUES(132,'2025_09_08_230000_add_is_admin_to_users_table',1);
INSERT INTO migrations VALUES(133,'2025_09_09_000000_add_soft_deletes_to_users_table',1);
INSERT INTO migrations VALUES(134,'2025_09_09_000000_create_discounts_table',1);
INSERT INTO migrations VALUES(135,'2025_09_09_000001_create_product_images_table',1);
INSERT INTO migrations VALUES(136,'2025_09_09_000001_optimize_media_indexes',1);
INSERT INTO migrations VALUES(137,'2025_09_09_000002_optimize_customer_relations',1);
INSERT INTO migrations VALUES(138,'2025_09_09_000003_optimize_core_indexes',1);
INSERT INTO migrations VALUES(139,'2025_09_09_000040_create_discount_conditions_table',1);
INSERT INTO migrations VALUES(140,'2025_09_09_000050_create_channels_table',1);
INSERT INTO migrations VALUES(141,'2025_09_09_000100_create_inventories_tables',1);
INSERT INTO migrations VALUES(142,'2025_09_09_000101_enhance_variant_inventories_table',1);
INSERT INTO migrations VALUES(143,'2025_09_09_000102_update_stock_tables_for_comprehensive_management',1);
INSERT INTO migrations VALUES(144,'2025_09_09_000103_add_sku_column_to_inventories_table',1);
INSERT INTO migrations VALUES(145,'2025_09_09_061950_update_partners_schema',1);
INSERT INTO migrations VALUES(146,'2025_09_09_072000_update_partners_add_missing_columns',1);
INSERT INTO migrations VALUES(147,'2025_09_09_150500_optimize_reviews_keys',1);
INSERT INTO migrations VALUES(148,'2025_09_09_160500_add_indexes_to_media_table',1);
INSERT INTO migrations VALUES(149,'2025_09_10_000100_update_variant_combinations_add_hash_and_soft_deletes',1);
INSERT INTO migrations VALUES(150,'2025_09_10_000900_create_news_tables',1);
INSERT INTO migrations VALUES(151,'2025_09_10_001000_add_indexes_to_news_tables',1);
INSERT INTO migrations VALUES(152,'2025_09_10_100000_create_menus_table',1);
INSERT INTO migrations VALUES(153,'2025_09_10_100100_create_menu_items_table',1);
INSERT INTO migrations VALUES(154,'2025_09_12_061724_create_legals_table',1);
INSERT INTO migrations VALUES(155,'2025_09_12_070041_add_deleted_at_to_addresses_table',1);
INSERT INTO migrations VALUES(156,'2025_09_12_075952_add_warehouse_quantity_to_products_table_after_rename',1);
INSERT INTO migrations VALUES(157,'2025_09_12_173543_create_locations_table',1);
INSERT INTO migrations VALUES(158,'2025_09_12_200000_create_posts_table',1);
INSERT INTO migrations VALUES(159,'2025_09_12_200001_add_translations_to_posts_table',1);
INSERT INTO migrations VALUES(160,'2025_09_13_123340_add_enhanced_fields_to_addresses_table',1);
INSERT INTO migrations VALUES(161,'2025_09_13_181846_add_soft_deletes_to_referral_rewards_table',1);
INSERT INTO migrations VALUES(162,'2025_09_13_184525_add_missing_columns_to_attribute_values_table',1);
INSERT INTO migrations VALUES(163,'2025_09_13_185848_add_missing_columns_to_cart_items_table',1);
INSERT INTO migrations VALUES(164,'2025_09_13_190134_add_missing_columns_to_channels_table',1);
INSERT INTO migrations VALUES(165,'2025_09_13_190421_create_channel_product_table',1);
INSERT INTO migrations VALUES(166,'2025_09_13_191437_add_missing_columns_to_cities_table',1);
INSERT INTO migrations VALUES(167,'2025_09_13_191643_add_is_active_to_cities_table',1);
INSERT INTO migrations VALUES(168,'2025_09_13_191719_add_is_active_to_collections_table',1);
INSERT INTO migrations VALUES(169,'2025_09_13_191817_add_missing_columns_to_collections_table',1);
INSERT INTO migrations VALUES(170,'2025_09_13_192158_add_meta_fields_to_collection_translations_table',1);
INSERT INTO migrations VALUES(171,'2025_09_14_062324_add_user_id_to_notifications_table',1);
INSERT INTO migrations VALUES(172,'2025_09_14_075827_update_feature_flags_table_add_missing_columns',1);
INSERT INTO migrations VALUES(173,'2025_09_14_125040_create_subscribers_table',1);
INSERT INTO migrations VALUES(174,'2025_09_14_133055_create_admin_users_table',1);
INSERT INTO migrations VALUES(175,'2025_09_14_163054_create_email_campaigns_table',1);
INSERT INTO migrations VALUES(176,'2025_09_14_163205_add_user_id_to_subscribers_table',1);
INSERT INTO migrations VALUES(177,'2025_09_14_163833_create_companies_table',1);
INSERT INTO migrations VALUES(178,'2025_09_14_164105_add_deleted_at_to_subscribers_table',1);
INSERT INTO migrations VALUES(179,'2025_09_14_171732_add_is_active_to_attributes_table',1);
INSERT INTO migrations VALUES(180,'2025_09_14_191939_add_missing_columns_to_reviews_table',1);
INSERT INTO migrations VALUES(181,'2025_09_14_204041_remove_regions_from_cities_and_addresses_tables',1);
INSERT INTO migrations VALUES(182,'2025_09_18_000001_add_views_count_to_products_table',1);
INSERT INTO migrations VALUES(183,'2025_09_18_112935_fix_discount_codes_foreign_key_constraint',1);
INSERT INTO migrations VALUES(184,'2025_09_18_115829_add_is_featured_to_brands_table',1);
INSERT INTO migrations VALUES(185,'2025_09_18_115944_create_brands_table',1);
INSERT INTO migrations VALUES(186,'2025_09_18_152512_add_views_count_to_products_table',1);
INSERT INTO migrations VALUES(187,'2025_09_18_162441_add_is_visible_to_brands_table',1);
INSERT INTO migrations VALUES(188,'2025_09_19_035640_create_sliders_table',1);
INSERT INTO migrations VALUES(189,'2025_09_19_040159_create_slider_translations_table',1);
INSERT INTO migrations VALUES(190,'2025_09_19_140000_create_zones_table',1);
INSERT INTO migrations VALUES(191,'2025_09_19_191025_add_is_active_and_is_visible_to_brands_table',1);
INSERT INTO migrations VALUES(192,'2025_09_19_191822_create_discount_condition_products_table',1);
INSERT INTO migrations VALUES(193,'2025_09_19_191845_create_discount_condition_categories_table',1);
INSERT INTO migrations VALUES(194,'2025_09_19_192227_add_minimum_quantity_to_cart_items_table',1);
INSERT INTO migrations VALUES(195,'2025_09_19_192712_add_deleted_at_to_cart_items_table',1);
INSERT INTO migrations VALUES(196,'2025_09_19_193000_add_missing_location_columns',1);
INSERT INTO migrations VALUES(197,'2025_09_19_194023_add_notes_column_to_order_items_table',1);
INSERT INTO migrations VALUES(198,'2025_09_19_194100_recreate_locations_table',1);
INSERT INTO migrations VALUES(199,'2025_09_19_201726_create_enum_values_table',1);
INSERT INTO migrations VALUES(200,'2025_09_19_235216_add_description_to_menus_table',1);
INSERT INTO migrations VALUES(201,'2025_09_20_000001_create_zones_table',1);
INSERT INTO migrations VALUES(202,'2025_09_20_000001_update_feature_flag_attribution_columns',1);
INSERT INTO migrations VALUES(203,'2025_09_20_000200_add_status_and_priority_to_discount_conditions',1);
INSERT INTO migrations VALUES(204,'2025_09_20_020500_fix_news_table_names',1);
INSERT INTO migrations VALUES(205,'2025_09_21_000500_add_discount_amount_to_cart_items_table',1);
INSERT INTO migrations VALUES(206,'2025_09_21_000500_optimize_menu_indexes',1);
INSERT INTO migrations VALUES(207,'2025_09_22_140300_add_is_default_and_nullable_pivots',1);
INSERT INTO migrations VALUES(208,'2025_09_24_000001_create_system_setting_histories_table',1);
INSERT INTO migrations VALUES(209,'2025_09_24_131300_add_missing_columns_to_documents_table',1);
INSERT INTO migrations VALUES(210,'2025_09_24_150500_add_is_active_to_news_tables',1);
INSERT INTO migrations VALUES(211,'2025_09_24_150600_create_collection_rules_table',1);
INSERT INTO migrations VALUES(212,'2025_09_24_181700_add_min_max_length_to_attributes_table',1);
INSERT INTO migrations VALUES(213,'2025_09_24_192722_add_missing_columns_to_category_translations',1);
INSERT INTO migrations VALUES(214,'2025_09_25_000001_add_flags_to_subscribers_table',1);
INSERT INTO migrations VALUES(215,'2025_09_25_180000_update_seo_data_add_columns',1);
INSERT INTO migrations VALUES(216,'2025_09_25_200500_add_missing_columns_to_system_settings_table',1);
INSERT INTO migrations VALUES(217,'2025_09_26_000001_enforce_order_shipping_constraints',1);
INSERT INTO migrations VALUES(218,'2025_09_26_090000_add_variant_attribute_matrix_to_product_variants_table',1);
INSERT INTO migrations VALUES(219,'2025_09_30_000001_add_valueable_columns_to_attribute_values_table',1);
INSERT INTO migrations VALUES(220,'2025_09_30_120000_add_is_featured_to_price_list_items_table',1);
INSERT INTO migrations VALUES(221,'2025_09_30_121500_enforce_order_item_constraints',1);
INSERT INTO migrations VALUES(222,'2025_10_01_000000_add_moderation_to_marketing_content',1);
INSERT INTO migrations VALUES(223,'2025_10_05_000001_add_missing_columns_to_variant_images_table',1);
INSERT INTO migrations VALUES(224,'2025_10_10_000000_add_correlation_id_to_stock_movements_table',1);
INSERT INTO migrations VALUES(225,'2025_10_10_000200_optimize_menu_indexes',1);
INSERT INTO migrations VALUES(226,'2025_10_15_000000_add_additional_columns_to_variant_images_table',1);
INSERT INTO migrations VALUES(227,'2025_10_15_000500_add_soft_deletes_to_news_table',1);
INSERT INTO migrations VALUES(228,'2025_10_20_073213_create_exports_table',1);
INSERT INTO migrations VALUES(229,'2025_10_20_073425_create_contact_messages_table',1);
INSERT INTO migrations VALUES(230,'2025_10_20_091207_convert_feature_flag_attribution_columns',1);
INSERT INTO migrations VALUES(231,'2025_10_23_000001_add_customer_group_to_coupons_table',1);
INSERT INTO migrations VALUES(232,'2025_10_23_164819_update_variant_price_history_schema',1);
INSERT INTO migrations VALUES(233,'2025_10_23_185200_add_is_active_to_users_table',1);
INSERT INTO migrations VALUES(234,'2025_10_24_000001_create_discount_customer_groups_table',1);
INSERT INTO migrations VALUES(235,'2025_10_25_000000_rebuild_discount_schema',1);
INSERT INTO migrations VALUES(236,'2025_10_25_010000_add_metadata_to_user_product_interactions_table',1);
INSERT INTO migrations VALUES(237,'2025_10_26_000001_add_admin_fields_to_email_campaigns',1);
INSERT INTO migrations VALUES(238,'2025_10_26_120000_add_name_attribution_columns_to_discount_tables',1);
INSERT INTO migrations VALUES(239,'2025_10_30_000000_add_is_breaking_to_news_table',1);
INSERT INTO migrations VALUES(240,'2025_10_30_000001_create_region_translations_table',1);
INSERT INTO migrations VALUES(241,'2025_10_30_010000_backfill_country_city_from_zones',1);
INSERT INTO migrations VALUES(242,'2025_10_30_080000_create_email_campaign_recipients_table',1);
INSERT INTO migrations VALUES(243,'2025_10_30_120000_fix_news_category_columns',1);
INSERT INTO migrations VALUES(244,'2025_10_31_000000_create_table_settings_table',1);
INSERT INTO migrations VALUES(245,'2025_11_03_000002_add_attribution_names_to_system_settings_table',1);
INSERT INTO migrations VALUES(246,'2025_11_05_120000_expand_order_status_options',1);
INSERT INTO migrations VALUES(247,'2025_11_06_120000_expand_product_status_column',2);
INSERT INTO migrations VALUES(248,'2025_11_06_130000_create_product_variant_translations_table',2);
INSERT INTO migrations VALUES(249,'2025_11_07_000001_add_schedule_columns_to_price_lists_table',2);
INSERT INTO migrations VALUES(250,'2025_11_10_000001_add_description_and_meta_data_to_attribute_value_translations_table',2);
INSERT INTO migrations VALUES(251,'2025_11_10_000200_enforce_order_schema_constraints',2);
INSERT INTO migrations VALUES(252,'2025_11_12_000001_add_attribution_names_to_documents_table',2);
INSERT INTO migrations VALUES(253,'2025_11_15_000000_update_user_product_interactions_table',2);
INSERT INTO migrations VALUES(254,'2025_11_20_000500_add_meta_columns_to_categories_table',2);
INSERT INTO migrations VALUES(255,'2025_11_30_000600_add_meta_columns_to_brands_table',2);
INSERT INTO migrations VALUES(256,'2025_11_30_000700_add_contact_details_to_brands_table',2);
INSERT INTO migrations VALUES(257,'2025_12_01_000000_create_payment_webhook_events_table',2);
INSERT INTO migrations VALUES(258,'2025_12_15_000100_ensure_legacy_attribute_tables_exist',2);
INSERT INTO migrations VALUES(259,'2025_12_31_000001_add_country_slug_unique_index_to_cities_table',2);
INSERT INTO migrations VALUES(260,'2026_01_07_000001_add_campaign_deprecation_indexes',2);
INSERT INTO migrations VALUES(261,'2026_01_07_000002_create_campaign_data_archive',2);
INSERT INTO migrations VALUES(262,'2026_01_07_154930_cleanup_news_tag_and_comment_tables',2);
INSERT INTO migrations VALUES(263,'2026_01_07_160000_drop_campaign_conversion_tables',2);
INSERT INTO migrations VALUES(264,'2026_01_22_000001_drop_unused_model_tables',2);
INSERT INTO migrations VALUES(265,'2026_01_22_000002_cleanup_unused_models',2);
INSERT INTO migrations VALUES(266,'2026_01_26_111205_create_orders_table',2);
INSERT INTO migrations VALUES(267,'2026_01_26_111212_create_order_items_table',2);
INSERT INTO migrations VALUES(268,'2026_01_26_154324_add_cost_to_order_shippings_table',2);
INSERT INTO migrations VALUES(269,'2026_01_26_155700_add_locale_to_orders_table',2);
INSERT INTO migrations VALUES(270,'2026_01_26_173510_add_missing_columns_to_partner_tiers_table',2);
INSERT INTO migrations VALUES(271,'2026_01_26_174947_create_news_images_table',2);
INSERT INTO migrations VALUES(272,'2026_01_26_181253_create_product_features_table',2);
INSERT INTO migrations VALUES(273,'2026_01_26_182258_create_shipping_options_table',2);
INSERT INTO migrations VALUES(274,'2026_01_27_000001_drop_system_logs_table',2);
INSERT INTO migrations VALUES(275,'2026_01_27_145317_drop_product_comparisons_table',2);
INSERT INTO migrations VALUES(276,'2026_01_29_145709_add_missing_fields_to_sliders_table',2);
INSERT INTO migrations VALUES(277,'2026_01_29_231210_create_permission_tables',2);
INSERT INTO migrations VALUES(278,'2026_02_02_103728_create_imports_table',2);
INSERT INTO migrations VALUES(279,'2026_02_02_103730_create_failed_import_rows_table',2);
INSERT INTO migrations VALUES(280,'2026_02_04_095610_add_failed_job_columns_to_job_batches_table',2);
INSERT INTO migrations VALUES(281,'2026_02_04_122004_add_is_default_to_product_images_table',2);
INSERT INTO migrations VALUES(282,'2026_02_04_132034_remove_compare_price_from_products_and_variants_tables',2);
INSERT INTO migrations VALUES(283,'2026_02_04_133457_drop_metadata_from_products_and_variants',2);
INSERT INTO migrations VALUES(284,'2026_02_04_135219_add_detailed_description_to_products_and_translations_tables',2);
INSERT INTO migrations VALUES(285,'2026_02_04_142742_create_product_similarities_table',2);
INSERT INTO migrations VALUES(286,'2026_02_04_143753_drop_fields_from_product_similarities',2);
INSERT INTO migrations VALUES(287,'2026_02_04_145330_create_services_table',2);
INSERT INTO migrations VALUES(288,'2026_02_04_145337_create_order_service_table',2);
INSERT INTO migrations VALUES(289,'2026_02_04_163337_drop_unique_sku_indexes',2);
INSERT INTO migrations VALUES(290,'2026_02_04_163811_create_import_row_results_table',2);
INSERT INTO migrations VALUES(291,'2026_02_04_165554_add_import_payload_to_imports_table',2);
INSERT INTO migrations VALUES(292,'2026_02_05_000001_drop_ui_translations_table',2);
