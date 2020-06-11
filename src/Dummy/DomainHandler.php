<?php
namespace AgileGeeks\RegistrarFacade\Dummy;

use \Coreproc\Enom as Enom;
use \AgileGeeks\RegistrarFacade\Models as Models;
use \AgileGeeks\RegistrarFacade\Helpers as Helpers;
use \AgileGeeks\RegistrarFacade\BaseHandler as BaseHandler;
use \AgileGeeks\RegistrarFacade\DomainHandlerInterface as DomainHandlerInterface;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '../../models.php');
require_once(__DIR__ . '../../DomainHandlerInterface.php');
require_once(__DIR__ . '../../BaseHandler.php');
require_once(__DIR__ . '../../ApiException.php');

class DomainHandler extends BaseHandler implements DomainHandlerInterface
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

            if (!isset($this->config['debug'])) {
                $this->config['debug'] = false;
            }

            $this->enom = new Enom\Enom(
                $this->config['uid'],
                $this->config['pw'],
                $this->config['base_url'],
                $this->config['verify_ssl'],
                $this->config['debug']
            );
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

    // TODO Make this so it can check for all currently available extensions
    public function check_availability($apex_domain)
    {
        // list($sld, $tld) = Helpers\apex_split($apex_domain);
        // $domain = $this->getDomainInstance();

        // try {
        //     $result = $domain->check($sld, $tld);
        // } catch (Enom\EnomApiException $e) {
        //     $this->format_enom_error_message($e);
        //     return False;
        // }

        // $this->extendedResult = $result;

        // if ($result->RRPCode == 210) {
        //     $this->setResult(True);
        // } else {
        //     $this->setResult(False);
        // }
        $this->setResult(True);
        return True;
    }

    public function info($apex_domain, $include_contacts = True, $include_namservers = True, $include_ds = True)
    {
        $domain_name = new Models\DomainNameModel();
        $domain_name->registration_date = '';
        $domain_name->domain_name = '';
        $domain_name->expiration_date = '';
        $domain_name->deletion_date = '';
        $domain_name->registrar = '';
        $domain_name->statuses = '';
        $domain_name->ds_data = array();

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
        return True;
    }

    public function purchase_service($apex_domain, $service, $period = 1)
    {
        return True;
    }

    public function update_nameservers($apex_domain, $nameservers = array())
    {
        return True;
    }

    public function renew($apex_domain, $period = 1)
    {
        return True;
    }

    public function extend_expired($apex_domain, $period = 1)
    {
        return True;
    }

    public function activate($apex_domain)
    {
        return True;
    }

    public function contact_update($apex_domain, $contact_registrant)
    {
        return True;
    }

    public function transfer($apex_domain, $authorization_key, $contact_registrant = null)
    {
        return True;
    }

    public function set_lock($apex_domain, $lock)
    {
        return True;
    }

    public function check_nameserver($apex_domain, $nameserver)
    {
        return True;
    }

    public function create_nameserver($apex_domain, $nameserver, $ip)
    {
        return True;
    }

    public function update_nameserver($apex_domain, $nameserver, $ip, $old_ip)
    {
        return True;
    }

    public function delete_nameserver($apex_domain, $nameserver)
    {
        return True;
    }

    public function check_balance()
    {
        return true;
    }

    public function get_whois_protect_info($apex_domain)
    {
        return true;
    }

    public function add_dnssec($apex_domain, $ds_data)
    {
        return true;
    }

    public function delete_dnssec($apex_domain, $ds_data)
    {
        return true;
    }

    public function get_expired_domain($fqdn)
    {
        $result = (object) array(
            'domain_name' => '',
            'expiration_date' => '',
            'expiration_status' => '',
            'auto_renew' => '',
            'ns_status' => '',
            'reactivate_price' => ''
        );

        return $result;
    }

    public function get_expired_domains()
    {
        return array();
    }

    public function trade($apex_domain, $authorization_key, $contact_registrant, $period)
    {
        return true;
    }

    public function trade_info($tid)
    {
        return true;
    }

    public function trade_confirm($tid)
    {
        return true;
    }
}
