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
                        <input type="hidden" name="builder_noc" value="1">
                        <input type="hidden" name="builder_master_id" value="<?php echo isset($master_id) ? $master_id : ''; ?>">
                        <input type="text" name="builder_noc_name" class="form-control " placeholder="Cost Certificate Name" value="<?php echo isset($customer) ? $customer['builder_noc_name'] : ''; ?>">
                        <br>
                        <br>
                        <br>
                        <p>To,</p>
                        <p>Housing Development Finance Corporation Bank Limited,</p>
                        <p>Ahmedabad</p>
                        <?php $date_value = (isset($documentation) ? $documentation[0]['date'] : '') ?>
                        <?php $month_value = (isset($documentation) ? $documentation[0]['month'] : '') ?>
                        <?php $year_value = (isset($documentation) ? $documentation[0]['year'] : '') ?>
                        <p>Date: <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date"  value="<?php echo $date_value; ?>"/> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>" />, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>" />
                        </p>
                        <br>
                        <br>
                        <p>Dear Sirs,</p>
                        <br>
                        <?php $unit_no_value = (isset($documentation) ? $documentation[0]['unit_no'] : '') ?>
                        <?php $bn_floor_no_value = (isset($documentation) ? $documentation[0]['bn_floor_no'] : '') ?>
                        <?php $scheme_value= (isset($documentation) ? $documentation[0]['scheme'] : '') ?>
                        <p>Ref: Loan to Mr./Ms. <strong style="font-weight: 700;"><?= $customer['company']; ?></strong> Flat No. / Unit No. <input type="text" name="unit_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $unit_no_value; ?>"/>on <input type="text" name="bn_floor_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $bn_floor_no_value; ?>" /> floor in the Scheme <input type="text" name="scheme" class="form-control input-sm" style="display:inline-block; width:auto;"  value="<?php echo $scheme_value; ?>"/>
                        </p>
                        <br>
                        <?php $project_name_value = (isset($documentation) ? $documentation[0]['project_name'] : '') ?>
                        <?php $rs_no_value = (isset($documentation) ? $documentation[0]['rs_no'] : '') ?>
                        <?php $tp_novalue = (isset($documentation) ? $documentation[0]['tp_no'] : '') ?>
                        <?php $fp_novalue = (isset($documentation) ? $documentation[0]['fp_no'] : '') ?>
                        <?php $total_no_of_flats_value = (isset($documentation) ? $documentation[0]['total_no_of_flats'] : '') ?>
                        <p>This is to confirm that we have undertaken a Project called <input type="text" name="project_name" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $project_name_value; ?>"/>constructed on land bearing R.S. No <input type="text" name="rs_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $rs_no_value; ?>" />T.P. No. <input type="text" name="tp_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $tp_novalue; ?>"/> F.P. No. <input type="text" name="fp_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $fp_novalue; ?>"/> having total number of <input type="text" name="total_no_of_flats" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_no_of_flats_value; ?>"/> flats/duplex/tenaments/plot. </p>
                        <br>
                        <?php $unit_no2_value = (isset($documentation) ? $documentation[0]['unit_no2'] : '') ?>
                        <?php $total_consideration_value = (isset($documentation) ? $documentation[0]['total_consideration'] : '') ?>
                        <?php $date2_value = (isset($documentation) ? $documentation[0]['date2'] : '') ?>
                        <?php $month2_value = (isset($documentation) ? $documentation[0]['month2'] : '') ?>
                        <?php $years2_value = (isset($documentation) ? $documentation[0]['years2'] : '') ?>
                        <p>This is to confirm that in the above mentioned scheme, the Flat No. / Unit No <input type="text" name="unit_no2" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $unit_no2_value; ?>" />has been allocated to the above purchaser for a total consideration of Rs. <input type="text" name="total_consideration" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_consideration_value; ?>"/> vide Agreement for Sale dated <input type="text" name="date2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date"  value="<?php echo $date2_value; ?>"/> day of <input type="text" name="month2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month2_value; ?>"/>, <input type="text" name="years2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $years2_value; ?>"/>. </p>
                        <br>
                        <p>We confirm that we have obtained necessary permission/approvals/sanctions for construction of the said Project from all the concerned competent authorities and the construction of the Project as well as of the flats/duplex/tenaments/plot in the Project in accordance with the approved plans.</p>
                        <br>
                        <?php $sanction_letter_value = (isset($documentation) ? $documentation[0]['sanction_letter'] : '') ?>
                        <?php $total_project_cost_value = (isset($documentation) ? $documentation[0]['total_project_cost'] : '') ?>
                        <?php $date3_value = (isset($documentation) ? $documentation[0]['date3'] : '') ?>
                        <?php $month3_value = (isset($documentation) ? $documentation[0]['month3'] : '') ?>
                        <?php $years3_value = (isset($documentation) ? $documentation[0]['years3'] : '') ?>
                        <p>We would like to confirm that we have taken a construction finance on the said Project of Rs. <input type="text" name="total_project_cost" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_project_cost_value; ?>" />Cr. From <input type="text" name="sanction_letter" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $sanction_letter_value; ?>"/> via Sanction Letter dated <input type="text" name="date3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date3_value; ?>"/> day of <input type="text" name="month3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month3_value; ?>" />, <input type="text" name="years3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $years3_value; ?>"/>. </p>
                        <br>
                        <?php $subject_to_charge_value = (isset($documentation) ? $documentation[0]['subject_to_charge'] : '') ?>
                        <?php $provisional_noc_value = (isset($documentation) ? $documentation[0]['provisional_noc'] : '') ?>
                        <?php $date4_value = (isset($documentation) ? $documentation[0]['date4'] : '') ?>
                        <?php $month4_value = (isset($documentation) ? $documentation[0]['month4'] : '') ?>
                        <?php $years4_value = (isset($documentation) ? $documentation[0]['years4'] : '') ?>
                        <p>We hereby also confirm that thesaid flats/duplex/tenaments/plot is subject to the charge of <input type="text" name="subject_to_charge" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $subject_to_charge_value; ?>"/> and as per the Provisional NOC of <input type="text" name="provisional_noc" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $provisional_noc_value; ?>"/>dated <input type="text" name="date4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date4_value; ?>" /> day of <input type="text" name="month4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month4_value; ?>"/>, <input type="text" name="years4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year"  value="<?php echo $years4_value; ?>"/>, the charge would be released after the payment of the consideration amount in the account mentioned as per Provisional NOC/ Sanction Letter. </p>
                        <br>
                        <p>We have no objection to giving your loans to the buyers in the above stated Project his/her/their mortgaging the said flats/duplex/tenaments/plot by way of security for repayment, notwithstanding anything to the contrary contained in the the Agreement.</p>
                        <br>
                        <p>We also undertake to inform, and give proper Notice to the Co Operative Housing Society as and when formed, above the Unit being so mortgaged. We shall not cancel re-allot, or transfer the said plot here after without HDFC's consent.</p>
                        <br>
                        <br>
                        <p>Yours faithfully.</p>
                        <br>
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