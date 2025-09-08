<?php
class SalesAdmin {
    private $email = 'salesadmin@gmail.com';
    private $password = 'Sales@123';

    public function login($inputEmail, $inputPassword) {
        if ($inputEmail === $this->email && $inputPassword === $this->password) {
            return true;
        }
        return false;
    }
}
?>
