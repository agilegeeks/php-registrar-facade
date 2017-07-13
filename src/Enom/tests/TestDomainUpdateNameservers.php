<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainUpdateNameservers extends BaseTestCase {

    function __construct() {
        $this->contact_registrant = null;
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = 'jump-test-domain.com';

        return;

        // $this->contact_registrant = (object) array(
        //     'organization_name' => 'Agile Geeks',
        //     'first_name' => 'Radu',
        //     'last_name' => 'Boncea',
        //     'address1' => 'Agile Geeks Street',
        //     'address2' => '',
        //     'city' => 'Bucuresti',
        //     'state_province' => '',
        //     'postal_code' => '',
        //     'country' => 'RO',
        //     'email' => 'radu.boncea@gmail.com',
        //     'phone' => '0040754074401',
        //     'fax' => ''
        // );
        //
        // $this->test_apex_domain = $this->randomstring(60).'.com';
        // $result = $this->handler->register($this->test_apex_domain,
        //                         $registration_period=5,
        //                         $nameservers=array(),
        //                         $domain_password=$this->randomstring(16),
        //                         $contact_registrant=$this->contact_registrant,
        //                         $contact_tech=null,
        //                         $contact_admin=null,
        //                         $contact_billing=null,
        //                         $extra_params=array()
        //                     );
        // sleep(2);
    }

    protected function tearDown() {

    }

    public function test_domain_update_nameservers() {

        $nameservers = array('ns1.x.ro','ns2.x.ro');
        $response = $this->handler->update_nameservers($this->test_apex_domain, $nameservers);
        $this->assertTrue($response);

        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        $this->assertSame($result->nameservers,$nameservers);

        $nameservers = array('ns3.x.ro','ns4.x.ro','ns5.x.ro','ns6.x.ro');
        $response = $this->handler->update_nameservers($this->test_apex_domain, $nameservers);
        $this->assertTrue($response);

        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $this->assertTrue($response);
        $result = $this->handler->getResult();
        $this->assertSame($result->nameservers,$nameservers);

        $nameservers = array();
        $response = $this->handler->update_nameservers($this->test_apex_domain, $nameservers);
        $this->assertTrue($response);
        $response = $this->handler->info(
            $this->test_apex_domain, $include_contacts=False, $include_namservers=True
        );
        $result = $this->handler->getResult();
        $this->assertSame($result->nameservers,$nameservers);

    }


}
