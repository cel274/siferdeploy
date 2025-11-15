const express = require('express');
const app = express();

// Servir archivos estáticos
app.use(express.static('.'));

// Para archivos PHP, servirlos como estáticos
app.use('*.php', express.static('.'));

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
  console.log(`SIFER ejecutándose en puerto ${PORT}`);
});