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
}


class ContactModel
{
    public $organization_name;
    public $first_name;
    public $last_name;
    public $address1;
    public $address2;
    public $city;
    public $state_province;
    public $postal_code;
    public $country;
    public $email;
    public $phone;
    public $fax;

    public function getFullName(){
        return $first_name." ".$last_name;
    }
}
 ?>
