<div class="modal fade" id="config-ai-asst-modal" tabindex="-1" aria-labelledby="config-ai-asst-modal"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5" id="config-ai-asst-modal">Assistant Configuration</h4>
            </div>
            <?php echo form_open(admin_url('lead_manager/asst_config')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                    $readonly = array('readonly' => false);
                                    if (get_option('lm_asistant_id') && !empty(get_option('lm_asistant_id'))) {
                                        $readonly['readonly'] = true;
                                    }?>

                                    <?php echo render_input('settings[lm_asistant_name]', 'Assistant Name', get_option('lm_asistant_name') ?? ''); ?>

                                    <?php echo render_input('settings[lm_asistant_id]', 'Assistant Id', get_option('lm_asistant_id') ?? '', '', $readonly); ?>

                                    <?php echo render_textarea('settings[lm_asistant_prompt]', 'Prompt', get_option('lm_asistant_prompt') ?? ''); ?>
                                
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>

                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>