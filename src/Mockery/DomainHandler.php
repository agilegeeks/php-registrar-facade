<?php
namespace AgileGeeks\RegistrarFacade\Mockery;

use \AgileGeeks\RegistrarFacade\ApiException as ApiException;

require_once(__DIR__ . '../../helpers.php');
require_once(__DIR__ . '../../DomainHandlerInterface.php');
require_once(__DIR__ . '../../BaseHandler.php');
require_once(__DIR__ . '../../ApiException.php');


class DomainHandler extends \AgileGeeks\RegistrarFacade\BaseHandler
                    implements \AgileGeeks\RegistrarFacade\DomainHandlerInterface {

    public function __construct($config){
        parent::__construct($config);
    }


    public function check_availability($apex_domain){
        switch ($apex_domain) {
            case 'google.com':
                return False;
            case 'domainthatdoesnotexistsandifitdoesbadluck.com':
                return True;
            default:
                return False;
        }
    }
}
?>
