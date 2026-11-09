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

// Database connection details for SSP
$sql_details = array(
    'user' => $db_user,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $server_name
);
 //custom where

 //$custom_where = "level = '3'";
 
// DB table to use
$table = 'package';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'id', 'dt' => 0 ),    
    array( 'db' => 'operator_id','dt' => 1,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 1) return 'Grameenphone';
        elseif($d == 2) return 'Robi';               
        elseif($d == 3) return 'Banglalink';               
        elseif($d == 4) return 'Airtel';               
        elseif($d == 5) return 'Teletalk';               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'package_name',   'dt' => 2 ),
    array( 'db' => 'pkg_type','dt' => 3,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 1) return 'Internet';
        elseif($d == 2) return 'Minute';               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'amount', 'dt' => 4,),
    array( 'db' => 'commission', 'dt' => 5,),
    array( 'db' => 'status','dt' => 6,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return '<span class="badge bg-danger p-2">Inactive</span>';
        elseif($d == 1) return '<span class="badge bg-success p-2">Active</span>';               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'id','dt' => 7,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="package_edit.php?pk_id=' . $d . '"><i class="mdi mdi-square-edit-outline action-icon"></i></a>'; 
        
    } 
    ),
    array( 'db' => 'id','dt' => 8,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="package_list.php?pk_id=' . $d . '"><i class="mdi mdi-delete action-icon"></i></a>'; 
        
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

    SSP::complex( $_GET, $sql_details, $table, $primaryKey, $columns, null, null )
);

