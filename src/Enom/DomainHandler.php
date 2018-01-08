<?php
namespace AgileGeeks\RegistrarFacade\Enom;

use \Coreproc\Enom as Enom;
use \AgileGeeks\RegistrarFacade\ApiException as ApiException;
use \AgileGeeks\RegistrarFacade\Models as Models;
use \AgileGeeks\RegistrarFacade\Helpers as Helpers;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '../../models.php');
require_once(__DIR__ . '../../DomainHandlerInterface.php');
require_once(__DIR__ . '../../BaseHandler.php');
require_once(__DIR__ . '../../ApiException.php');

class DomainHandler extends \AgileGeeks\RegistrarFacade\BaseHandler
    implements \AgileGeeks\RegistrarFacade\DomainHandlerInterface
{
    protected $enom = null;
    protected $domain = null;

    private function format_enom_error_message($e)
    {
        $errors = get_object_vars($e->getErrors());
        $error_message = '';
        foreach ($errors as $key => $val) {
            $error_message .= $val . " ";
        }
        $this->setError(trim($error_message));
    }

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function getEnomInstance()
    {
        if ($this->enom == null) {
            if (!isset($this->config['verify_ssl'])) {
                $this->config['verify_ssl'] = false;
            }
            $this->enom = new Enom\Enom($this->config['uid'], $this->config['pw'], $this->config['base_url'], $this->config['verify_ssl']);
        }

        return $this->enom;
    }

    public function getDomainInstance()
    {
        if ($this->domain == null) {
            $this->domain = new Enom\Domain($this->getEnomInstance());
        }

        return $this->domain;
    }

    public function check_availability($apex_domain)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        try {
            $result = $domain->check($sld, $tld);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        if ($result->RRPCode == 210) {
            $this->setResult(True);
        } else {
            $this->setResult(False);
        }

        return True;
    }

    public function info($apex_domain, $include_contacts = True, $include_namservers = True)
    {
        $contact_type_mapping = array(
            'admin' => 'contact_admin',
            'tech' => 'contact_tech',
            'registrant' => 'contact_registrant',
            'billing' => 'contact_billing'
        );

        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        try {
            $result = $domain->getInfo($sld, $tld);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        $domain_name = new Models\DomainNameModel();
        $domain_name->domain_name = $result->GetDomainInfo->domainname;
        $domain_name->expiration_date = strtotime($result->GetDomainInfo->status->expiration);
        $domain_name->deletion_date = strtotime($result->GetDomainInfo->status->deletebydate);
        $domain_name->registrar = $result->GetDomainInfo->status->registrar;

        //get contacts info
        if ($include_contacts === True) {
            try {
                $result = $domain->getContactInformation($sld, $tld);
            } catch (Enom\EnomApiException $e) {
                $this->format_enom_error_message($e);
                return False;
            }

            foreach ($result as $contact_type => $domain_contact) {
                $contact = new Models\ContactModel();
                $contact->organization_name = Helpers\object_to_empty_string($domain_contact->OrganizationName);
                $contact->first_name = Helpers\object_to_empty_string($domain_contact->FirstName);
                $contact->last_name = Helpers\object_to_empty_string($domain_contact->LastName);
                $contact->address1 = Helpers\object_to_empty_string($domain_contact->Address1);
                $contact->address2 = Helpers\object_to_empty_string($domain_contact->Address2);
                $contact->city = Helpers\object_to_empty_string($domain_contact->City);
                $contact->state_province = Helpers\object_to_empty_string($domain_contact->StateProvince);
                $contact->postal_code = Helpers\object_to_empty_string($domain_contact->PostalCode);
                $contact->country = Helpers\object_to_empty_string($domain_contact->Country);
                $contact->phone = Helpers\object_to_empty_string($domain_contact->Phone);
                $contact->fax = Helpers\object_to_empty_string($domain_contact->Fax);
                $contact->email = Helpers\object_to_empty_string($domain_contact->EmailAddress);
                $domain_name->{$contact_type_mapping[$contact_type]} = $contact;
            }
        }
        //finished getting contacts info

        //get nameservers info
        if ($include_namservers === True) {
            try {
                $result = $domain->getNSInformation($sld, $tld);
            } catch (Enom\EnomApiException $e) {
                $this->format_enom_error_message($e);
                return False;
            }
            if (isset($result->dns) && is_array($result->dns)) {
                foreach ($result->dns as $ns) {
                    $domain_name->nameservers[] = $ns;
                }
            }
        }
        //finished getting nameservers info

        $this->setResult($domain_name);
        return True;
    }

    public function register(
        $apex_domain,
        $registration_period = 1,
        $nameservers = array(),
        $domain_password = null,
        $contact_registrant = null,
        $contact_tech = null,
        $contact_admin = null,
        $contact_billing = null,
        $extra_params = array()
    )
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);

        $domain = $this->getDomainInstance();
        $extendedAttributes = array(
            'RegistrantOrganizationName' => $contact_registrant->organization_name,
            'RegistrantFirstName' => $contact_registrant->first_name,
            'RegistrantLastName' => $contact_registrant->last_name,
            'RegistrantAddress1' => $contact_registrant->address1,
            'RegistrantAddress2' => $contact_registrant->address2,
            'RegistrantCity' => $contact_registrant->city,
            'RegistrantStateProvince' => $contact_registrant->state_province,
            'RegistrantPostalCode' => $contact_registrant->postal_code,
            'RegistrantCountry' => $contact_registrant->country,
            'RegistrantEmailAddress' => $contact_registrant->email,
            'RegistrantPhone' => $contact_registrant->phone,
            'RegistrantFax' => $contact_registrant->fax,
            'DomainPassword' => $domain_password,
            'NumYears' => $registration_period,
            'IgnoreNSFail' => 'Yes',

        );

        for ($i = 0; $i < sizeof($nameservers); $i++) {
            if (array_key_exists($i, $nameservers)) {
                $key = $i + 1;
                $extendedAttributes['NS' . $key] = $nameservers[$i];
            }
        }

        try {
            $result = $domain->purchase($sld, $tld, $extendedAttributes);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;

    }

    public function purchase_service($apex_domain, $service, $period = 1)
    {
        // Only ID Protect supported, for now
        $supported_services = array('WPPS');

        if (!in_array($service, $supported_services)) {
            $this->setError('Invalid service');
            return False;
        }

        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();
        $extendedAttributes = array(
            'Service' => $service,
            'NumYears' => $period,
        );

        try {
            $result = $domain->purchaseService($sld, $tld, $extendedAttributes);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function update_nameservers($apex_domain, $nameservers = array())
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();
        $extendedAttributes = array();

        for ($i = 0; $i < sizeof($nameservers); $i++) {
            if (array_key_exists($i, $nameservers)) {
                $key = $i + 1;
                $extendedAttributes['NS' . $key] = $nameservers[$i];
            }
        }

        try {
            $result = $domain->ModifyNameservers($sld, $tld, $extendedAttributes);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function renew($apex_domain, $period = 1)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();
        $period = intval($period);

        if ($period < 1 || $period > 10) {
            $this->setError('Invalid period');
            return False;
        }

        try {
            $result = $domain->extend($sld, $tld, $period);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
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
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        $registrant_data = array(
            'RegistrantOrganizationName' => $contact_registrant->organization_name,
            'RegistrantFirstName' => $contact_registrant->first_name,
            'RegistrantLastName' => $contact_registrant->last_name,
            'RegistrantAddress1' => $contact_registrant->address1,
            'RegistrantAddress2' => $contact_registrant->address2,
            'RegistrantCity' => $contact_registrant->city,
            'RegistrantStateProvince' => $contact_registrant->state_province,
            'RegistrantPostalCode' => $contact_registrant->postal_code,
            'RegistrantCountry' => $contact_registrant->country,
            'RegistrantEmailAddress' => $contact_registrant->email,
            'RegistrantPhone' => $contact_registrant->phone,
            'RegistrantFax' => $contact_registrant->fax,
        );

        try {
            $result = $domain->setContactInformation($sld, $tld, $registrant_data);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function transfer($apex_domain, $authorization_key)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();
        $extra_params = array(
            'AuthInfo1' => $authorization_key
        );

        try {
            $result = $domain->transferIn($sld, $tld, $extra_params);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function set_lock($apex_domain, $lock)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        try {
            $result = $domain->setRegLock($sld, $tld, $lock);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function check_nameserver($apex_domain, $nameserver)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        try {
            $result = $domain->checkNameserver($nameserver);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }

        return True;
    }

    public function create_nameserver($apex_domain, $nameserver, $ip)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        $this->set_lock($apex_domain, '1');

        try {
            $result = $domain->registerNameserver($nameserver, $ip);
        } catch (Enom\EnomApiException $e) {
            $this->set_lock($apex_domain, '0');
            $this->format_enom_error_message($e);
            return False;
        }

        $this->set_lock($apex_domain, '0');
        return True;
    }

    public function update_nameserver($apex_domain, $nameserver, $ip, $old_ip)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        $this->set_lock($apex_domain, '1');

        try {
            $result = $domain->updateNameserver($nameserver, $old_ip, $ip);
        } catch (Enom\EnomApiException $e) {
            $this->set_lock($apex_domain, '0');
            $this->format_enom_error_message($e);
            return False;
        }

        $this->set_lock($apex_domain, '0');
        return True;
    }

    public function delete_nameserver($apex_domain, $nameserver)
    {
        list($sld, $tld) = Helpers\apex_split($apex_domain);
        $domain = $this->getDomainInstance();

        $this->set_lock($apex_domain, '1');

        try {
            $result = $domain->deleteNameserver($nameserver);
        } catch (Enom\EnomApiException $e) {
            $this->set_lock($apex_domain, '0');
            $this->format_enom_error_message($e);
            return False;
        }

        $this->set_lock($apex_domain, '0');
        return True;
    }
}
