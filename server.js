const express = require('express');
const path = require('path');
const app = express();

// Servir archivos estáticos
app.use(express.static('.'));

// Redirigir la ruta raíz a index.php
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.php'));
});

// Para cualquier otra ruta, servir el archivo directamente
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, req.path));
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
  console.log(`SIFER ejecutándose en puerto ${PORT}`);
});