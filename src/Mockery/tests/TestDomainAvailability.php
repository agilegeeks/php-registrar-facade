<?php
use AgileGeeks\RegistrarFacade\Mockery as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainAvailability extends BaseTestCase {

    protected function setUp() {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
    }

    protected function tearDown() {

    }

    public function test_domain_availability() {
        parent::test_domain_availability();
        
    }
}
