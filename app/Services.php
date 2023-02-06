<?php

class Services extends Core
{
	function __construct() 
	{
		parent::__construct();
	}
	
	public function setBlackList($params)
	{
		$core = new Core();

		$call_block_uuid = $core->uuid();		
		$domain_uuid = $params["domain_uuid"];
		$call_block_name = $params["call_block_name"];
		$call_block_number = $params["call_block_number"];
		$call_block_count = '0';
		$call_block_action = 'Reject';
		$call_block_enabled = 'true';
		
		$sql  = "insert into v_call_block ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "call_block_uuid, ";
		$sql .= "call_block_name, ";
		$sql .= "call_block_number, ";
		$sql .= "call_block_count, ";
		$sql .= "call_block_action, ";
		$sql .= "call_block_enabled, ";
		$sql .= "date_added ";
		$sql .= ") ";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "? ";
		$sql .= ")";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $call_block_uuid, PDO::PARAM_STR);
			$stmt->bindParam(3, $call_block_name, PDO::PARAM_STR);
			$stmt->bindParam(4, $call_block_number, PDO::PARAM_STR);
			$stmt->bindParam(5, $call_block_count, PDO::PARAM_STR);
			$stmt->bindParam(6, $call_block_action, PDO::PARAM_STR);
			$stmt->bindParam(7, $call_block_enabled, PDO::PARAM_STR);
			$stmt->bindParam(8, $date_added, PDO::PARAM_STR);

			if ($stmt->execute())
			{
				return '200@00';
			}
			else
			{
				return "404@00";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";			
		}
	}
}
?>