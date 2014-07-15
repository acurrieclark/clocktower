<?php


class db{

	/**
	 * Holds an instance of self
	 * @var $instance
	 */
	private static $instance = NULL;

	/**
	*
	* the constructor is set to private so
	* so nobody can create a new instance using new
	*
	*/
	private function __construct()
	{
	}

	/**
	*
	* Return DB instance or create intitial connection
	*
	* @return object (PDO)
	*
	* @access public
	*
	*/
	public static function getInstance()
	{
		if (!self::$instance)
		{
			try {
				if (!DB_NAME) {
			        throw new PDOException('No database name');
			    }
				self::$instance = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,
	                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				logger::Database('Connected to '.DB_NAME.'@'.DB_HOST);
			}
			catch(PDOException $ex) {
				logger::Database("Could not connect");
				logger::Database($ex->getMessage());
				logger::write();
				die ("Invalid database - please check the log file for details");
			}
		}
		return self::$instance;
	}


	/**
	*
	* Like the constructor, we make __clone private
	* so nobody can clone the instance
	*
	*/
	private function __clone()
	{
	}

} // end of class
