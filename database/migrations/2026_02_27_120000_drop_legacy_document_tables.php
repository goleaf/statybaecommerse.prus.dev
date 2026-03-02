<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
    }

    public function down(): void
    {
        // Legacy document tables intentionally not restored.
    }
};
