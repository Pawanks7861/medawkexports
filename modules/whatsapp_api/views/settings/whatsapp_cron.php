<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#whatsapp_cron_job_settings" aria-controls="whatsapp_cron_job_settings" role="tab"
                    data-toggle="tab">
                    <?php echo _l('settings'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#whatsapp_cron_job_queue" aria-controls="whatsapp_cron_job_queue" role="tab" data-toggle="tab">
                    <?php echo _l('whatsapp_cron_job_queue'); ?>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="whatsapp_cron_job_settings">
        <div class="alert alert-info tw-mb-0">
            <span class="bold text-info">WHATSAPP CRON COMMAND: wget -q -O-
                <?php echo site_url('whatsapp_api/cron/index') ?>
            </span><br />
            <?php if (is_admin()) { ?>
                <a href="<?= site_url('whatsapp_api/cron/manually') ?>">Run Cron Manually</a>
            <?php } ?>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="whatsapp_cron_job_queue">
        <div class="alert alert-danger">
            This feature requires a properly configured whatsapp cron job. Before activating the feature, make sure that
            the <a href="<?php echo admin_url('settings?group=whatsapp_cron'); ?>">cron job</a> is configured as explanation
            in the documentation.
        </div>
        <?php render_yes_no_option('whatsapp_cron_job_queue_enabled', 'whatsapp_cron_job_queue_enabled', 'Whatsapp email queue tooltip'); ?>
        <hr />
        <?php render_datatable([
            _l('status'),
            _l('scheduled_at'),
            _l('executed_at'),
            _l('type'),
            _l('action')
        ], 'whatsapp_cron_job_queue_table') ?>
    </div>
</div>