#  CMS Editorial Universidad Don Bosco 2025

## Tecnologías necesarias para correr el sistema
Las tecnologías esenciales con las que ha sido construido este sistema han sido las siguientes:
- PHP 5.6
- Apache 2.4.25.
- MySQL 5.7.
- Composer 1.
- [Laravel 5.4](https://laravel.com/docs/eloquent).

## Cómo ejecutar el proyecto de manera local

Luego de haber clonado del repositorio dentro de nuestra carpeta en el servidor de apache ```htdocs``` (para Windows o Mac usando XAMPP) o ```/var/www/html``` en Linux, copiaremos las variables de entorno de .env.example hacia un arhivo local .env:
```
cp .env.example .env
```
Donde configuraremos la conexion a la base de datos MySQL y demas variables de entorno.

Posterior a eso generaremos nuestras key
```
php artisan key:generate
```

Y por ultimo instalaremos las dependencias usando composer
```
composer install
```
Nuestro servidor estara listo para ser accedido de forma local en el puerto ```http://localhost:8080```.

## Cómo ejecutar el proyecto mediante Docker

*IMPORTANTE: este contenedor unicamente ha sido probado con exito en sistemas operativos Windows 10+*

Dentro de la carpeta ```/docker``` se encuentra el archivo ```docker-compose.yml``` y tambien las configuraciones del ```Dockerfile```. Si ya tenemos Docker instalado dentro de nuestro dispositivo, entonces dentro de esta carpeta usamos el siguiente comando:

```
docker-compose up -d
```

Luego, dentro de nuestro contenedor, nos dirigiremos a la carpeta ```/var/www/html``` y haremos uso de:

```
git clone https://github.com/MR211604/cms-udb-editorial.git .
```

Esto copiara todos los archivos directamente a la carpeta ```/html```, y en nuestra maquina anfitrion podremos acceder desde ```http://localhost:8080```. Una vez hecho esto, repetiremos los pasos vistos en [Cómo ejecutar el proyecto de manera local](#cómo-ejecutar-el-proyecto-de-manera-local).
