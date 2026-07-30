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
                        <input type="hidden" name="allotment_letter" value="1">
                        <input type="hidden" name="allotment_master_id" value="<?php echo isset($master_id) ? $master_id : ''; ?>">
                        <input type="text" name="allotment_letter_name" class="form-control " placeholder="Allotment Letter Name" value="<?php echo isset($customer) ? $customer['allotment_letter_name'] : ''; ?>">
                        <br>
                        <br>
                         <?php $date_value = (isset($documentation) ? $documentation[0]['date'] : '') ?>
                        <?php $month_value = (isset($documentation) ? $documentation[0]['month'] : '') ?>
                        <?php $year_value = (isset($documentation) ? $documentation[0]['year'] : '') ?>
                        <p>Date : <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value; ?>" /> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>"/>, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>"/></p>
                        <p>RERA Registration No.:</p>
                        <p>PR/GJ/AHMEDABAD/AHMEDABAD CITY/AUDA/MAA10980/291122</p>
                        <br>
                        <p>PROVISIONAL ALLOTMENT LETTER</p>
                        <p>To,</p>
                        <p>MR. <strong style="font-weight: 700;"><?= $customer['company']; ?></strong>
                        </p>
                        <?php $unit_no_value = (isset($documentation) ? $documentation[0]['unit_no'] : '') ?>
                        <?php $carpet_area_value = (isset($documentation) ? $documentation[0]['carpet_area'] : '') ?>
                        <?php $balcony_wash_area_value = (isset($documentation) ? $documentation[0]['balcony_wash_area'] : '') ?>
                        <?php $total_carpet_area_value = (isset($documentation) ? $documentation[0]['total_carpet_area'] : '') ?>
                        <?php $undivided_share_value = (isset($documentation) ? $documentation[0]['undivided_share'] : '') ?>
                        <p>Residential Unit No. <input type="text" name="unit_no" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $unit_no_value; ?>" />, having following details :-</p>
                        <p>Carpet area <input type="text" name="carpet_area" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $carpet_area_value; ?>"/> Sq.mtrs.</p>
                        <p>Balcony & Wash Area Carpet area <input type="text" name="balcony_wash_area" class="form-control input-sm" style="display:inline-block; width:auto;"  value="<?php echo $balcony_wash_area_value; ?>"/> Sq.mtrs.</p>
                        <p>Total Carpet area <input type="text" name="total_carpet_area" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_carpet_area_value; ?>"/> Sq.mtrs.</p>
                        <p>Undivided share of land <input type="text" name="undivided_share" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $undivided_share_value; ?>"/> Sq.mtrs.</p>
                        <br>
                        <p>In the scheme known as “KAUTILYA ONE-54 ”, constructed on the Non-Agriculture Land bearing Final Plot No. 321 & 322 of Town Planning Scheme No. 76/B allotted in lieu of Revenue Survey No. 875/3 & 875/4 situate, lying and being at Mouje : CHANDKHEDA, Taluka : Sabarmati in the Registration District - Ahmedabad and Sub - District of Ahmedabad - 13 (Sabarmati) and bounded as follows</p>
                        <br>
                        <?php $facing_value = (isset($documentation) ? $documentation[0]['facing'] : '') ?>
                        <p>Facing <input type="text" name="facing" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $facing_value; ?>"/></p>

                        <br>
                        <p>Above said property has been provisionally allotted to you subject to below referred terms and conditions.</p>
                        <br>
                        <?php $making_payment_value = (isset($documentation) ? $documentation[0]['making_payment'] : '') ?>
                        <?php $total_sale_consideration_value = (isset($documentation) ? $documentation[0]['total_sale_consideration'] : '') ?>
                        <p>On making payment of Rs. <input type="text" name="making_payment" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $making_payment_value; ?>"/>/- Only out of Total sale consideration of Rs. <input type="text" name="total_sale_consideration" class="form-control input-sm" style="display:inline-block; width:auto;" value="<?php echo $total_sale_consideration_value; ?>" />/- Agreement for sale shall be executed in favor of allottee only.</p>
                        <p>On default of making total payment booking shall consider as cancel and amount of 10 % shall be forfeited and remaining amount shall be refund within 30 days.</p>
                        <br>
                        <p>The other charges like Maintenance Deposits, Maintenance Charges, Electricity Charges, AMC Charges, Legal Charges, Value Added Tax, Service Tax / GST, Stamp Duty, Registration Charges, Advocate Fees any other Government levies or any other charges as decided on or before possession, will be recovered from you as and when it will be finalized.</p>
                        <br>
                        <p>Ownership rights shall be transferred only upon the execution of full and final Registered Deed of Conveyance / Sale Deed in your favor. Rights under this Allotment Letter are non-transferable without the prior written consent of KAUTILYA ONE-54.</p>
                        <br>
                        <p>We are allotting you the said flat C-503 subject to receipt of the payment equal to 10 % of total price. Kindly make the payment as soon as possible to confirm the allotment.</p>
                        <br>
                        <p>For I/We Admit, accept and acknoeledge</p>
                        <br>
                        <p>KAUTILYA DEVELOPERS (Member/s)</p>
                        <br>
                        <p>For,</p>
                        <p>KAUTILYA DEVELOPERS</p>
                        <br>
                        <br>
                        <p>I / We admit, accept and acknowledge.</p>
                        <br>
                        <br>
                        <p>(Member/s)</p>
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