<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');

            $table->foreignId('asset_type_id')
                ->constrained('asset_types')
                ->onDelete('cascade');

            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();

            $table->enum('status', [
                'IN_USE',
                'AVAILABLE',
                'DAMAGED',
                'REPAIRING',
                'PENDING_DISPOSAL',
                'DISPOSED',
                'LOST',
                'BORROWED',
                'PENDING_INSPECTION',
                'DISABLED',
            ])->default('AVAILABLE');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset');
    }
};
