<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('position_improvements', function (Blueprint $table) {
            $table->id(); // id is auto-increment and primary key by default
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('lookup');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_improvements');
    }
};
