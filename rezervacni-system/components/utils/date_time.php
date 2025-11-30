<?php
    const TIMEZONE = "Europe/Prague";
    function convertStringToDateTime(string $date): DateTime{
        return new DateTime($date, new DateTimeZone(TIMEZONE));
    }
?>