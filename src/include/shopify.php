<?php

class Shopify
{
  public $method;
  public $config;
  public $endPoint;
  public $query = [];

  function set_config(
    string $apiKey,
    string $secretKey,
    string $shopName,
    string $apiVersion,
    string $adminAccessToken
  ) {
    $this->config = [
      'apiKey' => $apiKey,
      'secretKey' => $secretKey,
      'shopName' => $shopName,
      'apiVersion' => $apiVersion,
      'adminAccessToken' => $adminAccessToken
    ];
  }

  function set_endpoint(string $endPoint)
  {
    $this->endPoint = $endPoint;
  }

  function set_method(string $method)
  {
    $this->method = $method;
  }

  function set_query(array $query) {
    $this->query = $query;
  }

  function api_calls()
  {
    $url = "https://" . $this->config['apiKey'] . ":" . $this->config['secretKey'];
    $url .= "@" . $this->config['shopName'] . ".myshopify.com/admin/api/";
    $url .= $this->config['apiVersion'] . "/" . $this->endPoint . ".json";

    /* Init Curl */
    $curlHandle = curl_init($url);
    /* Curl Configuration */
    // include the header in the output
    curl_setopt($curlHandle, CURLOPT_HEADER, true);
    // to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
    // to follow any "Location: " header that the server sends as part of the HTTP header
    curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, TRUE);
    // Set User Agent
    curl_setopt($curlHandle, CURLOPT_USERAGENT, 'My New Shopify App v.1');
    // The maximum amount of HTTP redirections to follow
    curl_setopt($curlHandle, CURLOPT_MAXREDIRS, 3);
    // to stop cURL from verifying the peer's certificate
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
    // The number of seconds to wait while trying to connect
    curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
    // The maximum number of seconds to allow cURL functions to execute
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
    // Set Method
    curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $this->method);

    // Set Header
    if ($this->config['adminAccessToken'] != null || $this->config['adminAccessToken'] != '') {
      $curlHeader = [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: " . $this->config['adminAccessToken']
      ];
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeader);
    } else {
      return error_log("Please set Admin Access Token", 401);
    }


    if ($this->method != 'GET' && in_array($this->method, ['POST', 'PUT'])) {
      if (is_array($this->query)) {
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->query);
      }
    }


    $response = curl_exec($curlHandle);

    if (curl_errno($curlHandle)) {
      /* Error Handling */
      return curl_error($curlHandle);
    } else {
      // Parsing out body and header
      $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
      $headers_data = explode("\n", $response[0]);
      $headers = array();
      $headers['Status'] = $headers_data[0];
      array_shift($headers_data);
      foreach ($headers_data as $head) {
        $h = explode(': ', $head);
        $headers[trim($h[0])] = trim($h[1]);
        if ($h[0] == 'Content-Security-Policy') {
          $temp = [];
          for($i = 1; $i < count($h); $i++) {
            array_push($temp, $h[$i]);
          }
          $headers[trim($h[0])] = implode(': ', $temp);
        }
      }

      return [
        'headers' => $headers,
        'response' => json_decode($response[1])
      ];
    }
  }
}
