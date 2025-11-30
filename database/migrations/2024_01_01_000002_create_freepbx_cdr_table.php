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
        Schema::create('freepbx_cdr', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->dateTime('call_date')->index();
            $table->string('clid')->nullable();
            $table->string('src')->index();
            $table->string('dst')->index();
            $table->string('dcontext')->nullable();
            $table->string('channel')->nullable();
            $table->string('dstchannel')->nullable();
            $table->string('lastapp')->nullable();
            $table->string('lastdata')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('billsec')->default(0);
            $table->string('disposition')->index();
            $table->integer('amaflags')->default(0);
            $table->string('accountcode')->nullable();
            $table->string('uniqueid')->unique();
            $table->text('userfield')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->index(['tenant_id', 'call_date']);
            $table->index(['tenant_id', 'disposition']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freepbx_cdr');
    }
};
