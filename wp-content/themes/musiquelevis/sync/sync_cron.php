<?
require_once('sync_utils.php');

/*
Call with https://instantcomptant.ca/wp-content/themes/musiquelevis/sync/sync_cron.php

NOTE: Check in proj #6996 for access to https://rosemont.whc.ca:2083/cpsess6579783092/frontend/paper_lantern/cron/index.html to setup cron
	Cron on:
		/usr/local/bin/php /home/instant1/instantcomptant.ca12/wp-content/themes/musiquelevis/sync/sync_cron.php dontRunRealCron=0 >/dev/null 2>&1
	Cron off:
		/usr/local/bin/php /home/instant1/instantcomptant.ca12/wp-content/themes/musiquelevis/sync/sync_cron.php dontRunRealCron=1 >/dev/null 2>&1
*/
//file_put_contents(__DIR__.'/tmp.txt', print_r($argv,true));
//if (!empty($argv['dontRunRealCron'])) { echo 'Cron stopped for now';exit; }
//file_put_contents(__DIR__.'/tmp.txt', 2);

buildSyncDefinesAndServerGlobalsForEnvironment('prod'); //Defines DB_HOST, DB_USER, DB_PWD, DB_NAME, TABLES_PREFIX, BWOO_STORE_URL, BWOO_CONSUMER_KEY, BWOO_CONSUMER_SECRET, IS_SERVER_UTF8

doSync($rebuildAllProductsSummaryInfos=false, $syncCategories=true, $syncProducts=true, $syncStocksIN=true, $syncStocksOUT=true, $syncImages=true, $queryLimit=50);

?>