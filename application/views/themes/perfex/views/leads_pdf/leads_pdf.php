<?php defined('BASEPATH') or exit('No direct script access allowed');

$formrowsinfo = '';

/* ===========================
   ADD CSS STYLES
=========================== */

$formrowsinfo .= '
<style>
.table * {
    font-size: 11px !important;
}
.table {
    table-layout: fixed !important;
    width: 100% !important;
    word-wrap: break-word !important;
}
.border_table, .border_tr, .border_td,
.border_td_left, .border_td_right {
    border: 1px solid #A4A4A4 !important;
}
.table th,
.table td {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: normal !important;
    padding: 2px !important;
}
.border_tr{
    text-align: center;
}
.border_td{
    text-align: center;
}
.border_td_left{
    text-align: left;
}
.border_td_right{
    text-align: right;
}
.thead-dark {
    background-color: #415164;
    color: #fff;
}
</style>
';

/* ===========================
   TABLE START
=========================== */
$organization_info = '';
$organization_info = '<div style="color:#424242;">';
$organization_info .= format_organization_info();
$organization_info .= '</div><br/><br/>';
$pdf->writeHTML($organization_info, true, false, false, false, '');
$formrowsinfo .= '
<table class="table border_table" width="100%" cellspacing="0" cellpadding="5">
<thead>



<tr class="border_tr thead-dark">
    <td width="4%"><b>#</b></td>
    <td width="14%"><b>Name</b></td>
    <td width="12%"><b>Phone</b></td>
    <td width="12%"><b>Alt Phone</b></td>
    <td width="10%"><b>Project</b></td>
    <td width="10%"><b>Status</b></td>
    <td width="10%"><b>Source</b></td>
    <td width="12%"><b>Assigned</b></td>
    <td width="10%"><b>Lead Value</b></td>
    <td width="10%"><b>Date Added</b></td>
</tr>

</thead>
<tbody>
';

/* ===========================
   TABLE BODY
=========================== */

if (!empty($lead_data)) {

    $i = 1;

    foreach ($lead_data as $row) {

        $assigned = trim(
            ($row['assigned_firstname'] ?? '') . ' ' .
                ($row['assigned_lastname'] ?? '')
        );

        $projects = !empty($row['projects'])
            ? get_projects($row['projects'])
            : '';

        $formrowsinfo .= '
        <tr class="border_tr">
            <td width="4%" class="border_td">' . $i++ . '</td>
            <td width="14%" class="border_td_left">' . $row['name'] . '</td>
            <td width="12%" class="border_td_left">' . $row['phonenumber'] . '</td>
            <td width="12%" class="border_td_left">' . $row['alt_phonenumber'] . '</td>
            <td width="10%" class="border_td_left">' . $projects . '</td>
            <td width="10%" class="border_td_left">' . $row['status_name'] . '</td>
            <td width="10%" class="border_td_left">' . $row['source_name'] . '</td>
            <td width="12%" class="border_td_left">' . $assigned . '</td>
            <td width="10%" class="border_td_right">' . $row['lead_value'] . '</td>
            <td width="10%" class="border_td">' . $row['dateadded'] . '</td>
        </tr>
        ';
    }
} else {

    $formrowsinfo .= '
    <tr>
        <td colspan="10" class="border_td">No Data Found</td>
    </tr>
    ';
}

$formrowsinfo .= '
</tbody>
</table>
';

/* ===========================
   PDF OUTPUT
=========================== */

$pdf->writeHTML($formrowsinfo, true, false, false, false, '');
