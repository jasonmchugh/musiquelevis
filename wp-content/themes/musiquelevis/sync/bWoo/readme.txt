
http://woocommerce.github.io/woocommerce-rest-api-docs/

Does require core woocommerce-api/ to work

View code examples in each file, like bWoo_Category.php and bWoo_Product.php

Minimal setup:
	require_once('[...]/bWoo/autoload.php');
	
	define('BWOO_STORE_URL',       'https://instantcomptant.bravad-dev.com');
	define('BWOO_CONSUMER_KEY',    'ck_fbcb961c8e6a7e9152e31bcf5a93f1f326116d48');
	define('BWOO_CONSUMER_SECRET', 'cs_d8fa239c0117ed104628241b3504fda8031b1769');
	
	$api = new bWooApi(BWOO_STORE_URL, BWOO_CONSUMER_KEY, BWOO_CONSUMER_SECRET);


Définition des champs de l'api et formats etc
	E:/SVNs/instantcomptant.bravad-dev.com/wp-content/plugins/woocommerce/includes/api
		class-wc-rest-products-controller.php::get_item_schema()
		class-wc-rest-product-categories-controller.php::get_item_schema()
