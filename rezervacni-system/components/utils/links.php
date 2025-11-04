<?php

$PREFIX = "/~hroudmi5/rezervacni-system";

function createLink($link)
{
    global $PREFIX;
    return $PREFIX . $link;
}

function create_error_link($message){
    return createLink("/error.php?message=".$message);
}