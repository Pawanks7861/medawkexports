<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('WHATSAPP_API_CRON', true);

class Whatsapp_api_cron_model extends App_Model
{
    public $manually = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function run($manually = false)
    {
        hooks()->do_action('before_whatsapp_cron_run', $manually);

        // update_option('last_whatsapp_cron_run', time());

        if ($manually == true) {
            $this->manually = true;
            log_activity('Whatsapp Api Cron Invoked Manually');
        }

        $this->db->where("scheduled_at < ", "NOW()", false);
        $this->db->where("type", "regular");
        $this->db->where_in("status", ["PENDING"]);
        $res = $this->db->get("scheduled_whatsapp_messages");
        $mapped_template_cron_data = $res->result();

        foreach ($mapped_template_cron_data as $whatsapp) {
            $this->whatsapp_api_lib->send_mapped_template($whatsapp->rel_type, false, $whatsapp->id, $whatsapp->rel_id);
        }

        $this->db->where("scheduled_at < ", "NOW()", false);
        $this->db->where("type", "broadcast");
        $this->db->where_in("status", ["PENDING"]);
        $res = $this->db->get("scheduled_whatsapp_messages");
        $broadcast_message_cron_data = $res->result();

        $this->load->model('leads_model');
        $this->load->model('staff_model');

        foreach ($broadcast_message_cron_data as $whatsapp) {
            switch ($whatsapp->rel_type) {
                case 'leads':
                    $receiverData[1] = (array) $this->leads_model->get($whatsapp->rel_id);
                    break;

                case 'staff':
                    $receiverData[1] = (array) $this->staff_model->get($whatsapp->rel_id);
                    break;

                case 'client':
                    $client[1] = (array) $this->clients_model->get($whatsapp->rel_id);
                    $contactData = array_map(function ($client) {
                        $primaryContact = get_primary_contact_user_id($client['userid']);
                        $contact         = $this->clients_model->get_contact($primaryContact);
                        if (!empty($contact)) {
                            $client['phonenumber'] = $contact->phonenumber;
                        }
                        return $client;
                    }, $client);
                    $receiverData = $contactData;
                    break;
            }

            $postData = [
                'template_name' => $whatsapp->whatsapp_mapped_template_id,
                'broadcast_message' => $whatsapp->broadcast_message,
                'rel_type' => $whatsapp->rel_type,
                'debug_mode' => $whatsapp->debug_mode,
                'whatsapp_after_number' => $whatsapp->whatsapp_after_number,
                'whatsapp_after_type' => $whatsapp->whatsapp_after_type
            ];

            $this->whatsapp_api_lib->send_custom_message($receiverData, $postData, $whatsapp->image_url, $whatsapp->id);
        }

        hooks()->do_action('after_whatsapp_cron_run', $manually);
    }
}
