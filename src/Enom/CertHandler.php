<?php
namespace AgileGeeks\RegistrarFacade\Enom;

use \Coreproc\Enom as Enom;
use \AgileGeeks\RegistrarFacade\ApiException as ApiException;
use \AgileGeeks\RegistrarFacade\Models as Models;
use \AgileGeeks\RegistrarFacade\Helpers as Helpers;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '../../models.php');
require_once(__DIR__ . '../../BaseHandler.php');
require_once(__DIR__ . '../../ApiException.php');

class CertHandler extends \AgileGeeks\RegistrarFacade\BaseHandler
{
    protected $enom = null;
    protected $cert = null;


    public function __construct($config)
    {
        parent::__construct($config);
    }

    private function format_enom_error_message($e)
    {
        $errors = get_object_vars($e->getErrors());
        $error_message = '';
        foreach ($errors as $key => $val) {
            $error_message .= $val . " ";
        }
        $this->setError(trim($error_message));
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

            $this->enom = new Enom\Enom($this->config['uid'], $this->config['pw'], $this->config['base_url'], $this->config['verify_ssl'], $this->config['debug']);
        }

        return $this->enom;
    }

    public function getCertInstance()
    {
        if ($this->cert == null) {
            $this->cert = new Enom\Cert($this->getEnomInstance());
        }

        return $this->cert;
    }

    public function get_certs()
    {
        $cert = $this->getCertInstance();
        try {
            $result = $cert->get_certs();
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }
        $this->extendedResult = $result;

        
        return True;
    }

    /*
    Services
        Certificate-Comodo-Essential 
        Certificate-Comodo-Instant 
        Certificate-Comodo-Essential-Wildcard 
        Certificate-Comodo-Premium-Wildcard 
        Certificate-Comodo-EV 
        Certificate-Comodo-EV-SGC 
        Certificate-GeoTrust-QuickSSL 
        Certificate-GeoTrust-QuickSSL-Premium 
        Certificate-GeoTrust-TrueBizID 
        Certificate-GeoTrust-TrueBizID-Wildcard 
        Certificate-GeoTrust-TrueBizID-EV 
        Certificate-RapidSSL
        Certificate-RapidSSL-RapidSSL-Wildcard 
        Certificate-VeriSign-Secure-Site 
        Certificate-VeriSign-Secure-Site-Pro 
        Certificate-VeriSign-Secure-Site-EV 
        Certificate-VeriSign-Secure-Site-Pro-EV 
        Certificate-Comodo-UCC-DV 
        Certificate-Comodo-UCC-OV
    
    Max Num Years
        3 Comodo Essential 
        3 Comodo Instant 
        3 Comodo Essential Wildcard 
        3 Comodo Premium Wildcard 
        2 Comodo EV 
        2 Comodo EV SGC 
        3 GeoTrust QuickSSL 
        3 GeoTrust QuickSSL Premium 
        3 GeoTrust True BusinessID 
        3 GeoTrust True BusinessID Wildcard 
        2 GeoTrust True BusinessID with EV 
        3 RapidSSL 
        3 RapidSSL Wildcard
        3 VeriSign Secure Site 
        3 VeriSign Secure Site Pro 
        2 VeriSign Secure Site with EV 
        2 VeriSign Secure Site Pro with EV 
        3 Comodo UCC DV 
        3 Comodo UCC OV
    */

    public function create_cert($service, $num_years)
    {
        $cert = $this->getCertInstance();
        try {
            $result = $cert->purchaseServices($service, $num_years);
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }
        $this->extendedResult = $result;
        
        if ($result->ErrCount == 0) {
            $this->setResult(
                (object) array(
                    'certid' => $result->certid,
                    'orderid' => $result->orderid
                )
            );
        } 
        return True;
    }

    private function format_contact($contact){
        if ($contact != null)
        {
            return array(
                'OrgName' => $contact->organization_name,
                'FName' => $contact->first_name,
                'LName' => $contact->last_name,
                'JobTitle' => $contact->job_title,
                'Address1' => $contact->address1,
                'Address2' => $contact->address2,
                'City' => $contact->city,
                'State' => $contact->state_province,
                'Province' => $contact->state_province,
                'StateProvinceChoice' =>'S',
                'PostalCode' => $contact->postal_code,
                'Country' => $contact->country,
                'EmailAddress' => $contact->email,
                'Phone' => $contact->phone,
                'Fax' => $contact->fax
            );
        }else
        {
            return null;
        }
        
    }


    /*
    Webserver
        1 Apache + MOD SSL 
        2 Apache + Raven
        3 Apache + SSLeay 
        4 C2Net Stronghold 
        7 IBM HTTP 
        8 iPlanet Server 4.1 
        9 Lotus Domino Go 4.6.2.51 
        10 Lotus Domino Go 4.6.2.6+ 
        11 Lotus Domino 4.6+ 
        12 Microsoft IIS 4.0 
        13 Microsoft IIS 5.0 
        14 Netscape Enterprise/FastTrack 
        17 Zeus v3+ 
        18 Other 
        20 Apache + OpenSSL 
        21 Apache 2 
        22 Apache + ApacheSSL 
        23 Cobalt Series 
        24 Cpanel 
        25 Ensim 
        26 Hsphere 
        27 Ipswitch 
        28 Plesk 
        29 Jakart-Tomcat 
        30 WebLogic (all versions) 
        31 O’Reilly WebSite Professional 
        32 WebStar 
        33 Microsoft IIS 6.0
        1000 Otherold 
        1001 AOL 
        1002 Apache/ModSSL 
        1003 Apache-SSL (Ben-SSL, not Strong-hold) 
        1004 C2Net Strongholdold 
        1005 Cobalt Raq 
        1006 Covalent Server Software 
        1007 IBM HTTP Server 
        1008 IBM Internet Connection Server 
        1009 iPlanet 
        1010 Java Web Server (Javasoft / Sun) 
        1011 Lotus Domino 
        1012 Lotus Domino Go! 
        1013 Microsoft IIS 1.x to 4.x 
        1014 Microsoft IIS 5.x and later 
        1015 Netscape Enterprise Server 
        1016 Netscape FastTrack 
        1017 Novell Web Server 
        1018 Oracle 
        1019 Quid Pro Quo 
        1020 R3 SSL Server
        1021 Raven SSL 
        1022 RedHat Linux 
        1023 SAP Web Application Server 
        1024 Tomcat 
        1025 Website Professional 
        1026 WebStar 4.x and later 
        1027 Web Ten ( from Tenon) 
        1028 Zeus Web Server 
        1029 Ensim 
        1030 Plesk 
        1031 WHM/cPanel 
        1032 H-Sphere

    */

    public function configure_cert(
        $certid, 
        $domains=array(), 
        $emails=array(), 
        $webserver,
        $csr,
        $admin_contact=null, 
        $tech_contact=null, 
        $billing_contact=null
    )
    {
        $cert = $this->getCertInstance();
        try {
            $result = $cert->configure_cert(
                $certid, 
                $domains, 
                $emails, 
                $webserver,
                $csr,
                $this->format_contact($admin_contact), 
                $this->format_contact($tech_contact),
                $this->format_contact($billing_contact)
                );
        } catch (Enom\EnomApiException $e) {
            $this->format_enom_error_message($e);
            return False;
        }
        $this->extendedResult = $result;
        $CertConfigureCert = $result->CertConfigureCert;
        $CSRData = $CertConfigureCert->CSRData;
        var_dump($CSRData);
        if ($result->ErrCount == 0) {
            $this->setResult(
                (object) array(
                    'certid' => $CertConfigureCert->CertID,
                    'CSRData' => array(
                        'Organization' => $CSRData->Organization,
                        'DomainName' => $CSRData->DomainName,
                        'Email' => $CSRData->Email,
                        'HasBadExtensions' => $CSRData->HasBadExtensions,
                        'Locality' => $CSRData->Locality,
                        'State' => $CSRData->State,
                        'Country' => $CSRData->Country,
                    ),
                )
            );
        } 
        
        return True;
    }

    
}