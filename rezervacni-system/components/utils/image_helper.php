<?php

namespace components\utils;

if (!extension_loaded('gd')) {
    die("ERROR: GD extension is not loaded");
}

/**
 * Processes, resizes, and saves an uploaded image.
 *
 * This function takes a temporary source file, resizes it to a specific width
 * while maintaining the original aspect ratio, and saves it to a target directory.
 * It includes specific handling for PNG transparency.
 *
 * @param string $source_path     The temporary path to the uploaded file (typically $_FILES['x']['tmp_name']).
 * @param int    $image_type      The image type constant (e.g., IMAGETYPE_JPEG or IMAGETYPE_PNG).
 * @param string $target_dir      The destination directory (must end with a trailing slash '/').
 * @param string $target_filename The full destination filename (including the file extension).
 * @param int    $target_width    The desired width of the output image in pixels.
 *
 * @return bool Returns true on success, false on failure.
 */
function processUploadedImage(string $source_path, int $image_type, string $target_dir, string $target_filename, int $target_width): bool
{
    // 1. Load the original image
    $original_image = null;
    if ($image_type == IMAGETYPE_JPEG) {
        $original_image = @imagecreatefromjpeg($source_path);
    } elseif ($image_type == IMAGETYPE_PNG) {
        $original_image = @imagecreatefrompng($source_path);
    }

    // Check if image loading failed
    if (!$original_image) {
        return false;
    }

    // 2. Get original dimensions
    $original_width = imagesx($original_image);
    $original_height = imagesy($original_image);

    // 3. Calculate new height maintaining aspect ratio
    $aspect_ratio = $original_height / $original_width;
    $target_height = (int)($target_width * $aspect_ratio);

    // 4. Create a new (empty) image with target dimensions
    $resized_image = imagecreatetruecolor($target_width, $target_height);

    // 5. Preserve transparency for PNG
    if ($image_type == IMAGETYPE_PNG) {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefilledrectangle($resized_image, 0, 0, $target_width, $target_height, $transparent);
    }

    // 6. Resample and copy original image to new canvas
    imagecopyresampled(
        $resized_image, $original_image,
        0, 0, 0, 0,
        $target_width, $target_height,
        $original_width, $original_height
    );

    // 7. Save the final image
    $final_path = $target_dir . $target_filename;
    $success = false;
    if ($image_type == IMAGETYPE_JPEG) {
        $success = imagejpeg($resized_image, $final_path, 90); // 90% quality
    } elseif ($image_type == IMAGETYPE_PNG) {
        $success = imagepng($resized_image, $final_path, 6); // Compression level 6 (0-9)
    }

    // 8. Free up memory
    imagedestroy($original_image);
    imagedestroy($resized_image);

    return $success;
}