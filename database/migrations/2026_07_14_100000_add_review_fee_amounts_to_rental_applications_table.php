<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_applications', function (Blueprint $table): void {
            $table->decimal('agent_fee_amount', 12, 2)->default(0)->after('review_notes');
            $table->decimal('legal_fee_amount', 12, 2)->default(0)->after('agent_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('rental_applications', function (Blueprint $table): void {
            $table->dropColumn(['agent_fee_amount', 'legal_fee_amount']);
        });
    }
};
