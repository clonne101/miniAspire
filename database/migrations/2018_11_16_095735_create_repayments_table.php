<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asp_repayments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('loan_id')->index();
            $table->decimal('amount_paid', 8, 2);
            $table->decimal('remaining_balance', 8, 2);
            $table->enum('status', ['SUCCESSFUL','FAILED','CLOSED']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayments');
    }
}
