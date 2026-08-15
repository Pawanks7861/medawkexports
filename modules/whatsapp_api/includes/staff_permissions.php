<?php


// permission for whatsapp api
hooks()->add_filter('staff_permissions', 'whatsapp_api_module_permissions_for_staff');
function whatsapp_api_module_permissions_for_staff($permissions)
{
    $viewGlobalName      = _l('permission_view') . '(' . _l('permission_global') . ')';
    $allPermissionsArray = [
        'list_templates_view'     => _l('list_of_templates_view'),
        'template_mapping_view'   => _l('template_mapping_view'),
        'template_mapping_add'   => _l('template_mapping_create'),
        'whatsapp_log_details_view'     => _l('whatsapp_log_details_view'),
        'whatsapp_log_details_clear'     => _l('whatsapp_log_details_clear'),
        'broadcast_messages'   => _l('broadcast_messages'),
    ];
    $permissions['whatsapp_api'] = [
        'name'         => _l('whatsapp_api'),
        'capabilities' => $allPermissionsArray,
    ];

    return $permissions;
}