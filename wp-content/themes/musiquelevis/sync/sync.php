<?
require_once('sync_utils.php');


if ($_SERVER['SERVER_NAME']==='instantcomptant.bravad-dev.com')
{
	buildSyncDefinesAndServerGlobalsForEnvironment('dev'); //Defines DB_HOST, DB_USER, DB_PWD, DB_NAME, TABLES_PREFIX, BWOO_STORE_URL, BWOO_CONSUMER_KEY, BWOO_CONSUMER_SECRET, IS_SERVER_UTF8
}
else
{
	buildSyncDefinesAndServerGlobalsForEnvironment('prod'); //Defines DB_HOST, DB_USER, DB_PWD, DB_NAME, TABLES_PREFIX, BWOO_STORE_URL, BWOO_CONSUMER_KEY, BWOO_CONSUMER_SECRET, IS_SERVER_UTF8
}


doSync($rebuildAllProductsSummaryInfos=false,$syncCategories=true, $syncProducts=true, $syncStocksIN=true, $syncStocksOUT=true, $syncImages=true, $queryLimit=50);
?>