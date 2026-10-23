<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_orders', function (Blueprint $table) {
            $table->string('fraksi')->nullable()->default('-')->change();
        });
    }

    public function down(): void
    {
        Schema::table('media_orders', function (Blueprint $table) {
            $table->string('fraksi')->nullable(false)->change();
        });
    }
};
