<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tickets', 'sla_deadline')) {
                $table->dateTime('sla_deadline')->nullable()->after('status');
            }
            if (! Schema::hasColumn('tickets', 'is_breached')) {
                $table->boolean('is_breached')->default(false)->after('sla_deadline');
            }
            if (! Schema::hasColumn('tickets', 'resolved_at')) {
                $table->dateTime('resolved_at')->nullable()->after('is_breached');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
            // Don't drop in down if you want safe rollback, but ok
        });
    }
};
