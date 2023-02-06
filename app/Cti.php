,<?php

	
	class Cti extends Core 
	{
		function __construct()
		{
			parent::__construct();
		}
		
		
		public function getCtiDisplay($params)
		{
		
			$fp = $this->event_socket_create();
			
			$agent = $params["agent"];
			$domain_uuid = $params["domain_uuid"];
			$domain_name = $this->getDomainNameUUID($domain_uuid);
			
			$cmd = 'callcenter_config agent list '.$agent."@".$domain_name;
			$event_socket_str  = trim($this->event_socket_request($fp, 'api '.$cmd));
			$result = $this->str_to_named_array($event_socket_str, '|');
			
			if (count($result) == 0)
			{
				$result[1]['refresh']='';
				$result[1]['info']='';
				$result[1]['name'] = '';
				$result[1]['uuid'] = '';
				$result[1]['status'] = '';
				$result[1]['on_break'] = 'false';
				$result[1]['state'] = 'Deslogado';
				$result[1]['wrap_up_time'] = '';
				$result[1]['last_status_change'] = '';
				$result[1]['extension'] = ' ---- ';								
				$result[1]['popup'] = '';
				$result[1]['showtab'] = 'false';
				$result[1]['key'] = '';
				$result[1]['caller'] = '';
				$result[1]['mailing_uuid'] = '';
				$result[1]['queue_uuid'] = '';
				$result[1]['dialer_uuid'] = '';
				$result[1]['row_uuid'] = '';
				$result[1]['number2call_field'] = '';
				$result[1]['code'] = '';
				$result[1]['cc_status'] = ' ----- ';

			}
			else
			{
				$result[1]['refresh']='';
				$result[1]['info']='';
				$result[1]['popup']='';
				$result[1]['mailing_uuid'] = '';
				$result[1]['queue_uuid'] = '';
				$result[1]['dialer_uuid'] = '';
				$result[1]['number2call_field'] = '';
				$result[1]['caller'] = '';
				$result[1]['row_uuid'] = '';
				$result[1]['key'] = '';
				$result[1]['name'] = '';
				$result[1]['code'] = '';
				$result[1]['cc_status'] = ' ----- ';
				$result[1]['showtab'] = '';
				
				$tmp_contact = explode ("/", $result[1]['contact']);				
				$tmp_contact = explode ("@", $tmp_contact[1]);								
				$result[1]['extension']= $tmp_contact[0];
				
				if ($result[1]['state'] == "Waiting")
				{
					if (($result[1]['last_bridge_end'] + $result[1]['wrap_up_time']) > Time()) 
					{
						$c_state = 1;
						$wrap_time = ($result[1]['last_bridge_end'] + $result[1]['wrap_up_time']) - Time();
						$state = "in preparation " . $wrap_time . " seg.";
						$result[1]['showtab'] = 'in preparation';
					}
					else
					{
						$result[1]['showtab'] = 'waiting';
					}
				}
				
				if(strtolower($result[1]['status']) == "on outbound")
				{
					$uuid = $result[1]['uuid'];
				}
				else
				{
					$uuid = $result[1]['uuid'];
				}
				
				if(strlen($uuid) == 0)
				{
					if(strlen($result[1]['status']) > 0)
					{
						$result[1]['cc_status'] = $result[1]['status'];
					}
					
					//die(json_encode($result));
				}
				
				if(strtolower($result[1]['status']) == 'logged out')
				{
					$result[1]['extension'] = '----';
					$result[1]['cc_status'] = 'logged out';
				}
				

				if ((strtolower($result[1]['status']) == 'available' or strtolower($result[1]['status']) == 'available (on demand)') || (strtolower($result[1]['status']) == "on outbound")) 
				{
					$result[1]['cc_status'] = strtolower($result[1]['status']);
					
					if (strtolower($result[1]['state']) == 'receiving')
					{
						$result[1]['showtab'] = 'receiving';
						$result[1]['cc_status'] = 'receiving';
					}
					
					if (strtolower($result[1]['state']) == 'in a queue call' || strtolower($result[1]['status']) == "on outbound")
					{
						$result[1]['cc_status'] = 'in a queue call';	
						$result[1]['showtab'] = 'in a queue call';
							
						$cc_member_session_uuid  = $this->getUuidGetvar($uuid, "cc_member_session_uuid", $fp);
						$result[1]['cc_member_session_uuid'] = $cc_member_session_uuid;
						$result[1]['destination_number'] = $this->getUuidGetvar($uuid, "destination_number", $fp);
						$result[1]['row_uuid'] = $this->getUuidGetvar($cc_member_session_uuid, "row_uuid", $fp);
						$result[1]['dialer_uuid'] = $this->getUuidGetvar($cc_member_session_uuid, "dialer_uuid", $fp);
						$result[1]['mailing_uuid'] = $this->getUuidGetvar($cc_member_session_uuid, "mailing_uuid", $fp);
						$result[1]['number2call_field'] = $this->getUuidGetvar($cc_member_session_uuid, "number2call_field", $fp);
						$result[1]['caller_id_number'] = $this->getUuidGetvar($uuid, "caller_id_number", $fp);
						
						$mailing_info = $this->getUuidGetvar($cc_member_session_uuid, "mailing_info", $fp);
						
						$mrow = $this->getMailingRow($mailing_info);
						
						$result[1]["key"] = $mrow["key"];
						$result[1]["name"] = $mrow["name"];
						$result[1]["code"] = $mrow["code"];
						$result[1]['popup'] = "";
						$result[1]['refresh'] = "true";
						$result[1]['info'] = $this->rowTranslate($mailing_info);
						
					}
				}
				
				/**
					em pausa
				*/
				
				if (strtolower($result[1]['status']) == 'on break') 
				{
					$result[1]['on_break'] = 'true';
					$result[1]['cc_status'] = 'on break';
				}
				else
				{
					$result[1]['on_break'] = 'false';
				}
				
				$result[1]['last_status_change'] = gmdate("G:i:s", (time() - $result[1]['last_status_change']));
			}
			
			$response = array(
				"agent" => $agent,
				"extension" => $result[1]['extension'],
				"name" => $result[1]["name"],
				"uuid" => $result[1]['uuid'],
				"status" => $result[1]['status'],
				"state" => $result[1]['state'],
				"info" => $result[1]['info'],
				"popup" => $result[1]['popup'],
				"mailing_uuid" => $result[1]['mailing_uuid'],
				"dialer_uuid" => $result[1]['dialer_uuid'],
				"number2call_field" => $result[1]['number2call_field'],
				"caller" => $result[1]['caller'],
				"row_uuid" => $result[1]['row_uuid'],
				"key" => $result[1]['key'],
				"code" => $result[1]['code']
			);
			
			return $response;
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
		
		public function str_to_named_array($tmp_str, $tmp_delimiter)
		{
			$tmp_array = explode ("\n", $tmp_str);
			$result = '';
			
			if (trim(strtoupper($tmp_array[0])) != "+OK") 
			{
				$tmp_field_name_array = explode ($tmp_delimiter, $tmp_array[0]);
				$x = 0;
				
				foreach ($tmp_array as $row) 
				{
					if ($x > 0) 
					{
						$tmp_field_value_array = explode ($tmp_delimiter, $tmp_array[$x]);
						$y = 0;
						foreach ($tmp_field_value_array as $tmp_value) 
						{
							$tmp_name = $tmp_field_name_array[$y];
							if (trim(strtoupper($tmp_value)) != "+OK") 
							{
								$result[$x][$tmp_name] = $tmp_value;							
							}
							$y++;
						}
					}
					$x++;
				}
				unset($row);
			}
			return $result;
		}
		
		public function getUuidGetvar($uuid, $var, $fp)
		{
			
			$c = "api uuid_getvar $uuid $var";
			return $this->emplyUuidGetvar(trim($this->event_socket_request($fp, $c)));
		}
		
		public function getMailingInfo($mailing_info)
		{
			$mailing_info = str_replace("|", ",", $mailing_info);
			$aux = json_decode($mailing_info, true);
			return json_encode($aux["info"], true);
		}
		
		public function getMailingRow($mailing_row)
		{
			$_row = array();
			$_row['key'] = "";
			$_row['code'] = "";
			$_row['name'] = "";
			
			if (strlen($mailing_row) > 0)
			{
				$mailing_row = str_replace("|", ",", $mailing_row);
				$mailing_aux = json_decode($mailing_row, true);
				$mailing_obj = $mailing_aux["info"];
				
				if(isset($mailing_obj["cpf_cnpj"])){
					if(strlen($mailing_obj["cpf_cnpj"]) > 0){
						$_row['code'] = $mailing_obj["cpf_cnpj"];
					}
				}
				
				if(isset($mailing_obj["name"])){
					if(strlen($mailing_obj["name"]) > 0){
						$_row['name'] = $mailing_obj["name"];
					}
				}
			}
			
			return $_row;
		}
		
		
		public function emplyUuidGetvar($str)
		{
			if(
			   (strpos($str, '-ERR') !== false) ||
			   (strpos($str, '-USAGE') !== false) ||
			   (strpos($str, '_undef_') !== false)
			  )
			{
				return "";
			}
			else
			{
				return $str;
			}
		}
		
		public function rowTranslate($mailing_info)
		{
			$mailing_info = $this->getMailingInfo($mailing_info);
			
			$rowTranslate = json_decode($mailing_info, true);		

			$l = array();
			$l["email"] = 'E-mail';
		
			$x = 0;
			$f = array();
			foreach($rowTranslate as $k => $v)
			{
				if(isset($l[$k]))
				{
					$l = $l[$k];
				}
				else
				{
					$l = $k;
				}
				
				$f[$x]['l'] = $l;
				$f[$x]['v'] = $v;
				$x++;
			}
			
			return $f;
		}
	
	}
	



?>