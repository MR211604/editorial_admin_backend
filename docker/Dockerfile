FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

# 1. Instalar dependencias del sistema y PHP 5.6
RUN apt-get update -y && \
    apt-get install -y software-properties-common apache2 git curl unzip zip nano && \
    add-apt-repository ppa:ondrej/php -y && \
    apt-get update -y && \
    apt-get install -y \
        php5.6 php5.6-cli php5.6-common php5.6-mysql php5.6-zip \
        php5.6-gd php5.6-mbstring php5.6-curl php5.6-xml php5.6-bcmath \
        libapache2-mod-php5.6

# 2. Instalar Composer 1 (compatible con Laravel 5.4)
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer self-update --1

# 3. Habilitar mod_rewrite y headers en Apache y configurar VirtualHost
RUN a2enmod rewrite headers php5.6 && \
    cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:80>
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# 4. Configurar directorio de trabajo y permisos
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html \
    chown -R www-data:www-data /var/www/html/storage \
    chmod -R 775 /var/www/html/storage

# 5. Exponer solo puerto 80
EXPOSE 80

# 6. Iniciar Apache en primer plano
CMD ["apachectl", "-D", "FOREGROUND"]
