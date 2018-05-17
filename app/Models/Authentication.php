<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\User as UserModel;

class Authentication extends \Illuminate\Database\Eloquent\Model
{
    protected $user_info;

    function Authenticate($post) {

        $user_ref = new UserModel();
        $user_model = $user_ref->newInstance();


        if ($user_model->isExist($post)) {
            $this->user_info = $user_model->getUserInfo();
            return true;
        } else {
            return false;
        }
    }


    public function getUserInfo()
    {
        return $this->user_info;
    }
}
