<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainCreateNameserver extends BaseTestCase {

    function __construct() {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = 'unaltdomeniupecaresailreinoiesc.com';

        return;
    }

    protected function tearDown() {

    }

    public function test_domain_create_nameservers() {
        $nameserver = 'dns6.'.$this->test_apex_domain;
        $ip = '12.32.13.13';

        // $response = $this->handler->create_nameserver($this->test_apex_domain, $nameserver, $ip);
        // $result = $this->handler->getResult();
        // $this->assertTrue($response);

        $response = $this->handler->check_nameserver($this->test_apex_domain, $nameserver);
        $this->assertTrue($response);

        $result = $this->handler->getResult();
        //die(var_dump($result));
        //$this->assertSame($result->nameserver, $nameserver);
    }

    public function test_domain_update_nameservers() {
        $nameserver = 'dns6.'.$this->test_apex_domain;
        $old_ip = '42.42.42.41';
        $new_ip = '42.42.42.12';

        $response = $this->handler->update_nameserver($this->test_apex_domain, $nameserver, $new_ip, $old_ip);
        $result = $this->handler->getResult();
        $error = $this->handler->getError();

        die(var_dump($error));

        $this->assertTrue($response);
    }
}
