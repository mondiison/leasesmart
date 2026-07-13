<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('landlord_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('caretaker_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('property_code')->nullable()->unique();
            $table->string('property_type');
            $table->text('description')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('publish_status')->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('property_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('unit_code')->nullable()->unique();
            $table->string('unit_name');
            $table->string('unit_type')->nullable();
            $table->string('floor_label')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('toilets')->nullable();
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->string('occupancy_status')->default('vacant')->index();
            $table->decimal('rent_amount', 12, 2);
            $table->string('billing_cycle')->default('yearly');
            $table->decimal('service_charge_amount', 12, 2)->default(0);
            $table->decimal('caution_fee_amount', 12, 2)->default(0);
            $table->decimal('inspection_fee_amount', 12, 2)->default(0);
            $table->date('available_from')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_listed')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('property_amenities', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('amenity_property', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_amenity_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['property_id', 'property_amenity_id'], 'amenity_property_unique');
        });

        Schema::create('amenity_property_unit', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_amenity_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['property_unit_id', 'property_amenity_id'], 'amenity_prop_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_property_unit');
        Schema::dropIfExists('amenity_property');
        Schema::dropIfExists('property_amenities');
        Schema::dropIfExists('property_units');
        Schema::dropIfExists('properties');
    }
};
