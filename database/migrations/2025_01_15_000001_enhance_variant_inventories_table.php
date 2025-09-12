<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_inventories', function (Blueprint $table) {
            // Add new columns for enhanced inventory management
            $table->text('notes')->nullable()->after('is_tracked');
            $table->timestamp('last_restocked_at')->nullable()->after('notes');
            $table->timestamp('last_sold_at')->nullable()->after('last_restocked_at');
            $table->decimal('cost_per_unit', 10, 2)->nullable()->after('last_sold_at');
            $table->integer('reorder_point')->default(0)->after('cost_per_unit');
            $table->integer('max_stock_level')->nullable()->after('reorder_point');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('max_stock_level');
            $table->string('batch_number')->nullable()->after('supplier_id');
            $table->date('expiry_date')->nullable()->after('batch_number');
            $table->string('status')->default('active')->after('expiry_date');
            
            // Add soft deletes
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['status', 'is_tracked']);
            $table->index(['supplier_id']);
            $table->index(['expiry_date']);
            $table->index(['last_restocked_at']);
            $table->index(['reorder_point']);
            
            // Add foreign key constraint for supplier
            $table->foreign('supplier_id')->references('id')->on('partners')->onDelete('set null');
        });

        // Create stock_movements table
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_inventory_id');
                $table->integer('quantity');
                $table->enum('type', ['in', 'out']);
                $table->string('reason');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('moved_at')->useCurrent();
                $table->timestamps();

                $table->foreign('variant_inventory_id')->references('id')->on('variant_inventories')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                
                $table->index(['variant_inventory_id', 'moved_at']);
                $table->index(['type', 'reason']);
                $table->index(['moved_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        
        Schema::table('variant_inventories', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropIndex(['status', 'is_tracked']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['expiry_date']);
            $table->dropIndex(['last_restocked_at']);
            $table->dropIndex(['reorder_point']);
            
            $table->dropColumn([
                'notes',
                'last_restocked_at',
                'last_sold_at',
                'cost_per_unit',
                'reorder_point',
                'max_stock_level',
                'supplier_id',
                'batch_number',
                'expiry_date',
                'status',
                'deleted_at'
            ]);
        });
    }
};

