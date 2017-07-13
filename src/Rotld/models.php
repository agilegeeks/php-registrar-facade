<?php
namespace AgileGeeks\RegistrarFacade\Rotld\Models;
use AgileGeeks\RegistrarFacade\Models;

require_once(__DIR__ . '../../models.php');

class RotldContactModel extends Models\ContactModel
{
    public $person_type;

}

class RotldDomainNameModel extends Models\DomainNameModel
{
    public $registrant_id;
}
 ?>
