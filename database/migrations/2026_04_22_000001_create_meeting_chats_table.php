<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_chats', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_code', 20)->index();
            $table->string('sender', 50);   // 'Host' | 'Guest'
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_chats');
    }
};
