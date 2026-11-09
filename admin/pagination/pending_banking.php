<?php
include "../login.check.php";
include "../../pagination/db.php";
include "../../db.php";
include "../autoload.php";
require "../class/Mixedfn.php";


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

 $custom_where = "status = '0'";
 
// DB table to use
$table = 'bank_request';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'id', 'dt' => 0 ),
    array( 'db' => 'submitted_date', 'dt' => 1 ),    
    array( 'db' => 'user_id',  'dt' => 2,
    'formatter' => function( $d, $row ) 
    { 
        return Mixedfn::username($d);
               
    } 
    ),
    array( 'db' => 'bank_name',   'dt' => 3 ),
    array( 'db' => 'account_name',   'dt' => 4 ),
    array( 'db' => 'ac_number', 'dt' => 5),
    array( 'db' => 'balance', 'dt' => 6),  
    array( 'db' => 'status','dt' => 7,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return '<span class="badge bg-primary p-2">Pending</span>';
        elseif($d == 1) return '<span class="badge bg-success p-2">Success</span>';
        elseif($d == 3) return '<span class="badge bg-danger p-2">Failed</span>';
        elseif($d == 4) return '<span class="badge bg-warning p-2">Waiting</span>';
        elseif($d == 6) return '<span class="badge bg-info p-2">Prcessing</span>';
        else return 'Unknwon'; 
    } 
    ),
    array( 'db' => 'id','dt' => 8,
    'formatter' => function( $d, $row ) 
    { 
        return '
        <a class="btn btn-success" role="button" data-bs-toggle="modal" data-bs-target="#suceessbank" data-id=" ' . $d .' ">Success</a>
        <a class="btn btn-danger" role="button" data-bs-toggle="modal" data-bs-target="#cancelbank" data-id=" '. $d .' ">Cancel</a>        
        ';
               
    } 
    )
    
 
   
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

