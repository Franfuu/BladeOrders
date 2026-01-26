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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relación: Order belongsTo Client con clave foránea
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('numero_pedido')->unique(); // Requisito: Único
            $table->date('fecha');
            // Requisito: Estados permitidos
            $table->enum('estado', ['pendiente', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2); // Requisito: Total del pedido
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
