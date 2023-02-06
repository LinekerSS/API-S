<?php

class Core
{
    protected $conn;

    function __construct() 
	{
        require_once __DIR__ . '/ConnectMySQL.php';
        $db = new ConnectMySQL();
        $this->conn = $db->connect();
    }

    /**
     * Validating platform api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key platform api key
     * @return boolean
     */
	 
    public function isValidApiKey($api_key)
	{
		$stmt = $this->conn->prepare("SELECT contact_url FROM v_contacts WHERE contact_url = ?");
		$stmt->bindParam(1, $api_key, PDO::PARAM_STR);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$num_rows = $stmt->rowCount();
        return $num_rows;
    }
	
	/**
		Generating uuid
    */
	
	public function uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

    /**
		Generating random Unique MD5 
		String for user Api key
    */
	 
    private function generateApiKey() 
	{
        return md5(uniqid(rand(), true));
    }
	
	/**
		formatNumber
    */
	
	public function formatNumber($str)
	{
		$pattern = '/[^0-9]/';
		$replacement = '';
		return preg_replace($pattern, $replacement, $str);
	}
	
	/**
		validate date
    */
	
	function validateDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	
	public function verifyRequiredParams($valid=array(), $args=array())
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
	
	public function event_socket_create()
	{
		$fp = fsockopen("127.0.0.1", "8021", $errno, $errdesc, 3);
		socket_set_blocking($fp,false);

		if (!$fp)
		{

		}
		else
		{
			while (!feof($fp))
			{
				$buffer = fgets($fp, 1024);
				usleep(100);
				if (trim($buffer) == "Content-Type: auth/request")
				{
					 fputs($fp, "auth ClueCon\n\n");
					 break;
				}
			}
			return $fp;
		}
	}
	
	public function event_socket_request($fp, $cmd)
	{
		if ($fp)
		{
			fputs($fp, $cmd."\n\n");
			usleep(100);

			$response = "";
			$i = 0;
			$contentlength = 0;
			
			while (!feof($fp))
			{
				$buffer = fgets($fp, 4096);
				
				if ($contentlength > 0)
				{
					$response .= $buffer;
				}

				if ($contentlength == 0)
				{
					if (strlen(trim($buffer)) > 0)
					{
						$temparray = explode(":", trim($buffer));
						if ($temparray[0] == "Content-Length")
						{
							$contentlength = trim($temparray[1]);
						}
					}
				}

				usleep(20);

				if ($i > 1000000)
				{ 
					break;
				}

				if ($contentlength > 0)
				{
					if (strlen($response) >= $contentlength)
					{
						break;
					}
				}
				$i++;
			}
			return $response;
		}
		else
		{
			echo "no handle";
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
				if($x > 0)
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
}
?>
