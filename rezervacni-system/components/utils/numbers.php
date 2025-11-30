<?php

function convertIfNumber($number):?int{
    if(is_numeric($number)){
        return intval($number);
    }
    return null;
}

?>
