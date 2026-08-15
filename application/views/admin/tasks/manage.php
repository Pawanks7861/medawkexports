<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'tasks'; ?>
<div id="wrapper">
    <div class="content">
        <div class="row _buttons tw-mb-2 sm:tw-mb-4">
            <div class="col-md-8">
                <?php if (staff_can('create',  'tasks')) { ?>
                    <a href="#" onclick="new_task(<?php if ($this->input->get('project_id')) {
                                                        echo "'" . admin_url('tasks/task?rel_id=' . $this->input->get('project_id') . '&rel_type=project') . "'";
                                                    } ?>); return false;" class="btn btn-primary pull-left new">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('New Follow Up'); ?>
                    </a>
                <?php } ?>
                <a
                    href="<?php echo admin_url(!$this->input->get('project_id') ? ('tasks/switch_kanban/' . $switch_kanban) : ('projects/view/' . $this->input->get('project_id') . '?group=project_tasks')); ?>" class="btn btn-default mleft10 pull-left hidden-xs" data-toggle="tooltip"
                    data-placement="top"
                    data-title="<?php echo $switch_kanban == 1 ? _l('switch_to_list_view') : _l('leads_switch_to_kanban'); ?>">
                    <?php if ($switch_kanban == 1) { ?>
                        <i class="fa-solid fa-table-list"></i>
                    <?php } else { ?>
                        <i class="fa-solid fa-grip-vertical"></i>
                    <?php }; ?>
                </a>
            </div>
            <div class="col-md-4">
                <?php if ($this->session->has_userdata('tasks_kanban_view') && $this->session->userdata('tasks_kanban_view') == 'true') { ?>
                    <div data-toggle="tooltip" data-placement="top" data-title="<?php echo _l('search_by_tags'); ?>">
                        <?php echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'tasks_kanban();', 'placeholder' => _l('search_tasks')], [], 'no-margin') ?>
                    </div>
                <?php } else {
                    if (is_admin()) { ?>
                        <a href="<?php echo admin_url('tasks/detailed_overview'); ?>"
                            class="btn btn-success pull-right mright5"><?php echo _l('Follow Up Overview'); ?></a>
                    <?php }
                    ?>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                if ($this->session->has_userdata('tasks_kanban_view') && $this->session->userdata('tasks_kanban_view') == 'true') { ?>
                    <div class="kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                        <div class="row">
                            <div id="kanban-params">
                                <?php echo form_hidden('project_id', $this->input->get('project_id')); ?>
                            </div>
                            <div class="container-fluid">
                                <div id="kan-ban"></div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>

                    <div class="row all_ot_filters">
                        <div class="col-md-2 form-group">
                            <?php
                            $lead_status_type_filter = get_module_filter($module_name, 'lead_status');
                            $lead_status_type_filter_val = !empty($lead_status_type_filter) ? explode(",", $lead_status_type_filter->filter_value) : '';
                            echo render_select('lead_status[]', $lead_status, array('id', 'name'), '', $lead_status_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('lead_status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                            ?>
                        </div>
                        <div class="col-md-2 form-group">
                            <?php
                            $task_status_type_filter = get_module_filter($module_name, 'task_status');
                            $task_status_type_filter_val = !empty($task_status_type_filter) ? explode(",", $task_status_type_filter->filter_value) : '';

                            echo render_select('task_status[]', $task_statuses, array('id', 'name'), '', $task_status_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                            ?>
                        </div>
                        <div class="col-md-2 form-group">
                            <?php
                            // Get the filter value if it exists
                            $task_assignees_type_filter = get_module_filter($module_name, 'task_assignees');
                            if (!empty($task_assignees_type_filter) && !empty($task_assignees_type_filter->filter_value)) {
                                $task_assignees_type_filter_val = !empty($task_assignees_type_filter) ? explode(",", $task_assignees_type_filter->filter_value) : '';
                            } else {
                                $staff_id = get_staff_user_id();
                                $task_assignees_type_filter_val = explode(",", $staff_id);
                            }

                            // Get staff list based on user role
                            $CI = &get_instance();

                            if (is_admin()) {
                                // Admin: Get all staff
                                $CI->db->select('staffid, firstname, lastname, CONCAT(firstname, " ", lastname) as full_name');
                                $CI->db->order_by('firstname', 'ASC');
                                $staff = $CI->db->get(db_prefix() . 'staff')->result();
                            } else {
                                // Non-admin: Get only the logged-in staff
                                $staff_id = get_staff_user_id();
                                $CI->db->select('staffid, firstname, lastname, CONCAT(firstname, " ", lastname) as full_name');
                                $CI->db->where('staffid', $staff_id);
                                $staff = $CI->db->get(db_prefix() . 'staff')->result();
                            }

                            // For the render_select, we need to map the fields properly
                            // Since we selected 'full_name', we need to use it for display
                            $staff_options = array();
                            foreach ($staff as $staff_member) {
                                $staff_options[] = array(
                                    'staffid' => $staff_member->staffid,
                                    'full_name' => $staff_member->full_name
                                );
                            }

                            // Use the staff options array
                            echo render_select('task_assignees[]', $staff_options, array('staffid', 'full_name'), '', $task_assignees_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task Assignees'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                            ?>
                        </div>
                        <div class="col-md-2 form-group">
                            <?php
                            $task_priority_type_filter = get_module_filter($module_name, 'task_priority');
                            $task_priority_type_filter_val = !empty($task_priority_type_filter) ? explode(",", $task_priority_type_filter->filter_value) : '';

                            echo render_select('task_priority[]', get_tasks_priorities(), array('id', 'name'), '', $task_priority_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task Priority'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                            ?>
                        </div>

                        <div class="col-md-2 form-group">
                            <?php
                            $period = [
                                ['id' => 'today', 'name' => 'Today'],
                                ['id' => '3_day', 'name' => '3 Days'],
                                ['id' => '7_day', 'name' => '7 Days'],
                                ['id' => 'this_week', 'name' => 'This Week'],
                            ];
                            $period_type_filter = get_module_filter($module_name, 'period');

                            if(!empty($period_type_filter) && !empty($period_type_filter->filter_value)){
                                $period_type_filter_val = !empty($period_type_filter) ? explode(",", $period_type_filter->filter_value) : '';
                            }else{
                                $period_type_filter_val = ['today'];
                            }
                            


                            


                            echo render_select('period[]', $period, array('id', 'name'), '', $period_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Period'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                            ?>
                        </div>
                        <div class="col-md-1 form-group">
                            <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
                                <?php echo _l('reset_filter'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="panel_s">
                        <div class="panel-body">
                            <?php $this->load->view('admin/tasks/_summary', ['table' => '.table-tasks']); ?>
                            <a href="#" data-toggle="modal" data-target="#tasks_bulk_actions"
                                class="hide bulk-actions-btn table-btn"
                                data-table=".table-tasks"><?php echo _l('bulk_actions'); ?></a>
                            <div class="panel-table-full">
                                <?php $this->load->view('admin/tasks/_table', ['bulk_actions' => true]); ?>
                            </div>
                            <?php $this->load->view('admin/tasks/_bulk_actions'); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table_rec_task;
    (function($) {
        table_rec_task = $('.table-tasks');

        var Params = {
            "lead_status": "[name='lead_status[]']",
            "task_status": "[name='task_status[]']",
            "task_assignees": "[name='task_assignees[]']",
            "task_priority": "[name='task_priority[]']",
            "period": "[name='period[]']",
        };

        initDataTable('.table-tasks', admin_url + 'tasks/table_tasks_details', [], [], Params, [4, 'asc']);


        $.each(Params, function(i, obj) {
            $('select' + obj).on('change', function() {
                table_rec_task.DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
            });
        });

        $(document).on('click', '.reset_all_ot_filters', function() {
            var filterArea = $('.all_ot_filters');
            filterArea.find('input').val("");
            filterArea.find('select').selectpicker("val", "");
            table_rec_task.DataTable().ajax.reload().columns.adjust().responsive.recalc();
        });

    })(jQuery);
</script>
<script>
    taskid = '<?php echo e($taskid); ?>';
    $(function() {
        tasks_kanban();
    });
</script>
</body>

</html>