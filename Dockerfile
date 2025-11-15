FROM php:8.2-apache

# Copiar todos los archivos de tu aplicación
COPY . /var/www/html/

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Exponer el puerto (Railway lo maneja automáticamente)
EXPOSE 8080

# Configurar Apache para usar el puerto de Railway
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf
RUN echo '<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Script de inicio que maneja la variable PORT
CMD sed -i "s/\\${PORT}/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && \
    apache2-foreground