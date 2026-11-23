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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state')->nullable();
            $table->string('region')->nullable();
            $table->enum('type', ['customer', 'dealer'])->default('customer');
            $table->timestamps();
        });

        // Seed initial data
        DB::table('locations')->insert([
            // Major Cities - Customer Locations
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'region' => 'West', 'type' => 'customer'],
            ['name' => 'Delhi', 'state' => 'Delhi', 'region' => 'North', 'type' => 'customer'],
            ['name' => 'Bangalore', 'state' => 'Karnataka', 'region' => 'South', 'type' => 'customer'],
            ['name' => 'Hyderabad', 'state' => 'Telangana', 'region' => 'South', 'type' => 'customer'],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'region' => 'South', 'type' => 'customer'],
            ['name' => 'Kolkata', 'state' => 'West Bengal', 'region' => 'East', 'type' => 'customer'],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'region' => 'West', 'type' => 'customer'],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'region' => 'West', 'type' => 'customer'],
            ['name' => 'Surat', 'state' => 'Gujarat', 'region' => 'West', 'type' => 'customer'],
            ['name' => 'Jaipur', 'state' => 'Rajasthan', 'region' => 'North', 'type' => 'customer'],
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh', 'region' => 'North', 'type' => 'customer'],
            ['name' => 'Kanpur', 'state' => 'Uttar Pradesh', 'region' => 'North', 'type' => 'customer'],
            ['name' => 'Nagpur', 'state' => 'Maharashtra', 'region' => 'Central', 'type' => 'customer'],
            ['name' => 'Indore', 'state' => 'Madhya Pradesh', 'region' => 'Central', 'type' => 'customer'],
            ['name' => 'Bhopal', 'state' => 'Madhya Pradesh', 'region' => 'Central', 'type' => 'customer'],
            ['name' => 'Visakhapatnam', 'state' => 'Andhra Pradesh', 'region' => 'South', 'type' => 'customer'],
            ['name' => 'Patna', 'state' => 'Bihar', 'region' => 'East', 'type' => 'customer'],
            ['name' => 'Vadodara', 'state' => 'Gujarat', 'region' => 'West', 'type' => 'customer'],
            ['name' => 'Ghaziabad', 'state' => 'Uttar Pradesh', 'region' => 'North', 'type' => 'customer'],
            ['name' => 'Ludhiana', 'state' => 'Punjab', 'region' => 'North', 'type' => 'customer'],
            
            // Dealer Locations
            ['name' => 'Andheri Showroom', 'state' => 'Maharashtra', 'region' => 'West', 'type' => 'dealer'],
            ['name' => 'Bandra Showroom', 'state' => 'Maharashtra', 'region' => 'West', 'type' => 'dealer'],
            ['name' => 'Thane Showroom', 'state' => 'Maharashtra', 'region' => 'West', 'type' => 'dealer'],
            ['name' => 'Connaught Place Showroom', 'state' => 'Delhi', 'region' => 'North', 'type' => 'dealer'],
            ['name' => 'Dwarka Showroom', 'state' => 'Delhi', 'region' => 'North', 'type' => 'dealer'],
            ['name' => 'Whitefield Showroom', 'state' => 'Karnataka', 'region' => 'South', 'type' => 'dealer'],
            ['name' => 'Koramangala Showroom', 'state' => 'Karnataka', 'region' => 'South', 'type' => 'dealer'],
            ['name' => 'Hitech City Showroom', 'state' => 'Telangana', 'region' => 'South', 'type' => 'dealer'],
            ['name' => 'Banjara Hills Showroom', 'state' => 'Telangana', 'region' => 'South', 'type' => 'dealer'],
            ['name' => 'Anna Nagar Showroom', 'state' => 'Tamil Nadu', 'region' => 'South', 'type' => 'dealer'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
