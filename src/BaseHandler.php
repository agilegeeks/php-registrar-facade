<?php
namespace AgileGeeks\RegistrarFacade;

class BaseHandler {
    protected $config = array();

    public function __construct($config){
        $this->config = $config;
    }
}
?>
