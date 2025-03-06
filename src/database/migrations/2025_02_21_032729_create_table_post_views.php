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
    Schema::create('post_views', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained();
        $table->foreignId('user_id')->constrained()->nullable();
        $table->string('ip_address')->nullable();
        $table->timestamp('viewed_at')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
