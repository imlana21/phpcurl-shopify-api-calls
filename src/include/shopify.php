<?php

class Shopify
{
  protected $method;
  protected $config;
  protected $endPoint;
  protected $query;
  protected $graphql;
  protected $headerContentType;

  function __construct(
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

  function setEndpoint(string $endPoint)
  {
    $this->endPoint = $endPoint;
  }

  function setMethod(string $method)
  {
    $this->method = $method;
  }

  function setQuery($query)
  {
    $this->query = $query;
  }

  function setContentType(string $type)
  {
    $this->headerContentType = $type;
  }

  function calls()
  {
    $url = "https://" . $this->config['apiKey'] . ":" . $this->config['secretKey'] . "@";
    $url .= $this->config['shopName'] . ".myshopify.com/admin/api/" . $this->config['apiVersion'] . "/";

    if ($this->headerContentType === 'graphql') {
      $url .= 'graphql.json';
    } else if ($this->headerContentType === 'json') {
      $url .= $this->endPoint;
    } else {
      return error_log('Only supported json & graphql type for shopify body');
    }

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
    curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Shopify App');
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
        "Content-Type: application/" . $this->headerContentType,
        "X-Shopify-Access-Token: " . $this->config['adminAccessToken']
      ];
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeader);
    } else {
      return error_log("Please set Admin Access Token", 401);
    }

    if($this->headerContentType == 'graphql') {
      if ($this->method == 'POST' && gettype($this->query) == 'string') {
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->query);
      } else {
        return error_log("Method or Query type not Allowed", 405);
      }
    } else {
      if (in_array($this->method, ['POST', 'PUT'])) {
        if (is_array($this->query)) {
          curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($this->query));
        } else {
          return error_log('For JSON Content type, the query must use an Array', 400);
        }
      }      
    }

    // if(!is_null($this->query)) {
    //   if (is_array($this->query)) {
    //     curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($this->query));
    //   } else {
    //     return error_log("Search query must be of type php array");
    //   }
    // } 

    // print_r(json_encode($this->query));
    $response = curl_exec($curlHandle);
    $errorNumber = curl_errno($curlHandle);
    $errorMsg = curl_error($curlHandle);

    curl_close($curlHandle);

    if ($errorNumber) {
      /* Error Handling */
      return $errorMsg;
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
          for ($i = 1; $i < count($h); $i++) {
            array_push($temp, $h[$i]);
          }
          $headers[trim($h[0])] = implode(': ', $temp);
        }
      }

      return [
        'headers' => $headers,
        'response' => json_decode($response[1], true)
      ];
    }
  }
}
