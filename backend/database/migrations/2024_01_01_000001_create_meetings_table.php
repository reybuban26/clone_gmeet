<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('meeting_code', 20)->unique();
            $table->string('host_peer_id')->nullable();
            $table->enum('status', ['waiting', 'active', 'ended'])->default('waiting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
