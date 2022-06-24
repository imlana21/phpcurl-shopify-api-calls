<?php
require_once './include/shopify.php';

$shopify = new Shopify();

$shopify->config(
  'your_api_key',
  'your_secret_key',
  'your_store_name',
  'api_version',
  'admin_api_access_token'
);

// Example get product
function getProducts($shopify)
{

  $shopify->method('GET');

  $shopify->endpoint('products.json');

  $api_call = $shopify->rest_calls();
  $response = $api_call['response'];

  return $response;
}

// Example get product using product_id
function getProductById($shopify, $id)
{
  $shopify->method('GET');

  $shopify->endpoint('products/'.$id.'.json');

  $api_call = $shopify->rest_calls();
  $response = $api_call['response'];

  return $response;
}