<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('subscription_start')->nullable();
            $table->timestamp('subscription_end')->nullable();
            $table->string('path');
            $table->integer('users_amount')->default(0);
            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant'); 
    }
};