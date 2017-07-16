<?php
namespace AgileGeeks\RegistrarFacade\Models;

class DomainNameModel
{
    public $domain_name = null;
    public $registration_date = null;
    public $expiration_date = null;
    public $deletion_date = null;
    public $registrar = null;
    public $contact_registrant = null;
    public $contact_tech = null;
    public $contact_admin = null;
    public $contact_billing = null;
    public $nameservers = array();
    public $statuses = array();
}


class ContactModel
{
    public $organization_name = null;
    public $first_name = null;
    public $last_name = null;
    public $address1 = null;
    public $address2 = null;
    public $address3 = null;
    public $city = null;
    public $state_province = null;
    public $postal_code = null;
    public $country = null;
    public $email = null;
    public $phone = null;
    public $fax = null;

    public function getFullName(){
        return $this->first_name." ".$this->last_name;
    }
}
 ?>
