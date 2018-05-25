<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\Reference as ReferenceModel;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $id;
    protected $username;
    protected $references_count;
    protected $guarded = [
      ''
    ];
    protected $fillable = array('username', 'is_admin', 'mail');

    public function setUserInfo()
    {
        $this->references_count = $this->getReferencesCount($this->id);
    }

    public function getUserInfo()
    {
        return
            [
                'id' => $this->id,
                'username' => $this->username,
                'mail' => $this->mail,
                'references_count' => $this->references_count,
                'is_admin' => $this->is_admin
            ];
    }

    public function getReferencesCount($user_id)
    {
        return ReferenceModel::where('user_id', "=", $user_id)->get()->count();
    }

    public function getUser($name)
    {
        $test = $this::where('username', '=', $name)->first();
        return $test;
    }

    //return true if user exist
    public function usernameExist($username)
    {
        if (! is_null($this->where('username', '=', $username)->first())) {
            return true;
        } else {
            return false;
        }
    }
}
