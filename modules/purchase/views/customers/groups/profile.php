<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="mtop5"><?php echo _l('Booking Form'); ?></h4>
<hr />
<div class="row">
   <?php echo form_hidden('userid', (isset($client) ? $client->userid : '')); ?>
   <?php echo form_open($this->uri->uri_string(), array('class' => 'vendor-form', 'autocomplete' => 'off')); ?>
   <div class="additional"></div>
   <div class="col-md-12">
      <div class="horizontal-scrollable-tabs">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
               <li role="presentation" class="<?php if (!$this->input->get('tab')) {
                                                   echo 'active';
                                                }; ?>">
                  <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
                     <?php echo _l('Customer Detail'); ?>
                  </a>
               </li>
               <?php
               $customer_custom_fields = false;
               if (total_rows(db_prefix() . 'customfields', array('fieldto' => 'vendors', 'active' => 1)) > 0) {
                  $customer_custom_fields = true;
               ?>
                  <li role="presentation" class="<?php if ($this->input->get('tab') == 'custom_fields') {
                                                      echo 'active';
                                                   }; ?>">
                     <a href="#custom_fields" aria-controls="custom_fields" role="tab" data-toggle="tab">
                        <?php echo _l('custom_fields'); ?>
                     </a>
                  </li>
               <?php } ?>

            </ul>
         </div>
      </div>
      <div class="tab-content">

         <?php if ($customer_custom_fields) { ?>
            <div role="tabpanel" class="tab-pane <?php if ($this->input->get('tab') == 'custom_fields') {
                                                      echo ' active';
                                                   }; ?>" id="custom_fields">
               <?php $rel_id = (isset($client) ? $client->userid : false); ?>
               <?php echo render_custom_fields('vendors', $rel_id); ?>
            </div>
         <?php } ?>
         <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
                                                echo ' active';
                                             }; ?>" id="contact_info">
            <div class="row">
               <div class="col-md-12<?php if (isset($client) && (!is_empty_customer_company($client->userid) && total_rows(db_prefix() . 'contacts', array('userid' => $client->userid, 'is_primary' => 1)) > 0)) {
                                       echo '';
                                    } else {
                                       echo ' hide';
                                    } ?>" id="client-show-primary-contact-wrapper">

               </div>
               <div class="col-md-6">
                  <?php $vendor_code = (isset($client) ? $client->vendor_code : $next_number);
                  echo render_input('vendor_code', 'Customer Code', $vendor_code, 'text', array('readonly' => true)); ?>
                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->company : ''); ?>
                        <?php $attrs = (isset($client) ? array() : array('autofocus' => true)); ?>
                        <?php echo render_input('company', 'Customer Name', $value, 'text', $attrs); ?>
                        <div id="company_exists_info" class="hide"></div>
                        <!-- Container for dynamic fields -->
                        <?php
                        if ($client2->company2) { ?>
                           <?php $value = (isset($client2) ? $client2->company2 : ''); ?>
                           <?php echo render_input('company2', '', $value, 'text', ['placeholder' => 'Customer Name']); ?>
                        <?php } else { ?>
                           <div id="extra_customers"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add New Customer" id="add_new_customer" style="cursor:pointer;"></i>
                           </span>
                        <?php }
                        ?>

                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->email : ''); ?>
                        <?php echo render_input('email', 'email', $value, 'email'); ?>

                     </div>

                     <div class="col-md-6" style="clear: both;">
                        <?php $value = (isset($client) ? $client->phonenumber : ''); ?>
                        <?php echo render_input('phonenumber', 'client_phonenumber', $value); ?>

                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->pan_card : ''); ?>
                        <?php echo render_input('pan_card', 'Pan Card', $value); ?>
                        <?php
                        if ($client2->pan_card_2) { ?>
                           <?php $value = (isset($client2) ? $client2->pan_card_2 : ''); ?>
                           <?php echo render_input('pan_card_2', '', $value, 'text', ['placeholder' => 'Pan Card 2']); ?>
                        <?php } else { ?>
                           <div id="extra_pan_card"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add Pan Card" id="add_new_pan_card" style="cursor:pointer;"></i>
                           </span>
                        <?php } ?>

                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->adhar_card : ''); ?>
                        <?php echo render_input('adhar_card', 'Adhar Card', $value); ?>

                        <?php
                        if ($client2->adhar_card_2) { ?>
                           <?php $value = (isset($client2) ? $client2->adhar_card_2 : ''); ?>
                           <?php echo render_input('adhar_card_2', '', $value, 'text', ['placeholder' => 'Adhar Card']); ?>
                        <?php } else { ?>
                           <div id="extra_adhar_card"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add Adhar Card" id="add_new_adhar_card" style="cursor:pointer;"></i>
                           </span>
                        <?php } ?>

                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->election_card : ''); ?>
                        <?php echo render_input('election_card', 'Election Card', $value); ?>
                        <?php
                        if ($client2->election_card_2) { ?>
                           <?php $value = (isset($client2) ? $client2->election_card_2 : ''); ?>
                           <?php echo render_input('election_card_2', '', $value, 'text', ['placeholder' => 'Election Card 2']); ?>
                        <?php } else { ?>
                           <div id="extra_election_card"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add Election Card" id="add_new_election_card" style="cursor:pointer;"></i>
                           </span>
                        <?php } ?>

                     </div>

                     <div class="col-md-6" style="    clear: both;">
                        <?php $value = (isset($client) ? $client->property_id : ''); ?>
                        <?php echo render_select('property_id', $warehouses, array('warehouse_code', 'warehouse_name'), 'Property Name', $value, ['required' => true]) ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->block_id : ''); ?>
                        <?php echo render_select('block_id', $commodity_groups, array('id', 'name'), 'Block Name', $value, ['required' => true]) ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->floor_id : ''); ?>
                        <?php echo render_select('floor_id', $sub_groups, array('id', 'sub_group_name'), 'Floor Name', $value, ['required' => true]) ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->flat_id : ''); ?>
                        <input type="hidden" id="flat_id_hidden" value="<?php echo $value; ?>">
                        <?php echo render_select('flat_id', [], [], 'Flat Name', $value, ['required' => true]) ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->age : ''); ?>
                        <?php echo render_input('age', 'Age', $value, 'text'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->occupation : ''); ?>
                        <?php echo render_input('occupation', 'Occupation', $value, 'text'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->sr_no : ''); ?>
                        <?php echo render_input('sr_no', 'Sr. No.', $value, 'text'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->sr_date : ''); ?>
                        <?php echo render_input('sr_date', 'Date', $value, 'date'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->sub_registrar : ''); ?>
                        <?php echo render_input('sub_registrar', 'Sub-Registrar', $value, 'text'); ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="bu_permissions">BU Permissions</label>
                           <div class="checkbox">
                              <input type="hidden" name="bu_permissions" value="0">
                              <input type="checkbox"
                                 id="bu_permissions"
                                 name="bu_permissions"
                                 value="1"
                                 <?php echo (isset($client) && $client->bu_permissions == 1) ? 'checked' : ''; ?>>
                              <label for="bu_permissions">Enable BU Permissions</label>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6" style="clear:both;">
                        <div class="form-group">
                           <label for="car_parking">Car Parking</label>
                           <div class="checkbox">
                              <input type="hidden" name="car_parking" value="0">
                              <input type="checkbox"
                                 id="car_parking"
                                 name="car_parking"
                                 value="1"
                                 <?php echo (isset($client) && $client->car_parking == 1) ? 'checked' : ''; ?>>
                              <label for="car_parking">One Car Parking</label>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->driving_licence : ''); ?>
                        <?php echo render_input('driving_licence', 'Driving Licence', $value, 'text'); ?>

                        <?php
                        if ($client2->driving_licence_2) { ?>
                           <?php $value = (isset($client2) ? $client2->driving_licence_2 : ''); ?>
                           <?php echo render_input('driving_licence_2', '', $value, 'text', ['placeholder' => 'Driving Licence ']); ?>
                        <?php } else { ?>
                           <div id="extra_driving_licence"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add Driving Licence" id="add_new_driving_licence" style="cursor:pointer;"></i>
                           </span>
                        <?php } ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="terrace">Terrace</label>
                           <div class="checkbox">
                              <input type="hidden" name="terrace" value="0">
                              <input type="checkbox"
                                 id="terrace"
                                 name="terrace"
                                 value="1"
                                 <?php echo (isset($client) && $client->terrace == 1) ? 'checked' : ''; ?>>
                              <label for="terrace">Terrace</label>
                           </div>
                           <!-- Added terrace_val input field -->
                           <input type="text"
                              class="form-control"
                              id="terrace_val"
                              name="terrace_val"
                              placeholder="Enter terrace value"
                              value="<?php echo isset($client) ? htmlspecialchars($client->terrace_val) : ''; ?>">
                        </div>
                     </div>
                  </div>
                  <?php if (get_option('disable_language') == 0) { ?>
                     <div class="form-group select-placeholder">
                        <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                        </label>
                        <select name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""><?php echo _l('system_default_string'); ?></option>
                           <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                              $selected = '';
                              if (isset($client)) {
                                 if ($client->default_language == $availableLanguage) {
                                    $selected = 'selected';
                                 }
                              }
                           ?>
                              <option value="<?php echo pur_html_entity_decode($availableLanguage); ?>" <?php echo pur_html_entity_decode($selected); ?>><?php echo ucfirst($availableLanguage); ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  <?php } ?>
               </div>
               <div class="col-md-6">
                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->tokan_amount : ''); ?>
                        <?php echo render_input('tokan_amount', 'Token Amount(₹)', $value, 'number'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($client) ? $client->final_amount : ''); ?>
                        <?php echo render_input('final_amount', 'Final Amount(₹)', $value, 'number'); ?>
                     </div>
                     <div id="bank-details-container">
                        <?php if (!empty($payment_details) && is_array($payment_details)): ?>
                           <?php foreach ($payment_details as $index => $detail): ?>
                              <div class="bank-detail-row" id="bank-detail-row-<?php echo $index; ?>">
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="col-md-12">
                                          <?php if ($index > 0): ?>
                                             <hr>
                                          <?php endif; ?>
                                          <h5>Payment Details <?php echo $index + 1; ?></h5>
                                       </div>
                                       <div class="col-md-6">
                                          <input type="hidden" name="bank_details[<?php echo $index; ?>][id]" value="<?php echo html_escape($detail['id'] ?? ''); ?>">
                                          <div class="form-group">
                                             <label for="bank_name_<?php echo $index; ?>" class="control-label">Bank Name</label>
                                             <input type="text" id="bank_name_<?php echo $index; ?>" name="bank_details[<?php echo $index; ?>][bank_name]" class="form-control" value="<?php echo html_escape($detail['bank_name'] ?? ''); ?>">
                                          </div>
                                       </div>
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label for="cheque_no_<?php echo $index; ?>" class="control-label">Cheque No. / UTR</label>
                                             <input type="text" id="cheque_no_<?php echo $index; ?>" name="bank_details[<?php echo $index; ?>][cheque_no]" class="form-control" value="<?php echo html_escape($detail['cheque_no'] ?? ''); ?>">
                                          </div>
                                       </div>
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label for="payment_date_<?php echo $index; ?>" class="control-label">Payment Date</label>
                                             <input type="date" id="payment_date_<?php echo $index; ?>" name="bank_details[<?php echo $index; ?>][payment_date]" class="form-control" value="<?php echo html_escape($detail['payment_date'] ?? ''); ?>">
                                          </div>
                                       </div>
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label for="amount_<?php echo $index; ?>" class="control-label">Amount</label>
                                             <input type="number" id="amount_<?php echo $index; ?>" name="bank_details[<?php echo $index; ?>][amount]" class="form-control" value="<?php echo html_escape($detail['amount'] ?? ''); ?>" step="0.01">
                                          </div>
                                       </div>
                                       <?php if ($index > 0): ?>
                                          <div class="col-md-12 text-right">
                                             <button type="button" class="btn btn-danger btn-remove-bank-detail" data-row-id="bank-detail-row-<?php echo $index; ?>">
                                                <i class="fa fa-times"></i> Remove
                                             </button>
                                          </div>
                                       <?php endif; ?>
                                    </div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <!-- Default/Empty row -->
                           <div class="bank-detail-row" id="bank-detail-row-0">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-12">
                                       <h5>Payment Details 1</h5>
                                    </div>
                                    <div class="col-md-6">
                                       <input type="hidden" name="bank_details[0][id]" value="">
                                       <div class="form-group">
                                          <label for="bank_name_0" class="control-label">Bank Name</label>
                                          <input type="text" id="bank_name_0" name="bank_details[0][bank_name]" class="form-control" value="">
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group">
                                          <label for="cheque_no_0" class="control-label">Cheque No. / UTR</label>
                                          <input type="text" id="cheque_no_0" name="bank_details[0][cheque_no]" class="form-control" value="">
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group">
                                          <label for="payment_date_0" class="control-label">Payment Date</label>
                                          <input type="date" id="payment_date_0" name="bank_details[0][payment_date]" class="form-control" value="">
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group">
                                          <label for="amount_0" class="control-label">Amount</label>
                                          <input type="number" id="amount_0" name="bank_details[0][amount]" class="form-control" value="" step="0.01">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        <?php endif; ?>
                     </div>

                     <!-- Add button -->
                     <div class="col-md-12">
                        <button type="button" id="add-bank-detail" class="btn btn-info pull-right mbot25 mtop10">
                           <i class="fa fa-plus"></i> Add More Bank Details
                        </button>
                     </div>

                     <div class="col-md-12">
                        <?php $value = (isset($client) ? $client->address : ''); ?>
                        <?php echo render_textarea('address', 'client_address', $value); ?>
                        <?php
                        if ($client2->address_2) { ?>
                           <?php $value = (isset($client2) ? $client2->address_2 : ''); ?>
                           <?php echo render_textarea('address_2', '', $value, ['placeholder' => 'Address 2']); ?>
                        <?php } else { ?>
                           <div id="extra_address"></div>
                           <span>
                              <i class="fa fa-plus pull-right" title="Add Address" id="add_new_address" style="cursor:pointer;"></i>
                           </span>
                        <?php } ?>
                     </div>

                  </div>
                  <?php $bank_detail = (isset($client) ? $client->bank_detail : ''); ?>
                  <?php echo render_textarea('bank_detail', 'bank_detail', $bank_detail); ?>
                  <?php $payment_terms = (isset($client) ? $client->payment_terms : ''); ?>
                  <?php echo render_textarea('payment_terms', 'payment_terms', $payment_terms); ?>

               </div>
            </div>
         </div>




      </div>
   </div>
   <?php echo form_close(); ?>
</div>
<?php if (isset($client)) { ?>
   <?php if (has_permission('purchase_vendors', '', 'create') || has_permission('purchase_vendors', '', 'edit')) { ?>
      <div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
         <div class="modal-dialog">
            <?php echo form_open(admin_url('purchase/assign_vendor_admins/' . $client->userid)); ?>
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
               </div>
               <div class="modal-body">
                  <?php
                  $selected = array();
                  foreach ($customer_admins as $c_admin) {
                     array_push($selected, $c_admin['staff_id']);
                  }
                  echo render_select('customer_admins[]', $staff, array('staffid', array('firstname', 'lastname')), '', $selected, array('multiple' => true), array(), '', '', false); ?>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
               </div>
            </div>
            <!-- /.modal-content -->
            <?php echo form_close(); ?>
         </div>
         <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
   <?php } ?>
<?php } ?>