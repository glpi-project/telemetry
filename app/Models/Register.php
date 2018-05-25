<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\User as UserModel;

class Register extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'register';
    protected $tab = [
                        'status' => '',
                        'msg' => ''
                    ];
    protected $guarded = [
        'id'
    ];

    /**
     * Check if the form's values from the registration page are correct and insert in database
     *
     * @param array $post   Informations from the registration's form
     *
     * @return array
     * @see insertRegister()
    **/
    public function checkRegister($post)
    {
        $user = htmlentities($post['username']);
        $hash = htmlentities($post['password']);
        $mail = htmlentities($post['mail']);
        $admin = false;

        if (!$this->isValidPassword($hash)) {
            $this->setTabReturn('406', 'Password is not compliant');
            return $this->tab;
        }

        $hash = password_hash($post['password'], PASSWORD_DEFAULT);

        return $this->insertRegister($user, $hash, $mail, $admin);
    }


    /**
    * Length 8 chars
    * At least one lowercase letter
    * At least one uppercase letter
    * At least one digit
    *
    * @param string $password
    *
    * @return boolean
    **/
    public function isValidPassword($password)
    {
        return preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $password) ? true : false;
    }

    /**
     * Create the new user by insert him in database
     *
     * @param string $user
     * @param string $hash  The hashed password
     * @param string $mail
     * @param boolean $admin
     *
     * @return array
    **/
    private function insertRegister($user, $hash, $mail, $admin)
    {

        // test if the user already exist
        $user_ref = new UserModel();
        $user_model = $user_ref->newInstance();
        if ($user_model->usernameExist($user)) {
            $this->setTabReturn('417', 'Registration failed, user already exist');
            return $this->tab;
        }


        $tmp =
            [
                'username' => $user,
                'hash' => $hash,
                'email' => $mail,
                'is_admin' => $admin
            ]
        ;


        $status = $user_model->insert($tmp);

        if ($status === true) {
            $this->setTabReturn('200', 'Registration done');
            return $this->tab;
        } else {
            $this->setTabReturn('417', 'Registration failed');
            return $this->tab;
        }
    }

    /**
    * Status 200 OK
    * Status 406 Not Acceptable
    * Status 417 Expectation Failed
    *
    * @param integer $status
    * @param string $msg
    *
    * @return void
    **/
    private function setTabReturn($status, $msg)
    {
        $this->tab['status'] = $status;
        $this->tab['msg'] = $msg;
    }
}
