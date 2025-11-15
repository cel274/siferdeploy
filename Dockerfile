FROM node:18-alpine

# Crear directorio de la app
WORKDIR /app

# Copiar package.json primero (para cache de dependencias)
COPY package.json .

# Instalar dependencias
RUN npm install

# Copiar el resto de la aplicación
COPY . .

# Exponer puerto
EXPOSE 3000

# Comando de inicio
CMD ["node", "server.js"]