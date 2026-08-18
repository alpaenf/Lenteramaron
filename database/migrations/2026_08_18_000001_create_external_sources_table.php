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
        Schema::create('external_sources', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index(); // OpenAlex ID, S2 ID, etc.
            $table->string('source_provider')->default('openalex'); // openalex, semantic_scholar, crossref
            $table->text('title');
            $table->json('authors')->nullable();
            $table->integer('publication_year')->nullable()->index();
            $table->string('publisher_or_journal')->nullable();
            $table->string('doi')->nullable()->index();
            $table->text('url')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('abstract')->nullable();
            $table->integer('citation_count')->default(0);
            $table->boolean('open_access')->default(false);
            $table->json('keywords')->nullable();
            $table->timestamps();

            $table->unique(['external_id', 'source_provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_sources');
    }
};
