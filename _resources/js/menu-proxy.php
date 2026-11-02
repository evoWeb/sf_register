<?php

# A proxy for the mainmenu.json of docs.typo3.org, for a
# page that is not served from docs.typo3.org: the browser refuses to fetch
# it from a foreign origin, so the page asks its own server to pass the
# request on.
#
# Every render but the one deployed to docs.typo3.org writes this file to
# "_resources/js/" beside its assets, which is where the page looks for it.
# It needs a server that runs PHP, such as the DDEV integration of this
# project.

$proxyUrl = 'https://docs.typo3.org/h/typo3/docs-homepage/main/en-us/mainmenu.json';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);

if (curl_errno($ch) || $response === false) {
    header('404 Not Found');
    echo 'cURL error: ' . curl_error($ch);
    curl_close($ch);
    exit();
}
$response = (string)$response;

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);
http_response_code($http_code);
$headers_array = explode("\r\n", $headers);

foreach ($headers_array as $header) {
    if (!empty($header) && !preg_match('/^Transfer-Encoding:/i', $header) && !preg_match('/^Content-Length:/i', $header)) {
        header($header);
    }
}

echo $body;
