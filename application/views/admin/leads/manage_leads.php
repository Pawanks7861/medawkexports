<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'leads'; ?>
<style>
    .show_hide_columns {
        position: absolute;
        z-index: 999;
        left: 223px;
    }

    .export-btn-div {
        position: absolute;
        z-index: 999;
        left: 272px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <a href="#" onclick="init_lead(); return false;"
                        class="btn btn-primary mright5 pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_lead'); ?>
                    </a>
                    <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>"
                            class="btn btn-primary pull-left display-block hidden-xs">
                            <i class="fa-solid fa-upload tw-mr-1"></i>
                            <?php echo _l('import_leads'); ?>
                        </a>
                    <?php } ?>
                    <div class="row">
                        <div class="col-sm-5 ">
                            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                                data-title="<?php echo _l('leads_summary'); ?>" data-placement="top"
                                onclick="slideToggle('.leads-overview'); return false;"><i
                                    class="fa fa-bar-chart"></i></a>
                            <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>"
                                class="btn btn-default mleft5 hidden-xs" data-toggle="tooltip" data-placement="top"
                                data-title="<?php echo $switch_kanban == 1 ? _l('leads_switch_to_kanban') : _l('switch_to_list_view'); ?>">
                                <?php if ($switch_kanban == 1) { ?>
                                    <i class="fa-solid fa-grip-vertical"></i>
                                <?php } else { ?>
                                    <i class="fa-solid fa-table-list"></i>
                                <?php }; ?>
                            </a>
                        </div>

                    </div>
                    <div class="clearfix"></div>
                    <div class="hide leads-overview tw-mt-2 sm:tw-mt-4 tw-mb-4 sm:tw-mb-0">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <?php echo _l('leads_summary'); ?>
                        </h4>
                        <div class="tw-flex tw-flex-wrap tw-flex-col lg:tw-flex-row tw-w-full tw-gap-3 lg:tw-gap-6">
                            <?php
                            foreach ($summary as $status) { ?>
                                <div
                                    class="lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-items-center last:tw-border-r-0">
                                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                                        <?php
                                        if (isset($status['percent'])) {
                                            echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                        } else {
                                            // Is regular status
                                            echo $status['total'];
                                        }
                                        ?>
                                    </span>
                                    <span style="color:<?php echo e($status['color']); ?>"
                                        class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>">
                                        <?php echo e($status['name']); ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>

                <div class="<?php echo $isKanBan ? '' : 'panel_s'; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body'; ?>">

                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="kanban-leads-sort">
                                        <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                                        <a href="#" onclick="leads_kanban_sort('dateadded'); return false"
                                            class="dateadded">
                                            <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_datecreated'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('leadorder');return false;"
                                            class="leadorder">
                                            <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('lastcontact');return false;"
                                            class="lastcontact">
                                            <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                                        </a>
                                    </div>
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row all_ot_filters">
                                    <div class="col-md-2 form-group">
                                        <?php
                                        $project_type_filter = get_module_filter($module_name, 'project');
                                        $project_type_filter_val = !empty($project_type_filter) ? explode(",", $project_type_filter->filter_value) : '';
                                        echo render_select('project[]', $projects, array('id', 'name'), '', $project_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('project'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 form-group">

                                        <?php
                                        $assigned_type_filter = get_module_filter($module_name, 'assigned');
                                        $assigned_type_filter_val = !empty($assigned_type_filter) ? explode(",", $assigned_type_filter->filter_value) : '';
                                        echo render_select('assigned[]', $staff, array('staffid', 'firstname'), '', $assigned_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>

                                    </div>
                                    <div class="col-md-2 form-group">
                                        <?php
                                        $status_type_filter = get_module_filter($module_name, 'status');
                                        $status_type_filter_val = !empty($status_type_filter) ? explode(",", $status_type_filter->filter_value) : '';
                                        echo render_select('status[]', $statuses, array('id', 'name'), '', $status_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);

                                        ?>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <?php
                                        $sources_type_filter = get_module_filter($module_name, 'source');
                                        $sources_type_filter_val = !empty($sources_type_filter) ? explode(",", $sources_type_filter->filter_value) : '';
                                        echo render_select('sources[]', $sources, array('id', 'name'), '', $sources_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Sources'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 form-group">

                                        <?php
                                        $months = [];
                                        foreach (range(1, 12) as $month) {
                                            $months[] = [
                                                'id' => $month,
                                                'name' => _l(date('F', mktime(0, 0, 0, $month, 10))),
                                            ];
                                        }

                                        $month_type_filter = get_module_filter($module_name, 'month');
                                        $month_type_filter_val = !empty($month_type_filter) ? explode(",", $month_type_filter->filter_value) : '';


                                        echo render_select('month[]', $months, array('id', 'name'), '', $month_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Month'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <?php
                                        $duplicate_type_filter = get_module_filter($module_name, 'duplicate');
                                        $duplicate_type_filter_val = !empty($duplicate_type_filter) ? explode(",", $duplicate_type_filter->filter_value) : '';

                                        $duplicate = [
                                            ['id' => '1', 'name' => 'Duplicate'],
                                        ];
                                        echo render_select('duplicate[]', $duplicate, array('id', 'name'), '', $duplicate_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Duplicate'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-1 form-group pull-right">
                                        <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
                                            <?php echo _l('reset_filter'); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="row" id="leads-table">
                                    <div class="col-md-12">
                                        <a href="#" data-toggle="modal" data-table=".table-leads"
                                            data-target="#leads_bulk_actions"
                                            class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                                        <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1"
                                            role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (staff_can('delete',  'leads')) { ?>
                                                            <div class="checkbox checkbox-danger">
                                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                                <label
                                                                    for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                                            </div>
                                                            <hr class="mass_delete_separator" />
                                                        <?php } ?>
                                                        <div id="bulk_change">
                                                            <div class="form-group">
                                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                                    <input type="checkbox" name="leads_bulk_mark_lost"
                                                                        id="leads_bulk_mark_lost" value="1">
                                                                    <label for="leads_bulk_mark_lost">
                                                                        <?php echo _l('lead_mark_as_lost'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <?php echo render_select('move_to_status_leads_bulk', $statuses, ['id', 'name'], 'ticket_single_change_status'); ?>
                                                            <?php
                                                            echo render_select('move_to_source_leads_bulk', $sources, ['id', 'name'], 'lead_source');
                                                            echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                                            echo render_select('assign_to_leads_bulk', $staff, ['staffid', ['firstname', 'lastname']], 'leads_dt_assigned');
                                                            ?>
                                                            <div class="form-group">
                                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                                <input type="text" class="tagsinput" id="tags_bulk"
                                                                    name="tags_bulk" value="" data-role="tagsinput">
                                                            </div>
                                                            <hr />
                                                            <div class="form-group no-mbot">
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                        id="leads_bulk_public" value="public">
                                                                    <label for="leads_bulk_public">
                                                                        <?php echo _l('lead_public'); ?>
                                                                    </label>
                                                                </div>
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                        id="leads_bulk_private" value="private">
                                                                    <label for="leads_bulk_private">
                                                                        <?php echo _l('private'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                            data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                        <a href="#" class="btn btn-primary"
                                                            onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->
                                        <div class="btn-group show_hide_columns" id="show_hide_columns">
                                            <!-- Settings Icon -->
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 4px 7px;">
                                                <i class="fa fa-cog"></i> <?php  ?> <span class="caret"></span>
                                            </button>
                                            <!-- Dropdown Menu with Checkboxes -->
                                            <div class="dropdown-menu" style="padding: 10px; min-width: 250px;">
                                                <!-- Select All / Deselect All -->
                                                <div>
                                                    <input type="checkbox" id="select-all-columns"> <strong><?php echo _l('select_all'); ?></strong>
                                                </div>
                                                <hr>
                                                <!-- Column Checkboxes -->
                                                <?php
                                                $columns = [
                                                    'checkbox',
                                                    'the_number_sign',
                                                    'leads_dt_name',
                                                    'leads_dt_phonenumber',
                                                    'Alt Phonenumber',
                                                    'Project',
                                                    'leads_dt_assigned',
                                                    'leads_dt_status',
                                                    'leads_source',
                                                    'leads_dt_datecreated',
                                                    'leads_dt_last_contact',
                                                    'Broker Name',
                                                    'Broker Contact',
                                                    'Duplicate',
                                                    'Month',
                                                    'tags',
                                                    'Whatsapp Enable'
                                                ];
                                                ?>
                                                <div>
                                                    <?php foreach ($columns as $key => $label): ?>
                                                        <input type="checkbox" class="toggle-column" name="toggle_column[<?php echo $label; ?>]" value="<?php echo $key; ?>" checked>
                                                        <?php echo _l($label); ?><br>
                                                    <?php endforeach; ?>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="btn-group export-btn-div" id="export-btn-div">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 4px 7px;">
                                                <i class="fa fa-download"></i> <?php echo _l('Export'); ?> <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu" style="padding: 10px;min-width: 94px;">
                                                <a class="dropdown-item export-btn" href="<?php echo admin_url('leads/export_leads_pdf'); ?>" data-type="pdf">
                                                    <i class="fa fa-file-pdf text-danger"></i> PDF
                                                </a><br>
                                                <a class="dropdown-item export-btn" href="<?php echo admin_url('leads/export_leads_excel'); ?>" data-type="excel">
                                                    <i class="fa fa-file-excel text-success"></i> Excel
                                                </a>
                                            </div>
                                        </div>
                                        <?php

                                        $table_data  = [];
                                        $_table_data = [
                                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                            [
                                                'name'     => _l('the_number_sign'),
                                                'th_attrs' => ['class' => 'toggleable', 'id' => 'th-number'],
                                            ],
                                            [
                                                'name'     => _l('leads_dt_name'),
                                                'th_attrs' => ['class' => 'toggleable', 'id' => 'th-name'],
                                            ],
                                        ];
                                        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                            $_table_data[] = [
                                                'name'     => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                                'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                                            ];
                                        }

                                        // $_table_data[] = [
                                        //     'name'     => _l('leads_dt_email'),
                                        //     'th_attrs' => ['class' => 'toggleable', 'id' => 'th-email'],
                                        // ];
                                        $_table_data[] = [
                                            'name'     => _l('leads_dt_phonenumber'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-phone'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('Alt Phonenumber'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-alt-phone'],
                                        ];
                                        // $_table_data[] = [
                                        //     'name'     => 'Budget',
                                        //     'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-value'],
                                        // ];

                                        $_table_data[] = [
                                            'name'     => 'Project',
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-project'],
                                        ];

                                        $_table_data[] = [
                                            'name'     => _l('leads_dt_assigned'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-assigned'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('leads_dt_status'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-status'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('leads_source'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-source'],
                                        ];

                                        $_table_data[] = [
                                            'name'     => _l('leads_dt_datecreated'),
                                            'th_attrs' => ['class' => 'date-created toggleable', 'id' => 'th-date-created'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('Budget'),
                                            'th_attrs' => ['class' => 'date-lead_value toggleable', 'id' => 'th-lead-value'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('Call Time'),
                                            'th_attrs' => ['class' => 'date-call_time toggleable', 'id' => 'th-call-time'],
                                        ];
                                        // $_table_data[] = [
                                        //     'name'     => _l('leads_dt_last_contact'),
                                        //     'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last-contact'],
                                        // ];
                                        $_table_data[] = [
                                            'name'     => 'Broker Name',
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-broker-name'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => 'Broker Contact',
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-broker-contact-details'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('Duplicate'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-double-entry-alert'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('Month'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-month'],
                                        ];
                                        $_table_data[] = [
                                            'name'     => _l('tags'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-tags'],
                                        ];
                                        foreach ($_table_data as $_t) {
                                            array_push($table_data, $_t);
                                        }
                                        $custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);
                                        foreach ($custom_fields as $field) {
                                            array_push($table_data, [
                                                'name'     => $field['name'],
                                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                            ]);
                                        }
                                        $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                                        ?>
                                        <div class="panel-table-full">
                                            <?php
                                            render_datatable(
                                                $table_data,
                                                'leads',
                                                ['customizable-table number-index-2'],
                                                [
                                                    'id'                         => 'leads',
                                                    'data-last-order-identifier' => 'leads',
                                                    'data-default-order'         => get_table_last_order('leads'),
                                                ]
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="hidden-columns-table-leads" type="text/json">
    <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<script>
    var table_rec_leads;
    (function($) {
        table_rec_leads = $('.table-leads');

        var Params = {
            "project": "[name='project[]']",
            "assigned": "[name='assigned[]']",
            "status": "[name='status[]']",
            "source": "[name='sources[]']",
            "month": "[name='month[]']",
            "duplicate": "[name='duplicate[]']",
        };

        initDataTable('.table-leads', admin_url + 'leads/table_leads_details', [], [], Params, [9, 'desc']);


        $.each(Params, function(i, obj) {
            $('select' + obj).on('change', function() {
                table_rec_leads.DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
            });
        });

        $(document).on('click', '.reset_all_ot_filters', function() {
            var filterArea = $('.all_ot_filters');
            filterArea.find('input').val("");
            filterArea.find('select').selectpicker("val", "");
            table_rec_leads.DataTable().ajax.reload().columns.adjust().responsive.recalc();
        });

    })(jQuery);
</script>
<script>
    var openLeadID = '<?php echo e($leadid); ?>';
    $(function() {
        leads_kanban();
        $('#leads_bulk_mark_lost').on('change', function() {
            $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
            $('#move_to_status_leads_bulk').selectpicker('refresh')
        });
        $('#move_to_status_leads_bulk').on('change', function() {
            if ($(this).selectpicker('val') != '') {
                $('#leads_bulk_mark_lost').prop('disabled', true);
                $('#leads_bulk_mark_lost').prop('checked', false);
            } else {
                $('#leads_bulk_mark_lost').prop('disabled', false);
            }
        });

    });
</script>
<script>
    $(document).ready(function() {
        var table = $('.customizable-table').DataTable();
        var storageKey = 'table_column_visibility';

        // Load saved column visibility settings from localStorage
        var savedSettings = JSON.parse(localStorage.getItem(storageKey)) || {};

        // Apply saved settings
        table.columns().every(function(index) {
            var column = this;
            var isVisible = savedSettings[index] !== undefined ? savedSettings[index] : column.visible();
            column.visible(isVisible);
            $('.toggle-column[value="' + index + '"]').prop('checked', isVisible);
        });

        // Handle "Select All" checkbox
        $('#select-all-columns').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.toggle-column').prop('checked', isChecked).trigger('change');
        });

        // Handle individual column visibility toggling
        $('.toggle-column').on('change', function() {
            var columnIndex = $(this).val();
            var column = table.column(columnIndex);
            var isVisible = $(this).is(':checked');

            column.visible(isVisible);

            // Update local storage
            savedSettings[columnIndex] = isVisible;
            localStorage.setItem(storageKey, JSON.stringify(savedSettings));

            // Sync "Select All" checkbox state
            var allChecked = $('.toggle-column').length === $('.toggle-column:checked').length;
            $('#select-all-columns').prop('checked', allChecked);
        });

        // Sync "Select All" checkbox state on page load
        var allChecked = $('.toggle-column').length === $('.toggle-column:checked').length;
        $('#select-all-columns').prop('checked', allChecked);

        // Prevent dropdown from closing when clicking inside
        $('.dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
</body>

</html>

<script>
    $('.buttons-collection').hide();
    <?php if (!$isadmin): ?>
        $('.export-btn-div').hide();
    <?php endif; ?>
</script>