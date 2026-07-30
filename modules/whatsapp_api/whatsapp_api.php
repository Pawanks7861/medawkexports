<?php

defined('BASEPATH') || exit('No direct script access allowed');

/*
    Module Name: WhatsApp Cloud API Business Integration module
    Module URI: https://codecanyon.net/item/whatsapp-cloud-api-business-integration-module-for-perfex-crm/38690826
    Description: Keep your Customers & Staff updated in real-time about New Invoices, Project's Tasks and more!
    Version: 1.2.9
    Requires at least: 3.0.*
*/

require_once __DIR__.'/vendor/autoload.php';

use WpOrg\Requests\Requests as Whatsapp_api_Requests;

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
 */
define('WHATSAPP_API_MODULE', 'whatsapp_api');

// Constant for whatsapp api upload path
define('WHATSAPP_API_UPLOAD_FOLDER', FCPATH.'uploads/whatsapp_api/');

// Get codeigniter instance
$CI = &get_instance();

require_once __DIR__ . '/install.php';

/*
 * Register activation module hook
 */
register_activation_hook(WHATSAPP_API_MODULE, 'whatsapp_api_module_activation_hook');
function whatsapp_api_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';

    // create invoices and proposal folders within module folder. perfex has .htaccess that is blocking access
    _maybe_create_upload_path(WHATSAPP_API_UPLOAD_FOLDER);
    _maybe_create_upload_path(WHATSAPP_API_UPLOAD_FOLDER . 'invoices');
    _maybe_create_upload_path(WHATSAPP_API_UPLOAD_FOLDER . 'proposals');
    _maybe_create_upload_path(WHATSAPP_API_UPLOAD_FOLDER . 'broadcast_images');
    _maybe_create_upload_path(WHATSAPP_API_UPLOAD_FOLDER . 'payments');
}

/*
 * Register deactivation module hook
 */
register_deactivation_hook(WHATSAPP_API_MODULE, 'whatsapp_api_module_deactivation_hook');
function whatsapp_api_module_deactivation_hook()
{
    update_option('whatsapp_api_enabled', 0);

    $my_files_list = [
        VIEWPATH . 'admin/staff/my_profile.php'
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }
}

/*
 * Register language files, must be registered if the module is using languages
 */
register_language_files(WHATSAPP_API_MODULE, [WHATSAPP_API_MODULE]);

/*
 * Load module helper file
 */
$CI->load->helper(WHATSAPP_API_MODULE . '/whatsapp_api');

/*
 * Load module Library file
 */
$CI->load->library(WHATSAPP_API_MODULE . '/whatsapp_api_lib');

get_instance()->config->load(WHATSAPP_API_MODULE . '/config');




require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

hooks()->add_action('lead_created', 'wa_lead_added_hook');
function wa_lead_added_hook($leadID)
{
    if (!is_array($leadID)) {
        $CI = &get_instance();
        $CI->whatsapp_api_lib->send_mapped_template('leads', false, 'MANUAL', $leadID);
    }
}

hooks()->add_action('web_to_lead_form_submitted', 'wa_web_to_lead_added_hook');
function wa_web_to_lead_added_hook($data)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('leads', false, 'MANUAL', $data['lead_id']);
}

hooks()->add_action('contact_created', 'wa_contact_added_hook');
function wa_contact_added_hook($contactID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('client', false, 'MANUAL', $contactID);
}

hooks()->add_action('after_invoice_added', 'wa_invoice_added_hook');
function wa_invoice_added_hook($invoiceID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('invoice', false, 'MANUAL', $invoiceID);
}

hooks()->add_action('after_add_task', 'wa_task_added_hook');
function wa_task_added_hook($taskID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('tasks', false, 'MANUAL', $taskID);
}

hooks()->add_action('after_add_project', 'wa_project_added_hook');
function wa_project_added_hook($projectID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('projects', false, 'MANUAL', $projectID);
}

hooks()->add_action('proposal_created', 'wa_proposal_added_hook');
function wa_proposal_added_hook($proposalID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('proposals', false, 'MANUAL', $proposalID);
}
hooks()->add_action('after_payment_added', 'wa_payment_added_hook');
function wa_payment_added_hook($paymentID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('payments', false, 'MANUAL', $paymentID);
}
hooks()->add_action('ticket_created', 'wa_ticket_created_hook');
function wa_ticket_created_hook($ticketID)
{
    $CI = &get_instance();
    $CI->whatsapp_api_lib->send_mapped_template('ticket', false, 'MANUAL', $ticketID);
}

hooks()->add_action('before_cron_run', 'update_whatsapp_template_list');
function update_whatsapp_template_list($manually)
{
    if (!empty(get_option('whatsapp_business_account_id')) && !empty(get_option('whatsapp_access_token')) && !empty(get_option('phone_number_id'))) {
        $CI                           = &get_instance();
        $whatsapp_business_account_id = get_option('whatsapp_business_account_id');
        $whatsapp_access_token        = get_option('whatsapp_access_token');
        $request                      = Whatsapp_api_Requests::get(
            'https://graph.facebook.com/v14.0/' . $whatsapp_business_account_id . '?fields=id,name,message_templates,phone_numbers&access_token=' . $whatsapp_access_token
        );

        $response    = json_decode($request->body);
        $data        = $response->message_templates->data;
        $insert_data = [];

        foreach ($data as $key => $template_data) {
            // Only consider "APPROVED" templates
            if ('APPROVED' == $template_data->status) {
                $insert_data[$key]['template_id']   = $template_data->id;
                $insert_data[$key]['template_name'] = $template_data->name;
                $insert_data[$key]['language']      = $template_data->language;

                $insert_data[$key]['status']   = $template_data->status;
                $insert_data[$key]['category'] = $template_data->category;

                $components = array_column($template_data->components, null, 'type');

                $insert_data[$key]['header_data_format']     = $components['HEADER']->format ?? '';
                $insert_data[$key]['header_data_text']       = $components['HEADER']->text ?? null;
                $insert_data[$key]['header_params_count']    = preg_match_all('/{{(.*?)}}/i', $components['HEADER']->text ?? '', $matches);

                $insert_data[$key]['body_data']            = $components['BODY']->text ?? null;
                $insert_data[$key]['body_params_count']    = preg_match_all('/{{(.*?)}}/i', $components['BODY']->text ?? '', $matches);

                $insert_data[$key]['footer_data']          = $components['FOOTER']->text ?? null;
                $insert_data[$key]['footer_params_count']  = preg_match_all('/{{(.*?)}}/i', $components['FOOTER']->text ?? '', $matches);

                $insert_data[$key]['buttons_data']  = json_encode($components['BUTTONS'] ?? []);
            }
        }
        $insert_data_id    = array_column($insert_data, 'template_id');
        $existing_template = $CI->db->where_in(array_column($insert_data, 'template_id'))->get(db_prefix() . 'whatsapp_templates')->result();

        $existing_data_id = array_column($existing_template, 'template_id');

        $new_template_id = array_diff($insert_data_id, $existing_data_id);
        $new_template    = array_filter($insert_data, function ($val) use ($new_template_id) {
            return in_array($val['template_id'], $new_template_id);
        });


        $directories = [
            FCPATH . 'uploads/whatsapp_api/invoices',
            FCPATH . 'uploads/whatsapp_api/payments',
            FCPATH . 'uploads/whatsapp_api/proposals'
        ];

        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $files = get_filenames($directory);
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                        $filePath = $directory . '/' . $file;
                        @unlink($filePath);
                    }
                }
            }
        }
    }

    // No need to update template data in db because you can't edit template in meta dashboard
    if (!empty($new_template)) {
        $CI->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
        $CI->db->insert_batch(db_prefix() . 'whatsapp_templates', $new_template);
    }
}

hooks()->add_filter('get_upload_path_by_type', 'add_broadcast_images_upload_path', 0, 2);
function add_broadcast_images_upload_path($path, $type)
{
    if ($type == 'broadcast_images') {
        return $path = WHATSAPP_API_UPLOAD_FOLDER . "broadcast_images/";
    }

    return $path;
}

hooks()->add_action('before_staff_login', function ($userDetails) {
    $CI = &get_instance();
    $staffDetails = $CI->db->get_where(db_prefix() . 'staff', ['staffid' => $userDetails['userid']])->row_array();
    if ($staffDetails['two_factor_auth_enabled'] == '0') {
        if ($staffDetails['whatsapp_auth_enabled'] == '1') {
            $CI->db->where('staffid', $staffDetails['staffid']);
            $CI->db->update(db_prefix() . 'staff', [
                'whatsapp_auth_code'           => generateOTP(),
                'whatsapp_auth_code_requested' => date('Y-m-d H:i:s'),
            ]);
            $CI->session->set_userdata('_whatsapp_auth_staff_email', $staffDetails['email']);
            $sent = $CI->whatsapp_api_lib->send_mapped_template('secure_login', true, 'MANUAL', $userDetails['userid']);
            if (!$sent) {
                set_alert('danger', _l('whatsapp_auth_failed_to_send_code'));
                redirect(admin_url('authentication'));
            }
            set_alert('success', _l('whatsapp_auth_code_not_sent'));
            redirect(admin_url('whatsapp_api/whatsapp_api_authentication'));
        }
    }
});

hooks()->add_action('before_update_contact', function ($data, $id) {
    $postData = get_instance()->input->post();

    $data['client_message']  = isset($postData['client_message']) ? 1 :0;
    $data['invoice_message']  = isset($postData['invoice_message']) ? 1 :0;
    $data['tasks_message']     = isset($postData['tasks_message']) ? 1 :0;
    $data['projects_message']  = isset($postData['projects_message']) ? 1 :0;
    $data['proposals_message'] = isset($postData['proposals_message']) ? 1 :0;
    $data['payments_message'] = isset($postData['payments_message']) ? 1 :0;
    $data['ticket_message']  = isset($postData['ticket_message']) ? 1 :0;

    $data['client_forward_phone']  = isset($postData['client_forward_phone']) ? $postData['client_forward_phone'] :null;
    $data['invoice_forward_phone']  = isset($postData['invoice_forward_phone']) ? $postData['invoice_forward_phone'] :null;
    $data['tasks_forward_phone']     = isset($postData['tasks_forward_phone']) ? $postData['tasks_forward_phone'] :null;
    $data['projects_forward_phone']  = isset($postData['projects_forward_phone']) ? $postData['projects_forward_phone'] :null;
    $data['proposals_forward_phone'] = isset($postData['proposals_forward_phone']) ? $postData['proposals_forward_phone'] :null;
    $data['payments_forward_phone'] = isset($postData['payments_forward_phone']) ? $postData['payments_forward_phone'] :null;
    $data['ticket_forward_phone']  = isset($postData['ticket_forward_phone']) ? $postData['ticket_forward_phone'] :null;

    return $data;
}, 10, 2);

hooks()->add_action('after_contact_modal_content_loaded', "whatsapp_updates");
hooks()->add_action('after_client_profile_form_loaded', "whatsapp_updates");

function whatsapp_updates() {
    $contact_id = get_contact_user_id();
    if(is_staff_member()){
        if(get_instance()->uri->segment(3) == "form_contact"){
            $contact_id = get_instance()->uri->segment(5);
        }
    }
    if(!is_primary_contact($contact_id)){
        return;
    }
    $contact = get_instance()->clients_model->get_contact($contact_id);
    $html = "";
    $html .= '<hr />';
    $html .= '<p class="bold email-notifications-label">' . _l("whatsapp_updates") . '</p>';
    $html .= render_checkbox_forward($contact, "client");
    if (has_contact_permission("invoices", $contact_id)) {
        $html .= render_checkbox_forward($contact, "invoice");
    }
    if (has_contact_permission("projects", $contact_id)) {
        $html .= render_checkbox_forward($contact, "tasks");
        $html .= render_checkbox_forward($contact, "projects");
    }
    if (has_contact_permission("proposals", $contact_id)) {
        $html .= render_checkbox_forward($contact, "proposals");
    }
    if (has_contact_permission("invoices", $contact_id)) {
        $html .= render_checkbox_forward($contact, "payments");
    }
    if (has_contact_permission("support", $contact_id)) {
        $html .= render_checkbox_forward($contact, "ticket");
    }
    echo $html;
};
