<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_reps', function (Blueprint $table) {
            $table->id();
            $table->string('rep_id')->unique();
            $table->string('rep_name');
            $table->string('contact_no')->nullable();
            $table->date('join_date')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('route_id')->unique()->constrained('routes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_reps');
    }
};
