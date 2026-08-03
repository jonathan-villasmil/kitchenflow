# Canales de pedido y reparto manual

KitchenFlow soporta en Fase 1 varios canales de venta desde el TPV sin integracion automatica con plataformas externas.

## Canales disponibles

- `pos`: sala / mesa.
- `takeaway`: recogida en local.
- `manual_delivery`: reparto propio.
- `glovo`: pedido introducido manualmente desde Glovo.
- `uber_eats`: pedido introducido manualmente desde Uber Eats.

## Alcance de Fase 1

Esta fase no conecta todavia con las APIs de Glovo o Uber Eats.

El personal introduce manualmente:

- Cliente.
- Telefono.
- Direccion, si aplica.
- Referencia externa de plataforma, si aplica.
- Coste de reparto cobrado al cliente.
- Comision estimada de plataforma.
- Notas de entrega.

## Datos guardados

Los datos principales del canal se guardan en `orders`:

- `type`
- `source`
- `external_platform`
- `external_order_id`
- `delivery_status`

Los datos especificos de entrega se guardan en `order_deliveries`:

- `customer_name`
- `customer_phone`
- `address_line`
- `city`
- `postal_code`
- `delivery_notes`
- `scheduled_at`
- `delivery_fee`
- `platform_fee`

## Reglas del TPV

- Sala usa mesa obligatoriamente.
- Recogida se abre como pedido directo sin mesa.
- Reparto propio requiere nombre, telefono y direccion.
- Glovo y Uber Eats requieren nombre, telefono y referencia externa.
- `delivery_fee` suma al total cobrado.
- `platform_fee` no suma al total cobrado; queda como dato operativo para informes.

## Cocina

El KDS muestra el canal del pedido:

- Mesa.
- Recogida.
- Reparto.
- Glovo.
- Uber Eats.

Tambien muestra la referencia externa y notas de entrega cuando existen.

## Pendiente para Fase 2

La Fase 2 deberia definir si la integracion se hara mediante:

- API oficial de Glovo.
- API oficial de Uber Eats.
- Middleware externo como Deliverect, Otter, Flipdish u otro.

Hasta tener credenciales y contrato de API, la operativa soportada es manual.
