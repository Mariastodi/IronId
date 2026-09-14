<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('matricula')->unique();
            $table->string('cpf')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('avatar_path')->nullable();
            $table->json('face_descriptor')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('plan_started_at')->nullable();
            $table->date('plan_expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
