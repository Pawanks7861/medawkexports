<?php
defined('BASEPATH') or exit('No direct script access allowed');
$module_name = 'tasks';

$status_filter_name = 'task_status';
$task_priority_name = 'task_priority';
$period_name = 'period';
$task_assignees_name = 'task_assignees';
$lead_status_name = 'lead_status';

// Get CI instance
$CI = &get_instance();
$CI->load->model('tasks_model');

$hasPermissionEdit = staff_can('edit', 'tasks');
$hasPermissionDelete = staff_can('delete', 'tasks');
$tasksPriorities = get_tasks_priorities();
$task_statuses = $CI->tasks_model->get_statuses();

// Base columns
$aColumns = [
    0, // bulk actions
    db_prefix() . 'tasks.id as id',
    db_prefix() . 'tasks.name as task_name',
    db_prefix() . 'tasks.description as description',
    '(CASE 
        WHEN ' . db_prefix() . 'leads.status = 16 THEN 1
        WHEN ' . db_prefix() . 'leads.status = 2  THEN 2
        WHEN ' . db_prefix() . 'leads.status = 3  THEN 3
        WHEN ' . db_prefix() . 'leads.status = 12 THEN 4
        WHEN ' . db_prefix() . 'leads.status = 6  THEN 5
        ELSE 999
      END) as status_order',
    db_prefix() . 'tasks.status as status',
    db_prefix() . 'tasks.duedate as duedate',
    get_sql_select_task_asignees_full_names() . ' as assignees',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'tasks.id and rel_type="task" ORDER by tag_order ASC) as tags',
    db_prefix() . 'tasks.priority as priority',
];

// Custom fields - FIXED: Use LIMIT 1 to prevent multiple rows
$custom_fields = get_table_custom_fields('tasks');
$customFieldsColumns = [];
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    $customFieldsColumns[] = $selectAs;
    $aColumns[] = '(SELECT value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=' . db_prefix() . 'tasks.id AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $field['id'] . ' AND ' . db_prefix() . 'customfieldsvalues.fieldto="' . $field['fieldto'] . '" LIMIT 1) as ' . $selectAs;
}

$aColumns = hooks()->apply_filters('tasks_table_sql_columns', $aColumns);

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

// Where conditions
$where = [];
$join = [
    'LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'tasks.rel_id AND ' . db_prefix() . 'tasks.rel_type = "lead"'
];

if (staff_cant('view', 'tasks')) {
    $where[] = get_tasks_where_string();
}

// Dashboard my tasks table
if ($CI->input->post('my_tasks')) {
    $where[] = 'AND (' . db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ') AND status != ' . Tasks_model::STATUS_COMPLETE . ')';
}

if ($CI->input->post('task_status') && count($CI->input->post('task_status')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'tasks.status IN (' . implode(',', array_map('intval', $CI->input->post('task_status'))) . ')';
}

if ($CI->input->post('task_priority') && count($CI->input->post('task_priority')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'tasks.priority IN (' . implode(',', array_map('intval', $CI->input->post('task_priority'))) . ')';
}

if ($CI->input->post('task_assignees') && count($CI->input->post('task_assignees')) > 0) {
    $conditions = [];
    foreach ($CI->input->post('task_assignees') as $assignee_id) {
        $assignee_id = (int)$assignee_id;
        $conditions[] = 'EXISTS (SELECT 1 FROM ' . db_prefix() . 'task_assigned WHERE taskid = ' . db_prefix() . 'tasks.id AND staffid = ' . $assignee_id . ')';
    }
    $where[] = 'AND (' . implode(' OR ', $conditions) . ')';
}

if ($CI->input->post('lead_status') && count($CI->input->post('lead_status')) > 0) {
    $statuses = implode(',', array_map('intval', $CI->input->post('lead_status')));
    $where[] = 'AND ' . db_prefix() . 'tasks.rel_type = "lead"';
    $where[] = 'AND ' . db_prefix() . 'leads.status IN (' . $statuses . ')';
}

// Period filter options
$period_type_filter_val = [
    ['id' => 'today', 'name' => _l('today')],
    ['id' => '3_day', 'name' => _l('last_3_days')],
    ['id' => '7_day', 'name' => _l('last_7_days')],
    ['id' => 'this_week', 'name' => _l('this_week')],
];

// Handle period filter
if ($CI->input->post('period') && is_array($CI->input->post('period')) && count($CI->input->post('period')) > 0) {
    $periods = $CI->input->post('period');
    $dateField = db_prefix() . 'tasks.startdate';

    $periodConditions = [];

    foreach ($periods as $period) {
        switch ($period) {
            case 'today':
                $todayStart = date('Y-m-d 00:00:00');
                $todayEnd = date('Y-m-d 23:59:59');
                $periodConditions[] = "($dateField >= '$todayStart' AND $dateField <= '$todayEnd')";
                break;

            case '7_day':
                $sevenDaysAgo = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $todayEnd = date('Y-m-d 23:59:59');
                $periodConditions[] = "($dateField >= '$sevenDaysAgo' AND $dateField <= '$todayEnd')";
                break;

            case '3_day':
                $threeDaysAgo = date('Y-m-d 00:00:00', strtotime('-3 days'));
                $todayEnd = date('Y-m-d 23:59:59');
                $periodConditions[] = "($dateField >= '$threeDaysAgo' AND $dateField <= '$todayEnd')";
                break;

            case 'this_week':
                $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                $periodConditions[] = "($dateField >= '$monday' AND $dateField <= '$sunday')";
                break;

            default:
                error_log("Unknown period value: " . $period);
                break;
        }
    }

    if (!empty($periodConditions)) {
        $where[] = 'AND (' . implode(' OR ', $periodConditions) . ')';
    }
}

// Update module filters
$status_filter_name_value = !empty($CI->input->post('task_status')) ? implode(',', $CI->input->post('task_status')) : null;
update_module_filter($module_name, $status_filter_name, $status_filter_name_value);

$task_priority_filter_name_value = !empty($CI->input->post('task_priority')) ? implode(',', $CI->input->post('task_priority')) : null;
update_module_filter($module_name, $task_priority_name, $task_priority_filter_name_value);

$period_filter_name_value = !empty($CI->input->post('period')) ? implode(',', $CI->input->post('period')) : null;
update_module_filter($module_name, $period_name, $period_filter_name_value);

$task_assignees_filter_name_value = !empty($CI->input->post('task_assignees')) ? implode(',', $CI->input->post('task_assignees')) : null;
update_module_filter($module_name, $task_assignees_name, $task_assignees_filter_name_value);

$lead_statusfilter_name_value = !empty($CI->input->post('lead_status')) ? implode(',', $CI->input->post('lead_status')) : null;
update_module_filter($module_name, $lead_status_name, $lead_statusfilter_name_value);

// Project visibility filter
$where[] = 'AND CASE 
    WHEN ' . db_prefix() . 'tasks.rel_type = "project" AND EXISTS (
        SELECT 1 FROM ' . db_prefix() . 'project_settings 
        WHERE project_id = ' . db_prefix() . 'tasks.rel_id 
        AND name = "hide_tasks_on_main_tasks_table" 
        AND value = 1
    ) THEN FALSE
    ELSE TRUE
END';

// FIXED: Replace the problematic tasks_rel_name_select_query() with individual CASE statements
$additionalSelect = [
    'rel_type',
    'rel_id',
    'recurring',
    '(CASE ' . db_prefix() . 'tasks.rel_type
        WHEN "contract" THEN (SELECT subject FROM ' . db_prefix() . 'contracts WHERE ' . db_prefix() . 'contracts.id = ' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "estimate" THEN (SELECT id FROM ' . db_prefix() . 'estimates WHERE ' . db_prefix() . 'estimates.id = ' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "proposal" THEN (SELECT id FROM ' . db_prefix() . 'proposals WHERE ' . db_prefix() . 'proposals.id = ' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "invoice" THEN (SELECT id FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "ticket" THEN (SELECT CONCAT(CONCAT("#",' . db_prefix() . 'tickets.ticketid), " - ", ' . db_prefix() . 'tickets.subject) FROM ' . db_prefix() . 'tickets WHERE ' . db_prefix() . 'tickets.ticketid=' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "lead" THEN (SELECT CASE WHEN ' . db_prefix() . 'leads.email = "" THEN ' . db_prefix() . 'leads.name ELSE CONCAT(' . db_prefix() . 'leads.name, " - ", ' . db_prefix() . 'leads.email) END FROM ' . db_prefix() . 'leads WHERE ' . db_prefix() . 'leads.id=' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "customer" THEN (SELECT CASE WHEN company = "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'contacts WHERE userid = ' . db_prefix() . 'clients.userid and is_primary = 1 LIMIT 1) ELSE company END FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.userid=' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "project" THEN (SELECT CONCAT(CONCAT(CONCAT("#",' . db_prefix() . 'projects.id)," - ",' . db_prefix() . 'projects.name), " - ", (SELECT CASE WHEN company = "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'contacts WHERE userid = ' . db_prefix() . 'clients.userid and is_primary = 1 LIMIT 1) ELSE company END FROM ' . db_prefix() . 'clients WHERE userid=' . db_prefix() . 'projects.clientid LIMIT 1)) FROM ' . db_prefix() . 'projects WHERE ' . db_prefix() . 'projects.id=' . db_prefix() . 'tasks.rel_id LIMIT 1)
        WHEN "expense" THEN (SELECT CASE WHEN expense_name = "" THEN ' . db_prefix() . 'expenses_categories.name ELSE CONCAT(' . db_prefix() . 'expenses_categories.name, " (",' . db_prefix() . 'expenses.expense_name,")") END FROM ' . db_prefix() . 'expenses JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category WHERE ' . db_prefix() . 'expenses.id=' . db_prefix() . 'tasks.rel_id LIMIT 1)
        ELSE NULL 
    END) as rel_name',
    'billed',
    '(SELECT staffid FROM ' . db_prefix() . 'task_assigned WHERE taskid=' . db_prefix() . 'tasks.id AND staffid=' . get_staff_user_id() . ' LIMIT 1) as is_assigned',
    '(SELECT GROUP_CONCAT(staffid ORDER BY id ASC SEPARATOR ",") FROM ' . db_prefix() . 'task_assigned WHERE taskid=' . db_prefix() . 'tasks.id) as assignees_ids',
    '(SELECT MAX(id) FROM ' . db_prefix() . 'taskstimers WHERE task_id=' . db_prefix() . 'tasks.id and staff_id=' . get_staff_user_id() . ' and end_time IS NULL) as not_finished_timer_by_current_staff',
    '(SELECT CASE WHEN addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0 THEN 1 ELSE 0 END FROM ' . db_prefix() . 'tasks WHERE id=' . db_prefix() . 'tasks.id LIMIT 1) as current_user_is_creator',
];

$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'tasks',
    $join,
    $where,
    $additionalSelect
);

$output  = $result['output'];
$rResult = $result['rResult'];
$sr = 1;

// Rest of your foreach loop remains the same...
foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    // Task ID
    $row[] = '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" onclick="init_task_modal(' . $aRow['id'] . '); return false;">' . $sr++ . '</a>';

    // Task name and options
    $outputName = '';
    if ($aRow['not_finished_timer_by_current_staff']) {
        $outputName .= '<span class="pull-left text-danger"><i class="fa-regular fa-clock fa-fw tw-mr-1"></i></span>';
    }

    $outputName .= '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" class="display-block main-tasks-table-href-name' . (!empty($aRow['rel_id']) ? ' mbot5' : '') . '" onclick="init_task_modal(' . $aRow['id'] . '); return false;">' . e($aRow['task_name']) . '</a>';

    if (!empty($aRow['rel_name'])) {
        $relName = task_rel_name($aRow['rel_name'], $aRow['rel_id'], $aRow['rel_type']);
        $link = task_rel_link($aRow['rel_id'], $aRow['rel_type']);
        $outputName .= '<span class="hide"> - </span><a class="tw-text-neutral-700 task-table-related tw-text-sm" data-toggle="tooltip" title="' . _l('task_related_to') . '" href="' . $link . '">' . e($relName) . '</a>';
    }

    if ($aRow['recurring'] == 1) {
        $outputName .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('recurring_task') . '</span>';
    }

    $outputName .= '<div class="row-options">';

    $class = 'text-success bold';
    $style = '';
    $tooltip = '';

    $is_assigned = !empty($aRow['is_assigned']);
    if ($aRow['billed'] == 1 || !$is_assigned || $aRow['status'] == Tasks_model::STATUS_COMPLETE) {
        $class = 'text-dark disabled';
        $style = 'style="opacity:0.6;cursor: not-allowed;"';
        if ($aRow['status'] == Tasks_model::STATUS_COMPLETE) {
            $tooltip = ' data-toggle="tooltip" data-title="' . e(format_task_status($aRow['status'], false, true)) . '"';
        } elseif ($aRow['billed'] == 1) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_billed_cant_start_timer') . '"';
        } elseif (!$is_assigned) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_start_timer_only_assignee') . '"';
        }
    }

    if ($aRow['not_finished_timer_by_current_staff']) {
        $outputName .= '<a href="#" class="text-danger tasks-table-stop-timer" onclick="timer_action(this,' . $aRow['id'] . ',' . $aRow['not_finished_timer_by_current_staff'] . '); return false;">' . _l('task_stop_timer') . '</a>';
    } else {
        $outputName .= '<span' . $tooltip . ' ' . $style . '>
            <a href="#" class="' . $class . ' tasks-table-start-timer" onclick="timer_action(this,' . $aRow['id'] . '); return false;">' . _l('task_start_timer') . '</a>
        </span>';
    }

    if ($hasPermissionEdit) {
        $outputName .= '<span class="tw-text-neutral-300"> | </span><a href="#" onclick="edit_task(' . $aRow['id'] . '); return false">' . _l('edit') . '</a>';
    }

    if ($hasPermissionDelete) {
        $outputName .= '<span class="tw-text-neutral-300"> | </span><a href="' . admin_url('tasks/delete_task/' . $aRow['id']) . '" class="text-danger _delete task-delete">' . _l('delete') . '</a>';
    }
    $outputName .= '</div>';

    $row[] = $outputName;

    // Description/comments
    $get_task_comments = get_task_comments($aRow['id']);
    $comments_text = '';
    if (!empty($get_task_comments)) {
        foreach ($get_task_comments as $comment) {
            $comments_text .= strip_tags($comment['content'], '<br><strong>') . "<br>";
        }
    }
    $row[] = $comments_text;

    // Lead status (if applicable)
    $lead_status = '';
    if ($aRow['rel_type'] === 'lead' && !empty($aRow['rel_id'])) {
        $lead_status = get_lead_status_by_lead_id($aRow['rel_id']);
    }
    $row[] = $lead_status;

    // Task status
    $canChangeStatus = ($aRow['current_user_is_creator'] != '0' || $is_assigned || staff_can('edit', 'tasks'));
    $status = get_task_status_by_id($aRow['status']);
    $outputStatus = '<span class="label" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';" task-status-table="' . e($aRow['status']) . '">';
    $outputStatus .= e($status['name']);

    if ($canChangeStatus) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
        $outputStatus .= '</a>';

        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $aRow['id'] . '">';
        foreach ($task_statuses as $taskChangeStatus) {
            if ($aRow['status'] != $taskChangeStatus['id']) {
                $outputStatus .= '<li>
                    <a href="#" onclick="task_mark_as(' . $taskChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                        ' . e(_l('task_mark_as', $taskChangeStatus['name'])) . '
                    </a>
                </li>';
            }
        }
        $outputStatus .= '</ul>';
        $outputStatus .= '</div>';
    }
    $outputStatus .= '</span>';
    $row[] = $outputStatus;

    // Assignees
    $assignees_ids = !empty($aRow['assignees_ids']) ? $aRow['assignees_ids'] : '';
    $assignees_names = !empty($aRow['assignees']) ? $aRow['assignees'] : '';
    $row[] = format_members_by_ids_and_names($assignees_ids, $assignees_names);

    // Tags
    $row[] = render_tags($aRow['tags']);

    // Priority
    $priority_color = task_priority_color($aRow['priority']);
    $priority_name = task_priority($aRow['priority']);
    $outputPriority = '<span style="color:' . e($priority_color) . ';" class="inline-block">' . e($priority_name);

    if (staff_can('edit', 'tasks') && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $outputPriority .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputPriority .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskPriority-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputPriority .= '<span data-toggle="tooltip" title="' . _l('task_single_priority') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
        $outputPriority .= '</a>';

        $outputPriority .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskPriority-' . $aRow['id'] . '">';
        foreach ($tasksPriorities as $priority) {
            if ($aRow['priority'] != $priority['id']) {
                $outputPriority .= '<li>
                    <a href="#" onclick="task_change_priority(' . $priority['id'] . ',' . $aRow['id'] . '); return false;">
                        ' . e($priority['name']) . '
                    </a>
                </li>';
            }
        }
        $outputPriority .= '</ul>';
        $outputPriority .= '</div>';
    }
    $outputPriority .= '</span>';
    $row[] = $outputPriority;

    // Custom fields
    foreach ($customFieldsColumns as $customFieldColumn) {
        $value = isset($aRow[$customFieldColumn]) ? $aRow[$customFieldColumn] : '';
        if (strpos($customFieldColumn, 'date_picker_') !== false) {
            $row[] = !empty($value) ? _d($value) : '';
        } else {
            $row[] = $value;
        }
    }

    $row['DT_RowClass'] = 'has-row-options';

    if ((!empty($aRow['duedate']) && $aRow['duedate'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' danger';
    }

    $row = hooks()->apply_filters('tasks_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}