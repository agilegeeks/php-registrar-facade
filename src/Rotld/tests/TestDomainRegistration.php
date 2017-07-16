<?php
use AgileGeeks\RegistrarFacade\Rotld as Api;
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
            'person_type' => 'p',
            'cnp_fiscal_code' => '1876972634324',
            'registration_number' => '',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'address3' => '',
            'city' => 'Bucuresti',
            'state_province' => 'Bucuresti',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => ''
        );
        $apex_domain = $this->randomstring(60).'.ro';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array('ns1.x.com','ns2.x.com'),
                                $domain_password='G0odPasswd21$',
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );

        //echo $this->handler->getError();
        $this->assertTrue($result);

    }

    public function test_domain_reservation_success() {
        $contact_registrant = (object) array(
            'person_type' => 'p',
            'cnp_fiscal_code' => '1876972634324',
            'registration_number' => '',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'address3' => '',
            'city' => 'Bucuresti',
            'state_province' => 'Bucuresti',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => ''
        );
        $apex_domain = $this->randomstring(60).'.ro';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array('ns1.x.com','ns2.x.com'),
                                $domain_password='G0odPasswd21$',
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array('reservation'=>true)
                            );

        //echo $this->handler->getError();
        $this->assertTrue($result);

    }

    public function test_domain_registration_failure() {
        $contact_registrant = (object) array(
            'person_type' => 'p',
            'cnp_fiscal_code' => '1876972634324',
            'registration_number' => '',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'address3' => '',
            'city' => 'Bucuresti',
            'state_province' => 'Bucuresti',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => ''
        );
        $apex_domain = $this->randomstring(60).'.ro';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array('ns1.x.com','ns2.x.com'),
                                $domain_password='G0odPasswd21$',
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );

        $this->assertTrue($result);
        echo $this->handler->getError();

    }
}
