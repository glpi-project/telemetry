<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\User as UserModel;

class Authentication extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $user;

    /**
     * Authenticate or not the user
     *
     * @param array $post   This is the informations from the connection form
     *
     * @return boolean
     * @see isExist()
     **/
    function authenticate($post)
    {

        if ($this->isExist($post) != false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the current user
     *
     * @return GLPI\Telemetry\Models\User
     * @see isExist()
     **/
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Test if the username already exist in database, verify the password and build the user object
     *
     * @param array $post   This is the informations from the connection form
     *
     * @return boolean|GLPI\Telemetry\Models\User
     **/
    public function isExist($post)
    {
        $user_ref = new UserModel();
        $user_model = $user_ref->newInstance();
        $bool_username = $user_model->usernameExist($post['user']);

        if ($bool_username) {
            $user_obj = $user_model::where('username', '=', $post['user'])->first();

            $check_pw = password_verify($post['password'], $user_obj->hash);
            if ($check_pw) {
                $this->user = $user_obj;
                return $user_obj;
            }
        }
        return false;
    }
}
