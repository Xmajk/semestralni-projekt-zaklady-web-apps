<?php

/**
 * The global base URI prefix for the application.
 *
 * This variable is used to construct absolute URLs, ensuring the application
 * functions correctly regardless of the subdirectory it is hosted in.
 *
 * @var string
 */
$PREFIX = "/~hroudmi5/rezervacni-system";

/**
 * Generates an absolute URL by appending the global application prefix.
 *
 * @global string $PREFIX The application base path.
 * @param string $link The relative path (e.g., "/login.php").
 * @return string The fully qualified URL.
 */
function createLink($link)
{
    global $PREFIX;
    return $PREFIX . $link;
}

/**
 * Generates a URL pointing to the public assets directory.
 *
 * @param string $link The relative path inside the public folder.
 * @return string The full URL to the public resource.
 */
function createPublicLink($link)
{
    return createLink("/public" . $link);
}

/**
 * Generates a URL for a JavaScript file located in the scripts directory.
 *
 * @param string $script The filename of the script (e.g., "/app.js").
 * @return string The full URL to the script file.
 */
function createScriptLink($script)
{
    return createPublicLink("/scripts" . $script);
}

/**
 * Generates a URL for a CSS stylesheet located in the styles directory.
 *
 * @param string $script The filename of the stylesheet (e.g., "/main.css").
 * @return string The full URL to the CSS file.
 */
function createStylesLink($script)
{
    return createPublicLink("/styles" . $script);
}

/**
 * Generates a URL for the 500 internal server error page with a custom message.
 *
 * @param string $message The error message to be passed in the query string.
 * @return string The full URL to the error page.
 */
function create_error_link($message){
    return createLink("/500.php?" . http_build_query(array("message" => $message), '', "&amp;"));
}

/**
 * Performs an HTTP redirect to the specified URL and terminates the script.
 *
 * @param string $link The target URL for the redirection.
 * @return void
 */
function redirect_to($link){
    header("Location: " . $link);
    exit();
}

/**
 * Generates a URL for a large version of an event image.
 *
 * @param string $imagname The filename of the image.
 * @return string The full URL to the large image.
 */
function create_large_image_link($imagname){
    return createPublicLink("/imgs/events/large/" . $imagname);
}

/**
 * Generates a URL for a thumbnail version of an event image.
 *
 * @param string $imagname The filename of the image.
 * @return string The full URL to the thumbnail image.
 */
function create_small_image_link($imagname){
    return createPublicLink("/imgs/events/thumb/" . $imagname);
}

/**
 * Redirects the user to the standard database error page.
 *
 * This is a convenience wrapper around {@see redirect_to()} and {@see create_error_link()}
 * specifically for handling database connection or query failures.
 *
 * @return void
 */
function redirectToDatabaseError()
{
    redirect_to(create_error_link("Chyba databáze"));
}