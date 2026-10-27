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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ico')->nullable();
            $table->boolean('vat_payer')->default(true);
            $table->text('registry_info')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->date('taxable_supply_date')->nullable();
            $table->string('variable_symbol', 20)->nullable();
            $table->string('constant_symbol', 10)->nullable();
            $table->string('specific_symbol', 20)->nullable();
            $table->string('payment_method', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'ico',
                'vat_payer',
                'registry_info',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'taxable_supply_date',
                'variable_symbol',
                'constant_symbol',
                'specific_symbol',
                'payment_method',
            ]);
        });
    }
};
