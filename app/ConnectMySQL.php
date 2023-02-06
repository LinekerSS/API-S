<?php
 
class ConnectMySQL 
{
    private $conn;

    function __construct() 
	{
		
    }

    /**
		Connecting to mysql database
    */   

    function connect()
	{
        try
		{
			$this->conn = new PDO();
		}
		catch(PDOException $e)
		{
			$e->getMessage();
		}
		
		return $this->conn;
    }
}
?>
