<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php if (isset($form)) {
    echo form_hidden('form_id', $form->id);
} ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (!isset($form)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('form_builder_create_form_first'); ?>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (isset($form)) { ?>
                        <h4><?php echo _l('form_integration_code'); ?></h4>
                        <?php } ?>
                        <div class="tab-content">
                            <?php if (isset($form)) { ?>
                                <p><?php echo _l('form_integration_code_help'); ?></p>
                                <textarea class="form-control"
                                    rows="2"><iframe width="400" height="500" src="<?php echo site_url('lead_manager/chat_ai/form/' . $form->form_key); ?>" frameborder="0" sandbox="allow-top-navigation allow-forms allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe></textarea>
                                <h4 class="tw-my-5 bold">Share direct link</h4>
                                <p>
                                    <span class="label label-default">
                                        <a href="<?php echo site_url('lead_manager/chat_ai/form/' . $form->form_key) . '?styled=1'; ?>"
                                            target="_blank">
                                            <?php echo site_url('lead_manager/chat_ai/form/' . $form->form_key) . '?styled=1'; ?>
                                        </a>
                                    </span>
                                    <br />
                                    <br />
                                    <span class="label label-default">
                                        <a href="<?php echo site_url('lead_manager/chat_ai/form/' . $form->form_key) . '?styled=1&with_logo=1'; ?>"
                                            target="_blank">
                                            <?php echo site_url('lead_manager/chat_ai/form/' . $form->form_key) . '?styled=1&with_logo=1'; ?>
                                        </a>
                                    </span>
                                </p>
                                <hr/>
                                <p class="bold mtop15">When placing the iframe snippet code consider the following:</p>
                                <p
                                    class="<?php echo strpos(site_url(), 'http://') !== false ? 'bold text-success' : ''; ?>">
                                    1. If the protocol of your installation is http use a http page inside the iframe.
                                </p>
                                <p
                                    class="<?php echo strpos(site_url(), 'https://') !== false ? 'bold text-success' : ''; ?>">
                                    2. If the protocol of your installation is https use a https page inside the
                                    iframe.
                                </p>
                                <p>
                                    None SSL installation will need to place the link in non ssl eq. landing page and
                                    backwards.
                                </p>

                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
