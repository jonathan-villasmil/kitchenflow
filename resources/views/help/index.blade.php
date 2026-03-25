<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Ayuda - KitchenFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        .sidebar { height: calc(100vh - 4rem); position: sticky; top: 4rem; overflow-y: auto; }
        .content-section { scroll-margin-top: 5rem; }
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .content-section { page-break-before: always; }
            img { max-width: 100% !important; border: 1px solid #ddd; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- Header -->
    <header class="bg-slate-900 text-white h-16 fixed top-0 w-full z-50 flex items-center justify-between px-8 border-b border-slate-800 no-print">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center font-bold text-xl">K</div>
            <span class="text-xl font-bold tracking-tight">KitchenFlow <span class="text-orange-500">Help</span></span>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="window.print()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i>
                Descargar Manual (PDF)
            </button>
            <a href="{{ url('/admin') }}" class="text-gray-400 hover:text-white transition uppercase text-xs font-bold tracking-widest">Volver Admin</a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto flex gap-8 px-8 pt-24 pb-12">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 sidebar no-print hidden lg:block">
            <nav class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-3">Primeros Pasos</p>
                <a href="#introduccion" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">✨ Introducción</a>
                <a href="#acceso" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">🔑 Acceso y PIN</a>
                <a href="#configuracion" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">⚙️ Configuración inicial</a>
                
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 px-3">Ventas - TPV</p>
                <a href="#mesas" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">🪑 Gestión de Mesas</a>
                <a href="#pedidos" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">🍔 Toma de Pedidos</a>
                <a href="#pagos" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">💳 Cobro y Propinas</a>
                <a href="#caja" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">💰 Cierre de Caja</a>
                
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 px-3">Operaciones</p>
                <a href="#cocina" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">🍳 Pantalla Cocina (KDS)</a>
                <a href="#inventario" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">📦 Inventario y Recetas</a>
                <a href="#fichaje" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">⏰ Fichaje de Personal</a>
                
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 px-3">Soporte</p>
                <a href="#faq" class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-orange-600 rounded-lg transition font-medium">❓ FAQs</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 main-content">
            
            <!-- Section: Introducción -->
            <section id="introduccion" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="bg-orange-100 p-3 rounded-xl text-orange-600">
                        <i class="fa-solid fa-rocket fa-2x"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-slate-800">Bienvenido a KitchenFlow</h1>
                        <p class="text-gray-500">La solución definitiva para la gestión de Restaurantes.</p>
                    </div>
                </div>
                <div class="prose prose-slate max-w-none text-gray-600 leading-relaxed">
                    <p>Este manual ha sido diseñado para que tanto gerentes como camareros y cocineros puedan dominar la herramienta en cuestión de minutos. KitchenFlow se divide en tres interfaces principales:</p>
                    <ul class="list-disc pl-5 mt-4 space-y-2">
                        <li><strong>Panel Administrativo:</strong> Gestión de productos, stock, personal e informes.</li>
                        <li><strong>TPV (Terminal Punto de Venta):</strong> Interfaz táctil para camareros y sala.</li>
                        <li><strong>KDS (Kitchen Display System):</strong> Pantalla de comandas para la cocina.</li>
                    </ul>
                </div>
            </section>

            <!-- Section: Acceso -->
            <section id="acceso" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-orange-500"></i> Acceso y Seguridad
                </h2>
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <p class="text-gray-600 mb-4">Para iniciar el día, cada usuario debe identificarse:</p>
                        <ol class="list-decimal pl-5 space-y-3 text-gray-600">
                            <li>Accede a tu URL personalizada (ej: <code>kitchenflow.test/admin</code>).</li>
                            <li>Ingresa tu Email y Contraseña.</li>
                        </ol>
                        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
                            <p class="text-sm text-blue-800 font-medium"><strong>💡 TIP: El PIN</strong></p>
                            <p class="text-xs text-blue-700">En el TPV, cada trabajador tiene un PIN único de 4 dígitos para cambiar de turno rápidamente sin cerrar la sesión principal.</p>
                        </div>
                    </div>
                    <div class="rounded-xl overflow-hidden border border-gray-200">
                        <img src="{{ asset('images/help/login_page.png') }}" onerror="this.src='https://placehold.co/600x400?text=Login+Screen'" alt="Pantalla de Login">
                    </div>
                </div>
            </section>

            <!-- Section: Configuración -->
            <section id="configuracion" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-orange-500"></i> Configuración Inicial
                </h2>
                <p class="text-gray-600 mb-6">Como Gerente, lo primero que debes hacer es configurar la identidad de tu negocio:</p>
                <div class="rounded-xl overflow-hidden border border-gray-200 mb-8">
                    <img src="{{ asset('images/help/restaurant_config.png') }}" onerror="this.src='https://placehold.co/800x400?text=Config+Restaurante'" alt="Configuración Restaurante">
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <h4 class="font-bold text-slate-800 mb-2">Identidad</h4>
                        <p class="text-xs text-gray-500 leading-tight">Sube tu logotipo y cambia el nombre para que aparezcan en los tickets de tus clientes.</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <h4 class="font-bold text-slate-800 mb-2">IVA / Impuestos</h4>
                        <p class="text-xs text-gray-500 leading-tight">Define la tasa impositiva (ej: 10% u 21%) para que el sistema calcule los precios automáticamente.</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <h4 class="font-bold text-slate-800 mb-2">Zonas</h4>
                        <p class="text-xs text-gray-500 leading-tight">Crea salas como 'Salón Principal', 'Terraza' o 'Barra' para organizar tus mesas.</p>
                    </div>
                </div>
            </section>

            <!-- Section: POS Mesas -->
            <section id="mesas" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-table-cells-large text-orange-500"></i> TPV: El Plano de Mesas
                </h2>
                <div class="rounded-xl overflow-hidden border border-gray-200 mb-8">
                    <img src="{{ asset('images/help/pos_mesas.png') }}" onerror="this.src='https://placehold.co/800x400?text=TPV+Plano+Mesas'" alt="Plano de Mesas">
                </div>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="h-8 w-8 bg-green-500 rounded flex items-center justify-center font-bold text-white shrink-0">1</div>
                            <p class="text-gray-600 text-sm italic">"Una mesa en verde significa que está libre y lista para recibir clientes."</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="h-8 w-8 bg-orange-500 rounded flex items-center justify-center font-bold text-white shrink-0">2</div>
                            <p class="text-gray-600 text-sm">Al pulsar una mesa, entrarás directamente a la pantalla de pedido para esa cuenta.</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-xs italic">
                            Si la mesa está roja, significa que tiene un pedido activo y no se puede liberar hasta que se cobre.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: Toma de Pedidos -->
            <section id="pedidos" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-orange-500"></i> Toma de Pedidos y Modificadores
                </h2>
                <div class="bg-slate-50 border border-slate-200 p-6 rounded-2xl mb-8">
                    <p class="text-sm text-slate-600 mb-4"><strong>Sigue estos pasos para un pedido perfecto:</strong></p>
                    <div class="grid md:grid-cols-2 gap-12 items-start">
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                <span class="text-gray-700 text-sm">Selecciona el producto del menú táctil.</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                <span class="text-gray-700 text-sm"><strong>Modificadores:</strong> Si el cliente pide algo especial (ej: Poco hecha), selecciona los extras o exclusiones.</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                <span class="text-gray-700 text-sm">Pulsa <strong>"Marchar 1s"</strong> para enviar a cocina los entrantes.</span>
                            </li>
                        </ul>
                        <img src="{{ asset('images/help/pos_pedidos.png') }}" onerror="this.src='https://placehold.co/400x300?text=Modulo+Modificadores'" class="rounded-lg shadow-md max-w-[300px]" alt="Carrito Modificadores">
                    </div>
                </div>
            </section>

            <!-- Section: KDS -->
            <section id="cocina" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-fire-burner text-orange-500"></i> Gestión de Cocina (KDS)
                </h2>
                <div class="rounded-xl overflow-hidden border border-gray-200 mb-8">
                    <img src="{{ asset('images/help/kds_screen.png') }}" onerror="this.src='https://placehold.co/800x400?text=KDS+Pantalla'" alt="Pantalla de Cocina">
                </div>
                <div class="prose prose-sm text-gray-600">
                    <p>Los cocineros verán los pedidos en tiempo real. Al pulsar sobre un plato o ticket, este se marcará como <strong>"LISTO"</strong>.</p>
                    <ul class="list-disc pl-5 mt-4 space-y-2">
                        <li><strong>Campana:</strong> Notifica al camarero que la mesa {{ 'X' }} está servida.</li>
                        <li><strong>Tiempos:</strong> El ticket cambia de color si lleva más de 15 minutos en espera.</li>
                    </ul>
                </div>
            </section>

            <!-- Section: Pagos -->
            <section id="pagos" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-orange-500"></i> Cobro, Propinas y Tickets
                </h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <h4 class="font-bold text-slate-800 mb-3 uppercase text-xs tracking-widest italic">Flujo de Cobro:</h4>
                            <p class="text-sm text-gray-600 leading-relaxed">Al pulsar cobrar, el sistema mostrará el total. Puedes añadir una propina personalizada (5%, 10% o monto manual) antes de seleccionar el método de pago.</p>
                        </div>
                        <div class="p-4 border-2 border-orange-100 rounded-2xl flex gap-4">
                            <div class="bg-orange-500 text-white p-3 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-print"></i>
                            </div>
                            <p class="text-xs text-gray-600 italic leading-snug">Al finalizar, pulsa <strong>"Imprimir Ticket"</strong> para obtener el recibo para el cliente. También puedes mandarlo por Email desde el panel de pedidos.</p>
                        </div>
                    </div>
                    <div>
                        <img src="{{ asset('images/help/receipt.png') }}" onerror="this.src='https://placehold.co/400x500?text=Ejemplo+Ticket'" alt="Ejemplo Ticket" class="rounded-xl shadow-lg border border-gray-200 max-h-[400px]">
                    </div>
                </div>
            </section>

            <!-- Section: Fichaje -->
            <section id="fichaje" class="content-section mb-20 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-orange-500"></i> Control de Turnos (Fichaje)
                </h2>
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 mb-4 font-medium italic">Es obligatorio fichar para poder usar el terminal.</p>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-green-500"></i>
                                <span class="text-sm font-medium">Pulsa el botón verde "Fichar Entrada" al llegar.</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-xmark text-red-500"></i>
                                <span class="text-sm font-medium">Pulsa el botón rojo "Fichar Salida" al terminar tu jornada o descanso.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex-1 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <img src="{{ asset('images/help/pin_keypad.png') }}" onerror="this.src='https://placehold.co/400x300?text=Pantalla+PIN'" alt="Teclado PIN">
                    </div>
                </div>
            </section>

            <!-- Section: FAQ -->
            <section id="faq" class="content-section bg-slate-900 text-white p-8 rounded-2xl shadow-xl overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                <h2 class="text-2xl font-bold mb-8 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-orange-500"></i> Preguntas Frecuentes y Errores
                </h2>
                <div class="space-y-6 relative z-10">
                    <div class="border-b border-slate-700 pb-4">
                        <h4 class="font-bold text-orange-400 mb-1 italic">¿Qué hago si sale el Error 419 al meter el PIN?</h4>
                        <p class="text-xs text-slate-300">Este error ocurre si la señal de internet se corta o la sesión caduca. El sistema ahora lo arregla solo haciendo un autorefresco. Solo dale a 'Volver' y reintenta.</p>
                    </div>
                    <div class="border-b border-slate-700 pb-4">
                        <h4 class="font-bold text-orange-400 mb-1 italic">Un producto no aparece en el TPV</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">Verifica en el Panel de Administración que el producto tiene marcada la opción <strong>"Disponible"</strong> y que pertenece a una categoría activa.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-orange-400 mb-1 italic">¿Cómo borro un plato que ya se cobró?</h4>
                        <p class="text-xs text-slate-300">Por seguridad, una cuenta cobrada solo puede anularse desde el panel de <strong>"Historial de Pedidos"</strong> si eres Administrador.</p>
                    </div>
                </div>
            </section>

            <footer class="mt-20 pt-8 border-t border-gray-200 text-center text-gray-400 text-sm italic">
                <p>© 2026 KitchenFlow Management System. Generado automáticamente para soporte de producción.</p>
            </footer>

        </main>
    </div>

</body>
</html>
