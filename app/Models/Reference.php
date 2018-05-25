<?php namespace GLPI\Telemetry\Models;

use GLPI\Telemetry\Models\User as UserModel;

class Reference extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'reference';
    protected $guarded = [
      'status'
    ];
    const DENIED = 0;
    const PENDING = 1;
    const ACCEPTED = 2;
    /**
     * Scope a query to only include references that can be displayed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', '=', self::ACCEPTED);
    }

    public function updateStatus($id, $status)
    {
        return $this::where('id', '=', $id)->update(['status' => $status]);
    }


    /**
     * Find emails from user and reference
     *
     * @param integer $ref_id
     *
     * @return array
    **/
    public function findMails($ref_id)
    {
        $ref_user = new UserModel();
        $user = $ref_user->newInstance();

        $res_ref = $this::where('id', '=', $ref_id)->first();
        $ref_mail = $res_ref->attributes['email'];
        $ref_user_id = $res_ref->attributes['user_id'];

        $res_user = $user::where('id', '=', $ref_user_id)->first();
        $user_mail = $res_user->attributes['email'];

        return
        [
            'user_mail' => $user_mail,
            'ref_mail' => $ref_mail
        ];
    }

    /**
     * Match between the constant and the string value for status
     *
     * @param integer $status
     *
     * @return array
    **/
    public function statusIntToText($status)
    {
        switch ($status) {
            case self::DENIED:
                return "denied";
            break;
            case self::PENDING:
                return "pending";
            break;
            case self::ACCEPTED:
                return "accepted";
            break;
            default:
                return false;
        }
    }
}
