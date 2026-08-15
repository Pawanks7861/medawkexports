<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="mtop5"><?php echo _l('documentation'); ?></h4>
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
               <?php if ($client->property_id == 1) { ?>

                  <li role="presentation" class="<?php if (!$this->input->get('tab')) {
                                                      echo 'active';
                                                   }; ?>">
                     <a href="#sale_agreement" aria-controls="sale_agreement" role="tab" data-toggle="tab">
                        <?php echo _l('Agreement Of Sale'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#cost_certificate" aria-controls="cost_certificate" role="tab" data-toggle="tab">
                        <?php echo _l('Cost Certificate'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#builder_noc" aria-controls="builder_noc" role="tab" data-toggle="tab">
                        <?php echo _l('Builder NOC'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#allotment_letter" aria-controls="allotment_letter" role="tab" data-toggle="tab">
                        <?php echo _l('Allotment Letter'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#sale_deed" aria-controls="sale_deed" role="tab" data-toggle="tab">
                        <?php echo _l('Sale Deed'); ?>
                     </a>
                  </li>
               <?php } elseif ($client->property_id == 2) { ?>
                  <li role="presentation" class="<?php if (!$this->input->get('tab')) {
                                                   echo 'active';
                                                }; ?>">
                  <a href="#sale_agreement2" aria-controls="sale_agreement2" role="tab" data-toggle="tab">
                     <?php echo _l('Agreement Of Sale'); ?>
                  </a>
                  </li>
                  <li role="presentation">
                     <a href="#cost_certificate2" aria-controls="cost_certificate2" role="tab" data-toggle="tab">
                        <?php echo _l('Cost Certificate'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#builder_noc2" aria-controls="builder_noc2" role="tab" data-toggle="tab">
                        <?php echo _l('Builder NOC'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#allotment_letter2" aria-controls="allotment_letter2" role="tab" data-toggle="tab">
                        <?php echo _l('Allotment Letter'); ?>
                     </a>
                  </li>
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="tab-content">
         <?php if ($client->property_id == 1) { ?>

            <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
                                                   echo ' active';
                                                }; ?>" id="sale_agreement">
               <!-- <a href="<?php echo admin_url('purchase/sale_agreements/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Agreement'); ?></a> -->

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Agreement Name'); ?></th>
                        <th><?php echo _l('Agreement Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($sale_agreements) && count($sale_agreements) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($sale_agreements as $agreement) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo pur_html_entity_decode($agreement['agreement_name']); ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($agreement['create_at']); ?>"><?php echo date('d M, Y', strtotime($agreement['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_sale_agreements/' . $agreement['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <!-- <a href="<?php echo admin_url('purchase/delete_sale_agreement/' . $agreement['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a> -->
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_agreement_pdf/' . $agreement['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_agreement_pdf/' . $agreement['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/sale_agreement_pdf/' . $agreement['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/sale_agreement_pdf/' . $agreement['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>


            </div>

            <div role="tabpanel" class="tab-pane" id="cost_certificate">
               <a href="<?php echo admin_url('purchase/cost_certificates/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Certificate'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Certificate Name'); ?></th>
                        <th><?php echo _l('Certificate Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($cost_certificates) && count($cost_certificates) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($cost_certificates as $certificate) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $certificate['cost_certificate_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($certificate['create_at']); ?>"><?php echo date('d M, Y', strtotime($certificate['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_cost_certificates/' . $certificate['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_cost_certificates/' . $certificate['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>


            <div role="tabpanel" class="tab-pane" id="builder_noc">
               <a href="<?php echo admin_url('purchase/bulder_noc/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Builder NOC'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('NOC Name'); ?></th>
                        <th><?php echo _l('NOC Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($builder_noc) && count($builder_noc) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($builder_noc as $noc) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $noc['builder_noc_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($noc['create_at']); ?>"><?php echo date('d M, Y', strtotime($noc['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_builder_noc/' . $noc['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_builder_noc/' . $noc['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="allotment_letter">
               <a href="<?php echo admin_url('purchase/allotment_letter/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Allotment Letter'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Allotment Letter Name'); ?></th>
                        <th><?php echo _l('Latter Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($alloment_letter) && count($alloment_letter) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($alloment_letter as $letter) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $letter['allotment_letter_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($letter['create_at']); ?>"><?php echo date('d M, Y', strtotime($letter['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_allotment_letter/' . $letter['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_allotment_letter/' . $letter['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="sale_deed">

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Sale Deed Name'); ?></th>
                        <th><?php echo _l('Deed Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($sale_deed) && count($sale_deed) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($sale_deed as $deed) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?= $deed['sale_deed_name'];?></td>
                              <td data-order="<?php echo pur_html_entity_decode($deed['create_at']); ?>"><?php echo date('d M, Y', strtotime($deed['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_sale_deed/' . $deed['customer_id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <!-- <a href="<?php echo admin_url('purchase/delete_sale_deed/' . $deed['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a> -->
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_deed_pdf/' . $deed['customer_id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_deed_pdf/' . $deed['customer_id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/sale_deed_pdf/' . $deed['customer_id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/sale_deed_pdf/' . $deed['customer_id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
         <?php } elseif ($client->property_id == 2) { ?>
            <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
                                                   echo ' active';
                                                }; ?>" id="sale_agreement2">
               <!-- <a href="<?php echo admin_url('purchase/sale_agreements2/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Agreement'); ?></a> -->

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Agreement Name'); ?></th>
                        <th><?php echo _l('Agreement Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($sale_agreements) && count($sale_agreements) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($sale_agreements as $agreement) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo pur_html_entity_decode($agreement['agreement_name']); ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($agreement['create_at']); ?>"><?php echo date('d M, Y', strtotime($agreement['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_sale_agreements2/' . $agreement['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <!-- <a href="<?php echo admin_url('purchase/delete_sale_agreement/' . $agreement['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a> -->
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_agreement2_pdf/' . $agreement['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/sale_agreement2_pdf/' . $agreement['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/sale_agreement2_pdf/' . $agreement['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/sale_agreement2_pdf/' . $agreement['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>


            </div>

            <div role="tabpanel" class="tab-pane" id="cost_certificate2">
               <a href="<?php echo admin_url('purchase/cost_certificates/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Certificate'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Certificate Name'); ?></th>
                        <th><?php echo _l('Certificate Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($cost_certificates) && count($cost_certificates) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($cost_certificates as $certificate) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $certificate['cost_certificate_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($certificate['create_at']); ?>"><?php echo date('d M, Y', strtotime($certificate['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_cost_certificates/' . $certificate['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_cost_certificates/' . $certificate['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/cost_certificate_pdf/' . $certificate['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>


            <div role="tabpanel" class="tab-pane" id="builder_noc2">
               <a href="<?php echo admin_url('purchase/bulder_noc/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Builder NOC'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('NOC Name'); ?></th>
                        <th><?php echo _l('NOC Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($builder_noc) && count($builder_noc) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($builder_noc as $noc) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $noc['builder_noc_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($noc['create_at']); ?>"><?php echo date('d M, Y', strtotime($noc['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_builder_noc/' . $noc['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_builder_noc/' . $noc['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/builder_noc_pdf/' . $noc['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="allotment_letter2">
               <a href="<?php echo admin_url('purchase/allotment_letter/' . $client->userid); ?>" class="btn btn-info new-contact mbot25 pull-right"><?php echo _l('New Allotment Letter'); ?></a>

               <table class="table dt-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th><?php echo _l('Allotment Letter Name'); ?></th>
                        <th><?php echo _l('Latter Date'); ?></th>
                        <th class="text-right"><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (isset($alloment_letter) && count($alloment_letter) > 0) {
                        $sr = 1; ?>
                        <?php foreach ($alloment_letter as $letter) { ?>
                           <tr>
                              <td><?php echo $sr++; ?></td>
                              <td><?php echo $letter['allotment_letter_name']; ?></td>
                              <td data-order="<?php echo pur_html_entity_decode($letter['create_at']); ?>"><?php echo date('d M, Y', strtotime($letter['create_at'])); ?></td>
                              <td class="text-right">
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('purchase/edit_allotment_letter/' . $letter['id']); ?>" class="btn btn-default btn-icon" style="padding: 10px !important"><i class="fa fa-pencil-square"></i></a>
                                    <a href="<?php echo admin_url('purchase/delete_allotment_letter/' . $letter['id']); ?>" class="btn btn-danger _delete btn-icon" style="padding: 10px !important"><i class="fa fa-remove"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 7px !important"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id']); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('purchase/allotment_letter_pdf/' . $letter['id'] . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>

                                 </div>
                              </td>
                           </tr>
                        <?php } ?>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
         <?php } ?>

      </div>
   </div>
   <?php echo form_close(); ?>
</div>