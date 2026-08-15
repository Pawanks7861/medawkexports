<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('purchase/customer/' . $customer['userid']), array('id' => 'cost-certificate-form')); ?>
                        <input type="hidden" name="customer_id" value="<?php echo $customer['userid']; ?>">
                        <input type="hidden" name="cost_certificates" value="1">
                        <input type="hidden" name="certificates_master_id" value="<?php echo isset($master_id) ? $master_id : ''; ?>">
                        <input type="text" name="cost_certificate_name" class="form-control " placeholder="Cost Certificate Name" value="<?php echo isset($customer) ? $customer['cost_certificate_name'] : ''; ?>">
                        <br><br>
                        <?php $date_value = (isset($documentation) ? $documentation[0]['date'] : '') ?>
                        <?php $month_value = (isset($documentation) ? $documentation[0]['month'] : '') ?>
                        <?php $year_value = (isset($documentation) ? $documentation[0]['year'] : '') ?>
                        <?php $date_value2 = (isset($documentation) ? $documentation[0]['date2'] : '') ?>
                        <?php $month_value2 = (isset($documentation) ? $documentation[0]['month2'] : '') ?>
                        <?php $year_value2 = (isset($documentation) ? $documentation[0]['years2'] : '') ?>
                        <?php $unit_name = (isset($documentation) ? $documentation[0]['unit_name'] : '') ?>
                        <p>DATE: - <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value; ?>" /> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>" />, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>" /></p><br><br>
                        <p>This is to certify that MR. <strong style="font-weight: 700;"><?= $customer['company']; ?></strong> booked Unit <input type="text" name="unit_name" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Unit Name" value="<?php echo $unit_name; ?>" /> in our Project Called KAUTILYA ONE54 Total cost of the unit is as on <input type="text" name="date2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value2; ?>" /> day of <input type="text" name="month2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value2; ?>" />, <input type="text" name="years2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value2; ?>" /> mentioned below.</p><br><br><br>
                        <?php $basic_cost = (isset($documentation) ? $documentation[0]['basic_cost'] : '') ?>
                        <?php $stamp_duty = (isset($documentation) ? $documentation[0]['stamp_duty'] : '') ?>
                        <?php $maintenance_deposit = (isset($documentation) ? $documentation[0]['maintenance_deposit'] : '') ?>
                        <?php $gst = (isset($documentation) ? $documentation[0]['gst'] : '') ?>
                        <?php $registration_charge = (isset($documentation) ? $documentation[0]['registration_charge'] : '') ?>
                        <?php $total_cost = (isset($documentation) ? $documentation[0]['total_cost'] : '') ?>
                        <p>1. Basic Flat cost Rs. <input type="number" name="basic_cost" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $basic_cost; ?>"/> /-</p>
                        <p>2. Stamp Duty 4.9%, Rs. <input type="number" name="stamp_duty" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $stamp_duty; ?>"/> /-</p>
                        <p>3. Maintenance Deposit Rs. <input type="number" name="maintenance_deposit" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $maintenance_deposit; ?>"/> /-</p>
                        <p>4. GST 5% Rs. <input type="number" name="gst" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $gst; ?>"/> /-</p>
                        <p>5. Registrastion Charge 1% Rs. <input type="number" name="registration_charge" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $registration_charge; ?>"/> /-</p><br><br><br>
                        <p>TOTAL COST (in Rs.) Rs. <input type="number" name="total_cost" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_cost; ?>"/> /-</p><br>
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