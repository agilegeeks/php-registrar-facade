<?php
namespace AgileGeeks\RegistrarFacade\Enom;

use \Coreproc\Enom as Enom;
use \AgileGeeks\RegistrarFacade\ApiException as ApiException;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '../../DomainHandlerInterface.php');
require_once(__DIR__ . '../../BaseHandler.php');
require_once(__DIR__ . '../../ApiException.php');


class DomainHandler extends \AgileGeeks\RegistrarFacade\BaseHandler
                    implements \AgileGeeks\RegistrarFacade\DomainHandlerInterface {
    protected $enom = null;
    protected $domain = null;

    public function __construct($config){
        parent::__construct($config);
    }

    public function getEnomInstance(){
        if ($this->enom==null){
            $this->enom = new Enom\Enom($this->config['uid'], $this->config['pw'], $this->config['base_url']);
        }
        return $this->enom;
    }

    public function getDomainInstance(){
        if ($this->domain==null){
            $this->domain = new Enom\Domain($this->getEnomInstance());
        }
        return $this->domain;
    }

    public function check_availability($apex_domain){
        list($sld,$tld) = apex_split($apex_domain);
        $domain = $this->getDomainInstance();
        try {
            $result = $domain->check($sld, $tld);
        } catch (Enom\EnomApiException $e) {
            throw new ApiException($e->getMessage(), 0);
        }
        if ($result->RRPCode==210){
            return True;
        }
        return False;
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
        list($sld,$tld) = apex_split($apex_domain);

        $domain = $this->getDomainInstance();
        $extendedAttributes = array(
            'RegistrantOrganizationName'=>$contact_registrant->organization_name,
            'RegistrantFirstName'=>$contact_registrant->first_name,
            'RegistrantLastName'=>$contact_registrant->last_name,
            'RegistrantAddress1'=>$contact_registrant->address1,
            'RegistrantAddress2'=>$contact_registrant->address2,
            'RegistrantCity'=>$contact_registrant->city,
            'RegistrantStateProvince'=>$contact_registrant->state_province,
            'RegistrantPostalCode'=>$contact_registrant->postal_code,
            'RegistrantCountry'=>$contact_registrant->country,
            'RegistrantEmailAddress'=>$contact_registrant->email,
            'RegistrantPhone'=>$contact_registrant->phone,
            'RegistrantFax'=>$contact_registrant->fax,
            'DomainPassword'=>$domain_password,
            'NumYears'=>$registration_period,
            'IgnoreNSFail'=>'Yes',

        );
        try {
            $result = $domain->purchase($sld, $tld, $extendedAttributes);
            var_dump($result);
            echo "result".$result;
        } catch (Enom\EnomApiException $e) {
            echo "--------------------------------------\n".$e->getMessage();
            throw new ApiException($e->getMessage(), 0);
        }
        echo "uaociciciicii";
        if ($result->RRPCode==210){
            return True;
        }
        return False;
    }
}
?>
