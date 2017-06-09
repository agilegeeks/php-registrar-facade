<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../DomainHandler.php');


class TestDomainInfo extends BaseTestCase {

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\DomainHandler($config);
        $this->test_apex_domain = '0t62ysxwzklmndfv8ocf0crrcp1739043671z4yzpkmcx186pklqwdh92jh6.com';
        return;
        $contact_registrant = (object) array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Peter',
            'last_name' => 'Griffin',
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
        $this->test_apex_domain = $this->randomstring(60).'.com';
        $result = $this->handler->register($this->test_apex_domain,
                                $registration_period=5,
                                $nameservers=array(),
                                $domain_password=$this->randomstring(16),
                                $contact_registrant=$contact_registrant,
                                $contact_tech=null,
                                $contact_admin=null,
                                $contact_billing=null,
                                $extra_params=array()
                            );
    }

    protected function tearDown() {

    }

    public function test_domain_info() {
        $this->assertFalse($this->handler->info('domainthatdoesnotexistsandifitdoesbadluck.com'));

        $response = $this->handler->info($this->test_apex_domain);
        $this->assertTrue($response);

        $result = $this->handler->getResult();
        $this->assertSame($result->domain_name, $this->test_apex_domain);

    }
}
