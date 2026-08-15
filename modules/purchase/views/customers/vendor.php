<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <?php if (isset($client) && $client->registration_confirmed == 0 && is_admin()) { ?>
               <div class="alert alert-warning">
                  <?php echo _l('customer_requires_registration_confirmation'); ?>
                  <br />
                  <a href="<?php echo admin_url('purchase/confirm_registration/' . $client->userid); ?>"><?php echo _l('confirm_registration'); ?></a>
               </div>
            <?php } ?>

            <?php if (isset($client) && (!has_permission('purchase_customers', '', 'view') && is_vendor_admin($client->userid))) { ?>
               <div class="alert alert-info">
                  <?php echo _l('customer_admin_login_as_client_message', get_staff_full_name(get_staff_user_id())); ?>
               </div>
            <?php } ?>
         </div>
         <?php if ($group == 'profile') { ?>
            <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
               <button class="btn btn-info only-save customer-form-submiter">
                  <?php echo _l('submit'); ?>
               </button>

            </div>
         <?php } ?>
         <?php if (isset($client)) { ?>
            <div class="col-md-3">
               <div class="panel_s mbot5">
                  <div class="panel-body padding-10">
                     <h4 class="bold">
                        #<?php echo pur_html_entity_decode($client->userid . ' ' . $title); ?>


                     </h4>
                  </div>
               </div>
               <?php $this->load->view('customers/tabs'); ?>
            </div>
         <?php } ?>
         <div class="col-md-<?php if (isset($client)) {
                                 echo 9;
                              } else {
                                 echo 12;
                              } ?>">
            <div class="panel_s">
               <div class="panel-body">
                  <?php if (isset($client)) { ?>
                     <?php echo form_hidden('isedit'); ?>
                     <?php echo form_hidden('userid', $client->userid); ?>
                     <div class="clearfix"></div>
                  <?php } ?>
                  <div>
                     <div class="tab-content">
                        <?php $this->load->view((isset($tabs) ? $tabs['view'] : 'customers/groups/profile')); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php if ($group == 'profile') { ?>
         <div class="btn-bottom-pusher"></div>
      <?php } ?>
   </div>
</div>
<?php init_tail(); ?>

<?php require 'modules/purchase/assets/js/customer_js.php'; ?>

</body>

</html>
<script>
   $('select[name="block_id"]').on('change', function() {

      var data_select = {};

      data_select.group_id = $('select[name="block_id"]').val();


      $.post(admin_url + 'warehouse/get_subgroup_fill_data', data_select).done(function(response) {
         response = JSON.parse(response);
         $("select[name='floor_id']").html('');

         $("select[name='floor_id']").append(response.subgroup);
         $("select[name='floor_id']").selectpicker('refresh');

         if (sub_group_value != '') {

            $("select[name='floor_id']").val(sub_group_value).change();
            sub_group_value = '';
         }



      });

   });

   $('select[name="property_id"]').on('change', function() {
      $("select[name='block_id']").val('').change();
      $("select[name='floor_id']").val('').change();
      $("select[name='flat_id']").val('').change();
   });

   $(document).ready(function() {
      // Check if floor_id has a value on page load
      var floor_id = $("select[name='floor_id']").val();

      if (floor_id && floor_id != '') {
         $('select[name="floor_id"]').trigger('change');
      }
   });
   $('select[name="floor_id"]').on('change', function() {
      var block_id = $("select[name='block_id']").val();
      var property_id = $("select[name='property_id']").val();
      var floor_id = $("select[name='floor_id']").val();
      var flat_id_hidden = $('#flat_id_hidden').val();
      if (floor_id != '') {

         var data_select = {};
         data_select.block_id = block_id;
         data_select.property_id = property_id;
         data_select.floor_id = floor_id;
         data_select.flat_id_hidden = flat_id_hidden;
         $.post(admin_url + 'warehouse/get_flat_fill_data', data_select).done(function(response) {
            response = JSON.parse(response);
            $("select[name='flat_id']").html('');
            $("select[name='flat_id']").append(response.flats);
            $("select[name='flat_id']").selectpicker('refresh');
         });
      }

   });
   $(document).ready(function() {
      $(document).on('click', '#add_new_customer', function() {

         let html = `
            <div class="col-md-12 customer-field" style="margin:10px 0; padding:0 !important; position:relative;">
                <input type="text" name="company2" class="form-control" placeholder="Customer Name">

                <span>
                    <i class="fa fa-times pull-right text-danger remove_customer" 
                       style="cursor:pointer; position:absolute; top:-4px; right:-4px;"></i>
                </span>

                <div class="extra_customers2"></div>

                <span>
                    <i class="fa fa-plus pull-right add_new_customer2" 
                       style="cursor:pointer;"></i>
                </span>
            </div>
        `;

         $('#extra_customers').append(html);
         $('#add_new_customer').addClass('hide');
      });


      // Second add button (delegated)
      $(document).on('click', '.add_new_customer2', function() {

         let html = `
            <div class="col-md-12 customer-field" style="margin:10px 0; padding:0 !important; position:relative;">
                <input type="text" name="company3" class="form-control" placeholder="Customer Name">

                <span>
                    <i class="fa fa-times pull-right text-danger remove_customer2" 
                       style="cursor:pointer; position:absolute; top:-4px; right:-4px;"></i>
                </span>
            </div>
        `;

         // append inside same block's extra_customers2
         $(this).closest('.customer-field').find('.extra_customers2').append(html);

         // hide only the clicked plus icon
         $(this).addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_customer', function() {
         $('#company2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_customer').removeClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_customer2', function() {
         $('#company3').val('');
         $(this).closest('.customer-field').remove();

         $(this).removeClass('hide');
      });

      $(document).on('click', '#add_new_pan_card', function() {
         var html = `
            <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <input type="text" name="pan_card_2" class="form-control" id="pan_card" placeholder="Pan Card">
               <span>
                    <i class="fa fa-times pull-right text-danger remove_pan_card" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
                </span>
                <div id="extra_pan_card2"></div>
               <span>
                  <i class="fa fa-plus pull-right" title="Add Pan Card" id="add_new_pan_card2" style="cursor:pointer;"></i>
               </span>
            </div>
            
        `;
         $('#extra_pan_card').append(html);
         $('#add_new_pan_card').addClass('hide');
      });


      // Second add button (delegated)
      $(document).on('click', '.add_new_pan_card2', function() {

         let html = `
             <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <input type="text" name="pan_card_3" class="form-control" id="pan_card" placeholder="Pan Card">
               <span>
                    <i class="fa fa-times pull-right text-danger remove_pan_card2" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
                </span>
            </div>
        `;

         // append inside same block's extra_pan_card2
         $(this).closest('.customer-field').find('.extra_pan_card2').append(html);

         // hide only the clicked plus icon
         $(this).addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_pan_card', function() {
         $('#pan_card_2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_pan_card').removeClass('hide');
      });

      $(document).on('click', '.remove_pan_card2', function() {
         $('#pan_card_3').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_pan_card2').removeClass('hide');
      });

      $(document).on('click', '#add_new_adhar_card', function() {
         var html = `
            <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <input type="text" name="adhar_card_2" class="form-control" id="adhar_card" placeholder="Adhar Card">
               <span>
                    <i class="fa fa-times pull-right text-danger remove_adhar_card" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
               </span>
            </div>

        `;
         $('#extra_adhar_card').append(html);
         $('#add_new_adhar_card').addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_adhar_card', function() {
         $('#adhar_card_2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_adhar_card').removeClass('hide');
      });


      $('#add_new_election_card').on('click', function() {
         var html = `
            <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <input type="text" name="election_card_2" class="form-control" id="election_card" placeholder="Election Card">
               <span>
                    <i class="fa fa-times pull-right text-danger remove_election_card" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
               </span>
            </div>

        `;
         $('#extra_election_card').append(html);
         $('#add_new_election_card').addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_election_card', function() {
         $('#election_card_2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_election_card').removeClass('hide');
      });

      $('#add_new_address').on('click', function() {
         var html = `
            <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <textarea name="address_2" class="form-control" id="address_2" rows="4" placeholder="Address 2"></textarea>
               <span>
                    <i class="fa fa-times pull-right text-danger remove_address" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
               </span>
            </div>

        `;
         $('#extra_address').append(html);
         $('#add_new_address').addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_address', function() {
         $('#address_2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_address').removeClass('hide');
      });
   });

   $(document).ready(function() {
      // Initialize counter based on existing rows
      let bankDetailCounter = $('#bank-details-container .bank-detail-row').length - 1;

      // If no rows exist, start from 0
      if (bankDetailCounter < 0) bankDetailCounter = 0;

      console.log('Initial counter:', bankDetailCounter); // Debug

      // Event delegation for adding rows
      $(document).on('click', '#add-bank-detail', function(e) {
         e.preventDefault();
         addBankDetailRow();
      });

      // Event delegation for removing rows
      $(document).on('click', '.btn-remove-bank-detail', function(e) {
         e.preventDefault();
         const rowId = $(this).data('row-id');
         removeBankDetailRow(rowId);
      });

      // Function to add a new bank detail row
      function addBankDetailRow() {
         // Increment counter
         bankDetailCounter++;
         console.log('Adding row, counter:', bankDetailCounter); // Debug

         // HTML template for bank details row with dynamic counter
         const bankDetailTemplate = `
            <div class="bank-detail-row" id="bank-detail-row-${bankDetailCounter}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <hr>
                            <h5>Payment Details ${bankDetailCounter + 1}</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name_${bankDetailCounter}" class="control-label">Bank Name</label>
                                <input type="text" id="bank_name_${bankDetailCounter}" name="bank_details[${bankDetailCounter}][bank_name]" class="form-control" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cheque_no_${bankDetailCounter}" class="control-label">Cheque No. / UTR</label>
                                <input type="text" id="cheque_no_${bankDetailCounter}" name="bank_details[${bankDetailCounter}][cheque_no]" class="form-control" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date_${bankDetailCounter}" class="control-label">Payment Date</label>
                                <input type="date" id="payment_date_${bankDetailCounter}" name="bank_details[${bankDetailCounter}][payment_date]" class="form-control" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount_${bankDetailCounter}" class="control-label">Amount</label>
                                <input type="number" id="amount_${bankDetailCounter}" name="bank_details[${bankDetailCounter}][amount]" class="form-control" value="" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-danger btn-remove-bank-detail" data-row-id="bank-detail-row-${bankDetailCounter}">
                                <i class="fa fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

         // Create new row
         const newRow = $(bankDetailTemplate);

         // Append to container
         $('#bank-details-container').append(newRow);

      }

      // Function to remove a bank detail row
      function removeBankDetailRow(rowId) {
         // Don't remove the first row if you want to keep at least one
         if (rowId !== 'bank-detail-row-0') {
            $('#' + rowId).remove();
            // Update payment detail numbers for better display
            updatePaymentNumbers();
         }
      }

      // Function to update payment numbers after removal
      function updatePaymentNumbers() {
         $('.bank-detail-row').each(function(index) {
            $(this).find('h5').text(`Payment Details ${index + 1}`);

            // Update all IDs and names in this row
            const currentRow = $(this);
            const rowIndex = index;

            // Update bank name field
            currentRow.find('input[name*="bank_name"]').attr({
               'name': `bank_details[${rowIndex}][bank_name]`,
               'id': `bank_name_${rowIndex}`
            });
            currentRow.find('label[for^="bank_name_"]').attr('for', `bank_name_${rowIndex}`);

            // Update cheque no field
            currentRow.find('input[name*="cheque_no"]').attr({
               'name': `bank_details[${rowIndex}][cheque_no]`,
               'id': `cheque_no_${rowIndex}`
            });
            currentRow.find('label[for^="cheque_no_"]').attr('for', `cheque_no_${rowIndex}`);

            // Update payment date field
            currentRow.find('input[name*="payment_date"]').attr({
               'name': `bank_details[${rowIndex}][payment_date]`,
               'id': `payment_date_${rowIndex}`
            });
            currentRow.find('label[for^="payment_date_"]').attr('for', `payment_date_${rowIndex}`);

            // Update amount field
            currentRow.find('input[name*="amount"]').attr({
               'name': `bank_details[${rowIndex}][amount]`,
               'id': `amount_${rowIndex}`
            });
            currentRow.find('label[for^="amount_"]').attr('for', `amount_${rowIndex}`);

            // Update remove button data attribute
            currentRow.find('.btn-remove-bank-detail').attr('data-row-id', `bank-detail-row-${rowIndex}`);

            // Update row ID itself
            currentRow.attr('id', `bank-detail-row-${rowIndex}`);
         });

         // Update the global counter after renumbering
         bankDetailCounter = $('.bank-detail-row').length - 1;
         console.log('Counter after update:', bankDetailCounter); // Debug
      }

      // Also update existing remove buttons to have data-row-id attribute
      // This ensures existing rows' remove buttons work properly
      $('.bank-detail-row').each(function(index) {
         const rowId = $(this).attr('id');
         $(this).find('.btn-remove-bank-detail').attr('data-row-id', rowId);
      });
   });

   $(document).on('click', '#add_new_driving_licence', function() {
         var html = `
            <div class="col-md-12 customer-field" style="margin:10px 0px; padding:0px !important;position:relative;">
                <input type="text" name="driving_licence_2" class="form-control" id="driving_licence" placeholder="Driving Licence">
               <span>
                    <i class="fa fa-times pull-right text-danger remove_driving_licence" title="Remove" style="cursor:pointer;position:absolute;top:-4px;right:-4px;"></i>
               </span>
            </div>

        `;
         $('#extra_driving_licence').append(html);
         $('#add_new_driving_licence').addClass('hide');
      });

      // Remove on click
      $(document).on('click', '.remove_driving_licence', function() {
         $('#driving_licence_2').val('');
         $(this).closest('.customer-field').remove();

         $('#add_new_driving_licence').removeClass('hide');
      });
</script>