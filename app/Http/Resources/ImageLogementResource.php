<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Pour vérifier l'existence du fichier

class ImageLogementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         if (is_null($this->resource)) {
            return [];
        }

        // **Important Assumption:** 'urlImage' column in 'image_logements' table
        // stores the RELATIVE PATH of the image within the 'public' disk
        // (e.g., 'images/logements/abc.jpg'), NOT binary data or a full URL.
        // Make sure your ImageLogement model/migration reflects this.

        $path = $this->urlImage;
        $imageUrl = null;

        // Construit l'URL complète seulement si le chemin existe et le fichier est accessible
        if ($path && Storage::disk('public')->exists($path)) {
             // 'asset()' génère une URL accessible depuis le web
             // 'storage/' est le lien symbolique créé par `php artisan storage:link`
            $imageUrl = asset('storage/' . $path);
        } else if ($path) {
            // Log or handle cases where the path exists in DB but not the file
            \Log::warning("Image file not found on disk for path: " . $path);
        }


        return [
            'id' => $this->id,
            // Retourne l'URL complète ou null si le fichier n'existe pas/chemin est vide
            'url' => $imageUrl,
            'taille_ko' => round($this->taille / 1024, 2), // Convertit la taille en Ko (si 'taille' est en octets)
            // 'chemin_relatif' => $this->urlImage, // Optionnel: pour le debug
        ];
    }
}
