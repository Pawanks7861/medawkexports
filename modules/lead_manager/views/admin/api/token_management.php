<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

   <div class="content">

      <div class="row">

         <div class="col-md-12">

            <div class="panel_s">

               <div class="panel-body">

                  <div class="_buttons">

                     <a href="#" onclick="new_user_token(); return false" class="btn btn-info pull-left display-block"><?php echo _l('lm_new_user_api'); ?></a>

                  </div>

                  <div class="clearfix"></div>

                  <hr class="hr-panel-heading" />

                  <div class="clearfix"></div>

                  <table class="lm_apitable table dt-table">

                     <thead>

                        <th><?php echo _l('id'); ?></th>

                        <th><?php echo _l('lm_staff_name'); ?></th>

                        <th><?php echo _l('lm_token'); ?></th>

                        <th><?php echo _l('lm_expiration_date'); ?></th>

                        <th><?php echo _l('options'); ?></th>

                     </thead>

                     <tbody>

                        <?php foreach($user_api as $user){ ?>

                        <tr>

                           <td><?php echo addslashes($user['id']); ?></td>

                           <td><?php echo get_staff_full_name($user['staff_id']); ?></td>

                           <td><?php echo addslashes($user['token']); ?></td>

                           <td><?php echo addslashes($user['expiration_date']); ?></td>

                           <td>

                             <a href="#" onclick="edit_user_token(this,<?php echo addslashes($user['id']); ?>); return false"  data-staff_id="<?php echo addslashes($user['staff_id']); ?>" data-expiration_date="<?php echo addslashes($user['expiration_date']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>

                             <a href="<?php echo admin_url('lead_manager/api/delete_user_token/'.addslashes($user['id'])); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

                           </td>

                        </tr>

                        <?php } ?>

                     </tbody>

                  </table>

               </div>

            </div>

         </div>

      </div>

   </div>

</div>

<div class="modal fade" id="user_token_api" tabindex="-1" role="dialog">

   <div class="modal-dialog">

      <?php echo form_open(admin_url('lead_manager/api/user_token')); ?>

      <div class="modal-content">

         <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title">

               <span class="edit-title"><?php echo _l('edit_user_api'); ?></span>

               <span class="add-title"><?php echo _l('new_user_api'); ?></span>

            </h4>

         </div>

         <div class="modal-body">

            <div class="row">

               <div class="col-md-12">

                  <div id="additional"></div>

                  <?php echo render_select('staff_id',$staffs, ['id', 'name'], 'select Staff'); ?>

				  <?php echo render_datetime_input('expiration_date','expiration_date'); ?>

               </div>

               

            </div>

         </div>

         <div class="modal-footer">

            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>

         </div>

      </div><!-- /.modal-content -->

      <?php echo form_close(); ?>

   </div><!-- /.modal-dialog -->

</div><!-- /.modal -->

<?php init_tail(); ?>

<script src="<?php echo base_url('modules/lead_manager/assets/js/api_main.js'); ?>"></script>