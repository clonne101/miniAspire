<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asp_loans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->date('duration');
            $table->enum('repayment_frequency', ['MONTHLY','WEEKLY','NULL']);
            $table->float('interest_rate', 8, 2);
            $table->float('arrangement_fee', 8, 2);
            $table->decimal('credit_amount', 8, 2);
            $table->enum('status', ['CREDITED','PENDING','PARTIAL_PAYMENT','PAID']);
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
        Schema::dropIfExists('loans');
    }
}
