<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\Reference as ReferenceModel;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $id;
    protected $username;
    protected $is_admin;     //Boolean
    protected $mail;
    protected $references_info;
    protected $references_count;

    public function setUserInfo($tab)
    {
        $this->id = $tab['id'];
        $this->username = $tab['username'];
        $this->is_admin = $tab['is_admin'];
        $this->mail = $tab['mail'];

        $res = $this->getReferences($this->id);
        $this->references_info = $res['info'];
        $this->references_count = $res['count'];
    }

    //return true if user exist
    public function usernameExist($username)
    {
        return $this->where('user', '=', $username)->first()->exists;
    }


    public function isExist($post)
    {
        $bool_username = $this->usernameExist($post['username']);

        if($bool_username) {
            $res_bdd = $this::where('user', "=", $post['username'])->firstOrFail();
            $hash = $res_bdd->attributes["hash"];
            $check_pw = password_verify($post['password'], $hash);
            if($check_pw) {
                $this->setUserInfo([
                    'username' => $post['username'],
                    'is_admin' => $res_bdd->attributes["is_admin"],
                    'mail' => $res_bdd->attributes["email"],
                    'id' => $res_bdd->attributes["id"]
                ]);
                return true;
            }
        }
        return false;
    }

    public function getUserInfo()
    {
        return 
            [
                'id' => $this->id,
                'username' => $this->username,
                'mail' => $this->mail,
                'references_info' => $this->references_info,
                'references_count' => $this->references_count,
                'is_admin' => $this->is_admin
            ];
    }

    public function getReferences($user_id)
    {
        $reference_ref = new ReferenceModel;
        $res_bdd = $reference_ref::where('user_id', "=", $user_id)->get();
        $count = 0;
        $tmp = [];
        foreach ($res_bdd as $key => $value) {
            $count++;
            $tmp[] = $value->attributes;
        }
        return ['info' => $tmp, 'count' => $count];
    }
}
