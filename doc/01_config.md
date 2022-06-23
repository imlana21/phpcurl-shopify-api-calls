```php
  // You need include file `shopify.php` from folder `include` to your project
  require_once './include/shopify.php';

  // You need call class Shopify as Object
  $shopify = new Shopify();

  // Configuration your Shopify
  $shopify->set_config(
    'your_api_key',
    'your_secret_key',
    'your_store_name',
    'api_version',
    'admin_api_access_token'
  );
```