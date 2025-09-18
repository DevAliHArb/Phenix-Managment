<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_improvement_id');
            $table->decimal('salary', 10, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('position_improvement_id')->references('id')->on('position_improvements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
