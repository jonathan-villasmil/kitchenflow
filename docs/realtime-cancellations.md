# Notificaciones en tiempo real para anulaciones

KitchenFlow notifica a cocina cuando una anulacion afecta a platos que ya fueron enviados al KDS.

## Flujo cubierto

Cuando desde el TPV se anula un plato ya enviado a cocina:

- Se exige PIN de gerente o super administrador.
- El `order_item` no se elimina; queda con estado `cancelled`.
- El pedido recalcula sus totales si todavia quedan platos activos.
- Si no quedan platos activos, el pedido pasa a `cancelled` y se libera la mesa.
- El sistema emite `OrderUpdatedForKitchen` en el canal privado de cocina del restaurante.

## Canal de broadcasting

El evento se emite por:

`private-kitchen.{restaurant_id}`

Esto mantiene el aislamiento por restaurante. Una cocina solo escucha las anulaciones de su propio local.

## Acciones del evento

El evento `OrderUpdatedForKitchen` soporta estas acciones:

- `item_cancelled`: se anulo un plato, pero la comanda sigue activa.
- `order_cancelled`: se anulo la comanda completa porque no quedan platos activos.

## Datos enviados al KDS

El payload incluye:

- `action`
- `order_id`
- `status`
- `table_number`
- `item_id`
- `item_name`
- `active_items_count`
- `cancelled_items_count`

## Comportamiento del KDS

El KDS escucha:

- `OrderSentToKitchen`
- `OrderUpdatedForKitchen`

Cuando recibe `OrderUpdatedForKitchen`, refresca la pantalla para retirar platos anulados o quitar la comanda si ya no queda nada pendiente.

## Pruebas cubiertas

La cobertura valida que:

- `OrderUpdatedForKitchen` usa el canal privado correcto del restaurante.
- El payload contiene contexto de anulacion.
- El TPV emite el evento al anular un plato enviado a cocina.
- La anulacion queda registrada como `cancelled` en base de datos.
