<?php
/* Example Using Produk Variant Level */
require_once 'include/shopify.php';

$shopify = new Shopify(
  'your_api_key',
  'your_secret_key',
  'your_store_name',
  'api_version',
  'admin_api_access_token'
);


$shopify->setContentType('graphql');

function getData ($shopify) {
  $shopify->setMethod('POST');
  $shopify->setQuery('{ products(first: 3) { edges { node { id title } } } }') ;
  print_r($shopify->calls());
}

getData($shopify);