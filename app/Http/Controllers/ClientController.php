<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Listado de todos los clientes
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    // Mostrar el formulario de creación
    public function create()
    {
        return view('clients.create');
    }

    // Guardar un nuevo cliente (con validación básica)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telefono' => 'nullable',
            'direccion' => 'nullable',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Cliente creado con éxito.');
    }

    // REQUISITO TEMA 6: Mostrar cliente y SUS PEDIDOS
    public function show(Client $client)
    {
        // Cargamos los pedidos gracias a la relación que creamos antes
        $client->load('orders'); 
        return view('clients.show', compact('client'));
    }
}
