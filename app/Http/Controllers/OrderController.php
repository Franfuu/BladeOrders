<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // REQUISITO: Mostrar nombre del cliente (usamos with para eficiencia)
        $orders = Order::with('client')->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        // Necesitamos pasar los clientes para seleccionarlos en el formulario
        $clients = Client::all();
        return view('orders.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'numero_pedido' => 'required|unique:orders',
            'fecha' => 'required|date',
            'estado' => 'required|in:pendiente,enviado,entregado,cancelado',
            'total' => 'required|numeric|min:0',
        ]);

        Order::create($validated);

        return redirect()->route('orders.index')->with('success', 'Pedido registrado.');
    }
}