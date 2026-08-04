<?php
defined('BASEPATH') or exit('No direct script access allowed');
$module_name = 'leads';


$project_filter_name = 'project';
$source_filter_name = 'source';
$status_filter_name = 'status';
$assigned_filter_name = 'assigned';
$month_filter_name = 'month';
$duplicate_filter_name = 'duplicate';
// Get CI instance
$CI = &get_instance();
$CI->load->model('gdpr_model');
$CI->load->model('leads_model');
$CI->load->model('staff_model');

$statuses      = $CI->leads_model->get_status();
$enableConsent = is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1';
if ($enableConsent) {
    $consent_purposes = $CI->gdpr_model->get_consent_purposes();
}

$custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);

// Base columns
$aColumns = [
    0, // checkbox placeholder
    db_prefix() . 'leads.id as id',
    db_prefix() . 'leads.name as name',
    db_prefix() . 'leads.phonenumber as phonenumber',
    db_prefix() . 'leads.alt_phonenumber as alt_phonenumber',
    db_prefix() . 'leads.projects as projects',
    db_prefix() . 'leads.assigned as assigned',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_sources.name as source_name',
    db_prefix() . 'leads.dateadded as dateadded',
    db_prefix() . 'leads.lead_value as lead_value',
    db_prefix() . 'leads.call_time as call_time',
    // db_prefix() . 'leads.lastcontact as lastcontact',
    db_prefix() . 'leads.broker as broker',
    db_prefix() . 'leads.contact_details as contact_details',
    db_prefix() . 'leads.duplicate as duplicate',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
];

if ($enableConsent) {
    $aColumns[] = '1';
}

// Joins
$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
];

// Custom-fields
$cfIndex = 0;
foreach ($custom_fields as $field) {
    $alias = is_cf_date($field) ? 'date_picker_cvalue_' . $cfIndex : 'cvalue_' . $cfIndex;
    $aColumns[] = 'ctable_' . $cfIndex . '.value as ' . $alias;
    $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' .
        $cfIndex . ' ON ' . db_prefix() . 'leads.id = ctable_' .
        $cfIndex . '.relid
             AND ctable_' . $cfIndex . '.fieldto = "' . $field['fieldto'] . '"
             AND ctable_' . $cfIndex . '.fieldid  = ' . $field['id'];
    $cfIndex++;
}

$where = [];

// -- Filters --

// Status (uncomment if you want it)
if ($CI->input->post('status') && count($CI->input->post('status')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'leads.status IN (' . implode(',', $CI->input->post('status')) . ')';
}

if ($CI->input->post('source') && count($CI->input->post('source')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'leads.source IN (' . implode(',', $CI->input->post('source')) . ')';
}

if ($CI->input->post('project') && count($CI->input->post('project')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'leads.projects IN (' . implode(',', $CI->input->post('project')) . ')';
}

if ($CI->input->post('assigned') && count($CI->input->post('assigned')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'leads.assigned IN (' . implode(',', $CI->input->post('assigned')) . ')';
}

if ($CI->input->post('month') && count($CI->input->post('month')) > 0) {
    $where[] = 'AND MONTH(' . db_prefix() . 'leads.dateadded) IN (' . implode(',', $CI->input->post('month')) . ')';
}

if ($CI->input->post('duplicate') && count($CI->input->post('duplicate')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'leads.duplicate IN (' . implode(',', $CI->input->post('duplicate')) . ')';
}


$project_filter_name_value = !empty($this->ci->input->post('project')) ? implode(',', $this->ci->input->post('project')) : NULL;
update_module_filter($module_name, $project_filter_name, $project_filter_name_value);

$source_filter_name_value = !empty($this->ci->input->post('source')) ? implode(',', $this->ci->input->post('source')) : NULL;
update_module_filter($module_name, $source_filter_name, $source_filter_name_value);

$status_filter_name_value = !empty($this->ci->input->post('status')) ? implode(',', $this->ci->input->post('status')) : NULL;
update_module_filter($module_name, $status_filter_name, $status_filter_name_value);

$assigned_filter_name_value = !empty($this->ci->input->post('assigned')) ? implode(',', $this->ci->input->post('assigned')) : NULL;
update_module_filter($module_name, $assigned_filter_name, $assigned_filter_name_value);

$month_filter_name_value = !empty($this->ci->input->post('month')) ? implode(',', $this->ci->input->post('month')) : NULL;
update_module_filter($module_name, $month_filter_name, $month_filter_name_value);

$duplicate_filter_name_value = !empty($this->ci->input->post('duplicate')) ? implode(',', $this->ci->input->post('duplicate')) : NULL;
update_module_filter($module_name, $duplicate_filter_name, $duplicate_filter_name_value);
$staffid = get_staff_user_id();

$get_assgined_projects = get_assgined_projects($staffid);

$project_ids = !empty($get_assgined_projects) ? array_column($get_assgined_projects, 'team_manage_id') : [];
if (is_admin()) {
} else {
    $project_condition = '';
    if (!empty($project_ids)) {
        $project_condition = ' OR projects IN (' . implode(',', $project_ids) . ')';
    }

    array_push($where, 'AND (assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1 ' . $project_condition . ')');
}



$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'leads',
    $join,
    $where,
    [
        'junk',
        'lost',
        'color',
        'status',
        'assigned',
        'firstname as assigned_firstname',
        'lastname  as assigned_lastname',
        db_prefix() . 'leads.addedfrom as addedfrom',
        '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',
        'duplicate',
        'zip'
    ]
);

$output  = $result['output'];
$rResult = $result['rResult'];
$srNo    = (int)$CI->input->post('start') + 1;

foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="'
        . $aRow['id'] . '"><label></label></div>';

    // Serial #
    $href  = 'href="' . admin_url('leads/index/' . $aRow['id'])
        . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
    $row[] = '<a ' . $href . '>' . $srNo . '</a>';

    // Name + row-options
    $name     = preg_match('/[a-zA-Z0-9]/', $aRow['name'])
        ? e($aRow['name'])
        : 'No Name';
    $nameRow  = '<a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true')
        . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . $name . '</a><div class="row-options">'
        . '<a ' . $href . '>' . _l('view') . '</a>';
    $locked   = ($aRow['is_converted'] > 0
        && !is_admin()
        && get_option('lead_lock_after_convert_to_customer') == 1);
    if (!$locked) {
        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true')
            . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">'
            . _l('edit') . '</a>';
    }
    if (
        $aRow['addedfrom'] == get_staff_user_id()
        || staff_can('delete', 'leads')
    ) {
        $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id'])
            . '" class="_delete text-danger">' . _l('delete') . '</a>';
    }
    $row[] = $nameRow . '</div>';

    if (is_gdpr() && $consentLeads == '1') {
        $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
        $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

        foreach ($consents as $consent) {
            $consentHTML .= '<p style="margin-bottom:0px;">' . e($consent['name']) . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }
        $row[] = $consentHTML;
    }

    $phone = trim($aRow['phonenumber']);

    if (!empty($phone)) {
        // Check if the phone number already starts with +91 or +1
        if (strpos($phone, '+91') !== 0 && strpos($phone, '+1') !== 0) {
            // If the phone number starts with "1", add +1; otherwise, add +91.
            if (strpos($phone, '1') === 0) {
                $phone = '+1' . $phone;
            } else {
                $phone = '+91' . $phone;
            }
        }
        $row[] = '<a href="tel:' . e($phone) . '">' . e($phone) . '</a>';
    } else {
        $row[] = '';
    }
    $row[] = !empty($aRow['alt_phonenumber'])
        ? '<a href="tel:' . e($aRow['alt_phonenumber']) . '">' . e($aRow['alt_phonenumber']) . '</a>'
        : '';

    // Projects
    $row[] = !empty($aRow['projects'])
        ? get_projects($aRow['projects'])
        : '';

    $assignedOutput = '';
    if ($aRow['assigned'] != 0) {
        $full_name = e($aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname']);

        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
            'staff-profile-image-small',
        ]) . '</a>';

        // For exporting
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
    }

    $row[] = $assignedOutput;


    if ($aRow['status_name'] == null) {
        if ($aRow['lost'] == 1) {
            $outputStatus = '<span class="label label-danger">' . _l('lead_lost') . '</span>';
        } elseif ($aRow['junk'] == 1) {
            $outputStatus = '<span class="label label-warning">' . _l('lead_junk') . '</span>';
        }
    } else {
        $outputStatus = '<span class="lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';">' . e($aRow['status_name']);

        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"></span>';
            $outputStatus .= '</a>';
            // <i class="fa-solid fa-chevron-down tw-opacity-70"></i>

            $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
            foreach ($statuses as $leadChangeStatus) {
                if ($aRow['status'] != $leadChangeStatus['id']) {
                    $outputStatus .= '<li>
                  <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                     ' . e($leadChangeStatus['name']) . '
                  </a>
               </li>';
                }
            }
            $outputStatus .= '</ul>';
            $outputStatus .= '</div>';
        }
        $outputStatus .= '</span>';
    }

    $row[] = $outputStatus;

    $row[] = e($aRow['source_name']);
    $row[] = '<span data-toggle="tooltip" data-title="' . e(_dt($aRow['dateadded'])) . '" class="text-has-action is-date">' . e(time_ago($aRow['dateadded'])) . '</span>';
    $row[] = e($aRow['lead_value']);
    $row[] = e($aRow['call_time']);
    // $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || !is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . e(_dt($aRow['lastcontact'])) . '" class="text-has-action is-date">' . e(time_ago($aRow['lastcontact'])) . '</span>');
    $row[]        = e($aRow['broker']);
    $row[]        = e($aRow['contact_details']);
    $check_double_entry = $aRow['duplicate'];

    if ($check_double_entry > 0) {
        $check_double_messgae = '<span style="color: #fd2c2c;font-weight: bold;">Duplicate Entry Alert!</span>';
    } else {
        $check_double_messgae = '';
    }

    $row[] .= $check_double_messgae;
    $row[] .= date('M', strtotime($aRow['dateadded']));
    // Tags
    $row[] = render_tags($aRow['tags']);

    // Custom fields
    $cfIdx = 0;
    foreach ($custom_fields as $field) {
        $alias = is_cf_date($field)
            ? 'date_picker_cvalue_' . $cfIdx
            : 'cvalue_' . $cfIdx;
        $row[] = strpos($alias, 'date_picker_') !== false
            ? _d($aRow[$alias])
            : $aRow[$alias];
        $cfIdx++;
    }

    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    if ($aRow['assigned'] == get_staff_user_id()) {
        $row['DT_RowClass'] = 'info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $output['aaData'][] = $row;
    $srNo++;
}
