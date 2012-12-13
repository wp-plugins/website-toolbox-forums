<?php
/*
Purpose: To prepare url using curl or file handling and sent request to Website Toolbox forum.
*/
#Check php basic functions exist or not
function _checkBasicFunctions($functionList) {	
	$functions = split(",",$functionList);
	foreach ($functions as $key=>$val) {
		$function = trim($val);
		if (!function_exists($function)) {
			return false;
		}
	}
	return true;
}

#compare result after getting response from the Website Toolbox
function _checkSuccess($response) {
	$SUCCESS_STRING = "Registration Complete.";
	if (strstr($response, $SUCCESS_STRING)) {
		return true;
	} else if (strstr($response, "Error:")) {
		return false;
	}
}

#Create a request using curl or file and getting response from the Website Toolbox.
function doHTTPCall($URL,$HOST) {
	if(_checkBasicFunctions("curl_init,curl_setopt,curl_exec,curl_close")) {
		$ch = curl_init("http://".$HOST.$URL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch); 
		curl_close($ch);
	} else if(_checkBasicFunctions("fsockopen,fputs,feof,fread,fgets,fclose")) {
		$fsock = fsockopen($HOST, 80, $errno, $errstr, 30);
		if (!$fsock) {
			echo "Error! $errno - $errstr";
		} else {
			$headers .= "POST $URL HTTP/1.1\r\n";
			$headers .= "HOST: $HOST\r\n";
			$headers .= "Connection: close\r\n\r\n";
			fputs($fsock, $headers);
			// Needed to omit extra initial information
			$get_info = false;
			while (!feof($fsock)) {
				if ($get_info) {
					$response .= fread($fsock, 1024);
				} else {
					if (fgets($fsock, 1024) == "\r\n") {
						$get_info = true;
					}
				}
			}
		fclose($fsock);
		}// if
	}
	return trim($response);	
}
?>	