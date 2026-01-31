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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sender');
            $table->string('device');
            $table->text('message');
            $table->string('inbox_id')->nullable();
            $table->timestamp('whatsapp_timestamp')->nullable(); // From Fonnte
            $table->string('type')->default('text');
            $table->string('direction')->default('incoming'); // incoming/outgoing
            $table->unsignedInteger('status_code')->nullable(); // HTTP response code
            $table->json('response')->nullable(); // Full response data
            $table->string('sender_name')->nullable(); // From Fonnte name field
            $table->index('sender');
            $table->index('device');
            $table->index('direction');
            $table->index('type');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
