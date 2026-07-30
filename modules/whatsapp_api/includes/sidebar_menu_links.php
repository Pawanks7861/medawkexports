<?php

/*
 * Inject sidebar menu and links for customtables module
 */
 hooks()->add_action('admin_init', 'whatsapp_api_module_init_menu_items');
 function whatsapp_api_module_init_menu_items()
 {
  $CI = &get_instance();

    if (
        has_permission('whatsapp_api', '', 'list_templates_view') ||
        has_permission('whatsapp_api', '', 'template_mapping_view') ||
        has_permission('whatsapp_api', '', 'whatsapp_log_details_view') ||
        has_permission('whatsapp_api', '', 'broadcast_messages')
    ) {
        $CI->app_menu->add_sidebar_menu_item('whatsapp_api', [
            'slug'     => 'whatsapp_api',
            'name'     => _l('whatsapp'),
            'position' => 30,
            'icon'     => 'fa fa-brands fa-whatsapp menu-icon',
        ]);

        if (has_permission('whatsapp_api', '', 'list_templates_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_template_view',
                'name'     => _l('template_list'),
                'href'     => admin_url('whatsapp_api'),
                'position' => 1,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'template_mapping_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_template_details',
                'name'     => _l('template_mapping'),
                'href'     => admin_url('whatsapp_api/template_mapping'),
                'position' => 2,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'whatsapp_log_details_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_log_details',
                'name'     => _l('whatsapp_log_details'),
                'href'     => admin_url('whatsapp_api/whatsapp_log_details'),
                'position' => 3,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'broadcast_messages')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_log_details',
                'name'     => _l('broadcast_messages'),
                'href'     => admin_url('whatsapp_api/broadcast_messages'),
                'position' => 4,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'template_mapping_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_cron',
                'name'     => _l('whatsapp_cron'),
                'href'     => admin_url('settings?group=whatsapp_cron'),
                'position' => 2,
            ]);
        }
    }

    $CI->app_tabs->add_settings_tab('whatsapp', [
        'name'     => _l('whatsapp_cloud_api'),
        'view'     => 'whatsapp_api/settings/whatsapp_settings',
        'icon'     => 'fa fa-brands fa-whatsapp menu-icon',
        'position' => 50,
    ]);

    $CI->app_tabs->add_settings_tab('whatsapp_cron', [
        'name'     => _l('whatsapp_cron'),
        'view'     => 'whatsapp_api/settings/whatsapp_cron',
        'position' => 52,
    ]);


};
