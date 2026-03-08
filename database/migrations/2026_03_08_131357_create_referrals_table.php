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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->text('referral_reason');
            $table->integer('priority')->default(1);
            $table->foreignId('referring_party_id')->constrained()->cascadeOnDelete();
            $table->integer('status')->default(1);
            $table->text('optional_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->index(['patient_id', 'referring_party_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
