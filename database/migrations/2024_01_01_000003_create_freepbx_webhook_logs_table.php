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
        Schema::create('freepbx_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('url');
            $table->json('payload');
            $table->boolean('success')->default(false)->index();
            $table->integer('status_code')->default(0);
            $table->text('response')->nullable();
            $table->float('duration_ms')->default(0);
            $table->integer('attempt')->default(1);
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freepbx_webhook_logs');
    }
};
