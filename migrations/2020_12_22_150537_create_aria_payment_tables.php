<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAriaPaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('amount');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });


        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255)->nullable();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('invoiceable_id')->nullable();
            $table->string('invoiceable_type')->nullable();
            $table->string('amount');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('description', 500)->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->index(["invoiceable_id" , "invoiceable_type"]);
        });


        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('ref_id')->nullable();
            $table->string('res_id');
            $table->string('amount');
            $table->unsignedTinyInteger('status');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->index('ref_id');
            $table->index('res_id');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aria_payment_tables');
    }
}
