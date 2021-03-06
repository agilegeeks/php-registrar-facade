<?php
namespace AgileGeeks\RegistrarFacade\Rotld;

use AgileGeeks\Rotld\RotldClient;
use AgileGeeks\RegistrarFacade\BaseHandler;
use AgileGeeks\RegistrarFacade\DomainHandlerInterface;
use AgileGeeks\RegistrarFacade\ApiException;
use AgileGeeks\RegistrarFacade\Rotld\Models;

use AgileGeeks\RegistrarFacade\Helpers;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '/models.php');

class DomainHandler extends BaseHandler
                    implements DomainHandlerInterface {
    protected $client = null;

    private function format_rotld_error_message(){
        $errors = get_object_vars($e->getErrors());
        $error_message = '';
        foreach ($errors as $key => $val) {
            $error_message .= $val." ";
        }
        $this->setError();
    }

    public function __construct($config){
        parent::__construct($config);
        $this->client = new RotldClient(
            $config['regid'],
            $config['password'],
            $config['apiurl'],
            $config['lang'],
            $config['format']
        );
    }

    public function check_availability($apex_domain){
        $result = $this->client->check_availability($apex_domain);
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        if ($result=='Available'){
            $this->setResult(True);
        }else{
            $this->setResult(False);
        }
        return True;
    }

    public function info($apex_domain, $include_contacts=True, $include_namservers=True, $include_ds=True){
        $contact_type_mapping = array(
            'Administrative'=>'contact_admin',
            'Technical'=>'contact_tech',
            'Registrant'=>'contact_registrant',
            'Billing'=>'contact_billing'
        );
        $domain_data = $this->client->info_domain($apex_domain);
        if (!$domain_data){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        $domain_name = new Models\RotldDomainNameModel ();
        $domain_name->domain_name = $domain_data->domain;
        $domain_name->registration_date = strtotime($domain_data->registration_date);
        $domain_name->expiration_date = $domain_data->expiration_date==null ? '' : strtotime($domain_data->expiration_date);
        $domain_name->deletion_date = $domain_data->deletion_date==null ? '' : strtotime($domain_data->deletion_date);
        $domain_name->registrant_id = $domain_data->registrant_id;
        $domain_name->statuses = $domain_data->statuses;

        //get contacts info
        if($include_contacts===True){
            $registrant_data = $this->client->info_contact($domain_name->registrant_id);
            if (!$registrant_data){
                $this->setError($this->client->getResultMessage());
                return False;
            }
            $contact = new Models\RotldContactModel();
            if($registrant_data->person_type=='p' || $registrant_data->person_type=='ap'){
                $contact->organization_name = '';
                if (strpos($registrant_data->name, " ") > 0) {
                    list($contact->first_name, $contact->last_name) = Helpers\object_to_empty_string(explode(" ", $registrant_data->name, 2));
                } else {
                    $contact->first_name = $registrant_data->name;
                    $contact->last_name = '';
                }
            }else{
                $contact->organization_name = $registrant_data->name;
                $contact->first_name='';
                $contact->last_name='';
            }
            $contact->person_type = Helpers\object_to_empty_string($registrant_data->person_type);
            $contact->address1 = Helpers\object_to_empty_string($registrant_data->address1);
            $contact->address2 = Helpers\object_to_empty_string($registrant_data->address2);
            $contact->address3 = Helpers\object_to_empty_string($registrant_data->address3);
            $contact->city = Helpers\object_to_empty_string($registrant_data->city);
            $contact->country = Helpers\object_to_empty_string($registrant_data->country_code);
            $contact->state_province = Helpers\object_to_empty_string($registrant_data->state_province);
            $contact->postal_code = Helpers\object_to_empty_string($registrant_data->postal_code);
            $contact->phone = Helpers\object_to_empty_string($registrant_data->phone);
            $contact->fax = Helpers\object_to_empty_string($registrant_data->fax);
            $contact->email = Helpers\object_to_empty_string($registrant_data->email);
            $domain_name->contact_registrant = $contact;

        }
        //finished getting contacts info

        //get nameservers info
        if($include_namservers===True){
            $domain_name->nameservers = $domain_data->nameservers;
            $domain_name->hosts = $domain_data->hosts;
        }
        //finished getting nameservers info

        if($include_ds === True){
            $domain_name->ds_data = $domain_data->dsdata;
        }

        $this->setResult($domain_name);
        return True;
    }

    public function register($apex_domain,
                        $registration_period=1,
                        $nameservers=array(),
                        $domain_password=null,
                        $contact_registrant=null,
                        $contact_tech=null,
                        $contact_admin=null,
                        $contact_billing=null,
                        $extra_params=array()
                    ){


        $registrant_data = array();

        if ($contact_registrant->person_type == 'p') {
            $registrant_data['name'] = $contact_registrant->first_name." ".$contact_registrant->last_name;
        } else {
            $registrant_data['name'] = $contact_registrant->organization_name;
        }
        
        $registrant_data['cnp_fiscal_code'] = $contact_registrant->cnp_fiscal_code;
        $registrant_data['registration_number'] = $contact_registrant->registration_number;
        $registrant_data['email'] = $contact_registrant->email;
        $registrant_data['phone'] = $contact_registrant->phone;
        $registrant_data['fax'] = $contact_registrant->fax;
        $registrant_data['address1'] = $contact_registrant->address1;
        $registrant_data['address2'] = $contact_registrant->address2;
        $registrant_data['address3'] = $contact_registrant->address3;
        $registrant_data['city'] = $contact_registrant->city;
        $registrant_data['state_province'] = $contact_registrant->state_province;
        $registrant_data['postal_code'] = $contact_registrant->postal_code;
        $registrant_data['country_code'] = $contact_registrant->country;
        $registrant_data['person_type'] = $contact_registrant->person_type;

        $cid = $this->client->create_contact($registrant_data);
        if (!$cid){
            $this->setError($this->client->getResultMessage());
            return False;
        }

        $reservation = false;
        if (isset($extra_params['reservation']) && $extra_params['reservation']==true){
            $reservation = true;
        }

        if ($reservation){
            $result = $this->client->reserve_domain(
                $domain_name = $apex_domain,
                $domain_period = $registration_period,
                $registrant_cid = $cid,
                $domain_password =  $domain_password
            );
        }else{
            $result = $this->client->register_domain(
                $domain_name = $apex_domain,
                $domain_period = $registration_period,
                $registrant_cid = $cid,
                $domain_password =  $domain_password
            );
        }

        if (!$result){
            $this->setError($this->client->getResultMessage(), $this->client->getResultCode());
            return False;
        }

        if (sizeof($nameservers)>0 && !$reservation){
            $result = $this->client->reset_nameservers(
                $domain_name = $apex_domain,
                $nameservers = $nameservers
            );
            if (!$result){
                $this->setError($this->client->getResultMessage());
                return False;
            }
        }


        return True;

    }

    public function update_nameservers($apex_domain, $nameservers=array()){
        $result = $this->client->reset_nameservers($apex_domain, $nameservers);
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        return True;
    }

    public function renew($apex_domain, $period=1){
        $period = intval($period);
        if($period<1 || $period>10){
            $this->setError('Invalid period');
            return False;
        }
        $result = $this->client->renew_domain($apex_domain, $period);
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        return True;
    }

    public function transfer($apex_domain, $authorization_key, $contact_registrant = null) {
        $result = $this->client->transfer_domain($apex_domain, $authorization_key);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }

        return True;
    }

    public function trade($apex_domain, $authorization_key, $contact_registrant, $period) {
        $registrant_data = array();

        if ($contact_registrant->person_type == 'p') {
            $registrant_data['name'] = $contact_registrant->first_name." ".$contact_registrant->last_name;
        } else {
            $registrant_data['name'] = $contact_registrant->organization_name;
        }
        
        $registrant_data['cnp_fiscal_code'] = $contact_registrant->cnp_fiscal_code;
        $registrant_data['registration_number'] = $contact_registrant->registration_number;
        $registrant_data['email'] = $contact_registrant->email;
        $registrant_data['phone'] = $contact_registrant->phone;
        $registrant_data['fax'] = $contact_registrant->fax;
        $registrant_data['address1'] = $contact_registrant->address1;
        $registrant_data['address2'] = $contact_registrant->address2;
        $registrant_data['address3'] = $contact_registrant->address3;
        $registrant_data['city'] = $contact_registrant->city;
        $registrant_data['state_province'] = $contact_registrant->state_province;
        $registrant_data['postal_code'] = $contact_registrant->postal_code;
        $registrant_data['country_code'] = $contact_registrant->country;
        $registrant_data['person_type'] = $contact_registrant->person_type;

        $cid = $this->client->create_contact($registrant_data);
        if (!$cid){
            $this->setError($this->client->getResultMessage());
            return False;
        }

        $result = $this->client->trade_domain($apex_domain, $authorization_key, $cid, $period);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }

        $this->setResult($result);
        return True;
    }

    public function trade_info($tid)
    {
        $result = $this->client->trade_info($tid);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }

        return True;
    }

    public function trade_confirm($tid)
    {
        $result = $this->client->trade_confirm($tid);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }

        return True;
    }

    public function activate($apex_domain){
        $result = $this->client->activate_domain($apex_domain);
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        return True;
    }

    public function contact_update($apex_domain, $contact_registrant)
    {
        $domain_data = $this->client->info_domain($apex_domain);
        if (!$domain_data) {
            $this->setError($this->client->getResultMessage());
            return False;
        }
        $registrant_data = array();
        $registrant_data['cid'] = $domain_data->registrant_id;
        $registrant_data['name'] = $contact_registrant->first_name . " " . $contact_registrant->last_name;
        $registrant_data['cnp_fiscal_code'] = $contact_registrant->cnp_fiscal_code;
        $registrant_data['registration_number'] = $contact_registrant->registration_number;
        $registrant_data['email'] = $contact_registrant->email;
        $registrant_data['phone'] = $contact_registrant->phone;
        $registrant_data['fax'] = $contact_registrant->fax;
        $registrant_data['address1'] = $contact_registrant->address1;
        $registrant_data['address2'] = $contact_registrant->address2;
        $registrant_data['address3'] = $contact_registrant->address3;
        $registrant_data['city'] = $contact_registrant->city;
        $registrant_data['state_province'] = $contact_registrant->state_province;
        $registrant_data['postal_code'] = $contact_registrant->postal_code;
        $registrant_data['country_code'] = $contact_registrant->country;
        $registrant_data['person_type'] = $contact_registrant->person_type;
        $result = $this->client->contact_update($registrant_data);
        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }
        return True;
    }

    public function create_nameserver($apex_domain, $nameserver, $ip)
    {
        $result = $this->client->create_nameserver($nameserver, $ip);
        
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        
        return True;
    }

    public function update_nameserver($apex_domain, $nameserver, $ip, $old_ip)
    {
        $result = $this->client->update_nameserver($nameserver, $ip);
        
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        
        return True;
    }

    public function delete_nameserver($apex_domain, $nameserver)
    {
        $result = $this->client->delete_nameserver($nameserver);
        
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        
        return True;
    }

    public function info_nameserver($apex_domain, $nameserver)
    {
        $result = $this->client->info_nameserver($nameserver);
        
        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }
        
        $this->setResult($result);
        return True;
    }

    public function check_balance()
    {
        $result = $this->client->check_balance();

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return false;
        }

        $this->setResult($result);

        return true;
    }

    public function add_dnssec($apex_domain, $ds_data)
    {
        $result = $this->client->add_dnssec_data($apex_domain, $ds_data);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return false;
        }

        $this->setResult($result);

        return true;
    }

    public function delete_dnssec($apex_domain, $ds_data)
    {
        $result = $this->client->remove_dnssec_data($apex_domain, $ds_data);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return false;
        }

        $this->setResult($result);

        return true;
    }

    public function get_whois_protect_info($apex_domain)
    {
    }
}
