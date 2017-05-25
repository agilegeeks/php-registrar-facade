<?php
namespace AgileGeeks\RegistrarFacade;

class BaseHandler {
    protected $config = array();
    protected $error_message='';
    protected $result;

    public function __construct($config){
        $this->config = $config;
    }

    public function setError($error_message){
        $this->error_message = $error_message;

    }
    public function getError(){
        return($this->error_message);
    }

    public function setResult($result){
        $this->result = $result;

    }
    public function getResult(){
        return($this->result);
    }
}
?>
