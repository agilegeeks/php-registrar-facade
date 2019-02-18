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

    public function test_create() {
        $response = $this->handler->create_cert('Certificate-Comodo-Essential', 1);

        $this->assertTrue($response);
        $result = $this->handler->getResult();
        echo "cert id: ".$result->certid." cert orderid: ".$result->orderid;
        $this->assertTrue(is_numeric($result->orderid));
        $this->assertTrue(is_numeric($result->certid));

        $response = $this->handler->create_cert('Certificate-Comodo-Essential-BLAH', 1);
        $this->assertFalse($response);
    }
}
