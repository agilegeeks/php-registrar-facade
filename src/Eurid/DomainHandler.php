<?php

namespace AgileGeeks\RegistrarFacade\Eurid;

use AgileGeeks\EPP\Eurid\Client;
use AgileGeeks\EPP\Eurid\Eurid_Exception;
use AgileGeeks\RegistrarFacade\BaseHandler;
use AgileGeeks\RegistrarFacade\DomainHandlerInterface;
use AgileGeeks\RegistrarFacade\ApiException;
use AgileGeeks\RegistrarFacade\Eurid\Models;
use AgileGeeks\RegistrarFacade\Helpers;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '/models.php');

class DomainHandler extends BaseHandler
implements DomainHandlerInterface
{
    protected $client = null;
    protected $login = False;

    private function format_eurid_error_message($e)
    {
        $this->setError(trim($e->getMessage()));
    }

    public function __construct($config)
    {
        parent::__construct($config);
        $this->client = new Client(
            $host = $config['host'],
            $user = $config['user'],
            $pass = $config['pass'],
            $debug = $config['debug'],
            $port = $config['port'],
            $timeout = $config['timeout'],
            $ssl = $config['ssl'],
            $context = NULL
        );
    }

    public function login()
    {
        if ($this->login) {
            return;
        }

        if (!$this->client->login()) {
            return;
        }

        $this->login = True;
    }
    public function logout()
    {
        if (!$this->login) {
            return;
        }

        if (!$this->client->logout()) {
            return;
        }

        $this->login = False;
    }

    public function check_availability($apex_domain)
    {
        try {
            $this->login();
            $response = $this->client->checkDomains($apex_domain);
            $this->setResult($response[$apex_domain]);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        return True;
    }

    public function info($apex_domain, $include_contacts = True, $include_namservers = True, $include_ds = True)
    {
        $this->login();

        try {
            $domain_data = $this->client->domainInfo($domain = $apex_domain);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }
        // var_dump($domain_data);

        $domain_name = new Models\EuridDomainNameModel();
        $domain_name->domain_name = $domain_data->name;
        $domain_name->registration_date = strtotime($domain_data->crDate);
        $domain_name->expiration_date = strtotime($domain_data->exDate);
        $domain_name->deletion_date = strtotime($domain_data->delDate);
        $domain_name->last_update = strtotime($domain_data->upDate);
        $domain_name->statuses = array();

        if ($domain_data->onHold) $domain_name->statuses[] = 'onHold';
        if ($domain_data->quarantined) $domain_name->statuses[] = 'quarantined';
        if ($domain_data->suspended) $domain_name->statuses[] = 'suspended';
        if ($domain_data->seized) $domain_name->statuses[] = 'seized';
        if ($domain_data->delayed) $domain_name->statuses[] = 'delayed';

        $domain_name->registrant_id = $domain_data->contacts['registrant'];
        $domain_name->contact_tech_id = $domain_data->contacts['tech'];
        $domain_name->contact_onsite_id = $domain_data->contacts['onsite'];
        $domain_name->contact_billing_id = $domain_data->contacts['billing'];
        $domain_name->contact_reseller_id = $domain_data->contacts['reseller'];
        $domain_name->registrar = $domain_data->clID;

        if ($include_namservers) {
            $domain_name->nameservers = $domain_data->nameservers;
        }

        if ($include_ds) {
            $domain_name->ds_data = $domain_data->secDNS;
        }

        if ($include_contacts) {
            $contact_type_mapping = array(
                'registrant' => 'contact_registrant',
                'tech' => 'contact_tech',
                'onsite' => 'contact_onsite',
                'billing' => 'contact_billing',
                'reseller' => 'contact_reseller'

            );

            foreach ($domain_data->contacts as $ctype => $cid) {
                if ($cid == null) continue;

                try {
                    $contact_data = $this->client->contactInfo($cid);
                    //var_dump($contact_data);
                } catch (Eurid_Exception $e) {
                    $this->format_eurid_error_message($e);
                    return False;
                }

                $contact_type = $contact_type_mapping[$ctype];
                $contact = new Models\EuridContactModel();

                list($contact->first_name, $contact->last_name) = Helpers\object_to_empty_string(explode(" ", $contact_data->name, 2));
                $contact->organization_name = Helpers\object_to_empty_string($contact_data->org);
                $i = 1;

                foreach ($contact_data->street as $address) {
                    $prop = 'address' . $i;
                    $contact->{$prop} = $address;
                    $i = $i + 1;
                }

                $contact->city = Helpers\object_to_empty_string($contact_data->city);

                if (isset($contact_data->sp)) {
                    $contact->state_province = Helpers\object_to_empty_string($contact_data->sp);
                }

                $contact->postal_code = Helpers\object_to_empty_string($contact_data->pc);
                $contact->country = Helpers\object_to_empty_string($contact_data->cc);
                $contact->phone = Helpers\object_to_empty_string($contact_data->voice);
                $contact->email = Helpers\object_to_empty_string($contact_data->email);
                $contact->person_type = Helpers\object_to_empty_string($contact_data->natural_person);

                $domain_name->{$contact_type} = $contact;
            }
        }

        $this->setResult($domain_name);

        return True;
    }

    public function register(
        $apex_domain,
        $registration_period = 1,
        $nameservers = array(),
        $domain_password = null,
        $contact_registrant,
        $contact_tech = null,
        $contact_admin = null,
        $contact_billing = null,
        $extra_params = array()
    ) {

        $registrant_natural_person = false;

        if ($contact_registrant->person_type === 'p') {
            $registrant_natural_person = true;
        }

        try {
            $this->login();

            if ($contact_registrant == null) {
                throw new ApiException("Registrant contact is mandatory");
            }

            if (!is_string($contact_registrant)) {
                $contact_registrant = $this->client->createContact(
                    $contact_registrant->first_name . " " . $contact_registrant->last_name,
                    $contact_registrant->organization_name,
                    $contact_registrant->address1,
                    $contact_registrant->address2,
                    $contact_registrant->address3,
                    $contact_registrant->city,
                    $contact_registrant->state_province,
                    $contact_registrant->postal_code,
                    $contact_registrant->country,
                    $contact_registrant->phone,
                    $contact_registrant->fax,
                    $contact_registrant->email,
                    $registrant_natural_person,
                    'registrant'
                );
            }

            if ($contact_tech != null && !is_string($contact_tech)) {
                $contact_tech = $this->client->createContact(
                    $contact_tech->first_name . " " . $contact_tech->last_name,
                    $contact_tech->organization_name,
                    $contact_tech->address1,
                    $contact_tech->address2,
                    $contact_tech->address3,
                    $contact_tech->city,
                    $contact_tech->state_province,
                    $contact_tech->postal_code,
                    $contact_tech->country,
                    $contact_tech->phone,
                    $contact_tech->fax,
                    $contact_tech->email,
                    false,
                    'tech'
                );
            }

            if ($contact_billing == null) {
                throw new ApiException("Billing contact is mandatory");
            }

            if (!is_string($contact_billing)) {
                $contact_billing = $this->client->createContact(
                    $contact_billing->first_name . " " . $contact_billing->last_name,
                    $contact_billing->organization_name,
                    $contact_billing->address1,
                    $contact_billing->address2,
                    $contact_billing->address3,
                    $contact_billing->city,
                    $contact_billing->state_province,
                    $contact_billing->postal_code,
                    $contact_billing->country,
                    $contact_billing->phone,
                    $contact_billing->fax,
                    $contact_billing->email,
                    false,
                    'billing'
                );
            }

            $contact_onsite = null;

            if (isset($extra_params['contact_onsite']) && $extra_params['contact_onsite'] != null) {
                $contact_onsite = $extra_params['contact_onsite'];
                if (!is_string($contact_onsite)) {
                    $contact_onsite = $this->client->createContact(
                        $contact_onsite->first_name . " " . $contact_onsite->last_name,
                        $contact_onsite->organization_name,
                        $contact_onsite->address1,
                        $contact_onsite->address2,
                        $contact_onsite->address3,
                        $contact_onsite->city,
                        $contact_onsite->state_province,
                        $contact_onsite->postal_code,
                        $contact_onsite->country,
                        $contact_onsite->phone,
                        $contact_onsite->fax,
                        $contact_onsite->email,
                        $registrant_natural_person,
                        'onsite'
                    );
                }
            }

            $contact_reseller = null;

            if (isset($extra_params['contact_reseller']) && $extra_params['contact_reseller'] != null) {
                $contact_reseller = $extra_params['contact_reseller'];
                if (!is_string($contact_reseller)) {
                    $contact_reseller = $this->client->createContact(
                        $contact_reseller->first_name . " " . $contact_reseller->last_name,
                        $contact_reseller->organization_name,
                        $contact_reseller->address1,
                        $contact_reseller->address2,
                        $contact_reseller->address3,
                        $contact_reseller->city,
                        $contact_reseller->state_province,
                        $contact_reseller->postal_code,
                        $contact_reseller->country,
                        $contact_reseller->phone,
                        $contact_reseller->fax,
                        $contact_reseller->email,
                        false,
                        'reseller'
                    );
                }
            }

            $norm_nameservers = array();

            if (sizeof($nameservers) > 0) {
                foreach ($nameservers as $ns) {
                    if ($ns == null) {
                        continue;
                    }

                    $ip = null;

                    if (is_array($ns)) {
                        $ip = $ns[1];
                        $ns = $ns[0];
                    }

                    $norm_nameservers[] = array($ns, $ip);
                }
            }

            $this->client->createDomain(
                $apex_domain,
                $registration_period,
                $contact_registrant,
                $contact_tech,
                $contact_billing,
                $contact_onsite,
                $contact_reseller,
                $norm_nameservers
            );

            $domain_data = $this->client->domainInfo($apex_domain, False, False);
            $this->client->deleteDomain($apex_domain, $domain_data->exDate);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        return True;
    }

    public function update_nameservers($apex_domain, $nameservers = array())
    {
        $this->login();

        // Domain info to get current nameservers
        try {
            $domain_data = $this->client->domainInfo($apex_domain);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        $chg_ns_to_remove = array();
        $ns_to_add = array_diff_key($nameservers, $domain_data->nameservers);
        $ns_to_remove = array_diff_key($domain_data->nameservers, $nameservers);

        // deep check for ip changes
        foreach ($domain_data->nameservers as $ns => $data) {
            // check to see if the ns is marked for deletion
            if (!array_key_exists($ns, $nameservers)) {
                continue;
            }

            $diff1 = array_diff($nameservers[$ns]['ips'], $domain_data->nameservers[$ns]['ips']);
            $diff2 = array_diff($domain_data->nameservers[$ns]['ips'], $nameservers[$ns]['ips']);

            if (count($diff1) > 0 ||  count($diff2)) {
                // we have different ips
                // we need to submit this data to the registry
                $chg_ns_to_remove[$ns] = $domain_data->nameservers[$ns];
                $ns_to_add[$ns] = $nameservers[$ns];
            }
        }

        if (!empty($chg_ns_to_remove)) {
            try {
                $domain_data = $this->client->updateNameservers($apex_domain, array(), $chg_ns_to_remove);
            } catch (Eurid_Exception $e) {
                $this->format_eurid_error_message($e);
                return False;
            }
        }

        if (empty($ns_to_add) && empty($ns_to_remove)) {
            return True;
        }

        // Update domain nameservers
        try {
            $domain_data = $this->client->updateNameservers($apex_domain, $ns_to_add, $ns_to_remove);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }


        return True;
    }

    public function renew($apex_domain, $period = 1)
    {
        try {
            $this->login();
            $result = $this->client->domainInfo($apex_domain, $include_contacts = False, $include_namservers = False);

            $expiration_date = substr($result->exDate, 0, 10);
            $this->client->renewDomain($apex_domain, $period, $expiration_date);

            $result = $this->client->domainInfo($apex_domain, $include_contacts = False, $include_namservers = False);
            $del_date = $result->exDate;
            $result = $this->client->deleteDomain($apex_domain, $del_date);
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        return True;
    }

    public function activate($apex_domain)
    {
        return True;
    }

    public function contact_update($apex_domain, $contact_registrant)
    {
        if (!$this->info($apex_domain, $include_contacts = False, $include_namservers = False)) {
            return False;
        }
        $result = $this->getResult();
        $natural_person = False;

        if ($contact_registrant->person_type === 'p') {
            $natural_person = True;
        }

        try {
            $this->login();

            $this->client->updateContact(
                $id = $result->registrant_id,
                $name = $contact_registrant->first_name . " " . $contact_registrant->last_name,
                $organization = $contact_registrant->organization_name,
                $street1 = $contact_registrant->address1,
                $street2 = $contact_registrant->address2,
                $street3 = $contact_registrant->address3,
                $city = $contact_registrant->city,
                $state_province = $contact_registrant->state_province,
                $postal_code = $contact_registrant->postal_code,
                $country_code = $contact_registrant->country,
                $phone = $contact_registrant->phone,
                $fax = $contact_registrant->fax,
                $email = $contact_registrant->email,
                $natural_person = $natural_person
            );
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        return True;
    }

    public function transfer($apex_domain, $authorization_key, $contact_registrant = null)
    {
        $natural_person = False;

        if ($contact_registrant->person_type === 'p') {
            $natural_person = True;
        }

        try {
            $this->login();
            $registrant_id = $this->client->createContact(
                $name = $contact_registrant->first_name . " " . $contact_registrant->last_name,
                $organization = $contact_registrant->organization_name,
                $street1 = $contact_registrant->address1,
                $street2 = $contact_registrant->address2,
                $street3 = $contact_registrant->address3,
                $city = $contact_registrant->city,
                $state_province = $contact_registrant->state_province,
                $postal_code = $contact_registrant->postal_code,
                $country_code = $contact_registrant->country,
                $phone = $contact_registrant->phone,
                $fax = $contact_registrant->fax,
                $email = $contact_registrant->email,
                $contact_type = 'registrant',
                $natural_person = $natural_person
            );
            $this->client->domainTransferRequest(
                $domain = $apex_domain,
                $authInfo = $authorization_key,
                $period = '1',
                $cid = $registrant_id,
                $billing = $this->config['contact_billing'],
                $tech = $this->config['contact_tech']
            );
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return False;
        }

        return True;
    }

    public function check_balance()
    {
        try {
            $this->login();
            $balance = $this->client->checkBalance();
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return false;
        }

        $result = (object) array(
            'balance' => $balance->availableAmount
        );

        $this->setResult($result);

        return true;
    }

    public function add_dnssec($apex_domain, $ds_data)
    {
        try {
            $this->login();
            $balance = $this->client->updateDNSSEC($apex_domain, array((object) $ds_data));
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return false;
        }

        return true;
    }

    public function delete_dnssec($apex_domain, $ds_data)
    {
        try {
            $this->login();
            $balance = $this->client->updateDNSSEC($apex_domain, array(), array((object) $ds_data));
        } catch (Eurid_Exception $e) {
            $this->format_eurid_error_message($e);
            return false;
        }

        return true;
    }

    public function create_nameserver($apex_domain, $nameserver, $ip)
    { }

    public function update_nameserver($apex_domain, $nameserver, $ip, $old_ip)
    { }

    public function delete_nameserver($apex_domain, $nameserver)
    { }

    public function trade($apex_domain, $authorization_key, $contact_registrant, $period)
    { }

    public function trade_info($tid)
    { }

    public function trade_confirm($tid)
    { }

    public function get_whois_protect_info($apex_domain)
    { }
}
