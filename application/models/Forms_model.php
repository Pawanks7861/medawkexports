<?php

use app\services\MergeForms;

defined('BASEPATH') or exit('No direct script access allowed');

class Forms_model extends App_Model
{
    private $piping = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function form_count($status = null)
    {
        $where = 'AND merged_form_id is NULL';
        if (!is_admin()) {
            $this->load->model('departments_model');
            $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'AND department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }
        }
        $_where = '';
        if (!is_null($status)) {
            if ($where == '') {
                $_where = 'status=' . $status;
            } else {
                $_where = 'status=' . $status . ' ' . $where;
            }
        }

        return total_rows(db_prefix() . 'forms', $_where);
    }

    public function insert_piped_form($data)
    {
        $data = hooks()->apply_filters('piped_form_data', $data);

        $this->piping = true;
        $attachments  = $data['attachments'];
        $subject      = $data['subject'];
        // Prevent insert form to database if mail delivery error happen
        // This will stop createing a thousand forms
        $system_blocked_subjects = [
            'Mail delivery failed',
            'failure notice',
            'Returned mail: see transcript for details',
            'Undelivered Mail Returned to Sender',
        ];

        $subject_blocked = false;

        foreach ($system_blocked_subjects as $sb) {
            if (strpos('x' . $subject, $sb) !== false) {
                $subject_blocked = true;

                break;
            }
        }

        if ($subject_blocked == true) {
            return;
        }

        $message = $data['body'];
        $name    = $data['fromname'];

        $email   = $data['email'];
        $to      = $data['to'];
        $cc      = $data['cc'] ?? [];
        $subject = $subject;
        $message = $message;

        $this->load->model('spam_filters_model');
        $mailstatus = $this->spam_filters_model->check($email, $subject, $message, 'forms');

        // No spam found
        if (!$mailstatus) {
            $pos = strpos($subject, '[Form ID: ');
            if ($pos === false) {
            } else {
                $tid = substr($subject, $pos + 12);
                $tid = substr($tid, 0, strpos($tid, ']'));
                $this->db->where('formid', $tid);
                $data = $this->db->get(db_prefix() . 'forms')->row();
                $tid  = $data->formid;
            }
            $to            = trim($to);
            $toemails      = explode(',', $to);
            $department_id = false;
            $userid        = false;
            foreach ($toemails as $toemail) {
                if (!$department_id) {
                    $this->db->where('email', trim($toemail));
                    $data = $this->db->get(db_prefix() . 'departments')->row();
                    if ($data) {
                        $department_id = $data->departmentid;
                        $to            = $data->email;
                    }
                }
            }
            if (!$department_id) {
                $mailstatus = 'Department Not Found';
            } else {
                if ($to == $email) {
                    $mailstatus = 'Blocked Potential Email Loop';
                } else {
                    $message = trim($message);
                    $this->db->where('active', 1);
                    $this->db->where('email', $email);
                    $result = $this->db->get(db_prefix() . 'staff')->row();
                    if ($result) {
                        if ($tid) {
                            $data            = [];
                            $data['message'] = $message;
                            $data['status']  = get_option('default_form_reply_status');

                            if (!$data['status']) {
                                $data['status'] = 3; // Answered
                            }

                            if ($userid == false) {
                                $data['name']  = $name;
                                $data['email'] = $email;
                            }

                            if (count($cc) > 0) {
                                $data['cc'] = $cc;
                            }

                            $reply_id = $this->add_reply($data, $tid, $result->staffid, $attachments);
                            if ($reply_id) {
                                $mailstatus = 'Form Reply Imported Successfully';
                            }
                        } else {
                            $mailstatus = 'Form ID Not Found';
                        }
                    } else {
                        $this->db->where('email', $email);
                        $result = $this->db->get(db_prefix() . 'contacts')->row();
                        if ($result) {
                            $userid    = $result->userid;
                            $contactid = $result->id;
                        }
                        if ($userid == false && get_option('email_piping_only_registered') == '1') {
                            $mailstatus = 'Unregistered Email Address';
                        } else {
                            $filterdate = date('Y-m-d H:i:s', strtotime('-15 minutes'));
                            $query      = 'SELECT count(*) as total FROM ' . db_prefix() . 'forms WHERE date > "' . $filterdate . '" AND (email="' . $this->db->escape($email) . '"';
                            if ($userid) {
                                $query .= ' OR userid=' . (int) $userid;
                            }
                            $query .= ')';
                            $result = $this->db->query($query)->row();
                            if (10 < $result->total) {
                                $mailstatus = 'Exceeded Limit of 10 Forms within 15 Minutes';
                            } else {
                                if (isset($tid)) {
                                    $data            = [];
                                    $data['message'] = $message;
                                    $data['status']  = 1;
                                    if ($userid == false) {
                                        $data['name']  = $name;
                                        $data['email'] = $email;
                                    } else {
                                        $data['userid']    = $userid;
                                        $data['contactid'] = $contactid;

                                        $this->db->where('formid', $tid);
                                        $this->db->group_start();
                                        $this->db->where('userid', $userid);

                                        // Allow CC'ed user to reply to the form
                                        $this->db->or_like('cc', $email);
                                        $this->db->group_end();
                                        $t = $this->db->get(db_prefix() . 'forms')->row();
                                        if (!$t) {
                                            $abuse = true;
                                        }
                                    }
                                    if (!isset($abuse)) {
                                        if (count($cc) > 0) {
                                            $data['cc'] = $cc;
                                        }
                                        $reply_id = $this->add_reply($data, $tid, null, $attachments);
                                        if ($reply_id) {
                                            // Dont change this line
                                            $mailstatus = 'Form Reply Imported Successfully';
                                        }
                                    } else {
                                        $mailstatus = 'Form ID Not Found For User';
                                    }
                                } else {
                                    if (get_option('email_piping_only_registered') == 1 && !$userid) {
                                        $mailstatus = 'Blocked Form Opening from Unregistered User';
                                    } else {
                                        if (get_option('email_piping_only_replies') == '1') {
                                            $mailstatus = 'Only Replies Allowed by Email';
                                        } else {
                                            $data               = [];
                                            $data['department'] = $department_id;
                                            $data['subject']    = $subject;
                                            $data['message']    = $message;
                                            $data['contactid']  = $contactid;
                                            $data['priority']   = get_option('email_piping_default_priority');
                                            if ($userid == false) {
                                                $data['name']  = $name;
                                                $data['email'] = $email;
                                            } else {
                                                $data['userid'] = $userid;
                                            }
                                            $tid = $this->add($data, null, $attachments);
                                            if ($tid && count($cc) > 0) {
                                                // A customer opens a form by mail to "support@example".com, with one or many 'Cc'
                                                // Remember those 'Cc'.
                                                $this->db->where('formid', $tid);
                                                $this->db->update('forms', ['cc' => implode(',', $cc)]);
                                            }
                                            // Dont change this line
                                            $mailstatus = 'Form Imported Successfully';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($mailstatus == '') {
            $mailstatus = 'Form Import Failed';
        }
        $this->db->insert(db_prefix() . 'forms_pipe_log', [
            'date'     => date('Y-m-d H:i:s'),
            'email_to' => $to,
            'name'     => $name ?: 'Unknown',
            'email'    => $email ?: 'N/A',
            'subject'  => $subject ?: 'N/A',
            'message'  => $message,
            'status'   => $mailstatus,
        ]);

        return $mailstatus;
    }

    private function process_pipe_attachments($attachments, $form_id, $reply_id = '')
    {
        if (!empty($attachments)) {
            $form_attachments = [];
            $allowed_extensions = array_map(function ($ext) {
                return strtolower(trim($ext));
            }, explode(',', get_option('form_attachments_file_extensions')));

            $path = FCPATH . 'uploads/form_attachments' . '/' . $form_id . '/';

            foreach ($attachments as $attachment) {
                $filename      = $attachment['filename'];
                $filenameparts = explode('.', $filename);
                $extension     = end($filenameparts);
                $extension     = strtolower($extension);
                if (in_array('.' . $extension, $allowed_extensions)) {
                    $filename = implode(array_slice($filenameparts, 0, 0 - 1));
                    $filename = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $filename));

                    if (!$filename) {
                        $filename = 'attachment';
                    }

                    if (!file_exists($path)) {
                        mkdir($path, 0755);
                        $fp = fopen($path . 'index.html', 'w');
                        fclose($fp);
                    }

                    $filename = unique_filename($path, $filename . '.' . $extension);
                    file_put_contents($path . $filename, $attachment['data']);

                    array_push($form_attachments, [
                        'file_name' => $filename,
                        'filetype'  => get_mime_by_extension($filename),
                    ]);
                }
            }

            $this->insert_form_attachments_to_database($form_attachments, $form_id, $reply_id);
        }
    }

    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'forms.userid,' . db_prefix() . 'forms.name as from_name,' . db_prefix() . 'forms.email as form_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'forms_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'forms.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'forms.formid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,lastreply,message,' . db_prefix() . 'forms.status,subject,department,priority,' . db_prefix() . 'contacts.email,adminread,clientread,date');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->join(db_prefix() . 'forms_priorities', db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'forms.formid', $id);

            return $this->db->get(db_prefix() . 'forms')->row();
        }
        $this->db->order_by('lastreply', 'asc');

        if (is_client_logged_in()) {
            $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);
        }

        return $this->db->get(db_prefix() . 'forms')->result_array();
    }

    /**
     * Get form by id and all data
     * @param  mixed  $id     form id
     * @param  mixed $userid Optional - Forms from USER ID
     * @return object
     */
    public function get_form_by_id($id, $userid = '')
    {
        $this->db->select('*, ' . db_prefix() . 'forms.userid, ' . db_prefix() . 'forms.name as from_name, ' . db_prefix() . 'forms.email as form_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'forms_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'forms.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'forms_status.name as status_name, ' . db_prefix() . 'forms.formid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname, ' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname, lastreply, message, ' . db_prefix() . 'forms.status, subject, department, priority, ' . db_prefix() . 'contacts.email, adminread, clientread, date');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid', 'left');
        $this->db->join(db_prefix() . 'forms_priorities', db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority', 'left');

        if (strlen($id) === 32) {
            $this->db->where(db_prefix() . 'forms.formkey', $id);
        } else {
            $this->db->where(db_prefix() . 'forms.formid', $id);
        }

        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'forms.userid', $userid);
        }

        $form = $this->db->get()->row();
        if ($form) {
            $form->submitter = $form->contactid != 0 ?
                ($form->user_firstname . ' ' . $form->user_lastname) :
                $form->from_name;

            if (!($form->admin == null || $form->admin == 0)) {
                $form->opened_by = $form->staff_firstname . ' ' . $form->staff_lastname;
            }

            $form->attachments = $this->get_form_attachments($form->formid);
        }


        return $form;
    }

    /**
     * Insert form attachments to database
     * @param  array  $attachments array of attachment
     * @param  mixed  $formid
     * @param  boolean $replyid If is from reply
     */
    public function insert_form_attachments_to_database($attachments, $formid, $replyid = false)
    {
        foreach ($attachments as $attachment) {
            $attachment['formid']  = $formid;
            $attachment['dateadded'] = date('Y-m-d H:i:s');
            if ($replyid !== false && is_int($replyid)) {
                $attachment['replyid'] = $replyid;
            }
            $this->db->insert(db_prefix() . 'form_attachments', $attachment);
        }
    }

    /**
     * Get form attachments from database
     * @param  mixed $id      form id
     * @param  mixed $replyid Optional - reply id if is from from reply
     * @return array
     */
    public function get_form_attachments($id, $replyid = '')
    {
        $this->db->where('formid', $id);
        $this->db->where('replyid', is_numeric($replyid) ? $replyid : null);

        return $this->db->get('form_attachments')->result_array();
    }

    /**
     * Add new reply to form
     * @param mixed $data  reply $_POST data
     * @param mixed $id    form id
     * @param boolean $admin staff id if is staff making reply
     */
    public function add_reply($data, $id, $admin = null, $pipe_attachments = false)
    {
        if (isset($data['assign_to_current_user'])) {
            $assigned = get_staff_user_id();
            unset($data['assign_to_current_user']);
        }

        $unsetters = [
            'note_description',
            'department',
            'priority',
            'subject',
            'assigned',
            'project_id',
            'service',
            'status_top',
            'attachments',
            'DataTables_Table_0_length',
            'DataTables_Table_1_length',
            'custom_fields',
        ];

        foreach ($unsetters as $unset) {
            if (isset($data[$unset])) {
                unset($data[$unset]);
            }
        }

        if ($admin !== null) {
            $data['admin'] = $admin;
            $status        = $data['status'];
        } else {
            $status = 1;
        }

        if (isset($data['status'])) {
            unset($data['status']);
        }

        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        // if form is merged
        $form           = $this->get($id);
        $data['formid'] = ($form && $form->merged_form_id != null) ? $form->merged_form_id : $id;
        $data['date']     = date('Y-m-d H:i:s');
        $data['message']  = trim($data['message']);

        if ($this->piping == true) {
            // $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        }

        $is_html_stripped = $this->piping === true;

        // admin can have html
        if (
            !$is_html_stripped &&
            $admin == null &&
            hooks()->apply_filters('form_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        // $data['message'] = remove_emojis($data['message']);
        $data            = hooks()->apply_filters('before_form_reply_add', $data, $id, $admin);

        $this->db->insert(db_prefix() . 'form_replies', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            /**
             * When a form is in status "In progress" and the customer reply to the form
             * it changes the status to "Open" which is not normal.
             *
             * The form should keep the status "In progress"
             */
            $this->db->select('status');
            $this->db->where('formid', $id);
            $old_form_status = $this->db->get(db_prefix() . 'forms')->row()->status;

            $newStatus = hooks()->apply_filters(
                'form_reply_status',
                ($old_form_status == 2 && $admin == null ? $old_form_status : $status),
                ['form_id' => $id, 'reply_id' => $insert_id, 'admin' => $admin, 'old_status' => $old_form_status]
            );

            if (isset($assigned)) {
                $this->db->where('formid', $id);
                $this->db->update(db_prefix() . 'forms', [
                    'assigned' => $assigned,
                ]);
            }

            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $id, $insert_id);
            } else {
                $attachments = handle_form_attachments($id);
                if ($attachments) {
                    $this->forms_model->insert_form_attachments_to_database($attachments, $id, $insert_id);
                }
            }

            $_attachments = $this->get_form_attachments($id, $insert_id);

            log_activity('New Form Reply [ReplyID: ' . $insert_id . ']');

            $this->db->where('formid', $id);
            $this->db->update(db_prefix() . 'forms', [
                'lastreply'  => date('Y-m-d H:i:s'),
                'status'     => $newStatus,
                'adminread'  => 0,
                'clientread' => 0,
            ]);

            if ($old_form_status != $newStatus) {
                hooks()->do_action('after_form_status_changed', [
                    'id'     => $id,
                    'status' => $newStatus,
                ]);
            }

            $form    = $this->get_form_by_id($id);
            $userid    = $form->userid;
            $isContact = false;
            if ($form->userid != 0 && $form->contactid != 0) {
                $email     = $this->clients_model->get_contact($form->contactid)->email;
                $isContact = true;
            } else {
                $email = $form->form_email;
            }
            if ($admin == null) {
                $this->load->model('departments_model');
                $this->load->model('staff_model');

                $notifiedUsers = [];
                $staff         = $this->getStaffMembersForFormNotification($form->department, $form->assigned);
                foreach ($staff as $staff_key => $member) {
                    // send_mail_template('form_new_reply_to_staff', $form, $member, $_attachments);
                    if (get_option('receive_notification_on_new_form_replies') == 1) {
                        $notified = add_notification([
                            'description'     => 'not_new_form_reply',
                            'touserid'        => $member['staffid'],
                            'fromcompany'     => 1,
                            'fromuserid'      => 0,
                            'link'            => 'forms/form/' . $id,
                            'additional_data' => serialize([
                                $form->subject,
                            ]),
                        ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                $this->update_staff_replying($id);

                $total_staff_replies = total_rows(db_prefix() . 'form_replies', ['admin is NOT NULL', 'formid' => $form->formid]);
                if (
                    $form->assigned == 0 &&
                    get_option('automatically_assign_form_to_first_staff_responding') == '1' &&
                    $total_staff_replies == 1
                ) {
                    $this->db->where('formid', $id);
                    $this->db->update(db_prefix() . 'forms', ['assigned' => $admin]);
                }

                $sendEmail = true;
                if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $form->contactid]) == 0) {
                    $sendEmail = false;
                }
                if ($sendEmail) {
                    // send_mail_template('form_new_reply_to_customer', $form, $email, $_attachments, $cc);
                }
            }

            if ($cc) {
                // imported reply
                if (is_array($cc)) {
                    if ($form->cc) {
                        $currentCC = explode(',', $form->cc);
                        $cc        = array_unique([$cc, $currentCC]);
                    }
                    $cc = implode(',', $cc);
                }
                $this->db->where('formid', $id);
                $this->db->update('forms', ['cc' => $cc]);
            }
            hooks()->do_action('after_form_reply_added', [
                'data'    => $data,
                'id'      => $id,
                'admin'   => $admin,
                'replyid' => $insert_id,
            ]);

            return $insert_id;
        }

        return false;
    }

    /**
     *  Delete form reply
     * @param   mixed $form_id    form id
     * @param   mixed $reply_id     reply id
     * @return  boolean
     */
    public function delete_form_reply($form_id, $reply_id)
    {
        hooks()->do_action('before_delete_form_reply', ['form_id' => $form_id, 'reply_id' => $reply_id]);

        $this->db->where('id', $reply_id);
        $this->db->delete(db_prefix() . 'form_replies');

        if ($this->db->affected_rows() > 0) {
            // Get the reply attachments by passing the reply_id to get_form_attachments method
            $attachments = $this->get_form_attachments($form_id, $reply_id);
            if (count($attachments) > 0) {
                foreach ($attachments as $attachment) {
                    $this->delete_form_attachment($attachment['id']);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Remove form attachment by id
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_form_attachment($id)
    {
        $deleted = false;
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'form_attachments')->row();
        if ($attachment) {
            if (unlink(get_upload_path_by_type('form') . $attachment->formid . '/' . $attachment->file_name)) {
                $this->db->where('id', $attachment->id);
                $this->db->delete(db_prefix() . 'form_attachments');
                $deleted = true;
            }
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(get_upload_path_by_type('form') . $attachment->formid);
            if (count($other_attachments) == 0) {
                delete_dir(get_upload_path_by_type('form') . $attachment->formid);
            }
        }

        return $deleted;
    }

    /**
     * Get form attachment by id
     * @param  mixed $id attachment id
     * @return mixed
     */
    public function get_form_attachment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'form_attachments')->row();
    }

    /**
     * This functions is used when staff open client form
     * @param  mixed $userid client id
     * @param  mixed $id     formid
     * @return array
     */
    public function get_user_other_forms($userid, $id)
    {
        $this->db->select(db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'services.name as service_name,' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'clients.lastname as staff_lastname,formid,subject,firstname,lastname,lastreply');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->where(db_prefix() . 'forms.userid', $userid);
        $this->db->where(db_prefix() . 'forms.formid !=', $id);
        $forms = $this->db->get()->result_array();
        $i       = 0;
        foreach ($forms as $form) {
            $forms[$i]['submitter'] = $form['firstname'] . ' ' . $form['lastname'];
            unset($form['firstname']);
            unset($form['lastname']);
            $i++;
        }

        return $forms;
    }

    /**
     * Get all form replies
     * @param  mixed  $id     formid
     * @param  mixed $userid specific client id
     * @return array
     */
    public function get_form_replies($id)
    {
        $form_replies_order = get_option('form_replies_order');
        // backward compatibility for the action hook
        $form_replies_order = hooks()->apply_filters('form_replies_order', $form_replies_order);

        $this->db->select(db_prefix() . 'form_replies.id,' . db_prefix() . 'form_replies.name as from_name,' . db_prefix() . 'form_replies.email as reply_email, ' . db_prefix() . 'form_replies.admin, ' . db_prefix() . 'form_replies.userid,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,' . db_prefix() . 'contacts.firstname as user_firstname,' . db_prefix() . 'contacts.lastname as user_lastname,message,date,contactid');
        $this->db->from(db_prefix() . 'form_replies');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'form_replies.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'form_replies.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'form_replies.contactid', 'left');
        $this->db->where('formid', $id);
        $this->db->order_by('date', $form_replies_order);
        $replies = $this->db->get()->result_array();
        $i       = 0;
        foreach ($replies as $reply) {
            if ($reply['admin'] !== null || $reply['admin'] != 0) {
                // staff reply
                $replies[$i]['submitter'] = $reply['staff_firstname'] . ' ' . $reply['staff_lastname'];
            } else {
                if ($reply['contactid'] != 0) {
                    $replies[$i]['submitter'] = $reply['user_firstname'] . ' ' . $reply['user_lastname'];
                } else {
                    $replies[$i]['submitter'] = $reply['from_name'];
                }
            }
            unset($replies[$i]['staff_firstname']);
            unset($replies[$i]['staff_lastname']);
            unset($replies[$i]['user_firstname']);
            unset($replies[$i]['user_lastname']);
            $replies[$i]['attachments'] = $this->get_form_attachments($id, $reply['id']);
            $i++;
        }

        return $replies;
    }

    /**
     * Add new form to database
     * @param mixed $data  form $_POST data
     * @param mixed $admin If admin adding the form passed staff id
     */
    public function add($data, $admin = null, $pipe_attachments = false)
    {
        if ($admin !== null) {
            $data['admin'] = $admin;
            unset($data['form_client_search']);
        }

        if (isset($data['assigned']) && $data['assigned'] == '') {
            $data['assigned'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if ($admin == null) {
            if (isset($data['email'])) {
                $data['userid']    = 0;
                $data['contactid'] = 0;
            } else {
                // Opened from customer portal otherwise is passed from pipe or admin area
                if (!isset($data['userid']) && !isset($data['contactid'])) {
                    $data['userid']    = get_client_user_id();
                    $data['contactid'] = get_contact_user_id();
                }
            }
            $data['status'] = 1;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        // CC is only from admin area
        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        $data['date']      = date('Y-m-d H:i:s');
        $data['formkey'] = app_generate_hash();
        $data['status']    = 1;
        $data['message']   = trim($data['message']);
        $data['subject']   = trim($data['subject']);
        // if ($this->piping == true) {
        //     $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        // }

        $is_html_stripped = $this->piping === true;

        // Admin can have html
        if (
            !$is_html_stripped &&
            $admin == null &&
            hooks()->apply_filters('form_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['subject'] = _strip_tags($data['subject']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        if ($data['duedate'] != '') {
            $data['duedate'] = to_sql_date($data['duedate']);
        }
        if (isset($data['form_type'])) {
            if ($data['form_type'] == "dpr") {
                $dpr_form = array();
                $dpr_form['client_id'] = $data['client_id'];
                $dpr_form['pmc'] = $data['pmc'];
                $dpr_form['weather'] = $data['weather'];
                $dpr_form['consultant'] = $data['consultant'];
                $dpr_form['contractor'] = $data['contractor'];
                $dpr_form['work_stop'] = $data['work_stop'];
                unset($data['client_id']);
                unset($data['pmc']);
                unset($data['weather']);
                unset($data['consultant']);
                unset($data['contractor']);
                unset($data['work_stop']);
                unset($data['location']);
                unset($data['agency']);
                unset($data['type']);
                unset($data['sub_type']);
                unset($data['work_execute']);
                unset($data['material_consumption']);
                unset($data['male']);
                unset($data['female']);
                unset($data['total']);
                unset($data['machinery']);
                unset($data['total_machinery']);
                unset($data['staff']);
                unset($data['attendance']);
                unset($data['over_time']);
                unset($data['kharchi']);
                unset($data['challan']);
                unset($data['grade']);
                unset($data['structure']);
                unset($data['quantity']);
                unset($data['supplier']);
                unset($data['material_description']);
                unset($data['total']);
                $new_order = [];
                if (isset($data['newitems'])) {
                    $new_order = $data['newitems'];
                    unset($data['newitems']);
                }
                $new_order_dept = [];
                if (isset($data['newitemsdept'])) {
                    $new_order_dept = $data['newitemsdept'];
                    unset($data['newitemsdept']);
                }
                $new_order_rmc = [];
                if (isset($data['newitemsrmc'])) {
                    $new_order_rmc = $data['newitemsrmc'];
                    unset($data['newitemsrmc']);
                }
                $new_order_material = [];
                if (isset($data['newitemsmaterial'])) {
                    $new_order_material = $data['newitemsmaterial'];
                    unset($data['newitemsmaterial']);
                }
                $new_order_cement = [];
                if (isset($data['inward_inventory']) || isset($data['today_usage']) || isset($data['remaining_cement']) || isset($data['notes'])) {
                    $new_order_cement[] = [
                        'inward_inventory' => $data['inward_inventory'],
                        'today_usage' => $data['today_usage'],
                        'remaining_cement' => $data['remaining_cement'],
                        'notes' => $data['notes'],
                    ];
                    unset($data['inward_inventory']);
                    unset($data['today_usage']);
                    unset($data['remaining_cement']);
                    unset($data['notes']);
                    unset($data['rack_cement_id']);
                }
                $new_order_block = [];
                if (isset($data['inward_inventory_bmj']) || isset($data['today_usage_bmj']) || isset($data['remaining_cement_bmj']) || isset($data['notes_bmj'])) {
                    $new_order_block[] = [
                        'inward_inventory_bmj' => $data['inward_inventory_bmj'],
                        'today_usage_bmj' => $data['today_usage_bmj'],
                        'remaining_cement_bmj' => $data['remaining_cement_bmj'],
                        'notes_bmj' => $data['notes_bmj'],
                    ];
                    unset($data['inward_inventory_bmj']);
                    unset($data['today_usage_bmj']);
                    unset($data['remaining_cement_bmj']);
                    unset($data['notes_bmj']);
                    unset($data['block_mortar_id']);
                }
                $new_order_tile = [];
                if (isset($data['inward_inventory_ta']) || isset($data['today_usage_ta']) || isset($data['remaining_cement_ta']) || isset($data['notes_ta'])) {
                    $new_order_tile[] = [
                        'inward_inventory_ta' => $data['inward_inventory_ta'],
                        'today_usage_ta' => $data['today_usage_ta'],
                        'remaining_cement_ta' => $data['remaining_cement_ta'],
                        'notes_ta' => $data['notes_ta'],
                    ];
                    unset($data['inward_inventory_ta']);
                    unset($data['today_usage_ta']);
                    unset($data['remaining_cement_ta']);
                    unset($data['notes_ta']);
                    unset($data['tile_adhesive_id']);
                }
                $new_order_coupler = [];

                if (
                    isset($data['inward_inventory_ca']) &&
                    isset($data['today_usage_ca']) &&
                    isset($data['remaining_cement_ca']) &&
                    isset($data['notes_ca']) &&
                    isset($data['coupler_type'])
                ) {
                    $count = count($data['inward_inventory_ca']);

                    for ($i = 0; $i < $count; $i++) {

                        // Skip completely empty rows (optional but recommended)
                        if (
                            $data['inward_inventory_ca'][$i] === '' &&
                            $data['today_usage_ca'][$i] === '' &&
                            $data['remaining_cement_ca'][$i] === '' &&
                            $data['notes_ca'][$i] === ''
                        ) {
                            continue;
                        }

                        $new_order_coupler[] = [
                            'inward_inventory_ca' => $data['inward_inventory_ca'][$i],
                            'today_usage_ca' => $data['today_usage_ca'][$i],
                            'remaining_cement_ca' => $data['remaining_cement_ca'][$i],
                            'notes_ca' => $data['notes_ca'][$i],
                            'coupler_type' => $data['coupler_type'][$i],
                        ];
                    }

                    // unset after processing
                    unset($data['inward_inventory_ca']);
                    unset($data['today_usage_ca']);
                    unset($data['remaining_cement_ca']);
                    unset($data['notes_ca']);
                    unset($data['coupler_type']);
                    unset($data['coupler_id']);
                }
                $new_order_wire = [];

                if (
                    isset($data['inward_inventory_wi']) &&
                    isset($data['today_usage_wi']) &&
                    isset($data['remaining_cement_wi']) &&
                    isset($data['notes_wi']) &&
                    isset($data['wire_type'])
                ) {
                    $count = count($data['inward_inventory_wi']);

                    for ($i = 0; $i < $count; $i++) {

                        // Skip empty rows
                        if (
                            $data['inward_inventory_wi'][$i] === '' &&
                            $data['today_usage_wi'][$i] === '' &&
                            $data['remaining_cement_wi'][$i] === '' &&
                            $data['notes_wi'][$i] === ''
                        ) {
                            continue;
                        }

                        $new_order_wire[] = [
                            'inward_inventory_wi' => $data['inward_inventory_wi'][$i],
                            'today_usage_wi' => $data['today_usage_wi'][$i],
                            'remaining_cement_wi' => $data['remaining_cement_wi'][$i],
                            'notes_wi' => $data['notes_wi'][$i],
                            'wire_type' => $data['wire_type'][$i],
                        ];
                    }

                    // unset after processing
                    unset($data['inward_inventory_wi']);
                    unset($data['today_usage_wi']);
                    unset($data['remaining_cement_wi']);
                    unset($data['notes_wi']);
                    unset($data['wire_type']);
                    unset($data['wires_id']);
                }
                $new_order_council = [];
                if (isset($data['inward_inventory_cb']) || isset($data['today_usage_cb']) || isset($data['remaining_cement_cb']) || isset($data['notes_cb'])) {
                    $new_order_council[] = [
                        'inward_inventory_cb' => $data['inward_inventory_cb'],
                        'today_usage_cb' => $data['today_usage_cb'],
                        'remaining_cement_cb' => $data['remaining_cement_cb'],
                        'notes_cb' => $data['notes_cb'],
                    ];
                    unset($data['inward_inventory_cb']);
                    unset($data['today_usage_cb']);
                    unset($data['remaining_cement_cb']);
                    unset($data['notes_cb']);
                    unset($data['cb_id']);
                }
            }
        }


        $data = hooks()->apply_filters('before_form_created', $data, $admin);

        $this->db->insert(db_prefix() . 'forms', $data);
        $formid = $this->db->insert_id();
        add_drp_activity_log($formid, true);
        if ($formid) {
            if ($data['form_type'] == "dpr") {
                if (isset($dpr_form)) {
                    if (!empty($dpr_form)) {
                        $dpr_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $dpr_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['location'] = $value['location'];
                            $dt_data['agency'] = $value['agency'];
                            $dt_data['type'] = $value['type'];
                            $dt_data['sub_type'] = $value['sub_type'];
                            $dt_data['work_execute'] = $value['work_execute'];
                            $dt_data['material_consumption'] = $value['material_consumption'];
                            $dt_data['male'] = $value['male'];
                            $dt_data['female'] = $value['female'];
                            $dt_data['total'] = $value['total'];
                            $dt_data['machinery'] = $value['machinery'];
                            $dt_data['total_machinery'] = $value['total_machinery'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                        }
                    }
                }
                if (isset($new_order_dept)) {
                    if (!empty($new_order_dept)) {
                        foreach ($new_order_dept as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['staff'] = $value['staff'];
                            $dt_data['attendance'] = $value['attendance'];
                            $dt_data['over_time'] = $value['over_time'];
                            $dt_data['kharchi'] = $value['kharchi'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_dept_form_detail', $dt_data);
                        }
                    }
                }
                if (isset($new_order_rmc)) {
                    if (!empty($new_order_rmc)) {
                        foreach ($new_order_rmc as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['challan'] = $value['challan'];
                            $dt_data['grade'] = $value['grade'];
                            $dt_data['structure'] = $value['structure'];
                            $dt_data['quantity'] = $value['quantity'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_rmc_form_detail', $dt_data);
                        }
                    }
                }
                if (isset($new_order_material)) {
                    if (!empty($new_order_material)) {
                        foreach ($new_order_material as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['challan'] = $value['challan'];
                            $dt_data['supplier'] = $value['supplier'];
                            $dt_data['material_description'] = $value['material_description'];
                            $dt_data['total'] = $value['total'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_material_form_detail', $dt_data);
                        }
                    }
                }

                if (isset($new_order_cement)) {
                    if (!empty($new_order_cement)) {
                        foreach ($new_order_cement as $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['inward_inventory'] = $value['inward_inventory'];
                            $dt_data['today_usage'] = $value['today_usage'];
                            $dt_data['remaining_cement'] = $value['remaining_cement'];
                            $dt_data['notes'] = $value['notes'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_cement_form_detail', $dt_data);
                        }
                    }
                }
                if (isset($new_order_block)) {
                    if (!empty($new_order_block)) {
                        foreach ($new_order_block as $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['inward_inventory_bmj'] = $value['inward_inventory_bmj'];
                            $dt_data['today_usage_bmj'] = $value['today_usage_bmj'];
                            $dt_data['remaining_cement_bmj'] = $value['remaining_cement_bmj'];
                            $dt_data['notes_bmj'] = $value['notes_bmj'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_block_form_detail', $dt_data);
                        }
                    }
                }
                if (isset($new_order_tile)) {
                    if (!empty($new_order_tile)) {
                        foreach ($new_order_tile as $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['inward_inventory_ta'] = $value['inward_inventory_ta'];
                            $dt_data['today_usage_ta'] = $value['today_usage_ta'];
                            $dt_data['remaining_cement_ta'] = $value['remaining_cement_ta'];
                            $dt_data['notes_ta'] = $value['notes_ta'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_tile_form_detail', $dt_data);
                        }
                    }
                }
                if (!empty($new_order_coupler)) {
                    foreach ($new_order_coupler as $value) {

                        $dt_data = [];
                        $dt_data['form_id'] = $formid;
                        $dt_data['inward_inventory_ca'] = $value['inward_inventory_ca'];
                        $dt_data['today_usage_ca'] = $value['today_usage_ca'];
                        $dt_data['remaining_cement_ca'] = $value['remaining_cement_ca'];
                        $dt_data['notes_ca'] = $value['notes_ca'];
                        $dt_data['coupler_type'] = $value['coupler_type'];

                        $this->db->insert(db_prefix() . $data['form_type'] . '_coupler_form_detail', $dt_data);
                    }
                }
                if (!empty($new_order_wire)) {
                    foreach ($new_order_wire as $value) {

                        $dt_data = [];
                        $dt_data['form_id'] = $formid;
                        $dt_data['inward_inventory_wi'] = $value['inward_inventory_wi'];
                        $dt_data['today_usage_wi'] = $value['today_usage_wi'];
                        $dt_data['remaining_cement_wi'] = $value['remaining_cement_wi'];
                        $dt_data['notes_wi'] = $value['notes_wi'];
                        $dt_data['wire_type'] = $value['wire_type'];

                        $this->db->insert(db_prefix() . $data['form_type'] . '_wires_form_detail', $dt_data);
                    }
                }
                if (isset($new_order_council)) {
                    if (!empty($new_order_council)) {
                        foreach ($new_order_council as $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['inward_inventory_cb'] = $value['inward_inventory_cb'];
                            $dt_data['today_usage_cb'] = $value['today_usage_cb'];
                            $dt_data['remaining_cement_cb'] = $value['remaining_cement_cb'];
                            $dt_data['notes_cb'] = $value['notes_cb'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_council_form_detail', $dt_data);
                        }
                    }
                }
            }
            handle_tags_save($tags, $formid, 'form');

            if (isset($custom_fields)) {
                handle_custom_fields_post($formid, $custom_fields);
            }

            if (isset($data['assigned']) && $data['assigned'] != 0) {
                if ($data['assigned'] != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_form_assigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'forms/form/' . $formid,
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }

                    // send_mail_template('form_assigned_to_staff', get_staff($data['assigned'])->email, $data['assigned'], $formid, $data['userid'], $data['contactid']);
                }
            }
            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $formid);
            } else {
                $attachments = handle_form_attachments($formid);
                if ($attachments) {
                    $this->insert_form_attachments_to_database($attachments, $formid);
                }
            }
            $_attachments = $this->get_form_attachments($formid);


            $isContact = false;
            if (isset($data['userid']) && $data['userid'] != false) {
                $email     = $this->clients_model->get_contact($data['contactid'])->email;
                $isContact = true;
            } else {
                $email = $data['email'];
            }

            $template = 'form_created_to_customer';
            if ($admin == null) {
            } else {
                if ($cc) {
                    $this->db->where('formid', $formid);
                    $this->db->update('forms', ['cc' => is_array($cc) ? implode(',', $cc) : $cc]);
                }
            }

            $sendEmail = true;

            if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $data['contactid']]) == 0) {
                $sendEmail = false;
            }

            if ($sendEmail) {
                $form = $this->get_form_by_id($formid);
                // $admin == null ? [] : $_attachments - Admin opened form from admin area add the attachments to the email
                // send_mail_template($template, $form, $email, $admin == null ? [] : $_attachments, $cc);
            }

            hooks()->do_action('form_created', $formid);
            log_activity('New Form Created [ID: ' . $formid . ']');

            return $formid;
        }

        return false;
    }

    /**
     * Get latest 5 client forms
     * @param  integer $limit  Optional limit forms
     * @param  mixed $userid client id
     * @return array
     */
    public function get_client_latests_form($limit = 5, $userid = '')
    {
        $this->db->select(db_prefix() . 'forms.userid, formstatusid, statuscolor, ' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'forms.formid, subject, date');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'forms.userid', $userid);
        } else {
            $this->db->where(db_prefix() . 'forms.userid', get_client_user_id());
        }
        $this->db->limit($limit);
        $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);

        return $this->db->get()->result_array();
    }

    /**
     * Delete form from database and all connections
     * @param  mixed $formid formid
     * @return boolean
     */
    public function delete($formid)
    {
        $affectedRows = 0;
        hooks()->do_action('before_form_deleted', $formid);
        // final delete form
        add_drp_activity_log($formid, false);
        $this->db->where('formid', $formid);
        $this->db->delete(db_prefix() . 'forms');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;

            $this->db->where('merged_form_id', $formid);
            $this->db->set('merged_form_id', null);
            $this->db->update(db_prefix() . 'forms');

            $this->db->where('formid', $formid);
            $attachments = $this->db->get(db_prefix() . 'form_attachments')->result_array();
            if (count($attachments) > 0) {
                if (is_dir(get_upload_path_by_type('form') . $formid)) {
                    if (delete_dir(get_upload_path_by_type('form') . $formid)) {
                        foreach ($attachments as $attachment) {
                            $this->db->where('id', $attachment['id']);
                            $this->db->delete(db_prefix() . 'form_attachments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }

            $this->db->where('relid', $formid);
            $this->db->where('fieldto', 'forms');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            // Delete replies
            $this->db->where('formid', $formid);
            $this->db->delete(db_prefix() . 'form_replies');

            $this->db->where('rel_id', $formid);
            $this->db->where('rel_type', 'form');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_id', $formid);
            $this->db->where('rel_type', 'form');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'form');
            $this->db->where('rel_id', $formid);
            $this->db->delete(db_prefix() . 'reminders');

            // Get related tasks
            $this->db->where('rel_type', 'form');
            $this->db->where('rel_id', $formid);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
        }
        if ($affectedRows > 0) {
            log_activity('Form Deleted [ID: ' . $formid . ']');

            hooks()->do_action('after_form_deleted', $formid);

            return true;
        }

        return false;
    }

    /**
     * Update form data / admin use
     * @param  mixed $data form $_POST data
     * @return boolean
     */
    public function update_single_form_settings($data)
    {
        $affectedRows = 0;
        $data         = hooks()->apply_filters('before_form_settings_updated', $data);

        $formBeforeUpdate = $this->get_form_by_id($data['formid']);

        if (isset($data['merge_form_ids'])) {
            $forms = explode(',', $data['merge_form_ids']);
            if ($this->merge($data['formid'], $formBeforeUpdate->status, $forms)) {
                $affectedRows++;
            }
            unset($data['merge_form_ids']);
        }

        if (isset($data['custom_fields']) && count($data['custom_fields']) > 0) {
            if (handle_custom_fields_post($data['formid'], $data['custom_fields'])) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (handle_tags_save($tags, $data['formid'], 'form')) {
            $affectedRows++;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        if ($data['assigned'] == '') {
            $data['assigned'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if (isset($data['contactid']) && $data['contactid'] != '') {
            $data['name']  = null;
            $data['email'] = null;
        }

        if (empty($data['department'])) {
            $data['department'] = 0;
        }

        if (isset($data['contact_db_id'])) {
            unset($data['contact_db_id']);
        }
        if ($data['duedate'] != '') {
            $data['duedate'] = to_sql_date($data['duedate']);
        }

        if ($formBeforeUpdate->form_type == "dpr") {
            $dpr_form = array();
            $dpr_form['client_id'] = $data['client_id'];
            $dpr_form['pmc'] = $data['pmc'];
            $dpr_form['weather'] = $data['weather'];
            $dpr_form['consultant'] = $data['consultant'];
            $dpr_form['contractor'] = $data['contractor'];
            $dpr_form['work_stop'] = $data['work_stop'];
            unset($data['client_id']);
            unset($data['pmc']);
            unset($data['weather']);
            unset($data['consultant']);
            unset($data['contractor']);
            unset($data['work_stop']);
            unset($data['location']);
            unset($data['agency']);
            unset($data['type']);
            unset($data['sub_type']);
            unset($data['work_execute']);
            unset($data['material_consumption']);
            unset($data['male']);
            unset($data['female']);
            unset($data['total']);
            unset($data['machinery']);
            unset($data['total_machinery']);
            unset($data['isedit']);
            unset($data['staff']);
            unset($data['attendance']);
            unset($data['over_time']);
            unset($data['kharchi']);
            unset($data['challan']);
            unset($data['grade']);
            unset($data['structure']);
            unset($data['quantity']);
            unset($data['supplier']);
            unset($data['material_description']);
            unset($data['total']);
            $new_order = [];
            if (isset($data['newitems'])) {

                $new_order = $data['newitems'];
                unset($data['newitems']);
            }

            $new_order_dept = [];
            if (isset($data['newitemsdept'])) {
                $new_order_dept = $data['newitemsdept'];
                unset($data['newitemsdept']);
            }

            $new_order_rmc = [];
            if (isset($data['newitemsrmc'])) {
                $new_order_rmc = $data['newitemsrmc'];
                unset($data['newitemsrmc']);
            }

            $new_order_material = [];
            if (isset($data['newitemsmaterial'])) {
                $new_order_material = $data['newitemsmaterial'];
                unset($data['newitemsmaterial']);
            }

            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }

            $update_order_dept = [];
            if (isset($data['itemsdepartment'])) {
                $update_order_dept = $data['itemsdepartment'];
                unset($data['itemsdepartment']);
            }

            $update_order_rmc = [];
            if (isset($data['itemsrmc'])) {
                $update_order_rmc = $data['itemsrmc'];
                unset($data['itemsrmc']);
            }

            $update_order_material = [];
            if (isset($data['itemsmaterial'])) {
                $update_order_material = $data['itemsmaterial'];
                unset($data['itemsmaterial']);
            }
            $update_order_cement = [];
            if (isset($data['inward_inventory']) || isset($data['today_usage']) || isset($data['remaining_cement']) || isset($data['notes'])) {
                $update_order_cement[] = [
                    'id' => $data['rack_cement_id'],
                    'inward_inventory' => $data['inward_inventory'],
                    'today_usage' => $data['today_usage'],
                    'remaining_cement' => $data['remaining_cement'],
                    'notes' => $data['notes'],
                ];

                unset($data['inward_inventory']);
                unset($data['today_usage']);
                unset($data['remaining_cement']);
                unset($data['notes']);
                unset($data['rack_cement_id']);
            }
            $update_order_block = [];
            if (isset($data['inward_inventory_bmj']) || isset($data['today_usage_bmj']) || isset($data['remaining_cement_bmj']) || isset($data['notes_bmj'])) {
                $update_order_block[] = [
                    'id' => $data['block_mortar_id'],
                    'inward_inventory_bmj' => $data['inward_inventory_bmj'],
                    'today_usage_bmj' => $data['today_usage_bmj'],
                    'remaining_cement_bmj' => $data['remaining_cement_bmj'],
                    'notes_bmj' => $data['notes_bmj'],
                ];

                unset($data['inward_inventory_bmj']);
                unset($data['today_usage_bmj']);
                unset($data['remaining_cement_bmj']);
                unset($data['notes_bmj']);
                unset($data['block_mortar_id']);
            }
            $update_order_tile = [];
            if (isset($data['inward_inventory_ta']) || isset($data['today_usage_ta']) || isset($data['remaining_cement_ta']) || isset($data['notes_ta'])) {
                $update_order_tile[] = [
                    'id' => $data['tile_adhesive_id'],
                    'inward_inventory_ta' => $data['inward_inventory_ta'],
                    'today_usage_ta' => $data['today_usage_ta'],
                    'remaining_cement_ta' => $data['remaining_cement_ta'],
                    'notes_ta' => $data['notes_ta'],
                ];

                unset($data['inward_inventory_ta']);
                unset($data['today_usage_ta']);
                unset($data['remaining_cement_ta']);
                unset($data['notes_ta']);
                unset($data['tile_adhesive_id']);
            }
            $update_order_coupler = [];

            if (
                isset($data['inward_inventory_ca']) &&
                isset($data['today_usage_ca']) &&
                isset($data['remaining_cement_ca']) &&
                isset($data['notes_ca']) &&
                isset($data['coupler_type'])
            ) {
                $count = count($data['inward_inventory_ca']);

                for ($i = 0; $i < $count; $i++) {

                    // Skip empty rows (optional)
                    if (
                        $data['inward_inventory_ca'][$i] === '' &&
                        $data['today_usage_ca'][$i] === '' &&
                        $data['remaining_cement_ca'][$i] === '' &&
                        $data['notes_ca'][$i] === ''
                    ) {
                        continue;
                    }

                    $update_order_coupler[] = [
                        'id' => isset($data['coupler_id'][$i]) ? $data['coupler_id'][$i] : '',
                        'inward_inventory_ca' => $data['inward_inventory_ca'][$i],
                        'today_usage_ca' => $data['today_usage_ca'][$i],
                        'remaining_cement_ca' => $data['remaining_cement_ca'][$i],
                        'notes_ca' => $data['notes_ca'][$i],
                        'coupler_type' => $data['coupler_type'][$i]
                    ];
                }

                // unset after processing
                unset($data['inward_inventory_ca']);
                unset($data['today_usage_ca']);
                unset($data['remaining_cement_ca']);
                unset($data['notes_ca']);
                unset($data['coupler_type']);
                unset($data['coupler_id']);
            }
            $update_wire_coupler = [];

            if (
                isset($data['inward_inventory_wi']) &&
                isset($data['today_usage_wi']) &&
                isset($data['remaining_cement_wi']) &&
                isset($data['notes_wi']) &&
                isset($data['wire_type'])
            ) {
                $count = count($data['inward_inventory_wi']);

                for ($i = 0; $i < $count; $i++) {

                    // Skip empty rows
                    if (
                        $data['inward_inventory_wi'][$i] === '' &&
                        $data['today_usage_wi'][$i] === '' &&
                        $data['remaining_cement_wi'][$i] === '' &&
                        $data['notes_wi'][$i] === ''
                    ) {
                        continue;
                    }

                    $update_wire_coupler[] = [
                        'id' => isset($data['wires_id'][$i]) ? $data['wires_id'][$i] : '',
                        'inward_inventory_wi' => $data['inward_inventory_wi'][$i],
                        'today_usage_wi' => $data['today_usage_wi'][$i],
                        'remaining_cement_wi' => $data['remaining_cement_wi'][$i],
                        'notes_wi' => $data['notes_wi'][$i],
                        'wire_type' => $data['wire_type'][$i]
                    ];
                }

                // unset after processing
                unset($data['inward_inventory_wi']);
                unset($data['today_usage_wi']);
                unset($data['remaining_cement_wi']);
                unset($data['notes_wi']);
                unset($data['wire_type']);
                unset($data['wires_id']);
            }
            $update_cb_coupler = [];
            if (isset($data['inward_inventory_cb']) || isset($data['today_usage_cb']) || isset($data['remaining_cement_cb']) || isset($data['notes_cb'])) {
                $update_cb_coupler[] = [
                    'id' => $data['cb_id'],
                    'inward_inventory_cb' => $data['inward_inventory_cb'],
                    'today_usage_cb' => $data['today_usage_cb'],
                    'remaining_cement_cb' => $data['remaining_cement_cb'],
                    'notes_cb' => $data['notes_cb'],
                ];

                unset($data['inward_inventory_cb']);
                unset($data['today_usage_cb']);
                unset($data['remaining_cement_cb']);
                unset($data['notes_cb']);
                unset($data['cb_id']);
            }
            $remove_order = [];
            if (isset($data['removed_items'])) {
                $remove_order = $data['removed_items'];
                unset($data['removed_items']);
            }

            $remove_order_dept = [];
            if (isset($data['removed_department_items'])) {
                $remove_order_dept = $data['removed_department_items'];
                unset($data['removed_department_items']);
            }

            $remove_order_rmc = [];
            if (isset($data['removed_rmc_items'])) {
                $remove_order_rmc = $data['removed_rmc_items'];
                unset($data['removed_rmc_items']);
            }

            $remove_order_material = [];
            if (isset($data['removed_material_items'])) {
                $remove_order_material = $data['removed_material_items'];
                unset($data['removed_material_items']);
            }
            $remove_order_cement = [];
            if (isset($data['removed_cement_items'])) {
                $remove_order_cement = $data['removed_cement_items'];
                unset($data['removed_cement_items']);
            }
        }

        $old_form = $this->db
            ->where('formid', $data['formid'])
            ->get(db_prefix() . 'forms')
            ->row_array();

        $this->db->where('formid', $data['formid']);
        $this->db->update(db_prefix() . 'forms', $data);
        if ($this->db->affected_rows() > 0) {
            update_forms_activity_log($data['formid'], $old_form, $data);
            hooks()->do_action(
                'form_settings_updated',
                [
                    'form_id'       => $data['formid'],
                    'original_form' => $formBeforeUpdate,
                    'data'            => $data,
                ]
            );
            $affectedRows++;
        }

        if ($formBeforeUpdate->form_type == "dpr") {

            /* === MAIN FORM UPDATE === */
            if (!empty($dpr_form)) {
                $old_dpr_form = $this->db
                    ->where('form_id', $data['formid'])
                    ->get(db_prefix() . 'dpr_form')
                    ->row_array();
                $this->db->where('form_id', $data['formid']);
                $this->db->update(db_prefix() . 'dpr_form', $dpr_form);

                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                    update_dpr_form_activity_log(
                        $data['formid'],
                        $old_dpr_form,
                        $dpr_form
                    );
                }
            }

            /* === ADD DETAILS === */
            if (!empty($new_order)) {
                foreach ($new_order as $value) {
                    $dt_data = [
                        'form_id' => $data['formid'],
                        'location' => $value['location'],
                        'agency' => $value['agency'],
                        'type' => $value['type'],
                        'sub_type' => $value['sub_type'],
                        'work_execute' => $value['work_execute'],
                        'material_consumption' => $value['material_consumption'],
                        'male' => $value['male'],
                        'female' => $value['female'],
                        'total' => $value['total'],
                        'machinery' => $value['machinery'],
                        'total_machinery' => $value['total_machinery'],
                    ];

                    $this->db->insert(db_prefix() . 'dpr_form_detail', $dt_data);

                    if ($this->db->insert_id()) {
                        $affectedRows++;
                        dpr_detail_added_log($data['formid'], $dt_data);
                    }
                }
            }

            /* === UPDATE DETAILS === */
            if (!empty($update_order)) {
                foreach ($update_order as $value) {
                    $old_row = $this->db
                        ->where('id', $value['id'])
                        ->get(db_prefix() . 'dpr_form_detail')
                        ->row_array();

                    if (empty($old_row)) {
                        continue;
                    }

                    $dt_data = [
                        'location'             => $value['location'],
                        'agency'               => $value['agency'],
                        'type'                 => $value['type'],
                        'sub_type'             => $value['sub_type'],
                        'work_execute'         => $value['work_execute'],
                        'material_consumption' => $value['material_consumption'],
                        'male'                 => $value['male'],
                        'female'               => $value['female'],
                        'total'                => $value['total'],
                        'machinery'            => $value['machinery'],
                        'total_machinery'      => $value['total_machinery'],
                    ];

                    $this->db->where('id', $value['id']);
                    $this->db->update(db_prefix() . 'dpr_form_detail', $dt_data);

                    $affectedRows += $this->db->affected_rows();

                    update_dpr_detail_activity_log(
                        $old_row['form_id'],
                        $old_row,
                        $dt_data
                    );
                }
            }

            if (isset($new_order_dept) && !empty($new_order_dept)) {
                foreach ($new_order_dept as $key => $value) {

                    $dt_data = [
                        'form_id' => $data['formid'],
                        'staff' => $value['staff'],
                        'attendance' => $value['attendance'],
                        'over_time' => $value['over_time'],
                        'kharchi' => $value['kharchi'],
                    ];

                    $this->db->insert(db_prefix() . $formBeforeUpdate->form_type . '_dept_form_detail', $dt_data);
                    $new_insert_id = $this->db->insert_id();
                    if ($new_insert_id) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        dept_detail_added_log($data['formid'], $dt_data);
                    }
                }
            }

            if (isset($new_order_rmc) && !empty($new_order_rmc)) {
                foreach ($new_order_rmc as $key => $value) {
                    $dt_data = [
                        'form_id' => $data['formid'],
                        'challan' => $value['challan'],
                        'grade' => $value['grade'],
                        'structure' => $value['structure'],
                        'quantity' => $value['quantity'],
                    ];

                    $this->db->insert(db_prefix() . $formBeforeUpdate->form_type . '_rmc_form_detail', $dt_data);
                    $new_insert_id = $this->db->insert_id();
                    if ($new_insert_id) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        rmc_detail_added_log($data['formid'], $dt_data);
                    }
                }
            }

            if (isset($new_order_material) && !empty($new_order_material)) {
                foreach ($new_order_material as $key => $value) {
                    $dt_data = [
                        'form_id' => $data['formid'],
                        'challan' => $value['challan'],
                        'supplier' => $value['supplier'],
                        'material_description' => $value['material_description'],
                        'total' => $value['total'],
                    ];

                    $this->db->insert(db_prefix() . $formBeforeUpdate->form_type . '_material_form_detail', $dt_data);
                    $new_insert_id = $this->db->insert_id();
                    if ($new_insert_id) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        material_detail_added_log($data['formid'], $dt_data);
                    }
                }
            }

            if (isset($update_order_dept) && !empty($update_order_dept)) {
                foreach ($update_order_dept as $key => $value) {
                    $old_row = $this->db
                        ->where('id', $value['id'])
                        ->get(db_prefix() . $formBeforeUpdate->form_type . '_dept_form_detail')
                        ->row_array();

                    if (empty($old_row)) {
                        continue;
                    }

                    $dt_data = [
                        'staff' => $value['staff'],
                        'attendance' => $value['attendance'],
                        'over_time' => $value['over_time'],
                        'kharchi' => $value['kharchi'],
                    ];

                    $this->db->where('id', $value['id']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_dept_form_detail', $dt_data);

                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        update_dept_detail_activity_log($data['formid'], $old_row, $dt_data);
                    }
                }
            }

            if (isset($update_order_rmc) && !empty($update_order_rmc)) {
                foreach ($update_order_rmc as $key => $value) {
                    $old_row = $this->db
                        ->where('id', $value['id'])
                        ->get(db_prefix() . $formBeforeUpdate->form_type . '_rmc_form_detail')
                        ->row_array();

                    if (empty($old_row)) {
                        continue;
                    }

                    $dt_data = [
                        'challan' => $value['challan'],
                        'grade' => $value['grade'],
                        'structure' => $value['structure'],
                        'quantity' => $value['quantity'],
                    ];

                    $this->db->where('id', $value['id']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_rmc_form_detail', $dt_data);

                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        update_rmc_detail_activity_log($data['formid'], $old_row, $dt_data);
                    }
                }
            }

            if (isset($update_order_material) && !empty($update_order_material)) {
                foreach ($update_order_material as $key => $value) {
                    $old_row = $this->db
                        ->where('id', $value['id'])
                        ->get(db_prefix() . $formBeforeUpdate->form_type . '_material_form_detail')
                        ->row_array();

                    if (empty($old_row)) {
                        continue;
                    }

                    $dt_data = [
                        'challan' => $value['challan'],
                        'supplier' => $value['supplier'],
                        'material_description' => $value['material_description'],
                        'total' => $value['total'],
                    ];

                    $this->db->where('id', $value['id']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_material_form_detail', $dt_data);

                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                        update_material_detail_activity_log($data['formid'], $old_row, $dt_data);
                    }
                }
            }

            if (isset($update_order_cement) && !empty($update_order_cement)) {
                foreach ($update_order_cement as $key => $value) {
                    $table_name = db_prefix() . $formBeforeUpdate->form_type . '_cement_form_detail';

                    // Check if we should update (has ID and exists)
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Prepare update data
                            $dt_data = [
                                'inward_inventory' => $value['inward_inventory'],
                                'today_usage' => $value['today_usage'],
                                'remaining_cement' => $value['remaining_cement'],
                                'notes' => $value['notes'],
                            ];

                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, $dt_data);

                            // Log the update
                            update_order_cement_activity_log($data['formid'], $exists, $dt_data);

                            if ($this->db->affected_rows() > 0) $affectedRows++;
                            continue;
                        }
                    }

                    // Prepare insert data
                    $dt_data = [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory' => $value['inward_inventory'],
                        'today_usage' => $value['today_usage'],
                        'remaining_cement' => $value['remaining_cement'],
                        'notes' => $value['notes'],
                    ];

                    // Insert new record
                    $this->db->insert($table_name, $dt_data);

                    // Log the addition
                    order_cement_added_log($data['formid'], $dt_data);

                    if ($this->db->affected_rows() > 0) $affectedRows++;
                }
            }

            if (isset($update_order_block) && !empty($update_order_block)) {
                foreach ($update_order_block as $key => $value) {
                    $table_name = db_prefix() . $formBeforeUpdate->form_type . '_block_form_detail';

                    // Check if we should update (has ID and exists)
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Prepare update data
                            $dt_data = [
                                'inward_inventory_bmj' => $value['inward_inventory_bmj'],
                                'today_usage_bmj' => $value['today_usage_bmj'],
                                'remaining_cement_bmj' => $value['remaining_cement_bmj'],
                                'notes_bmj' => $value['notes_bmj'],
                            ];

                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, $dt_data);

                            // Log the update (you'll need to create this logging function)
                            update_order_block_activity_log($data['formid'], $exists, $dt_data);

                            if ($this->db->affected_rows() > 0) $affectedRows++;
                            continue;
                        }
                    }

                    // Prepare insert data
                    $dt_data = [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory_bmj' => $value['inward_inventory_bmj'],
                        'today_usage_bmj' => $value['today_usage_bmj'],
                        'remaining_cement_bmj' => $value['remaining_cement_bmj'],
                        'notes_bmj' => $value['notes_bmj'],
                    ];

                    // Insert new record
                    $this->db->insert($table_name, $dt_data);

                    // Log the addition (you'll need to create this logging function)
                    order_block_added_log($data['formid'], $dt_data);

                    if ($this->db->affected_rows() > 0) $affectedRows++;
                }
            }



            if (isset($update_order_tile) && !empty($update_order_tile)) {
                foreach ($update_order_tile as $key => $value) {
                    $table_name = db_prefix() . $formBeforeUpdate->form_type . '_tile_form_detail';

                    // Check if we should update (has ID and exists)
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Prepare update data
                            $dt_data = [
                                'inward_inventory_ta' => $value['inward_inventory_ta'],
                                'today_usage_ta' => $value['today_usage_ta'],
                                'remaining_cement_ta' => $value['remaining_cement_ta'],
                                'notes_ta' => $value['notes_ta'],
                            ];

                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, $dt_data);

                            // Log the update (you'll need to create this logging function)
                            update_order_tile_activity_log($data['formid'], $exists, $dt_data);

                            if ($this->db->affected_rows() > 0) $affectedRows++;
                            continue;
                        }
                    }

                    // Prepare insert data
                    $dt_data = [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory_ta' => $value['inward_inventory_ta'],
                        'today_usage_ta' => $value['today_usage_ta'],
                        'remaining_cement_ta' => $value['remaining_cement_ta'],
                        'notes_ta' => $value['notes_ta'],
                    ];

                    // Insert new record
                    $this->db->insert($table_name, $dt_data);

                    // Log the addition (you'll need to create this logging function)
                    order_tile_added_log($data['formid'], $dt_data);

                    if ($this->db->affected_rows() > 0) $affectedRows++;
                }
            }


            if (!empty($update_order_coupler)) {
                $table_name = db_prefix() . $formBeforeUpdate->form_type . '_coupler_form_detail';

                foreach ($update_order_coupler as $value) {
                    // UPDATE if ID exists
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Prepare update data
                            $dt_data = [
                                'inward_inventory_ca' => $value['inward_inventory_ca'],
                                'today_usage_ca' => $value['today_usage_ca'],
                                'remaining_cement_ca' => $value['remaining_cement_ca'],
                                'notes_ca' => $value['notes_ca'],
                                'coupler_type' => $value['coupler_type'],
                            ];

                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, $dt_data);

                            // Log the update (you'll need to create this logging function)
                            update_order_coupler_activity_log($data['formid'], $exists, $dt_data);

                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }

                            continue;
                        }
                    }

                    // Prepare insert data
                    $dt_data = [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory_ca' => $value['inward_inventory_ca'],
                        'today_usage_ca' => $value['today_usage_ca'],
                        'remaining_cement_ca' => $value['remaining_cement_ca'],
                        'notes_ca' => $value['notes_ca'],
                        'coupler_type' => $value['coupler_type'],
                    ];

                    // INSERT if no ID
                    $this->db->insert($table_name, $dt_data);

                    // Log the addition (you'll need to create this logging function)
                    order_coupler_added_log($data['formid'], $dt_data);

                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }



            if (!empty($update_wire_coupler)) {
                $table_name = db_prefix() . $formBeforeUpdate->form_type . '_wires_form_detail';

                foreach ($update_wire_coupler as $value) {
                    // UPDATE if ID exists
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Prepare update data
                            $dt_data = [
                                'inward_inventory_wi' => $value['inward_inventory_wi'],
                                'today_usage_wi' => $value['today_usage_wi'],
                                'remaining_cement_wi' => $value['remaining_cement_wi'],
                                'notes_wi' => $value['notes_wi'],
                                'wire_type' => $value['wire_type'],
                            ];

                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, $dt_data);

                            // Log the update (you'll need to create this logging function)
                            update_order_wire_coupler_activity_log($data['formid'], $exists, $dt_data);

                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }

                            continue;
                        }
                    }

                    // Prepare insert data
                    $dt_data = [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory_wi' => $value['inward_inventory_wi'],
                        'today_usage_wi' => $value['today_usage_wi'],
                        'remaining_cement_wi' => $value['remaining_cement_wi'],
                        'notes_wi' => $value['notes_wi'],
                        'wire_type' => $value['wire_type'],
                    ];

                    // INSERT if no ID
                    $this->db->insert($table_name, $dt_data);

                    // Log the addition (you'll need to create this logging function)
                    order_wire_coupler_added_log($data['formid'], $dt_data);

                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_cb_coupler) && !empty($update_cb_coupler)) {
                foreach ($update_cb_coupler as $key => $value) {
                    $table_name = db_prefix() . $formBeforeUpdate->form_type . '_council_form_detail';

                    // Check if we should update (has ID and exists)
                    if (!empty($value['id'])) {
                        $exists = $this->db->where('id', $value['id'])->get($table_name)->row_array();

                        if ($exists) {
                            // Update
                            $this->db->where('id', $value['id'])->update($table_name, [
                                'inward_inventory_cb' => $value['inward_inventory_cb'],
                                'today_usage_cb' => $value['today_usage_cb'],
                                'remaining_cement_cb' => $value['remaining_cement_cb'],
                                'notes_cb' => $value['notes_cb'],
                            ]);

                            if ($this->db->affected_rows() > 0) $affectedRows++;
                            continue;
                        }
                    }

                    // Insert new record
                    $this->db->insert($table_name, [
                        'form_id' => $formBeforeUpdate->formid,
                        'inward_inventory_cb' => $value['inward_inventory_cb'],
                        'today_usage_cb' => $value['today_usage_cb'],
                        'remaining_cement_cb' => $value['remaining_cement_cb'],
                        'notes_cb' => $value['notes_cb'],
                    ]);

                    if ($this->db->affected_rows() > 0) $affectedRows++;
                }
            }

            /* === REMOVE DETAILS === */
            if (!empty($remove_order)) {
                foreach ($remove_order as $id) {
                    $row = $this->db->where('id', $id)
                        ->get(db_prefix() . 'dpr_form_detail')
                        ->row_array();

                    if (!empty($row)) {
                        dpr_detail_removed_log($row['form_id'], $row);

                        $this->db->where('id', $id);
                        if ($this->db->delete(db_prefix() . 'dpr_form_detail')) {
                            $affectedRows++;
                        }
                    }
                }
            }

            if (isset($remove_order_dept) && !empty($remove_order_dept)) {
                foreach ($remove_order_dept as $key => $value) {
                    $this->db->where('id', $value);
                    if ($this->db->delete(db_prefix() . $formBeforeUpdate->form_type . '_dept_form_detail')) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                    }
                }
            }

            if (isset($remove_order_rmc) && !empty($remove_order_rmc)) {
                foreach ($remove_order_rmc as $key => $value) {
                    $this->db->where('id', $value);
                    if ($this->db->delete(db_prefix() . $formBeforeUpdate->form_type . '_rmc_form_detail')) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                    }
                }
            }

            if (isset($remove_order_material) && !empty($remove_order_material)) {
                foreach ($remove_order_material as $key => $value) {
                    $this->db->where('id', $value);
                    if ($this->db->delete(db_prefix() . $formBeforeUpdate->form_type . '_material_form_detail')) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                    }
                }
            }

            if (isset($remove_order_cement) && !empty($remove_order_cement)) {
                foreach ($remove_order_cement as $key => $value) {
                    $this->db->where('id', $value);
                    if ($this->db->delete(db_prefix() . $formBeforeUpdate->form_type . '_cement_form_detail')) {
                        $affectedRows++;
                        // Consider adding logging here like other sections
                    }
                }
            }
        }

        $sendAssignedEmail = false;

        $current_assigned = $formBeforeUpdate->assigned;
        if ($current_assigned != 0) {
            if ($current_assigned != $data['assigned']) {
                if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                    $sendAssignedEmail = true;
                    $notified          = add_notification([
                        'description'     => 'not_form_reassigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'forms/form/' . $data['formid'],
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }
                }
            }
        } else {
            if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                $sendAssignedEmail = true;
                $notified          = add_notification([
                    'description'     => 'not_form_assigned_to_you',
                    'touserid'        => $data['assigned'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'forms/form/' . $data['formid'],
                    'additional_data' => serialize([
                        $data['subject'],
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$data['assigned']]);
                }
            }
        }
        if ($sendAssignedEmail === true) {
            $this->db->where('staffid', $data['assigned']);
            $assignedEmail = $this->db->get(db_prefix() . 'staff')->row()->email;

            // send_mail_template('form_assigned_to_staff', $assignedEmail, $data['assigned'], $data['formid'], $data['userid'], $data['contactid']);
        }
        if ($affectedRows > 0) {
            log_activity('Form Updated [ID: ' . $data['formid'] . ']');

            return true;
        }

        return false;
    }

    /**
     * C<ha></ha>nge form status
     * @param  mixed $id     formid
     * @param  mixed $status status id
     * @return array
     */
    public function change_form_status($id, $status)
    {
        $this->db->where('formid', $id);
        $this->db->update(db_prefix() . 'forms', [
            'status' => $status,
        ]);
        $alert   = 'warning';
        $message = _l('form_status_changed_fail');
        if ($this->db->affected_rows() > 0) {
            $alert   = 'success';
            $message = _l('form_status_changed_successfully');
            hooks()->do_action('after_form_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);
        }

        return [
            'alert'   => $alert,
            'message' => $message,
        ];
    }

    // Priorities

    /**
     * Get form priority by id
     * @param  mixed $id priority id
     * @return mixed     if id passed return object else array
     */
    public function get_priority($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('priorityid', $id);

            return $this->db->get(db_prefix() . 'forms_priorities')->row();
        }

        return $this->db->get(db_prefix() . 'forms_priorities')->result_array();
    }

    /**
     * Add new form priority
     * @param array $data form priority data
     */
    public function add_priority($data)
    {
        $this->db->insert(db_prefix() . 'forms_priorities', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Priority Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update form priority
     * @param  array $data form priority $_POST data
     * @param  mixed $id   form priority id
     * @return boolean
     */
    public function update_priority($data, $id)
    {
        $this->db->where('priorityid', $id);
        $this->db->update(db_prefix() . 'forms_priorities', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Priority Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete form priorit
     * @param  mixed $id form priority id
     * @return mixed
     */
    public function delete_priority($id)
    {
        $current = $this->get($id);
        // Check if the priority id is used in forms table
        if (is_reference_in_table('priority', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('priorityid', $id);
        $this->db->delete(db_prefix() . 'forms_priorities');
        if ($this->db->affected_rows() > 0) {
            if (get_option('email_piping_default_priority') == $id) {
                update_option('email_piping_default_priority', '');
            }
            log_activity('Form Priority Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Predefined replies

    /**
     * Get predefined reply  by id
     * @param  mixed $id predefined reply id
     * @return mixed if id passed return object else array
     */
    public function get_predefined_reply($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'forms_predefined_replies')->row();
        }

        return $this->db->get(db_prefix() . 'forms_predefined_replies')->result_array();
    }

    /**
     * Add new predefined reply
     * @param array $data predefined reply $_POST data
     */
    public function add_predefined_reply($data)
    {
        $this->db->insert(db_prefix() . 'forms_predefined_replies', $data);
        $insertid = $this->db->insert_id();
        log_activity('New Predefined Reply Added [ID: ' . $insertid . ', ' . $data['name'] . ']');

        return $insertid;
    }

    /**
     * Update predefined reply
     * @param  array $data predefined $_POST data
     * @param  mixed $id   predefined reply id
     * @return boolean
     */
    public function update_predefined_reply($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'forms_predefined_replies', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Updated [ID: ' . $id . ', ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete predefined reply
     * @param  mixed $id predefined reply id
     * @return boolean
     */
    public function delete_predefined_reply($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'forms_predefined_replies');
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    // Form statuses

    /**
     * Get form status by id
     * @param  mixed $id status id
     * @return mixed     if id passed return object else array
     */
    public function get_form_status($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('formstatusid', $id);

            return $this->db->get(db_prefix() . 'forms_status')->row();
        }
        $this->db->order_by('statusorder', 'asc');

        return $this->db->get(db_prefix() . 'forms_status')->result_array();
    }

    /**
     * Add new form status
     * @param array form status $_POST data
     * @return mixed
     */
    public function add_form_status($data)
    {
        $this->db->insert(db_prefix() . 'forms_status', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Status Added [ID: ' . $insert_id . ', ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update form status
     * @param  array $data form status $_POST data
     * @param  mixed $id   form status id
     * @return boolean
     */
    public function update_form_status($data, $id)
    {
        $this->db->where('formstatusid', $id);
        $this->db->update(db_prefix() . 'forms_status', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Status Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete form status
     * @param  mixed $id form status id
     * @return mixed
     */
    public function delete_form_status($id)
    {
        $current = $this->get_form_status($id);
        // Default statuses cant be deleted
        if ($current->isdefault == 1) {
            return [
                'default' => true,
            ];
            // Not default check if if used in table
        } elseif (is_reference_in_table('status', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('formstatusid', $id);
        $this->db->delete(db_prefix() . 'forms_status');
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Status Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Form services
    public function get_service($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('serviceid', $id);

            return $this->db->get(db_prefix() . 'services')->row();
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'services')->result_array();
    }

    public function add_service($data)
    {
        $this->db->insert(db_prefix() . 'services', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Service Added [ID: ' . $insert_id . '.' . $data['name'] . ']');
        }

        return $insert_id;
    }

    public function update_service($data, $id)
    {
        $this->db->where('serviceid', $id);
        $this->db->update(db_prefix() . 'services', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Service Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_service($id)
    {
        if (is_reference_in_table('service', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('serviceid', $id);
        $this->db->delete(db_prefix() . 'services');
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Service Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @return array
     * Used in home dashboard page
     * Displays weekly form openings statistics (chart)
     */
    public function get_weekly_forms_opening_statistics()
    {
        $departments_ids = [];
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
            }
        }

        $chart = [
            'labels'   => get_weekdays(),
            'datasets' => [
                [
                    'label'           => _l('home_weekend_form_opening_statistics'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                ],
            ],
        ];

        $monday = new DateTime(date('Y-m-d', strtotime('monday this week')));
        $sunday = new DateTime(date('Y-m-d', strtotime('sunday this week')));

        $thisWeekDays = get_weekdays_between_dates($monday, $sunday);

        $byDepartments = count($departments_ids) > 0;
        if (isset($thisWeekDays[1])) {
            $i = 0;
            foreach ($thisWeekDays[1] as $weekDate) {
                $this->db->like('DATE(date)', $weekDate, 'after');
                $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);
                if ($byDepartments) {
                    $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                }
                $chart['datasets'][0]['data'][$i] = $this->db->count_all_results(db_prefix() . 'forms');

                $i++;
            }
        }

        return $chart;
    }

    public function get_forms_assignes_disctinct()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as assigned FROM ' . db_prefix() . 'forms WHERE assigned != 0 AND merged_form_id IS NULL')->result_array();
    }

    /**
     * Check for previous forms opened by this email/contact and link to the contact
     * @param  string $email      email to check for
     * @param  mixed $contact_id the contact id to transfer the forms
     * @return boolean
     */
    public function transfer_email_forms_to_contact($email, $contact_id)
    {
        // Some users don't want to fill the email
        if (empty($email)) {
            return false;
        }

        $customer_id = get_user_id_by_contact_id($contact_id);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'forms', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'form_replies', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        return true;
    }

    /**
     * Check whether the given formid is already merged into another primary form
     *
     * @param  int  $id
     *
     * @return boolean
     */
    public function is_merged($id)
    {
        return total_rows('forms', "formid={$id} and merged_form_id IS NOT NULL") > 0;
    }

    /**
     * @param $primary_form_id
     * @param $status
     * @param  array  $ids
     *
     * @return bool
     */
    public function merge($primary_form_id, $status, array $ids)
    {
        if ($this->is_merged($primary_form_id)) {
            return false;
        }

        if (($index = array_search($primary_form_id, $ids)) !== false) {
            unset($ids[$index]);
        }

        if (count($ids) == 0) {
            return false;
        }

        return (new MergeForms($primary_form_id, $ids))
            ->markPrimaryFormAs($status)
            ->merge();
    }

    /**
     * @param array $forms id's of forms to check
     * @return array
     */
    public function get_already_merged_forms($forms)
    {
        if (count($forms) === 0) {
            return [];
        }

        $alreadyMerged = [];
        foreach ($forms as $formId) {
            if ($this->is_merged((int) $formId)) {
                $alreadyMerged[] = $formId;
            }
        }

        return $alreadyMerged;
    }

    /**
     * @param $primaryFormId
     * @return array
     */
    public function get_merged_forms_by_primary_id($primaryFormId)
    {
        return $this->db->where('merged_form_id', $primaryFormId)->get(db_prefix() . 'forms')->result_array();
    }

    public function update_staff_replying($formId, $userId = '')
    {
        $form = $this->get($formId);

        if ($userId === '') {
            return $this->db->where('formid', $formId)
                ->set('staff_id_replying', null)
                ->update(db_prefix() . 'forms');
        }

        if ($form->staff_id_replying !== $userId && !is_null($form->staff_id_replying)) {
            return false;
        }

        if ($form->staff_id_replying === $userId) {
            return true;
        }

        return $this->db->where('formid', $formId)
            ->set('staff_id_replying', $userId)
            ->update(db_prefix() . 'forms');
    }

    public function get_staff_replying($formId)
    {
        $this->db->select('formid,staff_id_replying');
        $this->db->where('formid', $formId);

        return $this->db->get(db_prefix() . 'forms')->row();
    }

    private function getStaffMembersForFormNotification($department, $assignedStaff = 0)
    {
        $this->load->model('departments_model');
        $this->load->model('staff_model');

        $staffToNotify = [];
        if ($assignedStaff != 0 && get_option('staff_related_form_notification_to_assignee_only') == 1) {
            $member = $this->staff_model->get($assignedStaff, ['active' => 1]);
            if ($member) {
                $staffToNotify[] = (array) $member;
            }
        } else {
            $staff = $this->staff_model->get('', ['active' => 1]);
            foreach ($staff as $member) {
                if (get_option('access_forms_to_none_staff_members') == 0 && !is_staff_member($member['staffid'])) {
                    continue;
                }
                $staff_departments = $this->departments_model->get_staff_departments($member['staffid'], true);
                if (in_array($department, $staff_departments)) {
                    $staffToNotify[] = $member;
                }
            }
        }

        return $staffToNotify;
    }

    public function find_project_contact($project_id)
    {
        $this->db->select(db_prefix() . 'contacts.id as id, ' . db_prefix() . 'contacts.userid as userid, CONCAT(firstname," ",lastname) AS full_name', FALSE);
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.clientid = ' . db_prefix() . 'contacts.userid', 'left');
        $this->db->where(db_prefix() . 'projects.id', $project_id);
        $contacts = $this->db->get(db_prefix() . 'contacts')->result_array();
        return $contacts;
    }

    /**
     * Creates a Daily Progress Report row template.
     *
     * @param      array   $unit_data  The unit data
     * @param      string  $name       The name
     */
    public function create_dpr_row_template($name = '', $location = '', $agency = '', $type = '', $sub_type = '', $work_execute = '', $material_consumption = '', $male = '', $female = '', $total = '', $machinery = '', $total_machinery = '', $is_edit = false, $item_key = '')
    {
        $row = '';

        $name_location = 'location';
        $name_agency = 'agency';
        $name_type = 'type';
        $name_sub_type = 'sub_type';
        $name_work_execute = 'work_execute';
        $name_material_consumption = 'material_consumption';
        $name_male = 'male';
        $name_female = 'female';
        $name_total = 'total';
        $name_machinery = 'machinery';
        $name_total_machinery = 'total_machinery';

        if ($name == '') {
            $row .= '<tr class="main">';
            $manual = true;
        } else {
            $manual = false;
            $row .= '<tr class="item"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '">';
            $name_location = $name . '[location]';
            $name_agency = $name . '[agency]';
            $name_type = $name . '[type]';
            $name_sub_type = $name . '[sub_type]';
            $name_work_execute = $name . '[work_execute]';
            $name_material_consumption = $name . '[material_consumption]';
            $name_male = $name . '[male]';
            $name_female = $name . '[female]';
            $name_total = $name . '[total]';
            $name_machinery = $name . '[machinery]';
            $name_total_machinery = $name . '[total_machinery]';
        }

        $male = !empty($male) ? $male : 0;
        $female = !empty($female) ? $female : 0;
        $total = !empty($total) ? $total : 0;
        $total_machinery = !empty($total_machinery) ? $total_machinery : 0;

        $row .= '<td class="location">' . render_input($name_location, '', $location) . '</td>';
        $row .= '<td class="agency">' . get_vendor($name_agency, $agency) . '</td>';
        $row .= '<td class="progress_report_type" >' . get_progress_report_type_listing($name_type, $type) . '</td>';
        $row .= '<td class="progress_report_sub_type">' . render_textarea($name_sub_type, '', $sub_type) . '</td>';
        $row .= '<td class="work_execute">' . render_input($name_work_execute, '', $work_execute) . '</td>';
        $row .= '<td class="material_consumption">' . render_input($name_material_consumption, '', $material_consumption) . '</td>';
        $row .= '<td class="male">' . render_input($name_male, '', $male, 'nubmer', ['onblur' => 'dpr_calculate_total();', 'onchange' => 'dpr_calculate_total();']) . '</td>';
        $row .= '<td class="female">' . render_input($name_female, '', $female, 'nubmer', ['onblur' => 'dpr_calculate_total();', 'onchange' => 'dpr_calculate_total();']) . '</td>';
        $row .= '<td class="total">' . render_input($name_total, '', $total, 'number', ['readonly' => true, 'style' => 'padding:0px !important;text-align: center;']) . '</td>';
        $row .= '<td class="machinery">' . get_progress_report_machinary_listing($name_machinery, $machinery) . '</td>';
        $row .= '<td class="total_machinery">' . render_input($name_total_machinery, '', $total_machinery, 'nubmer') . '</td>';

        if ($name == '') {
            $row .= '<td><button type="button" class="btn pull-right btn-info dpr-add-item-to-table"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="dpr_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }

        $row .= '</tr>';
        return $row;
    }

    public function get_dpr_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_form')->row();
    }

    public function get_dpr_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_form_detail')->result_array();
    }

    public function get_dpr_department_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_dept_form_detail')->result_array();
    }

    public function get_dpr_department_cement_rack($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_cement_form_detail')->result_array();
    }

    public function get_dpr_rmc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_rmc_form_detail')->result_array();
    }

    public function get_dpr_rmc_sum_form_detail($form_id)
    {
        $this->db->select('SUM(quantity) as quantity,grade');
        $this->db->where('form_id', $form_id);
        $this->db->group_by('grade');
        return $this->db->get(db_prefix() . 'dpr_rmc_form_detail')->result_array();
    }

    public function get_dpr_material_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_material_form_detail')->result_array();
    }


    public function get_apc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'apc_form')->row();
    }

    public function get_apc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'apc_form_detail')->result_array();
    }
    public function get_wpc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'wpc_form')->row();
    }

    public function get_wpc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'wpc_form_detail')->result_array();
    }
    public function get_mfa_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mfa_form')->row();
    }
    public function get_mfa_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mfa_form_detail')->result_array();
    }
    public function get_mlg_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mlg_form')->row();
    }

    public function get_mlg_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mlg_form_detail')->result_array();
    }
    public function get_apc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'apcattachments')->result_array();
    }
    public function get_esc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'esc_form')->row();
    }
    public function get_esc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'esc_form_detail')->result_array();
    }
    public function get_esc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'escattachments')->result_array();
    }
    public function delete_apc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'apcattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'apc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'apcattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function get_msh_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'msh_form')->row();
    }
    public function get_msh_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'msh_form_detail')->result_array();
    }
    public function get_msh_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'mshattachments')->result_array();
    }
    public function get_sca_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'sca_form')->row();
    }
    public function get_sca_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'sca_form_detail')->result_array();
    }
    public function get_sca_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'scaattachments')->result_array();
    }
    public function get_mlg_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'mlgattachments')->result_array();
    }

    public function get_wpc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'wpcattachments')->result_array();
    }

    public function get_cfwas_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cfwas_form')->row();
    }

    public function get_cfwas_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cfwas_form_detail')->result_array();
    }
    public function get_cfwas_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'cfwasattachments')->result_array();
    }
    public function get_cflc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cflc_form')->row();
    }

    public function get_cflc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cflc_form_detail')->result_array();
    }
    public function get_cflc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'cflcattachments')->result_array();
    }

    public function get_facc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'facc_form')->row();
    }

    public function get_facc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'facc_form_detail')->result_array();
    }
    public function get_facc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'faccattachments')->result_array();
    }
    public function get_cosc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cosc_form')->row();
    }
    public function get_cosc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'cosc_form_detail')->result_array();
    }
    public function get_cosc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'coscattachments')->result_array();
    }
    public function delete_wpc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'wpcattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'wpc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'wpcattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_msh_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'mshattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'msh_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'mshattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_sca_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'scaattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'sca_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'scaattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_mlg_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'mlgattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'mlg_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'mlgattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_esc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'escattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'esc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'escattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_cfwas_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'cfwasattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'cfwas_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'cfwasattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_cflc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'cflcattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'cflc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'cflcattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_facc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'faccattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'facc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'faccattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_cosc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'coscattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'cosc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'coscattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function get_form_listing()
    {
        $this->db->select('fc.id AS category_id, fc.name AS category_name, fo.form_id, fo.name AS form_name');
        $this->db->from('tblform_categories fc');
        $this->db->join('tblform_options fo', 'fc.id = fo.category_id', 'left');
        $this->db->order_by('fc.sort_order, fo.sort_order'); // Add sort_order fields if needed

        $query = $this->db->get();
        $result = array();

        foreach ($query->result_array() as $row) {
            $category_id = $row['category_id'];

            if (!isset($result[$category_id])) {
                $result[$category_id] = array(
                    'id' => $category_id,
                    'name' => $row['category_name'],
                    'options' => array()
                );
            }

            $result[$category_id]['options'][] = array(
                'id' => $row['form_id'],
                'name' => $row['form_name']
            );
        }

        return array_values($result);
    }
    public function get_form_items($form_type)
    {

        $this->db->select('id, name');
        $this->db->where('form_type', $form_type);
        $this->db->order_by('sort_order', 'asc');
        $query = $this->db->get('tblform_items');
        return $query->result_array();
    }

    public function get_form_data($id)
    {
        $this->db->select('*');
        $this->db->join(db_prefix() . 'form_options', db_prefix() . 'form_options.form_id = ' . db_prefix() . 'forms.form_type', 'inner');
        $this->db->where('formid', $id);
        $query = $this->db->get(db_prefix() . 'forms');
        return $query->row();
    }

    public function get_progress_report_type()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get(db_prefix() . 'progress_report_type');
        return $query->result_array();
    }

    public function get_progress_report_sub_type()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get(db_prefix() . 'progress_report_sub_type');
        return $query->result_array();
    }

    public function get_progress_report_machinary()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get(db_prefix() . 'progress_report_machinary');
        return $query->result_array();
    }

    public function get_progress_report_department_labor()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get(db_prefix() . 'progress_report_dept_labor');
        return $query->result_array();
    }

    public function get_progress_report_rmc_grade()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get(db_prefix() . 'progress_report_rmc_grade');
        return $query->result_array();
    }

    public function add_progress_report_type($data)
    {
        $this->db->insert(db_prefix() . 'progress_report_type', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function update_progress_report_type($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'progress_report_type', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function delete_progress_report_type($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'progress_report_type');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function add_progress_report_sub_type($data)
    {
        $this->db->insert(db_prefix() . 'progress_report_sub_type', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function update_progress_report_sub_type($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'progress_report_sub_type', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function delete_progress_report_sub_type($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'progress_report_sub_type');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function add_progress_report_machinary($data)
    {
        $this->db->insert(db_prefix() . 'progress_report_machinary', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }
    public function add_progress_report_dept_labor($data)
    {
        $this->db->insert(db_prefix() . 'progress_report_dept_labor', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function add_progress_report_rmc_grade($data)
    {
        $this->db->insert(db_prefix() . 'progress_report_rmc_grade', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function update_progress_report_machinary($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'progress_report_machinary', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function update_progress_report_department_labor($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'progress_report_dept_labor', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function update_progress_report_rmc_grade($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'progress_report_rmc_grade', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function delete_progress_report_machinary($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'progress_report_machinary');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function delete_progress_report_department_labor($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'progress_report_dept_labor');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function delete_progress_report_rmc_grade($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'progress_report_rmc_grade');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_daily_labor_report($id)
    {
        $result = array();
        $dpr_form_detail = $this->get_dpr_form_detail($id);

        if (!empty($dpr_form_detail)) {
            // Get all unique types from the form data
            $unique_types = array_values(array_unique(array_column($dpr_form_detail, 'type')));

            if (!empty($unique_types)) {
                foreach ($unique_types as $type_id) {
                    // Get type name from database
                    $this->db->where('id', $type_id);
                    $progress_report_type = $this->db->get(db_prefix() . 'progress_report_type')->row();

                    if ($progress_report_type) {
                        // Filter records by this type
                        $type_filtered = array_filter($dpr_form_detail, function ($item) use ($type_id) {
                            return $item['type'] == $type_id;
                        });

                        // Create result array for this type
                        $type_result = array(
                            'name' => $progress_report_type->name,
                            'male' => !empty($type_filtered) ? array_sum(array_column($type_filtered, 'male')) : 0,
                            'female' => !empty($type_filtered) ? array_sum(array_column($type_filtered, 'female')) : 0
                        );
                        $type_result['total'] = $type_result['male'] + $type_result['female'];

                        $result[] = $type_result;
                    }
                }
            }
        }

        return $result;
    }

    public function get_labor_report_machinery($id)
    {
        $result = array();
        $dpr_form_detail = $this->get_dpr_form_detail($id);

        if (!empty($dpr_form_detail)) {
            $unique_machinery = array_values(array_unique(array_column($dpr_form_detail, 'machinery')));
            if (!empty($unique_machinery)) {
                $unique_machinery = array_values(array_filter($unique_machinery));
                foreach ($unique_machinery as $key => $value) {
                    $machinery_array = array();
                    $this->db->where('id', $value);
                    $progress_report_machinary = $this->db->get(db_prefix() . 'progress_report_machinary')->row();
                    $machinery_array['name'] = $progress_report_machinary->name;
                    $machinery_filtered = array_filter($dpr_form_detail, function ($item) use ($value) {
                        return $item['machinery'] == $value;
                    });
                    $machinery_array['total'] = !empty($machinery_filtered) ? array_sum(array_column($machinery_filtered, 'total_machinery')) : 0;
                    $result[] = $machinery_array;
                }
            }
        }

        return $result;
    }

    public function get_dpr_dashboard($data)
    {
        $total_workforce_labels = [];
        $total_workforce_values = [];
        $stacked_labor_labels = [];
        $stacked_labor_values = [];
        // pull inputs
        $projects   = $data['projects']   ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date   = $data['end_date']   ?? null;

        // normalize dates to Y-m-d
        if ($start_date) {
            $start_date = date('Y-m-d', strtotime($start_date));
        }
        if ($end_date) {
            $end_date   = date('Y-m-d', strtotime($end_date));
        }

        // 1. distinct form dates
        $this->db->select('DATE(date) AS date')
            ->from(db_prefix() . 'forms');
        $this->db->where('form_type', 'dpr');

        if ($projects !== null) {
            if (is_array($projects)) {
                $this->db->where_in('project_id', $projects);
            } else {
                $this->db->where('project_id', $projects);
            }
        }

        if ($start_date) {
            $this->db->where('DATE(date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(date) <=', $end_date);
        }

        $this->db->group_by('date')
            ->order_by('date', 'ASC');
        $forms = $this->db->get()->result_array();


        // 2. sub_type totals
        $this->db->select([
            'DATE(f.date) AS date',
            'd.sub_type',
            'SUM(d.total) AS total'
        ])
            ->from(db_prefix() . 'dpr_form_detail d')
            ->join(db_prefix() . 'forms f', 'f.formid = d.form_id')
            ->where("d.sub_type != ''", null, false);

        if ($projects !== null) {
            if (is_array($projects)) {
                $this->db->where_in('f.project_id', $projects);
            } else {
                $this->db->where('f.project_id', $projects);
            }
        }

        if ($start_date) {
            $this->db->where('DATE(f.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(f.date) <=', $end_date);
        }

        $this->db->group_by(['date', 'd.sub_type'])
            ->order_by('date', 'ASC');
        $sub_type_array = $this->db->get()->result_array();


        // 3. type totals
        $this->db->select([
            'DATE(f.date) AS date',
            'd.type',
            'SUM(d.total) AS total'
        ])
            ->from(db_prefix() . 'dpr_form_detail d')
            ->join(db_prefix() . 'forms f', 'f.formid = d.form_id')
            ->where("d.type != ''", null, false);

        if ($projects !== null) {
            if (is_array($projects)) {
                $this->db->where_in('f.project_id', $projects);
            } else {
                $this->db->where('f.project_id', $projects);
            }
        }

        if ($start_date) {
            $this->db->where('DATE(f.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(f.date) <=', $end_date);
        }

        $this->db->group_by(['date', 'd.type'])
            ->order_by('date', 'ASC');
        $type_array = $this->db->get()->result_array();

        //4. deprt labour 
        $this->db->select([
            'DATE(f.date) AS date',
            'd.attendance',
            'd.staff',
        ])
            ->from(db_prefix() . 'dpr_dept_form_detail d')
            ->join(db_prefix() . 'forms f', 'f.formid = d.form_id')
            ->where("d.id != ''", null, false);

        if ($projects !== null) {
            if (is_array($projects)) {
                $this->db->where_in('f.project_id', $projects);
            } else {
                $this->db->where('f.project_id', $projects);
            }
        }

        if ($start_date) {
            $this->db->where('DATE(f.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(f.date) <=', $end_date);
        }

        $this->db
            ->order_by('date', 'ASC');
        $deprt_array = $this->db->get()->result_array();

        //5. RMC Plant
        $this->db->select([
            'DATE(f.date) AS date',
            'd.grade',
            'sum(d.quantity) AS quantity',
        ])
            ->from(db_prefix() . 'dpr_rmc_form_detail d')
            ->join(db_prefix() . 'forms f', 'f.formid = d.form_id')
            ->where("d.id != ''", null, false);

        if ($projects !== null) {
            if (is_array($projects)) {
                $this->db->where_in('f.project_id', $projects);
            } else {
                $this->db->where('f.project_id', $projects);
            }
        }

        if ($start_date) {
            $this->db->where('DATE(f.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(f.date) <=', $end_date);
        }

        $this->db->group_by(['date', 'd.grade'])
            ->order_by('date', 'ASC');
        $rmc_plant_array = $this->db->get()->result_array();
        //5. Cement
        $this->db->select([
            'DATE(f.date) AS date',
            'e.inward_inventory',
            'e.today_usage',
            'e.remaining_cement'
        ])
            ->from(db_prefix() . 'dpr_cement_form_detail e')
            ->join(db_prefix() . 'forms f', 'f.formid = e.form_id');

        // Fix the projects filter
        if ($projects !== null && !empty($projects)) {
            if (is_array($projects)) {
                $this->db->where_in('f.project_id', $projects);
            } else {
                $this->db->where('f.project_id', $projects);
            }
        }

        // Date filters
        if ($start_date) {
            $this->db->where('DATE(f.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(f.date) <=', $end_date);
        }

        // If you want only the latest record per date, use group_by with MAX or ORDER BY
        $this->db->group_by('DATE(f.date)')
            ->order_by('f.date', 'DESC'); // Get latest records first

        $cr_array = $this->db->get()->result_array();


        // 4. Reference lists
        $progress_report_sub_type = $this->db->get(db_prefix() . 'progress_report_sub_type')->result_array();
        $progress_report_type = $this->db->get(db_prefix() . 'progress_report_type')->result_array();
        $progress_report_dept_labor = $this->db->get(db_prefix() . 'progress_report_dept_labor')->result_array();
        $progress_report_dept_rmc_grade = $this->db->get(db_prefix() . 'progress_report_rmc_grade')->result_array();
        // 5. Process each unique form date
        foreach ($forms as $form) {
            $date = $form['date'];
            $total_workforce_labels[] = $date;
            $stacked_labor_labels[] = $date;

            foreach ($progress_report_sub_type as $sub) {
                $match = array_values(array_filter($sub_type_array, function ($x) use ($date, $sub) {
                    return $x['date'] == $date && $x['sub_type'] == $sub['id'];
                }));
                $total = !empty($match) ? $match[0]['total'] : 0;
                $total_workforce_values[$sub['name']][] = $total;
            }


            foreach ($progress_report_type as $type) {
                $match = array_values(array_filter($type_array, function ($x) use ($date, $type) {
                    return $x['date'] == $date && $x['type'] == $type['id'];
                }));
                $total = !empty($match) ? $match[0]['total'] : 0;
                $stacked_labor_values[$type['name']][] = $total;
            }
        }
        // 6. Convert values to Chart.js compatible datasets
        $total_workforce_datasets = array_map(function ($label) use ($total_workforce_values) {
            return ['label' => $label, 'data' => array_values($total_workforce_values[$label])];
        }, array_keys($total_workforce_values));
        $stacked_labor_datasets = array_map(function ($label) use ($stacked_labor_values) {
            return ['label' => $label, 'data' => array_values($stacked_labor_values[$label])];
        }, array_keys($stacked_labor_values));


        // 7. Build HTML tables
        $preport_sub_type_html = '<div class="table-responsive s_table "><table  class="table items no-mtop preportSubTypeTable" style="border: 1px solid #dee2e6;"><tbody>';
        $preport_sub_type_html .= '<tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;"><td align="left">Row Labels</td>';
        foreach ($progress_report_sub_type as $sub) {
            $preport_sub_type_html .= '<td align="right">' . $sub['name'] . '</td>';
        }
        $preport_sub_type_html .= '</tr>';

        if (!empty($forms)) {
            foreach ($forms as $form) {
                $date = $form['date'];
                $preport_sub_type_html .= '<tr><td>' . $date . '</td>';
                foreach ($progress_report_sub_type as $sub) {
                    $match = array_values(array_filter($sub_type_array, function ($x) use ($date, $sub) {
                        return $x['date'] == $date && $x['sub_type'] == $sub['id'];
                    }));
                    $total = !empty($match) ? $match[0]['total'] : 0;
                    $preport_sub_type_html .= '<td align="right">' . $total . '</td>';
                }
                $preport_sub_type_html .= '</tr>';
            }
        } else {
            $preport_sub_type_html .= '<tr><td colspan="' . (count($progress_report_sub_type) + 1) . '" align="center">No records found</td></tr>';
        }
        $preport_sub_type_html .= '</tbody></table></div>';

        // Type Table
        $preport_type_html = '<div class="table-responsive s_table" style="overflow-x: auto; border: 1px solid #dee2e6;"><table class="table items no-mtop preportTypeTable" style="border: 1px solid #dee2e6; min-width: 100%;"><tbody>';
        $preport_type_html .= '<tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;"><td align="left">Row Labels</td>';
        foreach ($progress_report_type as $type) {
            $preport_type_html .= '<td align="right">' . $type['name'] . '</td>';
        }
        $preport_type_html .= '</tr>';

        if (!empty($forms)) {
            foreach ($forms as $form) {
                $date = $form['date'];
                $preport_type_html .= '<tr><td>' . $date . '</td>';
                foreach ($progress_report_type as $type) {
                    $match = array_values(array_filter($type_array, function ($x) use ($date, $type) {
                        return $x['date'] == $date && $x['type'] == $type['id'];
                    }));
                    $total = !empty($match) ? $match[0]['total'] : 0;
                    $preport_type_html .= '<td align="right">' . $total . '</td>';
                }
                $preport_type_html .= '</tr>';
            }
        } else {
            $preport_type_html .= '<tr><td colspan="' . (count($progress_report_type) + 1) . '" align="center">No records found</td></tr>';
        }
        $preport_type_html .= '</tbody></table></div>';

        $preport_deprt_html = '<div class="table-responsive s_table"><table class="table items no-mtop preportDeprtTable" style="border: 1px solid #dee2e6;"><tbody>';
        $preport_deprt_html .= '<tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;"><td align="left">Row Labels</td>';
        foreach ($progress_report_dept_labor as $staff) {
            $preport_deprt_html .= '<td align="right">' . $staff['name'] . '</td>';
        }

        $preport_deprt_html .= '</tr>';

        // Initialize sum array for each staff
        $staff_sums = array_fill_keys(array_column($progress_report_dept_labor, 'id'), 0);

        if (!empty($forms)) {
            foreach ($forms as $form) {
                $date = $form['date'];
                $preport_deprt_html .= '<tr><td>' . $date . '</td>';

                foreach ($progress_report_dept_labor as $staff) {
                    $match = array_values(array_filter($deprt_array, function ($x) use ($date, $staff) {
                        return $x['date'] == $date && $x['staff'] == $staff['id'];
                    }));

                    $attendance = !empty($match) ? (float)$match[0]['attendance'] : 0;

                    // Initialize if not set
                    if (!isset($staff_sums[$staff['id']])) {
                        $staff_sums[$staff['id']] = 0;
                    }

                    // Add to the sum for this staff
                    $staff_sums[$staff['id']] = (float)$staff_sums[$staff['id']] + $attendance;

                    $preport_deprt_html .= '<td align="right">' . $attendance . '</td>';
                }

                $preport_deprt_html .= '</tr>';
            }

            // Add total row
            $preport_deprt_html .= '<tr style="font-weight: bold; background: #f8f9fa; border-top: 2px solid #dee2e6;">';
            $preport_deprt_html .= '<td align="left">Total</td>';

            foreach ($progress_report_dept_labor as $staff) {
                $preport_deprt_html .= '<td align="right">' . $staff_sums[$staff['id']] . '</td>';
            }

            $preport_deprt_html .= '</tr>';
        } else {
            $preport_deprt_html .= '<tr><td colspan="' . (count($progress_report_dept_labor) + 1) . '" align="center">No records found</td></tr>';
        }
        $preport_deprt_html .= '</tbody></table></div>';


        $preport_rmc_plant_html = '<div class="table-responsive s_table"><table class="table items no-mtop preportRMCplantTable" style="border: 1px solid #dee2e6;"><tbody>';
        $preport_rmc_plant_html .= '<tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;"><td align="left">Row Labels</td>';
        foreach ($progress_report_dept_rmc_grade as $grade) {
            $preport_rmc_plant_html .= '<td align="right">' . $grade['name'] . '</td>';
        }
        $preport_rmc_plant_html .= '</tr>';

        if (!empty($forms)) {
            foreach ($forms as $form) {
                $date = $form['date'];
                $preport_rmc_plant_html .= '<tr><td>' . $date . '</td>';
                foreach ($progress_report_dept_rmc_grade as $grade) {
                    $match = array_values(array_filter($rmc_plant_array, function ($x) use ($date, $grade) {
                        return $x['date'] == $date && $x['grade'] == $grade['id'];
                    }));
                    $quantity = !empty($match) ? $match[0]['quantity'] : 0;
                    $preport_rmc_plant_html .= '<td align="right">' . $quantity . '</td>';
                }
                $preport_rmc_plant_html .= '</tr>';
            }
        } else {
            $preport_rmc_plant_html .= '<tr><td colspan="' . (count($progress_report_dept_rmc_grade) + 1) . '" align="center">No records found</td></tr>';
        }
        $preport_rmc_plant_html .= '</tbody></table></div>';

        $preport_rack_cement_html = '<div class="table-responsive s_table"><table class="table items no-mtop preportcementTable" style="border: 1px solid #dee2e6;"><tbody>';

        // Add table headers
        $preport_rack_cement_html .= '<tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;">';
        $preport_rack_cement_html .= '<td align="left">Date</td>';
        $preport_rack_cement_html .= '<td align="right">Inward Inventory</td>';
        $preport_rack_cement_html .= '<td align="right">Today Usage</td>';
        $preport_rack_cement_html .= '<td align="right">Remaining Cement</td>';
        $preport_rack_cement_html .= '</tr>';

        // Add data rows
        if (!empty($cr_array)) {
            foreach ($cr_array as $row) {
                $preport_rack_cement_html .= '<tr>';
                $preport_rack_cement_html .= '<td align="left">' . $row['date'] . '</td>';
                $preport_rack_cement_html .= '<td align="right">' . $row['inward_inventory'] . '</td>';
                $preport_rack_cement_html .= '<td align="right">' . $row['today_usage'] . '</td>';
                $preport_rack_cement_html .= '<td align="right">' . $row['remaining_cement'] . '</td>';
                $preport_rack_cement_html .= '</tr>';
            }
        } else {
            // No records found
            $preport_rack_cement_html .= '<tr><td colspan="4" align="center">No cement records found</td></tr>';
        }

        $preport_rack_cement_html .= '</tbody></table></div>';

        // Final response
        return [
            'preport_sub_type_html' => $preport_sub_type_html,
            'preport_type_html' => $preport_type_html,
            'total_workforce_labels' => $total_workforce_labels,
            'total_workforce_values' => $total_workforce_datasets,
            'stacked_labor_labels' => $stacked_labor_labels,
            'stacked_labor_values' => $stacked_labor_values,
            'preport_deprt_html' => $preport_deprt_html,
            'preport_rmc_plant_html' => $preport_rmc_plant_html,
            'preport_rack_cement_html' => $preport_rack_cement_html
        ];
    }



    public function get_form($form_id)
    {
        $this->db->where('formid', $form_id);
        return $this->db->get(db_prefix() . 'forms')->row();
    }

    public function get_dpr_projects()
    {
        $this->db->select([
            db_prefix() . 'forms.project_id as id',
            db_prefix() . 'projects.name'
        ]);
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'forms.project_id', 'left');
        $this->db->where(db_prefix() . 'forms.form_type', 'dpr');
        $this->db->group_by(db_prefix() . 'forms.project_id');
        $this->db->order_by(db_prefix() . 'projects.name', 'asc');
        return $this->db->get()->result_array();
    }

    public function get_form_dpr_pdf_data($id)
    {
        $this->db->select('*');
        $this->db->where('formid', $id);
        $query = $this->db->get(db_prefix() . 'forms');
        return $query->row();
    }

    public function add_edit_attachments($data, $id, $admin = null, $pipe_attachments = false)
    {
        if (isset($data['assign_to_current_user'])) {
            $assigned = get_staff_user_id();
            unset($data['assign_to_current_user']);
        }

        $unsetters = [
            'note_description',
            'department',
            'priority',
            'subject',
            'assigned',
            'project_id',
            'service',
            'status_top',
            'attachments',
            'DataTables_Table_0_length',
            'DataTables_Table_1_length',
            'custom_fields',
        ];

        foreach ($unsetters as $unset) {
            if (isset($data[$unset])) {
                unset($data[$unset]);
            }
        }


        if ($pipe_attachments != false) {
            $this->process_pipe_attachments($pipe_attachments, $id);
        } else {
            $attachments = handle_form_attachments($id);
            if ($attachments) {
                $this->forms_model->insert_form_attachments_to_database($attachments, $id);
                return true;
            }
        }




        return false;
    }

    public function lock_dpr($data)
    {
        if (!empty($data)) {
            $this->db->where('formid', $data['formid']);
            $this->db->update(db_prefix() . 'forms', [
                'locked' => 1,
            ]);
            return true;
        }
    }

    public function unlock_dpr($data)
    {
        if (!empty($data)) {
            $this->db->where('formid', $data['formid']);
            $this->db->update(db_prefix() . 'forms', [
                'locked' => 0,
            ]);
            return true;
        }
    }

    public function create_dpr_department_row_template($name = '', $staff = '', $attendance = '', $over_time = '', $kharchi = '', $is_edit = false, $item_key = '')
    {
        $row = '';

        $name_staff = 'staff';
        $name_attendance = 'attendance';
        $name_over_time = 'over_time';
        $name_kharchi = 'kharchi';

        if ($name == '') {
            $row .= '<tr class="main">';
            $manual = true;
        } else {
            $manual = false;
            $row .= '<tr class="item"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '">';
            $name_staff = $name . '[staff]';
            $name_attendance = $name . '[attendance]';
            $name_over_time = $name . '[over_time]';
            $name_kharchi = $name . '[kharchi]';
        }
        $get_labour_list = $this->get_progress_report_department_labor();

        $row .= '<td class="staff">' . render_select($name_staff, $get_labour_list, ['id', 'name'], '', $staff, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-width' => '100%']) . '</td>';
        $row .= '<td class="attendance">' . render_input($name_attendance, '', $attendance) . '</td>';
        $row .= '<td class="over_time">' . render_input($name_over_time, '', $over_time) . '</td>';
        $row .= '<td class="kharchi">' . render_input($name_kharchi, '', $kharchi) . '</td>';


        if ($name == '') {
            $row .= '<td><button type="button" class="btn pull-right btn-info dpr-department-add-item-to-table"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="dpr_department_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }

        $row .= '</tr>';
        return $row;
    }

    public function create_dpr_rmc_row_template($name = '', $challan = '', $grade = '', $structure = '', $quantity = '', $is_edit = false, $item_key = '')
    {
        $row = '';

        $name_challan = 'challan';
        $name_grade = 'grade';
        $name_structure = 'structure';
        $name_quantity = 'quantity';

        if ($name == '') {
            $row .= '<tr class="main">';
            $manual = true;
        } else {
            $manual = false;
            $row .= '<tr class="item"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '">';
            $name_challan = $name . '[challan]';
            $name_grade = $name . '[grade]';
            $name_structure = $name . '[structure]';
            $name_quantity = $name . '[quantity]';
        }
        $get_grade_list = $this->get_progress_report_rmc_grade();
        $row .= '<td class="challan">' . render_input($name_challan, '', $challan) . '</td>';
        $row .= '<td class="grade">' . render_select($name_grade, $get_grade_list, ['id', 'name'], '', $grade, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-width' => '100%']) . '</td>';
        $row .= '<td class="structure">' . render_input($name_structure, '', $structure) . '</td>';
        $row .= '<td class="quantity">' . render_input($name_quantity, '', $quantity) . '</td>';

        if ($name == '') {
            $row .= '<td><button type="button" class="btn pull-right btn-info dpr-rmc-add-item-to-table"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="dpr_rmc_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }

        $row .= '</tr>';
        return $row;
    }

    public function create_dpr_material_row_template($name = '', $challan = '', $supplier = '', $material_description = '', $total = '', $is_edit = false, $item_key = '')
    {
        $row = '';

        $name_challan = 'challan';
        $name_supplier = 'supplier';
        $name_material_description = 'material_description';
        $name_total = 'total';

        if ($name == '') {
            $row .= '<tr class="main">';
            $manual = true;
        } else {
            $manual = false;
            $row .= '<tr class="item"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '">';
            $name_challan = $name . '[challan]';
            $name_supplier = $name . '[supplier]';
            $name_material_description = $name . '[material_description]';
            $name_total = $name . '[total]';
        }

        $row .= '<td class="challan">' . render_input($name_challan, '', $challan) . '</td>';
        $row .= '<td class="supplier">' . render_input($name_supplier, '', $supplier) . '</td>';
        $row .= '<td class="material_description">' . render_input($name_material_description, '', $material_description) . '</td>';
        $row .= '<td class="total">' . render_input($name_total, '', $total) . '</td>';

        if ($name == '') {
            $row .= '<td><button type="button" class="btn pull-right btn-info dpr-material-add-item-to-table"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="dpr_material_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }

        $row .= '</tr>';
        return $row;
    }
    public function get_dpr_department_block_mortar($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_block_form_detail')->result_array();
    }
    public function get_dpr_department_tile_mortar($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_tile_form_detail')->result_array();
    }

    public function get_dpr_department_coupler_mortar($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_coupler_form_detail')->result_array();
    }

    public function get_dpr_department_wires_mortar($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_wires_form_detail')->result_array();
    }


    public function get_dpr_department_council_mortar($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_council_form_detail')->result_array();
    }
}
