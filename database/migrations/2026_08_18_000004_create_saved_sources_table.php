<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saved_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('research_topic_id')->nullable()->constrained('research_topics')->nullOnDelete();
            $table->enum('source_type', ['local', 'external']);
            $table->foreignId('book_id')->nullable()->constrained('books')->cascadeOnDelete();
            $table->foreignId('external_source_id')->nullable()->constrained('external_sources')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['unread', 'reading', 'completed'])->default('unread');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_sources');
    }
};
