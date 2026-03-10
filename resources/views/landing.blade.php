<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KitchenFlow - Sistema de Gestión para Restaurantes</title>
    
    <!-- PWA Settings -->
    <meta name="theme-color" content="#f97316"/>
    <link rel="apple-touch-icon" href="{{ asset('pwa-logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f97316' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="antialiased min-h-screen hero-pattern flex flex-col">
    <!-- Navigation -->
    <nav class="w-full bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center text-white font-bold text-xl">
                        K
                    </div>
                    <span class="font-bold text-xl text-gray-900 tracking-tight">KitchenFlow</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-gray-600 hover:text-orange-500 transition">
                        Entrar al Sistema
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <!-- Decorative blobs -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-orange-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-yellow-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
        
        <div class="max-w-4xl mx-auto text-center relative z-10 w-full">
            <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 tracking-tight mb-6 leading-tight">
                El control de tu restaurante <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-yellow-500">
                    en la palma de tu mano
                </span>
            </h1>
            
            <p class="mt-4 text-xl md:text-2xl text-gray-600 mb-10 max-w-2xl mx-auto">
                Instala nuestra Aplicación Web Progresiva (PWA) para gestionar mesas, comandas, KDS e informes desde cualquier dispositivo de forma rápida.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <!-- PWA Install Button (Initial State Hidden via JS) -->
                <button id="pwa-install-btn" class="hidden group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-200 bg-orange-500 border border-transparent rounded-full shadow-lg hover:bg-orange-600 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 w-full sm:w-auto">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Instalar App KitchenFlow
                </button>

                <!-- Fallback Button if PWA is already installed or not supported -->
                <a href="{{ url('/admin') }}" id="pwa-fallback-btn" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-orange-600 transition-all duration-200 bg-orange-50 border border-orange-200 rounded-full hover:bg-orange-100 w-full sm:w-auto">
                    Entrar al Sistema
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <div class="mt-12 text-sm text-gray-500 bg-white/60 rounded-2xl p-6 border border-gray-100 shadow-sm inline-block">
                <p class="font-medium text-gray-800 mb-2">💡 ¿No ves el botón de instalar?</p>
                <div class="text-left space-y-2">
                    <p>• Si usas <b>iOS (iPhone/iPad)</b>: Abre la web en Safari, toca el botón <em>Compartir</em> <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> abajo y selecciona "Añadir a la pantalla de inicio".</p>
                    <p>• Asegúrate de visitar la web desde <b>localhost</b> o usar <b>HTTPS</b> seguro.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-gray-500 text-sm">
        &copy; {{ date('Y') }} KitchenFlow. Todos los derechos reservados.
    </footer>

    <!-- Service Worker Script injection -->
    @laravelPwa

    <!-- Custom Logic to Handle specific "Install" Button logic -->
    <script>
        let deferredPrompt;
        const installBtn = document.getElementById('pwa-install-btn');

        // Escuchar el evento que indica que la PWA se puede instalar
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log("PWA: El evento beforeinstallprompt se disparó correctamente.");
            
            // Prevenir la mini barra inferior en móviles
            e.preventDefault();
            
            // Guardar el evento para dispararlo cuando el usuario haga clic
            deferredPrompt = e;
            
            // Mostrar el botón de instalación
            installBtn.classList.remove('hidden');
        });

        // Configurar el click en el botón
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt !== null) {
                // Mostrar el cuadro de diálogo de instalación nativo
                deferredPrompt.prompt();
                
                // Esperar a que el usuario responda
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    console.log('PWA: El usuario aceptó la instalación.');
                } else {
                    console.log('PWA: El usuario canceló la instalación.');
                }
                
                // Limpiar la variable
                deferredPrompt = null;
                // Ocultar el botón
                installBtn.classList.add('hidden');
            }
        });

        // Detectar si la app ya se instaló exitosamente
        window.addEventListener('appinstalled', () => {
            installBtn.classList.add('hidden');
            deferredPrompt = null;
            console.log('PWA: La aplicación ha sido instalada.');
        });
        
        // Comprobar si ya estamos ejecutando la app como PWA instalada
        window.addEventListener('DOMContentLoaded', () => {
            if (window.matchMedia('(display-mode: standalone)').matches) {
                console.log('PWA: La app ya se está ejecutando instalada (Standalone).');
            }
        });
    </script>
</body>
</html>
