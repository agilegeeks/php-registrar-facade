<?php
namespace AgileGeeks\RegistrarFacade;

class BaseHandler {
    protected $config = array();
    protected $error_message='';
    protected $error_code='';
    protected $result;

    public function __construct($config){
        $this->config = $config;
    }

    public function setError($error_message, $error_code=''){
        $this->error_message = $error_message;
        $this->error_code = $error_code;
    }
    public function getError(){
        return($this->error_message);
    }

    public function getErrorCode(){
        return($this->error_code);
    }

    public function setResult($result){
        $this->result = $result;

    }
    public function getResult(){
        return($this->result);
    }
}
?>
