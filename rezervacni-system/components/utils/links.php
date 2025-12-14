<?php

/**
 * The global base URI prefix for the application.
 *
 * This path is prepended to all generated links to ensure they work correctly
 * regardless of where the application is hosted (e.g., in a subdirectory).
 *
 * @var string
 */
$PREFIX = "/~hroudmi5/rezervacni-system";

/**
 * Creates an absolute URL by appending the application prefix.
 *
 * This function uses the global {@see $PREFIX} variable to construct
 * the full path to a resource within the application.
 *
 * @global string $PREFIX The application base path.
 * @param string $link The relative path (e.g., "/login.php").
 * @return string The fully qualified URL path.
 */
function createLink($link)
{
    global $PREFIX;
    return $PREFIX . $link;
}

/**
 * Creates a URL pointing to the public directory.
 *
 * This is a wrapper for {@see createLink()} specifically for publicly
 * accessible resources.
 *
 * @param string $link The relative path inside the public folder.
 * @return string The full URL to the public resource.
 */
function createPublicLink($link)
{
    return createLink("/public".$link);
}

/**
 * Creates a URL for a JavaScript file.
 *
 * Helper function that points specifically to the '/public/scripts' directory.
 *
 * @param string $script The filename of the script (including extension, e.g., "/app.js").
 * @return string The full URL to the script file.
 */
function createScriptLink($script)
{
    return createPublicLink("/scripts".$script);
}

/**
 * Creates a URL for a CSS stylesheet.
 *
 * Helper function that points specifically to the '/public/styles' directory.
 *
 * @param string $script The filename of the stylesheet (including extension, e.g., "/main.css").
 * @return string The full URL to the style file.
 */
function createStylesLink($script)
{
    return createPublicLink("/styles".$script);
}

/**
 * Generates a URL for the 500 error page with a specific message.
 *
 * This function builds a query string containing the error message,
 * safely encoded for use in a URL.
 *
 * @param string $message The error message to display on the 500 page.
 * @return string The full URL to the 500 error page.
 */
function create_error_link($message){
    return createLink("/500.php?".http_build_query(array("message" => $message),'',"&amp;"));
}

/**
 * Redirects the user to a new URL and terminates the script.
 *
 * This function sends a raw HTTP Location header. It is crucial that
 * no output is sent to the browser before calling this function.
 * Execution stops immediately after the header is sent.
 *
 * @param string $link The URL to redirect to.
 * @return void
 */
function redirect_to($link){
    header("Location: ".$link);
    exit();
}

/**
 * Creates a URL for a large version of an event image.
 *
 * Points to the '/public/imgs/events/large/' directory.
 *
 * @param string $imagname The filename of the image.
 * @return string The full URL to the large image.
 */
function create_large_image_link($imagname){
    return createPublicLink("/imgs/events/large/".$imagname);
}

/**
 * Creates a URL for a thumbnail version of an event image.
 *
 * Points to the '/public/imgs/events/thumb/' directory.
 *
 * @param string $imagname The filename of the image.
 * @return string The full URL to the thumbnail image.
 */
function create_small_image_link($imagname){
    return createPublicLink("/imgs/events/thumb/".$imagname);
}

function redirectToDatabaseError()
{
    redirect_to(create_error_link("Chyba databáze"));
}