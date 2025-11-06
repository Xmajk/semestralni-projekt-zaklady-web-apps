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

function createPublicLink($link)
{
    return createLink("/public".$link);
}

function createScriptLink($script)
{
    return createPublicLink("/scripts".$script);
}

function createStylesLink($script)
{
    return createPublicLink("/styles".$script);
}