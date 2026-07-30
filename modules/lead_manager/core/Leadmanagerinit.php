<?php

namespace modules\lead_manager\core;

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../third_party/node.php';

use \Firebase\JWT\JWT as LMJWT;
use \Firebase\JWT\Key as LMKEY;
use WpOrg\Requests\Requests as Lm_Requests;

class Leadmanagerinit
{
    public static function check_url($module_name)
    {
        $CI       = &get_instance();
        $verified = false;

                update_option($module_name . '_last_verification', time());

        return true;
    }

    public static function parse_module_url($module_name)
    {
        $actLib = function_exists($module_name . "_actLib");
        $verify_module = function_exists($module_name . "_sidecheck");
        $deregister = function_exists($module_name . "_deregister");


    }
}
