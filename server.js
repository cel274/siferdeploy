const express = require('express');
const { exec } = require('child_process');
const app = express();
const port = process.env.PORT || 3000;

// Servir archivos estáticos
app.use(express.static('.'));

// Proxy para API PHP
app.use('/api/*', (req, res) => {
  // Aquí iría la lógica para ejecutar PHP
  // Por ahora redirigimos a un mensaje
  res.json({ 
    success: false, 
    message: 'PHP no disponible en este entorno',
    suggestion: 'Usar Railway o hosting con PHP'
  });
});

app.listen(port, () => {
  console.log(`Servidor en puerto ${port}`);
});
