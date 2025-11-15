FROM php:8.2-cli

WORKDIR /app
COPY . .

# Hacer el script ejecutable
RUN chmod +x start.sh

# Usar el script que maneja PORT correctamente
CMD ["./start.sh"]