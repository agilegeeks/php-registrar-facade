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

    public function test_domain_registration() {
        //parent::test_domain_availability();
        $contact_registrant = (object) array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Agile Geeks',
            'last_name' => 'Agile Geeks',
            'address1' => 'Agile Geeks',
            'address2' => 'Agile Geeks',
            'city' => 'Agile Geeks',
            'state_province' => 'Agile Geeks',
            'postal_code' => 'Agile Geeks',
            'country' => 'Agile Geeks',
            'email' => 'Agile Geeks',
            'phone' => 'Agile Geeks',
            'fax' => 'Agile Geeks'
        );

        $this->handler->register('rotld.com',
                            $registration_period=1,
                            $nameservers=array(),
                            $domain_password='d786dfhh48f',
                            $contact_registrant=$contact_registrant,
                            $contact_tech=null,
                            $contact_admin=null,
                            $contact_billing=null,
                            $extra_params=array()
                            );
    }
}
