<?php
class SuperAdmin {
    private $email = 'superadmin@gmail.com';
    private $password = 'Admin@123';

    public function login($inputEmail, $inputPassword) {
        if ($inputEmail === $this->email && $inputPassword === $this->password) {
            return true;
        }
        return false;
    }
}
?>
