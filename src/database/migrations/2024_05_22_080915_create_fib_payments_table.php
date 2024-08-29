<?php

    use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateFibPaymentsTable extends Migration
    {
        public function up(): void
        {
            Schema::create('fib_payments', function (Blueprint $table) {
                $table->increments('id'); // Use increments for older Laravel versions
                $table->string('fib_payment_id')->unique();
                $table->string('readable_code');
                $table->string('personal_app_link');
                $table->string('status')->index()->default(FibPayment::UNPAID);
                $table->integer('amount');
                $table->dateTime('valid_until');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('fib_payments');
        }
    }
