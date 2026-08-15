<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Get CI instance
$CI = &get_instance();

// Base columns - ensure these exactly match your database column names
$aColumns = [
    '1 as checkbox', // Added alias for the first column
    'flat_no',
    'block',
    'floor',
    'carpet_area',
    'wash_yard',
    'balcony',
    'total',
    'open_terrace',
    'undivided_land_share',
    'east',
    'west',
    'north',
    'south'
];

// Where conditions
$where = [];

if ($CI->input->post('warehouse_id') != '') {
    $where[] = 'AND project_id = ' . $CI->input->post('warehouse_id');
}
$join = [];
$additionalSelect = [];

$result = data_tables_init(
    $aColumns,
    'id', // Primary key
    db_prefix() . 'banakhat_properties',
    $join,
    $where,
    $additionalSelect
);

$output  = $result['output'];
$rResult = $result['rResult'];
$sr = 1;

foreach ($rResult as $aRow) {
    $row = [];

    // First column with serial number
    $row[] = $sr++;

    // Add all other columns in the same order as $aColumns
    $row[] = $aRow['flat_no'];
    $row[] = $aRow['block'];
    $row[] = $aRow['floor'];
    $row[] = $aRow['carpet_area'];
    $row[] = $aRow['wash_yard'];
    $row[] = $aRow['balcony'];
    $row[] = $aRow['total'];
    $row[] = $aRow['open_terrace'];
    $row[] = round($aRow['undivided_land_share'], 2);
    $row[] = $aRow['east'];
    $row[] = $aRow['west'];
    $row[] = $aRow['north'];
    $row[] = $aRow['south'];

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}