# Guía para instalar la base de datos en otro PC

## Pasos para transferir tu proyecto Symfony a otro PC:

### 1. Copiar archivos del proyecto
- Copia toda la carpeta del proyecto Symfony
- Incluye el archivo `backup_database.sql` que contiene todos los datos

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar la base de datos
- Instala MySQL/MariaDB en el nuevo PC
- Crea la base de datos `futbol`
- Importa el archivo `backup_database.sql`

### 4. Configurar variables de entorno
Crea un archivo `.env.local` con:
```
DATABASE_URL=mysql://usuario:password@127.0.0.1:3306/futbol?serverVersion=8.0.32&charset=utf8mb4
```

### 5. Verificar la instalación
```bash
php bin/console doctrine:schema:validate
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM club"
```

## Archivos importantes a copiar:
- ✅ Todo el código fuente (`src/`)
- ✅ Configuración (`config/`)
- ✅ Migraciones (`migrations/`)
- ✅ `backup_database.sql` (este archivo)
- ✅ `composer.json` y `composer.lock`

## ¡Listo! Tu proyecto debería funcionar igual en el nuevo PC.
