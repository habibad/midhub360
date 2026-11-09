<?php
include "../login.check.php";
include "../../pagination/db.php";
include "../../db.php";
include "../autoload.php";
require "../class/Mixedfn.php";

$customerid = $_GET['CustomerID'];

//$simbalance = Session::get('us_id');

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */


// Database connection details for SSP
$sql_details = array(
    'user' => $db_user,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $server_name
);


 //custom where

 $custom_where = "user_id = '$customerid'";
 
// DB table to use
$table = 'bkash_balance';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'updated', 'dt' => 0 ),        
    array( 'db' => 'load_by',  'dt' => 1,
    'formatter' => function( $d, $row ) 
    { 
        return Mixedfn::username($d);
               
    } 
    ),
    array( 'db' => 'amount',   'dt' => 2 ),
    array( 'db' => 'old_balance',   'dt' => 3 ),
    array( 'db' => 'new_balance', 'dt' => 4),     
    array( 'db' => 'return_payment','dt' => 5,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return '<span class="badge bg-success p-2">Added</span>';
        elseif($d == 1) return '<span class="badge bg-danger p-2">Return</span>';        
        else return 'Unknwon'; 
    } 
    ),
    array( 'db' => 'note', 'dt' => 6)
    
 
   
);
 

 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( '../../pagination/ssp_class.php' );
 
echo json_encode(
    //SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )

    SSP::complex( $_GET, $sql_details, $table, $primaryKey, $columns, null, $custom_where)
);

