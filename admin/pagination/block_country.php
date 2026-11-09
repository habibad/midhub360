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
$table = 'country_allow';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'id', 'dt' => 0 ),     
    array( 'db' => 'CountryName',   'dt' => 1 ),   
    array( 'db' => 'id','dt' => 2,
    'formatter' => function( $d, $row ) 
    { 
        return '<a href="block_country.php?country_id=' . $d . '"><i class="mdi mdi-delete action-icon"></i></a>'; 
        
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

