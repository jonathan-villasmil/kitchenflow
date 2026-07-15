# Contexto de restaurante en el panel administrativo

## Objetivo

El panel administrativo de Filament debe respetar el aislamiento multi-restaurante:

- Managers y usuarios administrativos no `super_admin` solo trabajan con su restaurante.
- El `super_admin` puede ver todos los restaurantes o seleccionar uno concreto como contexto.
- Recursos, formularios, widgets e informes deben usar el mismo criterio.

## Comportamiento

### Manager

El contexto queda forzado al `restaurant_id` del usuario. Aunque intente cambiar
la seleccion o manipular formularios, las consultas y guardados siguen usando su
restaurante asignado.

### Super admin

En la barra superior del panel aparece un selector de restaurante:

- `Todos`: muestra datos globales.
- Restaurante concreto: limita recursos, widgets e informes a ese restaurante.

La seleccion se guarda en sesion con la clave
`admin_restaurant_context_id`.

## Piezas principales

- `App\Support\AdminRestaurantContext`: define el restaurante activo del panel.
- `ScopedToRestaurant`: aplica el contexto en recursos Filament.
- `RestaurantFormScoping`: usa el contexto en formularios y selects dependientes.
- `AdminRestaurantContextController`: permite al super admin cambiar o limpiar el contexto.
- `resources/views/filament/partials/restaurant-context-selector.blade.php`: selector visual en la barra superior.

## Regla para consultas nuevas

Para modelos con `restaurant_id` directo:

```php
AdminRestaurantContext::scope(Order::query())
```

Para modelos que dependen del restaurante a traves de `order`:

```php
AdminRestaurantContext::scopeThroughOrder(OrderItem::query())
```

No usar consultas administrativas directas como:

```php
Order::query()
Customer::query()
OrderItem::query()
```

si el resultado puede aparecer en panel, widget, informe o exportacion.

## Cobertura actual

El contexto se aplica en:

- Recursos Filament con `ScopedToRestaurant`.
- Formularios y selects relacionados con restaurante.
- Dashboard y widgets de ingresos, ocupacion, clientes, platos y tiempos.
- Informe de ventas y exportacion PDF.
