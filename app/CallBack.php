<?php

class CallBack extends Core
{
	function __construct() 
	{
		parent::__construct();
	}
	
	public function getCallBackDestinations($call_back_uuid)
	{
		$sql = "SELECT * FROM v_call_back_destinations WHERE uuid = ? LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $call_back_uuid, PDO::PARAM_STR);
		
		if ($stmt->execute())
		{
			$all_call_back_destinations = array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$call_back_destinations = array();
				$call_back_destinations["call_back_phone"] = $row["call_back_phone"];
				$call_back_destinations["call_back_priority"] = $row["call_back_priority"];
				$call_back_destinations["scheduled"] = $row["scheduled"];
				$call_back_destinations["status"] = $row["status"];
				$call_back_destinations["tries"] = $row["tries"];
				$call_back_destinations["insert_epoch"] = $row["insert_epoch"];
				$call_back_destinations["code"] = $row["code"];
				$call_back_destinations["url"] = $row["url"];
				$call_back_destinations["call_back_form"] = $row["call_back_form"];
				array_push($all_call_back_destinations, $call_back_destinations);
			}

			return $all_call_back_destinations;
		}
		else 
		{
			return "404@00";
		}	
	}
	
	public function getCallBackDestinationsAll($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$call_back_uuid = $params["call_back_uuid"];
		
		$sql  = " SELECT *";
		$sql .= " FROM v_call_back_destinations";
		$sql .= " WHERE domain_uuid = ? AND call_back_uuid = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
		$stmt->bindParam(2, $call_back_uuid, PDO::PARAM_STR);
		
		if ($stmt->execute())
		{
			$all_call_back_destinations = array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$call_back_destinations = array();
				$call_back_destinations["call_back_phone"] = $row["call_back_phone"];
				$call_back_destinations["call_back_priority"] = $row["call_back_priority"];
				$call_back_destinations["scheduled"] = $row["scheduled"];
				$call_back_destinations["status"] = $row["status"];
				$call_back_destinations["tries"] = $row["tries"];
				$call_back_destinations["insert_epoch"] = $row["insert_epoch"];
				$call_back_destinations["code"] = $row["code"];
				$call_back_destinations["url"] = $row["url"];
				$call_back_destinations["call_back_form"] = $row["call_back_form"];
				array_push($all_call_back_destinations, $call_back_destinations);
			}

			return $all_call_back_destinations;
		}
		else 
		{
			return "404@00";
		}
	}
	
	public function updateCallBackDestinations($params)
	{
		$core = new Core();
		
		$call_back_phone = $core->formatNumber($params["phone"]);
		$call_back_inf = $params["inf"];
		$call_back_uuid = $params["uuid"];
		
		$sql  = "UPDATE v_call_back_destinations SET ";
		$sql .= "call_back_phone = ?, ";
		$sql .= "call_back_form = ? ";
		$sql .= "WHERE uuid = ? ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $call_back_phone, PDO::PARAM_STR);
			$stmt->bindParam(2, $call_back_inf, PDO::PARAM_STR);
			$stmt->bindParam(3, $call_back_uuid, PDO::PARAM_STR);

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
	
	public function disableCallBackDestinations($params)
	{
		$core = new Core();
		
		$domain_uuid = $params["domain_uuid"];
		$call_back_uuid = $params["call_back_uuid"];
		$call_back_phone = $core->formatNumber($params["call_back_phone"]);

		$sql  = " UPDATE v_call_back_destinations SET";
		$sql .= " status = 'ended'";
		$sql .= " WHERE domain_uuid = ? AND call_back_uuid = ? AND call_back_phone = ?";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $call_back_uuid, PDO::PARAM_STR);
			$stmt->bindParam(3, $call_back_phone, PDO::PARAM_STR);

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
	
	public function deleteCallBackDestinations($params)
	{
		$call_back_uuid = $params["call_back_uuid"];
		
		$sql  = "DELETE FROM v_call_back_destinations ";
		$sql .= "WHERE uuid = ?";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $call_back_uuid, PDO::PARAM_STR);
			
			if($stmt->execute())
			{
			   return '200@00';
			}
			else
			{
				return '404@00'; 
			}
		}
		catch(PDOExecption $e) 
		{
			return '409@00';			
		}		
	}
	
	public function setCallBackDestinations($params)
	{
		$callBack = new CallBack();
		$core = new Core();
		
		$domain_uuid = $params["domain_uuid"];
		$call_back_uuid = $params["call_back_uuid"];
		$call_back_phone = $core->formatNumber($params["call_back_phone"]);
		$call_back_inf = $params["call_back_inf"];

		$existCallBack = $callBack->existCallBack($domain_uuid, $call_back_uuid);
		$existsCallBackDestinations = $callBack->existsCallBackDestinations($domain_uuid, $call_back_phone);
		
		if($existCallBack == 0)
		{
			return "409@01";
		}
		
		if($existsCallBackDestinations >= 1)
		{
			return "409@02";
		}
		
		$sql  = "insert into v_call_back_destinations";
		$sql .= "(";
		$sql .=  "uuid, ";
		$sql .=  "domain_uuid, ";
		$sql .=  "call_back_uuid, ";
		$sql .=  "call_back_phone, ";
		$sql .=  "call_back_priority, ";
		$sql .=  "scheduled, ";
		$sql .=  "status, ";
		$sql .=  "tries, ";
		$sql .=  "insert_epoch, ";
		$sql .=  "call_back_form";
		$sql .=  ")";
		$sql .=  "values ";
		$sql .=  "( ";
		$sql .=  "?, ";
		$sql .=  "?, ";
		$sql .=  "?, ";
		$sql .=  "?, ";
		$sql .=  "0, ";
		$sql .=  "0, ";
		$sql .=  "'virgin', ";
		$sql .=  "0, ";
		$sql .=  "UNIX_TIMESTAMP(), ";
		$sql .=  "?";
		$sql .=  ")";
		$stmt = $this->conn->prepare($sql);
		
		$uuid = $core->uuid();
		
		try
		{
			$stmt->bindParam(1, $uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(3, $call_back_uuid, PDO::PARAM_STR);
			$stmt->bindParam(4, $call_back_phone, PDO::PARAM_STR);
			$stmt->bindParam(5, $call_back_inf, PDO::PARAM_STR);

			if ($stmt->execute())
			{
				return "200@00";
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
	
	public function existCallBack($domain_uuid, $call_back_uuid)
	{
		$sql  = "select count(1) rows_count from v_call_backs ";
		$sql .= "where domain_uuid = ? ";
		$sql .= "and call_back_uuid = ? ";
		$sql .= "limit 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $call_back_uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					return intval($row["rows_count"]);
				}
				
				$rowCount = $stmt->rowCount();
				
				if($rowCount == 0)
				{
					return 0;
				}
			}
			else 
			{
				return 0;
			}
		}
		catch(PDOExecption $e) 
		{
			return 0;			
		}
	}
	
	public function existsCallBackDestinations($domain_uuid, $call_back_phone)
	{
		$sql  = "select count(1) rows_count "; 
		$sql .= "from v_call_back_destinations d ";
		$sql .= "where d.domain_uuid = ? ";
		$sql .= "AND d.call_back_phone = ? ";
		$sql .= "and status = 'virgin' ";
		$sql .= "limit 1";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $call_back_phone, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{	
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					return intval($row["rows_count"]);
				}
				
				$rowCount = $stmt->rowCount();
				
				if($rowCount == 0)
				{
					return 0;
				}				
			}
			else 
			{
				return 0;
			}
		}
		catch(PDOExecption $e) 
		{
			return 0;			
		}
	}
}
?>