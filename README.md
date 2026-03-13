# Nuevo Mundo

Sistema de gestion interna desarrollado con Laravel 12, Inertia.js, Vue 3 y MySQL.

## Stack

- PHP 8.2+
- Laravel 12
- Inertia.js + Vue 3
- Vite
- MySQL
- Spatie Laravel Permission

## Modulos

- Roles
- Permisos
- Usuarios
- Clientes
- Productos
- Pedidos
- Seguimientos

## Requisitos locales

Antes de levantar el proyecto, asegurese de tener instalado:

- PHP 8.2 o superior
- Composer
- Node.js 20+ y npm
- MySQL 8+

## Despliegue local

### 1. Clonar el proyecto

```bash
git clone <repo-url> nuevo-mundo
cd nuevo-mundo
```

### 2. Instalar dependencias backend

```bash
composer install
```

### 3. Instalar dependencias frontend

```bash
npm install
```

### 4. Configurar entorno

Crear el archivo `.env` a partir de `.env.example` si aun no existe:

```bash
cp .env.example .env
```

Generar la llave de aplicacion:

```bash
php artisan key:generate
```

### 5. Configurar base de datos

En el archivo `.env`, usar una configuracion similar:

```env
APP_NAME="Nuevo Mundo"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_nuevo_mundo
DB_USERNAME=root
DB_PASSWORD=
```

Crear previamente la base de datos `bd_nuevo_mundo` en MySQL.

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Si desea reiniciar completamente la base local:

```bash
php artisan migrate:fresh --seed
```

### 7. Levantar el proyecto

En una terminal:

```bash
composer run dev
```

La aplicacion quedara disponible en:

```text
http://localhost:8000
```

## Usuarios semilla

- SuperAdmin: `admin@win.com.pe` / `password`
- Seguridad: `seguridad@win.com.pe` / `password`
- Ventas: `ventas@win.com.pe` / `password`
- Almacen: `almacen@win.com.pe` / `password`

## Comandos utiles

```bash
php artisan test
npm run build
vendor/bin/pint
```

## Notas

- Los permisos estan definidos por opcion del sistema.
- Los menus laterales se muestran segun los permisos del usuario autenticado.
- Los pedidos manejan correlativo propio desde tabla de series.
- Los estados de seguimiento se administran desde su propia tabla.
