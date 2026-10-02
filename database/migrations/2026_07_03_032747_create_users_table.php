<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();   // auto-incrementing 'id' column (primary key)
            $table->unsignedBigInteger('role_id');
            $table->string('username', 50)->unique();
            $table->string('full_name', 100);
            $table->string('password', 100);
            $table->boolean('is_active')->default(true);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps(); // created_at and updated_at timestamps

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
