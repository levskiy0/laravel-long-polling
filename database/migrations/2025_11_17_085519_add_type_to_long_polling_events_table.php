<?php

/**
 * add_type_to_long_polling_events_table.php
 * Migration to add type column to long_polling_events table
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 17.11.2025
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('long_polling_events', function (Blueprint $table) {
            $table->string('type', 50)->default('event')->after('channel_id');

            // Add composite index for efficient querying by channel_id and type
            $table->index('type');
            $table->index(['channel_id', 'type']);
            $table->index(['channel_id', 'type', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('long_polling_events', function (Blueprint $table) {
            $table->dropIndex(['channel_id', 'type', 'id']);
            $table->dropIndex(['channel_id', 'type']);
            $table->dropIndex('type');
            $table->dropColumn('type');
        });
    }
};
