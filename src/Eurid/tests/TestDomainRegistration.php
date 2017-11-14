<?php
use AgileGeeks\RegistrarFacade\Eurid as Api;
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
            'first_name' => 'John',
            'last_name' => 'Doe',
            'organization_name' => '',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'address3' => 'fffff',
            'city' => 'Bucuresti',
            'postal_code' => '672355',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => '',
        );

        $apex_domain = $this->randomstring(60).'.eu';
        echo $apex_domain;
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array(
                                                    array('ns1.'.$apex_domain,'192.162.16.101'),
                                                    array('ns2.'.$apex_domain,'192.162.16.102'),
                                                    array('ns.alfa.com',null)
                                                ),
                                $domain_password=null,
                                $contact_registrant=$contact_registrant,
                                $contact_tech='c504540',
                                $contact_admin=null,
                                $contact_billing='c503024',
                                $extra_params=array(
                                    'contact_onsite'=>$contact_registrant,
                                    'contact_reseller'=>$contact_registrant
                                )
                            );
        $this->assertTrue($result);


        $apex_domain = 'a-'.$this->randomstring(60).'.eu';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array(
                                                'ns.alfa.com',
                                                'ns.beta.com'
                                                ),
                                $domain_password=null,
                                $contact_registrant=$contact_registrant,
                                $contact_tech='c504540',
                                $contact_admin=null,
                                $contact_billing='c503024',
                                $extra_params=array(
                                    'contact_onsite'=>$contact_registrant,
                                    'contact_reseller'=>$contact_registrant
                                )
                            );
        $this->assertTrue($result);

    }

    public function test_domain_registration_failure() {
        $contact_registrant = (object) array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'organization_name' => '',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'address3' => 'fffff',
            'city' => 'Bucuresti',
            'postal_code' => '672355',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => '',
        );

        $apex_domain = 'test.eu';
        $result = $this->handler->register($apex_domain,
                                $registration_period=1,
                                $nameservers=array('ns1.x.com','ns2.x.com'),
                                $domain_password=null,
                                $contact_registrant=$contact_registrant,
                                $contact_tech='c504540',
                                $contact_admin=null,
                                $contact_billing='c503024',
                                $extra_params=array(
                                    'contact_onsite'=>$contact_registrant,
                                    'contact_reseller'=>$contact_registrant
                                )
                            );

        $this->assertSame($this->handler->getError(),'Object exists: The requested domain name is not available.');

    }
}
