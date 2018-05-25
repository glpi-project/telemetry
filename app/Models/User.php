<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\Reference as ReferenceModel;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $id;
    protected $username;
    protected $guarded = [
      ''
    ];
    protected $fillable = array('username', 'is_admin', 'mail');

    /**
     * Return informations about the current user
     *
     * @return array
     */
    public function getUserInfo()
    {
        return
            [
                'id' => $this->id,
                'username' => $this->username,
                'mail' => $this->mail,
                'is_admin' => $this->is_admin
            ];
    }

    /**
     * Return a user from database by his name
     *
     * @param string $name
     *
     * @return GLPI\Telemetry\Models\User
     */
    public function getUser($name)
    {
        $user = $this::where('username', '=', $name)->first();
        return $user;
    }

    /**
     * Test if a username exist in database
     *
     * @param string $username
     *
     * @return boolean
     */
    public function usernameExist($username)
    {
        if (! is_null($this->where('username', '=', $username)->first())) {
            return true;
        } else {
            return false;
        }
    }
}
