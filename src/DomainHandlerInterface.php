<?php
namespace AgileGeeks\RegistrarFacade;

interface DomainHandlerInterface
{
    public function check_availability($apex_domain);
    public function register($apex_domain,
                        $registration_period,
                        $nameservers,
                        $domain_password,
                        $contact_registrant,
                        $contact_tech,
                        $contact_admin,
                        $contact_billing,
                        $extra_params);
}
?>
