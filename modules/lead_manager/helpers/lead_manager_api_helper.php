<?php
if (!defined('MODULE_LEAD_MANAGER')) {
  define('MODULE_LEAD_MANAGER', basename(__DIR__));
}
if (!defined('LEAD_MANAGER_UPLOADS_FOLDER')) {
  define('LEAD_MANAGER_UPLOADS_FOLDER', FCPATH . 'uploads/lead_manager' . '/');
}

if (!defined('LEAD_MANAGER_MAILBOX_FOLDER')) {
  define('LEAD_MANAGER_MAILBOX_FOLDER', FCPATH . 'uploads/lead_manager/mailbox' . '/');
}
if (!defined('LEAD_MANAGER_WHATSAPP_FOLDER')) {
  define('LEAD_MANAGER_WHATSAPP_FOLDER', FCPATH . 'uploads/lead_manager/whatsapp' . '/');
}

function getStaff($id = null)
{
  $CI = get_instance();
  if (!is_admin()) {
    access_denied('Access Denied');
  }
  $CI->db->select('*');
  $CI->db->where('active', 1);
  if (!empty($id)) {
    $CI->db->where('staffid', $id);
  }

  return $CI->db->get(db_prefix() . 'staff')->result_array();
}

function getStaffId()
{
  $CI = get_instance();
  $CI->load->library('Authorization_Token');
  $is_valid_token = $CI->authorization_token->validateToken();
  if ($is_valid_token['status']) {
    return $is_valid_token['data']->staff_id;
  } else {
    return false;
  }
}

function get_total_unread_sms($lead_id, $where)
{
  if (is_numeric($lead_id)) {
    $CI = &get_instance();
    $query = '';
    if ($where['is_client'] == 'no') {
      $query = $CI->db->query("SELECT count(*) as unread FROM " . db_prefix() . "lead_manager_conversation WHERE (to_id=" . $where['to_id'] . " AND from_id=" . $lead_id . ") AND is_client = 0 AND is_read='no' AND sms_direction='incoming'");
    } else {
      $query = $CI->db->query("SELECT count(*) as unread FROM " . db_prefix() . "lead_manager_conversation WHERE (to_id=" . $where['to_id'] . " AND from_id=" . $lead_id . ") AND is_client = 1 AND is_read='no' AND sms_direction='incoming'");
    }
    return $query->row()->unread;
  }
}

function format_task_members_by_ids_and_names($ids, $names)
{
  $output = [];
  $assignees   = explode(',', $names);
  $assigneeIds = explode(',', $ids);
  foreach ($assignees as $key => $assigned) {
    $assignee_id = $assigneeIds[$key];
    $assignee_id = trim($assignee_id);
    if ($assigned != '') {
      $output[] = [
        'name' => $assigned,
        'profile_url' => admin_url('profile/' . $assignee_id),
        'profile_image' => staff_profile_image_url($assignee_id),

      ];
    }
  }
  return $output;
}
function lm_get_custom_fields($field_to, $where = [], $exclude_only_admin = false)
{
  $is_admin = is_admin();
  $CI       = &get_instance();
  $CI->db->where('fieldto', $field_to);
  if ((is_array($where) && count($where) > 0) || (!is_array($where) && $where != '')) {
    $CI->db->where($where);
  }
  if (!$is_admin || $exclude_only_admin == true) {
    $CI->db->where('only_admin', 0);
  }
  $CI->db->where('active', 1);
  $CI->db->order_by('field_order', 'asc');

  $results = $CI->db->get(db_prefix() . 'customfields')->result_array();
  //print_r($results); die;
  foreach ($results as $key => $result) {
    $results[$key]['name'] = _maybe_translate_custom_field_name($result['name'], $result['slug']);
    if ($results[$key]['options'] == 'Enable,Disable') {
      $results[$key]['options'] = explode(",", $result['options']);
    }
  }

  return $results;
}
