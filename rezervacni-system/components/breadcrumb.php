<?php

require_once __DIR__ . "/utils/links.php";

/**
 * Configuration constant mapping breadcrumb labels to their URLs.
 * * Key: The display string (label).
 * Value: The URL path for the link.
 */
define("BREADCRUMB_MAP", [
    'Home' => createLink("/index.php"),
    'Event' => createLink("/event.php"),
    'Admin' => createLink("/admin/index.php"),
    'Admin-users' => createLink("/admin/users.php"),
    'Admin-events' => createLink("/admin/events.php"),
    'Admin-users-update' => createLink("/admin/user.php"),
    'Admin-events-update' => createLink("/admin/events/update.php"),
]);

define("BREADCRUMB_ALIASES", [
    'Home' => "Domů",
    'Event' => "Událost",
    'Admin' => "Admin",
    'Admin-users' => "Uživetelé",
    'Admin-events' => "Události",
    'Admin-users-update' => "Úprava",
    'Admin-events-update' => "Událost",
]);



/**
 * Generates the HTML for the breadcrumb navigation based on a list of keys.
 *
 * This function filters the input list against the defined BREADCRUMB_MAP.
 * Keys not found in the map are ignored. The last item in the list is
 * rendered as plain text (current page), while previous items are rendered as links.
 *
 * @param string[] $items An array of strings representing the breadcrumb keys.
 * @return string The generated HTML string for the breadcrumb list.
 */
function generateBreadcrumbs(array $items): string {
    // 1. Filter the input list: Only keep items that exist in the map
    $validItems = [];

    foreach ($items as $item) {
        if (array_key_exists($item, BREADCRUMB_MAP)) {
            $validItems[] = $item;
        }
        // If the key is not in the map, it is skipped/ignored here
    }

    // 2. Early return: If no valid items exist, return an empty string
    if (empty($validItems)) {
        return '';
    }

    // 3. Build the HTML structure
    $html = '<ul class="breadcrumb">';
    $count = count($validItems);
    $currentIndex = 0;

    foreach ($validItems as $key) {
        $currentIndex++;
        $url = BREADCRUMB_MAP[$key];

        // Sanitize the output to prevent XSS
        $safeLabel = htmlspecialchars(BREADCRUMB_ALIASES[$key]);

        // Check if this is the last item in the breadcrumb trail
        if ($currentIndex === $count) {
            // Render without a link (represents the current active page)
            // Matches CSS: <li>Italy</li>
            $html .= "<li>$safeLabel</li>";
        } else {
            // Render as a clickable link
            // Matches CSS: <li><a href="...">Home</a></li>
            $html .= "<li><a href=\"$url\">$safeLabel</a></li>";
        }
    }

    $html .= '</ul>';

    $html = '<div class="breadcrumb-container">' . $html . '</div>';

    return $html;
}
?>