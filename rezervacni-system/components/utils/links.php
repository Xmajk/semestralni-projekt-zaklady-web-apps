<?php

$PREFIX = "/~hroudmi5/rezervacni-system";

function createLink($link)
{
    global $PREFIX;
    return $PREFIX . $link;
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

function create_error_link($message){
    return createLink("/500.php?".http_build_query(array("message" => $message),'',"&amp;"));
}

function redirect_to($link){
    header("Location: ".$link);
    exit();
}