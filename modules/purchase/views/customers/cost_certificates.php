<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('purchase/customer/' . $customer_id), array('id' => 'cost-certificate-form')); ?>
                        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                        <input type="hidden" name="cost_certificates" value="1">
                        <input type="text" name="cost_certificate_name" class="form-control " placeholder="Cost Certificate Name">
                        <br><br>
                        <p>DATE: - <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value; ?>" /> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>" />, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>" /></p><br><br>
                        <p>This is to certify that MR. <strong style="font-weight: 700;"><?= $customer->company; ?></strong>  booked Unit <input type="text" name="unit_name" class="form-control input-sm" style="display:inline-block; width:auto;" /> in our Project Called KAUTILYA ONE54 Total cost of the unit is as on <input type="text" name="date2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value; ?>" /> day of <input type="text" name="month2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>" />, <input type="text" name="years2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>" /> mentioned below.</p><br><br><br>
                        <p>1. Basic Flat cost Rs. <input type="number" name="basic_cost" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p>
                        <p>2. Stamp Duty 4.9%, Rs. <input type="number" name="stamp_duty" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p>
                        <p>3. Maintenance Deposit Rs. <input type="number" name="maintenance_deposit" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p>
                        <p>4. GST 5% Rs. <input type="number" name="gst" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p>
                        <p>5. Registrastion Charge 1% Rs. <input type="number" name="registration_charge" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p><br><br><br>
                        <p>TOTAL COST (in Rs.) Rs. <input type="number" name="total_cost" class="form-control input-sm" style="display:inline-block; width:auto;" /> /-</p><br>
                        <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
                            <button class="btn btn-info only-save customer-form-submiter">
                                <?php echo _l('submit'); ?>
                            </button>

                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>