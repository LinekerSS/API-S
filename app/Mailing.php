<?php

class Mailing extends Core
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getMailing($params)
	{
		$domain = $params["domain"];
		$mailing = $params["mailing"];
		$uuid = $params["uuid"];
		
		$sql  = "SELECT * ";
		$sql .= "FROM `dialer_$domain`.`v_mailing_$mailing` ";
		$sql .= "WHERE row_uuid = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $uuid, PDO::PARAM_STR);
		
		try
		{
			if ($stmt->execute())
			{
				$all_mailing = array();
				
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$mailing = array();
					$mailing["uuid"] = $row["row_uuid"];
					$mailing["status"] = $row["status"];
					$mailing["state"] = $row["state"];
					$mailing["scheduled"] = $row["scheduled"];
					$mailing["order"] = $row["order"];
					$mailing["total_tries"] = $row["total_tries"];
					$mailing["last_dialed"] = $row["last_dialed"];
					$mailing["number2call"] = $row["number2call"];
					$mailing["number2call_field"] = $row["number2call_field"];
					
					$mailing["num_1"] = $row["num_1"];
					$mailing["num_1_tries"] = $row["num_1_tries"];
					$mailing["num_1_state"] = $row["num_1_state"];

					$mailing["num_2"] = $row["num_2"];
					$mailing["num_2_tries"] = $row["num_2_tries"];
					$mailing["num_2_state"] = $row["num_2_state"];
					
					$mailing["num_3"] = $row["num_3"];
					$mailing["num_3_tries"] = $row["num_3_tries"];
					$mailing["num_3_state"] = $row["num_3_state"];
					
					$mailing["num_4"] = $row["num_4"];
					$mailing["num_4_tries"] = $row["num_4_tries"];
					$mailing["num_4_state"] = $row["num_4_state"];
					
					$mailing["num_5"] = $row["num_5"];
					$mailing["num_5_tries"] = $row["num_5_tries"];
					$mailing["num_5_state"] = $row["num_5_state"];
					
					$mailing["num_6"] = $row["num_6"];
					$mailing["num_6_tries"] = $row["num_6_tries"];
					$mailing["num_6_state"] = $row["num_6_state"];
					
					$mailing["num_7"] = $row["num_7"];
					$mailing["num_7_tries"] = $row["num_7_tries"];
					$mailing["num_7_state"] = $row["num_7_state"];
					
					$mailing["num_8"] = $row["num_8"];
					$mailing["num_8_tries"] = $row["num_8_tries"];
					$mailing["num_8_state"] = $row["num_8_state"];
					
					$mailing["num_9"] = $row["num_9"];
					$mailing["num_9_tries"] = $row["num_9_tries"];
					$mailing["num_9_state"] = $row["num_9_state"];
					
					$mailing["num_10"] = $row["num_10"];
					$mailing["num_10_tries"] = $row["num_10_tries"];
					$mailing["num_10_state"] = $row["num_10_state"];
					
					$mailing["num_11"] = $row["num_11"];
					$mailing["num_11_tries"] = $row["num_11_tries"];
					$mailing["num_11_state"] = $row["num_11_state"];
					
					$mailing["num_12"] = $row["num_12"];
					$mailing["num_12_tries"] = $row["num_12_tries"];
					$mailing["num_12_state"] = $row["num_12_state"];
					
					$mailing["num_13"] = $row["num_13"];
					$mailing["num_13_tries"] = $row["num_13_tries"];
					$mailing["num_13_state"] = $row["num_13_state"];
					
					$mailing["num_14"] = $row["num_14"];
					$mailing["num_14_tries"] = $row["num_14_tries"];
					$mailing["num_14_state"] = $row["num_14_state"];
					
					$mailing["num_15"] = $row["num_15"];
					$mailing["num_15_tries"] = $row["num_15_tries"];
					$mailing["num_15_state"] = $row["num_15_state"];
					
					$mailing["num_16"] = $row["num_16"];
					$mailing["num_16_tries"] = $row["num_16_tries"];
					$mailing["num_16_state"] = $row["num_16_state"];
					
					$mailing["num_17"] = $row["num_17"];
					$mailing["num_17_tries"] = $row["num_17_tries"];
					$mailing["num_17_state"] = $row["num_17_state"];
					
					$mailing["num_18"] = $row["num_18"];
					$mailing["num_18_tries"] = $row["num_18_tries"];
					$mailing["num_18_state"] = $row["num_18_state"];
					
					$mailing["num_19"] = $row["num_19"];
					$mailing["num_19_tries"] = $row["num_19_tries"];
					$mailing["num_19_state"] = $row["num_19_state"];
					
					$mailing["info"] = $row["info"];
					$mailing["mailing_key"] = $row["mailing_key"];
					
					array_push($all_mailing, $mailing);
				}
				
				$stmt->closeCursor();
				
				return $all_mailing;
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
	
	public function getMailingAll($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		
		$sql  = "SELECT * ";
		$sql .= "FROM `dialer_$domain_uuid`.`v_mailing_$mailing_uuid` ";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			if ($stmt->execute())
			{
				$all_mailing = array();
				
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$mailing = array();
					$mailing["status"] = $row["status"];
					$mailing["total_tries"] = $row["total_tries"];					
					$mailing["num_1"] = $row["num_1"];
					$mailing["num_2"] = $row["num_2"];					
					$mailing["num_3"] = $row["num_3"];					
					$mailing["num_4"] = $row["num_4"];					
					$mailing["num_5"] = $row["num_5"];					
					$mailing["num_6"] = $row["num_6"];					
					$mailing["num_7"] = $row["num_7"];					
					$mailing["num_8"] = $row["num_8"];					
					$mailing["num_9"] = $row["num_9"];					
					$mailing["num_10"] = $row["num_10"];					
					$mailing["num_11"] = $row["num_11"];					
					$mailing["num_12"] = $row["num_12"];					
					$mailing["num_13"] = $row["num_13"];					
					$mailing["num_14"] = $row["num_14"];					
					$mailing["num_15"] = $row["num_15"];					
					$mailing["num_16"] = $row["num_16"];					
					$mailing["num_17"] = $row["num_17"];					
					$mailing["num_18"] = $row["num_18"];					
					$mailing["num_19"] = $row["num_19"];					
					$mailing["info"] = $row["info"];
					$mailing["mailing_key"] = $row["mailing_key"];					
					array_push($all_mailing, $mailing);
				}
				
				$stmt->closeCursor();
				
				return $all_mailing;
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
	
	public function insertMailing($params)
	{
		try
		{
			$valid = array('nome', 'cpf_cnpj', 'endereco', 'numero', 'complemento'
			, 'cep', 'bairro', 'cidade', 'estado', 'prioridade', 'phone_res_01'
			, 'phone_res_02', 'phone_res_03', 'phone_res_04', 'phone_res_05'
			, 'phone_com_01', 'phone_com_02', 'phone_com_03', 'phone_com_04', 'phone_com_05'
			, 'phone_mob_01', 'phone_mob_02', 'phone_mob_03', 'phone_mob_04', 'phone_mob_05'
			, 'phone_esp_01', 'phone_esp_02', 'phone_esp_03', 'phone_esp_04', 'phone_esp_05');
			
			$domain = $params["domain"];
			$mailing = $params["mailing"];
			$rows = $params["rows"];
			
			$core = new Core();
			$db_mailing = new Mailing();
			
			$order = $db_mailing->getOrderMailing($domain, $mailing);
			$insered = 0;

			foreach($rows as $key => $row)
			{
				if(!verifyRequiredParams($valid, $row))
				{
					return "400@00";
				}
		
				$row_uuid = $core->uuid();
				$order++; 
				$status = 'virgin';
				$state = '';
				$scheduled = date('Y-m-d H:i:s');
				$total_tries = 0; 
				$name = '';
				$cpf_cnpj = '';
				$address = '';
				$number = '';
				$complement = '';
				$cep = '';
				$district = '';
				$city = '';
				$uf = '';
				$priority = '';
				$phone_res_01 = '';
				$phone_res_02 = '';
				$phone_res_03 = '';
				$phone_res_04 = '';
				$phone_res_05 = '';
				$phone_com_01 = '';
				$phone_com_02 = '';
				$phone_com_03 = '';
				$phone_com_04 = '';
				$phone_com_05 = '';
				$phone_mob_01 = '';
				$phone_mob_02 = '';
				$phone_mob_03 = '';
				$phone_mob_04 = '';
				$phone_mob_05 = '';
				$phone_esp_01 = '';
				$phone_esp_02 = '';
				$phone_esp_03 = '';
				$phone_esp_04 = '';
				$phone_esp_05 = '';
				
				$pes_codigo = '';
				$loj_codigo = '';
				$usu_codigo = '';
				$emp_codigo = '';
				
				$mailing_row = array();
				
				if(strlen($row["nome"]) > 0)
				{
					$name = $row["nome"];
					$mailing_row["nome"] = $row["nome"];
				}
				
				if(strlen($row["cpf_cnpj"]) > 0)
				{
					$cpf_cnpj = $row["cpf_cnpj"];
					$mailing_row["cpf_cnpj"] = $core->formatNumber($row["cpf_cnpj"]);
				}

				if(strlen($row["endereco"]) > 0)
				{
					$address = $row["endereco"];
					$mailing_row["endereco"] = $row["endereco"];
				}
				
				if(strlen($row["numero"]) > 0)
				{
					$number = $row["numero"];
					$mailing_row["numero"] = $row["numero"];
				}
				
				if(strlen($row["complemento"]) > 0)
				{
					$complement = $row["complemento"];
					$mailing_row["complemento"] = $row["complemento"];
				}
				
				if(strlen($row["cep"]) > 0)
				{
					$cep = $row["cep"];
					$mailing_row["cep"] = $row["cep"];
				}
				
				if(strlen($row["bairro"]) > 0)
				{
					$district = $row["bairro"];
					$mailing_row["bairro"] = $row["bairro"];
				}
				
				if(strlen($row["cidade"]) > 0)
				{
					$city = $row["cidade"];
					$mailing_row["cidade"] = $row["cidade"];
				}
				
				if(strlen($row["estado"]) > 0)
				{
					$uf = $row["estado"];
					$mailing_row["estado"] = $row["estado"];
				}
				
				if(strlen($row["prioridade"]) > 0)
				{
					$priority = $row["prioridade"];
					$mailing_row["prioridade"] = $row["prioridade"];
				}
				
				if(strlen($row["phone_res_01"]) > 0)
				{
					$phone_res_01 = $core->formatNumber($row["phone_res_01"]);
					$mailing_row["phone_res_01"] = $row["phone_res_01"];
				}
				
				if(strlen($row["phone_res_02"]) > 0)
				{
					$phone_res_02 = $core->formatNumber($row["phone_res_02"]);
					$mailing_row["phone_res_02"] = $row["phone_res_02"];
				}
				
				if(strlen($row["phone_res_03"]) > 0)
				{
					$phone_res_03 = $core->formatNumber($row["phone_res_03"]);
					$mailing_row["phone_res_03"] = $row["phone_res_03"];
				}
				
				if(strlen($row["phone_res_04"]) > 0)
				{
					$phone_res_04 = $core->formatNumber($row["phone_res_04"]);
					$mailing_row["phone_res_04"] = $row["phone_res_04"];
				}
				
				if(strlen($row["phone_res_05"]) > 0)
				{
					$phone_res_05 = $core->formatNumber($row["phone_res_05"]);
					$mailing_row["phone_res_05"] = $row["phone_res_05"];
				}
				
				if(strlen($row["phone_com_01"]) > 0)
				{
					$phone_com_01 = $core->formatNumber($row["phone_com_01"]);
					$mailing_row["phone_com_01"] = $row["phone_com_01"];
				}
				
				if(strlen($row["phone_com_02"]) > 0)
				{
					$phone_com_02 = $core->formatNumber($row["phone_com_02"]);
					$mailing_row["phone_com_02"] = $row["phone_com_02"];
				}
				
				if(strlen($row["phone_com_03"]) > 0)
				{
					$phone_com_03 = $core->formatNumber($row["phone_com_03"]);
					$mailing_row["phone_com_03"] = $row["phone_com_03"];
				}
				
				if(strlen($row["phone_com_04"]) > 0)
				{
					$phone_com_04 = $core->formatNumber($row["phone_com_04"]);
					$mailing_row["phone_com_04"] = $row["phone_com_04"];
				}
				
				if(strlen($row["phone_com_05"]) > 0)
				{
					$phone_com_05 = $core->formatNumber($row["phone_com_05"]);
					$mailing_row["phone_com_05"] = $row["phone_com_05"];
				}
				
				if(strlen($row["phone_mob_01"]) > 0)
				{
					$phone_mob_01 = $core->formatNumber($row["phone_mob_01"]);
					$mailing_row["phone_mob_01"] = $row["phone_mob_01"];
				}
				
				if(strlen($row["phone_mob_02"]) > 0)
				{
					$phone_mob_02 = $core->formatNumber($row["phone_mob_02"]);
					$mailing_row["phone_mob_02"] = $row["phone_mob_02"];
				}
				
				if(strlen($row["phone_mob_03"]) > 0)
				{
					$phone_mob_03 = $core->formatNumber($row["phone_mob_03"]);
					$mailing_row["phone_mob_03"] = $row["phone_mob_03"];
				}
				
				if(strlen($row["phone_mob_04"]) > 0)
				{
					$phone_mob_04 = $core->formatNumber($row["phone_mob_04"]);
					$mailing_row["phone_mob_04"] = $row["phone_mob_04"];
				}
				
				if(strlen($row["phone_mob_05"]) > 0)
				{
					$phone_mob_05 = $core->formatNumber($row["phone_mob_05"]);
					$mailing_row["phone_mob_05"] = $row["phone_mob_05"];
				}
				
				if(strlen($row["phone_esp_01"]) > 0)
				{
					$phone_esp_01 = $core->formatNumber($row["phone_esp_01"]);
					$mailing_row["phone_esp_01"] = $row["phone_esp_01"];
				}
				
				if(strlen($row["phone_esp_02"]) > 0)
				{
					$phone_esp_02 = $core->formatNumber($row["phone_esp_02"]);
					$mailing_row["phone_esp_02"] = $row["phone_esp_02"];
				}
				
				if(strlen($row["phone_esp_03"]) > 0)
				{
					$phone_esp_03 = $core->formatNumber($row["phone_esp_03"]);
					$mailing_row["phone_esp_03"] = $row["phone_esp_03"];
				}
				
				if(strlen($row["phone_esp_04"]) > 0)
				{
					$phone_esp_04 = $core->formatNumber($row["phone_esp_04"]);
					$mailing_row["phone_esp_04"] = $row["phone_esp_04"];
				}
				
				if(strlen($row["phone_esp_05"]) > 0)
				{
					$phone_esp_05 = $core->formatNumber($row["phone_esp_05"]);
					$mailing_row["phone_esp_05"] = $row["phone_esp_05"];
				}
				
				if(isset($row["pes_codigo"]))
				{
					if(strlen($row["pes_codigo"]) > 0)
					{
						$pes_codigo = $core->formatNumber($row["pes_codigo"]);
						$mailing_row["pes_codigo"] = $row["pes_codigo"];
					}
				}
				else
				{
					$pes_codigo = "";
				}
				
				if(isset($row["loj_codigo"]))
				{
					if(strlen($row["loj_codigo"]) > 0)
					{
						$loj_codigo = $core->formatNumber($row["loj_codigo"]);
						$mailing_row["loj_codigo"] = $row["loj_codigo"];
					}
				}
				else
				{
					$loj_codigo = "";
				}
				
				if(isset($row["usu_codigo"]))
				{
					if(strlen($row["usu_codigo"]) > 0)
					{
						$usu_codigo = $core->formatNumber($row["usu_codigo"]);
						$mailing_row["usu_codigo"] = $row["usu_codigo"];
					}
				}
				else
				{
					$usu_codigo = "";
				}
				
				if(isset($row["emp_codigo"]))
				{
					if(strlen($row["emp_codigo"]) > 0)
					{
						$emp_codigo = $core->formatNumber($row["emp_codigo"]);
						$mailing_row["emp_codigo"] = $row["emp_codigo"];
					}
				}
				else
				{
					$emp_codigo = "";
				}
				
				if(strlen($row["cpf_cnpj"]) > 0)
				{
					$mailing_key = $core->formatNumber($row["cpf_cnpj"]);
				}
				else
				{
					$mailing_key = $core->uuid();
				}			
				
				$json_info =
							array
							(
								"info"=>
								array
								(
									"name"			=> 	$name, 
									"cpf_cnpj"		=>	$cpf_cnpj,
									"address"		=>	$address,
									"number"		=>	$number,
									"complement"	=>	$complement,
									"cep"			=>	$cep,
									"district"		=>	$district,
									"city"			=>	$city,
									"uf"			=>	$uf,
									"priority"		=>	$priority,
									"pes_codigo"	=>	$pes_codigo,
									"loj_codigo"	=>	$loj_codigo,
									"usu_codigo"	=>	$usu_codigo,
									"emp_codigo"	=>	$emp_codigo,
								),
							);

				$info = json_encode($json_info);

				$mailing_row = json_encode($mailing_row);
				
				$sql  = " INSERT INTO `dialer_$domain`.`v_mailing_$mailing`";
				$sql .= " (`row_uuid`, `scheduled`, `order`, `last_dialed`, `number2call`,"; 
				$sql .= " `num_1`, `num_2`, `num_3`, ";
				$sql .= " `num_4`, `num_5`, `num_6`, ";
				$sql .= " `num_7`, `num_8`, `num_9`, ";
				$sql .= " `num_10`, `num_11`, `num_12`, ";
				$sql .= " `num_13`, `num_14`, `num_15`, ";
				$sql .= " `num_16`, `num_17`, `num_18`, "; 
				$sql .= " `num_19`, `num_20`, `info`, `mailing_row`,`mailing_key`)";
				$sql .= " VALUES ";
				$sql .= " ('$row_uuid', '$scheduled', $order, '', '',";
				$sql .= strlen($phone_res_01 > 0) ? " '$phone_res_01'," : " NULL,";		
				$sql .= strlen($phone_res_02 > 0) ? " '$phone_res_02'," : " NULL,";
				$sql .= strlen($phone_res_03 > 0) ? " '$phone_res_03'," : " NULL,";
				$sql .= strlen($phone_res_04 > 0) ? " '$phone_res_04'," : " NULL,";
				$sql .= strlen($phone_res_05 > 0) ? " '$phone_res_05'," : " NULL,";
				$sql .= strlen($phone_com_01 > 0) ? " '$phone_com_01'," : " NULL,";
				$sql .= strlen($phone_com_02 > 0) ? " '$phone_com_02'," : " NULL,";
				$sql .= strlen($phone_com_03 > 0) ? " '$phone_com_03'," : " NULL,";
				$sql .= strlen($phone_com_04 > 0) ? " '$phone_com_04'," : " NULL,";
				$sql .= strlen($phone_com_05 > 0) ? " '$phone_com_05'," : " NULL,";
				$sql .= strlen($phone_mob_01 > 0) ? " '$phone_mob_01'," : " NULL,";
				$sql .= strlen($phone_mob_02 > 0) ? " '$phone_mob_02'," : " NULL,";
				$sql .= strlen($phone_mob_03 > 0) ? " '$phone_mob_03'," : " NULL,";
				$sql .= strlen($phone_mob_04 > 0) ? " '$phone_mob_04'," : " NULL,";
				$sql .= strlen($phone_mob_05 > 0) ? " '$phone_mob_05'," : " NULL,"; 
				$sql .= strlen($phone_esp_01 > 0) ? " '$phone_esp_01'," : " NULL,"; 
				$sql .= strlen($phone_esp_02 > 0) ? " '$phone_esp_02'," : " NULL,"; 
				$sql .= strlen($phone_esp_03 > 0) ? " '$phone_esp_03'," : " NULL,";
				$sql .= strlen($phone_esp_04 > 0) ? " '$phone_esp_04'," : " NULL,"; 
				$sql .= strlen($phone_esp_05 > 0) ? " '$phone_esp_05'," : " NULL,";
				$sql .= " '$info', '$mailing_row', '$mailing_key')";
				error_log($sql);
				$stmt = $this->conn->prepare($sql);

				$db_mailing->updateCountMailing($domain, $mailing);
				
				if ($stmt->execute())
				{
					$insered++;
				}
				else
				{
					if($insered > 0)
					{
						$insered--;
					}	
				}
			}
			
			if(count($params["rows"]) == $insered)
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
			return '409@00';			
		}
	}

	public function disableMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$mailing_key = $params["mailing_key"];
		
		$sql  = "UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid` ";
		$sql .= "SET status = 'ended'";
		$sql .= "WHERE mailing_key = ?";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $mailing_key, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function disableAllMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		
		$sql  = "UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid` ";
		$sql .= "SET status = 'ended'";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			if ($stmt->execute())
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
	
	public function updateMailing($params)
	{
		try
		{
			$core = new Core();
			$mailing_row = array();
			
			$domain = $params["domain"];
			$mailing = $params["mailing"];
			$uuid = $params["uuid"];
			
			$name = $params["nome"];
			$mailing_row["nome"] = $params["nome"];
			
			$cpf_cnpj = $params["cpf_cnpj"];
			$mailing_row["cpf_cnpj"] = $core->formatNumber($params["cpf_cnpj"]);
			
			$address = $params["endereco"];
			$mailing_row["endereco"] = $params["endereco"];

			$number = $params["numero"];
			$mailing_row["numero"] = $params["numero"];
		
			$complement = $params["complemento"];
			$mailing_row["complemento"] = $params["complemento"];
		
			$cep = $params["cep"];
			$mailing_row["cep"] = $params["cep"];
		
			$district = $params["bairro"];
			$mailing_row["bairro"] = $params["bairro"];
		
			$city = $params["cidade"];
			$mailing_row["cidade"] = $params["cidade"];
		
			$uf = $params["estado"];
			$mailing_row["estado"] = $params["estado"];
		
			$priority = $params["prioridade"];
			$mailing_row["prioridade"] = $params["prioridade"];

			$phone_res_01 = $core->formatNumber($params["phone_res_01"]);
			$phone_res_01_desc = ((strlen($phone_res_01 > 0)) ? 'phone_res_01' : NULL);
			$mailing_row["phone_res_01"] = $params["phone_res_01"];
		
			$phone_res_02 = $core->formatNumber($params["phone_res_02"]);
			$phone_res_02_desc = ((strlen($phone_res_02 > 0)) ? 'phone_res_02' : NULL);
			$mailing_row["phone_res_02"] = $params["phone_res_02"];
		
			$phone_res_03 = $core->formatNumber($params["phone_res_03"]);
			$phone_res_03_desc = ((strlen($phone_res_03 > 0)) ? 'phone_res_03' : NULL);
			$mailing_row["phone_res_03"] = $params["phone_res_03"];
		
			$phone_res_04 = $core->formatNumber($params["phone_res_04"]);
			$phone_res_04_desc = ((strlen($phone_res_04 > 0)) ? 'phone_res_04' : NULL);
			$mailing_row["phone_res_04"] = $params["phone_res_04"];
		
			$phone_res_05 = $core->formatNumber($params["phone_res_05"]);
			$phone_res_05_desc = ((strlen($phone_res_05 > 0)) ? 'phone_res_05' : NULL);
			$mailing_row["phone_res_05"] = $params["phone_res_05"];
		
			$phone_com_01 = $core->formatNumber($params["phone_com_01"]);
			$phone_com_01_desc = ((strlen($phone_com_01 > 0)) ? 'phone_com_01' : NULL);
			$mailing_row["phone_com_01"] = $params["phone_com_01"];
		
			$phone_com_02 = $core->formatNumber($params["phone_com_02"]);
			$phone_com_02_desc = ((strlen($phone_com_02 > 0)) ? 'phone_com_02' : NULL);
			$mailing_row["phone_com_02"] = $params["phone_com_02"];
		
			$phone_com_03 = $core->formatNumber($params["phone_com_03"]);
			$phone_com_03_desc = ((strlen($phone_com_03 > 0)) ? 'phone_com_03' : NULL);
			$mailing_row["phone_com_03"] = $params["phone_com_03"];
		
			$phone_com_04 = $core->formatNumber($params["phone_com_04"]);
			$phone_com_04_desc = ((strlen($phone_com_04 > 0)) ? 'phone_com_04' : NULL);
			$mailing_row["phone_com_04"] = $params["phone_com_04"];
		
			$phone_com_05 = $core->formatNumber($params["phone_com_05"]);
			$phone_com_05_desc = ((strlen($phone_com_05 > 0)) ? 'phone_com_05' : NULL);
			$mailing_row["phone_com_05"] = $params["phone_com_05"];
		
			$phone_mob_01 = $core->formatNumber($params["phone_mob_01"]);
			$phone_mob_01_desc = ((strlen($phone_mob_01 > 0)) ? 'phone_mob_01' : NULL);
			$mailing_row["phone_mob_01"] = $params["phone_mob_01"];
		
			$phone_mob_02 = $core->formatNumber($params["phone_mob_02"]);
			$phone_mob_02_desc = ((strlen($phone_mob_02 > 0)) ? 'phone_mob_02' : NULL);
			$mailing_row["phone_mob_02"] = $params["phone_mob_02"];
		
			$phone_mob_03 = $core->formatNumber($params["phone_mob_03"]);
			$phone_mob_03_desc = ((strlen($phone_mob_03 > 0)) ? 'phone_mob_03' : NULL);
			$mailing_row["phone_mob_03"] = $params["phone_mob_03"];
		
			$phone_mob_04 = $core->formatNumber($params["phone_mob_04"]);
			$phone_mob_04_desc = ((strlen($phone_mob_04 > 0)) ? 'phone_mob_04' : NULL);
			$mailing_row["phone_mob_04"] = $params["phone_mob_04"];
		
			$phone_mob_05 = $core->formatNumber($params["phone_mob_05"]);
			$phone_mob_05_desc = ((strlen($phone_mob_05 > 0)) ? 'phone_mob_05' : NULL);
			$mailing_row["phone_mob_05"] = $params["phone_mob_05"];
		
			$phone_esp_01 = $core->formatNumber($params["phone_esp_01"]);
			$phone_esp_01_desc = ((strlen($phone_esp_01 > 0)) ? 'phone_esp_01' : NULL);
			$mailing_row["phone_esp_01"] = $params["phone_esp_01"];
		
			$phone_esp_02 = $core->formatNumber($params["phone_esp_02"]);
			$phone_esp_02_desc = ((strlen($phone_esp_02 > 0)) ? 'phone_esp_02' : NULL);
			$mailing_row["phone_esp_02"] = $params["phone_esp_02"];
		
			$phone_esp_03 = $core->formatNumber($params["phone_esp_03"]);
			$phone_esp_03_desc = ((strlen($phone_esp_03 > 0)) ? 'phone_esp_03' : NULL);
			$mailing_row["phone_esp_03"] = $params["phone_esp_03"];
		
			$phone_esp_04 = $core->formatNumber($params["phone_esp_04"]);
			$phone_esp_04_desc = ((strlen($phone_esp_04 > 0)) ? 'phone_esp_04' : NULL);
			$mailing_row["phone_esp_04"] = $params["phone_esp_04"];
		
			$phone_esp_05 = $core->formatNumber($params["phone_esp_05"]);
			$phone_esp_05_desc = ((strlen($phone_esp_05 > 0)) ? 'phone_esp_05' : NULL);
			$mailing_row["phone_esp_05"] = $params["phone_esp_05"];

			$json_info =
						array
						(
							"info"=>
							array
							(
								"name"			=> 	$name, 
								"cpf_cnpj"		=>	$cpf_cnpj,
								"address"		=>	$address,
								"number"		=>	$number,
								"complement"	=>	$complement,
								"cep"			=>	$cep,
								"district"		=>	$district,
								"city"			=>	$city,
								"uf"			=>	$uf,
								"priority"		=>	$priority,
							),
						);
						
			$info = json_encode($json_info);
			
			$mailing_row = json_encode($mailing_row);

			$sql  = "UPDATE `dialer_$domain`.`v_mailing_$mailing` ";
			$sql .= "SET ";
			$sql .= "num_1 = ?, ";
			$sql .= "desc_1 = ?, ";
			$sql .= "num_2 = ?, ";
			$sql .= "desc_2 = ?, ";
			$sql .= "num_3 = ?, ";
			$sql .= "desc_3 = ?, ";
			$sql .= "num_4 = ?, ";
			$sql .= "desc_4 = ?, ";
			$sql .= "num_5 = ?, ";
			$sql .= "desc_5 = ?, ";
			$sql .= "num_6 = ?, ";
			$sql .= "desc_6 = ?, ";
			$sql .= "num_7 = ?, ";
			$sql .= "desc_7 = ?, ";
			$sql .= "num_8 = ?, ";
			$sql .= "desc_8 = ?, ";
			$sql .= "num_9 = ?, ";
			$sql .= "desc_9 = ?, ";
			$sql .= "num_10 = ?, ";
			$sql .= "desc_10 = ?, ";
			$sql .= "num_11 = ?, ";
			$sql .= "desc_11 = ?, ";
			$sql .= "num_12 = ?, ";
			$sql .= "desc_12 = ?, ";
			$sql .= "num_13 = ?, ";
			$sql .= "desc_13 = ?, ";
			$sql .= "num_14 = ?, ";
			$sql .= "desc_14 = ?, ";
			$sql .= "num_15 = ?, ";
			$sql .= "desc_15 = ?, ";
			$sql .= "num_16 = ?, ";
			$sql .= "desc_16 = ?, ";
			$sql .= "num_17 = ?, ";
			$sql .= "desc_17 = ?, ";
			$sql .= "num_18 = ?, ";
			$sql .= "desc_18 = ?, ";
			$sql .= "num_19 = ?, ";
			$sql .= "desc_19 = ?, ";
			$sql .= "num_20 = ?, ";
			$sql .= "desc_20 = ?, ";		
			$sql .= "info = ?, ";
			$sql .= "mailing_row = ? ";
			$sql .= "WHERE row_uuid = ?";
			$stmt = $this->conn->prepare($sql);
		
			$stmt->bindParam(1, $phone_res_01, PDO::PARAM_STR);
			$stmt->bindParam(2, $phone_res_01_desc, PDO::PARAM_STR);
			$stmt->bindParam(3, $phone_res_02, PDO::PARAM_STR);
			$stmt->bindParam(4, $phone_res_02_desc, PDO::PARAM_STR);
			$stmt->bindParam(5, $phone_res_03, PDO::PARAM_STR);
			$stmt->bindParam(6, $phone_res_03_desc, PDO::PARAM_STR);
			$stmt->bindParam(7, $phone_res_04, PDO::PARAM_STR);
			$stmt->bindParam(8, $phone_res_05_desc, PDO::PARAM_STR);
			$stmt->bindParam(9, $phone_res_05, PDO::PARAM_STR);
			$stmt->bindParam(10, $phone_res_05_desc, PDO::PARAM_STR);
			
			$stmt->bindParam(11, $phone_com_01, PDO::PARAM_STR);
			$stmt->bindParam(12, $phone_com_01_desc, PDO::PARAM_STR);
			$stmt->bindParam(13, $phone_com_02, PDO::PARAM_STR);
			$stmt->bindParam(14, $phone_com_02_desc, PDO::PARAM_STR);
			$stmt->bindParam(15, $phone_com_03, PDO::PARAM_STR);
			$stmt->bindParam(16, $phone_com_03_desc, PDO::PARAM_STR);
			$stmt->bindParam(17, $phone_com_04, PDO::PARAM_STR);
			$stmt->bindParam(18, $phone_com_05_desc, PDO::PARAM_STR);
			$stmt->bindParam(19, $phone_com_05, PDO::PARAM_STR);
			$stmt->bindParam(20, $phone_com_05_desc, PDO::PARAM_STR);
			
			$stmt->bindParam(21, $phone_mob_01, PDO::PARAM_STR);
			$stmt->bindParam(22, $phone_mob_01_desc, PDO::PARAM_STR);
			$stmt->bindParam(23, $phone_mob_02, PDO::PARAM_STR);
			$stmt->bindParam(24, $phone_mob_02_desc, PDO::PARAM_STR);
			$stmt->bindParam(25, $phone_mob_03, PDO::PARAM_STR);
			$stmt->bindParam(26, $phone_mob_03_desc, PDO::PARAM_STR);
			$stmt->bindParam(27, $phone_mob_04, PDO::PARAM_STR);
			$stmt->bindParam(28, $phone_mob_05_desc, PDO::PARAM_STR);
			$stmt->bindParam(29, $phone_mob_05, PDO::PARAM_STR);
			$stmt->bindParam(30, $phone_mob_05_desc, PDO::PARAM_STR);
			
			$stmt->bindParam(31, $phone_esp_01, PDO::PARAM_STR);
			$stmt->bindParam(32, $phone_esp_01_desc, PDO::PARAM_STR);
			$stmt->bindParam(33, $phone_esp_02, PDO::PARAM_STR);
			$stmt->bindParam(34, $phone_esp_02_desc, PDO::PARAM_STR);
			$stmt->bindParam(35, $phone_esp_03, PDO::PARAM_STR);
			$stmt->bindParam(36, $phone_esp_03_desc, PDO::PARAM_STR);
			$stmt->bindParam(37, $phone_esp_04, PDO::PARAM_STR);
			$stmt->bindParam(38, $phone_esp_05_desc, PDO::PARAM_STR);
			$stmt->bindParam(39, $phone_esp_05, PDO::PARAM_STR);
			$stmt->bindParam(40, $phone_esp_05_desc, PDO::PARAM_STR);
			
			$stmt->bindParam(41, $info, PDO::PARAM_STR);
			$stmt->bindParam(42, $mailing_row, PDO::PARAM_STR);
			$stmt->bindParam(43, $uuid, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function createTableMailing($params)
	{
		$core = new Core();
		$mailing_uuid = $core->uuid();
		$domain_uuid = $params["domain"];
		
		$sql  = "  CREATE TABLE IF NOT EXISTS `dialer_$domain_uuid`.`v_mailing_$mailing_uuid` (";
		$sql .= " `row_uuid` char(36) NOT NULL,";
		$sql .= " `status` char(20) NOT NULL DEFAULT 'virgin' COMMENT 'dialer, mini_dialer, virgin, scheduled, ended',";
		$sql .= " `state` char(20) COMMENT 'motivos, por exemplo max_tries',";
		$sql .= " `scheduled` datetime NOT NULL COMMENT 'colocar data e hora atual',";
		$sql .= " `order` int(11) NOT NULL DEFAULT '0',";
		$sql .= " `total_tries` int(11) NOT NULL DEFAULT '0',";
		$sql .= " `last_dialed` tinytext,";
		$sql .= " `number2call` tinytext,";
		$sql .= " `number2call_field` tinytext,";
		$sql .= " `num_1` tinytext,";
		$sql .= " `num_1_tries` tinytext,";
		$sql .= " `num_1_state` tinytext,";
		$sql .= " `desc_1` varchar(50) DEFAULT NULL,";
		$sql .= " `num_2` tinytext,";
		$sql .= " `num_2_tries` tinytext,";
		$sql .= " `num_2_state` tinytext,";
		$sql .= " `desc_2` varchar(50) DEFAULT NULL,";
		$sql .= " `num_3` tinytext,";
		$sql .= " `num_3_tries` tinytext,";
		$sql .= " `num_3_state` tinytext,";
		$sql .= " `desc_3` varchar(50) DEFAULT NULL,";
		$sql .= " `num_4` tinytext,";
		$sql .= " `num_4_tries` tinytext,";
		$sql .= " `num_4_state` tinytext,";
		$sql .= " `desc_4` varchar(50) DEFAULT NULL,";
		$sql .= " `num_5` tinytext,";
		$sql .= " `num_5_tries` tinytext,";
		$sql .= " `num_5_state` tinytext,";
		$sql .= " `desc_5` varchar(50) DEFAULT NULL,";
		$sql .= " `num_6` tinytext,";
		$sql .= " `num_6_tries` tinytext,";
		$sql .= " `num_6_state` tinytext,";	
		$sql .= " `desc_6` varchar(50) DEFAULT NULL,";
		$sql .= " `num_7` tinytext,";
		$sql .= " `num_7_tries` tinytext,";
		$sql .= " `num_7_state` tinytext,";
		$sql .= " `desc_7` varchar(50) DEFAULT NULL,";
		$sql .= " `num_8` tinytext,";
		$sql .= " `num_8_tries` tinytext,";
		$sql .= " `num_8_state` tinytext,";
		$sql .= " `desc_8` varchar(50) DEFAULT NULL,";
		$sql .= " `num_9` tinytext,";
		$sql .= " `num_9_tries` tinytext,";
		$sql .= " `num_9_state` tinytext,";
		$sql .= " `desc_9` varchar(50) DEFAULT NULL,";
		$sql .= " `num_10` tinytext,";
		$sql .= " `num_10_tries` tinytext,";
		$sql .= " `num_10_state` tinytext,";
		$sql .= " `desc_10` varchar(50) DEFAULT NULL,";
		$sql .= " `num_11` tinytext,";
		$sql .= " `num_11_tries` tinytext,";
		$sql .= " `num_11_state` tinytext,";
		$sql .= " `desc_11` varchar(50) DEFAULT NULL,";
		$sql .= " `num_12` tinytext,";
		$sql .= " `num_12_tries` tinytext,";
		$sql .= " `num_12_state` tinytext,";
		$sql .= " `desc_12` varchar(50) DEFAULT NULL,";
		$sql .= " `num_13` tinytext,";
		$sql .= " `num_13_tries` tinytext,";
		$sql .= " `num_13_state` tinytext,";
		$sql .= " `desc_13` varchar(50) DEFAULT NULL,";
		$sql .= " `num_14` tinytext,";
		$sql .= " `num_14_tries` tinytext,";
		$sql .= " `num_14_state` tinytext,";
		$sql .= " `desc_14` varchar(50) DEFAULT NULL,";
		$sql .= " `num_15` tinytext,";
		$sql .= " `num_15_tries` tinytext,";
		$sql .= " `num_15_state` tinytext,";
		$sql .= " `desc_15` varchar(50) DEFAULT NULL,";
		$sql .= " `num_16` tinytext,";
		$sql .= " `num_16_tries` tinytext,";
		$sql .= " `num_16_state` tinytext,";
		$sql .= " `desc_16` varchar(50) DEFAULT NULL,";
		$sql .= " `num_17` tinytext,";
		$sql .= " `num_17_tries` tinytext,";
		$sql .= " `num_17_state` tinytext,";
		$sql .= " `desc_17` varchar(50) DEFAULT NULL,";
		$sql .= " `num_18` tinytext,";
		$sql .= " `num_18_tries` tinytext,";
		$sql .= " `num_18_state` tinytext,";
		$sql .= " `desc_18` varchar(50) DEFAULT NULL,";
		$sql .= " `num_19` tinytext,";
		$sql .= " `num_19_tries` tinytext,";
		$sql .= " `num_19_state` tinytext,";
		$sql .= " `desc_19` varchar(50) DEFAULT NULL,";
		$sql .= " `num_20` tinytext,";
		$sql .= " `num_20_tries` tinytext,";
		$sql .= " `num_20_state` tinytext,";
		$sql .= " `desc_20` varchar(50) DEFAULT NULL,";
		$sql .= " `info` text COMMENT 'json dos dados pessoais',";
		$sql .= " `issue_sound` text COMMENT 'audio do discador',";
		$sql .= " `mailing_row` text COMMENT 'json da linha como está no arquivo de importacao',";
		$sql .= " `mailing_key` tinytext COMMENT 'cpf, rg, contrato, ou qualquer outra chave q identifique o registro',";
		$sql .= " `answered_by` char(36) COMMENT 'uuid do registro de mesmo mailing_key que teve atendimento',";
		$sql .= " PRIMARY KEY (`row_uuid`),";
		$sql .= " KEY `mailing_key` (`mailing_key`(10)),";
		$sql .= " KEY `answered_by` (`answered_by`),";
		$sql .= " KEY `status` (`status`),";
		$sql .= " KEY `scheduled` (`scheduled`),";
		$sql .= " KEY `order` (`order`)";
		$sql .= " ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='v_mailing';";	
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
		
		$sql  = " INSERT INTO `dialer_$domain_uuid`.`v_mailings`(mailing_uuid, mailing_name, mailing_count,";
		$sql .= " active, description)";
		$sql .= " VALUES (?, ?, 0, 1, ?)";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $mailing_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $params["name"], PDO::PARAM_STR);
			$stmt->bindParam(3, $params["description"], PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				return $mailing_uuid;
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
	
	public function cleanTableMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$mailing_key = $params["mailing_key"];
		
		$sql  = " UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid`";
		$sql .= " SET";
		$sql .= " status = 'virgin',";
		$sql .= " state = '',";
		$sql .= " total_tries = 0,";
		$sql .= " last_dialed = NULL,";
		$sql .= " number2call = NULL,";
		$sql .= " number2call_field = NULL,";
		$sql .= " num_1_tries = NULL,";
		$sql .= " num_1_state = NULL,";
		$sql .= " num_2_tries = NULL,";
		$sql .= " num_2_state = NULL,";
		$sql .= " num_3_tries = NULL,";
		$sql .= " num_3_state = NULL,";
		$sql .= " num_4_tries = NULL,";
		$sql .= " num_4_state = NULL,";
		$sql .= " num_5_tries = NULL,";
		$sql .= " num_5_state = NULL,";
		$sql .= " num_6_tries = NULL,";
		$sql .= " num_6_state = NULL,";
		$sql .= " num_7_tries = NULL,";
		$sql .= " num_7_state = NULL,";
		$sql .= " num_8_tries = NULL,";
		$sql .= " num_8_state = NULL,";
		$sql .= " num_9_tries = NULL,";
		$sql .= " num_9_state = NULL,";
		$sql .= " num_10_tries = NULL,";
		$sql .= " num_10_state = NULL,";
		$sql .= " num_11_tries = NULL,";
		$sql .= " num_11_state = NULL,";
		$sql .= " num_12_tries = NULL,";
		$sql .= " num_12_state = NULL,";
		$sql .= " num_13_tries = NULL,";
		$sql .= " num_13_state = NULL,";
		$sql .= " num_14_tries = NULL,";
		$sql .= " num_14_state = NULL,";
		$sql .= " num_15_tries = NULL,";
		$sql .= " num_15_state = NULL,";
		$sql .= " num_16_tries = NULL,";
		$sql .= " num_16_state = NULL,";
		$sql .= " num_17_tries = NULL,";
		$sql .= " num_17_state = NULL,";
		$sql .= " num_18_tries = NULL,";
		$sql .= " num_18_state = NULL,";
		$sql .= " num_19_tries = NULL,";
		$sql .= " num_19_state = NULL";
		$sql .= " WHERE mailing_key = ?";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $mailing_key, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function redialMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$mailing_key = $params["mailing_key"];
		
		$sql  = " UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid`";
		$sql .= " SET";
		$sql .= " status = 'virgin',";
		$sql .= " state = '',";
		$sql .= " total_tries = 0,";
		$sql .= " last_dialed = NULL,";
		$sql .= " number2call = NULL,";
		$sql .= " number2call_field = NULL,";
		$sql .= " num_1_tries = NULL,";
		$sql .= " num_1_state = NULL,";
		$sql .= " num_2_tries = NULL,";
		$sql .= " num_2_state = NULL,";
		$sql .= " num_3_tries = NULL,";
		$sql .= " num_3_state = NULL,";
		$sql .= " num_4_tries = NULL,";
		$sql .= " num_4_state = NULL,";
		$sql .= " num_5_tries = NULL,";
		$sql .= " num_5_state = NULL,";
		$sql .= " num_6_tries = NULL,";
		$sql .= " num_6_state = NULL,";
		$sql .= " num_7_tries = NULL,";
		$sql .= " num_7_state = NULL,";
		$sql .= " num_8_tries = NULL,";
		$sql .= " num_8_state = NULL,";
		$sql .= " num_9_tries = NULL,";
		$sql .= " num_9_state = NULL,";
		$sql .= " num_10_tries = NULL,";
		$sql .= " num_10_state = NULL,";
		$sql .= " num_11_tries = NULL,";
		$sql .= " num_11_state = NULL,";
		$sql .= " num_12_tries = NULL,";
		$sql .= " num_12_state = NULL,";
		$sql .= " num_13_tries = NULL,";
		$sql .= " num_13_state = NULL,";
		$sql .= " num_14_tries = NULL,";
		$sql .= " num_14_state = NULL,";
		$sql .= " num_15_tries = NULL,";
		$sql .= " num_15_state = NULL,";
		$sql .= " num_16_tries = NULL,";
		$sql .= " num_16_state = NULL,";
		$sql .= " num_17_tries = NULL,";
		$sql .= " num_17_state = NULL,";
		$sql .= " num_18_tries = NULL,";
		$sql .= " num_18_state = NULL,";
		$sql .= " num_19_tries = NULL,";
		$sql .= " num_19_state = NULL";
		$sql .= " WHERE mailing_key = ?";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $mailing_key, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function deleteMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		
		$sql  = " DELETE FROM `dialer_$domain_uuid`.`v_mailing_$mailing_uuid`";
		
		$stmt = $this->conn->prepare($sql);
		
		try
		{			
			if ($stmt->execute())
			{
				return '200@00';
			}
			else
			{
				return '200@00'; 
			}
		}
		catch(PDOExecption $e) 
		{
			return '200@00';			
		}
	}
	
	public function cleanStateTableMailing($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$hangup_cause = $params["hangup_cause"];
		
		$sql  = " UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid` m";
		$sql .= " INNER JOIN calliopedb.v_xml_cdr_dialer d ON d.row_uuid = m.row_uuid";
		$sql .= " INNER JOIN calliopedb.v_hangup_causes c ON c.sip_enum = d.hangup_cause";
		$sql .= " INNER JOIN calliopedb.v_hangup_causes s ON s.sip_code = BINARY(d.disposition_code)";
		$sql .= " INNER JOIN calliopedb.v_hangup_cause_transcript tr ON c.hangup_cause_transcript_uuid = tr.uuid";
		$sql .= " SET";
		$sql .= " m.status = 'virgin',";
		$sql .= " m.state = '',";
		$sql .= " m.total_tries = 0,";
		$sql .= " m.last_dialed = NULL,";
		$sql .= " m.number2call = NULL,";
		$sql .= " m.number2call_field = NULL,";
		$sql .= " m.num_1_tries = NULL,";
		$sql .= " m.num_1_state = NULL,";
		$sql .= " m.num_2_tries = NULL,";
		$sql .= " m.num_2_state = NULL,";
		$sql .= " m.num_3_tries = NULL,";
		$sql .= " m.num_3_state = NULL,";
		$sql .= " m.num_4_tries = NULL,";
		$sql .= " m.num_4_state = NULL,";
		$sql .= " m.num_5_tries = NULL,";
		$sql .= " m.num_5_state = NULL,";
		$sql .= " m.num_6_tries = NULL,";
		$sql .= " m.num_6_state = NULL,";
		$sql .= " m.num_7_tries = NULL,";
		$sql .= " m.num_7_state = NULL,";
		$sql .= " m.num_8_tries = NULL,";
		$sql .= " m.num_8_state = NULL,";
		$sql .= " m.num_9_tries = NULL,";
		$sql .= " m.num_9_state = NULL,";
		$sql .= " m.num_10_tries = NULL,";
		$sql .= " m.num_10_state = NULL,";
		$sql .= " m.num_11_tries = NULL,";
		$sql .= " m.num_11_state = NULL,";
		$sql .= " m.num_12_tries = NULL,";
		$sql .= " m.num_12_state = NULL,";
		$sql .= " m.num_13_tries = NULL,";
		$sql .= " m.num_13_state = NULL,";
		$sql .= " m.num_14_tries = NULL,";
		$sql .= " m.num_14_state = NULL,";
		$sql .= " m.num_15_tries = NULL,";
		$sql .= " m.num_15_state = NULL,";
		$sql .= " m.num_16_tries = NULL,";
		$sql .= " m.num_16_state = NULL,";
		$sql .= " m.num_17_tries = NULL,";
		$sql .= " m.num_17_state = NULL,";
		$sql .= " m.num_18_tries = NULL,";
		$sql .= " m.num_18_state = NULL,";
		$sql .= " m.num_19_tries = NULL,";
		$sql .= " m.num_19_state = NULL";
		$sql .= " WHERE d.domain_uuid = ?"; 
		$sql .= " AND c.hangup_cause_transcript_uuid = ?";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
			$stmt->bindParam(2, $hangup_cause, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function setScheduling($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$mailing_key = $params["mailing_key"];
		$scheduling_date = $params["scheduling_date"];
		$agent = $params["agent"];
		
		$sql  = " UPDATE `dialer_$domain_uuid`.`v_mailing_$mailing_uuid`";
		$sql .= " SET";
		$sql .= " status = 'scheduling'";
		$sql .= " WHERE mailing_key = ?";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $mailing_key, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function updateCountMailing($domain, $mailing)
	{
		$sql  = "UPDATE `dialer_$domain`.`v_mailings` "; 
		$sql .= "SET mailing_count = ";
		$sql .= "( ";
		$sql .= "	SELECT (COUNT(1) + 1) AS registros ";
		$sql .= "	FROM `dialer_$domain`.`v_mailing_$mailing` ";
		$sql .= ") ";
		$sql .= "WHERE mailing_uuid = ?";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			$stmt->bindParam(1, $mailing, PDO::PARAM_STR);
			
			if ($stmt->execute())
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
	
	public function getMailingCountVirgin($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		
		$sql  = "SELECT COUNT(1) AS contacts ";
		$sql .= "FROM ";
		$sql .= "`dialer_$domain_uuid`.`v_mailing_$mailing_uuid` ";
		$sql .= "WHERE status = 'virgin' ";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$contacts["contacts"] = $row["contacts"];
				}
				
				$rowCount = $stmt->rowCount();
				
				if($rowCount == 0)
				{
					$contacts["contacts"] = 0;
				}
			}
			else 
			{
				$contacts["contacts"] = "404@00";
			}
		}
		catch(PDOExecption $e) 
		{
			$contacts["contacts"] = "404@00";
		}		
		return $contacts;
	}
	
	public function getOrderMailing($domain, $mailing)
	{
		$sql  = "SELECT tb.order ";
		$sql .= "FROM ";
		$sql .= "`dialer_$domain`.`v_mailing_$mailing` tb ";
		$sql .= "ORDER BY tb.order DESC ";
		$sql .= "LIMIT 1 ";
		$stmt = $this->conn->prepare($sql);
		
		try 
		{
			if ($stmt->execute())
			{	
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					return intval($row["order"]);
				}
				
				$rowCount = $stmt->rowCount();
				
				if($rowCount == 0)
				{
					return 0;
				}
				
				$stmt->closeCursor();				
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
		
		return 0;
	}
}

?>