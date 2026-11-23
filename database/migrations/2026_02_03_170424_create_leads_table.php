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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Page 1 - Customer Details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mobile_no');
            $table->string('email')->nullable();
            
            // Page 2 - Additional Details
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('age_group', ['under_30', '30_to_40', 'above_40'])->nullable();
            $table->enum('customer_type', ['individual', 'corporate'])->nullable();
            $table->string('occupation_type')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('country')->nullable()->default('India');
            
            // Page 3 - Enquiry Details
            $table->foreignId('enquiry_type_id')->nullable()->constrained('enquiry_types')->onDelete('set null');
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->onDelete('set null');
            $table->foreignId('lead_type_id')->nullable()->constrained('lead_types')->onDelete('set null');
            $table->foreignId('lead_status_id')->nullable()->constrained('lead_statuses')->onDelete('set null');
            $table->foreignId('segment_id')->nullable()->constrained('segments')->onDelete('set null');
            $table->foreignId('sub_segment_id')->nullable()->constrained('sub_segments')->onDelete('set null');
            $table->foreignId('vehicle_model_id')->nullable()->constrained('vehicle_models')->onDelete('set null');
            $table->integer('quantity')->default(1);
            // Multi-select: variants, fuel_types, transmissions, colours (via pivot tables)
            
            // Page 4 - Purchase Details
            $table->enum('purchase_type', ['first_time_buy', 'exchange_buy', 'additional_buy', 'scrappage'])->nullable();
            // Exchange vehicle details (only if purchase_type = exchange_buy)
            $table->string('exchange_vehicle_model')->nullable();
            $table->string('exchange_fuel_type')->nullable();
            $table->string('exchange_transmission')->nullable();
            $table->year('exchange_manufacture_year')->nullable();
            $table->enum('likely_purchase', ['under_15_days', '15_to_45_days', 'above_45_days'])->nullable();
            $table->date('next_follow_up')->nullable();
            
            // Page 5 - Assignment
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('dealer_locations')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('additional_comments')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
