FROM node:18-alpine

# Instalar un servidor estático
RUN npm install -g serve

# Copiar tu proyecto
COPY . .

# Exponer puerto
EXPOSE 3000

# Comando simple - servir archivos estáticos
CMD ["npx", "serve", "-s", ".", "-l", "tcp://0.0.0.0:3000"]