<?php

class CallCenter extends Core
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
			return "404@00";
		}
	}
	
	public function setAgentLogin($args)
	{
		/**
			101
			Logando o ramal no sistema
		*/
		
		$agent_name = (string)$args['agent'];
		
		$core = new Core();
		
		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain, ";
		$sql .= "d.domain_uuid, d.domain_name, t.agent_name, "; 
		$sql .= "t.tier_level, t.tier_position, q.queue_name, q.queue_extension "; 
		$sql .= "FROM v_call_center_queues q "; 
		$sql .= "INNER JOIN v_call_center_tiers t ON t.queue_name = q.queue_name "; 
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = q.domain_uuid "; 
		$sql .= "AND t.agent_name = ? "; 
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent_name, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				if($stmt->rowCount() > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$agent_domain = $row["agent_domain"];
						$queue_domain = $row["queue_domain"];
						$domain_uuid = $row["domain_uuid"];
						$domain_name = $row["domain_name"];
						$agent_name = $row["agent_name"];
						$queue_name = $row["queue_name"];
						$tier_level = $row["tier_level"];
						$tier_position = $row["tier_position"];
						$queue_extension = $row["queue_extension"];
					}
				}
				else
				{
					$log["status"] = "error";
					$log["status_type"] = "not_found_failed";
					return $log;
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e) 
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$login_interval = 4*60*60;
		$epoch = time();

		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		if ($fp)
		{
			$cmd = "api callcenter_config agent get status $agent_domain";
			error_log($cmd);
			$status_current = trim($core->event_socket_request($fp, $cmd));
			error_log("status_current " . $status_current);
			
			/**
				fica disponivel se estiver deslogado ou em pausa
			*/
			
			if (strtolower($status_current) == "logged out")
			{
				$cmd = "api callcenter_config tier add $queue_domain $agent_domain $tier_level $tier_position";
				error_log($cmd);
				$response = $core->event_socket_request($fp, $cmd);
				
				$cmd = "api callcenter_config agent set status $agent_domain 'Available'";
				error_log($cmd);
				$response = trim($core->event_socket_request($fp, $cmd));
				
				$cmd = "api callcenter_config agent set state $agent_domain 'Waiting'";
				error_log($cmd);
				$response = trim($core->event_socket_request($fp, $cmd));
				
				/**
					Se o último login até agora for maior que login_interval.
					Considerar primeiro Login, senão, apenas login
				*/
				
				$sql  = "select agent_status, start_epoch from v_xml_cdr_call_center_agent ";
				$sql .= "where domain_uuid = ? ";
				$sql .= "and cc_agent_name = ? ";
				$sql .= "order by start_epoch desc ";
				$sql .= "limit 1";
				$stmt = $this->conn->prepare($sql);
				
				try
				{
					$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
					$stmt->bindParam(2, $agent_name, PDO::PARAM_STR);
					
					if ($stmt->execute())
					{
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							if ($row['agent_status'] == 'logout')
							{
								if (($row['start_epoch'] + $login_interval) >= time())
								{
									$agent_status = 're_login';
									$disposition_code = 'r';
								}
							}
						}
					}
					else 
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				catch(PDOExecption $e) 
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
				
				$num_rows = $stmt->rowCount();
				
				if ($num_rows == 0)
				{
					$agent_status = 'first_login';
					$disposition_code = 'l';
				}
				
				$call_center_agent_status_uuid = $core->uuid();
				
				$sql  = "insert into v_xml_cdr_call_center_agent ";
				$sql .= "(";
				$sql .= "call_center_agent_status_uuid, ";
				$sql .= "domain_uuid, ";
				$sql .= "cc_queue, ";
				$sql .= "cc_agent_name, ";
				$sql .= "agent_status, ";
				$sql .= "start_stamp, ";
				$sql .= "start_epoch, ";
				$sql .= "disposition_code";
				$sql .= ") values (";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?";
				$sql .= ")";
				$stmt = $this->conn->prepare($sql);
				
				try
				{
					$stmt->bindParam(1, $call_center_agent_status_uuid, PDO::PARAM_STR);
					$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
					$stmt->bindParam(3, $queue_name, PDO::PARAM_STR);
					$stmt->bindParam(4, $agent_name, PDO::PARAM_STR);
					$stmt->bindParam(5, $agent_status, PDO::PARAM_STR);
					$stmt->bindParam(6, date("Y-m-d H:i:s", $epoch), PDO::PARAM_STR);
					$stmt->bindParam(7, $epoch, PDO::PARAM_STR);
					$stmt->bindParam(8, $disposition_code, PDO::PARAM_STR);
					
					if (!$stmt->execute())
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				catch(PDOExecption $e) 
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}

				/**
					insert no v_xml_cdr_call_center_agent_consolidate
				*/
				
				if ($agent_status == 'first_login')
				{
					$cc_agent_status_uuid = $core->uuid();
					$agent_state = "available";
					
					$sql  = "insert into v_xml_cdr_call_center_agent_consolidate ";
					$sql .= "(";
					$sql .= "cc_agent_status_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "domain_name, ";
					$sql .= "cc_queue, ";
					$sql .= "cc_agent_name, ";
					$sql .= "state, ";
					$sql .= "state_epoch, ";
					$sql .= "start_epoch, ";
					$sql .= "start_stamp, ";
					$sql .= "last_login, ";
					$sql .= "first_login, ";
					$sql .= "disposition_code";
					$sql .= ") values (";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?";
					$sql .= ")";
					$stmt = $this->conn->prepare($sql);
					
					try
					{
						$stmt->bindParam(1, $cc_agent_status_uuid, PDO::PARAM_STR);
						$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
						$stmt->bindParam(3, $domain_name, PDO::PARAM_STR);
						$stmt->bindParam(4, $queue_name, PDO::PARAM_STR);
						$stmt->bindParam(5, $agent_name, PDO::PARAM_STR);
						$stmt->bindParam(6, $agent_state, PDO::PARAM_STR);
						$stmt->bindParam(7, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(8, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(9, date("Y-m-d H:i:s", $epoch), PDO::PARAM_STR);
						$stmt->bindParam(10, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(11, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(12, $disposition_code, PDO::PARAM_STR);

						if (!$stmt->execute())
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}
					}
					catch(PDOExecption $e) 
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				
				if ($agent_status == 're_login')
				{
					$sql  = "select cc_agent_status_uuid from v_xml_cdr_call_center_agent_consolidate ";
					$sql .= "where domain_uuid = ? ";
					$sql .= "and cc_agent_name = ? ";
					$sql .= "and (logout > last_login) ";
					$sql .= "order by start_epoch desc limit 1 ";
					$stmt = $this->conn->prepare($sql);
					
					try
					{
						$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
						$stmt->bindParam(2, $agent_name, PDO::PARAM_STR);
						
						if ($stmt->execute())
						{
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$cc_agent_status_uuid = $row["cc_agent_status_uuid"];
							}
						}
						else
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}
					}
					catch(PDOExecption $e) 
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}

					if ($cc_agent_status_uuid && $epoch)
					{
						$sql  = "update v_xml_cdr_call_center_agent_consolidate set ";
						$sql .= "last_login = ?, ";
						$sql .= "state = 'available', ";
						$sql .= "state_epoch = ?, ";
						$sql .= "disposition_code = 'D' ";
						$sql .= "where cc_agent_status_uuid = ? ";
						$stmt = $this->conn->prepare($sql);
						
						try 
						{
							$stmt->bindParam(1, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(2, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(3, $cc_agent_status_uuid, PDO::PARAM_STR);

							if (!$stmt->execute())
							{
								$log["status"] = "error";
								$log["status_type"] = "execute_failed";
								return $log;
							}
						}
						catch(PDOExecption $e) 
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}						
					}
					else
					{
						$cc_agent_status_uuid = $core->uuid();
						$agent_state = "available";
						$disposition_code = "D";
						
						$sql.= "insert into v_xml_cdr_call_center_agent_consolidate ";
						$sql.= "(";
						$sql.= "cc_agent_status_uuid, ";
						$sql.= "domain_uuid, ";
						$sql.= "domain_name, ";
						$sql.= "cc_queue, ";
						$sql.= "cc_agent_name, ";
						$sql.= "state, ";
						$sql.= "state_epoch, ";
						$sql.= "start_epoch, ";
						$sql.= "start_stamp, ";
						$sql.= "last_login, ";
						$sql.= "first_login, ";
						$sql.= "disposition_code";
						$sql.= ") values (";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?, ";
						$sql.= "?";
						$sql.= ")";
						$stmt = $this->conn->prepare($sql);
						
						try 
						{
							$stmt->bindParam(1, $cc_agent_status_uuid, PDO::PARAM_STR);
							$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
							$stmt->bindParam(3, $domain_name, PDO::PARAM_STR);
							$stmt->bindParam(4, $queue_name, PDO::PARAM_STR);
							$stmt->bindParam(5, $agent_name, PDO::PARAM_STR);
							$stmt->bindParam(6, $agent_state, PDO::PARAM_STR);
							$stmt->bindParam(7, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(8, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(9, date("Y-m-d H:i:s", $epoch), PDO::PARAM_STR);
							$stmt->bindParam(10, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(11, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(12, $disposition_code, PDO::PARAM_STR);

							if (!$stmt->execute())
							{
								$log["status"] = "error";
								$log["status_type"] = "execute_failed";
								return $log;
							}
						}
						catch(PDOExecption $e) 
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}			
					}
					
					$log["status"] = "ok";
					$log["status_type"] = "";
					return $log;
				}

				$cmd = "api callcenter_config agent get status $agent_domain";
				$status_check = trim($core->event_socket_request($fp, $cmd));
				
				if(strtolower($status_check) == "available")
				{
					$log["status"] = "ok";
					$log["status_type"] = "";
					return $log;
				}
				else
				{
					$log["status"] = "error";
					$log["status_type"] = "not_found_failed";
					return $log;
				}
			}
			else
			{
				if (strtolower($status_current) == "available")
				{
					$log["status"] = "ok";
					$log["status_type"] = "";
					return $log;
				}
				else
				{
					$log["status"] = "error";
					$log["status_type"] = "not_found_failed";
					return $log;
				}
			}
		}
		else
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
	}

	public function setAgentLogout($args)
	{
		$agent = (string)$args['agent'];
		
		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain, ";
		$sql .= "d.domain_uuid, d.domain_name, t.agent_name, "; 
		$sql .= "t.tier_level, t.tier_position, q.queue_name, q.queue_extension "; 
		$sql .= "FROM v_call_center_queues q "; 
		$sql .= "INNER JOIN v_call_center_tiers t ON t.queue_name = q.queue_name "; 
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = q.domain_uuid "; 
		$sql .= "WHERE t.agent_name = ? "; 
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				if($stmt->rowCount() > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$agent_domain = $row["agent_domain"];
						$queue_domain = $row["queue_domain"];
						$domain_uuid = $row["domain_uuid"];
						$domain_name = $row["domain_name"];
						$agent_name = $row["agent_name"];
						$queue_name = $row["queue_name"];
						$tier_level = $row["tier_level"];
						$tier_position = $row["tier_position"];
						$queue_extension = $row["queue_extension"];
					}
				}
				else
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$core = new Core();
		
		$login_interval = 4*60*60;
		$epoch = time();

		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		if ($fp)
		{			
			$cmd = "api callcenter_config agent get status $agent_domain";
			$status_current = trim($core->event_socket_request($fp, $cmd));
			
			/**
				fica disponivel se estiver deslogado ou em pausa
			*/
			
			if (strtolower($status_current) == "available")
			{
				$cmd = "api callcenter_config tier del $queue_domain $agent_domain $tier_level $tier_position";
				$response = trim($core->event_socket_request($fp, $cmd));
				
				$cmd = "api callcenter_config agent set status $agent_domain 'Logged Out'";
				$response = trim($core->event_socket_request($fp, $cmd));
				
				$cmd = "api callcenter_config agent set state $agent_domain 'Waiting'";
				$response = trim($core->event_socket_request($fp, $cmd));
				
				if($response == '+OK')
				{
					$call_center_agent_status_uuid = $core->uuid();
					$agent_status = "logout";
					$user_action = "api";
					$disposition_code = "o";
					
					$epoch = time();
					$sql  = "insert into v_xml_cdr_call_center_agent ";
					$sql .= "(";
					$sql .= "call_center_agent_status_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "cc_queue, ";
					$sql .= "cc_agent_name, ";
					$sql .= "agent_status, ";
					$sql .= "user_action, ";
					$sql .= "start_stamp, ";
					$sql .= "start_epoch, ";
					$sql .= "disposition_code";
					$sql .= ") values (";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?, ";
					$sql .= "?";
					$sql .= ")";
					$stmt = $this->conn->prepare($sql);
					
					try
					{
						$stmt->bindParam(1, $call_center_agent_status_uuid, PDO::PARAM_STR);
						$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
						$stmt->bindParam(3, $queue_name, PDO::PARAM_STR);
						$stmt->bindParam(4, $agent_name, PDO::PARAM_STR);
						$stmt->bindParam(5, $agent_status, PDO::PARAM_STR);
						$stmt->bindParam(6, $user_action, PDO::PARAM_STR);
						$stmt->bindParam(7, date("Y-m-d H:i:s", $epoch), PDO::PARAM_STR);
						$stmt->bindParam(8, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(9, $disposition_code, PDO::PARAM_STR);
					
						if (!$stmt->execute())
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}
					}
					catch(PDOExecption $e) 
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
					
					$cc_agent_status_uuid = null;
					
					$sql  = "select cc_agent_status_uuid, first_login, last_login, worked_duration ";
					$sql .= "from v_xml_cdr_call_center_agent_consolidate ";
					$sql .= "where domain_uuid = ? ";
					$sql .= "and cc_agent_name = ? ";
					$sql .= "and (logout < last_login or logout is null) ";
					$sql .= "order by start_epoch desc limit 1 ";
					$stmt = $this->conn->prepare($sql);
					
					try
					{
						$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
						$stmt->bindParam(2, $agent_name, PDO::PARAM_STR);
					
						if ($stmt->execute())
						{
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$tmp_login = $row["first_login"];
								$last_login = $row["last_login"];
								$worked_duration = $row["worked_duration"];
								$cc_agent_status_uuid = $row["cc_agent_status_uuid"];
								
								if ($last_login)
								{
									$tmp_login = $last_login;
								}
							}
						}
					}
					catch(PDOExecption $e) 
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;			
					}
					
					if ($tmp_login)
					{
						if (strlen($worked_duration) == 0 or !($worked_duration))
						{
							$worked_duration = 0;
						}
						
						$worked_duration = $worked_duration + ($epoch - $tmp_login);
					}
				
					if ($cc_agent_status_uuid)
					{
						$sql  = "update v_xml_cdr_call_center_agent_consolidate set ";
						$sql .= "logout = ?, ";
						$sql .= "state = 'logout', ";
						$sql .= "state_epoch = ?, ";
						$sql .= "worked_duration = ? ";
						$sql .= "where cc_agent_status_uuid = ? ";
						$stmt = $this->conn->prepare($sql);
						
						try
						{
							$stmt->bindParam(1, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(2, $epoch, PDO::PARAM_STR);
							$stmt->bindParam(3, $worked_duration, PDO::PARAM_STR);
							$stmt->bindParam(4, $cc_agent_status_uuid, PDO::PARAM_STR);

							if (!$stmt->execute())
							{
								$log["status"] = "error";
								$log["status_type"] = "execute_failed";
								return $log;
							}
						}
						catch(PDOExecption $e)
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}
					}
					
					$cmd = "api callcenter_config agent get status $agent_domain";
					$status_check = trim($core->event_socket_request($fp, $cmd));
					
					if(strtolower($status_check) == "logged out")
					{
						$log["status"] = "ok";
						$log["status_type"] = "";
						return $log;
					}
					else
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				else
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
			}
		}
		else
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
	}
	
	public function setAgentBreak($args)
	{
		$agent = (string)$args['agent'];
		$break_id = (string)$args['break_id'];
		
		$cc = new CallCenter();
		$break_uuid = $cc->getBreakUuid($agent, $break_id);
		
		error_log("break_uuid " . $break_uuid);
		if(strlen($break_uuid) == 0)
		{
			$log["status"] = "error";
			$log["status_type"] = "not_found_failed";
			return $log;
		}

		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain, ";
		$sql .= "d.domain_uuid, d.domain_name, t.agent_name, "; 
		$sql .= "t.tier_level, t.tier_position, q.queue_name, q.queue_extension "; 
		$sql .= "FROM v_call_center_queues q "; 
		$sql .= "INNER JOIN v_call_center_tiers t ON t.queue_name = q.queue_name "; 
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = q.domain_uuid "; 
		$sql .= "WHERE t.agent_name = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent_domain = $row["agent_domain"];
					$queue_domain = $row["queue_domain"];
					$domain_uuid = $row["domain_uuid"];
					$domain_name = $row["domain_name"];
					$agent_name = $row["agent_name"];
					$queue_domain = $row["queue_domain"];
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e) 
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$core = new Core();
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api callcenter_config agent get status $agent_domain";
		$status_current = trim($core->event_socket_request($fp, $cmd));

		if(strtolower($status_current) == "available")
		{
			if($fp)
			{
				$cmd = "api callcenter_config agent set status $agent_domain 'On Break'";
				$response = trim($core->event_socket_request($fp, $cmd));
				
				$cmd = "api callcenter_config agent set state $agent_domain '$break_uuid'";
				$response = trim($core->event_socket_request($fp, $cmd));
				
				$call_center_agent_status_uuid = $core->uuid();
				$epoch = time();
				
				$sql  = "insert into v_xml_cdr_call_center_agent ";
				$sql .= "(";
				$sql .= "call_center_agent_status_uuid, ";
				$sql .= "domain_uuid, ";
				$sql .= "cc_queue, ";
				$sql .= "cc_agent_name, ";
				$sql .= "agent_status, ";
				$sql .= "break_uuid, ";
				$sql .= "user_action, ";
				$sql .= "start_stamp, ";
				$sql .= "start_epoch, ";
				$sql .= "disposition_code";
				$sql .= ") values (";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "'on break', ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "?, ";
				$sql .= "'p'";
				$sql .= ")";
				$stmt = $this->conn->prepare($sql);
				
				try
				{
					$stmt->bindParam(1, $call_center_agent_status_uuid, PDO::PARAM_STR);
					$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
					$stmt->bindParam(3, $queue_name, PDO::PARAM_STR);
					$stmt->bindParam(4, $agent_name, PDO::PARAM_STR);
					$stmt->bindParam(5, $break_uuid, PDO::PARAM_STR);
					$stmt->bindParam(6, $caller_id_number, PDO::PARAM_STR);
					$stmt->bindParam(7, date("Y-m-d H:m:s", $epoch), PDO::PARAM_STR);
					$stmt->bindParam(8, $epoch, PDO::PARAM_STR);
					
					if (!$stmt->execute())
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				catch(PDOExecption $e) 
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
				
				/**
					atualiza xml_agent
				*/
				
				$cc_agent_status_uuid = null;
				
				$sql  = "select cc_agent_status_uuid from v_xml_cdr_call_center_agent_consolidate ";
				$sql .= "where domain_uuid = ? ";
				$sql .= "and cc_agent_name= ? ";
				$sql .= "and (logout < last_login or logout is null) ";
				$sql .= "order by start_epoch desc limit 1 ";
				$stmt = $this->conn->prepare($sql);
				
				try
				{
					$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
					$stmt->bindParam(2, $agent_name, PDO::PARAM_STR);
					
					if ($stmt->execute())
					{
						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$cc_agent_status_uuid = $row["cc_agent_status_uuid"];
						}
					}
					else
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				catch(PDOExecption $e) 
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}

				if ($cc_agent_status_uuid)
				{
					$sql  = "update v_xml_cdr_call_center_agent_consolidate set ";
					$sql .= "state = 'on break', ";
					$sql .= "state_epoch = ? ";
					$sql .= "where cc_agent_status_uuid = ? ";
					
					try
					{
						$stmt->bindParam(1, $epoch, PDO::PARAM_STR);
						$stmt->bindParam(2, $cc_agent_status_uuid, PDO::PARAM_STR);
						
						if (!$stmt->execute())
						{
							$log["status"] = "error";
							$log["status_type"] = "execute_failed";
							return $log;
						}
					}
					catch(PDOExecption $e)
					{
						$log["status"] = "error";
						$log["status_type"] = "execute_failed";
						return $log;
					}
				}
				
				$log["status"] = "ok";
				$log["status_type"] = "";
				return $log;
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		else
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}		
	}
	
	public function setAgentBreakReturn($args)
	{
		$agent = (string)$args['agent'];
		$domain_uuid = (string)$args['company_id'];

		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain, ";
		$sql .= "d.domain_uuid, d.domain_name, t.agent_name, "; 
		$sql .= "t.tier_level, t.tier_position, q.queue_name, q.queue_extension "; 
		$sql .= "FROM v_call_center_queues q "; 
		$sql .= "INNER JOIN v_call_center_tiers t ON t.queue_name = q.queue_name "; 
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = q.domain_uuid "; 
		$sql .= "WHERE t.agent_name = ? and d.domain_uuid = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent_domain = $row["agent_domain"];
					$queue_domain = $row["queue_domain"];
					$domain_uuid = $row["domain_uuid"];
					$domain_name = $row["domain_name"];
					$agent_name = $row["agent_name"];
					$queue_domain = $row["queue_domain"];
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$core = new Core();
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api callcenter_config agent set status $agent_domain 'Available'";
		$response = trim($core->event_socket_request($fp, $cmd));
		
		$cmd = "api callcenter_config agent set state $agent_domain 'Waiting'";
		$response = trim($core->event_socket_request($fp, $cmd));
		
		/**
			Último Status
		*/
		
		$sql  = " select call_center_agent_status_uuid, agent_status, start_epoch"; 
		$sql .= " from v_xml_cdr_call_center_agent";
		$sql .= " where cc_agent_name = ?";
		$sql .= " order by start_epoch desc";
		$sql .= " limit 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$call_center_agent_status_uuid = $row["call_center_agent_status_uuid"];
					$agent_status = $row["agent_status"];
					$start_epoch = $row["start_epoch"];
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e) 
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		if($agent_status == 'on break')
		{
			$epoch = time();
			$duration = $epoch - $start_epoch;
			
			$sql  = " update v_xml_cdr_call_center_agent set";
			$sql .= " end_epoch = ?,";
			$sql .= " duration = ?";
			$sql .= " where call_center_agent_status_uuid = ?";
			$stmt = $this->conn->prepare($sql);

			try
			{
				$stmt->bindParam(1, $epoch, PDO::PARAM_STR);
				$stmt->bindParam(2, $duration, PDO::PARAM_STR);
				$stmt->bindParam(3, $call_center_agent_status_uuid, PDO::PARAM_STR);

				if (!$stmt->execute())
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
			}
			catch(PDOExecption $e) 
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		else
		{
			$core = new Core();
			$call_center_agent_status_uuid = $core->uuid();
			
			$epoch = time();
			$sql  = "insert into v_xml_cdr_call_center_agent ";
			$sql .= "(";
			$sql .= "call_center_agent_status_uuid, ";
			$sql .= "domain_uuid, ";
			$sql .= "cc_queue, ";
			$sql .= "cc_agent_name, ";
			$sql .= "agent_status, ";
			$sql .= "user_action, ";
			$sql .= "start_stamp, ";
			$sql .= "start_epoch, ";
			$sql .= "disposition_code";
			$sql .= ") values (";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?, ";
			$sql .= "?";
			$sql .= ")";

			try
			{
				$stmt->bindParam(1, $call_center_agent_status_uuid, PDO::PARAM_STR);
				$stmt->bindParam(2, $domain_uuid, PDO::PARAM_STR);
				$stmt->bindParam(3, $queue_name, PDO::PARAM_STR);
				$stmt->bindParam(4, $agent_name, PDO::PARAM_STR);
				$stmt->bindParam(5, 'break_return', PDO::PARAM_STR);
				$stmt->bindParam(6, '', PDO::PARAM_STR);
				$stmt->bindParam(7, date("Y-m-d H:i:s", $epoch), PDO::PARAM_STR);
				$stmt->bindParam(8, $epoch, PDO::PARAM_STR);
				$stmt->bindParam(9, 'P', PDO::PARAM_STR);

				if(!$stmt->execute())
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
			}
			catch(PDOExecption $e) 
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		
		/**
			atualiza xml_agent
		*/
		
		$cc_agent_status_uuid = null;
		
		$sql  = " select cc_agent_status_uuid, state_epoch, break_duration";
		$sql .= " from v_xml_cdr_call_center_agent_consolidate";
		$sql .= " where domain_uuid = ?";
		$sql .= " and cc_agent_name = ?";
		$sql .= " and (logout < last_login or logout is null)";
		$sql .= " and state = 'on break'";
		$sql .= " order by start_epoch desc limit 1";
		$stmt = $this->conn->prepare($sql);

		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $agent_name, PDO::PARAM_STR);

			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$cc_agent_status_uuid = $row["cc_agent_status_uuid"];
					$state_epoch = $row["state_epoch"];
					$break_duration = $row["break_duration"];
				}
			}
			else
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}

		if($cc_agent_status_uuid)
		{
			if (!$break_duration || strlen($break_duration) == 0)
			{
				$break_duration = 0;
			}
			
			$break_duration = $break_duration + ($epoch - $state_epoch);
			
			$sql  = "update v_xml_cdr_call_center_agent_consolidate set ";
			$sql .= "state = 'available'".", ";
			$sql .= "state_epoch = ?, ";
			$sql .= "break_duration = ? ";
			$sql .= "where cc_agent_status_uuid = ? ";
			$stmt = $this->conn->prepare($sql);

			try
			{
				$stmt->bindParam(1, $epoch, PDO::PARAM_STR);
				$stmt->bindParam(2, $break_duration, PDO::PARAM_STR);
				$stmt->bindParam(3, $cc_agent_status_uuid, PDO::PARAM_STR);

				if(!$stmt->execute())
				{
					$log["status"] = "error";
					$log["status_type"] = "execute_failed";
					return $log;
				}
			}
			catch(PDOExecption $e) 
			{
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		
		$log["status"] = "ok";
		$log["status_type"] = "";
		return $log;
	}

	public function getAgentStatusLogin($args)
	{
		/**
			103
			Status de login do ramal
		*/
		
		/**
			get arguments
		*/
	
		$ext = (string)$args['ext'];
		
		$sql  = "SELECT CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain ";
		$sql .= "FROM v_call_center_tiers t "; 
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = t.domain_uuid "; 
		$sql .= "WHERE t.agent_name = ? "; 
		$sql .= "LIMIT 1 ";
		
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				if($stmt->rowCount() > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$agent_domain = $row["agent_domain"];
					}
				}
				else
				{
					return "103@41";
				}
			}
			else
			{
				return "103@41";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";
		}
				
		$core = new Core();
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		if ($fp)
		{
			$cmd = "api callcenter_config agent get status $agent_domain";
			$status_current = trim($core->event_socket_request($fp, $cmd));
			
			if (strtolower($status_current) == "available")
			{
				return "103@00";
			}
			
			if (strtolower($status_current) == "logged out")
			{
				return "103@01";
			}
			
			return "103@41";
		}
		else
		{
			return "103@41";
		}
	}

	public function setAgentLeavePause($args)
	{
		/**
			202
			Listando os agentes em pausa
		*/
		
		/**
			get arguments
		*/
		
		$ext = (string)$args['ext'];
		$pwd = (string)$args['pwd'];
		$key = (string)$args['key'];
		$pse = (string)$args['pse'];
		
		$cc = new CallCenter();
		$domain_uuid = $cc->getDomainUuid($ext);
		
		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain," ;
		$sql .= "d.domain_uuid, d.domain_name, t.agent_name, ";
		$sql .= "t.tier_level, t.tier_position, q.queue_name, q.queue_extension ";
		$sql .= "FROM v_call_center_tiers t ";
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = t.domain_uuid ";
		$sql .= "INNER JOIN v_call_center_queues q ON q.queue_name = t.queue_name ";
		$sql .= "WHERE t.domain_uuid = ?";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				$agent_all = array();
				
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent = array();
					$agent["agent_domain"] = $row["agent_domain"];
					$agent["queue_domain"] = $row["queue_domain"];
					$agent["domain_uuid"] = $row["domain_uuid"];
					$agent["domain_name"] = $row["domain_name"];
					$agent["agent_name"] = $row["agent_name"];
					$agent["queue_domain"] = $row["queue_domain"];
					array_push($agent_all, $agent);
				}
			}
			else
			{
				return "Failed";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";
		}
		
		$sql  = "SELECT call_center_break_break_uuid, break_name "; 
		$sql .= "FROM v_call_center_break_breaks ";
		$sql .= "WHERE domain_uuid = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				$break_breaks_all = array();
				
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$break_breaks = array();
					$break_breaks["agent_domain"] = $row["agent_domain"];
					$break_breaks["queue_domain"] = $row["queue_domain"];
					array_push($break_breaks_all, $break_breaks);
				}
			}
			else
			{
				return "Failed";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";
		}
		
		$core = new Core();
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api callcenter_config tier list";
		$event_socket_str = trim($core->event_socket_request($fp, $cmd));
		
		$tier_list_fs[] = $core->csv_to_named_array($event_socket_str, '|');
		
		$breaks = array();
		$breaks_all = array();
		
		foreach($agent_all as $agent)
		{
			$agent_domain = $agent["agent_domain"];
			$queue_domain = $agent["queue_domain"];
			
			foreach($tier_list_fs as $agent_tier_fs)
			{
				foreach($agent_tier_fs as $agent_tier_fs_row)
				{
					if ($agent_domain && $agent_tier_fs_row['queue'])
					{
						if($queue_domain == $agent_tier_fs_row['queue'])
						{
							if ($fp)
							{	
								$cmd = "api callcenter_config agent get status $agent_domain";
								$response = event_socket_request($fp, $cmd);
								$queue_arr[$queue_name][$agent_name]['status']= $response;
								
								$cmd = "api callcenter_config agent get state $agent_domain";
								$response = trim(event_socket_request($fp, $cmd));
								$queue_arr[$queue_name][$agent_name]['state']= $response;							
							}
						}
					}
				}
			}
		}
			
		return $breaks_all;
	}

	public function getAgentStatusPause($args)
	{
		/**
			203
			Verifica se o ramal está pausado
		*/
		
		$ext = (string)$args['ext'];
		$pwd = (string)$args['pwd'];
		
		$core = new Core();
		$cc = new CallCenter();

		$domain_name = $cc->getDomain($ext);
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api callcenter_config agent get status $ext@$domain_name";
		$status = trim($core->event_socket_request($fp, $cmd));
		
		if (strtolower($status) == "on break")
		{
			/** 
				Ramal está em pausa 
			*/
			
			return "103@01";
		}
		else
		{
			/** 
				Ramal não está em pausa 
			*/
			
			return "203@00";
		}
		
		if (strtolower($status) == "-err agent not found!")
		{
			/** 
				Ramal não encontrado 
			*/
			
			return "203@43";
		}
		
		return "203@41";
	}

	public function getAgentListingPausesRegistered($args)
	{
		/**
			204
			Buscando a lista de pausas 
		*/
		
		/** 
			get arguments 
		*/
		
		$ext = (string)$args['ext'];
		$pwd = (string)$args['pwd'];
		
		$sql  = " SELECT * FROM v_call_center_break_breaks ccb";
		$sql .= " INNER JOIN v_call_center_queues ccq ON ccq.call_center_break_uuid = ccb.call_center_break_uuid";
		$sql .= " INNER JOIN v_call_center_tiers cct ON cct.queue_name = ccq.queue_name";
		$sql .= " WHERE cct.agent_name = ?";
		$sql .= " ORDER BY ccb.break_digit ASC";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				$rowCount = $stmt->rowCount();
				$breaks = "204@00@";
				
				if($rowCount > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$breaks .= $row["break_digit"];
						$breaks .= ":" . $row["break_name"] . "&";
					}
					
					return substr($breaks,0,-1);
				}
				else
				{
					return "204@01";
				}				
			}
			else 
			{
				return "204@01";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";			
		}
	}

	public function setCall($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();

		$domain_uuid = $params["company_id"];
		$domain_name = $callcenter->getDomainNameUUID($domain_uuid);
		$extension = $params["branch"];
		$destination = $params["to"];
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$sched_seconds = 3;
		$uuid = $core->uuid();
		$cmd  = "api bgapi sched_api +$sched_seconds $uuid bgapi originate";
		$cmd .= " {";
		$cmd .= "sip_auto_answer=true,";
		$cmd .= "domain_name=$domain_name,";
		$cmd .= "originate_src=originate_broadcast,";
		$cmd .= "originate_api='true',";
		$cmd .= "export_vars=originate_api,";
		$cmd .= "origination_caller_id_number=$extension,";
		$cmd .= "sip_h_Call-Info=_undef_}user/$extension@$domain_name $destination XML $domain_name";
		$response = trim($core->event_socket_request($fp, $cmd));
		
		sleep(10);
		
		$switch_cmd = 'api show channels as json';
		$json = trim($core->event_socket_request($fp, $switch_cmd));
		$results = json_decode($json, "true");
		
		$calls_uuid = array();
		
		if($results["row_count"] > 0)
		{
			foreach ($results["rows"] as $row)
			{
				foreach ($row as $key => $value)
				{
					$$key = $value;
				}

				/**
					get the sip profile
				*/
				
				$name_array = explode("/", $name);
				$sip_profile = $name_array[1];
				$sip_uri = $name_array[2];

				/**
					get the number
				*/
				
				$temp_array = explode("@", $sip_uri);
				$tmp_number = $temp_array[0];
				$tmp_number = str_replace("sip:", "", $tmp_number);

				/**
					remove the '+' because it breaks the call recording
				*/
				
				$cid_num = str_replace("+", "", $cid_num);

				if($cid_num == $extension)
				{
					$calls_status["call_uuid"] = $uuid;
					$calls_status["status"] = "ok";
					$calls_status["status_type"] = "";
					return $calls_status;
				}
			}
		}
		else
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
	}
	
	public function setCallOld($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();

		$domain_uuid = $params["domain_uuid"];
		$domain_name = $callcenter->getDomainNameUUID($domain_uuid);
		$extension = $params["extension"];
		$destination = $params["destination"];
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$sched_seconds = 3;
		$uuid = $core->uuid();
		$cmd  = "api bgapi sched_api +$sched_seconds $uuid bgapi originate";
		$cmd .= " {";
		$cmd .= "sip_auto_answer=true,";
		$cmd .= "domain_name=$domain_name,";
		$cmd .= "originate_src=originate_broadcast,";
		$cmd .= "originate_api='true',";
		$cmd .= "export_vars=originate_api,";
		$cmd .= "origination_caller_id_number=$extension,";
		$cmd .= "sip_h_Call-Info=_undef_}user/$extension@$domain_name $destination XML $domain_name";
		$response = trim($core->event_socket_request($fp, $cmd));
		
		sleep(10);
		
		$switch_cmd = 'api show channels as json';
		$json = trim($core->event_socket_request($fp, $switch_cmd));
		$results = json_decode($json, "true");
		
		$calls_uuid = array();
		
		if($results["row_count"] > 0)
		{
			foreach ($results["rows"] as $row)
			{
				foreach ($row as $key => $value)
				{
					$$key = $value;
				}

				$name_array = explode("/", $name);
				$sip_profile = $name_array[1];
				$sip_uri = $name_array[2];

				$temp_array = explode("@", $sip_uri);
				$tmp_number = $temp_array[0];
				$tmp_number = str_replace("sip:", "", $tmp_number);

				$cid_num = str_replace("+", "", $cid_num);

				if($tmp_number == $extension)
				{
					error_log($uuid);
					$calls_uuid["call_uuid"] = $uuid;
					return $calls_uuid;
				}
			}
		}
		else
		{
			return "409@00";
		}
	}
	
	public function setTransfer($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();
		
		$destination = $params["to"];
		$call_id = $params["call_id"];
		
		$domain_uuid = $params["company_id"];
		$domain_name = $callcenter->getDomainNameUUID($domain_uuid);
		
		if(strlen($domain_name) == 0 || strlen($call_id) == 0)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$fp = $core->event_socket_create();
		$cmd = "api bgapi uuid_transfer $call_id $destination XML $domain_name";
		$response = trim($core->event_socket_request($fp, $cmd));

		if(preg_match('/OK/i', $response))
		{
			$log["status"] = "ok";
			$log["status_type"] = "";
			return $log;
		}
		
		$log["status"] = "error";
		$log["status_type"] = "execute_failed";
		return $log;
	}
	
	public function setHold($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();
		
		$destination = $params["to"];
		$call_id = $params["call_id"];
		
		$domain_uuid = $params["company_id"];
		$domain_name = $callcenter->getDomainNameUUID($domain_uuid);
		
		if(strlen($domain_name) == 0 || strlen($call_id) == 0)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$fp = $core->event_socket_create();
		$cmd = "api bgapi uuid_hold $call_id";
		$response = trim($core->event_socket_request($fp, $cmd));

		if(preg_match('/OK/i', $response))
		{
			$log["status"] = "ok";
			$log["status_type"] = "";
			return $log;
		}
		
		$log["status"] = "error";
		$log["status_type"] = "execute_failed";
		return $log;
	}
	
	public function setCallHoldOff($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();
		
		$destination = $params["to"];
		$call_id = $params["call_id"];
		
		$domain_uuid = $params["company_id"];
		$domain_name = $callcenter->getDomainNameUUID($domain_uuid);
		
		if(strlen($domain_name) == 0 || strlen($call_id) == 0)
		{
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;
		}
		
		$fp = $core->event_socket_create();
		$cmd = "api bgapi uuid_hold $call_id";
		$response = trim($core->event_socket_request($fp, $cmd));

		if(preg_match('/OK/i', $response))
		{
			$log["status"] = "ok";
			$log["status_type"] = "";
			return $log;
		}
		
		$log["status"] = "error";
		$log["status_type"] = "execute_failed";
		return $log;
	}
	
	public function getAgentList($params)
	{
		$core = new Core();
		$callcenter = new CallCenter();
		
		$company_id = $params["company_id"];
		
		$domain_name = $callcenter->getDomainNameUUID($company_id);
		$queue_name = $params["queue"];
		
		$queue_name = $queue_name . "@" . $domain_name;
		
		/**
			setup the event socket connection
		*/

		$fp = $core->event_socket_create();
		
		$switch_cmd = 'api callcenter_config queue list tiers '.$queue_name;
		$event_socket_str = trim($core->event_socket_request($fp, $switch_cmd));
		$result = $core->str_to_named_array($event_socket_str, '|');
		
		$x = 0;
		foreach ($result as $row)
		{
			$tier_result[$x]['level'] = $row['level'];
			$tier_result[$x]['position'] = $row['position'];
			$tier_result[$x]['agent'] = $row['agent'];
			$tier_result[$x]['state'] = trim($row['state']);
			$tier_result[$x]['queue'] = $row['queue'];
			$x++;
		}
		
		array_multisort($tier_result, SORT_ASC);

		$switch_cmd = 'api callcenter_config queue list agents '.$queue_name;
		$event_socket_str = trim($core->event_socket_request($fp, $switch_cmd));
		$agent_result = $core->str_to_named_array($event_socket_str, '|');
		
		$switch_cmd = 'api callcenter_config queue list members '.$queue_name;
		$event_socket_str = trim($core->event_socket_request($fp, $switch_cmd));
		$result_members = $core->str_to_named_array($event_socket_str, '|');
	
		$sql = "select call_center_break_break_uuid, break_name, break_timeout from v_call_center_break_breaks ";
		$sql.= "where domain_uuid = ? ";
		$stmt_break_list = $this->conn->prepare($sql);
		unset($prep_statement);
		
		try
		{
			$stmt_break_list->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt_break_list->execute();
		}
		catch(PDOExecption $e) 
		{
		}
		
		$agent_count = 0;
		$agent_response = array();
		
		$agent_response["logged_out_agents"] = "";
		$agent_response["logged_in_agents"] = "";
		
		foreach ($tier_result as $tier_row)
		{
			foreach ($agent_result as $agent_row)
			{
				if ($tier_row['agent'] == $agent_row['name'])
				{
					$tmp_name = $agent_row['name'];
					$tmp_name = str_replace('@'.$domain_name, '', $tmp_name);
					
					if ($agent_row['status'] != "Logged Out")
					{
						$agent_response["logged_in_agents"][$agent_count]['name'] = $tmp_name;
						$agent_response["logged_in_agents"][$agent_count]['status'] = $agent_row['status'];
						
						$contact = $agent_row['contact'];
						$a_exten = preg_replace("/user\//", "", $contact);
						$a_exten = preg_replace("/@.*/", "", $a_exten);
						$a_exten = preg_replace("/{.*}/", "", $a_exten);
						
						$tmp_arr = explode('/', $a_exten);
						$tmp_arr = explode('=', $tmp_arr[count($tmp_arr)-1]);										 
						$tmp = $tmp_arr[count($tmp_arr)-1];
						$tmp = strlen($tmp) == 0 ? $a_exten : $tmp;
						$agent_response["logged_in_agents"][$agent_count]['branch'] = $tmp;
						
						if ($agent_row['status'] == "On Break")
						{
							while ($row = $stmt_break_list->fetch(PDO::FETCH_ASSOC))
							{
								if ($row_break['call_center_break_break_uuid'] == $agent_row['state'])
								{
									$agent_logged[$tier_row['agent']]['break_name'] = $row_break['break_name'];
									$agent_logged[$tier_row['agent']]['break_timeout'] = $row_break['break_timeout'] * 60;
									
									if (strlen($agent_logged[$tier_row['agent']]['break_timeout']) > 0 )
									{
										$tmp_break_timeout = gmdate("H:i:s", ($agent_logged[$tier_row['agent']]['break_timeout']));
									}
									else
									{
										$tmp_break_timeout = '';
									}
									
									$agent_logged[$tier_row['agent']]['break_name'] = $agent_logged[$tier_row['agent']]['break_name']." ".$tmp_break_timeout;

									break;
								}
							}
						}
						
						$sql  = "SELECT * FROM v_xml_cdr_call_center_agent_consolidate c ";
						$sql .= "WHERE c.cc_agent_name = ? AND c.domain_uuid = ? ";
						$sql .= "ORDER BY c.start_stamp DESC ";
						$sql .= "LIMIT 1 ";
						$stmt = $this->conn->prepare($sql);
						
						try
						{
							$stmt->bindParam(1, $tmp_name, PDO::PARAM_STR);
							$stmt->bindParam(2, $company_id, PDO::PARAM_STR);
							
							if ($stmt->execute())
							{
								if($stmt->rowCount() > 0)
								{
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
									{
										$first_login = $row["first_login"];
										$answered_count = $row["answered_count"];
										$answered_duration = $row["answered_duration"];
										$not_answered_count = $row["not_answered_count"];
										$outbound_count = $row["outbound_count"];
										$outbound_duration = $row["outbound_duration"];
									}
								}
							}
						}
						catch(PDOExecption $e) 
						{
						}
						
						$agent_response["logged_in_agents"][$agent_count]['first_login'] = $first_login;
						$agent_response["logged_in_agents"][$agent_count]['answered_count'] = $answered_count;
						$agent_response["logged_in_agents"][$agent_count]['answered_duration'] = $answered_duration;
						$agent_response["logged_in_agents"][$agent_count]['not_answered_count'] = $not_answered_count;
						$agent_response["logged_in_agents"][$agent_count]['outbound_count'] = $outbound_count;
						$agent_response["logged_in_agents"][$agent_count]['outbound_duration'] = $outbound_duration;
						
						$agent_count++;					
					}
					
					if ($agent_row['status'] == "Logged Out")
					{
						$agent_response["logged_out_agents"][$agent_count]['name'] = $tmp_name;
						$agent_response["logged_out_agents"][$agent_count]['status'] = $agent_row['status'];
						$agent_response["logged_out_agents"][$agent_count]['no_answer_count'] = $agent_row['no_answer_count'];
						$agent_response["logged_out_agents"][$agent_count]['calls_answered'] = $agent_row['calls_answered'];
						$agent_response["logged_out_agents"][$agent_count]['ready_time'] = $agent_row['ready_time'];
						
						$agent_count++;
					}
				}
			}
		}
		
		
		$log["status"] = "error";
		$log["status_type"] = "execute_failed";
		return $agent_response;
	}
	
	public function showChannels($extension)
	{
		$core = new Core();
		$fp = $core->event_socket_create();

		if($fp)
		{
			$switch_cmd = 'api show channels as json';
			$json = trim($core->event_socket_request($fp, $switch_cmd));
			$results = json_decode($json, "true");
			
			$calls_all = array();
		}
		return "0";
	}

	public function setDisconnectCall($params)
	{
		/**
			302
			Desliga qualquer chamada para liberar o canal do operador
		*/
		
		$uuid_kill = $params["call_id"];
				
		$core = new Core();

		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api bgapi uuid_kill $uuid_kill";
		error_log($cmd);
		$originate = trim($core->event_socket_request($fp, $cmd));
	}

	public function getChecksStatusExtension()
	{
		/**
			303
			Verifica o ramal para saber se a ligação está em andamento
		*/
		
		$core = new Core();

		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		$cmd = "api bgapi uuid_kill $uuid_kill";
		$status = trim($core->event_socket_request($fp, $cmd));
	}

	public function getCallID()
	{
		/**
			304
			Faz uma discagem
		*/
		
		$core = new Core();

		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
				
		if ($fp)
		{
			$cmd = "api show channels as json";
			$json = trim($core->event_socket_request($fp, $cmd));
			$results = json_decode($json, "true");
		
			$calls_all = array();
			
			foreach ($results["rows"] as $row)
			{
				$calls = array();
				
				foreach ($row as $key => $value)
				{
					$$key = $value;
				}

				$name_array = explode("/", $name);
				$sip_profile = $name_array[1];
				$sip_uri = $name_array[2];

				$temp_array = explode("@", $sip_uri);
				$tmp_number = $temp_array[0];
				$tmp_number = str_replace("sip:", "", $tmp_number);

				$cid_num = str_replace("+", "", $cid_num);

				$calls["profile"] = $sip_profile;
				$calls["created"] = $created;
				$calls["number"] = $tmp_number;
				$calls["cid_name"] = $cid_name;
				$calls["cid_number"] = $cid_num;
				$calls["destination"] = $dest;
				
				if (strlen($application) > 0)
				{
					$calls["app"] = $application.":".$application_data;
				}
				else
				{
					$calls["app"] = "";
				}
				
				$calls["codec"] = $read_codec . ":" . $read_rate . "/" . $write_codec . ":" . $write_rate;
				$calls["secure"] = $secure;
				
				array_push($calls_all, $calls);
			}
			
			return $calls_all;
		}
		
		return "Failed";
	}
	
	public function getEavesdrop($params)
	{
		/**
			306
			Escutar ligações de outro ramal
		*/
		
		$agent_name = $params["agent_name"];
		$queue_name = $params["queue_name"];
		$eavesdrop = $params["eavesdrop"];
		
		$sql  = "SELECT ";
		$sql .= "CONCAT(t.agent_name, '@', d.domain_name) AS agent_domain, ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain, ";
		$sql .= "agent_name, ";
		$sql .= "domain_name ";
		$sql .= "FROM v_call_center_tiers t ";
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = t.domain_uuid ";
		$sql .= "INNER JOIN v_call_center_queues q ON q.queue_name = t.queue_name ";
		$sql .= "WHERE t.agent_name = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent_name, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				$num_rows = $stmt->rowCount();
				if($num_rows > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$agent_name = $row["agent_name"];
						$domain_name = $row["domain_name"];
						$agent_domain = $row["agent_domain"];
						$queue_domain = $row["queue_domain"];
					}
				}
				else
				{
					return "Failed";
				}
			}
			else
			{
				return "Failed";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";
		}
		
		$core = new Core();

		/**
			setup the event socket connection
		*/
				
		$fp = $core->event_socket_create();
				
		if ($fp)
		{
			/**
				get the queue agents list
			*/
			
			$switch_cmd = "api callcenter_config queue list agents $queue_domain";
			$event_socket_str = trim($core->event_socket_request($fp, $switch_cmd));
			$agent_result = $core->str_to_named_array($event_socket_str, '|');

			foreach($agent_result as $row)
			{
				$agent = explode('@',  $row["name"]);
				$agent = $agent[0];

				if($agent_name == $agent)
				{
					$uuid = $row['uuid'];
					$contact = $row['contact'];
					$extension = preg_replace("/user\//", "", $contact);
					$extension = preg_replace("/@.*/", "", $extension);
					$extension = preg_replace("/{.*}/", "", $extension);
				}
			}
			
			if(strlen($uuid) > 0)
			{
				$switch_cmd = "api originate {origination_caller_id_name=eavesdrop,origination_caller_id_number=$extension}user/$eavesdrop@$domain_name &eavesdrop($uuid)";
				$switch_result = trim($core->event_socket_request($fp, $switch_cmd));
				return "Success";
			}
			else
			{
				return "Failed";
			}
		}
		
		return "Failed";
	}
	
	public function getStatusQueue($args)
	{
		/**
			501
			Status da Fila
		*/
		
		/**
			get arguments
		*/
	
		$ext = (string)$args['ext'];
		
		$sql  = "SELECT ";
		$sql .= "CONCAT(q.queue_name, '@', d.domain_name) AS queue_domain ";
		$sql .= "FROM v_call_center_tiers t ";
		$sql .= "INNER JOIN v_domains d ON d.domain_uuid = t.domain_uuid ";
		$sql .= "INNER JOIN v_call_center_queues q ON q.queue_name = t.queue_name ";
		$sql .= "WHERE t.agent_name = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				if($stmt->rowCount() > 0)
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$queue_domain = $row["queue_domain"];
					}
				}
				else
				{
					return "201@40";
				}
			}
			else
			{
				return "201@40";
			}
		}
		catch(PDOExecption $e) 
		{
			return "409@00";
		}
		
		$core = new Core();

		/**
			setup the event socket connection
		*/

		$fp = $core->event_socket_create();

		if ($fp)
		{
			/**
				get the queue agents list
			*/
			
			$cmd = "api callcenter_config queue list members $queue_domain";
			$event_socket_str = trim($core->event_socket_request($fp, $cmd));
			$result_members = $core->str_to_named_array($event_socket_str, '|');
			$status_queue = array();
			$status_queue_all = array();
			
			$q_waiting=0;
			$q_trying=0;
			$q_answered=0;
			
			if(count($result_members) > 0)
			{
				foreach ($result_members as $row)
				{
					$queue = $row['queue'];
					$system = $row['system'];
					$uuid = $row['uuid'];
					$session_uuid = $row['session_uuid'];
					$caller_number = $row['cid_number'];
					$caller_name = $row['cid_name'];
					$system_epoch = $row['system_epoch'];
					$joined_epoch = $row['joined_epoch'];
					$rejoined_epoch = $row['rejoined_epoch'];
					$bridge_epoch = $row['bridge_epoch'];
					$abandoned_epoch = $row['abandoned_epoch'];
					$base_score = $row['base_score'];
					$skill_score = $row['skill_score'];
					$serving_agent = $row['serving_agent'];
					$serving_system = $row['serving_system'];
					$state = $row['state'];
					
					if ($state == "Trying")
					{
						$q_trying = $q_trying + 1;
					}
					
					if ($state == "Waiting")
					{
						$q_waiting = $q_waiting + 1;
					}
					
					if ($state == "Answered")
					{
						$q_answered = $q_answered + 1;
					}
					
					if ($row["state"] == "Answered" )
					{
						continue;
					}
					
					$joined_seconds = time() - $joined_epoch;
					$joined_length_hour = floor($joined_seconds/3600);
					$joined_length_min = floor($joined_seconds/60 - ($joined_length_hour * 60));
					$joined_length_sec = $joined_seconds - (($joined_length_hour * 3600) + ($joined_length_min * 60));
					$joined_length_min = sprintf("%02d", $joined_length_min);
					$joined_length_sec = sprintf("%02d", $joined_length_sec);
					$joined_length = $joined_length_hour.':'.$joined_length_min.':'.$joined_length_sec;

					if ($state == "Answered")
					{				
						$state = "Chamada Atendida";
						$c_state = 1;
					}
					
					if ($state == "Waiting")
					{
						$state = "Em Fila";
						$c_state = 1;
					}
					
					if ($state == "Trying")
					{
						$state_pt_br = "Em Fila";
						$c_state = 1;
					}
					
					if ($fp)
					{
						$switch_cmd = "api uuid_getvar $session_uuid cc_base_score";
						$level_priority = trim($core->event_socket_request($fp, $switch_cmd));
						$level_priority = $level_priority > 0 ? round($level_priority / 1000) : '0';
					}

					$status = array();
					$queue = explode('@',  $queue);
					$status["fila"] = $queue[0];
					$status["duracao"] = $joined_length;
					$status["numero"] = $core->formatNumber($caller_number);
					$status["prioridade"] = $level_priority;
					array_push($status_queue, $status);
				}
				
				$status_queue_all["retorno"] = "OK";
				$status_queue_all["retorno_codigo"] = "00";
				$status_queue_all["retorno_descricao"] = "TRANSAÇÃO EFETUADA COM SUCESSO";
				$status_queue_all["chamadas"] = $status_queue;
				$xml_user_info = new SimpleXMLElement("<?xml version=\"1.0\"?><chamadas_fila></chamadas_fila>");
				$core->array_to_xml($status_queue_all,$xml_user_info);
				return $xml_user_info->asXML();
			}	
		}
		
		return "Failed";
	}

	public function setConnectExtensionDialer($params)
	{
		/**
			Conectar o ramal com o discador
		*/
				
		$agent_name = $params["agent_name"];
		$queue_name = $params["queue_name"];
		$domain_name = $params["domain_name"];
		$domain_uuid = $params["domain_uuid"];

		$hd = new Core();
		$tier_uuid = $hd->uuid();
		
		$cc = new CallCenter();
		$valid_agent = $cc->getExistAgent($agent_name);
		$valid_tiers = $cc->getExistAgentTiers($agent_name);
		
		if($valid_agent == false)
		{
			return "Invalid Agent";
		}
		
		if($valid_tiers == 'true')
		{
			return "Agent already exists in the queue";
		}
		
		$sql  = "insert into v_call_center_tiers ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "call_center_tier_uuid, ";
		$sql .= "agent_name, ";
		$sql .= "queue_name, ";
		$sql .= "tier_level, ";
		$sql .= "tier_position ";
		$sql .= ")";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "?, ";
		$sql .= "0, ";
		$sql .= "1 ";
		$sql .= ")";
		$stmt = $this->conn->prepare($sql);
				
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $tier_uuid, PDO::PARAM_STR);
			$stmt->bindParam(3, $agent_name, PDO::PARAM_STR);
			$stmt->bindParam(4, $queue_name, PDO::PARAM_STR);

			if ($stmt->execute())
			{
				return "Success";
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

	public function setDisconnectingExtensionDialer($params)
	{
		$core = new Core();
		
		/**
			Desconectar o ramal do discador
		*/
		
		$agent_name = $params["agent_name"];
		$domain_name = $params["domain_name"];
		
		/**
			get the agent details
		*/
		
		$sql  = "SELECT CONCAT(ti.agent_name, '@', dom.domain_name) AS agent_domain, "; 
		$sql .= "CONCAT(ti.queue_name, '@', dom.domain_name) AS queue_domain, "; 
		$sql .= "dom.domain_name, dom.domain_uuid, ti.call_center_tier_uuid as tier_uuid, ti.agent_name, "; 
		$sql .= "ti.queue_name, ti.tier_level, ti.tier_position ";
		$sql .= "FROM v_call_center_tiers ti ";
		$sql .= "INNER JOIN v_domains dom ON dom.domain_uuid = ti.domain_uuid ";
		$sql .= "WHERE ti.agent_name = ? AND dom.domain_name = ? ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent_name, PDO::PARAM_STR);
			$stmt->bindParam(2, $domain_name, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent_domain = $row["agent_domain"];
					$queue_domain = $row["queue_domain"];
					$tier_uuid = $row["tier_uuid"];
					$domain_uuid = $row["domain_uuid"];
					$domain_name = $row["domain_name"];
					$agent_name = $row["agent_name"];
					$queue_name = $row["queue_name"];
					$tier_level = $row["tier_level"];
					$tier_position = $row["tier_position"];
				}
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
		
		/**
			setup the event socket connection
		*/
		
		$fp = $core->event_socket_create();
		
		/**
			delete the agent over event socket
		*/
		
		if ($fp)
		{
			/**
				first logged out
			*/
			
			$cmd = "api callcenter_config agent set status $agent_domain 'Logged Out'";
			$response = $core->event_socket_request($fp, $cmd);
			usleep(200);

			/**
				callcenter_config tier del
			*/
			
			$cmd = "api callcenter_config tier del $queue_domain $agent_domain";
			$response = $core->event_socket_request($fp, $cmd);
		}
		
		/**
			delete the tier from the database
		*/
		
		if (strlen($tier_uuid)>0)
		{
			$sql  = "DELETE FROM v_call_center_tiers ";
			$sql .= "WHERE domain_uuid = ? AND call_center_tier_uuid = ?";
			$stmt = $this->conn->prepare($sql);
			
			try
			{
				$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
				$stmt->bindParam(2, $tier_uuid, PDO::PARAM_STR);
				
				if ($stmt->execute())
				{
					return 'Success';
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
	}
	
	public function getDomainName($ext)
	{
		$sql  = "SELECT d.domain_name AS domain_name";
		$sql .= "FROM v_domains d ";
		$sql .= "INNER JOIN v_call_center_tiers cct ON cct.domain_uuid = d.domain_uuid ";
		$sql .= "WHERE cct.agent_name = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$domain_name = $row["domain_name"];
				}
				return $domain_name;
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
	
	public function getDomainNameUUID($domain_uuid)
	{
		$sql  = " SELECT d.domain_name AS domain_name";
		$sql .= " FROM v_domains d";
		$sql .= " WHERE d.domain_uuid = ?";
		$sql .= " LIMIT 1";
		
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					error_log($row["domain_name"]);
					$domain_name = $row["domain_name"];
				}
				return $domain_name;
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
	
	public function getDomainUuid($ext)
	{
		$sql  = "SELECT d.domain_uuid as domain_uuid ";
		$sql .= "FROM v_domains d ";
		$sql .= "INNER JOIN v_call_center_tiers cct ON cct.domain_uuid = d.domain_uuid ";
		$sql .= "WHERE cct.agent_name = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$domain_uuid = $row["domain_uuid"];
				}
				return $domain_uuid;
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
	
	public function getBreakUuid($agent, $break_id)
	{
		$sql  = "SELECT ccbb.call_center_break_break_uuid as break_uuid ";
		$sql .= "FROM v_call_center_tiers cct ";
		$sql .= "INNER JOIN v_call_center_queues ccq ON ccq.queue_name = cct.queue_name ";
		$sql .= "INNER JOIN v_call_center_break_breaks ccbb ON ccbb.call_center_break_uuid = ccq.call_center_break_uuid ";
		$sql .= "WHERE cct.agent_name = ? AND ccbb.call_center_break_break_uuid = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			$stmt->bindParam(2, $break_id, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$break_uuid = $row["break_uuid"];
				}
				return $break_uuid;
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
	
	public function getBreakList($params)
	{
		$sql  = " SELECT b.call_center_break_break_uuid as break_uuid, b.break_name, b.break_timeout, b.break_digit";
		$sql .= " FROM v_call_center_tiers t";
		$sql .= " INNER JOIN v_call_center_queues c ON c.queue_name = t.queue_name";
		$sql .= " INNER JOIN v_call_center_break_breaks b ON b.call_center_break_uuid = c.call_center_break_uuid";
		$sql .= " WHERE t.agent_name = ? AND c.domain_uuid = ?";

		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $params["agent"], PDO::PARAM_STR);
			$stmt->bindParam(2, $params["company_id"], PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				$x=0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$response[$x]["break_id"] = $row["break_uuid"];
					$response[$x]["break_name"] = $row["break_name"];
					$response[$x]["break_timeout"] = $row["break_timeout"];
					$response[$x]["break_digit"] = $row["break_digit"];
					$x++;
				}
				
				$log["result"] = $response;
				$log["status"] = "ok";
				$log["status_type"] = "";
				return $log;
			}
			else 
			{
				$log["result"] = "";
				$log["status"] = "error";
				$log["status_type"] = "execute_failed";
				return $log;
			}
		}
		catch(PDOExecption $e)
		{
			$log["result"] = "";
			$log["status"] = "error";
			$log["status_type"] = "execute_failed";
			return $log;		
		}
	}
	
	public function getDomain($ext)
	{
		$sql  = "SELECT CONCAT(cct.agent_name, '@', d.domain_name) AS agent_domain ";
		$sql .= "FROM v_domains d ";
		$sql .= "INNER JOIN v_call_center_tiers cct ON cct.domain_uuid = d.domain_uuid ";
		$sql .= "WHERE cct.agent_name = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $ext, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent_domain = $row["agent_domain"];
				}
				
				return $agent_domain;
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
	
	public function getExistAgent($agent_name)
	{
		$sql  = "SELECT agent_name ";
		$sql .= "FROM v_call_center_agents d ";
		$sql .= "WHERE d.agent_name = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent_name, PDO::PARAM_STR);
			
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$num_rows = $stmt->rowCount();
			
			if($num_rows == 0)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		catch(PDOExecption $e) 
		{
			return false;			
		}
		
		return false;
	}
	
	public function getExistAgentTiers($agent_name)
	{
		$sql  = "SELECT t.agent_name ";
		$sql .= "FROM v_call_center_tiers t ";
		$sql .= "WHERE t.agent_name = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent_name, PDO::PARAM_STR);
			
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$num_rows = $stmt->rowCount();
			
			if($num_rows == 0)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		catch(PDOExecption $e) 
		{
			return false;
		}
		
		return false;
	}
	
	public function getRecording($uuid)
	{
		$sql  = "SELECT uuid_record_name as record_dir ";
		$sql .= "FROM v_xml_cdr_call_center_queue ";
		$sql .= "WHERE uuid = ? ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$record_dir = $row["record_dir"];
				}
				
				return $record_dir;
			}
			else 
			{
				return null;
			}
		}
		catch(PDOExecption $e) 
		{
			return null;			
		}
	}
}

?>