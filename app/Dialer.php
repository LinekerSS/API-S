<?php

class Dialer extends Core
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function returnsDialer($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$mailing_uuid = $params["mailing_uuid"];
		$start_stamp_begin = $params["date"];
		$start_stamp_end = $params["date"];
		
		if (strlen($start_stamp_begin) == 0)
		{
			$start_stamp_begin = date('Y-m-d 00:00:00');
			$start_stamp_begin_epoch = strtotime($start_stamp_begin);
		}
		else
		{
			$start_stamp_begin_epoch = strtotime($start_stamp_begin . " 00:00:00");
		}
		
		if (strlen($start_stamp_end) == 0)
		{
			$start_stamp_end = date('Y-m-d 23:59:59');
			$start_stamp_end_epoch = strtotime($start_stamp_end);			
		}
		else
		{
			$start_stamp_end_epoch = strtotime($start_stamp_end . " 23:59:59");
		}
		
		$sql  = " SELECT d.cdr_uuid,";
		$sql .= " d.start_epoch,";
		$sql .= " m.mailing_name Nome_Mailing,";
		$sql .= " d.destination_number Numero,";
		$sql .= " d.mailing_key Chave,";
		$sql .= " if(d.start_epoch IS NULL";
		$sql .= " OR LENGTH(TRIM(d.start_epoch)) = 0, 0, FROM_UNIXTIME(d.start_epoch, '%Y-%m-%d')) Data_Discagem,"; 
		$sql .= " if(d.start_epoch IS NULL";
		$sql .= " OR LENGTH(TRIM(d.start_epoch)) = 0, 0, FROM_UNIXTIME(d.start_epoch, '%H:%i:%s')) Hora_Discagem,";
		$sql .= " d.answer_epoch,";
		$sql .= " if(d.answer_epoch IN ('', 0)"; 
		$sql .= " OR d.answer_epoch IS NULL, 0, FROM_UNIXTIME(d.answer_epoch, '%H:%i:%s')) Hora_Atendimento,";
		$sql .= " if(d.end_epoch IN ('', 0)";
		$sql .= " OR d.end_epoch IS NULL, 0, FROM_UNIXTIME(d.end_epoch, '%H:%i:%s')) Hora_Fim,";
		$sql .= " if(d.answer_epoch IN ('', 0)";
		$sql .= " OR d.answer_epoch IS NULL, 0, SEC_TO_TIME(d.end_epoch - d.cc_queue_answered_epoch)) Tempo_Falado,"; 
		$sql .= " if(d.start_epoch IN ('', 0)";
		$sql .= " OR d.start_epoch IS NULL, 0, SEC_TO_TIME(d.end_epoch - d.start_epoch)) Duracao_Total,";
		$sql .= " SUBSTRING_INDEX(d.cc_queue, '@', 1) Fila,";
		$sql .= " SUBSTRING_INDEX(d.cc_agent, '@', 1) Agente,";
		$sql .= " CASE LOWER(d.hangup_side)";
		$sql .= " WHEN 'member' THEN 'Assinante'";
		$sql .= " WHEN 'agent' THEN 'Agente'";
		$sql .= " WHEN 'dialer' THEN 'Discador'";
		$sql .= " WHEN 'carrier' THEN 'Operadora'";
		$sql .= " WHEN 'detected_speech' THEN 'Audio Detectado'";
		$sql .= " ELSE d.hangup_side";
		$sql .= " END AS Lado_Desligamento,"; 
		$sql .= " CASE LOWER(d.hangup_cc_event_stress)"; 
		$sql .= " WHEN 'answered' THEN 'Atendida'";
		$sql .= " WHEN 'answer_machine' THEN 'ANSWER_MACHINE'";
		$sql .= " WHEN 'dialer' THEN 'Discador'";
		$sql .= " WHEN 'mudo' THEN 'Mudo'";
		$sql .= " ELSE d.hangup_cc_event_stress"; 
		$sql .= " END AS Audio_Analise,";
		$sql .= " s.sip_enum Fim_Chamada_Discador,";
		$sql .= " CASE LOWER(d.hangup_cause)";
		$sql .= " WHEN 'normal_clearing' THEN 'ATENDIDA'";
		$sql .= " ELSE 'NAO_ATENDIDA'";
		$sql .= " END AS Atendimento_Operadora,";
		$sql .= " d.hangup_cause Fim_Chamada_Operadora";
		$sql .= " FROM   calliopedb.v_xml_cdr_dialer d";
		$sql .= " INNER JOIN `dialer_$domain_uuid`.v_mailings m";
		$sql .= " ON m.mailing_uuid = d.mailing_uuid";
		$sql .= " LEFT JOIN calliopedb.v_hangup_causes c ON c.sip_enum = d.hangup_cause";
		$sql .= " LEFT JOIN calliopedb.v_hangup_causes s ON s.sip_code = BINARY(d.disposition_code)";
		$sql .= " LEFT JOIN calliopedb.v_hangup_cause_transcript tr ON c.hangup_cause_transcript_uuid = tr.uuid";
		$sql .= " WHERE d.domain_uuid = ?";
		$sql .= " AND m.mailing_uuid = ?";
		$sql .= " AND d.start_epoch BETWEEN ? AND ?";
		$sql .= " ORDER BY start_epoch DESC";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
		$stmt->bindParam(2, $mailing_uuid, PDO::PARAM_STR);
		$stmt->bindParam(3, $start_stamp_begin_epoch, PDO::PARAM_STR);
		$stmt->bindParam(4, $start_stamp_end_epoch, PDO::PARAM_STR);
		
		if($stmt->execute())
		{
			$all_dialer = array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$dialer = array();
				$dialer["uuid"] = $row["cdr_uuid"];
				$dialer["phone"] = $row["Numero"];
				$dialer["mailing_key"] = $row["Chave"];
				$dialer["date_of_dialing"] = $row["Data_Discagem"];
				$dialer["time_of_dialing"] = $row["Hora_Discagem"];
				$dialer["end_time"] = $row["Hora_Fim"];
				$dialer["time_spoken"] = $row["Tempo_Falado"];
				$dialer["total_duration"] = $row["Duracao_Total"];
				$dialer["queue"] = $row["Fila"];
				$dialer["agent"] = $row["Agente"];
				$dialer["who_hungup"] = $row["Lado_Desligamento"];
				$dialer["end_call_dialer"] = $row["Fim_Chamada_Discador"];
				$dialer["customer_service"] = $row["Atendimento_Operadora"];
				$dialer["end_call_operator"] = $row["Fim_Chamada_Operadora"];
				array_push($all_dialer, $dialer);
			}
			
			return $all_dialer;
		}
		else 
		{
			return "404@00";
		}
	}
	
	public function returnsDialerAll($params)
	{
		$domain_uuid = $params["domain_uuid"];
		$start_stamp_begin = $params["date"];
		$start_stamp_end = $params["date"];
		
		if (strlen($start_stamp_begin) == 0)
		{
			$start_stamp_begin = date('Y-m-d 00:00:00');
			$start_stamp_begin_epoch = strtotime($start_stamp_begin);
		}
		else
		{
			$start_stamp_begin_epoch = strtotime($start_stamp_begin . " 00:00:00");
		}
		
		if (strlen($start_stamp_end) == 0)
		{
			$start_stamp_end = date('Y-m-d 23:59:59');
			$start_stamp_end_epoch = strtotime($start_stamp_end);			
		}
		else
		{
			$start_stamp_end_epoch = strtotime($start_stamp_end . " 23:59:59");
		}
		
		$sql  = " SELECT d.cdr_uuid,";
		$sql .= " d.start_epoch,";
		$sql .= " d.destination_number Numero,";
		$sql .= " d.mailing_key Chave,";
		$sql .= " if(d.start_epoch IS NULL";
		$sql .= " OR LENGTH(TRIM(d.start_epoch)) = 0, 0, FROM_UNIXTIME(d.start_epoch, '%Y-%m-%d')) Data_Discagem,"; 
		$sql .= " if(d.start_epoch IS NULL";
		$sql .= " OR LENGTH(TRIM(d.start_epoch)) = 0, 0, FROM_UNIXTIME(d.start_epoch, '%H:%i:%s')) Hora_Discagem,";
		$sql .= " d.answer_epoch,";
		$sql .= " if(d.answer_epoch IN ('', 0)"; 
		$sql .= " OR d.answer_epoch IS NULL, 0, FROM_UNIXTIME(d.answer_epoch, '%H:%i:%s')) Hora_Atendimento,";
		$sql .= " if(d.end_epoch IN ('', 0)";
		$sql .= " OR d.end_epoch IS NULL, 0, FROM_UNIXTIME(d.end_epoch, '%H:%i:%s')) Hora_Fim,";
		$sql .= " if(d.answer_epoch IN ('', 0)";
		$sql .= " OR d.answer_epoch IS NULL, 0, SEC_TO_TIME(d.end_epoch - d.cc_queue_answered_epoch)) Tempo_Falado,"; 
		$sql .= " if(d.start_epoch IN ('', 0)";
		$sql .= " OR d.start_epoch IS NULL, 0, SEC_TO_TIME(d.end_epoch - d.start_epoch)) Duracao_Total,";
		$sql .= " SUBSTRING_INDEX(d.cc_queue, '@', 1) Fila,";
		$sql .= " SUBSTRING_INDEX(d.cc_agent, '@', 1) Agente,";
		$sql .= " CASE LOWER(d.hangup_side)";
		$sql .= " WHEN 'member' THEN 'Assinante'";
		$sql .= " WHEN 'agent' THEN 'Agente'";
		$sql .= " WHEN 'dialer' THEN 'Discador'";
		$sql .= " WHEN 'carrier' THEN 'Operadora'";
		$sql .= " WHEN 'detected_speech' THEN 'Audio Detectado'";
		$sql .= " ELSE d.hangup_side";
		$sql .= " END AS Lado_Desligamento,"; 
		$sql .= " CASE LOWER(d.hangup_cc_event_stress)"; 
		$sql .= " WHEN 'answered' THEN 'Atendida'";
		$sql .= " WHEN 'answer_machine' THEN 'ANSWER_MACHINE'";
		$sql .= " WHEN 'dialer' THEN 'Discador'";
		$sql .= " WHEN 'mudo' THEN 'Mudo'";
		$sql .= " ELSE d.hangup_cc_event_stress"; 
		$sql .= " END AS Audio_Analise,";
		$sql .= " s.sip_enum Fim_Chamada_Discador,";
		$sql .= " CASE LOWER(d.hangup_cause)";
		$sql .= " WHEN 'normal_clearing' THEN 'ATENDIDA'";
		$sql .= " ELSE 'NAO_ATENDIDA'";
		$sql .= " END AS Atendimento_Operadora,";
		$sql .= " d.hangup_cause Fim_Chamada_Operadora";
		$sql .= " FROM   calliopedb.v_xml_cdr_dialer d";
		$sql .= " LEFT JOIN calliopedb.v_hangup_causes c ON c.sip_enum = d.hangup_cause";
		$sql .= " LEFT JOIN calliopedb.v_hangup_causes s ON s.sip_code = BINARY(d.disposition_code)";
		$sql .= " LEFT JOIN calliopedb.v_hangup_cause_transcript tr ON c.hangup_cause_transcript_uuid = tr.uuid";
		$sql .= " WHERE d.domain_uuid = ?";
		$sql .= " AND d.start_epoch >= ? AND d.start_epoch <= ?";
		$sql .= " ORDER BY start_epoch DESC";
		$sql .= " LIMIT 10";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $domain_uuid, PDO::PARAM_STR);
		$stmt->bindParam(2, $start_stamp_begin_epoch, PDO::PARAM_STR);
		$stmt->bindParam(3, $start_stamp_end_epoch, PDO::PARAM_STR);
		
		if($stmt->execute())
		{
			$all_dialer = array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$dialer = array();
				$dialer["uuid"] = $row["cdr_uuid"];
				$dialer["phone"] = $row["Numero"];
				$dialer["mailing_key"] = $row["Chave"];
				$dialer["date_of_dialing"] = $row["Data_Discagem"];
				$dialer["time_of_dialing"] = $row["Hora_Discagem"];
				$dialer["end_time"] = $row["Hora_Fim"];
				$dialer["time_spoken"] = $row["Tempo_Falado"];
				$dialer["total_duration"] = $row["Duracao_Total"];
				$dialer["queue"] = $row["Fila"];
				$dialer["agent"] = $row["Agente"];
				$dialer["who_hungup"] = $row["Lado_Desligamento"];
				$dialer["end_call_dialer"] = $row["Fim_Chamada_Discador"];
				$dialer["customer_service"] = $row["Atendimento_Operadora"];
				$dialer["end_call_operator"] = $row["Fim_Chamada_Operadora"];
				array_push($all_dialer, $dialer);
			}
			
			return $all_dialer;
		}
		else 
		{
			return "404@00";
		}
	}
}

?>