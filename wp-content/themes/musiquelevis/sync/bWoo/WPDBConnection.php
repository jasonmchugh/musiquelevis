<?

/*
For $tablesPrefix:
	Ex if we set to "ic_"
		"SELECT * FROM {wp}posts"
			-> "SELECT * FROM ic_posts"
*/

class WPDBConnection
{
	const TABLES_PREFIX_REPLACEMENT_TAG = '{wp_}';
    private $_mysqli       = null;
    private $_tablesPrefix = null;
    public function __construct($host, $user, $pwd, $dbName, $tablesPrefix)
    {
        $this->_mysqli = new mysqli($host, $user, $pwd, $dbName);
        if ($this->_mysqli->connect_errno > 0) { throw new Exception('Mysql can\'t connect'); }
        $this->_mysqli->autoCommit(false);
        
        $this->_tablesPrefix = $tablesPrefix;
    }
    public function preparedQuery($preparedQuery, $params=array())
    {
    	$parsedQuery = $this->debugPreparedQuery($preparedQuery, $params);
        
        $MySQLiResult = $this->_mysqli->query($parsedQuery);
        if ($MySQLiResult === false) { throw new Exception("WPDBConnection::preparedQuery(): ".$this->_mysqli->error."\n$parsedQuery"); }
        if ($MySQLiResult === true)  { return array(); } //Inserts and updates
        
        $allResults = new WPDBQueryResult($MySQLiResult);
        $allRecords = array();
        while ($record = $allResults->next()) { $allRecords[] = $record; }
        return $allRecords;
    }
    public function debugPreparedQuery($preparedQuery, $params=array())
    {
    	$preparedQuery = str_replace(WPDBConnection::TABLES_PREFIX_REPLACEMENT_TAG, $this->_tablesPrefix, $preparedQuery);
    	$parsedQuery   = $preparedQuery;
        foreach($params as $param)
    	{
    		$parsedQuery = $this->preparedQuery_escapeReplaceOneParam($parsedQuery, $param);
    	}
    	
        return $parsedQuery;
    }
	    public function preparedQuery_escapeReplaceOneParam($parsedQuery, $param)
		{
			return preg_replace("/\?/", $this->escape($param), $parsedQuery, 1);
		}
		    public function escape($string)
		    {
		    	return $string===null ? 'NULL' : '"'.$this->_mysqli->real_escape_string($string).'"';
		    }
    public function stop() { $this->_mysqli->close(); }
    public function last_id() { return $this->_mysqli->insert_id; }
    public function commit() { $this->_mysqli->commit(); }
    public function isSuccessful() { return true; }
};
    class WPDBQueryResult //implements Iterator WTF?! Doesn't work...
    {
        private $MySQLiResult;
        private $cursor;
        private $record;
        public function __construct($MySQLiResult) { $this->MySQLiResult = $MySQLiResult; $this->cursor = 0; $this->record = array(); }
        public function __destruct() {}
        public function rewind() { if ($this->MySQLiResult) { $record = $this->MySQLiResult->data_seek(0); } $this->cursor = -1; $this->next(); }
        public function next() { $this->record = array(); if ($this->MySQLiResult) { $this->record = $this->MySQLiResult->fetch_array(); ++$this->cursor; } return $this->record; }
        public function size() { if ($this->MySQLiResult) { return $this->MySQLiResult->num_rows; } return 0; }
        public function isEmpty() { return !$this->MySQLiResult || $this->MySQLiResult->num_rows < 1; }
        public function current() { return $this->record; }
        public function key() { return $this->cursor; }
        public function valid() { return is_array($this->record); }
    };

?>