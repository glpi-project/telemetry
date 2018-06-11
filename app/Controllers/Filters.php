<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\PageAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\User as UserModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Filters extends PageAbstract
{

    /**
     * This function will set filters (orderby, sort and status).
     * It is used in several pages
     *
     * @param array $args[]     Contain the route's parameters like status
     * @param array $get[]      Contain Query Params
     * @param boolean $status_filter    To set or not status filter (use for References page)
     *
     * @return array
    **/
    public function setDifferentsFilters(Request $req, Response $res, array $args)
    {
        if (isset($args['type_page'])) {
            $management_name = $args['type_page'];

            $_SESSION[$management_name] = $this->setDefaultFilters('orderby', 'created_at', $_SESSION[$management_name], $args);
            $_SESSION[$management_name] = $this->setDefaultFilters('sort', 'desc', $_SESSION[$management_name], $args);
            $_SESSION[$management_name] = $this->setDefaultFilters('pagination', 10, $_SESSION[$management_name], $args);
            $_SESSION[$management_name] = $this->setDefaultFilters('search', "null", $_SESSION[$management_name], $args);
            $_SESSION[$management_name] = $this->setDefaultFilters('action_code', "null", $_SESSION[$management_name], $args);

            switch ($management_name) {
                case 'users_management':
                    $_SESSION[$management_name] = $this->setDefaultFilters('customFilter', "null", $_SESSION[$management_name], $args);
                    $_SESSION[$management_name]['search_on'] = 'Username';
                    break;

                case 'reference_management':
                case 'profile':
                    $_SESSION[$management_name] = $this->setDefaultFilters('customFilter', 1, $_SESSION[$management_name], $args);
                    $_SESSION[$management_name]['search_on'] = 'Name';
                    break;
            }

            if (isset($args['orderby']) && $_SESSION[$management_name]['orderby'] == $args['orderby']) {
               // toggle sort if orderby requested on the same column
                $_SESSION[$management_name]['sort'] = ($_SESSION[$management_name]['sort'] == "desc"
                                                ? "asc"
                                                : "desc");
            }
        }

        return $this->typePageRedirect($req, $res, $management_name);
    }

    public function setDefaultFilters($filter_name, $default_value, $session_ref, $args)
    {
        if (isset($args[$filter_name])) {
            $session_ref[$filter_name] = $args[$filter_name];
        } elseif (isset($session_ref[$filter_name])) {
            $session_ref[$filter_name] = $session_ref[$filter_name];
        } else {
            $session_ref[$filter_name] = $default_value;
        }
        return $session_ref;
    }
}
