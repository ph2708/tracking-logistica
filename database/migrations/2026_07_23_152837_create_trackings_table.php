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
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'entrega' or 'coleta'
            $table->string('order_number')->index(); // Purchase/Sales Order number
            $table->string('status'); // pendente_roteirizacao, pendente_coleta, pendente_entrega, em_transporte, entregue, coletado, finalizada
            $table->text('observations_origin')->nullable(); // estoque or compras observations
            $table->text('observations_logistics')->nullable();
            $table->string('transport_type')->nullable(); // 'proprio' or 'terceirizado'
            $table->string('vehicle_info')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('carrier_name')->nullable();
            $table->decimal('weight', 15, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->string('invoice_path')->nullable();
            $table->string('qrcode_token')->unique()->index();
            $table->string('collection_address')->nullable();
            $table->datetime('collection_schedule')->nullable();
            $table->datetime('departure_time')->nullable();
            $table->datetime('completion_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};
