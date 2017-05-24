<?php
use PHPUnit\Framework\TestCase;

class BaseTestCase extends PHPUnit\Framework\TestCase {

    protected function setUp($configfile = null) {

    }

    protected function tearDown() {

    }


    protected static function randomstring($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected static function randomnumber($length) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function test_domain_availability() {

        $this->assertFalse($this->handler->check_availability('google.com'));
        $this->assertTrue($this->handler->check_availability('domainthatdoesnotexistsandifitdoesbadluck.com'));
    }
}
