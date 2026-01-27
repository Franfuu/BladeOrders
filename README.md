# BladeOrders: Sistema de Gestión de Pedidos

**BladeOrders** es una aplicación web CRUD desarrollada en Laravel para gestionar clientes y sus pedidos, con relaciones 1:N entre entidades.

## Inicio Rápido

```shell
# 1. Instalar dependencias
composer install

# 2. Configurar base de datos (copiar .env.example a .env si es necesario)
cp .env.example .env

# 3. Crear tablas y poblar con datos de prueba
php artisan migrate:fresh --seed

# 4. Iniciar servidor
php artisan serve
```

> Acceder a: **http://127.0.0.1:8000**

---

## Estructura del Proyecto

```
BladeOrders/
├── app/
│   ├── Http/Controllers/
│   │   ├── ClientController.php      # CRUD de clientes
│   │   └── OrderController.php       # CRUD de pedidos
│   └── Models/
│       ├── Client.php                # Modelo Cliente (hasMany)
│       └── Order.php                 # Modelo Pedido (belongsTo)
│
├── database/
│   ├── factories/
│   │   ├── ClientFactory.php         # Genera clientes falsos
│   │   └── OrderFactory.php          # Genera pedidos falsos
│   ├── migrations/
│   │   ├── create_clients_table.php
│   │   ├── create_orders_table.php
│   │   └── create_sessions_table.php
│   └── seeders/
│       └── DatabaseSeeder.php        # Pobla la BD
│
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php             # Layout principal
│   ├── clients/
│   │   ├── index.blade.php           # Lista clientes
│   │   ├── create.blade.php          # Crear cliente
│   │   ├── edit.blade.php            # Editar cliente
│   │   └── show.blade.php            # Ver cliente + pedidos
│   └── orders/
│       ├── index.blade.php           # Lista pedidos
│       ├── create.blade.php          # Crear pedido
│       └── edit.blade.php            # Editar pedido
│
├── routes/
│   └── web.php                       # Rutas de la aplicación
└── .env                              # Configuración de entorno
```

---

# Diario de Aprendizaje

## Día 1: Creación del Proyecto

### Comando `-mcr`: Crear estructura de un golpe

```shell
php artisan make:model Client -mcr
php artisan make:model Order -mcr
```

| Flag   | Crea                            |
| ------ | ------------------------------- |
| `-m` | Migración (tabla en BD)        |
| `-c` | Controlador                     |
| `-r` | Métodos CRUD en el controlador |

### Migraciones

Las migraciones son el "control de versiones" de la base de datos.

**create_clients_table.php**

```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('email')->unique();
    $table->string('telefono')->nullable();
    $table->string('direccion')->nullable();
    $table->timestamps();
});
```

**create_orders_table.php**

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained()->onDelete('cascade');
    $table->string('numero_pedido')->unique();
    $table->date('fecha');
    $table->enum('estado', ['pendiente', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
    $table->decimal('total', 10, 2);
    $table->timestamps();
});
```

**Conceptos clave:**

| Elemento                   | Descripción                                    |
| -------------------------- | ----------------------------------------------- |
| `foreignId('client_id')` | Clave foránea que conecta con clients          |
| `constrained()`          | Verifica que el cliente exista                  |
| `onDelete('cascade')`    | Si se borra el cliente, se borran sus pedidos   |
| `enum()`                 | Restringe valores a opciones específicas       |
| `decimal(10, 2)`         | Precisión para dinero (evita errores de float) |

---

## Día 2: Factories, Seeders y Vistas de Edición

### Factories: Generando datos falsos

Los Factories usan **Faker** para crear datos de prueba realistas.

**ClientFactory.php**

```php
public function definition(): array
{
    return [
        'nombre'    => $this->faker->name(),
        'email'     => $this->faker->unique()->safeEmail(),
        'telefono'  => $this->faker->phoneNumber(),
        'direccion' => $this->faker->address()
    ];
}
```

**OrderFactory.php**

```php
public function definition(): array
{
    return [
        'client_id'     => Client::factory(),
        'numero_pedido' => 'PED-' . $this->faker->unique()->numberBetween(1000, 9999),
        'fecha'         => $this->faker->dateTimeBetween('-1 year', 'now'),
        'estado'        => $this->faker->randomElement(['pendiente', 'enviado', 'entregado', 'cancelado']),
        'total'         => $this->faker->randomFloat(2, 10, 500)
    ];
}
```

### Métodos de Faker más usados

| Método                                             | Ejemplo            |
| --------------------------------------------------- | ------------------ |
| `$this->faker->name()`                            | "Juan García"     |
| `$this->faker->unique()->safeEmail()`             | "juan@example.com" |
| `$this->faker->phoneNumber()`                     | "+34 612 345 678"  |
| `$this->faker->address()`                         | "Calle Mayor 5"    |
| `$this->faker->numberBetween(1, 100)`             | 42                 |
| `$this->faker->randomFloat(2, 10, 500)`           | 123.45             |
| `$this->faker->randomElement(['a','b'])`          | "b"                |
| `$this->faker->dateTimeBetween('-1 year', 'now')` | "2025-06-15"       |

### DatabaseSeeder

```php
public function run(): void
{
    // Crear 10 clientes
    $clients = Client::factory(10)->create();

    // Crear 2-5 pedidos por cliente
    $clients->each(function ($client) {
        Order::factory(rand(2, 5))->create([
            'client_id' => $client->id
        ]);
    });
}
```

### Problemas resueltos

| Error                     | Causa                               | Solución                                            |
| ------------------------- | ----------------------------------- | ---------------------------------------------------- |
| "no such table: clients"  | Migraciones no ejecutadas           | `php artisan migrate:fresh`                        |
| "no such table: sessions" | Laravel usa sesiones en BD          | `php artisan session:table && php artisan migrate` |
| "No such file: User.php"  | DatabaseSeeder usa User por defecto | Modificar seeder para usar Client/Order              |

---

## Día 3: Arquitectura MVC

### Flujo de una petición

```
Navegador → Rutas → Controlador → Modelo ↔ BD
                        ↓
                      Vista → HTML → Navegador
```

### Modelos (app/Models/)

Representan tablas y definen relaciones.

**Client.php**

```php
class Client extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'email', 'telefono', 'direccion'];

    // Un cliente tiene muchos pedidos
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
```

**Order.php**

```php
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'numero_pedido', 'fecha', 'estado', 'total'];

    protected $casts = ['fecha' => 'date'];

    // Un pedido pertenece a un cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
```

| Elemento        | Descripción                                       |
| --------------- | -------------------------------------------------- |
| `$fillable`   | Campos permitidos para asignación masiva          |
| `$casts`      | Convierte automáticamente tipos (fecha → Carbon) |
| `hasMany()`   | Relación 1:N (cliente → pedidos)                 |
| `belongsTo()` | Relación inversa N:1 (pedido → cliente)          |

### Controladores (app/Http/Controllers/)

Contienen la lógica de negocio.

**ClientController.php**

```php
class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

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

    public function show(Client $client)
    {
        $client->load('orders'); // Carga los pedidos del cliente
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'telefono' => 'nullable',
            'direccion' => 'nullable',
        ]);

        $client->update($validated);
        return redirect()->route('clients.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
```

**OrderController.php**

```php
class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('client')->get(); // Eager loading
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
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

    public function edit(Order $order)
    {
        $clients = Client::all();
        return view('orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'numero_pedido' => 'required|unique:orders,numero_pedido,' . $order->id,
            'fecha' => 'required|date',
            'estado' => 'required|in:pendiente,enviado,entregado,cancelado',
            'total' => 'required|numeric|min:0',
        ]);

        $order->update($validated);
        return redirect()->route('orders.index')->with('success', 'Pedido actualizado con éxito.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Pedido eliminado con éxito.');
    }
}
```

### Métodos CRUD del Controlador

| Método          | HTTP   | Ruta               | Descripción      |
| ---------------- | ------ | ------------------ | ----------------- |
| `index()`      | GET    | /clients           | Listar todos      |
| `create()`     | GET    | /clients/create    | Formulario crear  |
| `store()`      | POST   | /clients           | Guardar nuevo     |
| `show($id)`    | GET    | /clients/{id}      | Ver detalle       |
| `edit($id)`    | GET    | /clients/{id}/edit | Formulario editar |
| `update()`     | PUT    | /clients/{id}      | Actualizar        |
| `destroy($id)` | DELETE | /clients/{id}      | Eliminar          |

### Rutas (routes/web.php)

```php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;

Route::get('/', fn() => redirect()->route('clients.index'));

Route::resource('clients', ClientController::class);
Route::resource('orders', OrderController::class);
```

`Route::resource()` genera automáticamente las 7 rutas CRUD.

### Sintaxis Blade (Vistas)

```blade
{{-- Extender layout --}}
@extends('layouts.app')

@section('content')
    {{-- Mostrar variables --}}
    {{ $variable }}

    {{-- Bucles --}}
    @foreach($items as $item)
        {{ $item->nombre }}
    @endforeach

    {{-- Condicionales --}}
    @if($condicion)
        ...
    @endif

    {{-- Formularios --}}
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf
        @method('PUT') {{-- Para update/delete --}}
    </form>

    {{-- Errores de validación --}}
    @error('campo')
        <span>{{ $message }}</span>
    @enderror
@endsection
```

---

## Comandos Artisan

### Crear componentes

```shell
php artisan make:model Nombre -mcrf     # Modelo + Migración + Controlador + Factory
php artisan make:controller Nombre      # Solo controlador
php artisan make:migration create_x     # Solo migración
php artisan make:factory Nombre         # Solo factory
php artisan make:seeder Nombre          # Solo seeder
```

### Base de datos

```shell
php artisan migrate                     # Ejecutar migraciones
php artisan migrate:fresh               # Borrar todo y recrear
php artisan migrate:fresh --seed        # Recrear + poblar datos
php artisan migrate:status              # Ver estado
php artisan db:seed                     # Solo poblar datos
```

### Servidor y depuración

```shell
php artisan serve                       # Iniciar servidor (http://127.0.0.1:8000)
php artisan route:list                  # Ver todas las rutas
php artisan cache:clear                 # Limpiar caché
```

### Flujo completo para iniciar

```shell
php artisan migrate:fresh --seed        # Crear BD + datos
php artisan serve                       # Iniciar servidor
```

> ⚠️ `php artisan serve` **solo inicia el servidor**, no crea tablas ni datos. Ejecuta primero `migrate` y `db:seed`.

---

## Tecnologías

- **Laravel 11** - Framework PHP
- **SQLite** - Base de datos
- **Blade** - Motor de plantillas
