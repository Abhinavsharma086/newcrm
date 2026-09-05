<?php
$url = 'http://localhost:8000/admin/chatbot/search';
$data = ['query' => 'ABC Electricals'];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                     "X-Requested-With: XMLHttpRequest\r\n" .
                     "Accept: application/json\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'ignore_errors' => true,
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Status Code: " . $http_response_header[0] . "\n";
echo "Response: " . $result . "\n";
