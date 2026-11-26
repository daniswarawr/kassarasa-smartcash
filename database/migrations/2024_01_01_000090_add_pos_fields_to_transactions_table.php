<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable()->after('transaction_code');
            $table->decimal('cash_received', 15, 2)->default(0)->after('total_price');
            $table->decimal('change_amount', 15, 2)->default(0)->after('cash_received');
            $table->enum('kitchen_status', ['waiting', 'preparing', 'ready', 'served'])->default('waiting')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'customer_name', 'cash_received', 'change_amount', 'kitchen_status']);
        });
    }
};
