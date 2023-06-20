<?php 

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, '/hook/1/verify/request');
curl_setopt($curl, CURLOPT_POST, true);

// $data = array('name' => 'John', 'email' => 'john@example.com');
// curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$headers = array(
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($curl);

echo "<pre>";
print_r($response);
echo "</pre>";
die;

curl_close($curl);