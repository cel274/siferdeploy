FROM php:8.2-apache

# Copiar todos los archivos de tu aplicación
COPY . /var/www/html/

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Exponer el puerto que usa Railway
EXPOSE 8080

# Configurar Apache para usar el puerto 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/*.conf

# Comando para iniciar Apache
CMD ["apache2-foreground"]