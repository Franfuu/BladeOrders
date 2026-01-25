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
