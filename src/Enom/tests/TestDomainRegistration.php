<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainRegistration extends BaseTestCase {

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
    }

    protected function tearDown() {

    }

    public function test_domain_registration_success() {
        $contact_registrant = (object) array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Radu',
            'last_name' => 'Boncea',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'city' => 'Bucuresti',
            'state_province' => '',
            'postal_code' => '',
            'country' => 'Romania',
            'email' => 'radu.boncea@gmail.com',
            'phone' => '0040754074401',
            'fax' => ''
        );
        $apex_domain = $this->randomstring(60).'.com';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array(),
                                $domain_password='d786dfhh48f',
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );
        $this->assertTrue($result);
    }

    public function test_domain_registration_failure() {
        $contact_registrant = (object) array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Radu',
            'last_name' => 'Boncea',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'city' => 'Bucuresti',
            'state_province' => '',
            'postal_code' => '',
            'country' => 'Romania',
            'email' => 'radu.boncea',
            'phone' => '0040754074401',
            'fax' => ''
        );

        $this->handler->register('domainfortesting-1.com',
                                $registration_period=1,
                                $nameservers=array(),
                                $domain_password='d786dfhh48f',
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );
        $this->assertSame($this->handler->getError(),'Domain name not available');
    }
}
