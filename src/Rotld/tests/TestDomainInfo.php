<?php
use AgileGeeks\RegistrarFacade\Rotld as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainInfo extends BaseTestCase {

    function __construct() {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = 'rotld.ro';


        $this->contact_registrant = (object) array(
            'person_type' => 'p',
            'cnp_fiscal_code' => '1876972634324',
            'registration_number' => '',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => 'Agile Geeks Street',
            'address2' => 'Line 2',
            'address3' => 'Line 3',
            'city' => 'Bucuresti',
            'state_province' => 'Bucuresti',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'johndoe@gmail.com',
            'phone' => '+40.1222227',
            'fax' => ''
        );


        $this->test_apex_domain = $this->randomstring(60).'.ro';
        $result = $this->handler->register($this->test_apex_domain,
                                $registration_period=1,
                                $nameservers=array('ns1.x.com','ns2.x.com'),
                                $domain_password='G0odPasswd21$',
                                $contact_registrant=$this->contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );
    }

    protected function tearDown() {

    }

    public function test_domain_info() {
        $this->assertFalse($this->handler->info('domainthatdoesnotexistsandifitdoesbadluck.ro'));
        $this->assertSame($this->handler->getError(),"DOMAIN NOT FOUND");

        $response = $this->handler->info($this->test_apex_domain, $include_contacts=True);
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        var_dump($result);
        $this->assertSame($result->domain_name, $this->test_apex_domain);
        $this->assertSame(strtolower($result->contact_registrant->organization_name), '');
        $this->assertSame(strtolower($result->contact_registrant->first_name), strtolower($this->contact_registrant->first_name));
        $this->assertSame(strtolower($result->contact_registrant->last_name), strtolower($this->contact_registrant->last_name));
        $this->assertSame(strtolower($result->contact_registrant->address1), strtolower($this->contact_registrant->address1));
        $this->assertSame(strtolower($result->contact_registrant->address2), strtolower($this->contact_registrant->address2));
        $this->assertSame(strtolower($result->contact_registrant->address3), strtolower($this->contact_registrant->address3));
        $this->assertSame(strtolower($result->contact_registrant->city), strtolower($this->contact_registrant->city));
        $this->assertSame(strtolower($result->contact_registrant->state_province), strtolower($this->contact_registrant->state_province));
        $this->assertSame(strtolower($result->contact_registrant->postal_code), strtolower($this->contact_registrant->postal_code));
        $this->assertSame(strtolower($result->contact_registrant->country), strtolower($this->contact_registrant->country));
        $this->assertSame(strtolower($result->contact_registrant->email), strtolower($this->contact_registrant->email));


        $response = $this->handler->info($this->test_apex_domain, $include_contacts=False);
        $this->assertTrue($response);

        $result = $this->handler->getResult();
        $this->assertSame($result->domain_name, $this->test_apex_domain);

        $this->assertSame($result->contact_registrant, null);
        $this->assertSame($result->contact_tech, null);
        $this->assertSame($result->contact_billing, null);
        $this->assertSame($result->contact_admin, null);
    }

    public function test_domain_info_ns() {
        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $result = $this->handler->getResult();
        $this->assertEquals(2, sizeof($result->nameservers));
    }
}
