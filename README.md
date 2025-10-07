# Futbol-Clubs-Gestor
Futbol-Club-Gestor es una aplicacion creada para facilitar la tarea de gestionar un club de futbol y a todos los miembros!

## Funcionalidades del proyecto
- `Dar de alta clubs, entrenadores y jugadores` : La aplicacion permite que crees un club nuevo, puedas dar de alta a un jugador o un entrenador en un club existente o que crees entrenadores y jugadores sin ningun club

- `Editar la informacion de los clubs y sus miembros`: La aplicacion tiene implementada la posiblidad de poder editar la informacion de los clubs y sus miembros, pudiendo dejar a jugadores y/o entrenadores sin equipo o cambiando sus datos personales

- `Eliminar clubs y miembros` : Se tiene la posiblidad de poder eliminar a jugadores, entrenadores y a los clubs

- `Listar jugadores, clubs y entrenadores` : Se podran mostrar a todos los clubs, jugadores y entrenadores que tiene la aplicacion, soporta filtros por nombre en jugadores y paginacion ademas de la posibilidad de ver informacion especifica de un jugador 

# Tecnologías utilizadas
- **PHP**: PHP 8.2/Symfony 7
- **ORM**: Doctrine ORM
- **Base de datos**: MySQL8.0+

## Instalacion

Clonamos el repositorio

```bash
#Clonamos el repositorio
git clone https://github.com/IagoFP2004/Club-Manager-API.git
```

Intalamos las dependencias

```bash
#composer
composer install
```

## Configurar base de datos

### Paso 1: Importar la base de datos

Importa la base de datos usando el script PHP incluido:

```bash
# Importar futbol.sql
php scripts/import_backup.php futbol.sql
```

Este comando:
-  Crea la base de datos `futbol`
-  Crea las tablas: `club`, `player`, `coach`
-  Inserta datos de ejemplo (clubes, jugadores y entrenadores)
-  Configura las relaciones entre tablas

### Paso 2: Configurar variables de entorno

Crea el archivo `.env` con la configuracion para conectarse a la base de datos

puedes usar el ejemplo del archivo `.env.example` para crear las variables de entorno que se van a usar

## Database Configuration - MySQL
DATABASE_URL="mysql://root:@127.0.0.1:3306/futbol?serverVersion=8.0&charset=utf8mb4"

## Mailer Configuration - STMP
MAILER_DSN=null://null

## Symfony Configuration
APP_ENV=test

APP_SECRET=change_secret

### Paso 3: Iniciar el servidor
```bash
Opcion 1: Servidor PHP integrado
php -S localhost:8000 -t public
```

```bash
Opcion 2: Servidor Symfony
symfony server:start
```

## API Endpoints

### Clubs

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/clubs` | Listar todos los clubs |
| `GET` | `/clubs/{id}` | Obtener club por ID |
| `POST` | `/clubs` | Crear nuevo club |
| `PUT` | `/clubs/{id}` | Actualizar club |
| `DELETE` | `/clubs/{id}` | Eliminar club |

### Jugadores (Players)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/players` | Listar todos los jugadores |
| `GET` | `/players/{id}` | Obtener jugador por ID |
| `POST` | `/players` | Crear nuevo jugador |
| `PUT` | `/players/{id}` | Actualizar jugador |
| `DELETE` | `/players/{id}` | Eliminar jugador |

### Entrenadores (Coaches)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/coaches` | Listar todos los entrenadores |
| `GET` | `/coaches/{id}` | Obtener entrenador por ID |
| `POST` | `/coaches` | Crear nuevo entrenador |
| `PUT` | `/coaches/{id}` | Actualizar entrenador |
| `DELETE` | `/coaches/{id}` | Eliminar entrenador |

### Usuarios (User)
| Método | Endpoint        | Descripción                                              |
|--------|-----------------|----------------------------------------------------------|
| `POST` | `/login`        | Iniciar sesion con `email` y `password`                  |
| `POST` | `/alta/register` | Dar de alta un usuario (Sin necesidad de estar logueado) |


Para poder hacer una consulta a los endpoints deberas estar logueado, en la ruta `/login` seran necesarios los campos `email` y `password`, si los datos son correctos recibiras un estado `200` y el `token` que sera necesario para poder trabajar con los endpoints, en caso contrario recibirar un error con la cauda del fallo.

### Datos acceso usuarios
| email | password        
|----|-----------------
| `admin@futbol.com` | `admin123`    
|`test@futbol.com`| `test123`

* Nota: Las contraseñas estaran hasheadas y cuando se haga login se compararán las originales con el hash usando los metodos que el lenguaje ya nos facilita



### Ejemplos de uso

#### Crear un jugador
```bash
curl -X POST http://localhost:8000/players \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Lionel",
    "apellidos": "Messi",
    "dorsal": 10,
    "salario": 50000000,
    "id_club": "FCB"
  }'
```

#### Crear un entrenador
```bash
curl -X POST http://localhost:8000/coaches \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "nombre": "Pep",
    "apellidos": "Guardiola",
    "salario": 20000000,
    "id_club": "FCB"
  }'
```

#### Crear un club
```bash
curl -X POST http://localhost:8000/clubs \
  -H "Content-Type: application/json" \
  -d '{
    "id_club": "BAR",
    "nombre": "FC Barcelona",
    "fundacion": 1899,
    "ciudad": "Barcelona",
    "estadio": "Camp Nou",
    "presupuesto": 1000000000
  }'
```

### Validaciones importantes

- **Presupuesto**: Los clubs no pueden tener gastos (jugadores + entrenadores) que superen su presupuesto
- **DNI único**: Los entrenadores deben tener un DNI único
- **Dorsal único**: Los jugadores no pueden tener el mismo dorsal en el mismo club
- **Un entrenador por club**: Cada club solo puede tener un entrenador

## Códigos de Error y Respuestas

### Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| `200` | OK - Operación exitosa |
| `400` | Bad Request - Datos inválidos o faltantes |
| `404` | Not Found - Recurso no encontrado |
| `500` | Internal Server Error - Error del servidor |

### Formato de Respuestas de Error

Todas las respuestas de error siguen el formato:

```json
{
  "error": "Descripción del error"
}
```

### Errores Comunes

#### **400 - Bad Request**

**Campos requeridos faltantes:**
```json
{
  "error": "Todos los campos son requeridos"
}
```

**JSON inválido:**
```json
{
  "error": "JSON inválido"
}
```

**Datos inválidos:**
```json
{
  "error": "El salario no puede ser 0 o negativo"
}
```

**Validaciones de negocio:**
```json
{
  "error": "El Club no tiene presupuesto suficiente. Presupuesto restante: 5000000"
}
```

```json
{
  "error": "El dorsal debe ser mayor que 0 y menor que 100"
}
```

```json
{
  "error": "El dorsal ya existe en el club"
}
```

```json
{
  "error": "Este club ya tiene un entrenador asignado"
}
```

```json
{
  "error": "El DNI no puede ser modificado"
}
```

#### **404 - Not Found**

**Recurso no encontrado:**
```json
{
  "error": "Player not found"
}
```

```json
{
  "error": "Coach not found"
}
```

```json
{
  "error": "Club not found"
}
```

#### **500 - Internal Server Error**

**Errores de email:**
```json
{
  "error": "Error al enviar el email: [detalle del error]"
}
```

### Ejemplos de Manejo de Errores

#### **Crear jugador con datos faltantes:**
```bash
curl -X POST http://localhost:8000/players \
  -H "Content-Type: application/json" \
  -d '{"nombre": "Lionel"}'
```

**Respuesta:**
```json
{
  "error": "Todos los campos son requeridos"
}
```

#### **Crear entrenador con DNI duplicado:**
```bash
curl -X POST http://localhost:8000/coaches \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "11223344C",
    "nombre": "Nuevo",
    "apellidos": "Entrenador",
    "salario": "1000000"
  }'
```

**Respuesta:**
```json
{
  "error": "El DNI ya existe"
}
```

#### **Actualizar jugador con salario que excede presupuesto:**
```bash
curl -X PUT http://localhost:8000/players/1 \
  -H "Content-Type: application/json" \
  -d '{"salario": "100000000"}'
```

**Respuesta:**
```json
{
  "error": "El Club no tiene presupuesto suficiente. Presupuesto restante: 5000000"
}
```

La paginación está disponible en todos los endpoints de listado:

##  Filtros y Paginación

### Filtros Disponibles

#### Jugadores
- `nombre`: Filtrar por nombre del jugador

#### Clubs
- `nombre`: Filtrar por nombre del club (búsqueda parcial)

#### Entrenadores
- `nombre`: Filtrar por nombre del entrenador (búsqueda parcial)

### Paginación

La paginación está disponible en todos los endpoints de listado:

```
GET /players?page=1&pageSize=10
GET /clubs?page=1&pageSize=10
GET /coaches?page=1&pageSize=10
```

**Respuesta incluye metadatos de paginación:**
```json
{
  "players": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total_items": 25,
    "total_pages": 3,
    "has_next_page": true,
    "has_prev_page": false,
    "next_page": 2,
    "prev_page": null
  }
}
```

### Ejemplos de Uso

```bash
# Listar jugadores con paginación
GET /players?page=2&pageSize=5

# Filtrar jugadores por nombre
GET /players?nombre=Iago

# Listar clubs con paginación y filtro por nombre
GET /clubs?page=1&pageSize=5&nombre=Real

# Obtener club específico por ID numérico
GET /clubs/1

# Crear nuevo club
POST /clubs
{
  "id_club": "NEW",
  "nombre": "Nuevo Club",
  "fundacion": 2024,
  "ciudad": "Madrid",
  "estadio": "Nuevo Estadio",
  "presupuesto": "100000000"
}

# Listar entrenadores con filtro por nombre
GET /coaches?nombre=José&page=1&pageSize=10

# Combinar filtros en jugadores
GET /players?nombre=Iago&page=1&pageSize=10

# Crear jugador sin club
POST /players
{
  "nombre": "Jugador",
  "apellidos": "Libre",
  "dorsal": 99,
  "salario": "50000"
}

# Asignar jugador a un club
PUT /players/1
{
  "id_club": "FCB"
}

# Quitar jugador del club
PUT /players/1
{
  "id_club": ""
}
```

### Respuestas JSON

#### Club (GET /clubs/1)
```json
{
  "club": {
    "id": 1,
    "id_club": "ATM",
    "nombre": "Atlético de Madrid",
    "fundacion": 1903,
    "ciudad": "Madrid",
    "estadio": "Wanda Metropolitano",
    "presupuesto": "400000000.00",
    "entrenador": ["Diego Simeone"],
    "jugadores": ["Antoine Griezmann", "Jan Oblak"]
  }
}
```

#### Lista de Clubs (GET /clubs)
```json
{
  "clubs": [
    {
      "id": 1,
      "id_club": "ATM",
      "nombre": "Atlético de Madrid",
      "fundacion": 1903,
      "ciudad": "Madrid",
      "estadio": "Wanda Metropolitano",
      "presupuesto": "400000000.00",
      "entrenador": "Diego Simeone",
      "jugadores": ["Antoine Griezmann", "Jan Oblak"]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total_items": 8,
    "total_pages": 1,
    "has_next_page": false,
    "has_prev_page": false,
    "next_page": null,
    "prev_page": null
  }
}
```

#### Lista de Entrenadores (GET /coaches)
```json
{
  "coaches": [
    {
      "id": 1,
      "dni": "11223344C",
      "nombre": "Diego",
      "apellidos": "Simeone",
      "salario": "30000000",
      "club": "Atlético de Madrid"
    },
    {
      "id": 2,
      "dni": "12345678A",
      "nombre": "Carlo",
      "apellidos": "Ancelotti",
      "salario": "15000000",
      "club": "Real Madrid"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total_items": 7,
    "total_pages": 1,
    "has_next_page": false,
    "has_prev_page": false,
    "next_page": null,
    "prev_page": null
  }
}
```

# Testing

El proyecto incluye una suite completa de tests unitarios, funcionales e integración para asegurar la calidad y funcionamiento correcto de la aplicación.

## Configuración de Tests

### Paso 1: Crear Base de Datos de Test
Los tests utilizan una base de datos separada (`futbol_test`) para no interferir con los datos de desarrollo/producción.

**Opción A - Con usuario root:**
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS futbol_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON futbol_test.* TO 'futboluser'@'localhost'; FLUSH PRIVILEGES;"
```

**Opción B - Con usuario con privilegios:**
```sql
CREATE DATABASE IF NOT EXISTS futbol_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON futbol_test.* TO 'futboluser'@'localhost';
FLUSH PRIVILEGES;
```

### Paso 2: Configurar Variables de Entorno
El archivo `config/packages/test/doctrine.yaml` debe apuntar a la base de datos de test:

```yaml
doctrine:
    dbal:
        url: 'mysql://futboluser:Futbol_2025@127.0.0.1:3306/futbol_test?serverVersion=8.0.32&charset=utf8mb4'
```

### Paso 3: Crear el Esquema de Base de Datos de Test
```bash
# Crear todas las tablas en la base de datos de test
php bin/console doctrine:schema:create --env=test

# O usar migraciones
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## Ejecutar Tests

### Ejecutar todos los tests
```bash
php bin/phpunit
```

### Ejecutar tests con más información
```bash
# Con formato detallado
php bin/phpunit --testdox

# Con cobertura de código (requiere Xdebug)
php bin/phpunit --coverage-html coverage/
```

### Ejecutar tests específicos
```bash
# Tests de controladores
php bin/phpunit tests/Controller/

# Tests de un controlador específico
php bin/phpunit tests/Controller/PlayerControllerTest.php
php bin/phpunit tests/Controller/ClubControllerTest.php
php bin/phpunit tests/Controller/CoachControllerTest.php
php bin/phpunit tests/Controller/UserControllerTest.php

# Tests funcionales
php bin/phpunit tests/Functional/

# Tests de integración
php bin/phpunit tests/Integration/

# Test específico por nombre
php bin/phpunit --filter testCreateClub
php bin/phpunit --filter testCreatePlayer
```

## Estructura de Tests

### Tests de Controladores
- `ClubControllerTest.php` - Tests para endpoints de clubs
- `PlayerControllerTest.php` - Tests para endpoints de jugadores  
- `CoachControllerTest.php` - Tests para endpoints de entrenadores
- `UserControllerTest.php` - Tests para registro de usuarios

### Tests Funcionales
- `BudgetValidationTest.php` - Tests de validación de presupuestos

### Tests de Integración
- `DatabaseIntegrationTest.php` - Tests de integración con la base de datos

## Solución de Problemas Comunes

### Error: "Access denied for user to database futbol_test"
**Causa:** El usuario no tiene permisos en la base de datos de test.

**Solución:**
```bash
mysql -u root -p -e "GRANT ALL PRIVILEGES ON futbol_test.* TO 'futboluser'@'localhost'; FLUSH PRIVILEGES;"
```

### Error: "Table doesn't exist"
**Causa:** El esquema de la base de datos de test no está creado.

**Solución:**
```bash
php bin/console doctrine:schema:create --env=test
```

### Limpiar base de datos de test entre ejecuciones
```bash
# Eliminar y recrear el esquema
php bin/console doctrine:schema:drop --env=test --force
php bin/console doctrine:schema:create --env=test
```



