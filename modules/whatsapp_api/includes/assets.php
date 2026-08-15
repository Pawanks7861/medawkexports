<?php
/*
 * Inject css file for whatsapp_api module
 */
hooks()->add_action('app_admin_head', 'whatsapp_api_add_head_components');
function whatsapp_api_add_head_components()
{
    // Check module is enable or not (refer install.php)
    if ('1' == get_option('whatsapp_api_enabled')) {
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/tribute.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/whatsapp_api.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/prism.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';

        if ('template_mapping' == $CI->router->fetch_class() && 'add' == $CI->router->fetch_method()) {
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/material-design-iconic-font.min.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/devices.min.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/preview.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        }
    }
}

/*
 * Inject Javascript file for whatsapp_api module
 */
hooks()->add_action('app_admin_footer', 'whatsapp_api_load_js');
function whatsapp_api_load_js()
{
    if ('1' == get_option('whatsapp_api_enabled')) {
        $CI = &get_instance();
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>
                var merge_fields = ' .
            json_encode($merge_fields) .
            '
            </script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/whatsapp_api.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        if ('template_mapping' == $CI->router->fetch_class() && 'add' == $CI->router->fetch_method()) {
            echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/preview.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        }
    }
}

hooks()->add_action('app_init', WHATSAPP_API_MODULE.'_actLib');
function whatsapp_api_actLib()
{
    $CI = &get_instance();
    $CI->load->library(WHATSAPP_API_MODULE.'/Whatsapp_api_aeiou');
    $envato_res = $CI->whatsapp_api_aeiou->validatePurchase(WHATSAPP_API_MODULE);

}

hooks()->add_action('pre_activate_module', WHATSAPP_API_MODULE.'_sidecheck');
function whatsapp_api_sidecheck($module_name)
{

}

hooks()->add_action('pre_deactivate_module', WHATSAPP_API_MODULE.'_deregister');
function whatsapp_api_deregister($module_name)
{

}
