<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\User as UserModel;

class Authentication extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $user;

    function Authenticate($post) {

        if ($this->isExist($post) != false) {
            return true;
        } else {
            return false;
        }
    }


    public function getUser()
    {
        return $this->user;
    }


    public function isExist($post)
    {
        $user_ref = new UserModel();
        $user_model = $user_ref->newInstance();
        $bool_username = $user_model->usernameExist($post['user']);

        if($bool_username) {
            $user_obj = $user_model::where('username', '=', $post['user'])->first();

            $check_pw = password_verify($post['password'], $user_obj->hash);
            if($check_pw) {
                $this->user = $user_obj;
                $user_obj->setUserInfo();
                return $user_obj;
            }
        }
        return false;
    }

}
