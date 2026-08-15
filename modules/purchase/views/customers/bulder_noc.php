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
                        <input type="hidden" name="builder_noc" value="1">
                        <input type="text" name="builder_noc_name" class="form-control " placeholder="Builder NOC Name">
                        <br>
                        <br>
                        <br>
                        <p>To,</p>
                        <p>Housing Development Finance Corporation Bank Limited,</p>
                        <p>Ahmedabad</p>
                        <p>Date: <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" /> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month"  />, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year"  />
                        </p>
                        <br>
                        <br>
                        <p>Dear Sirs,</p>
                        <br>
                        <p>Ref: Loan to Mr./Ms. <strong style="font-weight: 700;"><?= $customer->company; ?></strong> Flat No. / Unit No. <input type="text" name="unit_no" class="form-control input-sm" style="display:inline-block; width:auto;" />on <input type="text" name="bn_floor_no" class="form-control input-sm" style="display:inline-block; width:auto;" /> floor in the Scheme <input type="text" name="scheme" class="form-control input-sm" style="display:inline-block; width:auto;" />
                        </p>
                        <br>
                        <p>This is to confirm that we have undertaken a Project called <input type="text" name="project_name" class="form-control input-sm" style="display:inline-block; width:auto;" />constructed on land bearing R.S. No <input type="text" name="rs_no" class="form-control input-sm" style="display:inline-block; width:auto;" />T.P. No. <input type="text" name="tp_no" class="form-control input-sm" style="display:inline-block; width:auto;" /> F.P. No. <input type="text" name="fp_no" class="form-control input-sm" style="display:inline-block; width:auto;" /> having total number of <input type="text" name="total_no_of_flats" class="form-control input-sm" style="display:inline-block; width:auto;" /> flats/duplex/tenaments/plot. </p>
                        <br>
                        <p>This is to confirm that in the above mentioned scheme, the Flat No. / Unit No <input type="text" name="unit_no2" class="form-control input-sm" style="display:inline-block; width:auto;" />has been allocated to the above purchaser for a total consideration of Rs. <input type="text" name="total_consideration" class="form-control input-sm" style="display:inline-block; width:auto;" /> vide Agreement for Sale dated <input type="text" name="date2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" /> day of <input type="text" name="month2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month"  />, <input type="text" name="years2" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year"  />. </p>
                        <br>
                        <p>We confirm that we have obtained necessary permission/approvals/sanctions for construction of the said Project from all the concerned competent authorities and the construction of the Project as well as of the flats/duplex/tenaments/plot in the Project in accordance with the approved plans.</p>
                        <br>
                        <p>We would like to confirm that we have taken a construction finance on the said Project of Rs. <input type="text" name="total_project_cost" class="form-control input-sm" style="display:inline-block; width:auto;" />Cr. From <input type="text" name="sanction_letter" class="form-control input-sm" style="display:inline-block; width:auto;" /> via Sanction Letter dated <input type="text" name="date3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" /> day of <input type="text" name="month3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month"  />, <input type="text" name="years3" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year"  />. </p>
                        <br>
                        <p>We hereby also confirm that thesaid flats/duplex/tenaments/plot is subject to the charge of <input type="text" name="subject_to_charge" class="form-control input-sm" style="display:inline-block; width:auto;" /> and as per the Provisional NOC of <input type="text" name="provisional_noc" class="form-control input-sm" style="display:inline-block; width:auto;" />dated <input type="text" name="date4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" /> day of <input type="text" name="month4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month"  />, <input type="text" name="years4" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year"  />, the charge would be released after the payment of the consideration amount in the account mentioned as per Provisional NOC/ Sanction Letter. </p>
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