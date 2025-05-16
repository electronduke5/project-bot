<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->nullable()->unique();
            $table->string('tg_id')->unique();
            $table->integer('points')->unsigned()->default(0);
            $table->integer('gems')->unsigned()->default(0);
            $table->timestamp('last_request')->nullable();
            $table->string('timeout')->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE users ALTER COLUMN timeout TYPE interval USING (timeout::interval)');
        DB::statement("ALTER TABLE users ALTER COLUMN timeout SET DEFAULT '60 minutes'::interval");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
