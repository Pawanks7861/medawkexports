<?php

defined('BASEPATH') or exit('No direct script access allowed');

$organization_info = '';
$organization_info = '<div style="color:#424242;">';
$organization_info .= format_organization_info();
$organization_info .= '</div><br/><br/>';
$pdf->writeHTML($organization_info, true, false, false, false, '');

$formbasicinfo = '';
$formbasicinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$formbasicinfo .= '<tbody>';
$formbasicinfo .= '
<tr style="font-size:20px;" colspan="4">
    <td align="center"><b>DAILY PROGRESS REPORT</b></td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('form_settings_subject') . '</b></td>
    <td width="40%;" align="left">' . $form_data->subject . '</td>
    <td width="20%;" align="left"><b>' . _l('form_settings_assign_to') . '</b></td>
    <td width="20%;" align="left">' . get_staff_full_name($form_data->assigned) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('project') . '</b></td>
    <td width="40%;" align="left">' . get_project_name_by_id($form_data->project_id) . '</td>
    <td width="20%;" align="left"><b>Due Date</b></td>
    <td width="20%;" align="left">' . date('d M, Y', strtotime($form_data->duedate)) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('department') . '</b></td>
    <td width="40%;" align="left">' . get_staff_department_name($form_data->department) . '</td>
    <td width="20%;" align="left"><b>Priority</b></td>
    <td width="20%;" align="left">' . get_priority_name($form_data->priority) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>DPR Date</b></td>
    <td width="40%;" align="left">' . date('d M, Y', strtotime($form_data->date)) . '</td>
    <td width="20%;" align="left"><b>Client</b></td>
    <td width="20%;" align="left">' . get_company_name($form_basic_info->client_id) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Consultant</b></td>
    <td width="40%;" align="left">' . $form_basic_info->consultant . '</td>
    <td width="20%;" align="left"><b>PMC</b></td>
    <td width="20%;" align="left">' . $form_basic_info->pmc . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Weather</b></td>
    <td width="10%;" align="left">' . $form_basic_info->weather . '</td>
    <td width="20%;" align="left"><b>Work Stop?</b></td>
    <td width="10%;" align="left">' . $form_basic_info->work_stop . '</td>
    <td width="20%;" align="left"><b>Contractor</b></td>
    <td width="20%;" align="left">' . $form_basic_info->contractor . '</td>
</tr>
';
$formbasicinfo .= '</tbody>';
$formbasicinfo .= '</table>';

$pdf->writeHTML($formbasicinfo, true, false, false, false, '');

$formrowsinfo = '';
$formrowsinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$formrowsinfo .= '<thead>';  // Changed from tbody to thead for header rows
$formrowsinfo .= '
<tr style="font-size:20px;">
    <td colspan="11" align="center"><b>ACTIVITY WITH LOCATION & OUTPUT</b></td>
</tr>
<tr style="font-size:11px;">
    <td rowspan="2" width="11%;" align="center"><b>Location</b></td>
    <td rowspan="2" width="11%;" align="center"><b>Agency</b></td>
    <td rowspan="2" width="10%;" align="center"><b>Type</b></td>
    <td rowspan="2" width="16%;" align="center"><b>Remarks</b></td> 
    <td colspan="2" width="20%;" align="center"><b>Work Progress</b></td>
    <td colspan="3" width="19%;" align="center"><b>Type Of Manpower</b></td>
    <td rowspan="2" width="8%;" align="center"><b>Machinery</b></td>
    <td rowspan="2" width="5%;" align="center"><b>Total</b></td> 
</tr>
<tr style="font-size:11px;">
    <td width="10%;" align="center"><b>Work Execute (smt/Rmt/Cmt)</b></td>
    <td width="10%;" align="center"><b>Material Consumption</b></td>
    <td width="6%;" align="center"><b>Skilled</b></td>
    <td width="8%;" align="center"><b>Unskilled</b></td>
    <td width="5%;" align="center"><b>Total</b></td>
</tr>
';
$formrowsinfo .= '</thead>';

$formrowsinfo .= '<tbody>';  // Start tbody for data rows
if (!empty($form_rows_info)) {
    foreach ($form_rows_info as $key => $value) {
        $formrowsinfo .= '
            <tr style="font-size:11px;">
                <td align="left" width="11%;">' . $value['location'] . '</td>
                <td align="left" width="11%;">' . get_vendor_company_name($value['agency']) . '</td>
                <td align="left" width="10%;">' . get_progress_report_type_name($value['type']) . '</td>
                <td align="left" width="16%;">' . $value['sub_type'] . '</td> 
                <td align="left" width="10%;">' . $value['work_execute'] . '</td>
                <td align="left" width="10%;">' . $value['material_consumption'] . '</td>
                <td align="center" width="6%;">' . $value['male'] . '</td>
                <td align="center" width="8%;">' . $value['female'] . '</td>
                <td align="center" width="5%;">' . $value['total'] . '</td>
                <td align="left" width="8%;">' . get_progress_report_machinary_name($value['machinery']) . '</td>
                <td align="center" width="5%;">' . $value['total_machinery'] . '</td> 
            </tr>';
    }
}
$formrowsinfo .= '</tbody>';
$formrowsinfo .= '</table>';



$pdf->SetAutoPageBreak(true, 20);
$pdf->writeHTML($formrowsinfo, true, false, false, false, '');


$rcmplanttable = '';
$rcmplanttable .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$rcmplanttable .= '<thead>';  // Changed from tbody to thead for header rows
$rcmplanttable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>RMC PLANT</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Challan No</b></td>
        <td align="center"><b>Grade</b></td>
        <td align="center"><b>Structure Work</b></td>
        <td align="center"><b>Quantity(CMT)</b></td>
    </tr>
    
    ';
$rcmplanttable .= '</thead>';

$rcmplanttable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_rmc_plant)) {
    foreach ($form_rmc_plant as $key => $value) {
        $rcmplanttable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['challan'] != '' ? $value['challan'] : '') . '</td>
                    <td align="center" >' . ($value['grade'] != '' ? get_rmc_grade_name($value['grade']) : '') . '</td>
                    <td align="center" >' . ($value['structure'] != '' ? $value['structure'] : '') . '</td>
                    <td align="center" >' . ($value['quantity'] != '' ? $value['quantity'] : '') . '</td>
                </tr>';
    }
}
$rcmplanttable .= '</tbody>';
$rcmplanttable .= '</table>';

$materialinwardtable = '';
$materialinwardtable = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$materialinwardtable .= '<thead>';  // Changed from tbody to thead for header rows
$materialinwardtable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>MATERIAL INWARD</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Challan No/ Truck No</b></td>
        <td align="center"><b>Supplier Name</b></td>
        <td align="center"><b>Material Description</b></td>
        <td align="center"><b>Total</b></td>
    </tr>
    
    ';
$materialinwardtable .= '</thead>';

$materialinwardtable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_material_inward)) {
    foreach ($form_material_inward as $key => $value) {
        $materialinwardtable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['challan'] != '' ? $value['challan'] : '') . '</td>
                    <td align="center" >' . ($value['supplier'] != '' ? $value['supplier'] : '') . '</td>
                    <td align="center" >' . ($value['material_description'] != '' ? $value['material_description'] : '') . '</td>
                    <td align="center" >' . ($value['total'] != '' ? $value['total'] : '') . '</td>
                </tr>';
    }
}
$materialinwardtable .= '</tbody>';
$materialinwardtable .= '</table>';


$deprtmenttable = '';
$deprtmenttable = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$deprtmenttable .= '<thead>';  // Changed from tbody to thead for header rows
$deprtmenttable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>DEPARTMENT LABOUR</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Name</b></td>
        <td align="center"><b>Attendance</b></td>
        <td align="center"><b>Over Time</b></td>
        <td align="center"><b>Kharchi</b></td>
    </tr>
    ';
$deprtmenttable .= '</thead>';

$deprtmenttable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_dept_labour)) {
    foreach ($form_dept_labour as $key => $value) {
        $deprtmenttable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['staff'] != '' ? get_dept_labour_name($value['staff']) : '') . '</td>
                    <td align="center" >' . ($value['attendance'] != '' ? $value['attendance'] : '') . '</td>
                    <td align="center" >' . ($value['over_time'] != '' ? $value['over_time'] : '') . '</td>
                    <td align="center" >' . ($value['kharchi'] != '' ? $value['kharchi'] : '') . '</td>
                </tr>';
    }
}
$deprtmenttable .= '</tbody>';
$deprtmenttable .= '</table>';

$cementtable = '';
$cementtable = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$cementtable .= '<thead>';
$cementtable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>ON RACK CEMENT BAG</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Inward Inventory</b></td>
        <td align="center"><b>Todays usage</b></td>
        <td align="center"><b>Total Remaining</b></td>
        <td align="center"><b>Notes</b></td>
    </tr>
    ';
$cementtable .= '</thead>';

$cementtable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_cement_rack)) {
    foreach ($form_cement_rack as $key => $value) {
        $cementtable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['inward_inventory'] != '' ? $value['inward_inventory'] : '') . '</td>
                    <td align="center" >' . ($value['today_usage'] != '' ? $value['today_usage'] : '') . '</td>
                    <td align="center" >' . ($value['remaining_cement'] != '' ? $value['remaining_cement'] : '') . '</td>
                    <td align="center" >' . ($value['notes'] != '' ? $value['notes'] : '') . '</td>
                </tr>';
    }
}
$cementtable .= '</tbody>';
$cementtable .= '</table>';

$blocktable = '';
$blocktable = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$blocktable .= '<thead>';
$blocktable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>Block mortar joint</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Inward Inventory</b></td>
        <td align="center"><b>Todays usage</b></td>
        <td align="center"><b>Total Remaining</b></td>
        <td align="center"><b>Notes</b></td>
    </tr>
    ';
$blocktable .= '</thead>';

$blocktable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_block_mortar_joint)) {
    foreach ($form_block_mortar_joint as $key => $value) {
        $blocktable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['inward_inventory_bmj'] != '' ? $value['inward_inventory_bmj'] : '') . '</td>
                    <td align="center" >' . ($value['today_usage_bmj'] != '' ? $value['today_usage_bmj'] : '') . '</td>
                    <td align="center" >' . ($value['remaining_cement_bmj'] != '' ? $value['remaining_cement_bmj'] : '') . '</td>
                    <td align="center" >' . ($value['notes_bmj'] != '' ? $value['notes_bmj'] : '') . '</td>
                </tr>';
    }
}
$blocktable .= '</tbody>';
$blocktable .= '</table>';


$tiletable = '';
$tiletable = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';
$tiletable .= '<thead>';
$tiletable .= '
    <tr style="font-size:20px;">
        <td colspan="5" align="center"><b>Tile Adhesive</b></td>
    </tr>
    <tr style="font-size:11px;">
        <td align="center"><b>Sr. No.</b></td>
        <td align="center"><b>Inward Inventory</b></td>
        <td align="center"><b>Todays usage</b></td>
        <td align="center"><b>Total Remaining</b></td>
        <td align="center"><b>Notes</b></td>
    </tr>
    ';
$tiletable .= '</thead>';

$tiletable .= '<tbody>';  // Start tbody for data rows
if (!empty($form_tile)) {
    foreach ($form_tile as $key => $value) {
        $tiletable .= '
                <tr style="font-size:11px;">
                    <td align="center" >' . ($key + 1) . '</td>
                    <td align="center" >' . ($value['inward_inventory_ta'] != '' ? $value['inward_inventory_ta'] : '') . '</td>
                    <td align="center" >' . ($value['today_usage_ta'] != '' ? $value['today_usage_ta'] : '') . '</td>
                    <td align="center" >' . ($value['remaining_cement_ta'] != '' ? $value['remaining_cement_ta'] : '') . '</td>
                    <td align="center" >' . ($value['notes_ta'] != '' ? $value['notes_ta'] : '') . '</td>
                </tr>';
    }
}
$tiletable .= '</tbody>';
$tiletable .= '</table>';

$cuplertable = '';
$cuplertable .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';

$cuplertable .= '<thead>';
$cuplertable .= '
<tr style="font-size:20px;">
    <td colspan="6" align="center"><b>Coupler</b></td>
</tr>
<tr style="font-size:11px;">
    <td align="center"><b>Sr. No.</b></td>
    <td align="center"><b>Inward Inventory</b></td>
    <td align="center"><b>Type</b></td>
    <td align="center"><b>Todays usage</b></td>
    <td align="center"><b>Total Remaining</b></td>
    <td align="center"><b>Notes</b></td>
</tr>
';
$cuplertable .= '</thead>';

$type_list = [
    '1' => '16mm',
    '2' => '20mm',
    '3' => '25mm',
    '4' => '20x16mm',
    '5' => '25x20mm',
];

$cuplertable .= '<tbody>';

// Create indexed data by type for easy mapping
$mapped_data = [];
if (!empty($form_coupler)) {
    foreach ($form_coupler as $row) {
        $mapped_data[$row['coupler_type']] = $row;
    }
}

// Always print 5 rows
foreach ($type_list as $type_key => $type_label) {

    $row = isset($mapped_data[$type_key]) ? $mapped_data[$type_key] : [];

    $cuplertable .= '
        <tr style="font-size:11px;">
            <td align="center">' . $type_key . '</td>
            <td align="center">' . (!empty($row['inward_inventory_ca']) ? $row['inward_inventory_ca'] : '') . '</td>
            <td align="center">' . $type_label . '</td>
            <td align="center">' . (!empty($row['today_usage_ca']) ? $row['today_usage_ca'] : '') . '</td>
            <td align="center">' . (!empty($row['remaining_cement_ca']) ? $row['remaining_cement_ca'] : '') . '</td>
            <td align="center">' . (!empty($row['notes_ca']) ? $row['notes_ca'] : '') . '</td>
        </tr>';
}

$cuplertable .= '</tbody>';
$cuplertable .= '</table>';

$wiretable = '';
$wiretable .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1" style="word-break: break-word;">';

$wiretable .= '<thead>';
$wiretable .= '
<tr style="font-size:20px;">
    <td colspan="6" align="center"><b>Wire</b></td>
</tr>
<tr style="font-size:11px;">
    <td align="center"><b>Sr. No.</b></td>
    <td align="center"><b>Inward Inventory</b></td>
    <td align="center"><b>Type</b></td>
    <td align="center"><b>Todays usage</b></td>
    <td align="center"><b>Total Remaining</b></td>
    <td align="center"><b>Notes</b></td>
</tr>
';
$wiretable .= '</thead>';

$type_list = [
    '1' => '1 sqmm',
    '2' => '1.5 sqmm',
    '3' => '2.5 sqmm',
    '4' => '4 sqmm',
    '5' => '6 sqmm',
];

$wiretable .= '<tbody>';

// Map data by type
$mapped_data = [];
if (!empty($form_wire)) {
    foreach ($form_wire as $row) {
        $mapped_data[$row['wire_type']] = $row;
    }
}

// Always print 5 rows
foreach ($type_list as $type_key => $type_label) {

    $row = isset($mapped_data[$type_key]) ? $mapped_data[$type_key] : [];

    $wiretable .= '
        <tr style="font-size:11px;">
            <td align="center">' . $type_key . '</td>
            <td align="center">' . (!empty($row['inward_inventory_wi']) ? $row['inward_inventory_wi'] : '') . '</td>
            <td align="center">' . $type_label . '</td>
            <td align="center">' . (!empty($row['today_usage_wi']) ? $row['today_usage_wi'] : '') . '</td>
            <td align="center">' . (!empty($row['remaining_cement_wi']) ? $row['remaining_cement_wi'] : '') . '</td>
            <td align="center">' . (!empty($row['notes_wi']) ? $row['notes_wi'] : '') . '</td>
        </tr>';
}

$wiretable .= '</tbody>';
$wiretable .= '</table>';
if ($form_rmc_plant || $form_material_inward || $form_dept_labour || $form_cement_rack || $form_block_mortar_joint || $form_tile || $form_coupler || $form_wire) {
    $pdf->AddPage();
}
// Add a page break before the note
if ($form_data->message != '') {
    if (empty($form_rmc_plant) && empty($form_material_inward) && empty($form_dept_labour)) {
        $pdf->AddPage(); // Add a new page
    }

    $noteContent = '<h2>Note:</h2>';
    $noteContent .= '<p>' . $form_data->message . '</p>';

    $pdf->writeHTML($noteContent, true, false, false, false, '');
}
if (!empty($form_rmc_plant)) {
    $pdf->writeHTML($rcmplanttable, true, false, false, false, '');
}
if (!empty($form_material_inward)) {
    $pdf->writeHTML($materialinwardtable, true, false, false, false, '');
}
if (!empty($form_dept_labour)) {
    $pdf->writeHTML($deprtmenttable, true, false, false, false, '');
}
if (!empty($form_cement_rack)) {
    $pdf->writeHTML($cementtable, true, false, false, false, '');
}
if (!empty($form_block_mortar_joint)) {
    $pdf->writeHTML($blocktable, true, false, false, false, '');
}
if (!empty($form_tile)) {
    $pdf->writeHTML($tiletable, true, false, false, false, '');
}
if (!empty($form_coupler)) {
    $pdf->writeHTML($cuplertable, true, false, false, false, '');
}
if (!empty($form_wire)) {
    $pdf->writeHTML($wiretable, true, false, false, false, '');
}


if (!empty($form_attachments)) {
    $formhtml = '';
    // Add page break before the image grid starts
    $formhtml .= '<div style="page-break-before: always;"></div>';
    $formhtml .= '<h2>Photos</h2>';

    // Split into groups of 4 (2x2 grid per page)
    $chunks = array_chunk($form_attachments, 4);

    foreach ($chunks as $chunk_index => $chunk) {
        // Add page break for all chunks except the first one
        if ($chunk_index > 0) {
            $formhtml .= '<div style="page-break-before: always;"></div>';
        }

        $formhtml .= '<table width="100%" cellspacing="10" cellpadding="0" border="1" style="margin-top: 10px;">';

        // Process images in 2 rows of 2 columns each
        for ($row = 0; $row < 2; $row++) {
            $formhtml .= '<tr>';

            for ($col = 0; $col < 2; $col++) {
                $index = $row * 2 + $col;
                $formhtml .= '<td width="50%" style="text-align: center; vertical-align: middle; height: 400px; padding: 10px;">';

                if (isset($chunk[$index])) {
                    $file_path = 'uploads/form_attachments/' . $chunk[$index]['formid'] . '/' . $chunk[$index]['file_name'];

                    if (file_exists(FCPATH . $file_path)) {
                        $file_ext = strtolower(pathinfo($chunk[$index]['file_name'], PATHINFO_EXTENSION));
                        $full_path = FCPATH . $file_path;

                        // Check if it's an image
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            try {
                                $base64 = base64_encode(file_get_contents($full_path));
                                $mime_type = mime_content_type($full_path);
                                $formhtml .= '<img src="data:' . $mime_type . ';base64,' . $base64 . '" style="max-width: 100%; max-height: 350px; display: block; margin: 0 auto;">';
                            } catch (Exception $e) {
                                $formhtml .= '<div style="color: red;">Error loading image: ' . htmlspecialchars($chunk[$index]['file_name']) . '</div>';
                            }
                        } else {
                            $formhtml .= '<div style="padding: 10px; border: 1px solid #ccc;">File: ' . htmlspecialchars($chunk[$index]['file_name']) . '</div>';
                        }
                    } else {
                        $formhtml .= '<div style="color: red;">File not found: ' . htmlspecialchars($chunk[$index]['file_name']) . '</div>';
                    }
                } else {
                    $formhtml .= '&nbsp;';
                }

                $formhtml .= '</td>';
            }

            $formhtml .= '</tr>';
        }

        $formhtml .= '</table>';
    }
}

$pdf->writeHTML($formhtml, true, false, false, false, '');
