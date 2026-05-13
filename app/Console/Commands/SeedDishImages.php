<?php

namespace App\Console\Commands;

use App\Models\Dish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SeedDishImages extends Command
{
    protected $signature   = 'dishes:seed-images';
    protected $description = 'Copia las imágenes generadas al storage y las asigna a los platos';

    // Mapa: nombre del plato => ruta de la imagen fuente
    private array $images = [
        'Nachos con Queso'    => 'dish_nachos',
        'Ensalada César'      => 'dish_caesar_salad',
        'Hamburguesa Clásica' => 'dish_hamburger',
        'Refresco de Cola'    => 'dish_cola',
        'Cerveza Artesanal'   => 'dish_craft_beer',
    ];

    public function handle(): int
    {
        // Directorio fuente donde están las imágenes generadas
        $sourceDir = 'C:\\Users\\Panita\\.gemini\\antigravity\\brain\\43bb4e6b-96dc-46db-8ebb-a540d7fea16c';

        // Mapa de nombres de archivo (sin timestamp, buscar por prefijo)
        $fileMap = [
            'dish_nachos'        => 'dish_nachos_1778640165134.png',
            'dish_caesar_salad'  => 'dish_caesar_salad_1778640177388.png',
            'dish_hamburger'     => 'dish_hamburger_1778640189786.png',
            'dish_cola'          => 'dish_cola_1778640206336.png',
            'dish_craft_beer'    => 'dish_craft_beer_1778640220933.png',
        ];

        // Asegurar que el directorio de destino existe
        Storage::disk('public')->makeDirectory('dishes');

        foreach ($this->images as $dishName => $imageKey) {
            $dish = Dish::where('name', $dishName)->first();

            if (!$dish) {
                $this->warn("⚠ Plato no encontrado: {$dishName}");
                continue;
            }

            $sourceFile = $sourceDir . '\\' . $fileMap[$imageKey];

            if (!file_exists($sourceFile)) {
                $this->warn("⚠ Archivo no encontrado: {$sourceFile}");
                continue;
            }

            // Copiar al storage público
            $destPath = 'dishes/' . $imageKey . '.png';
            $contents = file_get_contents($sourceFile);
            Storage::disk('public')->put($destPath, $contents);

            // Actualizar el plato
            $dish->update(['image' => $destPath]);

            $this->info("✅ {$dishName} → storage/app/public/{$destPath}");
        }

        $this->newLine();
        $this->info('¡Listo! Ejecuta "php artisan storage:link" si el enlace simbólico no existe.');

        return self::SUCCESS;
    }
}
