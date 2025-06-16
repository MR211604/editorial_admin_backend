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