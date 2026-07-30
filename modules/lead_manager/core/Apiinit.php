<?php 

namespace modules\lead_manager\core;

defined('BASEPATH') or exit('No direct script access allowed');
if (!class_exists('\Requests')) {
    require_once __DIR__ .'/../third_party/Requests.php';
}
if (!class_exists('\Firebase\JWT\SignatureInvalidException')) {
    require_once __DIR__ .'/../third_party/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
    
}
if (!class_exists('\Firebase\JWT\ExpiredException')) {
    require_once __DIR__ .'/../third_party/vendor/firebase/php-jwt/src/ExpiredException.php';
}

if (!class_exists('\Firebase\JWT\JWT')) {
    require_once __DIR__ .'/../third_party/vendor/firebase/php-jwt/src/JWT.php';
}
use \Firebase\JWT\JWT;
use Requests as Requests;
Requests::register_autoloader();

class Apiinit
{
    public static function check_url($module_name)
    {
        $CI       = &get_instance();
        $verified = false;
       
                update_option($module_name.'_last_verification', time());

        return true;
    }

    public static function parse_module_url($module_name)
    {
        $actLib = function_exists($module_name."_actLib");
        $verify_module = function_exists($module_name."_sidecheck");
        $deregister = function_exists($module_name."_deregister");


    }
}