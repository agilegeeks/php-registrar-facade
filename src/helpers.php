<?php
namespace AgileGeeks\RegistrarFacade\Helpers;

function apex_split($apex_domain){
    return explode(".", $apex_domain, 2);
}

function object_to_empty_string($object){
    if (is_object($object)) {
        return '';
    }
    return $object;
}
?>
