<?php

class Zendesk extends Core
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function logEvent($event)
	{
		$enable = true;
		
		if($enable)
		{
			error_log(json_encode($event));
		}
	}
	
	public function toPhoneAdd55($number)
	{
		if(preg_match('/^\+55/', $number))
		{
			
		}
		elseif(preg_match('/^55/', $number))
		{
			$number = '+'.$number;
		}
		else
		{
			if(strlen($number) > 8)
			{
				$number = '+55'.$number;
			}
		}
		
		return $number;
	}
	
	public function sendZendeskNotification($params)
	{
		error_log("sendZendeskNotification");
		
		$webhook = $params["webhook"];
		$agent = $params["agent"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		$app_id = $myObj["a"];
		
		$z = new Zendesk();
		$agent_id = $z->getAppIdAgent($agent);
		
		error_log("agent_id " . $agent_id);

		if(strlen($agent_id) == 0)
		{
			return "404@00";
		}
		
		$event = '{"event": "incoming_call", "app_id": "'.$app_id.'", "agent_id": "'.$agent_id.'"}';
		
		error_log(json_encode($event, true));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/apps/notify.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		
		error_log(json_encode($output, true));
		
		return "200@00";
	}
	
	public function createTicketInbound($params)
	{
		error_log("createTicketInbound ..: " . json_encode($params, true));
		
		$z = new Zendesk();

		$app = $params["app"];
		$webhook = $params["webhook"];
		$ticket_id = $params["ticket_id"];
		$agent = $params["agent"];
		$number = $params["number"];
		$session_uuid = $params["session_uuid"];
		$user_cpf = $params["user_cpf"];
		$user_cnpj = $params["user_cnpj"];
		$user_id = $params["user_id"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		
		if((strlen($user_id) == 0) && (strlen($user_cpf) == 0) && (strlen($user_cnpj) == 0))
		{
			error_log("createUser number $number");
			$user_id = $z->createUser($params, $number);
		}
		elseif(strlen($user_id) == 0)
		{
			if(strlen($user_cpf) > 0)
			{
				error_log("createUser user_cpf $user_cpf");
				$user_id = $z->createUser($params, $user_cpf);
			}
			elseif(strlen($user_cnpj) > 0)
			{
				error_log("createUser user_cnpj $user_cnpj");
				$user_id = $z->createUser($params, $user_cnpj);
			}
		}
		
		error_log("user_id " . $user_id);
		error_log("user_id " . $user_id);
		
		$_event = array();
		$_event["ticket"]["requester_id"] = $user_id;
		$_event["ticket"]["subject"] = "Chamada recebida e atendida";
		$_event["ticket"]["comment"]["body"] = "Telefone: " . $z->toPhoneAdd55($number);
		$_event["ticket"]["comment"]["author_id"] = $user_id;
		
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url . "/api/v2/tickets.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);
		
		$ticket_id = $decoded["ticket"]["id"];
		
		$agent_id = $z->getAppIdAgent($agent);
		$z->createNotifyTicketAgent($ticket_id, $agent_id, $app_url, $app_token);
		
		if(strlen($agent_id) == 0)
		{
			return "404@00";
		}
		
		return $ticket_id;
	}
	
	public function createNotifyTicketAgent($ticket_id, $agent_id, $app_url, $app_token)
	{
		error_log("createNotifyTicketAgent");
		error_log("ticket_id " . $ticket_id);
		error_log("agent_id " . $agent_id);
		error_log("app_token " . $app_token);
		error_log("app_url " . $app_url."/api/v2/channels/voice/agents/$agent_id/tickets/$ticket_id/display.json");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/channels/voice/agents/$agent_id/tickets/$ticket_id/display.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		
		$decoded = json_decode($output, true);
	}
	
	public function createTicketCanceled($params)
	{
		error_log("createTicketCanceled");
		error_log("createTicketCanceled" . json_encode($params, true));
		
		$app = $params["app"];
		$webhook = $params["webhook"];
		$value1 = $params["value1"];
		$value2 = $params["value2"];
		$value3 = $params["value3"];
		$value4 = $params["value4"];
		$value5 = $params["value5"];
		$value6 = $params["value6"];
		$value7 = $params["value7"];
		$user_cpf = $params["user_cpf"];
		$user_cnpj = $params["user_cnpj"];
		$user_id = $params["user_id"];
		$number = $params["number"];
		$gateway = $params["gateway"];
		$digits = $params["user_digits"];
		$ticket_id_digits = $params["ticket_id_digits"];
		$domain = $params["domain"];
		
		error_log("ticket_id json " . $params["ticket_id"]);
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		$id_tme = $myObj["tme"];
		$id_tma = $myObj["tma"];
		
		$z = new Zendesk();
		$value5 = $z->toPhoneAdd55($value5);
		
		if((strlen($user_id) == 0) && (strlen($user_cpf) == 0) && (strlen($user_cnpj) == 0))
		{
			error_log("createUser number $number");
			$user_id = $z->createUser($params, $number);
		}
		elseif(strlen($user_id) == 0)
		{
			if(strlen($user_cpf) > 0)
			{
				error_log("createUser user_cpf $user_cpf");
				$user_id = $z->createUser($params, $user_cpf);
			}
			elseif(strlen($user_cnpj) > 0)
			{
				error_log("createUser user_cnpj $user_cnpj");
				$user_id = $z->createUser($params, $user_cnpj);
			}
		}
		
		error_log("user_id " . $user_id);
		error_log("user_id " . $user_id);

		$_event = array();
		$_event["ticket"]["requester_id"] = $user_id;
		$_event["ticket"]["status"] = "open";
		$_event["ticket"]["subject"] = "Chamada abandonada na fila";
		$_event["ticket"]["comment"]["body"] = "Telefone: " .$value5 . " \n Data: $value6 \n Hora Início: $value7 \n Hora Fim: $value4 \n T.E: $value1 \n T.A: $value2 \n Digitado na URA: $digits \n Ticket Digitado: $ticket_id_digits \n Tronco de Entrada: $gateway";
		$_event["ticket"]["comment"]["public"] = "false";
		$_event["ticket"]["comment"]["author_id"] = $user_id;
		$_event["ticket"]["custom_fields"][0]["id"] = $id_tme;
		$_event["ticket"]["custom_fields"][0]["value"] = $value1;
		$_event["ticket"]["custom_fields"][1]["id"] = $id_tma;
		$_event["ticket"]["custom_fields"][1]["value"] = $value2;
		
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $app_url. "/api/v2/tickets.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);
		
		$author_id = $decoded["audit"]["author_id"];
		
		return $author_id;
	}
	
	public function createTicketOutbound($params)
	{
		error_log("createTicketOutbound");
		
		$app = $params["app"];
		$webhook = $params["webhook"];
		$code = $params["code"];
		$agent = $params["agent"];
		$number = $params["number"];
		$session_uuid = $params["session_uuid"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		
		$z = new Zendesk();
		$agent_id = $z->getAppIdAgent($agent);
		
		if(strlen($agent_id) == 0)
		{
			return "404@00";
		}
		
		$_event = array();
		$_event["ticket"]["status"] = "open";
		$_event["ticket"]["subject"] = "Chamada efetuada";
		$_event["ticket"]["requester_id"] = $agent_id;
		$_event["ticket"]["comment"]["author_id"] = $agent_id;
		$_event["ticket"]["comment"]["body"] = " Ticket aberto automaticamente pelo PABX";
		
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url . "/api/v2/tickets.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);
		
		$ticket_id = $decoded["ticket"]["id"];
		
		$z->createNotifyTicketAgent($ticket_id, $agent_id, $app_url, $app_token);
		
		return $ticket_id;
	}
	
	public function updateTicketOutboundClosed($params)
	{
		error_log("updateTicketOutboundClosed");
		error_log("updateTicketOutboundClosed" . json_encode($params));
		
		$app = $params["app"];
		$webhook = $params["webhook"];
		$agent = $params["agent"];
		$session_uuid = $params["session_uuid"];
		$ticket_id = $params["ticket_id"];
		$domain_name = $params["domain_name"];
		
		$value1 = $params["value1"]; // queue_duration
		$value2 = $params["value2"]; // answered_duration
		$value3 = $params["value3"]; // cc_queue_answered_epoch
		$value4 = $params["value4"]; // H:i:s end_epoch
		$value5 = $params["value5"]; // number
		$value6 = $params["value6"]; // d/m/Y end_epoch
		$value7 = $params["value7"]; // d/m/Y start_epoch
		$recording = "https://$domain_name/app/recording_play/recording_play.php?id=$session_uuid";

		$myJson = base64_decode($webhook);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		$id_tme = $myObj["tme"];
		$id_tma = $myObj["tma"];
		
		$z = new Zendesk();
		$author_id = $z->getAppIdAgent($agent);
			
		$_event = array();
		$_event["ticket"]["status"] = "open";
		$_event["ticket"]["comment"]["author_id"] = $author_id;
		$_event["ticket"]["comment"]["body"] = "Data: $value6 \n Hora Início: $value7 \n Hora Fim: $value4 \n T.E: $value1 \n T.A: $value2 \n Link da Gravação: $recording \n Telefone: $value5";
		$_event["ticket"]["comment"]["public"] = "false";
		$_event["ticket"]["custom_fields"][0]["id"] = $id_tme;
		$_event["ticket"]["custom_fields"][0]["value"] = $value1;
		$_event["ticket"]["custom_fields"][1]["id"] = $id_tma;
		$_event["ticket"]["custom_fields"][1]["value"] = $value2;

		$event = json_encode($_event, true);

		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/tickets/$ticket_id.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		
		return $ticket_id;
	}
	
	public function updateTicketInboundClosed($params)
	{
		error_log("updateTicketInboundClosed");
		error_log("updateTicketInboundClosed " . json_encode($params));
		
		$z = new Zendesk();

		$app = $params["app"];
		$webhook = $params["webhook"];
		$ticket_id = $params["ticket_id"];
		$agent = $params["agent"];
		$gateway = $params["gateway"];
		$session_uuid = $params["session_uuid"];
		$domain = $params["domain"];
		$ticket_src = $params["ticket_src"];
		$digits = $params["digits"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];
		$id_tme = $myObj["tme"];
		$id_tma = $myObj["tma"];
		
		if($ticket_src == "true")
		{
			$new_tme = $params["value1"]; // queue_duration
			$new_tma = $params["value2"]; // answered_duration
			$auxCustomFields = $z->getCustomFieldsTME_TMA($ticket_id, $new_tme, $new_tma, $app_url, $app_token, $id_tme, $id_tma);
			
			$ticket_tme = $auxCustomFields["ticket_tme"]; // queue_duration
			$ticket_tma = $auxCustomFields["ticket_tma"]; // answered_duration
		}
		else
		{
			$new_tme = $params["value1"]; // queue_duration
			$new_tma = $params["value2"]; // answered_duration
			$ticket_tme = $params["value1"]; // queue_duration
			$ticket_tma = $params["value2"]; // answered_duration
		}
		
		$value3 = $params["value3"]; // cc_queue_answered_epoch
		$value4 = $params["value4"]; // H:i:s end_epoch
		$value5 = $params["value5"]; // number
		$value6 = $params["value6"]; // d/m/Y end_epoch
		$value7 = $params["value7"]; // d/m/Y start_epoch
		$recording = "https://".$domain."/app/recording_play/recording_play.php?id=".$session_uuid;
		
		$author_id = $z->getAppIdAgent($agent);
		
		$_event = array();
		$_event["ticket"]["status"] = "open";
		$_event["ticket"]["comment"]["author_id"] = $author_id;
		$_event["ticket"]["comment"]["body"] = "Data: $value6 \n Hora Início: $value7 \n Hora Fim: $value4 \n T.E: $new_tme \n T.A: $new_tma \n Link da Gravação: $recording \n Telefone: $value5 \n Tronco de Entrada: $gateway \n Digitado: $digits";
		$_event["ticket"]["comment"]["public"] = "false";
		$_event["ticket"]["custom_fields"][0]["id"] = $id_tme;
		$_event["ticket"]["custom_fields"][0]["value"] = $ticket_tme;
		$_event["ticket"]["custom_fields"][1]["id"] = $id_tma;
		$_event["ticket"]["custom_fields"][1]["value"] = $ticket_tma;
		
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/tickets/$ticket_id.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
	}
	
	public function getCustomFieldsTME_TMA($ticket_id, $new_tme, $new_tma, $app_url, $app_token, $id_tme, $id_tma)
	{
		error_log("getCustomFieldsTME_TMA");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/tickets/$ticket_id.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);

		foreach($decoded["ticket"]["custom_fields"] as $row)
		{
			error_log("custom_fields id_tma " . $id_tma);
			error_log("custom_fields ticket_tma " . $row["value"]);
			error_log("custom_fields id_tme " . $id_tme);
			error_log("custom_fields ticket_tme " . $row["value"]);
			
			if($row["id"] == $id_tma)
			{
				$ticket_tma = $row["value"];
			}
			
			if($row["id"] == $id_tme)
			{
				$ticket_tme = $row["value"];
			}
		}
		
		error_log("ticket_tma " . $ticket_tma);
		error_log("ticket_tme " . $ticket_tme);
		error_log("new_tme " . $new_tme);
		error_log("new_tma " . $new_tma);
		
		$auxCustomFields = array();
		if(strlen($ticket_tme) > 0)
		{
			$auxCustomFields["ticket_tme"] = date('H:i:s', strtotime('+' . date('H', strtotime($new_tme)) . ' hour +' . date('i', strtotime($new_tme)) . ' minute +' . date('s', strtotime($new_tme)) . ' second', strtotime(date($ticket_tme))));
		}
		else
		{
			$auxCustomFields["ticket_tme"] = $new_tme;
		}
		
		if(strlen($ticket_tma) > 0)
		{
			$auxCustomFields["ticket_tma"] = date('H:i:s', strtotime('+' . date('H', strtotime($new_tma)) . ' hour +' . date('i', strtotime($new_tma)) . ' minute +' . date('s', strtotime($new_tma)) . ' second', strtotime(date($ticket_tma))));
		}
		else
		{
			$auxCustomFields["ticket_tma"] = $new_tma;
		}
		
		return $auxCustomFields;
	}
	
	public function createTicketTransfer($params)
	{
		error_log("createTicketTransfer");
		
		$ticket_id = $params["ticket_id"];
		$agent = $params["agent"];
		$webhook = $params["webhook"];

		$myJson = base64_decode($webhook);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		$app_code = $myObj["c"];

		$z = new Zendesk();
		$agent_id = $z->getAppIdAgent($agent);
		
		if(strlen($agent_id) == 0)
		{
			return "404@00";
		}
		
		$_event = array();
		$_event["ticket"]["status"] = "open";
		$_event["ticket"]["comment"]["author_id"] = $agent_id;
		$_event["ticket"]["comment"]["body"] = "Ticket digitado na URA.";
		$_event["ticket"]["comment"]["public"] = "false";
		
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/tickets/$ticket_id.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);
		
		$ticket_id = $decoded["ticket"]["id"];
		
		$z->createNotifyTicketAgent($ticket_id, $agent_id, $app_url, $app_token);
		
		return $ticket_id;
	}
	
	public function createUser($params, $user_name)
	{
		error_log("createUser");
		
		$webhook = $params["webhook"];

		$myJson = base64_decode($webhook);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		
		$z = new Zendesk();
		
		$user_fields_cpf = $params["user_cpf"];
		$user_fields_cnpj = $params["user_cnpj"];
		$user_phone = $z->toPhoneAdd55($params["number"]);

		$_event = array();
		$_event["user"]["name"] = $user_name;
		$_event["user"]["phone"] = $user_phone;
		$_event["user"]["user_fields"]["cpf"] = $user_fields_cpf;
		$_event["user"]["user_fields"]["cnpj"] = $user_fields_cnpj;
		$event = json_encode($_event, true);
		
		error_log("payloads " . $event);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/users.json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Content-Length: ' . strlen($event),
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);
		
		$user_id = $decoded["user"]["id"];
		
		return $user_id;
	}
	
	public function searchUserCPF($params)
	{
		error_log("searchUserCPF");
		
		$user_cpf = $params["user_cpf"];
		$webhook = $params["webhook"];

		$myJson = base64_decode($webhook);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		
		if(strlen($user_cpf) == 0)
		{
			return "";
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/search?query=type:user%20cpf:$user_cpf");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($output, true);
		
		$tag = array();
		if(isset($result["results"][0]["id"]))
		{
			error_log("results " . $result["results"][0]["id"]);
			$tag["user_id"] = $result["results"][0]["id"];
			$tag["ticket_query"] = "true";
			$tag["ticket_tag"] = "cpf";
		}
		else
		{
			error_log("results vazio");
			$tag["user_id"] =  "";
			$tag["ticket_query"] = "true";
			$tag["ticket_tag"] = "cpf";
		}
		
		return $tag;
	}
	
	public function searchUserCNPJ($params)
	{
		error_log("searchUserCNPJ");
		
		$user_cnpj = $params["user_cnpj"];
		$webhook = $params["webhook"];

		$myJson = base64_decode($webhook);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		
		
		if(strlen($user_cnpj) == 0)
		{
			return "";
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/search?query=type:user%20cnpj:$user_cnpj");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($output, true);
		
		$tag = array();
		if(isset($result["results"][0]["id"]))
		{
			error_log("results " . $result["results"][0]["id"]);
			$tag["user_id"] = $result["results"][0]["id"];
			$tag["ticket_query"] = "true";
			$tag["ticket_tag"] = "cnpj";
		}
		else
		{
			error_log("results vazio");
			$tag["user_id"] =  "";
			$tag["ticket_query"] = "true";
			$tag["ticket_tag"] = "cnpj";
		}
		
		return $tag;
	}
	
	public function searchTicket($params)
	{
		error_log("searchTicket");
		error_log("searchTicket..: " . json_encode($params, true));
		
		$z = new Zendesk();
		
		$tag = array();
		if($z->validateCnpj($params["user_cnpj"]))
		{
			error_log("validateCnpj Ok");
			$user_id = $z->searchUserCNPJ($params);
			error_log("user_id . " . json_encode($user_id, true));
			return $user_id;
		}
		
		if($z->validateCPF($params["user_cpf"]))
		{
			error_log("validateCPF Ok");
			$user_id = $z->searchUserCPF($params);
			error_log("user_id . " . json_encode($user_id, true));
			return $user_id;
		}
		
		if(strlen($params["ticket_id"]) < 8)
		{
			$ticket_id = $z->searchTicketId($params);
			error_log("ticket_id " . json_encode($ticket_id));
			return $ticket_id;
		}
		
		return "";
	}
	
	public function searchTicketId($params)
	{
		error_log("searchTicketId" . json_encode($params));
		
		$ticket_id = $params["ticket_id"];
		$webhook = $params["webhook"];

		$myJson = base64_decode($webhook);
		$myObj = json_decode($myJson, true);

		$myObj = json_decode($myJson, true);
		$app_url = $myObj["u"];
		$app_token = $myObj["t"];
		
		if(strlen($ticket_id) == 0)
		{
			return "";
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $app_url."/api/v2/tickets/".$ticket_id.".json");
		curl_setopt($ch, CURLOPT_USERPWD, base64_encode($app_token));
		$headers = array
		(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($app_token)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($output, true);
		
		$tag = array();
		if(isset($result["ticket"]["id"]))
		{
			$tag["ticket_id"] = $result["ticket"]["id"];
			$tag["user_id"] = $result["ticket"]["requester_id"];
			$tag["ticket_query"] = "true";
			$tag["ticket_tag"] = "ticket";
		}
		else
		{
			$tag["ticket_id"] =  "";
			$tag["user_id"] =  "";
			$tag["ticket_query"] = "false";
			$tag["ticket_tag"] = "ticket";
		}
		
		return $tag;
	}
	
	public function getAppIdAgent($agent)
	{
		error_log("getAppIdAgent");
		error_log("getAppIdAgent " .$agent);
		
		$agent_app_id = "";
		
		$sql  = "SELECT agent_app_id ";
		$sql .= "FROM v_call_center_agents d ";
		$sql .= "WHERE d.agent_name = ? ";
		$sql .= "LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		
		try
		{
			$stmt->bindParam(1, $agent, PDO::PARAM_STR);
			
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$agent_app_id = $row["agent_app_id"];
				}
				
				return $agent_app_id;
			}
		}
		catch(PDOExecption $e)
		{
						
		}
		
		return "";
	}
	
	public function validateCnpj($cnpj)
	{
		error_log("validateCnpj");
		
		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

		if (strlen($cnpj) != 14)
		{
			return false;
		}
		
		if (preg_match('/(\d)\1{13}/', $cnpj))
		{
			return false;
		}
		
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		
		$resto = $soma % 11;
		
		if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
		{
			return false;
		}
		
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		
		$resto = $soma % 11;
		
		return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
	}
	
	public function validateCPF($cpf)
	{
		error_log("validateCPF");
		
		$cpf = preg_replace( '/[^0-9]/is', '', $cpf );
		 
		if (strlen($cpf) != 11)
		{
			return false;
		}
		
		if (preg_match('/(\d)\1{10}/', $cpf))
		{
			return false;
		}
		
		for ($t = 9; $t < 11; $t++)
		{
			for ($d = 0, $c = 0; $c < $t; $c++)
			{
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			
			$d = ((10 * $d) % 11) % 10;
			
			if ($cpf[$c] != $d)
			{
				return false;
			}
		}
		
		return true;
	}
}

?>