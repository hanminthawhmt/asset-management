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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                ->constrained('asset')
                ->onDelete('cascade');

            $table->foreignId('approver_id')
                ->constrained('approver')
                ->onDelete('cascade');

            $table->string('borrower_name');
            $table->date('recorded_date');
            $table->date('borrow_date');
            $table->date('return_date');

            $table->string('property_front_image')->nullable();
            $table->string('property_back_image')->nullable();
            $table->string('property_left_image')->nullable();
            $table->string('property_right_image')->nullable();
            $table->string('property_overall_image')->nullable();

            $table->string('acc_front_image')->nullable();
            $table->string('acc_back_image')->nullable();
            $table->string('acc_left_image')->nullable();
            $table->string('acc_right_image')->nullable();
            $table->string('acc_overall_image')->nullable();

            $table->enum('approval_status', [
                'AWAITING',
                'APPROVED',
                'REJECTED',
            ])->default('AWAITING');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
