<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_unit_id')->constrained('property_units')->cascadeOnDelete();
            $table->foreignId('rental_application_id')->nullable()->constrained('rental_applications')->nullOnDelete();
            $table->foreignId('tenant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending_activation')->index();
            $table->string('tenant_name');
            $table->string('tenant_email')->nullable()->index();
            $table->string('tenant_phone', 40)->nullable();
            $table->date('lease_start_date')->index();
            $table->date('lease_end_date')->nullable()->index();
            $table->date('move_in_date')->nullable();
            $table->timestamp('activated_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->decimal('rent_amount', 12, 2)->default(0);
            $table->decimal('service_charge_amount', 12, 2)->default(0);
            $table->string('billing_cycle')->default('yearly');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('rental_application_id', 'tenancy_application_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
