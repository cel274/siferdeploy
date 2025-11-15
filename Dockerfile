FROM php:8.2-apache

# Instalar dependencias del sistema básicas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mysqli pdo pdo_mysql

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Configurar Apache para Railway
RUN echo 'Listen 8080' > /etc/apache2/ports.conf

# Configurar virtual host para apuntar a public/
RUN echo '<VirtualHost *:8080>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory "/var/www/html/public">\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Configurar ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copiar todos los archivos del proyecto
COPY . /var/www/html/

# Dar permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# Script de inicio que maneja el puerto dinámico
CMD sed -i "s/8080/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && \
    apache2-foreground