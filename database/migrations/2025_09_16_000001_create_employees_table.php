<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->text('address')->nullable();
                $table->date('date_of_birth');
                $table->string('phone')->nullable();
                $table->string('image')->nullable();
                $table->unsignedBigInteger('position_id')->nullable();
                $table->foreign('position_id')->references('id')->on('lookup');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status')->nullable();
                $table->time('working_hours_from')->nullable();
                $table->time('working_hours_to')->nullable();
                $table->boolean('monday')->default(false);
                $table->boolean('tuesday')->default(false);
                $table->boolean('wednesday')->default(false);
                $table->boolean('thursday')->default(false);
                $table->boolean('friday')->default(false);
                $table->boolean('saturday')->default(false);
                $table->boolean('sunday')->default(false);
                $table->integer('yearly_vacations_total')->default(0);
                $table->integer('yearly_vacations_used')->default(0);
                $table->integer('yearly_vacations_left')->default(0);
                $table->integer('sick_leave_used')->default(0);
                $table->decimal('last_salary', 10, 2)->nullable();
                $table->timestamps();
                $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
