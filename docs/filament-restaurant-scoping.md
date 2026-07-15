# Aislamiento por restaurante en formularios de Filament

## Contexto

KitchenFlow es multi-restaurante. Los listados de Filament ya se filtran con
`ScopedToRestaurant`, pero los formularios tambien deben impedir que un usuario
no super admin seleccione o guarde datos asociados a otro restaurante.

## Cambio aplicado

Se anadio `App\Filament\Resources\Concerns\RestaurantFormScoping` como helper
compartido para:

- Mostrar solo el restaurante del usuario cuando no es `super_admin`.
- Permitir a `super_admin` elegir entre todos los restaurantes.
- Filtrar selects dependientes por restaurante, como mesas, clientes,
  categorias, grupos de modificadores, empleados, inventario y proveedores.
- Forzar `restaurant_id` al restaurante del usuario en guardado mediante
  `ForcesRestaurantFormData`.

## Recursos cubiertos

Se revisaron formularios de platos, categorias de menu, grupos de modificadores,
modificadores, mesas, zonas, reservas, pedidos, usuarios, clientes, empleados,
turnos, fichajes, inventario, proveedores, movimientos de stock y cajas.

## Regla para nuevos formularios

Cuando un recurso tenga `restaurant_id`, usar:

```php
RestaurantFormScoping::restaurantSelect()
```

Y en las paginas `Create*` / `Edit*` del recurso:

```php
use App\Filament\Resources\Concerns\ForcesRestaurantFormData;

class CreateExample extends CreateRecord
{
    use ForcesRestaurantFormData;
}
```

Cuando un selector apunte a un modelo que pertenece a restaurante, filtrar la
relacion:

```php
->relationship('employee', 'first_name',
    modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
)
```

Para opciones dependientes del restaurante seleccionado:

```php
->options(fn ($get) =>
    Table::where('restaurant_id', RestaurantFormScoping::selectedRestaurantId($get('restaurant_id')))
        ->pluck('number', 'id')
)
```

## Nota operativa

El filtrado visual evita errores de uso normal. La proteccion importante es
`ForcesRestaurantFormData`, porque evita que un manager cambie `restaurant_id`
manipulando la peticion del formulario.
