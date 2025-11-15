FROM php:8.2-apache

# Copiar todo al directorio de Apache
COPY . /var/www/html/

# Habilitar rewrite para Apache
RUN a2enmod rewrite

# Configurar Apache para el puerto de Railway
RUN echo 'Listen 8080' > /etc/apache2/ports.conf
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/*.conf

# Script que maneja el puerto dinámico
CMD sed -i "s/8080/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf && \
    apache2-foreground