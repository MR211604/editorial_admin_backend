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

#general
<!-- START_a925a8d22b3615f12fca79456d286859 -->
## api/auth/login

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


### HTTP Request
`POST api/auth/login`


<!-- END_a925a8d22b3615f12fca79456d286859 -->

<!-- START_a72455bc9968f52ac7e1d8518d843eb6 -->
## api/auth/renew

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
null
```

### HTTP Request
`GET api/auth/renew`


<!-- END_a72455bc9968f52ac7e1d8518d843eb6 -->

<!-- START_19ff1b6f8ce19d3c444e9b518e8f7160 -->
## api/auth/logout

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


### HTTP Request
`POST api/auth/logout`


<!-- END_19ff1b6f8ce19d3c444e9b518e8f7160 -->

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

<!-- START_b9db63505163eb92403717250ed1745e -->
## api/authors/getAuthors

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
null
```

### HTTP Request
`GET api/authors/getAuthors`


<!-- END_b9db63505163eb92403717250ed1745e -->

<!-- START_04ae12876e1e048741bb9d37b4ca3457 -->
## api/authors/getAuthor/{id}

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
null
```

### HTTP Request
`GET api/authors/getAuthor/{id}`


<!-- END_04ae12876e1e048741bb9d37b4ca3457 -->

<!-- START_4b1b0caf286ea71c299b0389a6525a2c -->
## api/authors/createAuthor

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


### HTTP Request
`POST api/authors/createAuthor`


<!-- END_4b1b0caf286ea71c299b0389a6525a2c -->

<!-- START_e26ff161ef208a2517bd182fa36d07ef -->
## api/authors/updateAuthor

> Example request:

```bash
curl -X POST "http://localhost/api/authors/updateAuthor" \
    -H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://localhost/api/authors/updateAuthor",
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
`POST api/authors/updateAuthor`


<!-- END_e26ff161ef208a2517bd182fa36d07ef -->

<!-- START_2531cf2dd315bde2e0853bdd0a05eabb -->
## api/authors/deleteAuthor/{id}

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


### HTTP Request
`DELETE api/authors/deleteAuthor/{id}`


<!-- END_2531cf2dd315bde2e0853bdd0a05eabb -->

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


