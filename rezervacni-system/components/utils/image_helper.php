<?php

namespace components\utils;

if (!extension_loaded('gd')) {
    die("Chyba: Pro zpracování obrázků je vyžadována PHP knihovna 'gd', která není povolena.");
}

/**
 * Zpracuje nahraný obrázek: změní velikost a uloží jej.
 *
 * @param string $source_path Dočasná cesta k nahranému souboru (tmp_name).
 * @param int $image_type Typ obrázku (konstanta IMAGETYPE_JPEG nebo IMAGETYPE_PNG).
 * @param string $target_dir Cílový adresář (musí končit lomítkem!).
 * @param string $target_filename Plný cílový název souboru (včetně přípony).
 * @param int $target_width Cílová šířka obrázku.
 * @return bool Vrací true při úspěchu, false při selhání.
 */
function processUploadedImage(string $source_path, int $image_type, string $target_dir, string $target_filename, int $target_width): bool
{
    // 1. Načtení originálního obrázku
    $original_image = null;
    if ($image_type == IMAGETYPE_JPEG) {
        $original_image = @imagecreatefromjpeg($source_path);
    } elseif ($image_type == IMAGETYPE_PNG) {
        $original_image = @imagecreatefrompng($source_path);
    }
    if (!$original_image) {
        return false;
    }

    // 2. Zjištění rozměrů
    $original_width = imagesx($original_image);
    $original_height = imagesy($original_image);

    // 3. Výpočet nové výšky se zachováním poměru stran
    $aspect_ratio = $original_height / $original_width;
    $target_height = (int)($target_width * $aspect_ratio);

    // 4. Vytvoření nového (prázdného) obrázku s cílovými rozměry
    $resized_image = imagecreatetruecolor($target_width, $target_height);

    // 5. Zachování průhlednosti pro PNG
    if ($image_type == IMAGETYPE_PNG) {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefilledrectangle($resized_image, 0, 0, $target_width, $target_height, $transparent);
    }

    // 6. Zkopírování originálního obrázku do nového s novými rozměry
    imagecopyresampled(
        $resized_image, $original_image,
        0, 0, 0, 0,
        $target_width, $target_height,
        $original_width, $original_height
    );

    // 7. Uložení finálního obrázku
    $final_path = $target_dir . $target_filename;
    $success = false;
    if ($image_type == IMAGETYPE_JPEG) {
        $success = imagejpeg($resized_image, $final_path, 90); // 90% kvalita
    } elseif ($image_type == IMAGETYPE_PNG) {
        $success = imagepng($resized_image, $final_path, 6); // 6 z 9 úrovně komprese
    }

    // 8. Uvolnění paměti
    imagedestroy($original_image);
    imagedestroy($resized_image);

    return $success;
}