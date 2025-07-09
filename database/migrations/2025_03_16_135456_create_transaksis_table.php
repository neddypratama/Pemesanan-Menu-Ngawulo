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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal');
            $table->integer('total');
            // $table->integer('no_meja');
            $table->string('midtrans_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->enum('status', ['new', 'success', 'deliver', 'done', 'pending', 'error', 'expire', 'cancel', 'reviewed']);
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
