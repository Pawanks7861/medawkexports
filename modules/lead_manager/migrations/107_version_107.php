<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'lm_ai_chat_form')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "lm_ai_chat_form` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
             `form_key` varchar(32) NOT NULL,
            `lead_source` int(11) NOT NULL,
            `lead_status` int(11) NOT NULL,
            `notify_lead_imported` int(11) NOT NULL DEFAULT 1,
            `notify_type` varchar(20) DEFAULT NULL,
            `notify_ids` longtext DEFAULT NULL,
            `responsible` int(11) NOT NULL DEFAULT 0,
            `name` varchar(191) NOT NULL,
            `form_data` longtext DEFAULT NULL,
            `recaptcha` int(11) NOT NULL DEFAULT 0,
            `submit_btn_name` varchar(40) DEFAULT NULL,
            `submit_btn_text_color` varchar(10) DEFAULT '#ffffff',
            `submit_btn_bg_color` varchar(10) DEFAULT '#84c529',
            `success_submit_msg` mediumtext DEFAULT NULL,
            `submit_action` int(11) DEFAULT 0,
            `lead_name_prefix` varchar(255) DEFAULT NULL,
            `submit_redirect_url` longtext DEFAULT NULL,
            `language` varchar(40) DEFAULT NULL,
            `allow_duplicate` int(11) NOT NULL DEFAULT 1,
            `mark_public` int(11) NOT NULL DEFAULT 0,
            `track_duplicate_field` varchar(20) DEFAULT NULL,
            `track_duplicate_field_and` varchar(20) DEFAULT NULL,
            `create_task_on_duplicate` int(11) NOT NULL DEFAULT 0,
            `dateadded` datetime NOT NULL
              ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }
    }
}
