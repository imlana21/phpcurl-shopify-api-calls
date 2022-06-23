<?php
require_once './src/include/shopify.php';

$shopify = new Shopify();

$shopify->set_config(
  'your_api_key',
  'your_secret_key',
  'your_store_name',
  'api_version',
  'admin_api_access_token'
);
$shopify->set_method('GET');

$shopify->set_endpoint('products/7725227606224');

$shopify->api_calls();

print_r(getenv());