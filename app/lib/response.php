<?php
namespace App\Lib;

class Response
{
	public $result     = null;
	public $response   = false;
	public $message    = 'Unexpected error occurred.';
	public $href       = null;
	public $function   = null;
	
	public $filter     = null;
	
	public function SetResponse($response, $m = '')
	{
		$this->response = $response;
		$this->message = $m;

		if(!$response && $m = '') $this->response = 'Unexpected error occurred';
	}
}