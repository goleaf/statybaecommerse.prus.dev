<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the initial customer_groups table with core fields so later enhancements can build upon it.
     */
    public function up(): void
    {
        if (Schema::hasTable('customer_groups')) {
            // Bail out early when the table already exists to keep the migration idempotent.
            return;
        }

        Schema::create('customer_groups', function (Blueprint $table): void {
            $table->id(); // Primary identifier for the customer group.
            $table->string('name'); // Human readable label displayed across the storefront and admin.
            $table->string('slug')->unique(); // URL friendly identifier for routing and lookups.
            $table->string('code')->unique()->nullable(); // Optional external identifier for ERP or CRM integrations.
            $table->text('description')->nullable(); // Additional information shown in management interfaces.
            $table->decimal('discount_percentage', 5, 2)->default(0); // Baseline percentage discount applied to the group.
            $table->boolean('is_active')->default(true); // Quick toggle to activate or deactivate the group.
            $table->timestamps(); // Track when records are created or updated for auditing.
        });
    }

    /**
     * Roll back the migration by removing the table when it was created here.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            // Nothing to drop when the table is already missing.
            return;
        }

        Schema::dropIfExists('customer_groups');
    }
};
