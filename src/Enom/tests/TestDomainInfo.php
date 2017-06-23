<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainInfo extends BaseTestCase {

    function __construct() {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = 'jump-test-domain.com';


        $this->contact_registrant = (object) array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Radu',
            'last_name' => 'Boncea',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'city' => 'Bucuresti',
            'state_province' => '',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'radu.boncea@gmail.com',
            'phone' => '0040754074401',
            'fax' => ''
        );

        return;

        $this->test_apex_domain = $this->randomstring(60).'.com';
        $result = $this->handler->register($this->test_apex_domain,
                                $registration_period=5,
                                $nameservers=array(),
                                $domain_password=$this->randomstring(16),
                                $contact_registrant=$this->contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );
        sleep(2);
    }

    protected function tearDown() {

    }

    public function test_domain_info() {
        $this->assertFalse($this->handler->info('domainthatdoesnotexistsandifitdoesbadluck.com'));

        $response = $this->handler->info($this->test_apex_domain, $include_contacts=True);
        $this->assertTrue($response);

        $result = $this->handler->getResult();
        $this->assertSame($result->domain_name, $this->test_apex_domain);
        $this->assertSame(strtolower($result->contact_registrant->organization_name), strtolower($this->contact_registrant->organization_name));
        $this->assertSame(strtolower($result->contact_registrant->first_name), strtolower($this->contact_registrant->first_name));
        $this->assertSame(strtolower($result->contact_registrant->last_name), strtolower($this->contact_registrant->last_name));
        $this->assertSame(strtolower($result->contact_registrant->address1), strtolower($this->contact_registrant->address1));
        $this->assertSame(strtolower($result->contact_registrant->address2), strtolower($this->contact_registrant->address2));
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
