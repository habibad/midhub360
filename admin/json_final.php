<?php

$url = "https://localhost/eflexiload/api/recharge_status.php?number=01711721453&amount=20&user=distributor&key=3d3d7e40508fd4e8ff7df74ed002e82c";
 
$client = curl_init($url);
curl_setopt($client, CURLOPT_RETURNTRANSFER,true);
curl_setopt($client, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($client, CURLOPT_SSLVERSION, 1);
curl_setopt($client, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

 $response = curl_exec($client); 
 $result = json_decode($response); 

 curl_close($client);

 
 //$saju = $result->re_status;



 //echo "$saju <br><br>";



if($result->STATUS == 'SUCCESS')
{

    echo "Wow!!!!";

}else{
echo "Sorry...!";
//continue;
} 
 
 //exit;

 


 


?>