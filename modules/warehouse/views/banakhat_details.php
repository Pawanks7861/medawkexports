<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">

                    <div class="panel-body">
                        <h4 class="no-margin">Banakhat Details</h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-2">
                                <?php echo render_select('warehouse_id', $warehouses, array('warehouse_id', array('warehouse_code', 'warehouse_name')), 'warehouse_name',2); ?>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-banakhat-details table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Flat No</th>
                                        <th>Block</th>
                                        <th>Floor</th>
                                        <th>Carpet Area</th>
                                        <th>Wash Yard</th>
                                        <th>Balcony</th>
                                        <th>Total</th>
                                        <th>Open Terrace</th>
                                        <th>Undivided Land Share</th>
                                        <th>East</th>
                                        <th>West</th>
                                        <th>North</th>
                                        <th>South</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table_banakhat_details;
    (function($) {
        table_banakhat_details = $('.table-banakhat-details');

        var Params = {

            "warehouse_id": "[name='warehouse_id']",
        };

        initDataTable('.table-banakhat-details', admin_url + 'warehouse/table_banakhat_details', [], [], Params);


        $.each(Params, function(i, obj) {
            $('select' + obj).on('change', function() {
                table_banakhat_details.DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
            });
        });

    })(jQuery);
</script>