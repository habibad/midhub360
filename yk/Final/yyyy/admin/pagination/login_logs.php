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




 //custom where

 //$custom_where = "level = '3'";
 
// DB table to use
$table = 'login_logs';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'id', 'dt' => 0 ),
    array( 'db' => 'submitted_time', 'dt' => 1 ),    
    array( 'db' => 'username', 'dt' => 2 ),  
    array( 'db' => 'password', 'dt' => 3 ),
    array( 'db' => 'ip',  'dt' => 4,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="https://ipinfo.io/' . $d . '">' .$d. '</a>';
               
    } 
    ),
    array( 'db' => 'browser', 'dt' => 5),
    array( 'db' => 'os', 'dt' => 6),  
    array( 'db' => 'status','dt' => 7,
    'formatter' => function( $d, $row ) 
    { 
        //return ($d == 1)?'Success':'Waiting'; 
        if($d == 0) return 'Login Failed';
        elseif($d == 1) return 'Login Success';       
        else return 'Unknwon'; 
    } 
    ),
    array( 'db' => 'ip','dt' => 8,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="login_logs.php?ip=' . $d . '"><span class="badge bg-danger p-2">Block</span></a>';

               
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

