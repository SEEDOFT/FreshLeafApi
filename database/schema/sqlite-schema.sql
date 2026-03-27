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
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
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
CREATE TABLE IF NOT EXISTS "addresses"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "label" varchar not null,
  "recipient_name" varchar not null,
  "phone" varchar not null,
  "address_line_1" varchar not null,
  "address_line_2" varchar,
  "city" varchar not null,
  "province" varchar not null,
  "postal_code" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "user_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "user_statuses_code_unique" on "user_statuses"("code");
CREATE TABLE IF NOT EXISTS "user_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "user_types_code_unique" on "user_types"("code");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "product_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "product_statuses_code_unique" on "product_statuses"(
  "code"
);
CREATE TABLE IF NOT EXISTS "product_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "product_types_code_unique" on "product_types"("code");
CREATE TABLE IF NOT EXISTS "units"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "symbol" varchar not null,
  "conversion_to_base" numeric not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "product_substitutions"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "substitute_product_id" integer not null,
  "priority" integer not null default '0',
  "reason" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("substitute_product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "product_variants"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "unit_id" integer not null,
  "name" varchar not null,
  "quantity_in_unit" numeric not null,
  "price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("unit_id") references "units"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "product_type_id" integer not null,
  "default_unit_id" integer not null,
  "product_status_id" integer not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "nutrition_data" text,
  "shelf_life_days" integer,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("category_id") references "categories"("id") on delete restrict,
  foreign key("product_type_id") references "product_types"("id") on delete restrict,
  foreign key("default_unit_id") references "units"("id") on delete restrict,
  foreign key("product_status_id") references "product_statuses"("id") on delete restrict
);
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
CREATE TABLE IF NOT EXISTS "suppliers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "contact_name" varchar,
  "phone" varchar,
  "email" varchar,
  "address" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "purchase_order_items"(
  "id" integer primary key autoincrement not null,
  "purchase_order_id" integer not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "qty_ordered" numeric not null,
  "qty_received" numeric not null default '0',
  "cost_per_unit" numeric not null,
  "expiry_date" date,
  "batch_code" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("purchase_order_id") references "purchase_orders"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete restrict,
  foreign key("product_variant_id") references "product_variants"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "purchase_order_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "purchase_order_statuses_code_unique" on "purchase_order_statuses"(
  "code"
);
CREATE TABLE IF NOT EXISTS "purchase_orders"(
  "id" integer primary key autoincrement not null,
  "supplier_id" integer not null,
  "purchase_order_status_id" integer not null,
  "po_number" varchar not null,
  "ordered_at" datetime not null,
  "received_at" datetime,
  "total_cost" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("supplier_id") references "suppliers"("id") on delete restrict,
  foreign key("purchase_order_status_id") references "purchase_order_statuses"("id") on delete restrict
);
CREATE UNIQUE INDEX "purchase_orders_po_number_unique" on "purchase_orders"(
  "po_number"
);
CREATE TABLE IF NOT EXISTS "inventory_batches"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "supplier_id" integer not null,
  "inventory_batch_status_id" integer not null,
  "batch_code" varchar not null,
  "received_qty" numeric not null,
  "reserved_qty" numeric not null default '0',
  "sold_qty" numeric not null default '0',
  "damaged_qty" numeric not null default '0',
  "expired_qty" numeric not null default '0',
  "cost_per_unit" numeric not null,
  "expiry_date" date,
  "received_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete restrict,
  foreign key("product_variant_id") references "product_variants"("id") on delete restrict,
  foreign key("supplier_id") references "suppliers"("id") on delete restrict,
  foreign key("inventory_batch_status_id") references "inventory_batch_statuses"("id") on delete restrict
);
CREATE UNIQUE INDEX "inventory_batches_batch_code_unique" on "inventory_batches"(
  "batch_code"
);
CREATE TABLE IF NOT EXISTS "inventory_batch_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "inventory_batch_statuses_code_unique" on "inventory_batch_statuses"(
  "code"
);
CREATE TABLE IF NOT EXISTS "inventory_movement_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "inventory_movement_types_code_unique" on "inventory_movement_types"(
  "code"
);
CREATE TABLE IF NOT EXISTS "inventory_movements"(
  "id" integer primary key autoincrement not null,
  "inventory_batch_id" integer not null,
  "inventory_movement_type_id" integer not null,
  "quantity" numeric not null,
  "reference_type" varchar,
  "reference_id" integer,
  "note" text,
  "created_by" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("inventory_batch_id") references "inventory_batches"("id") on delete cascade,
  foreign key("inventory_movement_type_id") references "inventory_movement_types"("id") on delete restrict,
  foreign key("created_by") references "users"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "price_histories"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "old_price" numeric not null,
  "new_price" numeric not null,
  "changed_by" integer not null,
  "changed_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("product_variant_id") references "product_variants"("id") on delete cascade,
  foreign key("changed_by") references "users"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "cart_items"(
  "id" integer primary key autoincrement not null,
  "cart_id" integer not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "quantity" numeric not null,
  "unit_price" numeric not null,
  "subtotal" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("cart_id") references "carts"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete restrict,
  foreign key("product_variant_id") references "product_variants"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "cart_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "cart_statuses_code_unique" on "cart_statuses"("code");
CREATE TABLE IF NOT EXISTS "carts"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "cart_status_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("cart_status_id") references "cart_statuses"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "order_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "sort_order" integer not null default '0',
  "color" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "order_statuses_code_unique" on "order_statuses"("code");
CREATE TABLE IF NOT EXISTS "payment_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "payment_statuses_code_unique" on "payment_statuses"(
  "code"
);
CREATE TABLE IF NOT EXISTS "order_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "order_types_code_unique" on "order_types"("code");
CREATE TABLE IF NOT EXISTS "payment_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "payment_types_code_unique" on "payment_types"("code");
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "product_name_snapshot" varchar not null,
  "unit_snapshot" varchar not null,
  "unit_price_snapshot" numeric not null,
  "quantity" numeric not null,
  "subtotal" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete restrict,
  foreign key("product_variant_id") references "product_variants"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "address_id" integer not null,
  "order_type_id" integer not null,
  "order_status_id" integer not null,
  "payment_status_id" integer not null,
  "order_number" varchar not null,
  "delivery_date" date not null,
  "delivery_slot" varchar not null,
  "subtotal" numeric not null,
  "discount_amount" numeric not null default '0',
  "delivery_fee" numeric not null default '0',
  "tax_amount" numeric not null default '0',
  "total_amount" numeric not null,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete restrict,
  foreign key("address_id") references "addresses"("id") on delete restrict,
  foreign key("order_type_id") references "order_types"("id") on delete restrict,
  foreign key("order_status_id") references "order_statuses"("id") on delete restrict,
  foreign key("payment_status_id") references "payment_statuses"("id") on delete restrict
);
CREATE UNIQUE INDEX "orders_order_number_unique" on "orders"("order_number");
CREATE TABLE IF NOT EXISTS "payments"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "payment_type_id" integer not null,
  "payment_status_id" integer not null,
  "amount" numeric not null,
  "transaction_reference" varchar,
  "paid_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("payment_type_id") references "payment_types"("id") on delete restrict,
  foreign key("payment_status_id") references "payment_statuses"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "order_status_histories"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "from_order_status_id" integer not null,
  "to_order_status_id" integer not null,
  "changed_by" integer not null,
  "note" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("from_order_status_id") references "order_statuses"("id") on delete restrict,
  foreign key("to_order_status_id") references "order_statuses"("id") on delete restrict,
  foreign key("changed_by") references "users"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "ai_recommendation_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "ai_recommendation_types_code_unique" on "ai_recommendation_types"(
  "code"
);
CREATE TABLE IF NOT EXISTS "ai_recommendation_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "ai_recommendation_statuses_code_unique" on "ai_recommendation_statuses"(
  "code"
);
CREATE TABLE IF NOT EXISTS "behavior_event_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "behavior_event_types_code_unique" on "behavior_event_types"(
  "code"
);
CREATE TABLE IF NOT EXISTS "user_behavior_events"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "behavior_event_type_id" integer not null,
  "product_id" integer,
  "product_variant_id" integer,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("behavior_event_type_id") references "behavior_event_types"("id") on delete restrict,
  foreign key("product_id") references "products"("id") on delete set null,
  foreign key("product_variant_id") references "product_variants"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "ai_recommendation_items"(
  "id" integer primary key autoincrement not null,
  "ai_recommendation_id" integer not null,
  "product_id" integer not null,
  "product_variant_id" integer not null,
  "suggested_qty" numeric not null,
  "reason" text,
  "estimated_price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ai_recommendation_id") references "ai_recommendations"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete restrict,
  foreign key("product_variant_id") references "product_variants"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "ai_recommendations"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "ai_recommendation_type_id" integer not null,
  "ai_recommendation_status_id" integer not null,
  "title" varchar not null,
  "payload" text,
  "score" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("ai_recommendation_type_id") references "ai_recommendation_types"("id") on delete restrict,
  foreign key("ai_recommendation_status_id") references "ai_recommendation_statuses"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "notification_types"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "notification_types_code_unique" on "notification_types"(
  "code"
);
CREATE TABLE IF NOT EXISTS "notifications"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "notification_type_id" integer not null,
  "notification_status_id" integer not null,
  "title" varchar not null,
  "message" text not null,
  "data" text,
  "read_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("notification_type_id") references "notification_types"("id") on delete restrict,
  foreign key("notification_status_id") references "notification_statuses"("id") on delete restrict
);
CREATE TABLE IF NOT EXISTS "notification_statuses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "notification_statuses_code_unique" on "notification_statuses"(
  "code"
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
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "first_name" varchar not null,
  "email" varchar,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "phone_number" varchar,
  "user_type_id" integer,
  "status_id" integer,
  "deleted_at" datetime,
  "last_name" varchar not null,
  foreign key("status_id") references user_statuses("id") on delete restrict on update no action,
  foreign key("user_type_id") references user_types("id") on delete restrict on update no action
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_03_26_071854_create_addresses_table',2);
INSERT INTO migrations VALUES(5,'2026_03_26_071854_create_user_statuses_table',2);
INSERT INTO migrations VALUES(6,'2026_03_26_071854_create_user_types_table',2);
INSERT INTO migrations VALUES(7,'2026_03_26_071902_update_users_table_add_phone_and_types',2);
INSERT INTO migrations VALUES(8,'2026_03_26_072548_create_categories_table',2);
INSERT INTO migrations VALUES(9,'2026_03_26_072548_create_product_statuses_table',2);
INSERT INTO migrations VALUES(10,'2026_03_26_072548_create_product_types_table',2);
INSERT INTO migrations VALUES(11,'2026_03_26_072549_create_units_table',2);
INSERT INTO migrations VALUES(12,'2026_03_26_072630_create_product_substitutions_table',2);
INSERT INTO migrations VALUES(13,'2026_03_26_072630_create_product_variants_table',2);
INSERT INTO migrations VALUES(14,'2026_03_26_072630_create_products_table',2);
INSERT INTO migrations VALUES(15,'2026_03_26_073220_create_suppliers_table',2);
INSERT INTO migrations VALUES(16,'2026_03_26_073221_create_purchase_order_items_table',2);
INSERT INTO migrations VALUES(17,'2026_03_26_073221_create_purchase_order_statuses_table',2);
INSERT INTO migrations VALUES(18,'2026_03_26_073221_create_purchase_orders_table',2);
INSERT INTO migrations VALUES(19,'2026_03_26_073921_create_inventory_batches_table',2);
INSERT INTO migrations VALUES(20,'2026_03_26_073922_create_inventory_batch_statuses_table',2);
INSERT INTO migrations VALUES(21,'2026_03_26_073922_create_inventory_movement_types_table',2);
INSERT INTO migrations VALUES(22,'2026_03_26_073922_create_inventory_movements_table',2);
INSERT INTO migrations VALUES(23,'2026_03_26_073922_create_price_histories_table',2);
INSERT INTO migrations VALUES(24,'2026_03_26_074203_create_cart_items_table',2);
INSERT INTO migrations VALUES(25,'2026_03_26_074203_create_cart_statuses_table',2);
INSERT INTO migrations VALUES(26,'2026_03_26_074203_create_carts_table',2);
INSERT INTO migrations VALUES(27,'2026_03_26_074930_create_order_statuses_table',2);
INSERT INTO migrations VALUES(28,'2026_03_26_074930_create_payment_statuses_table',2);
INSERT INTO migrations VALUES(29,'2026_03_26_074931_create_order_types_table',2);
INSERT INTO migrations VALUES(30,'2026_03_26_074931_create_payment_types_table',2);
INSERT INTO migrations VALUES(31,'2026_03_26_075024_create_order_items_table',2);
INSERT INTO migrations VALUES(32,'2026_03_26_075024_create_orders_table',2);
INSERT INTO migrations VALUES(33,'2026_03_26_075024_create_payments_table',2);
INSERT INTO migrations VALUES(34,'2026_03_26_075025_create_order_status_histories_table',2);
INSERT INTO migrations VALUES(35,'2026_03_26_075543_create_ai_recommendation_types_table',2);
INSERT INTO migrations VALUES(36,'2026_03_26_075544_create_ai_recommendation_statuses_table',2);
INSERT INTO migrations VALUES(37,'2026_03_26_075544_create_behavior_event_types_table',2);
INSERT INTO migrations VALUES(38,'2026_03_26_075544_create_user_behavior_events_table',2);
INSERT INTO migrations VALUES(39,'2026_03_26_075545_create_ai_recommendation_items_table',2);
INSERT INTO migrations VALUES(40,'2026_03_26_075545_create_ai_recommendations_table',2);
INSERT INTO migrations VALUES(41,'2026_03_26_080019_create_notification_types_table',2);
INSERT INTO migrations VALUES(42,'2026_03_26_080019_create_notifications_table',2);
INSERT INTO migrations VALUES(43,'2026_03_26_080020_create_notification_statuses_table',2);
INSERT INTO migrations VALUES(44,'2026_03_26_081409_create_personal_access_tokens_table',2);
INSERT INTO migrations VALUES(45,'2026_03_26_081500_update_users_table_split_name',3);
INSERT INTO migrations VALUES(46,'2026_03_26_082934_update_users_table_make_email_nullable',4);
