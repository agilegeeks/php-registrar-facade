<?php
use AgileGeeks\RegistrarFacade\Eurid as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainInfo extends BaseTestCase {

    function __construct() {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);

        $this->test_apex_domain = 'test-info-1t8gtpzm8jnwmjnoifa4.eu';
        //return;

        $this->test_apex_domain = 'test-info-'.$this->randomstring(20).'.eu';

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

        $result = $this->handler->register($this->test_apex_domain,
                                $registration_period=1,
                                $nameservers=array(
                                                    array('ns1.'.$this->test_apex_domain,'192.162.16.101'),
                                                    array('ns2.'.$this->test_apex_domain,'192.162.16.102'),
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
    }

    protected function tearDown() {

    }

    public function test_domain_renew() {
        $period = 1;
        $response = $this->handler->renew($this->test_apex_domain, $period);
        $this->assertTrue($response);

        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        $this->assertTrue(strtotime($result->expiration_date) > (time()+31536000));
        $this->assertTrue(strtotime($result->expiration_date) < (time()+3*31536000));

        $period = 8;
        $response = $this->handler->renew($this->test_apex_domain, $period);
        $this->assertTrue($response);

        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        $this->assertTrue(strtotime($result->expiration_date) > (time()+10*31536000));
        $this->assertTrue(strtotime($result->expiration_date) < (time()+11*31536000));

        $period = 1;
        $response = $this->handler->renew($this->test_apex_domain, $period);
        $this->assertFalse($response);
        
    }
}
