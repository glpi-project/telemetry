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
     * Find emails
     *
     * @param reference id
     *
     * @return an array [user's email, reference's email]
     * Return array even if only one of this is null.
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


    public function statusIntToText($status)
    {
        switch($status){
            case self::DENIED : return "denied";
            break;
            case self::PENDING : return "pending";
            break;
            case self::ACCEPTED : return "accepted";
            break;
            default : return false;
        }
    }

    /**
     * This function will set filters (orderby, sort and status)
     * It is used in several pages
     * @param array $args[] Contain the route's parameters like status
     * @param array $get[] Contain Query Params
     * @param array $session_ref[] Contain the $_SESSION['reference']
     * @param string $page Contain the page to which the filter will be applied
     * @param boolean $status_filter To set or not status filter (use for References page)
     * @return array [args, session_ref] Who can be changed by the function
    **/
    public static function setDifferentsFilters(array $get, array $args, $session_ref, $page, $status_filter = true)
    {

        // default session param for this controller
        if (!isset($session_ref)) {
            $session_ref['orderby'] = 'created_at';
            $session_ref['sort'] = 'desc';
        }
        
        /**
         * if status is not specified in parameter $args :
         * default -> pending
         * and if the status is specified in the $session_ref, we'll take it.
         * That allow the pagination doesn't specified any status parameter.
        **/

        if ($status_filter) {
            if(!isset($args['status'] )){
                $args['status'] = 1;
                if($session_ref[$page] !== null){
                    $args['status'] = $session_ref[$page];
                }
            }
            $session_ref[$page] = $args['status'];
        }

        // manage sorting
        if (isset($get['orderby'])) {
            if ($session_ref['orderby'] == $get['orderby']) {
               // toggle sort if orderby requested on the same column
                $session_ref['sort'] = ($session_ref['sort'] == "desc"
                                                ? "asc"
                                                : "desc");
            }
            $session_ref['orderby'] = $get['orderby'];
        }
        $session_ref['pagination'] = 15;

        return $session_ref;
    }
}
