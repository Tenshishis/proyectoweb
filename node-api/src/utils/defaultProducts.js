const productBases = [
  { nombre: 'Audifonos Bluetooth', categoria: 'Audio', descripcion: 'Audifonos inalambricos con conectividad Bluetooth y sonido claro.', precioBase: 399, stockBase: 24 },
  { nombre: 'Bocina Portatil', categoria: 'Audio', descripcion: 'Bocina portatil para musica, reuniones y entretenimiento diario.', precioBase: 549, stockBase: 20 },
  { nombre: 'Barra de Sonido', categoria: 'Audio', descripcion: 'Barra de sonido para mejorar el audio de pantallas y espacios pequenos.', precioBase: 1599, stockBase: 10 },
  { nombre: 'Microfono USB', categoria: 'Audio', descripcion: 'Microfono USB para videollamadas, streaming y grabaciones caseras.', precioBase: 699, stockBase: 18 },
  { nombre: 'Webcam Full HD', categoria: 'Computo', descripcion: 'Webcam Full HD para oficina remota, clases y creacion de contenido.', precioBase: 629, stockBase: 22 },
  { nombre: 'Mouse Inalambrico', categoria: 'Computo', descripcion: 'Mouse inalambrico para trabajo, estudio y productividad diaria.', precioBase: 249, stockBase: 28 },
  { nombre: 'Teclado Mecanico', categoria: 'Computo', descripcion: 'Teclado mecanico con respuesta firme para trabajo y gaming.', precioBase: 899, stockBase: 14 },
  { nombre: 'Monitor LED 24', categoria: 'Computo', descripcion: 'Monitor LED de 24 pulgadas para escritorio y entretenimiento.', precioBase: 2899, stockBase: 8 },
  { nombre: 'Base de Enfriamiento Laptop', categoria: 'Computo', descripcion: 'Base de enfriamiento para mejorar la temperatura en laptops.', precioBase: 459, stockBase: 18 },
  { nombre: 'SSD Externo 500GB', categoria: 'Almacenamiento', descripcion: 'Unidad SSD externa para respaldo y transporte de archivos.', precioBase: 899, stockBase: 16 },
  { nombre: 'Tablet 10 Pulgadas', categoria: 'Movilidad', descripcion: 'Tablet para estudio, lectura, videollamadas y entretenimiento.', precioBase: 1899, stockBase: 14 },
  { nombre: 'Smartwatch Deportivo', categoria: 'Wearables', descripcion: 'Reloj inteligente para notificaciones, actividad y salud.', precioBase: 999, stockBase: 18 },
  { nombre: 'Router WiFi', categoria: 'Redes', descripcion: 'Router WiFi para mejorar cobertura y estabilidad de red.', precioBase: 899, stockBase: 16 },
  { nombre: 'Repetidor WiFi', categoria: 'Redes', descripcion: 'Repetidor de senal para ampliar cobertura inalambrica.', precioBase: 549, stockBase: 18 },
  { nombre: 'Power Bank 10000mAh', categoria: 'Energia', descripcion: 'Bateria externa para recargar telefonos y gadgets.', precioBase: 450, stockBase: 24 },
  { nombre: 'Cargador Inalambrico', categoria: 'Energia', descripcion: 'Base de carga inalambrica para telefonos compatibles.', precioBase: 279, stockBase: 26 },
  { nombre: 'Regleta Inteligente', categoria: 'Energia', descripcion: 'Regleta para proteger y distribuir energia a equipos electronicos.', precioBase: 399, stockBase: 20 },
  { nombre: 'Camara de Seguridad', categoria: 'Smart Home', descripcion: 'Camara inteligente para monitoreo remoto en interiores.', precioBase: 1099, stockBase: 14 },
  { nombre: 'Foco Inteligente', categoria: 'Smart Home', descripcion: 'Foco inteligente con control remoto y automatizacion.', precioBase: 229, stockBase: 30 },
  { nombre: 'Control Gamer Inalambrico', categoria: 'Gaming', descripcion: 'Control inalambrico para juegos con ergonomia y respuesta precisa.', precioBase: 699, stockBase: 16 }
];

const productVariants = [
  { nombre: 'Lite', detalle: 'Version de entrada para uso diario.', precioExtra: 0, stockExtra: 4 },
  { nombre: 'Plus', detalle: 'Incluye mejor autonomia y funciones extendidas.', precioExtra: 90, stockExtra: 2 },
  { nombre: 'Pro', detalle: 'Orientado a usuarios que buscan mayor rendimiento.', precioExtra: 180, stockExtra: 0 },
  { nombre: 'Max', detalle: 'Configuracion reforzada para jornadas intensivas.', precioExtra: 270, stockExtra: -2 },
  { nombre: 'X', detalle: 'Edicion con conectividad moderna y acabado premium.', precioExtra: 350, stockExtra: -4 },
  { nombre: 'Studio', detalle: 'Pensado para creadores, oficina y trabajo hibrido.', precioExtra: 210, stockExtra: 1 },
  { nombre: 'Office', detalle: 'Ideal para productividad continua y estaciones de trabajo.', precioExtra: 120, stockExtra: 3 },
  { nombre: 'Home', detalle: 'Ajustado para entretenimiento y uso familiar.', precioExtra: 70, stockExtra: 5 },
  { nombre: 'Travel', detalle: 'Diseno compacto para movilidad y espacios reducidos.', precioExtra: 110, stockExtra: 2 },
  { nombre: 'Ultra', detalle: 'Acabado superior con mejor respuesta general.', precioExtra: 320, stockExtra: -3 }
];

const defaultProducts = productBases.flatMap((productBase, baseIndex) => (
  productVariants.map((variant, variantIndex) => ({
    nombre: `${productBase.nombre} ${variant.nombre}`,
    precio: productBase.precioBase + variant.precioExtra + (baseIndex * 15) + (variantIndex % 3) * 10,
    descripcion: `${productBase.descripcion} ${variant.detalle}`,
    categoria: productBase.categoria,
    stock: Math.max(6, productBase.stockBase + variant.stockExtra + (baseIndex % 4)),
    activo: true
  }))
));

module.exports = defaultProducts;