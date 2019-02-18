<?php
use AgileGeeks\RegistrarFacade\Enom as Api;
require_once(__DIR__.'../../../BaseTestCase.php');
require_once(__DIR__.'../../CertHandler.php');


class TestDomainInfo extends BaseTestCase {

    function __construct() {
    }

    protected function setUp($configfile = null) {
        include('config.php');
        $this->handler = new Api\CertHandler($config);

    }

    protected function tearDown() {

    }

    public function test_configure() {
        $certid = '81852';
        // $response = $this->handler->create_cert('Certificate-Comodo-Essential', 1);
        // $certid = $result->certid;
        
        $csr = "MIIC6TCCAdECAQAwgYkxCzAJBgNVBAYTAlJPMRIwEAYDVQQIDAlCdWNoYXJlc3Qx
        EjAQBgNVBAcMCUJ1Y2hhcmVzdDEUMBIGA1UECgwLQWdpbGUgR2Vla3MxFjAUBgNV
        BAMMDXJhZHVib25jZWEucm8xJDAiBgkqhkiG9w0BCQEWFXJhZHUuYm9uY2VhQGdt
        YWlsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALUxV7/9R95D
        bBRbElgBe8Gl0M6vcugauprRNmy4SHwkHg6WOqeBLNRT+ZP86hFc9a/j01FQ2Gv/
        81z/CAPeSNPsOglHT8uk5M6gHqFU4BhNRdkVW1e4QnjTybeYNz1E6BVSNa7KATi7
        VbSpFUAaig97nSR3K1dBBmWZUtNGPPNlMVAVvSwnQ3uGfASVbH/BmOqQnpeSzri6
        Sfa2t7KoDdEcNc7yDJmTjur2xFQtISdF9iZDQtrf043tN9l9CYMYXgtJPYO+vs7I
        Fm18qIt53gJq2QOPO6wOu5AQ4YSFlM0vbzDOSLMu7kojeXNiC7KKjs5RdFXLdfTL
        ETtJUVp+0LECAwEAAaAaMBgGCSqGSIb3DQEJBzELDAljYXRoeTE3MDYwDQYJKoZI
        hvcNAQELBQADggEBAIYXmn/7609mzibqMImnqQ7zBIk5Axtl4GswhgAr+SHEPoKQ
        XFP3GgsU5QhEXlw0MHv4BjFEqw8SQnGnrdlT/CT3BeHVgk40m8s/aisR9oPu0o63
        KC1gFfPcxTgCcTF+STHabP5f/EH73jAZ5D5vJ46KT7woD3gzaBfecrNlHFqBoNWn
        zAKJEhDGmvHu/ls6OUQKBChWOtdDJ6YiTobNAsnVydVPgVnnHl11gV62xmLB9W9h
        B7/gYH7310bwkWkUMBzhN7OUMwzIFotVUEK+4ruf66GyyPXuT6eXrhDJWu+kGu+m
        u+I9nznLflIl3Lb/ZRxdAPdlNs58l6zIbplLrYI=
        ";
        $admin_contact = (object)array(
            'organization_name' => 'Agile Geeks',
            'first_name' => 'Radu',
            'last_name' => 'Boncea',
            'job_title' => 'CEO',
            'address1' => 'Agile Geeks Street',
            'address2' => '',
            'city' => 'Bucuresti',
            'state_province' => 'Bucuresti',
            'postal_code' => '12233',
            'country' => 'RO',
            'email' => 'radu.boncea@gmail.com',
            'phone' => '0040754074401',
            'fax' => ''
        );
        $response = $this->handler->configure_cert(
            $certid, 
            array('raduboncea.ro'), 
            array('radu.boncea@gmail.com'), 
            2,
            $csr,
            $admin_contact, 
            null, 
            null
        );
        $result = $this->handler->getResult();
        
        $this->assertEquals($result->certid,$certid);
        //var_dump($result);
        //var_dump($this->handler->extendedResult);
        
    }
}
