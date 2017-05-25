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
        $this->assertFalse($this->handler->check_availability('google.com'));
        $this->assertTrue($this->handler->check_availability('domainthatdoesnotexistsandifitdoesbadluck.com'));
    }
}
