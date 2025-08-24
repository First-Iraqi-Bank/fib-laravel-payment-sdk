<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFibRefundsTable extends Migration
{
    public function up(): void
    {
        Schema::create('fib_refunds', function (Blueprint $table) {
            $table->increments('id'); // Use increments for older Laravel versions
            $table->string('fib_trace_id')->nullable();
            $table->string('status')->default('PENDING')->index();
            $table->string('refund_details')->nullable();
            $table->string('refund_failure_reason')->nullable();
            $table->timestamps();

            $table->unsignedInteger('payment_id');
            $table->foreign('payment_id')
                ->references('id')->on('fib_payments')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fib_refunds');
    }
}
