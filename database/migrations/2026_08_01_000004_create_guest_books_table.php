<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_books', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_no', 50);
            $table->string('name');
            $table->string('institution');
            $table->string('purpose');
            $table->text('feedback')->nullable();
            $table->text('note')->nullable();
            $table->date('date');
            $table->time('time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_books');
    }
};
