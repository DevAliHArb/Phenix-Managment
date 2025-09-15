<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('verified')->default(User::UNVERIFIED_USER)->nullable();
            $table->string('verification_token')->nullable();
            $table->string('active')->default(User::ACTIVE_USER)->nullable();
            $table->string('google')->default(User::REGULAR_USER)->nullable();
            $table->string('blocked')->default(User::BLOCKED_USER)->nullable();
            $table->string('currency')->default(User::USER_DEFAULT_CURRENCY)->nullable();
            $table->string('language')->default(User::USER_DEFAULT_LANGUAGE)->nullable();
            $table->string('type')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->decimal('tva', 10, 2)->nullable();
            $table->string('siret')->nullable();
            $table->string('token')->nullable();
            $table->string('delete_token')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
