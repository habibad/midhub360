<?php
include "../login.check.php";
include "../../pagination/db.php";
include "../../db.php";
include "../autoload.php";
require "../class/Mixedfn.php";


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


 //custom where

 $custom_where = "level = '0' and created_by = '$us_id'";
 
// DB table to use
$table = 'user';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'id', 'dt' => 0 ),
    array( 'db' => 'user',  'dt' => 1 ),
    array( 'db' => 'balance2',   'dt' => 2 ),
    array( 'db' => 'bkash_balance', 'dt' => 3,),
    array( 'db' => 'active','dt' => 4,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return '<span class="badge bg-danger p-2">Inactive</span>';
        elseif($d == 1) return '<span class="badge bg-success p-2">Active</span>';               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'id','dt' => 5,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="cs-profile.php?reseller_id=' . $d . '"><i class="mdi mdi-eye action-icon"></i></a>'; 
        
    } 
    ),
    array( 'db' => 'id','dt' => 6,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="cs-edit.php?reseller_id=' . $d . '"><i class="mdi mdi-square-edit-outline action-icon"></i></a>'; 
        
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

    SSP::complex( $_GET, $sql_details, $table, $primaryKey, $columns, null, $custom_where )
);

