<?php
use AgileGeeks\RegistrarFacade\Rotld as Api;

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
        $this->test_apex_domain = 'rotld.ro';
        $this->contact_registrant = (object)array(
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
        $this->test_apex_domain = $this->randomstring(60) . '.ro';
        $result = $this->handler->register(
            $this->test_apex_domain,
            $registration_period = 1,
            $nameservers = array('ns1.x.com', 'ns2.x.com'),
            $domain_password = 'G0odPasswd21$',
            $contact_registrant = $this->contact_registrant,
            $contact_tech = null,
            $contact_admin = null,
            $contact_billing = null,
            $extra_params = array()
        );
    }

    protected function tearDown()
    {
    }

    public function test_contact_update()
    {
        $response = $this->handler->info($this->test_apex_domain, $include_contacts = True);
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        $cid = $result->registrant_id;

        $contact_data = array(
            'cid' => $cid,
            'address1' => 'Street test nr.12'
        );
        $response = $this->handler->contact_update($this->test_apex_domain, $contact_data);
        $this->assertTrue($response);
        
        $this->handler->info($this->test_apex_domain, $include_contacts = True);
        $result = $this->handler->getResult();
        $this->assertEquals($result->contact_registrant->address1, $contact_data['address1']);
    }
}
