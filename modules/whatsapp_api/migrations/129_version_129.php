<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_129 extends App_module_migration
{
    public function up()
    {
        // Get codeigniter instance
        $CI = &get_instance();

        add_option("whatsapp_cron_has_run_from_cli", 0);

        if (!$CI->db->table_exists(db_prefix().'scheduled_whatsapp_messages')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'scheduled_whatsapp_messages` (
                `id` int NOT NULL AUTO_INCREMENT,
                `whatsapp_mapped_template_id` int NOT NULL,
                `rel_id` int NOT NULL,
                `rel_type` varchar(15) NOT NULL,
                `scheduled_at` datetime NOT NULL,
                `executed_at` datetime DEFAULT NULL,
                `error_message` text,
                `status` varchar(15) NOT NULL DEFAULT "PENDING",
                `type` varchar(10) DEFAULT NULL,
                `broadcast_message` text,
                `debug_mode` tinyint(1) NOT NULL DEFAULT "0",
                `image_url` text,
                `whatsapp_after_number` int DEFAULT NULL,
                `whatsapp_after_type` varchar(20) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }

        if ($CI->db->table_exists(db_prefix() . 'whatsapp_templates_mapping')) {
            if (!$CI->db->field_exists('whatsapp_after_number', db_prefix() . 'whatsapp_templates_mapping')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_templates_mapping` ADD `whatsapp_after_number` INT NULL');
            }
            if (!$CI->db->field_exists('whatsapp_after_type', db_prefix() . 'whatsapp_templates_mapping')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_templates_mapping` ADD `whatsapp_after_type` VARCHAR(20) NULL');
            }
        }

        if ($CI->db->field_exists('buttons_data', db_prefix() . 'whatsapp_templates')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_templates` CHANGE `buttons_data` `buttons_data` TEXT DEFAULT NULL');
        }

        _maybe_create_upload_path(FCPATH.'/uploads/whatsapp_api');
        _maybe_create_upload_path(FCPATH.'/uploads/whatsapp_api/invoices');
        _maybe_create_upload_path(FCPATH.'/uploads/whatsapp_api/proposals');
        _maybe_create_upload_path(FCPATH.'/uploads/whatsapp_api/broadcast_images');
        _maybe_create_upload_path(FCPATH.'/uploads/whatsapp_api/payments');

        add_option('whatsapp_cron_job_queue_enabled', 0);
    }
}