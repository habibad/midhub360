<?php
include "../login.check.php";
include "../../pagination/db.php";
include "../../db.php";
include "../autoload.php";
require "../class/Mixedfn.php";


 //$us_id = Session::get('us_id');

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

 //$custom_where = "created_by =" . '$us_id';
 $custom_where = "created_by = '$us_id'";
 
// DB table to use
$table = 'user';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(    
    array( 'db' => 'user',  'dt' => 0 ),
    array( 'db' => 'balance2',   'dt' => 1 ),
    array( 'db' => 'bkash_balance', 'dt' => 2,),
    array( 'db' => 'active','dt' => 3,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return 'Inactive';
        elseif($d == 1) return 'Active';               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'level','dt' => 4,
    'formatter' => function( $d, $row ) 
    { 
        if($d == 0) return Mixedfn::levelnamechange("1","level1");
        elseif($d == 1) return Mixedfn::levelnamechange("1","level2");               
        elseif($d == 2) return Mixedfn::levelnamechange("1","level3");               
        elseif($d == 3) return Mixedfn::levelnamechange("1","level4");               
        else return 'Unknwon';  
    } 
    ),
    array( 'db' => 'id', 'dt' => 5 ),
    array( 'db' => 'id','dt' => 6,
    'formatter' => function( $d, $row ) 
    { 
        return '<a class="badge badge-success p-2" data-toggle="modal" data-target="#addPaymentModal" data-userid=" '.$d.' ">Add Payment</a>';
               
    } 
    ),
    array( 'db' => 'id','dt' => 7,
    'formatter' => function( $d, $row ) 
    { 
        return '<a class="badge badge-primary p-2" data-toggle="modal" data-target="#paymentHistoryModal" data-userid=" '.$d.' ">Payment History</a>';
        
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

