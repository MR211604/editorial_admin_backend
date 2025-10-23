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

Autentica con credenciales y devuelve un **JWT** (Bearer).
> Este endpoint **no** requiere autenticación previa.

> Example request:

```bash
curl -X POST "http://localhost/api/auth/login" \
    -H "Accept: application/json" \
    -d "token"="eveniet" \
        -d "password"="eveniet" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/login",
    "method": "POST",
    "data": {
        "token": "eveniet",
        "password": "eveniet"
    },
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
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N.....",
    "user": {
        "id": 2,
        "name": "testnameupdate",
        "active": 1,
        "join_date": "2025-06-29 19:42:59",
        "last_login_date": "2025-10-23 10:34:31",
        "rol_id": 2,
        "token": "testnameupdate"
    },
    "first_login": null,
    "last_login": null
}
```

### HTTP Request
`POST api/auth/login`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    token | string |  required  | 
    password | string |  required  | 

<!-- END_a925a8d22b3615f12fca79456d286859 -->

<!-- START_a72455bc9968f52ac7e1d8518d843eb6 -->
## Renovar token

Recibe el **Bearer Token** actual y devuelve un **nuevo token** válido.

> Example request:

```bash
curl -X GET -G "http://localhost/api/auth/renew" \
    -H "Accept: application/json" \
    -d "token"="enim" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/renew",
    "method": "GET",
    "data": {
        "token": "enim"
    },
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
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N.....",
    "user": {
        "id": 2,
        "name": "testnameupdate",
        "active": 1,
        "join_date": "2025-06-29 19:42:59",
        "last_login_date": "2025-10-23 10:34:31",
        "rol_id": 2,
        "token": "testnameupdate"
    }
}
```

### HTTP Request
`GET api/auth/renew`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    token | string |  required  | 

<!-- END_a72455bc9968f52ac7e1d8518d843eb6 -->

<!-- START_19ff1b6f8ce19d3c444e9b518e8f7160 -->
## Cerrar sesión

Invalida el **Bearer Token** actual.

> Example request:

```bash
curl -X POST "http://localhost/api/auth/logout" \
    -H "Accept: application/json" \
    -d "token"="earum" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/auth/logout",
    "method": "POST",
    "data": {
        "token": "earum"
    },
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

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    token | string |  required  | 

<!-- END_19ff1b6f8ce19d3c444e9b518e8f7160 -->

#Authors

Gestión de autores del sistema editorial.
<!-- START_b9db63505163eb92403717250ed1745e -->
## Listar autores

Devuelve un listado paginado de autores con opciones de búsqueda y ordenamiento.

> Example request:

```bash
curl -X GET -G "http://localhost/api/authors/getAuthors" \
    -H "Accept: application/json" \
    -d "page"="1687728440" \
        -d "limit"="1687728442" \
        -d "search"="repellendus" \
        -d "sort"="repellendus" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/getAuthors",
    "method": "GET",
    "data": {
        "page": 1687728440,
        "limit": 1687728442,
        "search": "repellendus",
        "sort": "repellendus"
    },
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
            "id": 114,
            "name": "asdasd",
            "lastname": "asdasdssss"
        },
        {
            "id": 113,
            "name": "Dross",
            "lastname": "Rotzank"
        },
        {
            "id": 109,
            "name": "Emilio",
            "lastname": "Delgado Chavarría"
        },
        {
            "id": 108,
            "name": "Humberto",
            "lastname": "Flores"
        },
        {
            "id": 106,
            "name": "René",
            "lastname": "Mauricio Valdéz"
        },
        {
            "id": 105,
            "name": "Claudia",
            "lastname": "Urbina"
        }
    ],
    "pagination": {
        "total": 114,
        "current_page": 1,
        "last_page": 12,
        "per_page": "10",
        "from": 1,
        "to": 10
    }
}
```

### HTTP Request
`GET api/authors/getAuthors`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    page | integer |  optional  | Minimum: `1`
    limit | integer |  optional  | Minimum: `10`
    search | string |  optional  | 
    sort | string |  optional  | 

<!-- END_b9db63505163eb92403717250ed1745e -->

<!-- START_04ae12876e1e048741bb9d37b4ca3457 -->
## Obtener autor

Devuelve la información de un autor específico.

> Example request:

```bash
curl -X GET -G "http://localhost/api/authors/getAuthor/{id}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/getAuthor/{id}",
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
        "name": "Ana Cecilia equisde",
        "lastname": "de Méndez"
    },
    "pagination": null
}
```

### HTTP Request
`GET api/authors/getAuthor/{id}`


<!-- END_04ae12876e1e048741bb9d37b4ca3457 -->

<!-- START_4b1b0caf286ea71c299b0389a6525a2c -->
## Crear autor

Registra un nuevo autor.
> Body Esperado

Body esperado:
```json
{
  "name": "Julio",
  "lastname": "Cortázar"
}
```

> Example request:

```bash
curl -X POST "http://localhost/api/authors/createAuthor" \
    -H "Accept: application/json" \
    -d "name"="enim" \
        -d "lastname"="enim" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/createAuthor",
    "method": "POST",
    "data": {
        "name": "enim",
        "lastname": "enim"
    },
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
    "message": "Autor creado exitosamente",
    "data": {
        "name": "Robert",
        "lastname": "Martin",
        "id": 115
    },
    "pagination": null
}
```

### HTTP Request
`POST api/authors/createAuthor`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    name | string |  required  | Maximum: `255`
    lastname | string |  required  | Maximum: `255`

<!-- END_4b1b0caf286ea71c299b0389a6525a2c -->

<!-- START_e26ff161ef208a2517bd182fa36d07ef -->
## Actualizar autor

Modifica los datos de un autor existente.
> Body Esperado

Body esperado
```json
{
  "id": 1,
  "name": "Robert C.",
  "lastname": "Martin"
}
```

> Example request:

```bash
curl -X POST "http://localhost/api/authors/updateAuthor" \
    -H "Accept: application/json" \
    -d "id"="1" \
        -d "name"="ab" \
        -d "lastname"="ab" 
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/updateAuthor",
    "method": "POST",
    "data": {
        "id": 1,
        "name": "ab",
        "lastname": "ab"
    },
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
        "id": 12,
        "name": "Laura",
        "lastname": "Esquivel"
    },
    "pagination": null
}
```

### HTTP Request
`POST api/authors/updateAuthor`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  required  | Valid author id
    name | string |  required  | Maximum: `255`
    lastname | string |  required  | Maximum: `255`

<!-- END_e26ff161ef208a2517bd182fa36d07ef -->

<!-- START_2531cf2dd315bde2e0853bdd0a05eabb -->
## Eliminar autor

Endpoint reservado para completar la eliminación de autores.

> Example request:

```bash
curl -X DELETE "http://localhost/api/authors/deleteAuthor/{id}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/deleteAuthor/{id}",
    "method": "DELETE",
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
    "message": "Función no implementada aún."
}
```

### HTTP Request
`DELETE api/authors/deleteAuthor/{id}`


<!-- END_2531cf2dd315bde2e0853bdd0a05eabb -->

#general
<!-- START_61b568955dfc112d52401a1ac60333a2 -->
## api/users/getUsers

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
null
```

### HTTP Request
`GET api/users/getUsers`


<!-- END_61b568955dfc112d52401a1ac60333a2 -->

<!-- START_bdad24daa8aa8effe5bff98edf583f1f -->
## api/users/createUser

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


### HTTP Request
`POST api/users/createUser`


<!-- END_bdad24daa8aa8effe5bff98edf583f1f -->

<!-- START_f32197e71cb96cfd48b93667b5eedf8c -->
## api/publishings/getPublishings

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
null
```

### HTTP Request
`GET api/publishings/getPublishings`


<!-- END_f32197e71cb96cfd48b93667b5eedf8c -->

<!-- START_ad89b3f28c1bdbc089c08760b57e4f29 -->
## api/publishings/getPublishing/{id?}

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
null
```

### HTTP Request
`GET api/publishings/getPublishing/{id?}`


<!-- END_ad89b3f28c1bdbc089c08760b57e4f29 -->

<!-- START_bd07c46bcd1ea465d2b325edab2a8edf -->
## api/publishings/createPublishing

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


### HTTP Request
`POST api/publishings/createPublishing`


<!-- END_bd07c46bcd1ea465d2b325edab2a8edf -->

<!-- START_ef93a34472cbbf4adc14b6f2833ce65d -->
## api/publishings/updatePublishing/{id?}

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


### HTTP Request
`POST api/publishings/updatePublishing/{id?}`


<!-- END_ef93a34472cbbf4adc14b6f2833ce65d -->

<!-- START_4f71d50134a0a4c178582dc2fc2aa3e5 -->
## api/publishings/updateStatus/{id?}

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


### HTTP Request
`POST api/publishings/updateStatus/{id?}`


<!-- END_4f71d50134a0a4c178582dc2fc2aa3e5 -->

<!-- START_2ebc19a9c467323e08aee4aeb2bceeef -->
## api/publishings/deletePublishing/{id?}

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


### HTTP Request
`POST api/publishings/deletePublishing/{id?}`


<!-- END_2ebc19a9c467323e08aee4aeb2bceeef -->

<!-- START_5d4088a4940540ba2e40f3f61016e478 -->
## api/publishings/summary

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
null
```

### HTTP Request
`GET api/publishings/summary`


<!-- END_5d4088a4940540ba2e40f3f61016e478 -->

<!-- START_d0505dab0b8dac386a10ea0f27445f01 -->
## api/rol/getRol

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
null
```

### HTTP Request
`GET api/rol/getRol`


<!-- END_d0505dab0b8dac386a10ea0f27445f01 -->

<!-- START_4e3c593528e7edbafc2f458f2cde4089 -->
## api/rol/createRol

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


### HTTP Request
`POST api/rol/createRol`


<!-- END_4e3c593528e7edbafc2f458f2cde4089 -->

<!-- START_5a74c92ebeef8c365686cb7eadbbd64d -->
## api/rol/updateRol/{id}

> Example request:

```bash
curl -X PUT "http://localhost/api/rol/updateRol/{id}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/rol/updateRol/{id}",
    "method": "PUT",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`PUT api/rol/updateRol/{id}`


<!-- END_5a74c92ebeef8c365686cb7eadbbd64d -->

<!-- START_d765e5042c1d26a3a027d6aeda631bbd -->
## api/rol/deleteRol/{id}

> Example request:

```bash
curl -X DELETE "http://localhost/api/rol/deleteRol/{id}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/rol/deleteRol/{id}",
    "method": "DELETE",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`DELETE api/rol/deleteRol/{id}`


<!-- END_d765e5042c1d26a3a027d6aeda631bbd -->

<!-- START_dd0177d887ba52822ffb3d90a24e1a69 -->
## api/privileges/getPrivileges

> Example request:

```bash
curl -X GET -G "http://localhost/api/privileges/getPrivileges" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/privileges/getPrivileges",
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
null
```

### HTTP Request
`GET api/privileges/getPrivileges`


<!-- END_dd0177d887ba52822ffb3d90a24e1a69 -->

<!-- START_e843c3c236aca5d613b0652d164fac15 -->
## api/privileges/getPrivilege/{id?}

> Example request:

```bash
curl -X GET -G "http://localhost/api/privileges/getPrivilege/{id?}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/privileges/getPrivilege/{id?}",
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
null
```

### HTTP Request
`GET api/privileges/getPrivilege/{id?}`


<!-- END_e843c3c236aca5d613b0652d164fac15 -->

<!-- START_1f1fed92545eadd034b4841af9899b67 -->
## api/privileges/createPrivilege

> Example request:

```bash
curl -X POST "http://localhost/api/privileges/createPrivilege" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/privileges/createPrivilege",
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
`POST api/privileges/createPrivilege`


<!-- END_1f1fed92545eadd034b4841af9899b67 -->

<!-- START_427babe17cd9b85a995bdd33f4395bed -->
## api/privileges/updatePrivilege

> Example request:

```bash
curl -X POST "http://localhost/api/privileges/updatePrivilege" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/privileges/updatePrivilege",
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
`POST api/privileges/updatePrivilege`


<!-- END_427babe17cd9b85a995bdd33f4395bed -->

<!-- START_425247abdbf41c99398159e68b414b4f -->
## api/privileges/deletePrivilege/{id}

> Example request:

```bash
curl -X DELETE "http://localhost/api/privileges/deletePrivilege/{id}" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/privileges/deletePrivilege/{id}",
    "method": "DELETE",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`DELETE api/privileges/deletePrivilege/{id}`


<!-- END_425247abdbf41c99398159e68b414b4f -->

<!-- START_e025a908a743d8d7e8b0c3c27ddc9046 -->
## api/categories/getCategories

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
null
```

### HTTP Request
`GET api/categories/getCategories`


<!-- END_e025a908a743d8d7e8b0c3c27ddc9046 -->

<!-- START_b69b9dfb42050f0036c4a5ec9f9d6a6b -->
## api/categories/getCategory/{id?}

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
null
```

### HTTP Request
`GET api/categories/getCategory/{id?}`


<!-- END_b69b9dfb42050f0036c4a5ec9f9d6a6b -->

<!-- START_73cd004146fd92a9601b7943fa74d33c -->
## api/categories/createCategory

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


### HTTP Request
`POST api/categories/createCategory`


<!-- END_73cd004146fd92a9601b7943fa74d33c -->

<!-- START_1263912fd1b0f393408f41322fb6c8fe -->
## api/categories/updateCategory/{id?}

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


### HTTP Request
`POST api/categories/updateCategory/{id?}`


<!-- END_1263912fd1b0f393408f41322fb6c8fe -->

<!-- START_3baa66247916c6f29706b1dba1d0f3b6 -->
## api/publishingDetails/createPublishingDetail

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


### HTTP Request
`POST api/publishingDetails/createPublishingDetail`


<!-- END_3baa66247916c6f29706b1dba1d0f3b6 -->

<!-- START_a802abe40b0b7535aa3b715bd30146f7 -->
## api/publishingDetails/updatePublishingDetail/{id?}

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


### HTTP Request
`POST api/publishingDetails/updatePublishingDetail/{id?}`


<!-- END_a802abe40b0b7535aa3b715bd30146f7 -->


