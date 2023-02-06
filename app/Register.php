<?php

class Register extends Core
{
	function __construct() 
	{
		parent::__construct();
	}
	
	public function getExtensions($domain_uuid)
	{
		$sql  = "select extension, number_alias, ";
		$sql .= "call_group, user_context, enabled ";
		$sql .= "from v_extensions ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
				
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
		
		if ($stmt->execute())
		{
			$all_extension = array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$extension = array();
				$extension["extension"] = $row["extension"];
				$extension["number_alias"] = $row["number_alias"];
				$extension["call_group"] = $row["call_group"];
				$extension["user_context"] = $row["user_context"];
				$extension["enabled"] = $row["enabled"];
				array_push($all_extension, $extension);
			}

			return $all_extension;
		}
		else 
		{
			return "Failed";
		}
		
		return "Failed";	
	}
}

?>