<?php
if (PHP_SAPI == 'cli-server')
{
    /**
		To help the built-in PHP dev server, check if the request was actually for
		something which should probably be served as a static file
	*/
	
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file))
	{
        return false;
    }
}


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core.php';
require __DIR__ . '/../app/Zendesk.php';
require __DIR__ . '/../app/CallCenter.php';
require __DIR__ . '/../app/Octadesk.php';
require __DIR__ . '/../app/Cti.php';

require __DIR__ . '/../app/Services.php';
require __DIR__ . '/../app/CallBack.php';
require __DIR__ . '/../app/Mailing.php';
require __DIR__ . '/../app/Dialer.php';
require __DIR__ . '/../app/Quality.php';

session_start();
/**
	Instantiate the app
*/

$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

/**
	Set up dependencies
*/

$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

/**
	Register middleware
*/

$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

/**
	Register routes
*/

$routes = require __DIR__ . '/../src/routes.php';
$routes($app);


$app->post('/cti/get-info/', 'ctiGetInfo', function (Request $request, Response $response, $args)
{
	
});

$app->post('/octadesk/upload-attachment/', 'OctaUploadAttachment', function (Request $request, Response $response, $args)
{
	
});

$app->post('/octadesk/update-ticket/', 'OctaUpdateTicket', function (Request $request, Response $response, $args)
{
	
});

$app->post('/octadesk/create-ticket-inbound/', 'OctaCreateTicket', function (Request $request, Response $response, $args)
{
	
});

$app->post('/octadesk/create-ticket-canceled/', 'OctaCreateTicketCanceled', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/notify-create-ticket-agent/', 'createNotifyTicketAgent', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/send_notification_app/', 'sendZendeskNotification', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/create-ticket-inbound/', 'createTicketInbound', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/create-ticket-transfer/', 'createTicketTransfer', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/create-ticket-canceled/', 'createTicketCanceled', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/create-ticket-outbound/', 'createTicketOutbound', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/update-ticket-outbound-closed/', 'updateTicketOutboundClosed', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/update-ticket-inbound-closed/', 'updateTicketInboundClosed', function (Request $request, Response $response, $args)
{
	
});

$app->get('/zendesk/by-json/', 'byJson', function (Request $request, Response $response, $args)
{
	
});

$app->post('/zendesk/search-ticket/', 'searchTicket', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/agent/login/', 'ctiLogin', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/agent/logout/', 'ctiLogout', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/agent/get_break_list/', 'ctiBreakList', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/agent/state_set_break/', 'ctiStateSetBreak', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/agent/state_return_break/', 'ctiStateReturnBreak', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/call/call_services/', 'setCallServices', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/call/call_terminate/', 'setCallTerminate', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/call/call_transfer/', 'setCallTransfer', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/call/call_hold/', 'setCallHoldOn', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/call/call_hold_retrieve/', 'setCallHoldOff', function (Request $request, Response $response, $args)
{
	
});

$app->post('/interact_cti/get_agent_list/', 'getAgentList', function (Request $request, Response $response, $args)
{
	
});

$app->post('/clicktocall2/call/destinations/', 'setCall', function (Request $request, Response $response, $args)
{
	
});

$app->post('/quality/client/', 'setClient', function (Request $request, Response $response) 
{

});


function setClient($request, $response, $args) {	
	

	$headers = $request->getHeaders();
	
	$params = jsonConvert($request->getBody());
	
	error_log(json_encode($params, true));
	
	$valid = array('app', 'webhook', 'agent');	

	if(verifyRequiredParams($valid, $params))
	{
		$db = new Quality();
		$r = $db->getMessage($params);
	}
	else
	{
		$r['response'] = "404";
	}
	return $response->write(json_encode($r, true));
		   $response->withHeader('Content-type', 'application/json');
		   $response->withStatus(200)->write(json_encode($r, true));

}

function ctiLogin($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	/**
		valid
	*/
	
	$valid = array('agent', 'branch', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setAgentLogin($params);
			error_log("setAgentLogin " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "login";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "login";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "login";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function ctiLogout($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'branch', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setAgentLogout($params);
			error_log("setAgentLogout " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "logout";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "logout";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "logout";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function ctiBreakList($request, $response, $args)
{
	error_log("ctiBreakList");
	
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
	error_log("headers " . json_encode($headers, true));
	error_log("params  " . json_encode($params, true));
	
	/** 
		valid
	*/
	
	$valid = array('company_id', 'agent');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->getBreakList($params);
			error_log("getBreakList " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["result"] = $r["result"];
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["result"] = "";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "break";
		$callcenter["response"]["result"] = "";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function ctiStateSetBreak($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'break_id', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setAgentBreak($params);
			error_log("setAgentBreak " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "break";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function ctiStateReturnBreak($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setAgentBreakReturn($params);
			error_log("setAgentBreakReturn " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "break";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "break";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function setCallServices($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'branch', 'to', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setCall($params);
			error_log("setCall " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_services";
			$callcenter["response"]["call_id"] = $r["call_uuid"];
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_services";
			$callcenter["response"]["call_id"] = "";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "call_services";
		$callcenter["response"]["call_id"] = "";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	error_log("callcenter " . json_encode($callcenter));
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function setCallTerminate($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'call_id', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setDisconnectCall($params);
			error_log("setDisconnectCall " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_terminate";
			$callcenter["response"]["status"] = 'ok';
			$callcenter["response"]["status_type"] = '';
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_terminate";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "call_terminate";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	error_log("callcenter " . json_encode($callcenter));
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function setCallTransfer($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'call_id', 'to', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setTransfer($params);
			error_log("setTransfer " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_transfer";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_transfer";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "call_transfer";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	error_log("callcenter " . json_encode($callcenter));
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function setCallHoldOn($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'call_id', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setCallHoldOn($params);
			error_log("setCallHoldOn " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_hold";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_hold";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "call_hold";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	error_log("callcenter " . json_encode($callcenter));
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function setCallHoldOff($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('agent', 'call_id', 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->setCallHoldOff($params);
			error_log("setCallHoldOff " . json_encode($r));
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_hold_retrieve";
			$callcenter["response"]["status"] = $r["status"];
			$callcenter["response"]["status_type"] = $r["status_type"];
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "call_hold_retrieve";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "call_hold_retrieve";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	error_log("callcenter " . json_encode($callcenter));
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function getAgentList($request, $response, $args)
{
	/**
		getHeaders
	*/
	
	$headers = $request->getHeaders();
		
	/** 
		getBody
	*/
	
	$params = jsonConvert($request->getBody());
	
		
	/** 
		valid
	*/
	
	$valid = array('queue' , 'company_id');

	if(isAuthorization($headers))
	{
		if(verifyRequiredParams($valid, $params))
		{
			$db = new CallCenter();
			$r = $db->getAgentList($params);
			error_log("getAgentList " . json_encode($r));
			$callcenter["response"]["queue"] = $params["queue"];
			$callcenter["response"]["command"] = "agent_list";
			$callcenter["response"]["result"] = $r;
			$callcenter["response"]["status"] = 'ok';
			$callcenter["response"]["status_type"] = '';
		}
		else
		{
			$callcenter["response"]["agent"] = $params["agent"];
			$callcenter["response"]["command"] = "agent_list";
			$callcenter["response"]["status"] = "error";
			$callcenter["response"]["status_type"] = "params_failed";
		}
	}
	else
	{
		$callcenter["response"]["agent"] = $params["agent"];
		$callcenter["response"]["command"] = "agent_list";
		$callcenter["response"]["status"] = "error";
		$callcenter["response"]["status_type"] = "authentication_failed";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($callcenter, true));
}

function byJson($request, $response, $args)
{
	$asJson = array();
	$asJson["app"] = "Teste";
	$asJson["description"] = "test";
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($asJson);
}

function sendZendeskNotification($request, $response, $args)
{
	error_log("sendZendeskNotification index");
	
	$headers = $request->getHeaders();
	
	$params = jsonConvert($request->getBody());
	
	error_log(json_encode($params, true));
	
	$valid = array('app', 'webhook', 'agent');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->sendZendeskNotification($params);
	}
	else
	{
		$zendesk = "400@00";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(200)->write(json_encode($zendesk, true));
}

function createTicketInbound($request, $response, $args)
{
	error_log("createTicketInbound");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->createTicketInbound($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}

	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function createTicketTransfer($request, $response, $args)
{
	error_log("createTicketTransfer");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('ticket_id');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->createTicketTransfer($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}

	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function createNotifyTicketAgent($request, $response, $args)
{
	error_log("createNotifyTicketAgent");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->createNotifyTicketAgent($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}

	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function createTicketCanceled($request, $response, $args)
{
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->createTicketCanceled($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}

	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function updateTicketInboundClosed($request, $response, $args)
{
	error_log("updateTicketInboundClosed");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->updateTicketInboundClosed($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}

	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function createTicketOutbound($request, $response, $args)
{
	error_log("createTicketOutbound");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');

	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->createTicketOutbound($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}
	
	return $response->write(json_encode($zendesk, true))
				    ->withHeader('Content-Type', 'application/json')
					->withStatus(201);
}

function updateTicketOutboundClosed($request, $response, $args)
{
	error_log("updateTicketOutboundClosed");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('app');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk["response"] = $db->updateTicketOutboundClosed($params);
	}
	else
	{
		$zendesk["response"] = "400@00";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(200)->write(json_encode($zendesk, true));
}

function searchTicket($request, $response, $args)
{
	error_log("searchTicket");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('ticket_id');

	if(verifyRequiredParams($valid, $params))
	{
		$db = new Zendesk();
		$zendesk = $db->searchTicket($params);
	}
	else
	{
		$zendesk = "";
	}
	
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($zendesk);
}

function responseAccessDenied($response)
{
	$result["response"] = "400@00";
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($result, true));
	return $response;
}

function responseInvalidParameters($response)
{
	$result["response"] = "401@00";
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(401)->write(json_encode($result, true));
}

function jsonConvert($params)
{
	$json = json_decode($params, true);
	
	if(is_array($json))
	{
		return $json;
	}
	else
	{
		return false;
	}
}

function isAuthorization($headers)
{
	if (isset($headers['HTTP_AUTHORIZATION']))
	{
		$db = new Core();
		
		list($username, $password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		
		$auth_user = null;
		$auth_pw = null;

		if (isset($_SERVER['PHP_AUTH_USER']))
		{
			$auth_user = $_SERVER['PHP_AUTH_USER'];
			$auth_pw = $_SERVER['PHP_AUTH_PW'];
		}
		elseif (isset($_SERVER['HTTP_AUTHORIZATION']))
		{
			if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic') === 0)
				list($auth_user, $auth_pw) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}
		
		if (is_null($auth_pw))
		{
			return false;
		}
		
		error_log("auth_pw " . $auth_pw);
		
		if ($db->isValidApiKey($auth_pw))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

function verifyRequiredParams($valid=array(), $args=array())
{
	$request_params = array();
	
	$request_params = array_keys($args);	
	$verify[] = array_diff($valid, $request_params);
	
	if((count($verify[0])) == 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function OctaCreateTicket($request, $response, $args)
{
	error_log("OctaCreateTicket");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('webhook','number','extension');

	if(verifyRequiredParams($valid, $params))
	{
		error_log('parametros validos');
		$db = new Octadesk();
		$octadesk = $db->createTicket($params);
	}
	else
	{
		
		$octadesk = "parametros invalidos";
	}
	
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($octadesk);
}

function OctaCreateTicketCanceled($request, $response, $args)
{
	error_log("OctaCreateTicketCanceled");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('webhook','number');

	if(verifyRequiredParams($valid, $params))
	{
		error_log('parametros validos');
		$db = new Octadesk();
		$octadesk = $db->createTicketCanceled($params);
	}
	else
	{
		
		$octadesk = "parametros invalidos";
	}
	
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($octadesk);
}

function OctaUpdateTicket($request, $response, $args)
{
	error_log("OctaUpdateTicket");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */

	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('webhook', 'number_ticket');

	if(verifyRequiredParams($valid, $params))
	{
		error_log('parametros validos');
		$db = new Octadesk();
		$octadesk = $db->updateTicketOctadesk($params);
	}
	else
	{
		
		$octadesk = "parametros invalidos";
	}
	
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($octadesk);
}

function OctaUploadAttachment($request, $response, $args)
{
	error_log("OctaUploadAttachment");
	
	/** get headers */

	$headers = $request->getHeaders();
	
	/** get params */
	
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('path_file', 'filename', 'webhook');

	if(verifyRequiredParams($valid, $params))
	{
		error_log('parametros validos');
		$db = new Octadesk();
		$octadesk = $db->uploadAttachment($params);
	}
	else
	{
		
		$octadesk = "parametros invalidos";
	}
	
	return $response->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8')->withJson($octadesk);
}

function setCall($request, $response, $args)
{
	error_log("Entrou aqui");
	
	/** get headers */

	$headers = $request->getHeaders();
	/** get params */
	error_log(json_encode($headers));
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('domain_uuid', 'extension', 'destination');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new CallCenter();
		$dialer["response"] = $db->setCallOld($params);
	}
	else
	{
		$dialer["response"] = "400@00";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(200)->write(json_encode($dialer, true));
}

function ctiGetInfo($request, $response, $args)
{
	error_log("Entrou aqui");
	
	/** get headers */

	$headers = $request->getHeaders();
	/** get params */
	$params = jsonConvert($request->getBody());
	
	/** Validate required fields */
	
	$valid = array('agent', 'domain_uuid');
	
	if(verifyRequiredParams($valid, $params))
	{
		$db = new Cti();
		$dialer["response"] = $db->getCtiDisplay($params);
	}
	else
	{
		$dialer["response"] = "400@00";
	}
	
	$response->withHeader('Content-type', 'application/json');
	$response->withStatus(200)->write(json_encode($dialer, true));
}

/**
	Run app
*/

$app->run();