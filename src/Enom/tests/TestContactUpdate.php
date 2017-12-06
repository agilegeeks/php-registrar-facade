<?php
use AgileGeeks\RegistrarFacade\Enom as Api;

require_once(__DIR__ . '../../../BaseTestCase.php');
require_once(__DIR__ . '../../DomainHandler.php');

class TestContactUpdate extends BaseTestCase
{
    function __construct()
    {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null)
    {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = 'jump-test-domain.com';
        $this->contact_registrant = (object)array(
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

        $this->test_apex_domain = $this->randomstring(60) . '.com';
        $result = $this->handler->register(
            $this->test_apex_domain,
            $registration_period = 5,
            $nameservers = array(),
            $domain_password = $this->randomstring(16),
            $contact_registrant = $this->contact_registrant,
            $contact_tech = null,
            $contact_admin = null,
            $contact_billing = null,
            $extra_params = array()
        );
        sleep(2);
    }

    protected function tearDown()
    {
    }

    public function test_contact_update()
    {
        $contact_data = (object)array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Radu',
            'last_name' => 'Boncea',
            'address1' => 'Test adress nr.12',
            'address2' => '',
            'city' => 'Bucuresti',
            'state_province' => '',
            'postal_code' => '',
            'country' => 'RO',
            'email' => 'radu.boncea@gmail.com',
            'phone' => '0040754074401',
            'fax' => ''
        );
        $response = $this->handler->contact_update($this->test_apex_domain, $contact_data);
        $this->assertTrue($response);
        
        $this->handler->info($this->test_apex_domain, $include_contacts = True);
        $result = $this->handler->getResult();
        $this->assertEquals($result->contact_registrant->address1, $contact_data->address1);
    }
}
