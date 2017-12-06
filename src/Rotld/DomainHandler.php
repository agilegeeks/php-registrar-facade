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

    public function info($apex_domain, $include_contacts=True, $include_namservers=True){
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
        $domain_name->registration_date = $domain_data->registration_date;
        $domain_name->expiration_date = $domain_data->expiration_date==null ? '' : strtotime($domain_data->expiration_date);
        $domain_name->deletion_date = $domain_data->deletion_date==null ? '' : strtotime($domain_data->deletion_date);
        $domain_name->registrant_id = $domain_data->registrant_id;

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
                list($contact->first_name, $contact->last_name) = Helpers\object_to_empty_string(explode(" ", $registrant_data->name, 2));
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
        }
        //finished getting nameservers info


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
        $registrant_data['name'] = $contact_registrant->first_name." ".$contact_registrant->last_name;
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

        $result = $this->client->register_domain(
            $domain_name = $apex_domain,
            $domain_period = $registration_period,
            $registrant_cid = $cid,
            $domain_password =  $domain_password
        );

        if (!$result){
            $this->setError($this->client->getResultMessage());
            return False;
        }

        if (sizeof($nameservers)>0){
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

    public function transfer($apex_domain, $authorization_key) {
        $result = $this->client->transfer_domain($apex_domain, $authorization_key);

        if (!$result) {
            $this->setError($this->client->getResultMessage());
            return False;
        }

        return True;
    }

}
?>
