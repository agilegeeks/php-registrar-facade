<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainAvailability extends BaseTestCase {

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
    }

    protected function tearDown() {

    }

    public function test_domain_availability() {
        $response = $this->handler->check_availability('google.com');
        $this->assertTrue($response);
        $this->assertFalse($this->handler->getResult());
        $response = $this->handler->check_availability('domainthatdoesnotexistsandifitdoesbadluck.com');
        $this->assertTrue($response);
        $this->assertTrue($this->handler->getResult());
    }
}
