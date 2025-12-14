<?php

namespace components\utils;

if (!extension_loaded('gd')) {
    die("ERROR: GD extension is not loaded");
}

/**
 * Processes, resizes, and saves an uploaded image file.
 *
 * This function handles the resizing of an image to a specified width while
 * automatically calculating the height to maintain the original aspect ratio.
 * It supports JPEG and PNG formats and preserves transparency for PNG images.
 *
 * @param string $source_path     The absolute path to the temporary source file (e.g., from $_FILES).
 * @param int    $image_type      The GD image type constant (IMAGETYPE_JPEG or IMAGETYPE_PNG).
 * @param string $target_dir      The destination directory path (must include trailing slash).
 * @param string $target_filename The name of the file to save (including extension).
 * @param int    $target_width    The desired width of the resized image in pixels.
 *
 * @return bool True if the image was successfully processed and saved, false otherwise.
 */
function processUploadedImage(string $source_path, int $image_type, string $target_dir, string $target_filename, int $target_width): bool
{
    $original_image = null;
    if ($image_type == IMAGETYPE_JPEG) {
        $original_image = @imagecreatefromjpeg($source_path);
    } elseif ($image_type == IMAGETYPE_PNG) {
        $original_image = @imagecreatefrompng($source_path);
    }

    if (!$original_image) {
        return false;
    }

    $original_width = imagesx($original_image);
    $original_height = imagesy($original_image);

    $aspect_ratio = $original_height / $original_width;
    $target_height = (int)($target_width * $aspect_ratio);

    $resized_image = imagecreatetruecolor($target_width, $target_height);

    if ($image_type == IMAGETYPE_PNG) {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefilledrectangle($resized_image, 0, 0, $target_width, $target_height, $transparent);
    }

    imagecopyresampled(
        $resized_image, $original_image,
        0, 0, 0, 0,
        $target_width, $target_height,
        $original_width, $original_height
    );

    $final_path = $target_dir . $target_filename;
    $success = false;
    if ($image_type == IMAGETYPE_JPEG) {
        $success = imagejpeg($resized_image, $final_path, 90);
    } elseif ($image_type == IMAGETYPE_PNG) {
        $success = imagepng($resized_image, $final_path, 6);
    }

    imagedestroy($original_image);
    imagedestroy($resized_image);

    return $success;
}