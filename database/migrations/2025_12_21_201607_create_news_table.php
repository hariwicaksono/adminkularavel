<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('konten')->nullable();
            $table->date('tanggal')->nullable();
            $table->tinyInteger('jenis')->comment('1=News Ticker, 2=Informasi, 3=Masjid');
            $table->boolean('status')->default(1)->comment('1=Aktif, 0=Nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
