**KitchenFlow** es un sistema de gestión integral para restaurantes, pensado para manejar varios locales desde una misma plataforma, manteniendo separados los datos de cada restaurante.

A nivel práctico, el proyecto cubre:

‌

1. **Panel Administrativo**

Es la zona de gestión para managers y super admin.

Desde aquí se gestionan:

- Restaurantes.
- Usuarios y roles.
- Empleados.
- Mesas y zonas.
- Carta, categorías, platos, modificadores y extras.
- Inventario.
- Proveedores.
- Reservas.
- Pedidos.
- Informes de ventas.
- Reportes y estadísticas.

Cada manager ve solo su restaurante.
El super admin puede ver todos o elegir un restaurante concreto.

‌

1. **TPV / POS**

Es la pantalla de venta para camareros, cajeros o managers.

Permite:

- Seleccionar mesa.
- Añadir platos.
- Mandar platos a cocina.
- Dividir cuenta.
- Cobrar en efectivo o tarjeta.
- Aplicar propinas.
- Usar clientes/fidelización.
- Abrir y cerrar caja.
- Generar tickets y reporte Z.
- Anular platos con PIN de manager.

‌

1. **KDS / Pantalla de Cocina**

Es la pantalla para cocina.

Muestra:

- Pedidos enviados desde el POS o menú público.
- Platos pendientes por estación: caliente, fría, barra, panadería.
- Tiempos de espera.
- Estado de conexión en tiempo real.
- Marcado de platos como listos.

Está aislado por restaurante: cocina solo ve pedidos de su local.

‌

1. **Menú Público Digital**

Es el menú al que accede el cliente desde una mesa, probablemente por QR.

Permite:

- Ver categorías y platos disponibles.
- Añadir platos al carrito.
- Enviar pedido directamente a cocina.
- Respetar stock/disponibilidad.
- Asociar el pedido a la mesa correcta.

También está protegido para que no se puedan pedir platos de otro restaurante manipulando IDs.

‌

1. **Multi-Restaurante**

El sistema usa una sola base de datos, pero separa los datos con `restaurant_id`.

Eso significa:

- Restaurante A tiene sus platos, mesas, pedidos y usuarios.
- Restaurante B tiene los suyos.
- Un manager de A no ve datos de B.
- El super admin puede decidir si ve todo o un restaurante concreto.

‌

1. **Tiempo Real**

Usa Laravel Reverb/WebSockets para avisar en tiempo real:

- Nuevos pedidos a cocina.
- Pedidos listos para servir.
- Cambios de stock.
- Eventos POS/KDS.

Queda justo pendiente cerrar del todo la notificación en tiempo real para anulaciones, que era lo que estábamos haciendo.

‌

1. **Seguridad Y Roles**

Los roles principales son:

- `super_admin`: control global.
- `manager`: administra su restaurante.
- `camarero`: usa POS.
- `cajero`: usa POS/cobro.
- `cocinero`: usa KDS.

El proyecto ya tiene bastante trabajo hecho para que cada rol entre donde toca.

En resumen: **KitchenFlow es un SaaS/POS multi-restaurante para operar sala, cocina, caja, carta, inventario, reservas e informes desde una sola plataforma, con separación de datos por restaurante y control central para super admin.**