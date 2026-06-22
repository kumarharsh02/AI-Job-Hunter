<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            $table->string('tone')->default('professional');

            $table->longText('content');
            $table->json('ai_metadata')->nullable();

            $table->string('model_used')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();

            $table->unsignedTinyInteger('version')->default(1);

            $table->index(['application_id', 'version']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letters');
    }
};