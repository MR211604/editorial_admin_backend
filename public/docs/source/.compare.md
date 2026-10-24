---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)

<!-- END_INFO -->

#Auth

Endpoints de autenticación (login, renovación de token y logout).
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_a925a8d22b3615f12fca79456d286859 -->
## Iniciar sesión

Autentica a un usuario con sus credenciales y devuelve un token JWT.
El token debe ser incluido en el header Authorization como Bearer token para las siguientes peticiones.

**Parámetros del Body:**
- `token` (string, requerido): El token único del usuario (username). Ejemplo: `admin123`
- `password` (string, requerido): La contraseña del usuario. Ejemplo: `password123`

```json
{
  "token": "admin123",
  "password": "mySecurePassword123"
}
```

> Example request:

```bash
curl -X POST "http://localhost/api/auth/login" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/login",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Usuario conectado correctamente",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvZWRpdG9yaWFsL2FwaS9wdWJsaWMvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NzU1MTUyNzAsImV4cCI6MTc3NTUxODg3MCwibmJmIjoxNzc1NTE1MjcwLCJqdGkiOiI4SkY2bUdtdlh5UU1nR2k5Iiwic3ViIjozLCJwcnYiOiI4N2UwYWYxZWY5ZmQxNTgxMmZkZWM5NzE1M2ExNGUwYjA0NzU0NmFhIn0.jgkC__hycWzG29nRfgzGq7I5CeOmhCY0wqkXcDhY05Y",
    "user": {
        "idUser": 3,
        "name": "admin",
        "active": 1,
        "join_date": "2025-07-06 19:48:15",
        "last_login_date": "2026-04-06 16:41:10",
        "token": "admin",
        "user_roles": [
            {
                "idRol": 2,
                "name": "Administrador",
                "description": "Control total dentro de la aplicacion",
                "pivot": {
                    "user_id": 3,
                    "rol_id": 2
                }
            }
        ]
    }
}
```

### HTTP Request
`POST api/auth/login`


<!-- END_a925a8d22b3615f12fca79456d286859 -->

<!-- START_a72455bc9968f52ac7e1d8518d843eb6 -->
## Renovar token

Renueva un token JWT existente antes de que expire completamente.
El usuario debe estar autenticado con un token válido para poder renovarlo.

**Autenticación requerida:**
Este endpoint requiere que incluyas tu token JWT actual en el header de autorización.

**Header requerido:**
- `Authorization`: Bearer {tu_token_actual}

```bash
curl -X GET "http://localhost/api/auth/renew" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/auth/renew" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/renew",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Token renovado correctamente",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MFwvYXBpXC9hdXRoXC9yZW5ldyIsImlhdCI6MTczMTY5NjAwMCwiZXhwIjoxNzMxNjk5NjAwLCJuYmYiOjE3MzE2OTYwMDAsImp0aSI6ImFiY2RlZjEyMzQ1IiwidXNlcl9pZCI6MX0",
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "token": "admin123",
        "active": 1
    }
}
```

### HTTP Request
`GET api/auth/renew`


<!-- END_a72455bc9968f52ac7e1d8518d843eb6 -->

<!-- START_19ff1b6f8ce19d3c444e9b518e8f7160 -->
## Cerrar sesión

Invalida el token JWT actual del usuario, cerrando su sesión.
Después de cerrar sesión, el token ya no será válido para futuras peticiones.

**Autenticación requerida:**
Este endpoint requiere que incluyas tu token JWT actual en el header de autorización.

**Header requerido:**
- `Authorization`: Bearer {tu_token_actual}

```bash
curl -X POST "http://localhost/api/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota:** Una vez que cierres sesión, deberás volver a iniciar sesión para obtener un nuevo token.

> Example request:

```bash
curl -X POST "http://localhost/api/auth/logout" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/logout",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "message": "Usuario desconectado correctamente"
}
```

### HTTP Request
`POST api/auth/logout`


<!-- END_19ff1b6f8ce19d3c444e9b518e8f7160 -->

#Authors

Endpoints para la gestión de autores.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_b9db63505163eb92403717250ed1745e -->
## Listar autores

Obtiene una lista paginada de autores con opciones de búsqueda y ordenamiento.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
- `search` (string, opcional): Búsqueda por nombre o apellido. Ejemplo: `García`
- `sort` (string, opcional): Campo para ordenar (id, name, lastname). Ejemplo: `name:asc`

```bash
curl -X GET "http://localhost/api/authors/getAuthors?page=1&limit=10&search=García" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/authors/getAuthors" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/getAuthors",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Autores obtenidos exitosamente",
    "data": [
        {
            "id": 1,
            "name": "Juan",
            "lastname": "García",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        },
        {
            "id": 2,
            "name": "María",
            "lastname": "López",
            "created_at": "2025-11-15 11:00:00",
            "updated_at": "2025-11-15 11:00:00"
        }
    ],
    "pagination": {
        "total": 50,
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "from": 1,
        "to": 10
    }
}
```

### HTTP Request
`GET api/authors/getAuthors`


<!-- END_b9db63505163eb92403717250ed1745e -->

<!-- START_61f6ceba7c1c20445e8c048a58341d74 -->
## Obtener un autor

Obtiene la información detallada de un autor específico por su ID.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del autor a consultar. Ejemplo: `1`

```bash
curl -X GET "http://localhost/api/authors/getAuthor/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/authors/getAuthor/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/getAuthor/{id?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Autor obtenido exitosamente",
    "data": {
        "id": 1,
        "name": "Juan",
        "lastname": "García",
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00"
    }
}
```

### HTTP Request
`GET api/authors/getAuthor/{id?}`


<!-- END_61f6ceba7c1c20445e8c048a58341d74 -->

<!-- START_4b1b0caf286ea71c299b0389a6525a2c -->
## Crear un autor

Crea un nuevo autor en el sistema.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros del Body:**
- `name` (string, requerido): Nombre del autor (máximo 255 caracteres). Ejemplo: `Juan`
- `lastname` (string, requerido): Apellido del autor (máximo 255 caracteres). Ejemplo: `García`

```json
{
  "name": "Juan",
  "lastname": "García"
}
```

```bash
curl -X POST "http://localhost/api/authors/createAuthor" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"name":"Juan","lastname":"García"}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/authors/createAuthor" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/createAuthor",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`POST api/authors/createAuthor`


<!-- END_4b1b0caf286ea71c299b0389a6525a2c -->

<!-- START_5b035c2aca4a9dc77187e2e7f08080f8 -->
## Actualizar un autor

Actualiza la información de un autor existente.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del autor a actualizar. Ejemplo: `1`

**Parámetros del Body:**
- `name` (string, requerido): Nuevo nombre del autor (máximo 255 caracteres). Ejemplo: `Juan Carlos`
- `lastname` (string, requerido): Nuevo apellido del autor (máximo 255 caracteres). Ejemplo: `García Pérez`

```json
{
  "name": "Juan Carlos",
  "lastname": "García Pérez"
}
```

```bash
curl -X POST "http://localhost/api/authors/updateAuthor/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"name":"Juan Carlos","lastname":"García Pérez"}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/authors/updateAuthor/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/updateAuthor/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Autor actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Carlos",
        "lastname": "García Pérez",
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 12:00:00"
    }
}
```

### HTTP Request
`POST api/authors/updateAuthor/{id?}`


<!-- END_5b035c2aca4a9dc77187e2e7f08080f8 -->

<!-- START_e1321a8b15fdbe094d92f98e3e9e5c4d -->
## Eliminar un autor

Elimina un autor del sistema. También elimina las relaciones del autor con las publicaciones.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del autor a eliminar. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/authors/deleteAuthor/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota:** Esta operación eliminará el autor y todas sus relaciones con publicaciones, pero no eliminará las publicaciones mismas.

> Example request:

```bash
curl -X POST "http://localhost/api/authors/deleteAuthor/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/deleteAuthor/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Autor eliminado exitosamente",
    "data": null
}
```

### HTTP Request
`POST api/authors/deleteAuthor/{id?}`


<!-- END_e1321a8b15fdbe094d92f98e3e9e5c4d -->

#Banners

Endpoints para la gestión de banners.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_ea57fc4cbab9bc046c292cf8f114056e -->
## Listar banners

Obtiene una lista paginada de banners con opciones de búsqueda y filtrado.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 1). Por defecto: 10. Ejemplo: `10`
- `search` (string, opcional): Búsqueda por título. Ejemplo: `Promoción`
- `state` (integer, opcional): Filtrar por estado (0=Inactivo, 1=Activo). Ejemplo: `1`
- `sort` (string, opcional): Campo para ordenar (idBanner, title, state). Ejemplo: `idBanner:desc`

```bash
curl -X GET "http://localhost/api/banners/getBanners?page=1&limit=10&state=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/banners/getBanners" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/getBanners",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicaciones obtenidas correctamente",
    "data": [
        {
            "idBanner": 1,
            "title": "Promoción 2025",
            "resourceURL": "https:\/\/www.youtube.com\/watch?v=dQw4w9WgXcQ",
            "state": 1,
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        }
    ],
    "pagination": {
        "total": 5,
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "from": 1,
        "to": 5
    }
}
```

### HTTP Request
`GET api/banners/getBanners`


<!-- END_ea57fc4cbab9bc046c292cf8f114056e -->

<!-- START_bf584b702121e8b0d9da94badacfe133 -->
## Obtener el único video activo

Retorna el video que se encuentra activo. Las operaciones de escritura mantienen un único video activo.

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

```bash
curl -X GET "http://localhost/api/banners/getLastActiveBanner" \
  -H "Accept: application/json"
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/banners/getActiveBanner" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/getActiveBanner",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Operación exitosa",
    "data": {
        "idBanner": 5,
        "title": "Gran Promoción",
        "resourceURL": "https:\/\/www.youtube.com\/watch?v=dQw4w9WgXcQ",
        "state": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00"
    }
}
```

### HTTP Request
`GET api/banners/getActiveBanner`


<!-- END_bf584b702121e8b0d9da94badacfe133 -->

<!-- START_d0ea90067e2cd05ba7a52b2fee8957c7 -->
## Compatibilidad con clientes que todavía consumen el endpoint anterior.

> Example request:

```bash
curl -X GET -G "http://localhost/api/banners/getLastActiveBanner" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/getLastActiveBanner",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener los banner",
    "message": "Error al obtener los banner"
}
```

### HTTP Request
`GET api/banners/getLastActiveBanner`


<!-- END_d0ea90067e2cd05ba7a52b2fee8957c7 -->

<!-- START_5f83d81e469a9707d89d7b83ccaef869 -->
## Crear un banner

Crea un nuevo banner en el sistema.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros del Body:**
- `title` (string, requerido): Título del banner (máximo 255 caracteres). Ejemplo: `Promoción 2025`
- `resourceURL` (string, requerido): URL única del video de YouTube, TikTok, Facebook o Instagram (máximo 255 caracteres).
- `state` (boolean, requerido): Estado del banner (0=Inactivo, 1=Activo). Ejemplo: `1`

```json
{
  "title": "Promoción 2025",
  "resourceURL": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "state": 1
}
```

```bash
curl -X POST "http://localhost/api/banners/createBanner" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"title":"Video editorial","resourceURL":"https://www.youtube.com/watch?v=dQw4w9WgXcQ","state":1}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/banners/createBanner" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/createBanner",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Banner creado correctamente",
    "data": {
        "idBanner": 1,
        "title": "Promoción 2025",
        "resourceURL": "https:\/\/www.youtube.com\/watch?v=dQw4w9WgXcQ",
        "state": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00"
    }
}
```

### HTTP Request
`POST api/banners/createBanner`


<!-- END_5f83d81e469a9707d89d7b83ccaef869 -->

<!-- START_40b11e520c8a7e6a4b8a9bc35b2438d6 -->
## Actualizar un banner

Actualiza la información de un banner existente.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del banner a actualizar. Ejemplo: `1`

**Parámetros del Body (JSON o Form Data):**
- `title` (string, requerido): Título del banner (máximo 255 caracteres). Ejemplo: `Promoción Actualizada 2025`
- `resourceURL` (string, requerido): URL única del video de YouTube, TikTok, Facebook o Instagram (máximo 255 caracteres).
- `state` (boolean, requerido): Estado del banner (0=Inactivo, 1=Activo). Ejemplo: `0`

```json
{
  "title": "Promoción Actualizada 2025",
  "resourceURL": "https://www.instagram.com/reel/C0UDBvideo/",
  "state": 0
}
```

```bash
curl -X POST "http://localhost/api/banners/updateBanner/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"title":"Video editorial actualizado","resourceURL":"https://www.instagram.com/reel/C0UDBvideo/","state":0}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/banners/updateBanner/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/updateBanner/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Banner actualizado correctamente",
    "data": {
        "idBanner": 1,
        "title": "Promoción Actualizada 2025",
        "resourceURL": "https:\/\/www.instagram.com\/reel\/C0UDBvideo\/",
        "state": 0,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 11:30:00"
    }
}
```

### HTTP Request
`POST api/banners/updateBanner/{id?}`


<!-- END_40b11e520c8a7e6a4b8a9bc35b2438d6 -->

<!-- START_67fcb4da59fbb669316b7c1783a6115a -->
## Alternar estado de un banner

Habilita o deshabilita un banner específico.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del banner a alternar. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/banners/toggleState/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X POST "http://localhost/api/banners/toggleState/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/banners/toggleState/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Estado del banner actualizado correctamente",
    "data": {
        "idBanner": 1,
        "title": "Promoción Actualizada 2025",
        "resourceURL": "https:\/\/example.com\/banner1_mod.jpg",
        "state": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 12:30:00"
    }
}
```

### HTTP Request
`POST api/banners/toggleState/{id?}`


<!-- END_67fcb4da59fbb669316b7c1783a6115a -->

#Categories

Endpoints para la gestión de categorías y subcategorías del sistema editorial.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_e025a908a743d8d7e8b0c3c27ddc9046 -->
## Listar categorías

Obtiene una lista de categorías. Por defecto obtiene solo las categorías padre con sus hijas recursivamente.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `getAll` (boolean, opcional): Si es `true`, obtiene todas las categorías sin estructura jerárquica.
  Si es `false` u omitido, obtiene solo categorías padre con sus subcategorías anidadas.
  Ejemplo: `1`, `true`, `false`

> Example request:

```bash
curl -X GET -G "http://localhost/api/categories/getCategories" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/getCategories",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categorías obtenidas correctamente",
    "data": [
        {
            "idCategory": 1,
            "title": "Ciencias",
            "description": "Categoría de ciencias",
            "category_type": 1,
            "URL": "ciencias",
            "state": 1,
            "children_recursive": [
                {
                    "idCategory": 2,
                    "title": "Física",
                    "description": "Subcategoría de física",
                    "category_type": 1,
                    "state": 1,
                    "main_category": 1,
                    "children_recursive": []
                },
                {
                    "idCategory": 3,
                    "title": "Química",
                    "description": "Subcategoría de química",
                    "category_type": 1,
                    "state": 1,
                    "main_category": 1,
                    "children_recursive": []
                }
            ]
        }
    ]
}
```

### HTTP Request
`GET api/categories/getCategories`


<!-- END_e025a908a743d8d7e8b0c3c27ddc9046 -->

<!-- START_b69b9dfb42050f0036c4a5ec9f9d6a6b -->
## Obtener una categoría

Obtiene la información detallada de una categoría específica por su ID, incluyendo sus detalles adicionales.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

> Example request:

```bash
curl -X GET -G "http://localhost/api/categories/getCategory/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/getCategory/{id?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categoría obtenida correctamente",
    "data": {
        "idCategory": 1,
        "title": "Ciencias",
        "description": "Categoría de ciencias",
        "main_category": null,
        "state": 1,
        "URL": "ciencias",
        "is_featured": 0,
        "language_id": 1,
        "category_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00",
        "detail": {
            "idCategoryDetail": 1,
            "category_id": 1,
            "language_id": 1,
            "about": "Información sobre la categoría",
            "editorial_board": "Consejo editorial",
            "policy": "Políticas de publicación",
            "norms": "Normas de la categoría",
            "process": "Proceso de publicación",
            "contact": "info@example.com"
        }
    }
}
```

### HTTP Request
`GET api/categories/getCategory/{id?}`


<!-- END_b69b9dfb42050f0036c4a5ec9f9d6a6b -->

<!-- START_fe098ef3e869114d1d2d1cee9e13cbf2 -->
## Buscar una categoria por URL/slug

Obtiene la información detallada de una categoria específica usando su URL/slug con todas sus relaciones.
Esta función es para uso público del frontend usando URLs amigables.

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

> Example request:

```bash
curl -X GET -G "http://localhost/api/categories/searchCategory/{categoryURL?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/searchCategory/{categoryURL?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categoría encontrada exitosamente",
    "data": {
        "idCategory": 1,
        "title": "Ciencias",
        "description": "Categoría de ciencias",
        "main_category": null,
        "state": 1,
        "URL": "ciencias",
        "is_featured": 0,
        "language_id": 1,
        "category_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00",
        "detail": {
            "idCategoryDetail": 1,
            "category_id": 1,
            "language_id": 1,
            "about": "Información sobre la categoría",
            "editorial_board": "Consejo editorial",
            "policy": "Políticas de publicación",
            "norms": "Normas de la categoría",
            "process": "Proceso de publicación",
            "contact": "info@example.com"
        }
    }
}
```

### HTTP Request
`GET api/categories/searchCategory/{categoryURL?}`


<!-- END_fe098ef3e869114d1d2d1cee9e13cbf2 -->

<!-- START_73cd004146fd92a9601b7943fa74d33c -->
## Crear una categoría

Crea una nueva categoría en el sistema. Por defecto se crea como categoría padre (sin categoría principal).

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

> Example request:

```bash
curl -X POST "http://localhost/api/categories/createCategory" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/createCategory",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categoría creada correctamente",
    "data": {
        "idCategory": 1,
        "title": "Ciencias",
        "description": "Categoría de ciencias exactas",
        "main_category": null,
        "URL": "ciencias",
        "state": 1,
        "is_featured": 0,
        "language_id": 1,
        "category_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00"
    }
}
```

### HTTP Request
`POST api/categories/createCategory`


<!-- END_73cd004146fd92a9601b7943fa74d33c -->

<!-- START_1263912fd1b0f393408f41322fb6c8fe -->
## Actualizar una categoría

Actualiza la información de una categoría existente, incluyendo sus detalles adicionales.
Permite convertir una categoría padre en subcategoría o viceversa.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

> Example request:

```bash
curl -X POST "http://localhost/api/categories/updateCategory/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/updateCategory/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categoría actualizada correctamente",
    "data": {
        "idCategory": 1,
        "title": "Ciencias Exactas",
        "URL": "ciencias_exactas",
        "description": "Categoría actualizada de ciencias",
        "main_category": null,
        "state": 1,
        "is_featured": 1,
        "language_id": 1,
        "category_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 12:00:00",
        "detail": {
            "idCategoryDetail": 1,
            "category_id": 1,
            "language_id": 1,
            "about": "Información sobre la categoría",
            "editorial_board": "Dr. Juan Pérez, Dra. María López",
            "policy": "Políticas de revisión por pares",
            "norms": "Normas APA 7",
            "process": "Proceso de revisión en 30 días",
            "contact": "ciencias@example.com"
        }
    }
}
```

### HTTP Request
`POST api/categories/updateCategory/{id?}`


<!-- END_1263912fd1b0f393408f41322fb6c8fe -->

<!-- START_4feae160b9ae5351c9386deab52228dc -->
## Activar/Desactivar categoría

Alterna el estado de una categoría (activa/inactiva).
Si es una categoría padre, también afecta recursivamente a todas sus subcategorías.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

> Example request:

```bash
curl -X POST "http://localhost/api/categories/toggleState/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/toggleState/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Categoría activada correctamente",
    "data": {
        "idCategory": 1,
        "title": "Ciencias",
        "description": "Categoría de ciencias",
        "main_category": null,
        "state": 1,
        "is_featured": 0,
        "URL": "ciencias",
        "language_id": 1,
        "category_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 12:30:00",
        "children_recursive": [
            {
                "idCategory": 2,
                "title": "Física",
                "state": 1,
                "main_category": 1
            }
        ]
    }
}
```

### HTTP Request
`POST api/categories/toggleState/{id?}`


<!-- END_4feae160b9ae5351c9386deab52228dc -->

<!-- START_7396e5e5dcabd33dc7f12c1ee5cd2242 -->
## Subir imagen de categoría

Sube una imagen para el contenido de la categoría (detalles/cuerpo).

> Example request:

```bash
curl -X POST "http://localhost/api/categories/uploadCategoryImage" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categories/uploadCategoryImage",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Imagen subida correctamente",
    "data": {
        "filename": "ciencias_123456_uniqid.jpg"
    }
}
```

### HTTP Request
`POST api/categories/uploadCategoryImage`


<!-- END_7396e5e5dcabd33dc7f12c1ee5cd2242 -->

#Category Types

Endpoints para la gestión de tipos de categorías.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_658bb2bbcd93a39102798b891f308fdc -->
## Listar tipos de categoría

Obtiene todos los tipos de categoría disponibles en el sistema.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página (mínimo 1). Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 10). Ejemplo: `10`

```bash
curl -X GET "http://localhost/api/categoryTypes/getCategoryTypes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota:** Actualmente devuelve todos los tipos sin paginación.
Si se requiere paginación en el futuro, revisar el código comentado en el controlador.

> Example request:

```bash
curl -X GET -G "http://localhost/api/categoryTypes/getCategoryTypes" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/categoryTypes/getCategoryTypes",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Tipos de categoría obtenidos exitosamente",
    "data": [
        {
            "idCategoryType": 1,
            "name": "Revista",
            "description": "Publicaciones periódicas",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        },
        {
            "idCategoryType": 2,
            "name": "Libro",
            "description": "Publicaciones en formato libro",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        }
    ]
}
```

### HTTP Request
`GET api/categoryTypes/getCategoryTypes`


<!-- END_658bb2bbcd93a39102798b891f308fdc -->

#Emails

Endpoints para la gestión de correos y mensajes de contacto recibidos.
Usa JWT en el header: `Authorization: Bearer {token}` (solo para listado).
<!-- START_3ed9d9d3f8a472b4597b2c9c89f6603f -->
## Listar correos

Obtiene una lista paginada de correos y mensajes de contacto con opciones de búsqueda y ordenamiento.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
- `search` (string, opcional): Búsqueda por nombre, correo electrónico o asunto. Ejemplo: `Consulta`
- `sort` (string, opcional): Campo para ordenar (idUserEmail, name, email, subject, message). Ejemplo: `idUserEmail:desc`

```bash
curl -X GET "http://localhost/api/emails/getEmails?page=1&limit=10&search=Consulta" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/emails/getEmails" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/emails/getEmails",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Correos obtenidos exitosamente",
    "data": [
        {
            "idUserEmail": 1,
            "name": "Juan Pérez",
            "subject": "Consulta sobre publicaciones",
            "email": "juan.perez@example.com",
            "message": "Me gustaría saber más sobre los procesos de publicación.",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        }
    ],
    "pagination": {
        "total": 50,
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "from": 1,
        "to": 10
    }
}
```

### HTTP Request
`GET api/emails/getEmails`


<!-- END_3ed9d9d3f8a472b4597b2c9c89f6603f -->

<!-- START_249b2dd1bae35f8413b5c10084efdf78 -->
## Guardar correo

Guarda un nuevo mensaje de contacto enviado desde el formulario público.
Este endpoint tiene un límite de peticiones (throttle: 5 peticiones por minuto por IP) para evitar spam.

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

**Parámetros del Body:**
- `name` (string, requerido): Nombre del remitente (máximo 255 caracteres). Ejemplo: `Carlos López`
- `subject` (string, requerido): Asunto del mensaje (máximo 255 caracteres). Ejemplo: `Duda sobre compra`
- `email` (string, requerido): Dirección de correo válida (máximo 255 caracteres). Ejemplo: `carlos@example.com`
- `message` (string, opcional): Cuerpo del mensaje. Ejemplo: `Quiero comprar un libro pero tengo problemas con mi tarjeta...`

```json
{
  "name": "Carlos López",
  "subject": "Duda sobre compra",
  "email": "carlos@example.com",
  "message": "Quiero comprar un libro pero tengo problemas con mi tarjeta..."
}
```

```bash
curl -X POST "http://localhost/api/emails/saveEmail" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Carlos López","subject":"Duda sobre compra","email":"carlos@example.com","message":"Texto del mensaje"}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/emails/saveEmail" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/emails/saveEmail",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`POST api/emails/saveEmail`


<!-- END_249b2dd1bae35f8413b5c10084efdf78 -->

#PublishingDetail

Endpoints para la gestión de detalles de publicaciones (revistas).
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_3baa66247916c6f29706b1dba1d0f3b6 -->
## Crear detalle de publicación

Crea un artículo o detalle de una publicación (revista) en un idioma específico.
Cada publicación puede tener múltiples detalles, uno por cada idioma disponible.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros del Body:**
- `title` (string, requerido): Título del artículo/detalle. Ejemplo: `Avances en Inteligencia Artificial`
- `summary` (string, opcional): Resumen del artículo. Ejemplo: `Este artículo explora los últimos avances...`
- `URL` (string, opcional): URL del artículo completo. Ejemplo: `https://revista.udb.edu.sv/articulo/123`
- `repository_URL` (string, opcional): URL del repositorio académico. Ejemplo: `https://repositorio.udb.edu.sv/123`
- `language_id` (integer, requerido): ID del idioma (debe existir en tabla languages). Ejemplo: `1`
- `publishing_id` (integer, requerido): ID de la publicación padre (debe existir en tabla publishings). Ejemplo: `5`

```json
{
  "title": "Avances en Inteligencia Artificial",
  "summary": "Este artículo explora los últimos avances en IA aplicados a la educación superior",
  "URL": "https://revista.udb.edu.sv/articulo/123",
  "repository_URL": "https://repositorio.udb.edu.sv/123",
  "language_id": 1,
  "publishing_id": 5
}
```

```bash
curl -X POST "http://localhost/api/publishingDetails/createPublishingDetail" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{
    "title":"Avances en IA",
    "summary":"Resumen del artículo",
    "URL":"https://revista.udb.edu.sv/articulo/123",
    "language_id":1,
    "publishing_id":5
  }'
```

**Nota importante:**
- La información NO se comparte entre idiomas, se debe crear un detalle por cada idioma
- Los campos `URL` y `repository_URL` son opcionales
- El `publishing_id` debe corresponder a una publicación de tipo revista existente

> Example request:

```bash
curl -X POST "http://localhost/api/publishingDetails/createPublishingDetail" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishingDetails/createPublishingDetail",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`POST api/publishingDetails/createPublishingDetail`


<!-- END_3baa66247916c6f29706b1dba1d0f3b6 -->

<!-- START_a802abe40b0b7535aa3b715bd30146f7 -->
## Actualizar detalle de publicación

Actualiza la información de un artículo o detalle de publicación existente.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del detalle de publicación a actualizar. Ejemplo: `1`

**Parámetros del Body:**
- `title` (string, requerido): Nuevo título del artículo. Ejemplo: `Inteligencia Artificial en la Educación`
- `summary` (string, opcional): Nuevo resumen del artículo. Ejemplo: `Resumen actualizado del contenido...`
- `URL` (string, opcional): Nueva URL del artículo completo. Ejemplo: `https://revista.udb.edu.sv/articulo/123-v2`
- `repository_URL` (string, opcional): Nueva URL del repositorio. Ejemplo: `https://repositorio.udb.edu.sv/123-v2`
- `publishing_id` (integer, requerido): ID de la publicación padre (debe existir en tabla publishings). Ejemplo: `5`

```json
{
  "title": "Inteligencia Artificial en la Educación Superior",
  "summary": "Resumen actualizado con información adicional sobre IA en educación",
  "URL": "https://revista.udb.edu.sv/articulo/123-v2",
  "repository_URL": "https://repositorio.udb.edu.sv/123-v2",
  "publishing_id": 5
}
```

```bash
curl -X POST "http://localhost/api/publishingDetails/updatePublishingDetail/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{
    "title":"IA en Educación Superior",
    "summary":"Resumen actualizado",
    "URL":"https://revista.udb.edu.sv/articulo/123-v2",
    "publishing_id":5
  }'
```

**Nota:** El idioma (`language_id`) no se puede cambiar. Para cambiar el idioma, crea un nuevo detalle.

> Example request:

```bash
curl -X POST "http://localhost/api/publishingDetails/updatePublishingDetail/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishingDetails/updatePublishingDetail/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Success",
    "data": {
        "id": 1,
        "title": "Inteligencia Artificial en la Educación Superior",
        "summary": "Resumen actualizado con información adicional sobre IA en educación",
        "URL": "https:\/\/revista.udb.edu.sv\/articulo\/123-v2",
        "repository_URL": "https:\/\/repositorio.udb.edu.sv\/123-v2",
        "language_id": 1,
        "publishing_id": 5,
        "created_at": "2025-11-17 10:00:00",
        "updated_at": "2025-11-17 12:30:00"
    }
}
```

### HTTP Request
`POST api/publishingDetails/updatePublishingDetail/{id?}`


<!-- END_a802abe40b0b7535aa3b715bd30146f7 -->

<!-- START_a9da9d3b2714b3d6a886603796446c9c -->
## Eliminar detalle de publicación

Elimina un artículo o detalle de publicación del sistema.
Esta acción es permanente y no se puede deshacer.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del detalle de publicación a eliminar. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/publishingDetails/deletePublishingDetail/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota importante:**
- Esta operación es irreversible
- Solo elimina el detalle en un idioma específico, no la publicación completa
- Para eliminar todos los detalles de una publicación, debe eliminar cada uno individualmente

> Example request:

```bash
curl -X POST "http://localhost/api/publishingDetails/deletePublishingDetail/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishingDetails/deletePublishingDetail/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Detalle de publicación eliminado exitosamente",
    "data": null
}
```

### HTTP Request
`POST api/publishingDetails/deletePublishingDetail/{id?}`


<!-- END_a9da9d3b2714b3d6a886603796446c9c -->

#Publishings

Endpoints para la gestión de publicaciones (libros y revistas).
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_f32197e71cb96cfd48b93667b5eedf8c -->
## Listar publicaciones

Obtiene una lista paginada de publicaciones (libros y revistas) con opciones de filtrado, búsqueda y ordenamiento.

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 1). Por defecto: 10. Ejemplo: `10`
- `publishing_type` (string, opcional): Nombre del tipo de publicación (Libro, Revista). Ejemplo: `Libro`
- `language_id` (integer, opcional): ID del idioma para las traducciones. Por defecto: 1. Ejemplo: `1`
- `search` (string, opcional): Búsqueda por título, código ISBN o nombre/apellido del autor. Ejemplo: `Laravel`
- `category` (integer, opcional): Filtrar por ID de categoría (incluye sus subcategorías). Ejemplo: `3`
- `is_archived` (integer, opcional): Filtrar por estado (0=Activo, 1=Archivado). Ejemplo: `0`
- `sort` (string, opcional): Campo para ordenar (id, title, created_at, updated_at). Ejemplo: `title:asc`

```bash
curl -X GET "http://localhost/api/publishings/getPublishings?page=1&limit=10&publishing_type=Libro&search=Laravel" \
  -H "Accept: application/json"
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/publishings/getPublishings" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/getPublishings",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicaciones obtenidas correctamente",
    "data": [
        {
            "idPublishing": 1,
            "title": "Laravel para Principiantes",
            "cover": "editorial_files\/libros\/programacion\/laravel_principiantes\/1234567890.jpg",
            "pages": 320,
            "publish_date": "2025-01-15",
            "publish_code": "978-84-9876-123-4",
            "category_id": 3,
            "publishing_type": 1,
            "is_new_arrival": 1,
            "state": 1,
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00",
            "type": {
                "idPublishingType": 1,
                "name": "Libro"
            },
            "authors": [
                {
                    "idAuthor": 1,
                    "name": "Juan",
                    "lastname": "García"
                }
            ],
            "details": [],
            "translation": {
                "idPublishingTranslation": 1,
                "title": "Laravel para Principiantes",
                "URL": "laravel-para-principiantes",
                "language_id": 1
            }
        }
    ],
    "pagination": {
        "total": 50,
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "from": 1,
        "to": 10
    }
}
```

### HTTP Request
`GET api/publishings/getPublishings`


<!-- END_f32197e71cb96cfd48b93667b5eedf8c -->

<!-- START_ad89b3f28c1bdbc089c08760b57e4f29 -->
## Obtener una publicación por ID

Obtiene la información detallada de una publicación específica con todas sus relaciones (tipo, autores, detalles, traducciones).
Esta función es para uso interno del CMS usando ID numérico.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID de la publicación a consultar. Ejemplo: `1`

```bash
curl -X GET "http://localhost/api/publishings/getPublishing/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/publishings/getPublishing/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/getPublishing/{id?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicación obtenida exitosamente",
    "data": {
        "idPublishing": 1,
        "title": "Laravel para Principiantes",
        "cover": "editorial_files\/libros\/programacion\/laravel_principiantes\/1234567890.jpg",
        "pages": 320,
        "publish_date": "2025-01-15",
        "publish_code": "978-84-9876-123-4",
        "category_id": 3,
        "publishing_type": 1,
        "is_new_arrival": 1,
        "state": 1,
        "type": {
            "idPublishingType": 1,
            "name": "Libro"
        },
        "authors": [
            {
                "idAuthor": 1,
                "name": "Juan",
                "lastname": "García"
            }
        ],
        "translations": [
            {
                "idPublishingTranslation": 1,
                "title": "Laravel para Principiantes",
                "URL": "laravel-para-principiantes",
                "language": {
                    "idLanguage": 1,
                    "name": "Español"
                }
            }
        ]
    }
}
```

### HTTP Request
`GET api/publishings/getPublishing/{id?}`


<!-- END_ad89b3f28c1bdbc089c08760b57e4f29 -->

<!-- START_d9b6a14dbcebbf5115083f6c8fd40b69 -->
## Buscar una publicación por URL/slug

Obtiene la información detallada de una publicación específica usando su URL/slug con todas sus relaciones.
Esta función es para uso público del frontend usando URLs amigables.
Solo retorna publicaciones activas (is_archived=0).

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

**Parámetros de ruta (URL Parameters):**
- `publishingURL` (string, requerido): URL/slug de la publicación a consultar. Ejemplo: `laravel-para-principiantes`

```bash
curl -X GET "http://localhost/api/publishings/searchByURL/laravel-para-principiantes" \
  -H "Accept: application/json"
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/publishings/searchPublishing/{publishingURL?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/searchPublishing/{publishingURL?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicación encontrada exitosamente",
    "data": {
        "idPublishing": 1,
        "title": "Laravel para Principiantes",
        "URL": "laravel-para-principiantes",
        "cover": "editorial_files\/libros\/programacion\/laravel_principiantes\/1234567890.jpg",
        "pages": 320,
        "publish_date": "2025-01-15",
        "publish_code": "978-84-9876-123-4",
        "category_id": 3,
        "publishing_type": 1,
        "is_new_arrival": 1,
        "state": 1,
        "type": {
            "idPublishingType": 1,
            "name": "Libro"
        },
        "authors": [
            {
                "idAuthor": 1,
                "name": "Juan",
                "lastname": "García"
            }
        ],
        "details": [],
        "translations": [
            {
                "idPublishingTranslation": 1,
                "title": "Laravel para Principiantes",
                "URL": "laravel-para-principiantes",
                "language": {
                    "idLanguage": 1,
                    "name": "Español"
                }
            }
        ]
    }
}
```

### HTTP Request
`GET api/publishings/searchPublishing/{publishingURL?}`


<!-- END_d9b6a14dbcebbf5115083f6c8fd40b69 -->

<!-- START_bd07c46bcd1ea465d2b325edab2a8edf -->
## Crear una publicación

Crea una nueva publicación (libro o revista) con sus traducciones en múltiples idiomas.
Esta es una operación inicial que crea la publicación base, luego debe actualizarse con más detalles.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros del Body (JSON):**
- `title` (string, requerido): Título principal de la publicación (máximo 255 caracteres, único). Ejemplo: `Laravel para Principiantes`
- `URL` (string, opcional): URL slug de la publicación. Ejemplo: `laravel-para-principiantes`
- `category_id` (integer, opcional): ID de la categoría. Ejemplo: `3`
- `publishing_type` (string, requerido): Nombre del tipo de publicación (Libro, Revista). Ejemplo: `Libro`
- `translations` (array, requerido): Array de traducciones (mínimo 1)
- `translations[].language_id` (integer, requerido): ID del idioma. Ejemplo: `1`
- `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres). Ejemplo: `Laravel for Beginners`
- `translations[].URL` (string, opcional): URL slug traducida. Ejemplo: `laravel-for-beginners`

```json
{
  "title": "Laravel para Principiantes",
  "URL": "laravel-para-principiantes",
  "category_id": 3,
  "publishing_type": "Libro",
  "translations": [
    {
      "language_id": 1,
      "title": "Laravel para Principiantes",
      "URL": "laravel-para-principiantes"
    },
    {
      "language_id": 2,
      "title": "Laravel for Beginners",
      "URL": "laravel-for-beginners"
    }
  ]
}
```

```bash
curl -X POST "http://localhost/api/publishings/createPublishing" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"title":"Laravel para Principiantes","publishing_type":"Libro","translations":[{"language_id":1,"title":"Laravel para Principiantes"}]}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/publishings/createPublishing" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/createPublishing",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicacion creada exitosamente",
    "data": {
        "idPublishing": 1,
        "title": "Laravel para Principiantes",
        "URL": "laravel-para-principiantes",
        "category_id": 3,
        "publishing_type": 1,
        "created_at": "2025-11-15 10:30:00",
        "updated_at": "2025-11-15 10:30:00"
    }
}
```

### HTTP Request
`POST api/publishings/createPublishing`


<!-- END_bd07c46bcd1ea465d2b325edab2a8edf -->

<!-- START_ef93a34472cbbf4adc14b6f2833ce65d -->
## Actualizar una publicación

Actualiza una publicación existente. Los campos requeridos varían según el tipo de publicación:
- **Libros (publishing_type=1)**: Requiere páginas, fecha de publicación, código ISBN
- **Revistas (publishing_type=2)**: Además de lo anterior, puede incluir año, número, volumen

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID de la publicación a actualizar. Ejemplo: `1`

**Parámetros del Body (para Libros - publishing_type=1):**
- `publishing_type` (integer, requerido): Debe ser 1 para libros
- `title` (string, opcional): Título de la publicación (único excepto para el registro actual)
- `pages` (integer, requerido): Número de páginas (mínimo 1). Ejemplo: `320`
- `publish_date` (date, requerido): Fecha de publicación. Ejemplo: `2025-01-15`
- `publish_code` (string, requerido): Código ISBN (formato: XXX-XX-XXXX-XXX-X). Ejemplo: `978-84-9876-123-4`
- `category_id` (integer, opcional): ID de categoría. Ejemplo: `3`
- `is_new_arrival` (boolean, opcional): ¿Es novedad? Ejemplo: `true`
- `URL` (string, opcional): URL slug de la publicación
- `resource_URL` (string, opcional): URL del recurso/documento
- `translations` (array, requerido): Array de traducciones
- `translations[].language_id` (integer, requerido): ID del idioma
- `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres)
- `authors` (array, opcional): Array de IDs de autores. Ejemplo: `[1, 2]`
- `cover` (file, opcional): Imagen de portada (JPEG, PNG)

**Parámetros del Body (para Revistas - publishing_type=2):**
- `publishing_type` (integer, requerido): Debe ser 2 para revistas
- `title` (string, opcional): Título de la publicación (único excepto para el registro actual)
- `pages` (integer, requerido): Número de páginas (mínimo 1)
- `publish_date` (date, requerido): Fecha de publicación
- `publish_code` (string, requerido): Código ISSN (formato: XXX-XX-XXXX-XXX-X)
- `age` (integer, opcional): Año de la revista (mínimo 1). Ejemplo: `2025`
- `age_number` (integer, opcional): Número dentro del año (mínimo 1). Ejemplo: `3`
- `volume` (integer, opcional): Volumen (mínimo 1). Ejemplo: `12`
- `URL` (string, opcional): URL slug de la publicación
- `is_new_arrival` (boolean, opcional): ¿Es novedad?
- `category_id` (integer, opcional): ID de categoría
- `translations` (array, requerido): Array de traducciones
- `translations[].language_id` (integer, requerido): ID del idioma
- `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres)
- `authors` (array, opcional): Array de IDs de autores
- `cover` (file, opcional): Imagen de portada

**Nota:** Si se actualiza la traducción en español (language_id=1), el título principal de la publicación también se actualiza automáticamente.

```bash
curl -X POST "http://localhost/api/publishings/updatePublishing/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -F "publishing_type=1" \
  -F "pages=320" \
  -F "publish_date=2025-01-15" \
  -F "publish_code=978-84-9876-123-4" \
  -F "category_id=3" \
  -F "is_new_arrival=1" \
  -F "translations[0][language_id]=1" \
  -F "translations[0][title]=Laravel para Principiantes" \
  -F "authors[]=1" \
  -F "authors[]=2" \
  -F "cover=@/path/to/cover.jpg"
```

> Example request:

```bash
curl -X POST "http://localhost/api/publishings/updatePublishing/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/updatePublishing/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicación actualizada exitosamente",
    "data": {
        "idPublishing": 1,
        "title": "Laravel para Principiantes",
        "cover": "editorial_files\/libros\/programacion\/laravel_principiantes\/1234567890.jpg",
        "pages": 320,
        "publish_date": "2025-01-15",
        "publish_code": "978-84-9876-123-4",
        "category_id": 3,
        "publishing_type": 1,
        "is_new_arrival": 1,
        "authors": [],
        "category": null,
        "type": {
            "idPublishingType": 1,
            "name": "Libro"
        },
        "details": []
    }
}
```

### HTTP Request
`POST api/publishings/updatePublishing/{id?}`


<!-- END_ef93a34472cbbf4adc14b6f2833ce65d -->

<!-- START_4f71d50134a0a4c178582dc2fc2aa3e5 -->
## Cambiar estado de archivo de una publicación

Alterna el estado de archivo de una publicación entre activo (0) y archivado (1).
Las publicaciones archivadas no aparecen en las búsquedas públicas pero pueden ser recuperadas.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID de la publicación. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/publishings/updateStatus/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota:** Este endpoint alterna el estado automáticamente:
- Si `is_archived=0` (activo) → cambia a `is_archived=1` (archivado) → mensaje: "Publicación reactivada exitosamente"
- Si `is_archived=1` (archivado) → cambia a `is_archived=0` (activo) → mensaje: "Publicación archivada exitosamente"

> Example request:

```bash
curl -X POST "http://localhost/api/publishings/updateStatus/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/updateStatus/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicación archivada exitosamente",
    "data": null
}
```

### HTTP Request
`POST api/publishings/updateStatus/{id?}`


<!-- END_4f71d50134a0a4c178582dc2fc2aa3e5 -->

<!-- START_2ebc19a9c467323e08aee4aeb2bceeef -->
## Eliminar una publicación

Elimina permanentemente una publicación del sistema junto con todas sus relaciones (autores, detalles, traducciones).
También elimina la imagen de portada del servidor si existe.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID de la publicación a eliminar. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/publishings/deletePublishing/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**IMPORTANTE:**
- La publicación DEBE estar archivada (is_archived=1) antes de poder eliminarla.
- Esta operación es IRREVERSIBLE y eliminará toda la información relacionada.
- Se eliminarán: autores asociados, detalles, traducciones y archivo de portada.

> Example request:

```bash
curl -X POST "http://localhost/api/publishings/deletePublishing/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/deletePublishing/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Publicación eliminada exitosamente",
    "data": null
}
```

### HTTP Request
`POST api/publishings/deletePublishing/{id?}`


<!-- END_2ebc19a9c467323e08aee4aeb2bceeef -->

<!-- START_5d4088a4940540ba2e40f3f61016e478 -->
## Resumen del dashboard

Obtiene un resumen estadístico con el conteo total de libros y revistas en el sistema.
Útil para mostrar en el dashboard o página principal.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

```bash
curl -X GET "http://localhost/api/publishings/summary" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/publishings/summary" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/summary",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Resumen del dashboard obtenido correctamente",
    "data": {
        "books": 125,
        "magazines": 87
    }
}
```

### HTTP Request
`GET api/publishings/summary`


<!-- END_5d4088a4940540ba2e40f3f61016e478 -->

<!-- START_d02233462a1778f8d4210a33836160cb -->
## Búsqueda global

Realiza una búsqueda global de libros por título, código ISBN o nombre/apellido del autor.
Acepta nombre completo del autor (cada palabra puede coincidir con el nombre o el apellido) y no distingue mayúsculas ni acentos.
Solo retorna libros activos (state=1).

**Autenticación requerida:**
Este endpoint NO requiere autenticación (público).

**Parámetros de consulta (Query Parameters):**
- `search` (string, requerido): Término de búsqueda por título, código ISBN o nombre del autor. Ejemplo: `Laravel`

```bash
curl -X GET "http://localhost/api/publishings/globalSearch?search=Laravel" \
  -H "Accept: application/json"
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/publishings/globalSearch" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/publishings/globalSearch",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Búsqueda global realizada exitosamente",
    "data": {
        "libros": [
            {
                "idPublishing": 1,
                "title": "Laravel para Principiantes",
                "URL": "laravel_para_principiantes",
                "resource_URL": "https:\/\/example.com\/laravel.pdf"
            }
        ]
    }
}
```

### HTTP Request
`GET api/publishings/globalSearch`


<!-- END_d02233462a1778f8d4210a33836160cb -->

#Roles

Endpoints para la gestión de roles de usuarios.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_d0505dab0b8dac386a10ea0f27445f01 -->
## Listar roles

Obtiene todos los roles disponibles en el sistema.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página (mínimo 1). Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 10). Ejemplo: `10`
- `search` (string, opcional): Buscar rol por nombre. Ejemplo: `Administrador`

```bash
curl -X GET "http://localhost/api/rol/getRol" \
  -H "Accept: application/json"
```

Si se requiere paginación en el futuro, revisar el código comentado en el controlador.
Este endpoint NO requiere autenticación actualmente.

> Example request:

```bash
curl -X GET -G "http://localhost/api/rol/getRol" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/rol/getRol",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Roles obtenidos exitosamente",
    "data": [
        {
            "id": 1,
            "name": "Administrador",
            "description": "Control total del sistema",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        },
        {
            "id": 2,
            "name": "Editor",
            "description": "Puede editar publicaciones",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        },
        {
            "id": 3,
            "name": "Revisor",
            "description": "Puede revisar contenido",
            "created_at": "2025-11-15 10:30:00",
            "updated_at": "2025-11-15 10:30:00"
        }
    ]
}
```

### HTTP Request
`GET api/rol/getRol`


<!-- END_d0505dab0b8dac386a10ea0f27445f01 -->

<!-- START_4e3c593528e7edbafc2f458f2cde4089 -->
## Crear un rol

Crea un nuevo rol en el sistema.

**Parámetros del Body:**
- `name` (string, requerido): Nombre del rol (máximo 255 caracteres, debe ser único). Ejemplo: `Moderador`
- `description` (string, opcional): Descripción del rol (máximo 500 caracteres). Ejemplo: `Puede moderar contenido`

```json
{
  "name": "Moderador",
  "description": "Puede moderar contenido y aprobar publicaciones"
}
```

```bash
curl -X POST "http://localhost/api/rol/createRol" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Moderador","description":"Puede moderar contenido"}'
```

> Example request:

```bash
curl -X POST "http://localhost/api/rol/createRol" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/rol/createRol",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`POST api/rol/createRol`


<!-- END_4e3c593528e7edbafc2f458f2cde4089 -->

#Users

Endpoints para la gestión de usuarios del sistema.
Usa JWT en el header: `Authorization: Bearer {token}`.
<!-- START_61b568955dfc112d52401a1ac60333a2 -->
## Listar usuarios

Obtiene una lista paginada de usuarios con sus roles asociados.
Permite filtrar por rol y buscar por nombre.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de consulta (Query Parameters):**
- `page` (integer, opcional): Número de página (mínimo 1). Por defecto: 1. Ejemplo: `1`
- `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
- `rol_id` (integer, opcional): Filtrar usuarios por rol. Ejemplo: `1`
- `search` (string, opcional): Buscar usuarios por nombre. Ejemplo: `Juan`
- `sort` (string, opcional): Ordenar por campo (id, name, token). Ejemplo: `name:asc`

```bash
# Listar todos los usuarios
curl -X GET "http://localhost/api/users/getUsers?page=1&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."

# Filtrar por rol y buscar por nombre
curl -X GET "http://localhost/api/users/getUsers?rol_id=1&search=Juan" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/getUsers" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/users/getUsers",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Usuarios obtenidos exitosamente",
    "data": [
        {
            "id": 1,
            "name": "Juan Pérez",
            "token": "juanperez",
            "rol_id": 1,
            "active": 1,
            "join_date": "2025-01-15 10:00:00",
            "last_login_date": "2025-11-15 09:30:00",
            "created_at": "2025-01-15 10:00:00",
            "updated_at": "2025-11-15 09:30:00",
            "rol": {
                "id": 1,
                "name": "Administrador",
                "description": "Control total del sistema"
            }
        },
        {
            "id": 2,
            "name": "María López",
            "token": "marialopez",
            "rol_id": 2,
            "active": 1,
            "join_date": "2025-02-20 11:00:00",
            "last_login_date": "2025-11-14 16:45:00",
            "created_at": "2025-02-20 11:00:00",
            "updated_at": "2025-11-14 16:45:00",
            "rol": {
                "id": 2,
                "name": "Editor",
                "description": "Puede editar publicaciones"
            }
        }
    ],
    "pagination": {
        "total": 50,
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "from": 1,
        "to": 10
    }
}
```

### HTTP Request
`GET api/users/getUsers`


<!-- END_61b568955dfc112d52401a1ac60333a2 -->

<!-- START_bdad24daa8aa8effe5bff98edf583f1f -->
## Crear un usuario

Crea un nuevo usuario en el sistema con su rol asignado.
El usuario se crea activo por defecto y se establece una relación con el rol.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros del Body:**
- `name` (string, requerido): Nombre del usuario (mínimo 2, máximo 255 caracteres, debe ser único). Ejemplo: `Carlos Ramírez`
- `password` (string, requerido): Contraseña del usuario (mínimo 8 caracteres). Ejemplo: `SecurePass123`
- `rol_id` (integer, requerido): ID del rol a asignar (debe existir en la tabla roles). Ejemplo: `2`

```json
{
  "name": "Carlos Ramírez",
  "password": "SecurePass123",
  "rol_id": 2
}
```

```bash
curl -X POST "http://localhost/api/users/createUser" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"name":"Carlos Ramírez","password":"SecurePass123","rol_id":2}'
```

**Nota:**
- La contraseña se encripta automáticamente con bcrypt
- El campo `token` se genera automáticamente igual al `name`
- El usuario se crea activo (`active=1`) por defecto
- Se crea una relación en la tabla pivot `user_rol`
- La operación usa transacción de base de datos

> Example request:

```bash
curl -X POST "http://localhost/api/users/createUser" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/users/createUser",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`POST api/users/createUser`


<!-- END_bdad24daa8aa8effe5bff98edf583f1f -->

<!-- START_4b6533a6af57c56260dfd1718d7d15a6 -->
## Actualizar un usuario

Actualiza la información de un usuario existente, incluyendo su nombre y rol asignado.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del usuario a actualizar. Ejemplo: `1`

**Parámetros del Body:**
- `name` (string, requerido): Nuevo nombre del usuario (mínimo 2, máximo 255 caracteres, debe ser único excepto para el mismo usuario). Ejemplo: `Juan Carlos Pérez`
- `rol_id` (integer, requerido): Nuevo ID del rol a asignar (debe existir en la tabla roles). Ejemplo: `1`

```json
{
  "name": "Juan Carlos Pérez",
  "rol_id": 1
}
```

```bash
curl -X POST "http://localhost/api/users/updateUser/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
  -d '{"name":"Juan Carlos Pérez","rol_id":1}'
```

**Nota:**
- La relación de roles se sincroniza automáticamente en la tabla pivot `user_rol`
- La operación usa transacción de base de datos para garantizar consistencia
- No se puede actualizar la contraseña a través de este endpoint

> Example request:

```bash
curl -X POST "http://localhost/api/users/updateUser/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/users/updateUser/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Usuario actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Carlos Pérez",
        "token": "juanperez",
        "rol_id": 1,
        "active": 1,
        "join_date": "2025-01-15 10:00:00",
        "last_login_date": "2025-11-15 09:30:00",
        "created_at": "2025-01-15 10:00:00",
        "updated_at": "2025-11-15 13:00:00"
    }
}
```

### HTTP Request
`POST api/users/updateUser/{id?}`


<!-- END_4b6533a6af57c56260dfd1718d7d15a6 -->

<!-- START_887a98664fab32daa6f1f4e4045eadcb -->
## Activar/Desactivar usuario

Alterna el estado de un usuario entre activo e inactivo.
Los usuarios inactivos no podrán iniciar sesión en el sistema.

**Autenticación requerida:**
Este endpoint requiere un token JWT válido en el header de autorización.

**Parámetros de ruta (URL Parameters):**
- `id` (integer, requerido): ID del usuario cuyo estado se alternará. Ejemplo: `1`

```bash
curl -X POST "http://localhost/api/users/toggleActive/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
```

**Nota:** Esta operación es reversible. Puede activar o desactivar usuarios según sea necesario.

> Example request:

```bash
curl -X POST "http://localhost/api/users/toggleActive/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/users/toggleActive/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": true,
    "message": "Usuario activado exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "token": "juanperez",
        "rol_id": 1,
        "active": 1,
        "join_date": "2025-01-15 10:00:00",
        "last_login_date": "2025-11-15 09:30:00",
        "created_at": "2025-01-15 10:00:00",
        "updated_at": "2025-11-15 12:30:00"
    }
}
```

### HTTP Request
`POST api/users/toggleActive/{id?}`


<!-- END_887a98664fab32daa6f1f4e4045eadcb -->

#general
<!-- START_dd1765791d47886081493d47230328f2 -->
## api/documents/getDocuments

> Example request:

```bash
curl -X GET -G "http://localhost/api/documents/getDocuments" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/getDocuments",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener los documentos",
    "message": "Error al obtener los documentos"
}
```

### HTTP Request
`GET api/documents/getDocuments`


<!-- END_dd1765791d47886081493d47230328f2 -->

<!-- START_e21b1143bc16539dfc428b7c2ef787f1 -->
## api/documents/getActiveDocuments

> Example request:

```bash
curl -X GET -G "http://localhost/api/documents/getActiveDocuments" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/getActiveDocuments",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener los documentos activos",
    "message": "Error al obtener los documentos activos"
}
```

### HTTP Request
`GET api/documents/getActiveDocuments`


<!-- END_e21b1143bc16539dfc428b7c2ef787f1 -->

<!-- START_25766213cdbbdde140769bb9714ce5a1 -->
## api/documents/getDocument/{id?}

> Example request:

```bash
curl -X GET -G "http://localhost/api/documents/getDocument/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/getDocument/{id?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener el documento",
    "message": "Error al obtener el documento"
}
```

### HTTP Request
`GET api/documents/getDocument/{id?}`


<!-- END_25766213cdbbdde140769bb9714ce5a1 -->

<!-- START_a135f7a9265e87edbabf9216d244280a -->
## api/documents/searchDocument/{slug?}

> Example request:

```bash
curl -X GET -G "http://localhost/api/documents/searchDocument/{slug?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/searchDocument/{slug?}",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al buscar el documento",
    "message": "Error al buscar el documento"
}
```

### HTTP Request
`GET api/documents/searchDocument/{slug?}`


<!-- END_a135f7a9265e87edbabf9216d244280a -->

<!-- START_3de9f3c7667b605d6a4733f6c72a56b3 -->
## api/documents/createDocument

> Example request:

```bash
curl -X POST "http://localhost/api/documents/createDocument" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/createDocument",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/documents/createDocument`


<!-- END_3de9f3c7667b605d6a4733f6c72a56b3 -->

<!-- START_5a9a5959a4094f53c732642a39100953 -->
## api/documents/updateDocument/{id?}

> Example request:

```bash
curl -X POST "http://localhost/api/documents/updateDocument/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/updateDocument/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/documents/updateDocument/{id?}`


<!-- END_5a9a5959a4094f53c732642a39100953 -->

<!-- START_73890ee55882b279af055acd133b1174 -->
## api/documents/toggleState/{id?}

> Example request:

```bash
curl -X POST "http://localhost/api/documents/toggleState/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/toggleState/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/documents/toggleState/{id?}`


<!-- END_73890ee55882b279af055acd133b1174 -->

<!-- START_1173c0257c0afe519c9fd1198b0d4cd6 -->
## api/documents/uploadDocumentImage

> Example request:

```bash
curl -X POST "http://localhost/api/documents/uploadDocumentImage" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/documents/uploadDocumentImage",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/documents/uploadDocumentImage`


<!-- END_1173c0257c0afe519c9fd1198b0d4cd6 -->

<!-- START_6d2785b43ea309737a283009e91b8367 -->
## api/locations/getLocations

> Example request:

```bash
curl -X GET -G "http://localhost/api/locations/getLocations" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/getLocations",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener las ubicaciones",
    "message": "Error al obtener las ubicaciones"
}
```

### HTTP Request
`GET api/locations/getLocations`


<!-- END_6d2785b43ea309737a283009e91b8367 -->

<!-- START_ee7b99843678865003e536b1b9bf92f3 -->
## api/locations/getActiveLocations

> Example request:

```bash
curl -X GET -G "http://localhost/api/locations/getActiveLocations" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/getActiveLocations",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
{
    "ok": false,
    "error": "Error al obtener las ubicaciones activas",
    "message": "Error al obtener las ubicaciones activas"
}
```

### HTTP Request
`GET api/locations/getActiveLocations`


<!-- END_ee7b99843678865003e536b1b9bf92f3 -->

<!-- START_d80ce991012069074dbafde00aff973e -->
## api/locations/createLocation

> Example request:

```bash
curl -X POST "http://localhost/api/locations/createLocation" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/createLocation",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/locations/createLocation`


<!-- END_d80ce991012069074dbafde00aff973e -->

<!-- START_8c12121cd509ca5ce71e61aa245745c1 -->
## api/locations/updateLocation/{id?}

> Example request:

```bash
curl -X POST "http://localhost/api/locations/updateLocation/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/updateLocation/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/locations/updateLocation/{id?}`


<!-- END_8c12121cd509ca5ce71e61aa245745c1 -->

<!-- START_78ed3d1a4bb57c35d7cbea7771609bbf -->
## api/locations/toggleState/{id?}

> Example request:

```bash
curl -X POST "http://localhost/api/locations/toggleState/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/toggleState/{id?}",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/locations/toggleState/{id?}`


<!-- END_78ed3d1a4bb57c35d7cbea7771609bbf -->

<!-- START_4dac1036f50f9be5d59fbc1b90e67957 -->
## api/locations/uploadLocationImage

> Example request:

```bash
curl -X POST "http://localhost/api/locations/uploadLocationImage" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/locations/uploadLocationImage",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/locations/uploadLocationImage`


<!-- END_4dac1036f50f9be5d59fbc1b90e67957 -->


