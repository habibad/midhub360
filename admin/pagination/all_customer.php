<?php
// Required includes
include "../login.check.php";
include "../../db.php";
include "../autoload.php";
require "../class/Mixedfn.php";

/*
 * DataTables server-side processing script using SSP class
 * @license MIT - http://datatables.net/license_mit
 */

// Database connection details for SSP
$sql_details = array(
    'user' => $db_user,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $server_name
);

// DB table to use
$table = 'user';

// Table's primary key
$primaryKey = 'id';

// Array of database columns
$columns = array(
    array('db' => 'id',            'dt' => 0),
    array('db' => 'user',          'dt' => 1),
    array('db' => 'balance2',      'dt' => 2),
    array('db' => 'bkash_balance', 'dt' => 3),
    array(
        'db' => 'active',
        'dt' => 4,
        'formatter' => function ($d, $row) {
            if ($d == 0) return '<span class="badge bg-danger p-2">Inactive</span>';
            elseif ($d == 1) return '<span class="badge bg-success p-2">Active</span>';
            elseif ($d == 3) return '<span class="badge bg-warning p-2">Signup</span>';
            else return 'Unknown';
        }
    ),
    array(
        'db' => 'id',
        'dt' => 5,
        'formatter' => function ($d, $row) {
            return '<a href="cs-profile.php?reseller_id=' . $d . '"><i class="mdi mdi-eye action-icon"></i></a>';
        }
    ),
    array(
        'db' => 'id',
        'dt' => 6,
        'formatter' => function ($d, $row) {
            return '<a href="cs-edit.php?reseller_id=' . $d . '"><i class="mdi mdi-square-edit-outline action-icon"></i></a>';
        }
    ),
    array(
        'db' => 'id',
        'dt' => 7,
        'formatter' => function ($d, $row) {
            return '<a href="customer_list.php?id=' . $d . '"><i class="mdi mdi-delete action-icon"></i></a>';
        }
    )
);

// Load SSP class
require('../../pagination/ssp_class.php');

// Get the response
$response = SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, null, null);

// Always return JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;
