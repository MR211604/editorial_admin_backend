#  CMS Editorial Universidad Don Bosco 2025

## Tecnologías necesarias para correr el sistema
Las tecnologías esenciales con las que ha sido construido este sistema han sido las siguientes:
- PHP 5.6
- Apache 2.4.25.
- MySQL 5.7.
- Composer 2.2.
- [Laravel 5.4](https://laravel.com/docs/eloquent).

## Cómo ejecutar el proyecto de manera local

Luego de haber clonado del repositorio dentro de nuestra carpeta en el servidor de apache ```htdocs``` (para Windows o Mac usando XAMPP) o ```/var/www/html``` en Linux, copiaremos las variables de entorno de .env.example hacia un arhivo local .env:
```sh
cp .env.example .env
```
Donde configuraremos la conexion a la base de datos MySQL y demas variables de entorno.

Instalaremos las dependencias usando composer
```sh
composer install
```

Posterior a eso generaremos nuestras key
```sh
php artisan key:generate
```

Brindaremos los permisos necesarios a la carpeta raiz, para no tener inconvenientes futuros
```sh
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

chown -R www-data:www-data /var/www/html/public
chmod -R 755 /var/www/html/public/editorial_files
chmod -R 755 /var/www/html/public/imagescontent

```

Nuestro servidor estara listo para ser accedido de forma local en el puerto ```http://localhost:8080```.

## Cómo ejecutar el proyecto mediante Docker

*IMPORTANTE: este contenedor unicamente ha sido probado con exito en sistemas operativos Windows 10+ y distribuciones de linux como Arch.*

Dentro de la carpeta ```/docker``` se encuentra el archivo ```docker-compose.yml``` y tambien las configuraciones del ```Dockerfile```. 

Antes de generar el contendor, debido a que el repositorio de Github es privado, posiblemente tendremos que generar un fine-grained personal access token en GitHub con acceso solo a ese repositorio y el permiso Contents: Read-only.

Ese token lo guardaremos en una variable de entorno GH_TOKEN dentro de ```/docker```, asi:

Según tu terminal

Linux / macOS / WSL / Git Bash:

bash
```sh
export GH_TOKEN=ghp_xxxxxxxxxxxx
```

PowerShell (Windows):

```sh
$env:GH_TOKEN = "ghp_xxxxxxxxxxxx"
```

CMD (Windows):

```sh
set GH_TOKEN=ghp_xxxxxxxxxxxx
```

Luego haremos:

```sh
docker-compose up -d
```

Luego, podremos acceder a nuestro contenedor de manera local con los siguientes commandos:

```sh
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
```

Esto sincronizara nuestros cambios locales con todos los archivos directamente a la carpeta del contenedor ```/html```, y podremos acceder desde ```http://localhost:8080```.


## Documentación

Para poder actualizar la documentación de la API, necesitarás hacer el siguiente comando:

```sh
php artisan api:generate --routePrefix="api/*" --force 2>&1 | tail -10
```

Para mantener el estilo del logo en la documentación (ya que el index.html se reescribe cuando haces el comando con api:generate), solamente debes reescribir esta parte del HTML:

```html
<img src="images/logo.png" style="object-fit: contain; width: -webkit-fill-available; height: 60px; background: #ffffff;">
```
