<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .bootstrap-select.form-control.input-group-btn {
        width: 18%;
    }

    .invalid-feedback {
        color: red;
        position: absolute;
        left: 0;
        top: 32px;
    }
</style>

<div class="lead-wrapper<?php echo !empty($openEdit) ? ' open-edit' : ''; ?><?php echo (isset($lead) && ($lead->junk == 1 || $lead->lost == 1)) ? ' lead-is-junk-or-lost' : ''; ?>">

    <?php if (isset($lead)) { ?>
        <div class="btn-group pull-right mleft5" id="lead-more-btn">
            <a href="#" class="btn btn-default dropdown-toggle lead-top-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo _l('more'); ?> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-left" id="lead-more-dropdown">
                <?php if ($lead->junk == 0) {
                    if ($lead->lost == 0 && (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0)) { ?>
                        <li>
                            <a href="#" onclick="lead_mark_as_lost(<?php echo e($lead->id); ?>); return false;">
                                <i class="fa fa-mars"></i> <?php echo _l('lead_mark_as_lost'); ?>
                            </a>
                        </li>
                    <?php } elseif ($lead->lost == 1) { ?>
                        <li>
                            <a href="#" onclick="lead_unmark_as_lost(<?php echo e($lead->id); ?>); return false;">
                                <i class="fa fa-smile-o"></i> <?php echo _l('lead_unmark_as_lost'); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>

                <?php if ($lead->lost == 0) {
                    if ($lead->junk == 0 && (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0)) { ?>
                        <li>
                            <a href="#" onclick="lead_mark_as_junk(<?php echo e($lead->id); ?>); return false;">
                                <i class="fa fa-times"></i> <?php echo _l('lead_mark_as_junk'); ?>
                            </a>
                        </li>
                    <?php } elseif ($lead->junk == 1) { ?>
                        <li>
                            <a href="#" onclick="lead_unmark_as_junk(<?php echo e($lead->id); ?>); return false;">
                                <i class="fa fa-smile-o"></i> <?php echo _l('lead_unmark_as_junk'); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>

                <?php if ((staff_can('delete', 'leads') && $lead_locked == false) || is_admin()) { ?>
                    <li>
                        <a href="<?php echo admin_url('leads/delete/' . $lead->id); ?>" class="text-danger delete-text _delete" data-toggle="tooltip" title="">
                            <i class="fa fa-remove"></i> <?php echo _l('lead_edit_delete_tooltip'); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="pull-right mleft5">
            <a data-toggle="tooltip" class="btn btn-default lead-print-btn lead-top-btn lead-view"
                onclick="print_lead_information(); return false;" data-placement="top" title="<?php echo _l('print'); ?>" href="#">
                <i class="fa fa-print"></i>
            </a>
        </div>

        <div class="mleft5 pull-right<?php echo $lead_locked == true ? ' hide' : ''; ?>">
            <a href="#" lead-edit data-toggle="tooltip" id="lead-edit-square" data-title="<?php echo _l('edit'); ?>"
                class="btn btn-default lead-top-btn">
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div>

        <?php
        $client                                 = false;
        $convert_to_client_tooltip_email_exists = '';
        if (total_rows(db_prefix() . 'contacts', ['email' => $lead->email]) > 0 && total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0) {
            $convert_to_client_tooltip_email_exists = _l('lead_email_already_exists');
            $text = _l('lead_convert_to_client');
        } elseif (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id])) {
            $client = true;
        } else {
            $text = _l('lead_convert_to_client');
        }
        ?>

        <?php if ($lead_locked == false) { ?>
            <div class="lead-edit<?php if (isset($lead)) {
                                        echo ' hide';
                                    } ?>">
                <button type="button" class="btn btn-primary pull-right lead-top-btn lead-save-btn"
                    onclick="document.getElementById('lead-form-submit').click();" form="lead_form">
                    <?php echo _l('submit'); ?>
                </button>
            </div>
        <?php } ?>

        <?php if ($client && (staff_can('view', 'customers') || is_customer_admin(get_client_id_by_lead_id($lead->id)))) { ?>
            <a data-toggle="tooltip" class="btn btn-success pull-right lead-top-btn lead-view" data-placement="top"
                title="<?php echo _l('lead_converted_edit_client_profile'); ?>"
                href="<?php echo admin_url('clients/client/' . get_client_id_by_lead_id($lead->id)); ?>">
                <i class="fa-regular fa-user"></i>
            </a>
        <?php } ?>

        <?php if (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0) { ?>
            <a href="#" data-toggle="tooltip" data-title="<?php echo e($convert_to_client_tooltip_email_exists); ?>"
                class="btn btn-success pull-right lead-convert-to-customer lead-top-btn lead-view"
                onclick="convert_lead_to_customer(<?php echo e($lead->id); ?>); return false;">
                <i class="fa-regular fa-user"></i> <?php echo e($text); ?>
            </a>
        <?php } ?>
    <?php } ?>

    <div class="clearfix no-margin"></div>

    <?php if (isset($lead)) { ?>
        <div class="row mbot15" style="margin-top:12px;">
            <hr class="no-margin" />
        </div>

        <div class="alert alert-warning hide mtop20" role="alert" id="lead_proposal_warning">
            <?php echo _l('proposal_warning_email_change', [_l('lead_lowercase'), _l('lead_lowercase'), _l('lead_lowercase')]); ?>
            <hr />
            <a href="#" onclick="update_all_proposal_emails_linked_to_lead(<?php echo e($lead->id); ?>); return false;">
                <?php echo _l('update_proposal_email_yes'); ?>
            </a>
            <br />
            <a href="#" onclick="init_lead_modal_data(<?php echo e($lead->id); ?>); return false;">
                <?php echo _l('update_proposal_email_no'); ?>
            </a>
        </div>
    <?php } ?>

    <?php echo form_open((isset($lead) ? admin_url('leads/lead/' . $lead->id) : admin_url('leads/lead')), ['id' => 'lead_form']); ?>
    <div class="row">
        <div class="lead-view<?php if (!isset($lead)) {
                                    echo ' hide';
                                } ?>" id="leadViewWrapper">
            <div class="col-md-4 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4><?php echo _l('lead_info'); ?></h4>
                </div>
                <dl>
                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_add_edit_name'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 lead-name">
                        <?php echo (isset($lead) && $lead->name != '' ? e($lead->name) : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500">Project</dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php
                        if (isset($lead) && !empty($lead->projects)) {
                            $projects = get_projects($lead->projects);
                            echo $projects ? e($projects) : '-';
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_add_edit_email'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->email != '' ? '<a href="mailto:' . e($lead->email) . '">' . e($lead->email) . '</a>' : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_add_edit_phonenumber'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php
                        if (isset($lead) && $lead->phonenumber != '') {
                            $phone = trim($lead->phonenumber);
                            if (strpos($phone, '+91') !== 0 && strpos($phone, '+1') !== 0) {
                                if (strpos($phone, '1') === 0) {
                                    $phone = '+1' . $phone;
                                } else {
                                    $phone = '+91' . $phone;
                                }
                            }
                            echo '<a href="tel:' . e($phone) . '">' . e($phone) . '</a>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_value'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->lead_value != 0 ? $lead->lead_value : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('Broker Name'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->broker != '' ? process_text_content_for_display($lead->broker) : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('Firm Name'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->firm != '' ? process_text_content_for_display($lead->firm) : '-') ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('Call Time'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->call_time != '' ? $lead->call_time : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('Broker Contact Details'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->contact_details != '' ? $lead->contact_details : '-') ?>
                    </dd>
                </dl>
            </div>

            <div class="col-md-4 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4><?php echo _l('lead_general_info'); ?></h4>
                </div>
                <dl>
                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500 no-mtop"><?php echo _l('lead_add_edit_status'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-2 mbot15">
                        <?php
                        if (isset($lead)) {
                            echo $lead->status_name != '' ? ('<span class="lead-status-' . e($lead->status) . ' label' . (empty($lead->color) ? ' label-default' : '') . '" style="color:' . e($lead->color) . ';border:1px solid ' . adjust_hex_brightness($lead->color, 0.4) . ';background: ' . adjust_hex_brightness($lead->color, 0.04) . ';">' . e($lead->status_name) . '</span>') : '-';
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_add_edit_source'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?php echo (isset($lead) && $lead->source_name != '' ? e($lead->source_name) : '-') ?>
                    </dd>

                    <?php if (!is_language_disabled()) { ?>
                        <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('localization_default_language'); ?></dt>
                        <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                            <?php echo (isset($lead) && $lead->default_language != '' ? e(ucfirst($lead->default_language)) : _l('system_default_string')) ?>
                        </dd>
                    <?php } ?>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_add_edit_assigned'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?php echo (isset($lead) && $lead->assigned != 0 ? e(get_staff_full_name($lead->assigned)) : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('Alt Phonenumber'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php
                        if (isset($lead) && $lead->alt_phonenumber != '') {
                            $alt_phonenumber = trim($lead->alt_phonenumber);
                            if (strpos($alt_phonenumber, '+91') !== 0 && strpos($alt_phonenumber, '+1') !== 0) {
                                if (strpos($alt_phonenumber, '1') === 0) {
                                    $alt_phonenumber = '+1' . $alt_phonenumber;
                                } else {
                                    $alt_phonenumber = '+91' . $alt_phonenumber;
                                }
                            }
                            echo '<a href="tel:' . e($alt_phonenumber) . '">' . e($alt_phonenumber) . '</a>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('tags'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot10">
                        <?php
                        if (isset($lead)) {
                            $tags = get_tags_in($lead->id, 'lead');
                            if (count($tags) > 0) {
                                echo render_tags($tags);
                                echo '<div class="clearfix"></div>';
                            } else {
                                echo '-';
                            }
                        }
                        ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('leads_dt_datecreated'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->dateadded != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . e(_dt($lead->dateadded)) . '">' . e(time_ago($lead->dateadded)) . '</span>' : '-') ?>
                    </dd>

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('leads_dt_last_contact'); ?></dt>
                    <!-- <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo (isset($lead) && $lead->lastcontact != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . e(_dt($lead->lastcontact)) . '">' . e(time_ago($lead->lastcontact)) . '</span>' : '-') ?>
                    </dd> -->

                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_public'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?php if (isset($lead)) {
                            echo ($lead->is_public == 1) ? _l('lead_is_public_yes') : _l('lead_is_public_no');
                        } else {
                            echo '-';
                        } ?>
                    </dd>

                    <?php if (isset($lead) && $lead->from_form_id != 0) { ?>
                        <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('web_to_lead_form'); ?></dt>
                        <dd class="tw-text-neutral-900 tw-mt-1 mbot15"><?php echo e($lead->form_data->name); ?></dd>
                    <?php } ?>
                </dl>
            </div>

            <div class="col-md-4 col-xs-12 lead-information-col">
                <?php if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'leads', 'active' => 1]) > 0 && isset($lead)) { ?>
                    <div class="lead-info-heading">
                        <h4><?php echo _l('custom_fields'); ?></h4>
                    </div>
                    <dl>
                        <?php
                        $custom_fields = get_custom_fields('leads');
                        foreach ($custom_fields as $field) {
                            $value = get_custom_field_value($lead->id, $field['id'], 'leads'); ?>
                            <dt class="lead-field-heading tw-font-medium tw-text-neutral-500 no-mtop"><?php echo e($field['name']); ?></dt>
                            <dd class="tw-text-neutral-900 tw-mt-1 tw-break-words"><?php echo ($value != '' ? $value : '-') ?></dd>
                        <?php } ?>
                    </dl>
                <?php } ?>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-12">
                <dl>
                    <dt class="lead-field-heading tw-font-medium tw-text-neutral-500"><?php echo _l('lead_description'); ?></dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?php echo process_text_content_for_display((isset($lead) && $lead->description != '' ? $lead->description : '-')); ?>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="lead-edit<?php if (isset($lead)) {
                                    echo ' hide';
                                } ?>">
            <div class="col-md-4">
                <?php
                if (isset($lead)) {
                    $selected = $lead->status;
                } else {
                    $selected = 18;
                }
                echo render_leads_status_select($statuses, $selected, 'lead_add_edit_status');
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $selected = (isset($lead) ? $lead->source : get_option('leads_default_source'));
                echo render_leads_source_select($sources, $selected, 'lead_add_edit_source');
                ?>
            </div>
            <?php
            if (is_admin()) { ?>
                <div class="col-md-4">
                    <?php
                    $assigned_attrs = [];
                    $selected       = (isset($lead) ? $lead->assigned : get_staff_user_id());
                    if (
                        isset($lead)
                        && $lead->assigned == get_staff_user_id()
                        && $lead->addedfrom != get_staff_user_id()
                        && !is_admin($lead->assigned)
                        && staff_cant('view', 'leads')
                    ) {
                        $assigned_attrs['disabled'] = true;
                    }
                    echo render_select('assigned', $members, ['staffid', ['firstname', 'lastname']], 'lead_add_edit_assigned', $selected, $assigned_attrs);
                    ?>
                </div>
            <?php }
            ?>


            <div class="clearfix"></div>
            <hr class="mtop5 mbot10" />

            <div class="col-md-12">
                <div class="form-group no-mbot" id="inputTagsWrapper">
                    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                    <input type="text" class="tagsinput" id="tags" name="tags"
                        value="<?php echo (isset($lead) ? prep_tags_input(get_tags_in($lead->id, 'lead')) : ''); ?>"
                        data-role="tagsinput">
                </div>
            </div>

            <div class="clearfix"></div>
            <hr class="no-mtop mbot15" />

            <div class="col-md-6">
                <?php $value = (isset($lead) ? $lead->name : ''); ?>
                <?php echo render_input('name', 'lead_add_edit_name', $value); ?>

                <?php
                $phone = isset($lead) ? $lead->phonenumber : '';
                $selected_country_code = '+91'; // default country code
                $local_phone = $phone;

                // Define a list of country codes you expect (you can add more as needed)
                $country_codes = $countryCodes = [
                    '+93',
                    '+355',
                    '+213',
                    '+1 684',
                    '+376',
                    '+244',
                    '+1 264',
                    '+672',
                    '+1268',
                    '+54',
                    '+374',
                    '+297',
                    '+61',
                    '+43',
                    '+994',
                    '+1 242',
                    '+973',
                    '+880',
                    '+1 246',
                    '+375',
                    '+32',
                    '+501',
                    '+229',
                    '+1 441',
                    '+975',
                    '+591',
                    '+387',
                    '+267',
                    '+55',
                    '+55',
                    '+246',
                    '+673',
                    '+359',
                    '+226',
                    '+257',
                    '+855',
                    '+237',
                    '+1',
                    '+238',
                    '+1345',
                    '+236',
                    '+235',
                    '+56',
                    '+86',
                    '+61',
                    '+61',
                    '+57',
                    '+269',
                    '+242',
                    '+243',
                    '+682',
                    '+506',
                    '+225',
                    '+385',
                    '+53',
                    '+357',
                    '+420',
                    '+45',
                    '+253',
                    '+1 767',
                    '+1 849',
                    '+593',
                    '+20',
                    '+503',
                    '+240',
                    '+291',
                    '+372',
                    '+251',
                    '+500',
                    '+298',
                    '+679',
                    '+358',
                    '+33',
                    '+689',
                    '+262',
                    '+241',
                    '+220',
                    '+995',
                    '+49',
                    '+233',
                    '+350',
                    '+30',
                    '+299',
                    '+1 473',
                    '+590',
                    '+1 671',
                    '+502',
                    '+44',
                    '+224',
                    '+245',
                    '+592',
                    '+509',
                    '+672',
                    '+379',
                    '+504',
                    '+852',
                    '+36',
                    '+354',
                    '+91',
                    '+62',
                    '+98',
                    '+964',
                    '+353',
                    '+44',
                    '+972',
                    '+39',
                    '+1 876',
                    '+81',
                    '+44',
                    '+962',
                    '+7',
                    '+254',
                    '+686',
                    '+850',
                    '+82',
                    '+965',
                    '+996',
                    '+856',
                    '+371',
                    '+961',
                    '+266',
                    '+231',
                    '+218',
                    '+423',
                    '+370',
                    '+352',
                    '+853',
                    '+389',
                    '+261',
                    '+265',
                    '+60',
                    '+960',
                    '+223',
                    '+356',
                    '+692',
                    '+596',
                    '+222',
                    '+230',
                    '+262',
                    '+52',
                    '+691',
                    '+373',
                    '+377',
                    '+976',
                    '+382',
                    '+1664',
                    '+212',
                    '+258',
                    '+95',
                    '+264',
                    '+674',
                    '+977',
                    '+31',
                    '+599',
                    '+687',
                    '+64',
                    '+505',
                    '+227',
                    '+234',
                    '+683',
                    '+672',
                    '+1 670',
                    '+47',
                    '+968',
                    '+92',
                    '+680',
                    '+970',
                    '+507',
                    '+675',
                    '+595',
                    '+51',
                    '+63',
                    '+870',
                    '+48',
                    '+351',
                    '+1 939',
                    '+974',
                    '+262',
                    '+40',
                    '+7',
                    '+250',
                    '+290',
                    '+1 869',
                    '+1 758',
                    '+508',
                    '+1 784',
                    '+685',
                    '+378',
                    '+239',
                    '+966',
                    '+221',
                    '+381',
                    '+248',
                    '+232',
                    '+65',
                    '+421',
                    '+386',
                    '+677',
                    '+252',
                    '+27',
                    '+500',
                    '+34',
                    '+94',
                    '+249',
                    '+597',
                    '+47',
                    '+268',
                    '+46',
                    '+41',
                    '+963',
                    '+886',
                    '+992',
                    '+255',
                    '+66',
                    '+670',
                    '+228',
                    '+690',
                    '+676',
                    '+1 868',
                    '+216',
                    '+90',
                    '+993',
                    '+1 649',
                    '+688',
                    '+256',
                    '+380',
                    '+971',
                    '+44',
                    '+1',
                    '+1581',
                    '+598',
                    '+998',
                    '+678',
                    '+58',
                    '+84',
                    '+1 284',
                    '+1 340',
                    '+681',
                    '+732',
                    '+967',
                    '+260',
                    '+263'
                ];

                // To ensure we match the longer codes first (so +91 is not confused with +9...),
                usort($country_codes, function ($a, $b) {
                    return strlen($b) - strlen($a);
                });

                // Check if the phone number starts with any of the known country codes
                foreach ($country_codes as $code) {
                    if (strpos($phone, $code) === 0) {
                        $selected_country_code = $code;
                        $local_phone = substr($phone, strlen($code));
                        break;
                    }
                }
                ?>

                <div class="form-group">
                    <label for="phonenumber"><?php echo _l('lead_add_edit_phonenumber'); ?></label>
                    <div class="input-group" style="width: 100%;">
                        <select id="countryCode" name="country_code" class="form-control selectpicker" data-live-search="true" style="max-width: 100px;">
                            <!-- The country code options are populated via your API -->
                            <option value="+91" selected>+91</option> <!-- Default to India -->
                        </select>

                        <input type="text" id="phonenumber" name="phonenumber" data-id="<?= isset($lead) ?? $lead->phonenumber ?>" class="form-control"
                            value="<?php echo e($local_phone); ?>">
                    </div>
                </div>



                <?php if ((isset($lead) && empty($lead->website)) || !isset($lead)) {
                    // $value = (isset($lead) ? $lead->website : '');
                    // echo render_input('website', 'lead_website', $value);
                } else { ?>
                    <!-- <div class="form-group">
                        <label for="website"><?php echo _l('lead_website'); ?></label>
                        <div class="input-group">
                            <input type="text" name="website" id="website" value="<?php echo e($lead->website); ?>"
                                class="form-control">
                            <div class="input-group-addon">
                                <span>
                                    <a href="<?php echo e(maybe_add_http($lead->website)); ?>" target="_blank" tabindex="-1">
                                        <i class="fa fa-globe"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div> -->
                <?php }

                ?>


                <div class="form-group">
                    <label for="lead_value"><?php echo _l('lead_value'); ?></label>
                    <div class="input-group" data-toggle="tooltip" title="<?php echo _l('lead_value_tooltip'); ?>">
                        <input type="text" class="form-control" name="lead_value" value="<?php if (isset($lead)) {
                                                                                                echo $lead->lead_value;
                                                                                            } ?>">
                        <div class="input-group-addon">
                            <?php echo e($base_currency->symbol); ?>
                        </div>
                    </div>
                    </label>
                </div>
                <?php $value = (isset($lead) ? $lead->broker : ''); ?>
                <?php echo render_input('broker', 'Broker Name', $value); ?>
                <?php $value = (isset($lead) ? $lead->contact_details : ''); ?>
                <?php echo render_input('contact_details', 'Broker Contact Details', $value, 'number'); ?>
                <!-- <?php $value = (isset($lead) ? $lead->company : ''); ?>
                <?php echo render_input('company', 'lead_company', $value); ?> -->
            </div>

            <div class="col-md-6">
                <?php
                $projects = get_projects();
                $selected = (isset($lead) ? $lead->projects : '');
                echo render_select('projects', $projects, ['id', ['name']], 'Projects', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>

                <div class="form-group">
                    <label for="alt_phonenumber"><?php echo _l('Alt PhoneNumber'); ?></label>
                    <div class="input-group" style="width: 100%;">
                        <input type="text"
                            id="alt_phonenumber"
                            name="alt_phonenumber"
                            data-id="<?php echo isset($lead) ? e($lead->id) : ''; ?>"
                            class="form-control"
                            value="<?php echo isset($lead) ? e($lead->alt_phonenumber) : ''; ?>">
                    </div>
                </div>

                <?php echo render_input('email', 'lead_add_edit_email', isset($lead) ? $lead->email : ''); ?>
                <?php echo render_input('firm', 'Firm Name', isset($lead) ? $lead->firm : ''); ?>
                <?php echo render_input('call_time', 'Call Time', isset($lead) ? $lead->call_time : ''); ?>

                <?php if (!is_language_disabled()) { ?>
                    <div class="form-group">
                        <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?></label>
                        <select name="default_language" data-live-search="true" id="default_language"
                            class="form-control selectpicker"
                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""><?php echo _l('system_default_string'); ?></option>
                            <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                                $selected = '';
                                if (isset($lead) && $lead->default_language == $availableLanguage) {
                                    $selected = 'selected';
                                } ?>
                                <option value="<?php echo e($availableLanguage); ?>" <?php echo e($selected); ?>>
                                    <?php echo e(ucfirst($availableLanguage)); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <!-- <?php if (!isset($lead)) { ?>
                            <div class="lead-select-date-contacted hide">
                                <?php echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', _dt($lead->lastcontact), ['data-date-end-date' => date('Y-m-d')]); ?>
                            </div>
                        <?php } else { ?>
                            <?php echo render_datetime_input('lastcontact', 'leads_dt_last_contact', _dt($lead->lastcontact), ['data-date-end-date' => date('Y-m-d')]); ?>
                        <?php } ?> -->

                        <!-- <div class="checkbox-inline checkbox checkbox-primary<?php if (isset($lead)) {
                                                                                        echo ' hide';
                                                                                    } ?><?php if (isset($lead) && (is_lead_creator($lead->id) || staff_can('edit', 'leads'))) {
                                                                                        echo ' lead-edit';
                                                                                    } ?>">
                            <input type="checkbox" name="is_public" <?php if (isset($lead) && $lead->is_public == 1) {
                                                                        echo 'checked';
                                                                    } ?> id="lead_public">
                            <label for="lead_public"><?php echo _l('lead_public'); ?></label>
                        </div> -->

                        <!-- <?php if (!isset($lead)) { ?>
                            <div class="checkbox-inline checkbox checkbox-primary">
                                <input type="checkbox" name="contacted_today" id="contacted_today" checked>
                                <label for="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></label>
                            </div>
                        <?php } ?> -->
                    </div>
                </div>

                <?php if (!empty($lead)) { ?>
                    <div class="lead-latest-activity tw-mb-3 ">
                        <div class="lead-info-heading">
                            <h4><?php echo _l('notes'); ?></h4>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="lead_notes_inner">
                            <?php echo form_open(admin_url('leads/add_note/' . $lead->id), ['id' => 'lead-notes']); ?>
                            <div class="form-group">
                                <textarea id="lead_note_description" name="lead_note_description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="lead-select-date-contacted ">
                                <?php
                                $cruntDate = date('Y-m-d H:i');
                                echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', $cruntDate, ['data-date-end-date' => date('Y-m-d')]); ?>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="radio radio-primary">
                                    <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes" checked>
                                    <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
                                </div>
                                <div class="radio radio-primary">
                                    <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no">
                                    <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="next_followup_date"><?php echo _l('Next Follow Up Date'); ?></label><small class="req text-danger">* </small>
                                <input type="date" class="form-control pull-right" name="next_followup_date" id="next_followup_date">
                            </div>

                            <button type="submit" form="lead-notes" id="lead-notes-submit" class="btn btn-primary pull-right hide">
                                <?php echo _l('lead_add_edit_add_note'); ?>
                            </button>
                            <?php echo form_close(); ?>

                            <div class="clearfix"></div>
                            <hr />

                            <!-- <?php
                                    $len = count($notes);
                                    $i   = 0;
                                    foreach ($notes as $note) { ?>
                                <div class="media lead-note">
                                    <a href="<?php echo admin_url('profile/' . $note['addedfrom']); ?>" target="_blank">
                                        <?php echo staff_profile_image($note['addedfrom'], ['staff-profile-image-small', 'pull-left mright10']); ?>
                                    </a>
                                    <div class="media-body">
                                        <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                            <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo e($note['id']); ?>, <?php echo e($lead->id); ?>);return false;">
                                                <i class="fa fa-times"></i>
                                            </a>
                                            <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo e($note['id']); ?>);return false;">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                        <?php } ?>

                                        <a href="<?php echo admin_url('profile/' . $note['addedfrom']); ?>" target="_blank">
                                            <h5 class="media-heading tw-font-semibold tw-mb-0">
                                                <?php if (!empty($note['date_contacted'])) { ?>
                                                    <span data-toggle="tooltip" data-title="<?php echo e(_dt($note['date_contacted'])); ?>">
                                                        <i class="fa fa-phone-square text-success" aria-hidden="true"></i>
                                                    </span>
                                                <?php } ?>
                                                <?php echo e(get_staff_full_name($note['addedfrom'])); ?>
                                            </h5>
                                            <span class="tw-text-sm tw-text-neutral-500">
                                                <?php echo e(_l('lead_note_date_added', _dt($note['dateadded']))); ?>
                                            </span><br>
                                            <?php if ($note['next_followup_date'] != '0000-00-00' && $note['next_followup_date'] != '') { ?>
                                                <span class="tw-text-sm tw-text-neutral-500">
                                                    Next Follow Up Date: <?php echo date('d-m-Y', strtotime($note['next_followup_date'])); ?>
                                                </span>
                                            <?php } ?>
                                        </a>

                                        <div data-note-description="<?php echo e($note['id']); ?>" class="text-muted mtop10">
                                            <?php echo process_text_content_for_display($note['description']); ?>
                                        </div>
                                        <div data-note-edit-textarea="<?php echo e($note['id']); ?>" class="hide mtop15">
                                            <?php echo render_textarea('note', '', $note['description']); ?>
                                            <div class="text-right">
                                                <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo e($note['id']); ?>);return false;"><?php echo _l('cancel'); ?></button>
                                                <button type="button" class="btn btn-primary" onclick="edit_note(<?php echo e($note['id']); ?>);"><?php echo _l('update_note'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($i >= 0 && $i != $len - 1) {
                                            echo '<hr />';
                                        } ?>
                                </div>
                            <?php $i++;
                                    } ?> -->
                        </div>
                    </div>


                <?php } ?>
            </div>

            <div class="col-md-12 mtop15">
                <?php $rel_id = (isset($lead) ? $lead->id : false); ?>
                <?php echo render_custom_fields('leads', $rel_id); ?>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>

    <?php if (isset($lead)) { ?>
        <?php
        $len = count($notes);
        $i   = 0;
        foreach ($notes as $note) { ?>
            <div class="media lead-note">
                <a href="<?php echo admin_url('profile/' . $note['addedfrom']); ?>" target="_blank">
                    <?php echo staff_profile_image($note['addedfrom'], ['staff-profile-image-small', 'pull-left mright10']); ?>
                </a>
                <div class="media-body">
                    <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                        <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo e($note['id']); ?>, <?php echo e($lead->id); ?>);return false;">
                            <i class="fa fa-times"></i>
                        </a>
                        <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo e($note['id']); ?>);return false;">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                    <?php } ?>

                    <a href="<?php echo admin_url('profile/' . $note['addedfrom']); ?>" target="_blank">
                        <h5 class="media-heading tw-font-semibold tw-mb-0">
                            <?php if (!empty($note['date_contacted'])) { ?>
                                <span data-toggle="tooltip" data-title="<?php echo e(_dt($note['date_contacted'])); ?>">
                                    <i class="fa fa-phone-square text-success" aria-hidden="true"></i>
                                </span>
                            <?php } ?>
                            <?php echo e(get_staff_full_name($note['addedfrom'])); ?>
                        </h5>
                        <span class="tw-text-sm tw-text-neutral-500">
                            <?php echo e(_l('lead_note_date_added', _dt($note['dateadded']))); ?>
                        </span><br>
                        <?php if ($note['next_followup_date'] != '0000-00-00' && $note['next_followup_date'] != '') { ?>
                            <span class="tw-text-sm tw-text-neutral-500">
                                Next Follow Up Date: <?php echo date('d-m-Y', strtotime($note['next_followup_date'])); ?>
                            </span>
                        <?php } ?>
                    </a>

                    <div data-note-description="<?php echo e($note['id']); ?>" class="text-muted mtop10">
                        <?php echo process_text_content_for_display($note['description']); ?>
                    </div>
                    <div data-note-edit-textarea="<?php echo e($note['id']); ?>" class="hide mtop15">
                        <?php echo render_textarea('note', '', $note['description']); ?>
                        <div class="text-right">
                            <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo e($note['id']); ?>);return false;"><?php echo _l('cancel'); ?></button>
                            <button type="button" class="btn btn-primary" onclick="edit_note(<?php echo e($note['id']); ?>);"><?php echo _l('update_note'); ?></button>
                        </div>
                    </div>
                </div>
                <?php if ($i >= 0 && $i != $len - 1) {
                    echo '<hr />';
                } ?>
            </div>
        <?php $i++;
        } ?>
        <div class="lead-latest-activity tw-mb-3 lead-view">
            <div class="lead-info-heading">
                <h4><?php echo _l('lead_latest_activity'); ?></h4>
            </div>
            <div id="lead-latest-activity" class="pleft5"></div>
        </div>
    <?php } ?>

    <?php if ($lead_locked == false) { ?>
        <div class="lead-edit<?php echo isset($lead) ? ' hide' : ''; ?>">
            <hr class="-tw-mx-4 tw-border-neutral-200" />
            <button type="submit" class="btn btn-primary pull-right lead-save-btn" id="lead-form-submit" form="lead_form">
                <?php echo _l('submit'); ?>
            </button>
            <button type="button" class="btn btn-default pull-right mright5" data-dismiss="modal">
                <?php echo _l('close'); ?>
            </button>
        </div>
    <?php } ?>

    <div class="clearfix"></div>
</div>

<?php if (isset($lead) && $lead_locked == true) { ?>
    <script>
        $(function() {
            $.each($('.lead-wrapper').find('input, select, textarea'), function() {
                $(this).attr('disabled', true);
                if ($(this).is('select')) {
                    $(this).selectpicker('refresh');
                }
            });
        });
    </script>
<?php } ?>

<script>
    $(function() {
        function populateCountryCodes(selectedCode = "+91") {
            let countryCodeSelect = $("#countryCode");
            countryCodeSelect.empty();
            countryCodeSelect.append('<option value="">Loading...</option>');

            // Fetch correct country codes
            fetch("https://countriesnow.space/api/v0.1/countries/codes")
                .then(response => response.json())
                .then(data => {
                    countryCodeSelect.empty();

                    if (data.error) {
                        console.error("API Error:", data.error);
                        countryCodeSelect.html('<option value="">Error Loading</option>');
                        return;
                    }

                    let defaultCountry = "+91"; // Default to India

                    data.data.forEach(country => {
                        let code = country.dial_code;

                        let isSelected = selectedCode ? selectedCode === code : code === defaultCountry;

                        let option = `<option value="${code}" ${isSelected ? "selected" : ""}>${code} (${country.name})</option>`;

                        countryCodeSelect.append(option);
                    });

                    // Refresh Selectpicker
                    $('.selectpicker').selectpicker('refresh');
                })
                .catch(error => {
                    console.error("API Fetch Error:", error);
                    countryCodeSelect.html('<option value="">Error Loading</option>');
                });
        }

        // Initialize Selectpicker
        $('.selectpicker').selectpicker();

        // Run when modal opens
        $('#lead-modal').on('shown.bs.modal', function() {
            let existingNumber = $("#phonenumber").val().trim();
            let selectedCode = existingNumber.match(/^\+(\d+)/) ? existingNumber.match(/^\+(\d+)/)[0] : "+91";

            populateCountryCodes(selectedCode);

            setTimeout(() => {
                $('.selectpicker').selectpicker('refresh');
            }, 500);
        });

        $('#lead-edit-square').on('click', function() {
            let existingNumber = $("#phonenumber").val().trim();
            let selectedCode = existingNumber.match(/^\+(\d+)/) ? existingNumber.match(/^\+(\d+)/)[0] : "+91";

            populateCountryCodes(selectedCode);

            setTimeout(() => {
                $('.selectpicker').selectpicker('refresh');
            }, 500);
        });

        // MAIN FORM SUBMIT - validate and combine phone number
        $('#lead-form-submit').on('click', function(e) {
            const $form = $('#lead_form');
            const $cc = $('#countryCode');
            const $num = $('#phonenumber');

            // clear old errors
            $num.removeClass('is-invalid');
            $num.closest('.form-group').find('label').removeClass('text-danger');
            $num.next('.invalid-feedback').remove();

            let local = ($num.val() || '').trim();
            if (local === '') {
                e.preventDefault();
                $num.addClass('is-invalid');
                $num.closest('.form-group').find('label').addClass('text-danger');
                $num.after('<div class="invalid-feedback">This field is required</div>');
                return false;
            }
            if (!/^\d+$/.test(local)) {
                e.preventDefault();
                $num.addClass('is-invalid');
                $num.closest('.form-group').find('label').addClass('text-danger');
                $num.after('<div class="invalid-feedback">Please enter only numbers</div>');
                return false;
            }

            // combine country code + local
            // const code = ($cc.val() || '').trim();
            // if (code) {
            //     $num.val(code + local);
            // } else {
            //     $num.val('+91' + local);
            // }

            // $form.submit();
        });
    });
</script>

<script>
    $('#lead-notes-submit').on('click', function(e) {
        e.preventDefault();

        const leadId = '<?php echo isset($lead) ? (int)$lead->id : 0; ?>';
        const $btn = $(this);
        const $form = $('#lead-notes');

        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo admin_url("leads/get_lead_status"); ?>',
            type: 'POST',
            data: {
                id: leadId
            },
            dataType: 'json'
        }).done(function(resp) {
            if (resp && resp.success) {
                if (resp.skip_date_check) {
                    // $form.submit();
                } else {
                    // If you want to enforce next_followup_date, add validation here
                    // $form.submit();
                }
            } else {
                alert_float('danger', (resp && resp.message) || 'Invalid lead status');
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            alert_float('danger', 'Error checking lead status');
            $btn.prop('disabled', false);
        });
    });
</script>