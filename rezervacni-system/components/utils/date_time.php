<?php
/**
 * The default timezone identifier used for date and time operations.
 *
 * @var string
 */
const TIMEZONE = "Europe/Prague";

/**
 * Converts a date string into a DateTime object.
 *
 * This function creates a new DateTime instance based on the provided string,
 * explicitly setting the timezone to the global constant {@see TIMEZONE}.
 *
 * @param string $date The string representation of the date (e.g., "2023-10-25" or "now").
 * @return DateTime The resulting DateTime object set to the configured timezone.
 * @throws Exception If the provided date string is invalid or cannot be parsed.
 */
function convertStringToDateTime(string $date): DateTime{
    return new DateTime($date, new DateTimeZone(TIMEZONE));
}

/**
 * @throws Exception
 */
function getDateTimeNow(): DateTime{
    return new DateTime(timezone:new DateTimeZone(TIMEZONE));
}
?>