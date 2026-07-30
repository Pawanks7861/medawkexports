<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">Only 1 active voice CALL gateway is allowed!</div>
<div class="panel-group" id="call_gateways_options" role="tablist" aria-multiselectable="false">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingtwilio">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#call_gateways_options" href="#call_twilio"
					aria-expanded="false" aria-controls="call_twilio" class="collapsed">
					Twilio Voice Call <span class="pull-right"><i class="fa fa-sort-down"></i></span>
				</a>
			</h4>
		</div>
		<div id="call_twilio" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingtwilio"
			aria-expanded="false" style="height: 0px;">
			<div class="panel-body no-br-tlr no-border-color">
				<p>Twilio voice call integration is two way communication channel, means that your customers would be
					able to reply to the CALL. Phone numbers must be in format <a
						href="https://www.twilio.com/docs/glossary/what-e164" target="_blank">E.164</a>. Click <a
						href="https://support.twilio.com/hc/en-us/articles/223183008-Formatting-International-Phone-Numbers"
						target="_blank">here</a> to read more how phone numbers should be formatted. Click <a
						href="https://zonvoir.com/lead_manager/Api's-document" target="_blank">here</a> for module api's
					integration documentation.</p>
				<hr class="hr-10">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[call_twilio_account_sid]"><label
								for="settings[call_twilio_account_sid]" class="control-label">Account SID</label><input
								type="text" id="settings[call_twilio_account_sid]"
								name="settings[call_twilio_account_sid]" class="form-control"
								value="<?= get_option('call_twilio_account_sid') ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[call_twilio_auth_token]"><label
								for="settings[call_twilio_auth_token]" class="control-label">Auth Token</label><input
								type="text" id="settings[call_twilio_auth_token]"
								name="settings[call_twilio_auth_token]" class="form-control"
								value="<?= get_option('call_twilio_auth_token'); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[call_twilio_phone_number]"><label
								for="settings[call_twilio_phone_number]" class="control-label">Twilio Phone
								Number</label><input type="text" id="settings[call_twilio_phone_number]"
								name="settings[call_twilio_phone_number]" class="form-control"
								value="<?= get_option('call_twilio_phone_number'); ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[call_twiml_app_sid]"><label
								for="settings[call_twiml_app_sid]" class="control-label">Twiml App SID (<a
									href="https://support.twilio.com/hc/en-us/articles/223183008-Formatting-International-Phone-Numbers"
									target="_blank">Download</a> app configuration steps.)</label><input type="text"
								id="settings[call_twiml_app_sid]" name="settings[call_twiml_app_sid]"
								class="form-control" value="<?= get_option('call_twiml_app_sid'); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="call_twilio_recording_active" class="control-label clearfix">
								Recording Allow ?</label>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_1_Active_record"
									name="settings[call_twilio_recording_active]" value="1"
									<?= get_option('call_twilio_recording_active') == '1' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_1_Active_record">
									YES</label>
							</div>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_2_Active_record"
									name="settings[call_twilio_recording_active]" value="0"
									<?= get_option('call_twilio_recording_active') == '0' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_2_Active_record">
									NO</label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="call_twilio_active" class="control-label clearfix">
								Active</label>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_1_Active" name="settings[call_twilio_active]" value="1"
									<?= get_option('call_twilio_active') == '1' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_1_Active">
									Yes</label>
							</div>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_2_Active" name="settings[call_twilio_active]" value="0"
									<?= get_option('call_twilio_active') == '0' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_2_Active">
									No</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingzoom">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#call_gateways_options" href="#zoom_meeting"
					aria-expanded="false" aria-controls="zoom_meeting" class="collapsed">
					Zoom Meeting <span class="pull-right"><i class="fa fa-sort-down"></i></span>
				</a>
			</h4>
		</div>
		<div id="zoom_meeting" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingzoom"
			aria-expanded="false" style="height: 0px;">
			<div class="panel-body no-br-tlr no-border-color">
				<p>Zoom API integration Details </p>
				<hr class="hr-10">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[zoom_api_key]"><label
								for="settings[zoom_api_key]" class="control-label">CLIENT ID</label><input type="text"
								id="settings[zoom_api_key]" name="settings[zoom_api_key]" class="form-control"
								value="<?= get_option('zoom_api_key') ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[zoom_secret_key]"><label
								for="settings[zoom_secret_key]" class="control-label">CLIENT SECRET</label><input
								type="text" id="settings[zoom_secret_key]" name="settings[zoom_secret_key]"
								class="form-control" value="<?= get_option('zoom_secret_key'); ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[lm_zoom_account_id]"><label
								for="settings[lm_zoom_account_id]" class="control-label">ZOOM ACCOUNT ID</label><input
								type="text" id="settings[lm_zoom_account_id]" name="settings[lm_zoom_account_id]"
								class="form-control" value="<?= get_option('lm_zoom_account_id'); ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="call_zoom_active" class="control-label clearfix">
								Active</label>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_zoom_1_Active" name="settings[call_zoom_active]" value="1"
									<?= get_option('call_zoom_active') == '1' ? 'checked="checked"' : ''; ?>>
								<label for="y_zoom_1_Active">
									Yes</label>
							</div>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_zoom_2_Active" name="settings[call_zoom_active]" value="0"
									<?= get_option('call_zoom_active') == '0' ? 'checked="checked"' : ''; ?>>
								<label for="y_zoom_2_Active">
									No</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- ASSISTANT CONFIGURATION -->

	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingheadingassistantConfiguration">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#headingassistantConfiguration"
					href="#assistantConfiguration" aria-expanded="false" aria-controls="zoom_meeting" class="collapsed">
					AI Configuration <span class="pull-right"><i class="fa fa-sort-down"></i></span>
				</a>
			</h4>
		</div>
		<div id="assistantConfiguration" class="panel-collapse collapse" role="tabpanel"
			aria-labelledby="headingassistantConfiguration" aria-expanded="false" style="height: 0px;">
			<div class="panel-body no-br-tlr no-border-color">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[lm_api_key]"><input type="text"
								id="settings[lm_api_key]" name="settings[lm_api_key]" class="form-control"
								value="<?= get_option('lm_api_key') ?>" placeholder="Chat GPT api key Ex: SK************">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[lm_ai_asst]">
							<label for="settings[lm_ai_asst]" class="control-label"></label>
							<?php if (!empty(get_option('lm_api_key'))) { ?>
								<?php if (!empty(get_option('lm_asistant_id'))) { ?>
									<button type="button" class="btn btn-success pull-right" data-toggle="modal"
										data-target="#config-ai-asst-modal"><i class="fa-solid fa-gear"></i> Assistant
										Configured
									</button>
								<?php } else { ?>
									<button type="button" class="btn btn-primary pull-right" data-toggle="modal"
										data-target="#config-ai-asst-modal"><i class="fa-solid fa-gear"></i>Configure Assistant
									</button>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label for="lm_ai_add_lead" class="control-label clearfix">
								<i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="Create Lead after end publc chat by customer!" data-original-title="" title=""></i> Create Lead </label>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_1_Create Lead" name="settings[lm_ai_add_lead]" value="1" <?= get_option('lm_ai_add_lead') == '1' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_1_Create Lead">
									Yes </label>
							</div>
							<div class="radio radio-primary radio-inline">
								<input type="radio" id="y_opt_2_Create Lead" name="settings[lm_ai_add_lead]" value="0" <?= get_option('lm_ai_add_lead') == '0' ? 'checked="checked"' : ''; ?>>
								<label for="y_opt_2_Create Lead">
									No </label>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<?php echo render_select('settings[lm_ai_lead_added_by]', $staff, ['staffid', 'full_name'], 'Added By', get_option('lm_ai_lead_added_by')); ?>
					</div>
					<div class="col-md-3">
						<?php
						$CI = &get_instance();
						if (!class_exists('leads_model')) {
							$CI->load->model('leads_model');
						}
						$statuses = $CI->leads_model->get_status();
						echo render_select('settings[lm_ai_lead_status]', $statuses, ['id', 'name'], 'Status', get_option('lm_ai_lead_status'));
						?>
					</div>
					<div class="col-md-3">
						<?php
						$CI = &get_instance();
						if (!class_exists('leads_model')) {
							$CI->load->model('leads_model');
						}
						$sources = $CI->leads_model->get_source();
						echo render_select('settings[lm_ai_lead_source]', $sources, ['id', 'name'], 'Source', get_option('lm_ai_lead_source'));
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- FIREBASE CONFIGURATION -->

	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingheadingFirebaseConfiguration">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#headingheadingFirebaseConfiguration"
					href="#firebaseConfiguration" aria-expanded="false" aria-controls="firebaseConfiguration"
					class="collapsed">
					Firebase Configuration <span class="pull-right"><i class="fa fa-sort-down"></i></span>
				</a>
			</h4>
		</div>
		<div id="firebaseConfiguration" class="panel-collapse collapse" role="tabpanel"
			aria-labelledby="headingassistantConfiguration" aria-expanded="false" style="height: 0px;">
			<div class="panel-body no-br-tlr no-border-color">
				<p>Firebase Configuration Details</p>
				<hr class="hr-10">
				<div class="row">
					<div class="col-md-6">

						<div class="form-group" app-field-wrapper="settings[lm_firebase_api_key]"><label
								for="settings[lm_firebase_api_key]" class="control-label">API KEY</label><input type="text"
								id="settings[lm_firebase_api_key]" name="settings[lm_firebase_api_key]" class="form-control"
								value="<?= get_option('lm_firebase_api_key') ?>">
						</div>
						<div class="form-group" app-field-wrapper="settings[lm_firebase_auth_domain]"><label
								for="settings[lm_firebase_auth_domain]" class="control-label">AUTH DOMAIN</label><input type="text"
								id="settings[lm_firebase_auth_domain]" name="settings[lm_firebase_auth_domain]" class="form-control"
								value="<?= get_option('lm_firebase_auth_domain') ?>">
						</div>

					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[lm_firebase_databse_url]"><label
								for="settings[lm_firebase_databse_url]" class="control-label">DATABASE URL</label><input type="text"
								id="settings[lm_firebase_databse_url]" name="settings[lm_firebase_databse_url]" class="form-control"
								value="<?= get_option('lm_firebase_databse_url') ?>">
						</div>
						<div class="form-group" app-field-wrapper="settings[lm_firebase_project_id]"><label
								for="settings[lm_firebase_project_id]" class="control-label">PROJECT ID</label><input type="text"
								id="settings[lm_firebase_project_id]" name="settings[lm_firebase_project_id]" class="form-control"
								value="<?= get_option('lm_firebase_project_id') ?>">
						</div>
					</div>
					<div class="col-md-6">

						<div class="form-group" app-field-wrapper="settings[lm_firebase_storage_bucket]"><label
								for="settings[lm_firebase_storage_bucket]" class="control-label">STORAGE BUCKET</label><input type="text"
								id="settings[lm_firebase_storage_bucket]" name="settings[lm_firebase_storage_bucket]" class="form-control"
								value="<?= get_option('lm_firebase_storage_bucket') ?>">
						</div>

						<div class="form-group" app-field-wrapper="settings[lm_firebase_messaging_sender_id]"><label
								for="settings[lm_firebase_messaging_sender_id]" class="control-label">MESSAGING SENDER ID</label><input type="text"
								id="settings[lm_firebase_messaging_sender_id]" name="settings[lm_firebase_messaging_sender_id]" class="form-control"
								value="<?= get_option('lm_firebase_messaging_sender_id') ?>">
						</div>

					</div>
					<div class="col-md-6">
						<div class="form-group" app-field-wrapper="settings[lm_firebase_app_id]"><label
								for="settings[lm_firebase_app_id]" class="control-label">APP ID</label><input type="text"
								id="settings[lm_firebase_app_id]" name="settings[lm_firebase_app_id]" class="form-control"
								value="<?= get_option('lm_firebase_app_id') ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>