<?php
namespace AgileGeeks\RegistrarFacade;

interface DomainHandlerInterface
{
    public function check_availability($apex_domain);
    public function info($apex_domain);
    public function register($apex_domain,
                        $registration_period,
                        $nameservers,
                        $domain_password,
                        $contact_registrant,
                        $contact_tech,
                        $contact_admin,
                        $contact_billing,
                        $extra_params);
    public function update_nameservers($apex_domain,$nameservers);
    public function create_nameserver($apex_domain, $nameserver, $ip);
    public function update_nameserver($apex_domain, $nameserver, $ip, $old_ip);
    public function delete_nameserver($apex_domain, $nameserver);
    public function activate($apex_domain);
    public function renew($apex_domain,$period);
    public function transfer($apex_domain, $authorization_key);
    public function trade($apex_domain, $authorization_key, $contact_registrant, $period);
    public function trade_info($tid);
    public function trade_confirm($tid);
}
?>
