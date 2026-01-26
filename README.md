# BladeOrders: Sistema de Gestión de Pedidos

**BladeOrders** es una aplicación web desarrollada en Laravel diseñada para centralizar la gestión de clientes y sus respectivos pedidos, permitiendo un control total sobre el flujo de ventas de un pequeño negocio.

# Cosas aprendidas:

## Día 1

### El atajo "-mcr": Creando todo de un golpe

**¿Para qué sirve?**: Con este añadido al final del comando, Laravel te construye la estructura básica de una sección de tu web de un solo golpe. Te crea tres archivos clave:

* **La M (Migración):** El plano para crear la tabla en la base de datos.
* **La C (Controlador):** El "cerebro" que recibe las peticiones de los usuarios.
* **La R (Recurso):** Hace que ese controlador ya venga con los métodos estándar para ver, crear, editar y borrar (el famoso CRUD) ya escritos, para que no tengas que crearlos tú uno a uno.

**Uso real**: Se utiliza para **ahorrar tiempo y evitar errores de nombres**.

---

# Diario de Trabajo: Día 1

## Creación del proyecto

Primero creamos el proyecto y entramos a él con los comandos:

```shell
composer create-project laravel/laravel BladeOrders
cd BladeOrders
```

## Creación del modelo, Migración y Controladores

Luego creamos el modelo, migraciones y controladores.

```shell
php artisan make:model Client -mcr
php artisan make:model Order -mcr
```

> **Nota:**
>
> `-mcr` crea el Modelo, La Migración y el Controlador con los métodos CRUD ya definidos

---

## Migraciones (Database)

### create_clients_table

Esta tabla almacena la información básica de los clientes que realizarán pedidos.

* **`id()`**: Crea un campo autoincremental como clave primaria.
* **`nombre`**: Campo de texto estándar para el nombre del cliente.
* **`email`**: Se marca como `unique()` para evitar que dos clientes se registren con el mismo correo.
* **`telefono` y `direccion`**: Campos opcionales (`nullable()`) para información de contacto.
* **`timestamps()`**: Crea automáticamente las columnas `created_at` y `updated_at`.

```php
public function up(): void
{
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('email')->unique();
        $table->string('telefono')->nullable();
        $table->string('direccion')->nullable();
        $table->timestamps();
    });
}
```

### create_orders_table

Esta tabla gestiona los pedidos y vincula cada compra con un cliente específico.

* **`foreignId('client_id')`**: Es la pieza clave de la relación. Conecta el pedido con un ID de la tabla `clients`.
* **`constrained()`**: Asegura que el cliente realmente exista.
* **`onDelete('cascade')`**: Si un cliente es eliminado de la base de datos, todos sus pedidos se borrarán automáticamente para no dejar datos huérfanos.
* **`numero_pedido`**: Un identificador único para seguimiento comercial (diferente al ID interno).
* **`fecha`**: Registra el momento exacto de la venta.
* **`estado`**: Utiliza un `enum`, lo que restringe los valores posibles a solo cuatro opciones específicas (`pendiente`, `enviado`, `entregado`, `cancelado`), garantizando la integridad de los datos.
* **`total`**: Definido como `decimal(10, 2)` para manejar dinero con precisión (evitando los errores de redondeo de los tipos *float*).

```php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->string('numero_pedido')->unique();
        $table->date('fecha');
        $table->enum('estado', ['pendiente', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
        $table->decimal('total', 10, 2);
        $table->timestamps();
    });
}
```

---

## Estructura de archivos creados

Tras ejecutar los comandos del día 1, se generaron los siguientes archivos:

```
app/
├── Http/
│   └── Controllers/
│       ├── ClientController.php    # Controlador CRUD para clientes
│       └── OrderController.php     # Controlador CRUD para pedidos
└── Models/
    ├── Client.php                  # Modelo de Cliente
    └── Order.php                   # Modelo de Pedido

database/
└── migrations/
    ├── 2026_01_25_193533_create_clients_table.php
    └── 2026_01_25_193546_create_orders_table.php
```

---

# Diario de Trabajo: Día 2

## Problemas encontrados y soluciones

### Error: "no such table: clients"

Al intentar acceder a `/clients`, Laravel mostraba un error indicando que la tabla no existía.

**Causa**: Las migraciones no se habían ejecutado o el archivo de base de datos se había corrompido.

**Solución**: Ejecutar las migraciones con el comando `fresh` para recrear todas las tablas:

```shell
php artisan migrate:fresh
```

### Error: "no such table: sessions"

Laravel estaba configurado para usar sesiones en base de datos, pero la tabla no existía.

**Solución**: Crear la migración de sesiones y ejecutarla:

```shell
php artisan session:table
php artisan migrate
```

### Error: "No such file: User.php"

Al ejecutar `--seed`, el seeder intentaba crear usuarios con un modelo `User` que no existía en el proyecto.

**Causa**: El `DatabaseSeeder` por defecto viene con código para crear usuarios de prueba.

**Solución**: Modificar el seeder para usar los modelos propios del proyecto (`Client` y `Order`).

---

## Factories: Generando datos de prueba

### ¿Qué son los Factories?

Los **Factories** son clases que definen cómo generar datos falsos para poblar la base de datos durante el desarrollo y testing. Utilizan la librería **Faker** para crear datos realistas.

### ClientFactory

Genera clientes con datos aleatorios:

```php
public function definition(): array
{
    return [
        'nombre' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'telefono' => fake()->phoneNumber(),
        'direccion' => fake()->address()
    ];
}
```

### OrderFactory

Genera pedidos con datos aleatorios. **Importante**: Debe incluir `client_id` para la relación con clientes:

```php
public function definition(): array
{
    return [
        'client_id' => Client::factory(),
        'numero_pedido' => 'PED-' . fake()->unique()->numberBetween(1000, 9999),
        'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
        'estado' => fake()->randomElement(['pendiente', 'enviado', 'entregado', 'cancelado']),
        'total' => fake()->randomFloat(2, 10, 500)
    ];
}
```

### DatabaseSeeder

El seeder principal que orquesta la creación de datos:

```php
public function run(): void
{
    // Crear 10 clientes de prueba
    $clients = Client::factory(10)->create();

    // Crear 2-5 orders para cada cliente
    $clients->each(function ($client) {
        Order::factory(rand(2, 5))->create([
            'client_id' => $client->id
        ]);
    });
}
```

**Ejecutar seeders**:

```shell
php artisan migrate:fresh --seed
```

---

## Vistas de Edición (CRUD completo)

### Vista clients/edit.blade.php

Formulario para editar clientes existentes. Características clave:

* **`@method('PUT')`**: Indica que es una actualización (HTTP no soporta PUT nativamente en formularios).
* **`old('campo', $client->campo)`**: Muestra el valor anterior si hay error de validación, o el valor actual del modelo.
* **`@error('campo')`**: Muestra mensajes de error de validación.

```blade
<form action="{{ route('clients.update', $client) }}" method="POST">
    @csrf
    @method('PUT')
    
    <input type="text" name="nombre" value="{{ old('nombre', $client->nombre) }}">
    @error('nombre')
        <span class="text-red-500">{{ $message }}</span>
    @enderror
</form>
```

### Vista orders/edit.blade.php

Similar a clientes, pero con:

* **Select de cliente**: Con el cliente actual preseleccionado.
* **Select de estado**: Con el estado actual preseleccionado.
* **Campo fecha**: Formateado correctamente con `$order->fecha->format('Y-m-d')`.

---

## Métodos del Controlador para Edición

### ClientController

```php
// Mostrar formulario de edición
public function edit(Client $client)
{
    return view('clients.edit', compact('client'));
}

// Procesar actualización
public function update(Request $request, Client $client)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'email' => 'required|email|unique:clients,email,' . $client->id,
        // unique:tabla,campo,excepto_id -> ignora el registro actual
    ]);

    $client->update($validated);
    return redirect()->route('clients.index')->with('success', 'Cliente actualizado.');
}

// Eliminar cliente
public function destroy(Client $client)
{
    $client->delete();
    return redirect()->route('clients.index')->with('success', 'Cliente eliminado.');
}
```

### OrderController

```php
public function edit(Order $order)
{
    $clients = Client::all(); // Necesario para el select
    return view('orders.edit', compact('order', 'clients'));
}

public function update(Request $request, Order $order)
{
    $validated = $request->validate([
        'numero_pedido' => 'required|unique:orders,numero_pedido,' . $order->id,
        // ... resto de validaciones
    ]);

    $order->update($validated);
    return redirect()->route('orders.index')->with('success', 'Pedido actualizado.');
}
```

---

## Cast de fechas en el Modelo

Para que Laravel trate el campo `fecha` como un objeto Carbon (y poder usar `->format()`), se añade el cast en el modelo:

```php
class Order extends Model
{
    protected $casts = [
        'fecha' => 'date',
    ];
}
```

---

## Estructura de archivos del Día 2

```
database/
├── factories/
│   ├── ClientFactory.php           # Factory para generar clientes
│   └── OrderFactory.php            # Factory para generar pedidos
├── migrations/
│   └── 2026_01_26_122822_create_sessions_table.php
└── seeders/
    └── DatabaseSeeder.php          # Seeder principal actualizado

resources/views/
├── clients/
│   └── edit.blade.php              # Formulario de edición de clientes
└── orders/
    └── edit.blade.php              # Formulario de edición de pedidos
```
