# Politica de documentacion

Desde este punto, cada cambio funcional o de seguridad debe quedar reflejado en dos niveles de documentacion cuando aplique:

1. Documentacion tecnica en `docs/`.
2. Manual de usuario en `resources/views/help/index.blade.php`.

## Cuando actualizar `docs/`

Actualizar la documentacion tecnica cuando el cambio afecte a:

- Reglas de seguridad.
- Aislamiento por restaurante.
- Permisos, roles o acceso a paneles.
- Validaciones de negocio.
- Flujos de caja, pedidos, cocina, inventario, turnos o informes.
- Configuracion de servicios externos como Reverb, broadcasting o colas.

La documentacion tecnica debe explicar:

- Que problema se corrige o que regla se define.
- Donde aplica.
- Como se comporta el sistema.
- Que pruebas cubren el cambio.
- Que queda pendiente si hay riesgos o mejoras posteriores.

## Cuando actualizar el manual de usuario

Actualizar el manual de usuario cuando el cambio afecte a lo que ve o hace una persona en la aplicacion:

- Gerentes.
- Super administradores.
- Camareros.
- Cajeros.
- Cocineros.
- Personal con acceso al TPV, KDS o panel administrativo.

El manual debe explicar el cambio con lenguaje operativo, sin depender de nombres internos de codigo.

## Regla de trabajo

A partir de ahora, antes de dar por cerrado un cambio, se debe comprobar si necesita documentacion tecnica, manual de usuario o ambas cosas.

Si el cambio es solo interno y no modifica reglas ni uso visible, basta con indicarlo en el cierre del trabajo.
