<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../CertHandler.php');


class TestDomainInfo extends BaseTestCase {

    function __construct() {
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\CertHandler($config);

    }

    protected function tearDown() {

    }

    public function test_get_certs() {
        $this->handler->get_certs();
        var_dump($this->handler->extendedResult);
    }

}
