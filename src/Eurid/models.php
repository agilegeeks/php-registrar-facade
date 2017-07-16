<?php
namespace AgileGeeks\RegistrarFacade\Eurid\Models;
use AgileGeeks\RegistrarFacade\Models;

require_once(__DIR__ . '../../models.php');

class EuridContactModel extends Models\ContactModel
{

}

class EuridDomainNameModel extends Models\DomainNameModel
{
    public $registrant_id = null;
    public $contact_tech_id = null;
    public $contact_onsite_id = null;
    public $contact_billing_id = null;
    public $contact_reseller_id = null;
    public $contact_onsite = null;
    public $contact_reseller = null;
    public $last_update = null;
    
}
 ?>
