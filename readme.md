#  CMS Editorial Universidad Don Bosco 2025

## Tecnologías necesarias para correr el sistema
Las tecnologías esenciales con las que ha sido construido este sistema han sido las siguientes:
- PHP 5.6
- Apache 2.4.25.
- MySQL 5.7.
- Composer 2.
- [Laravel 5.4](https://laravel.com/docs/eloquent).

## Cómo ejecutar el proyecto de manera local

Luego de haber clonado del repositorio dentro de nuestra carpeta en el servidor de apache ```htdocs``` (para Windows o Mac usando XAMPP) o ```/var/www/html``` en Linux, copiaremos las variables de entorno de .env.example hacia un arhivo local .env:
```
cp .env.example .env
```
Donde configuraremos la conexion a la base de datos MySQL y demas variables de entorno.

Instalaremos las dependencias usando composer
```
composer install
```

Posterior a eso generaremos nuestras key
```
php artisan key:generate
```

Brindaremos los permisos necesarios a la carpeta raiz, para no tener inconvenientes futuros
```
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

chown -R www-data:www-data /var/www/html/public
chmod -R 755 /var/www/html/public/editorial_files
chmod -R 755 /var/www/html/public/imagescontent

```

Nuestro servidor estara listo para ser accedido de forma local en el puerto ```http://localhost:8080```.

## Cómo ejecutar el proyecto mediante Docker

*IMPORTANTE: este contenedor unicamente ha sido probado con exito en sistemas operativos Windows 10+ y distribuciones de linux como Arch.*

Dentro de la carpeta ```/docker``` se encuentra el archivo ```docker-compose.yml``` y tambien las configuraciones del ```Dockerfile```. Si ya tenemos Docker instalado dentro de nuestro dispositivo, entonces dentro de esta carpeta usamos el siguiente comando:

```
docker-compose up -d
```

Luego, dentro de nuestro contenedor, nos dirigiremos a la carpeta ```/var/www/html``` y haremos uso de:

```
git clone https://github.com/MR211604/cms-udb-editorial.git .
```

Esto copiara todos los archivos directamente a la carpeta ```/html```, y en nuestra maquina anfitrion podremos acceder desde ```http://localhost:8080```. Una vez hecho esto, repetiremos los pasos vistos en [Cómo ejecutar el proyecto de manera local](#cómo-ejecutar-el-proyecto-de-manera-local).


## Documentación

Para poder actualizar la documentación de la API, necesitarás hacer el siguiente comando:

```shell
php artisan api:generate --routePrefix="api/*" --force 2>&1 | tail -10
```

Para mantener el estilo del logo en la documentación (ya que el index.html se reescribe cuando haces el comando con api:generate), solamente debes reescribir esta parte del HTML:

```html
<img src="images/logo.png" style="object-fit: contain; width: -webkit-fill-available; height: 60px; background: #ffffff;">
```