<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frontend_logs', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_code')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend_logs');
    }
};
