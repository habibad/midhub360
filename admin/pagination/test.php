<?php

//fetch.php

$connect = new PDO("mysql:host=localhost;dbname=eflexiloadapp", "root", "");

$column = array('id', 'submitted_date', 'user_id', 'phone', 'operator', 'load_type', 'balance', 'balance');

$query = '
SELECT * FROM send_query 
WHERE operator  LIKE "%'.$_POST["search"]["value"].'%" 
OR order_item LIKE "%'.$_POST["search"]["value"].'%" 
OR order_date LIKE "%'.$_POST["search"]["value"].'%" 
OR order_value LIKE "%'.$_POST["search"]["value"].'%" 

';

if(isset($_POST["order"]))
{
 $query .= 'ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
}
else
{
 $query .= 'ORDER BY order_id DESC ';
}

$query1 = '';

if($_POST["length"] != -1)
{
 $query1 = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$statement = $connect->prepare($query);

$statement->execute();

$number_filter_row = $statement->rowCount();

$statement = $connect->prepare($query . $query1);

$statement->execute();

$result = $statement->fetchAll();

$data = array();

$total_order = 0;

foreach($result as $row)
{
 $sub_array = array();
 $sub_array[] = $row["id"];
 $sub_array[] = $row["submitted_date"];
 $sub_array[] = $row["user_id"];
 $sub_array[] = $row["phone"];
 $sub_array[] = $row["operator"];
 $sub_array[] = $row["load_type"];
 $sub_array[] = $row["balance"];
 $sub_array[] = $row["balance"];

 $total_order = $total_order + floatval($row["balance"]);
 $data[] = $sub_array;
}

function count_all_data($connect)
{
 $query = "SELECT * FROM send_query";
 $statement = $connect->prepare($query);
 $statement->execute();
 return $statement->rowCount();
}

$output = array(
 'draw'    => intval($_POST["draw"]),
 'recordsTotal'  => count_all_data($connect),
 'recordsFiltered' => $number_filter_row,
 'data'    => $data,
 'total'    => number_format($total_order, 2)
);

echo json_encode($output);


?>