<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->unsignedBigInteger('tier_id')->nullable()->after('code');
            $table->enum('type', ['supplier', 'manufacturer', 'distributor', 'other'])->default('supplier')->after('tier_id');
            $table->softDeletes();
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->foreign('tier_id')->references('id')->on('partner_tiers')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['tier_id']);
            $table->dropColumn(['tier_id', 'type', 'deleted_at']);
        });
    }
};
