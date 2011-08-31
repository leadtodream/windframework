<?php
Wind::import("WIND:db.exception.WindDbException");
Wind::import("WIND:db.WindSqlStatement");
Wind::import("WIND:db.WindResultSet");

/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id$
 * @package 
 */
class WindConnection extends WindModule {
	/**
	 * PDO 链接字符串
	 * @var string
	 */
	protected $_dsn;
	protected $_driverName;
	protected $_user;
	protected $_pwd;
	protected $_tablePrefix;
	protected $_charset;
	/**
	 * @var array
	 */
	protected $_attributes = array();
	/**
	 * @var PDO
	 */
	protected $_dbHandle = null;

	/**
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($dsn = '', $username = '', $password = '') {
		$this->_dsn = $dsn;
		$this->_user = $username;
		$this->_pwd = $password;
	}

	/**
	 * 接受一条sql语句，并返回sqlStatement对象
	 * @param string $sql | sql语句
	 * @return WindSqlStatement
	 */
	public function createStatement($sql = null) {
		return new WindSqlStatement($this, $this->parseQueryString($sql));
	}

	/**
	 * 返回数据库链接对象
	 * @return WindMysqlPdoAdapter
	 */
	public function getDbHandle() {
		$this->init();
		return $this->_dbHandle;
	}

	/**
	 * 获得链接相关属性设置
	 * @param string $attribute
	 * @return string
	 * */
	public function getAttribute($attribute) {
		if ($this->_dbHandle !== null) {
			return $this->_dbHandle->getAttribute($attribute);
		} else
			return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : '';
	}

	/**
	 * 设置链接相关属性
	 * @param string $attribute
	 * @param string $value
	 * @return 
	 * */
	public function setAttribute($attribute, $value = null) {
		if (!$attribute)
			return;
		if ($this->_dbHandle !== null) {
			$this->_dbHandle->setAttribute($attribute, $value);
		} else
			$this->_attributes[$attribute] = $value;
	}

	/**
	 * 返回DB驱动类型
	 * @return string
	 */
	public function getDriverName() {
		if ($this->_driverName)
			return $this->_driverName;
		if ($this->_dbHandle !== null) {
			$this->_driverName = $this->_dbHandle->getAttribute(PDO::ATTR_DRIVER_NAME);
		} elseif (($pos = strpos($this->_dsn, ':')) !== false) {
			$this->_driverName = strtolower(substr($this->_dsn, 0, $pos));
		}
		return $this->_driverName;
	}

	/**
	 * 执行一条sql语句 同时返回影响行数
	 * @param string $sql | SQL statement
	 * @return int
	 */
	public function execute($sql) {
		try {
			$result = $this->getDbHandle()->exec($this->parseQueryString($sql));
			if (WIND_DEBUG & 2)
				Wind::getApp()->getComponent('windLogger')->info(
					"[db.WindConnection.execute] \r\n\tSQL: " . $sql . " \r\n\tResult:" . WindString::varToString(
						$result));
			return $result;
		} catch (PDOException $e) {
			$this->close();
			throw new WindDbException($e->getMessage());
		}
	}

	/**
	 * 执行一条查询同时返回结果集
	 * @param string $sql | SQL statement 
	 * @return WindResultSet
	 */
	public function query($sql) {
		try {
			$sql = $this->parseQueryString($sql);
			return new WindResultSet($this->getDbHandle()->query($sql));
		} catch (PDOException $e) {
			throw new WindDbException();
		}
	}

	/**
	 * 过滤SQL元数据，数据库对象(如表名字，字段等)
	 * @param string $data
	 * @throws WindDbException
	 */
	public function sqlMetadata($data) {
		$data = str_replace(array('`', ' '), '', $data);
		return ' `' . $data . '` ';
	}

	/**
	 * 过滤数组变量，将数组变量转换为字符串，并用逗号分隔每个数组元素支持多维数组
	 * @param array $array
	 */
	public function quoteArray($array) {
		return $this->getDbHandle()->filterArray($array);
	}

	/**
	 * sql元数据安全过滤
	 * @param string $string
	 */
	public function quote($string) {
		return $this->getDbHandle()->quote($string);
	}

	/**
	 * 组装单条 key=value 形式的SQL查询语句值 insert/update并进行安全过滤
	 * @param array $array
	 */
	public function sqlSingle($array) {
		return $this->getDbHandle()->sqlSingle($array);
	}

	/**
	 * 创建表
	 * @param string $tableName
	 * @param array $fileds
	 */
	public function createTable($tableName, $values, $engine = '', $autoIncrement = '') {
		return $this->getDbHandle()->createTable($tableName, $values, $engine, $this->_charset, $autoIncrement);
	}

	/**
	 * 返回最后一条插入数据ID
	 * @param string $name
	 * @return int 
	 */
	public function lastInsertId($name = '') {
		if ($name)
			return $this->getDbHandle()->lastInsertId($name);
		else
			return $this->getDbHandle()->lastInsertId();
	}

	/**
	 * 关闭数据库连接
	 */
	public function close() {
		$this->_dbHandle = null;
	}

	/**
	 * 初始化DB句柄
	 * @throws Exception
	 * @return 
	 */
	public function init() {
		try {
			if ($this->_dbHandle !== null)
				return;
			$driverName = $this->getDriverName();
			$dbHandleClass = "WIND:db." . $driverName . ".Wind" . ucfirst($driverName) . "PdoAdapter";
			$dbHandleClass = Wind::import($dbHandleClass);
			$this->_dbHandle = new $dbHandleClass($this->_dsn, $this->_user, $this->_pwd, (array) $this->_attributes);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_dbHandle->setCharset($this->_charset);
			if (WIND_DEBUG & 2)
				Wind::getApp()->getComponent('windLogger')->info(
					"[db.WindConnection.init] Initialize db connection success. \r\n\tDSN:" . $this->_dsn . "\r\n\tUser:" . $this->_user . "\r\n\tCharset:" . $this->_charset . "\r\n\tTablePrefix:" . $this->_tablePrefix . "\r\n\tDriverName:" . $this->_driverName, 
					'db');
		} catch (PDOException $e) {
			$this->close();
			throw new WindDbException($e->getMessage());
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->_attributes = array();
		$this->_initConfig();
	}

	/**
	 * 根据配置信息，初始化当前连接对象
	 * @param array $config
	 */
	protected function _initConfig($config = array()) {
		$this->_dsn = $this->getConfig('dsn', '', '', $config);
		$this->_user = $this->getConfig('user', '', '', $config);
		$this->_pwd = $this->getConfig('pwd', '', '', $config);
		$this->_charset = $this->getConfig('charset', '', '', $config);
		$this->_tablePrefix = $this->getConfig('tableprefix', '', '', $config);
	}

	/**
	 * @param string $sql
	 */
	protected function parseQueryString($sql) {
		if ($_prefix = $this->getTablePrefix()) {
			list($new, $old) = explode('|', $_prefix . '|');
			$sql = preg_replace('/{(' . $old . ')?(.*?)}/', $new . '\2', $sql);
		}
		return $sql;
	}

	/**
	 * 获得表前缀
	 * @return string $tablePrefix
	 */
	public function getTablePrefix() {
		return $this->_tablePrefix;
	}

	/**
	 * 设置表前缀
	 * @param string $tablePrefix
	 */
	public function setTablePrefix($tablePrefix) {
		$this->_tablePrefix = $tablePrefix;
	}
}
?>