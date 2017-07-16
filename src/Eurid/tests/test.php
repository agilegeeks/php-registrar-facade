<?php
require('../../../vendor/agilegeeks/php-epp-client/autoloader.php');
require('config.php');

use Metaregistrar\EPP\euridEppConnection;
use Metaregistrar\EPP\eppException;
use Metaregistrar\EPP\eppCheckDomainRequest;
use Metaregistrar\EPP\eppCheckDomainResponse;
use Metaregistrar\EPP\eppLoginRequest;


function greet($conn) {
    // Set this to true to see the server greeting
    $showgreeting = true;
    try {
        $greeting = new Metaregistrar\EPP\eppHelloRequest();
        if ((($response = $conn->writeandread($greeting)) instanceof Metaregistrar\EPP\eppHelloResponse) && ($response->Success())) {
            if ($showgreeting) {
                echo "Welcome to " . $response->getServerName() . ", date and time: " . $response->getServerDate() . "\n";
                $languages = $response->getLanguages();
                if (is_array($languages)) {
                    echo "Supported languages:\n";
                    foreach ($languages as $language) {
                        echo "-" . $language . "\n";
                    }
                }
                $versions = $response->getVersions();
                if (is_array($versions)) {
                    echo "Supported versions:\n";
                    foreach ($versions as $version) {
                        echo "-" . $version . "\n";
                    }
                }
                $services = $response->getServices();
                if (is_array($services)) {
                    echo "Supported services:\n";
                    foreach ($services as $service) {
                        echo "-" . $service . "\n";
                    }
                }
                $extensions = $response->getExtensions();
                if (is_array($extensions)) {
                    echo "Supported extensions:\n";
                    foreach ($extensions as $extension) {
                        echo "-" . $extension . "\n";
                    }
                }
            }
            return true;
        }
    } catch (Metaregistrar\EPP\eppException $e) {
        echo $e->getMessage() . "\n";
    }
    return false;
}


function login($conn) {
    try {
        $login = new Metaregistrar\EPP\eppLoginRequest();
        if ((($response = $conn->writeandread($login)) instanceof Metaregistrar\EPP\eppLoginResponse) && ($response->Success())) {
            return true;
        }
    } catch (Metaregistrar\EPP\eppException $e) {
        echo $e->getMessage() . "\n";
    }
    return false;
}


function logout($conn) {
    try {
        $logout = new Metaregistrar\EPP\eppLogoutRequest();
        if ((($response = $conn->writeandread($logout)) instanceof Metaregistrar\EPP\eppLogoutResponse) && ($response->Success())) {
            return true;
        } else {
            echo "Logout failed with message " . $response->getResultMessage() . "\n";
            return false;
        }
    } catch (Metaregistrar\EPP\eppException $e) {
        echo $e->getMessage() . "\n";
    }
    return false;
}


$conn = new Metaregistrar\EPP\euridEppConnection();
$conn->setHostname($config['hostname']); // Hostname may vary depending on the registry selected
$conn->setPort($config['port']); // Port may vary depending on the registry selected
$conn->setUsername($config['regid']);
$conn->setPassword($config['password']);

if (!$conn->login()) {
    echo "NUUUUUUUUU!!!!!";
    die();
}
//login($conn);
greet($conn);
//logout($conn);

$domains = array('eurid.eu');
$check = new eppCheckDomainRequest($domains);

if ($response = $conn->request($check)) {
    /* @var $response eppCheckDomainResponse */
    // Walk through the results
    $checks = $response->getCheckedDomains();
    var_dump($checks);exit();
    foreach ($checks as $check) {
        echo $check['domainname'] . " is " . ($check['available'] ? 'free' : 'taken');
        if ($check['available']) {
            echo ' (' . $check['reason'] .')';
        }
        echo "\n";
    }
}
$conn->logout();
// try {
//     // Set login details for the service in the form of
//     // interface=metaregEppConnection
//     // hostname=ssl://epp.test2.metaregistrar.com
//     // port=7443
//     // userid=xxxxxxxx
//     // password=xxxxxxxxx
//     // Please enter the location of the file with these settings in the string location here under
//     if ($conn = eppConnection::create('config.php')) {
//         // Connect and login to the EPP server
//         if ($conn->login()) {
//             // Check domain names
//             checkdomains($conn, $domains);
//             $conn->logout();
//         }
//     }
// } catch (eppException $e) {
//     echo "ERROR: " . $e->getMessage() . "\n\n";
// }
 ?>
