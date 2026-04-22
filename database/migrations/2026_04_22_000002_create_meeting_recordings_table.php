<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_recordings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_code', 20)->index();
            $table->string('speaker', 50);          // 'Host' | 'Guest'
            $table->string('file_path');             // storage path
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_recordings');
    }
};
