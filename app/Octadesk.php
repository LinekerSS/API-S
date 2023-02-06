<?php

class Octadesk extends Core 
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function createTicket($params)
	{
		error_log("createTicketOctadesk ..: " . json_encode($params, true));
		
		$webhook = $params["webhook"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		
		$url = $myObj["u"]."/tickets";		
		$access_token = $myObj["t"];
		$email = $myObj["e"];
		
		$number = $params["number"];
		$extension = $params["extension"];

		$body = '{
		  "requester": {
			"email": "'.$email.'",
			"name": ""
		  },
		  "numberChannel": 0,
		  "summary": "Chamada recebida de '.$number.'",
		  "tags": [],
		  "inbox": {
			  "domain": "CallCenterPro",
			  "email": "'.$email.'"
		  },
		  "comments": {
			  "description": {
				  "content": "Atendida pelo ramal: '.$extension.'"
			  }
		  },
		  "idCurrentStatus": "18d082d0-4a24-4620-9d96-a827323dfc8b"
		}';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		
		curl_setopt
		(
			$ch, CURLOPT_HTTPHEADER, array
			(
				'Content-Type: application/json',
				"Authorization: Bearer $access_token"
			)
		);
	
		$result = curl_exec($ch);
		
		curl_close($ch);

		if($result)
		{
			$decoded = json_decode($result,true);
			if($decoded['number'])
			{
				$return['number_ticket'] = $decoded['number'];
				$return['url_ticket'] = $decoded['octadeskNumberUrl'];
				$return['status'] = "200@00";
				return $return;
			}
			else
			{
				return "404@00";
			}
		}
		else
		{
			return "400@00";
		}
		
	}
	
	public function createTicketCanceled($params)
	{
		error_log("createTicketCanceledOctadesk ..: " . json_encode($params, true));
		
		$webhook = $params["webhook"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		
		$url = $myObj["u"]."/tickets";		
		$access_token = $myObj["t"];
		$email = $myObj["e"];
		
		$number = $params["number"];
		$value1 = $params["value1"];

		$body = '{
		  "requester": {
			"email": "'.$email.'",
			"name": ""
		  },
		  "numberChannel": 0,
		  "summary": "Chamada recebida de '.$number.'",
		  "tags": [],
		  "inbox": {
			  "domain": "CallCenterPro",
			  "email": "'.$email.'"
		  },
		  "comments": {
			  "description": {
				  "content": "Chamada cancelada na Fila \n Tempo em Fila: '.$value1.'"
			  }
		  }
		}';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		
		curl_setopt
		(
			$ch, CURLOPT_HTTPHEADER, array
			(
				'Content-Type: application/json',
				"Authorization: Bearer $access_token"
			)
		);
	
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		if($result)
		{
			$decoded = json_decode($result,true);
			if($decoded['number'])
			{
				$return['number_ticket'] = $decoded['number'];
				$return['url_ticket'] = $decoded['octadeskNumberUrl'];
				$return['status'] = "200@00";
				return $return;
			}
			else
			{
				return "404@00";
			}
		}
		else
		{
			return "400@00";
		}
		
	}
	
	
	public function updateTicketOctadesk($params)
	{		
		$number = $params['number_ticket'];
		
		$webhook = $params["webhook"];
		
		$myJson = base64_decode($webhook);
		
		$myObj = json_decode($myJson, true);
		
		$url = $myObj["u"]."/tickets/$number";		
		$access_token = $myObj["t"];
		
		if(isset($params['file']))
		{
			$file = $params['file'];
			$attachment = ' ,"comments": {
			"description": {
			  "content": "Audio da Chamada",
			  "attachments": [
				{
				  "url": "'.$file.'",
				  "fileType": "audio/mp3",
				  "urlPreview": "'.$file.'"
				}
			  ]
			}
		  }';
		}
		else
		{
			$attachment = '';
		}
		$body = '{
		  "idCurrentStatus": "18d082d0-4a24-4620-9d96-a827323dfc8b"'.$attachment .'
		}';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		
		curl_setopt
		(
			$ch, CURLOPT_HTTPHEADER, array
			(
				'Content-Type: application/json',
				"Authorization: Bearer $access_token"
			)
		);

		$result = curl_exec($ch);
		curl_close($ch);
		
		if($result)
		{
			$decoded = json_decode($result,true);
			if($decoded['number'])
			{
				return "200@00";
			}
			else
			{
				return $decoded;
			}
		}
		else
		{
			return "400@00";
		}
		
	}
	
	public function uploadAttachment($params)
	{		
		$path_file = $params["path_file"];
		$filename = $params["filename"];
		$webhook = $params["webhook"];
		
		$myJson = base64_decode($webhook);
		$myObj = json_decode($myJson, true);
		
		$url = $myObj["u"]."/tickets/attachments/upload";		
		$access_token = $myObj["t"];
		
		//$url = "api.octadesk.services/tickets/attachments/upload";
		//$access_token = "OCTADESK.eyJuYmYiOjE1ODE1MTQ2NTAsImV4cCI6MTkwMjkyMjY1MCwiaXNzIjoiaHR0cDovL2lkZW50aXR5LXNlcnZlci5vY3RhZGVzay5zZXJ2aWNlcyIsImF1ZCI6WyJodHRwOi8vaWRlbnRpdHktc2VydmVyLm9jdGFkZXNrLnNlcnZpY2VzL3Jlc291cmNlcyIsImdlbmVyYWwiXSwiY2xpZW50X2lkIjoiOWxGZk5LbThOWjBzIiwic3ViIjoiMTYzMGM2MjEtNDYwNy00ZTMzLTk1NmUtOTk4ODhiM2ExYmE2IiwiYXV0aF90aW1lIjoxNTgxNTE0NjUwLCJpZHAiOiJsb2NhbCIsInN1YmRvbWFpbiI6Im15dWMyYiIsInJvbGUiOiJvd25lciIsInR5cGUiOiJub25lIiwic2NvcGUiOlsiZ2VuZXJhbCJdLCJhbXIiOlsiQmVhcmVyIl19.qGEkTqxt5JXhIKGTe3aGsDvw_gco4rw1qtSmnxSF5z6sChW24CBerX5Qnfb3I8ecTcwV_QnSnYSo0XKp55xX2_n93IybETBjxoQFsLEnHV0196d5eeGDP7kUA9xKpf9m9u-B92CADlx5H5wQtb_EjgCKpRL8bEmUyN7E_qD-ilJczrA76hVFd-21i34zMmRYYEXm7FP9Qr4k6zzwyx-9-sBOCtmfi1hVwWiBaZT1lokLWiUCHiR3UHqiGE1Ix1qcZBmbudv5t7O7_21dC1WaJZjBkO0Qd9NqUg8M0APrVET0SQNGPjD4Hh3VKa5_mAl2LHtKsE1GepiIbJZwfXP5DA";

		$body['file'] = curl_file_create($path_file, 'audio/mp3', $filename);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

		curl_setopt
		(
			$ch, CURLOPT_HTTPHEADER, array
			(
				"Accept: application/json",
				"Authorization: Bearer $access_token"
			)
		);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		
		curl_close($ch);
		
		if($result)
		{
			$decoded = json_decode($result, true);
			$response["name"] = $decoded["name"];
			$response["url"] = $decoded["url"];
			$response["status"] = "200@00";
			return $response;
		}
		else
		{
			//$decoded = json_decode($info, true);
			$response["status"] = "400@00";
			return $response;
		}
		
	}
}


	


?>