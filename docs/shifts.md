# Turnos y horarios nocturnos

Este documento define las reglas actuales para la gestion de turnos en KitchenFlow.

## Restaurante del turno

- El restaurante del turno no se toma del formulario manualmente.
- Al crear o editar un turno, el sistema calcula `restaurant_id` desde el empleado seleccionado.
- Si hay un restaurante activo en el panel administrativo, el empleado seleccionado debe pertenecer a ese mismo restaurante.
- Si el empleado no pertenece al restaurante activo, el formulario se bloquea con un error de validacion.

Esto evita crear turnos cruzados entre locales desde Filament.

## Solapamiento de turnos

Un empleado no puede tener dos turnos que se solapen en fecha y hora.

La validacion se aplica al crear y editar turnos. Al editar, el turno actual se ignora para que pueda guardarse sin chocarse consigo mismo.

Reglas:

- Dos turnos se consideran solapados si comparten cualquier tramo real de tiempo.
- Dos turnos contiguos estan permitidos si uno termina exactamente cuando empieza el siguiente.
- La validacion es por empleado, no por restaurante completo.

Ejemplo permitido:

- Turno A: `10:00 - 14:00`
- Turno B: `14:00 - 18:00`

Ejemplo bloqueado:

- Turno A: `10:00 - 14:00`
- Turno B: `13:30 - 18:00`

## Turnos nocturnos

Los turnos nocturnos estan permitidos.

La fecha del turno representa el dia de inicio. Si la hora de fin es menor o igual que la hora de inicio, el sistema interpreta que el turno termina al dia siguiente.

Ejemplo:

- Fecha: `2026-07-28`
- Inicio: `22:00`
- Fin: `02:00`

El sistema interpreta este turno como:

- Inicio real: `2026-07-28 22:00`
- Fin real: `2026-07-29 02:00`

Por esta regla, otro turno del mismo empleado el dia siguiente de `01:00 - 05:00` se bloquea porque se solapa con el turno nocturno anterior.

Un turno del dia siguiente de `02:00 - 06:00` se permite porque empieza justo cuando termina el turno nocturno anterior.

## Pruebas cubiertas

La cobertura actual valida que:

- El restaurante del turno se deriva del empleado seleccionado.
- Se rechaza un empleado de otro restaurante cuando hay restaurante activo en el panel.
- Se rechazan turnos solapados para el mismo empleado.
- Se permiten turnos contiguos.
- Al editar, el turno actual no se cuenta como solapamiento.
- Se permiten turnos nocturnos.
- Se bloquean solapamientos contra turnos nocturnos al dia siguiente.
- Se permiten turnos contiguos despues de un turno nocturno.

## Manual de usuario

La regla operativa para gerentes queda reflejada tambien en el manual de ayuda de la aplicacion, dentro de la seccion de planificacion de turnos.

El objetivo es que el usuario entienda que:

- El turno pertenece al restaurante del empleado seleccionado.
- No puede planificar dos turnos solapados para el mismo empleado.
- Los turnos nocturnos estan permitidos cuando la hora de fin cae al dia siguiente.

## Conexion con fichajes reales

Los fichajes reales se conectan con los turnos planificados mediante `clockings.shift_id`.

Cuando un empleado ficha entrada desde el TPV:

- El sistema busca el turno planificado mas cercano de ese empleado.
- La busqueda revisa el dia del fichaje, el dia anterior y el dia siguiente para soportar turnos nocturnos.
- Si encuentra un turno dentro de la ventana operativa permitida, guarda ese turno en el fichaje.
- El turno pasa de `scheduled` a `confirmed`.

Cuando el empleado ficha salida:

- El sistema guarda `clocked_out_at`.
- Calcula `total_minutes`.
- Si el fichaje esta enlazado a un turno, el turno pasa a `completed`.

El sistema no bloquea el fichaje aunque el empleado llegue tarde, salga antes o no tenga turno planificado. La prioridad es registrar la realidad operativa.

## Desviaciones calculadas

Cada fichaje puede calcular:

- `minutes_late`: minutos de retraso respecto a la hora planificada de inicio.
- `minutes_early_departure`: minutos de salida anticipada respecto a la hora planificada de fin.
- `attendance_status`: estado operativo del fichaje.

Estados posibles:

- `matched`: fichaje enlazado y sin desviaciones.
- `open`: fichaje abierto y enlazado.
- `open_late`: fichaje abierto con entrada tarde.
- `late`: entrada tarde.
- `left_early`: salida anticipada.
- `late_and_left_early`: entrada tarde y salida anticipada.
- `unplanned`: fichaje sin turno planificado enlazado.

En el panel administrativo de fichajes se muestra el turno asociado y el estado operativo.

## Pendiente recomendado

Esta regla cubre la conexion basica entre planificacion y fichaje real. Como mejora posterior, conviene crear informes especificos de puntualidad, horas extra, ausencias y cierres incompletos.
