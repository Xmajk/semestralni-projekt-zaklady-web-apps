<?php
// api.php

// Řekneme prohlížeči, že posíláme JSON
header('Content-Type: application/json; charset=utf-8');

// Povolíme třeba i CORS, pokud budeš volat z jiné domény
// header('Access-Control-Allow-Origin: *');

// Tvůj výstupní objekt/array
$response = [
    'status' => 200,
    'message' => 'Ahoj světe!',
    'data' => ['foo' => 'bar', 'baz' => 123]
];

// Vrátíme jako JSON
echo json_encode($response);

// Pokud chceš i HTTP status kód:
http_response_code(200);