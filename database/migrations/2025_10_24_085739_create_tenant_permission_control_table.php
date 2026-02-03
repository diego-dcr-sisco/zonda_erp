<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantPermissionControlTable extends Migration
{
    public function up()
    {
        Schema::create('tenant_permission_control', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('permission_id');
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'permission_id']);
            $table->foreign('tenant_id')->references('id')->on('tenant')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            
            $table->index(['tenant_id', 'is_allowed']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_permission_control');
    }
}