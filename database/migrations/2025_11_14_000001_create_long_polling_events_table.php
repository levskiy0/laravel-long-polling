<?php

/**
 * create_long_polling_events_table.php
 * Migration for long polling events table
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('long_polling_events', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id')->index();
            $table->longText('event');
            $table->timestamp('created_at')->useCurrent();

            // Index for efficient querying by channel_id and offset (id)
            $table->index(['channel_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('long_polling_events');
    }
};
