<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // id is auto-increment and primary key by default
            $table->string('name');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('position_id');
            $table->date('birthdate');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('employment_type');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('position_id')->references('id')->on('lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
