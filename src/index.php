<?php
/* Example Using Produk Variant Level */
require_once './include/shopify.php';

$shopify = new Shopify();

$shopify->config(
  'your_api_key',
  'your_secret_key',
  'your_store_name',
  'api_version',
  'admin_api_access_token'
);

/* Get Product By ID using Variants Level */
function getById($shopify, $id)
{
  $shopify->method('GET');

  $shopify->endpoint('variants/' . $id . '.json');

  $api_call = $shopify->rest_calls();
  $response = $api_call['response'];

  return $response['product'];
} 

/* Get All Products using Variant Level */
function getAll($shopify)
{

  $shopify->method('GET');

  $shopify->endpoint('variants.json');

  $api_call = $shopify->rest_calls();
  $response = $api_call['response'];

  return $response['variants'];
}

/* Update Product Stock */
function updateStockBySku($shopify, $sku)
{
  $products = getProductsVariants($shopify);
  $result = [];

  foreach ( $products as $data) {
    if ($data['sku'] == $sku) {
      // Get inventory item id
      $inventory_item_id = $data['inventory_item_id'];

      // Get location id
      $shopify->endpoint('inventory_levels.json?inventory_item_ids=' . $inventory_item_id);
      $result = $shopify->rest_calls();
      $location_id = $result['response']['inventory_levels'][0]['location_id'];

      // Update Stock
      $shopify->method('POST');
      $shopify->query(
        [
          'inventory_item_id' => $inventory_item_id,
          'location_id' => $location_id,
          'available' => '10'
        ]
      );
      $shopify->endpoint('inventory_levels/set.json');
      $result = $shopify->rest_calls();
    }
  }

  return $result;
}
