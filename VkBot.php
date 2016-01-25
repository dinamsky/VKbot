<?php


class VkBot{

	const API_VERSION = '5.24';
	const METHOD_URL = 'https://api.vk.com/method/';

	private $access_token = "";
	private $scopes = [];	
    //Classes
	/** @var  VkAudios */
	private $audios;
	public function __construct($token = null){
		if(!is_null($token)){
			$this->access_token = $token;
		}
		$this->init_classes();
	}

	public function init_classes(){
		$this->audios = new VkAudios($this->access_token);
		//TODO: More Classes
	}


	/**
	 * @return null|string
	 */
	public function getAccessToken(){
		return $this->access_token;
	}

	/**
	 * @param null|string $access_token
	 */	
	public function setAccessToken($access_token){
		$this->access_token = $access_token;
	}

	/**
	 * @return array
	 */
	public function getScopes(){
		return $this->scopes;
	}

	/**
	 * @param array $scopes
	 */
	public function setScopes($scopes){
		$this->scopes = $scopes;
	}

	/**
	 * @param string $method - �����, http://vk.com/dev/methods
	 * @param array $vars
	 * @return array
	 */
	public function api($method = '', array $vars = array()){

		$vars['v'] = self::API_VERSION;
		$vars['access_token'] = $this->access_token;

		$params = http_build_query($vars);

		$url = $this->http_build_query($method, $params);

		return (array)$this->call($url);
	}

	/**
	 * @param $method
	 * @param string $params
	 * @return string
	 */	
	private function http_build_query($method, $params = ''){
		return  self::METHOD_URL . $method . '?' . $params;
	}

	/**
	 * @param string $url
	 * @return bool|mixed|string
	 * @throws VkException
	*/
	private function call($url = ''){
		if(function_exists('curl_init')) $json = $this->curl_post($url); else $json = file_get_contents($url);

		$json = json_decode($json, true);

		// Ошибки, которые могут возникнуть описаны тут: https://vk.com/dev/errors
		if(isset($json['error'])){
			if($json['error']['error_msg'] != "Captcha needed") {
				if (isset($json['error'], $json['error']['error_msg'], $json['error']['error_code'])) {
					throw new VkException($json['error']['error_msg'], $json['error']['error_code']);
				}
			}else{
				echo "Error detected! Need captcha! Captcha img: ".$json['error']['captcha_img'];
			}
		}

		if(isset($json['response'])) return $json['response'];

		return $json;
    }
		
	private function curl_post($url){

		if(!function_exists('curl_init')) return false;

		$param = parse_url($url);

		if( $curl = curl_init() ) {

			curl_setopt($curl, CURLOPT_URL, $param['scheme'].'://'.$param['host'].$param['path']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $param['query']);
			$out = curl_exec($curl);

			curl_close($curl);

			return $out;
		}

		return false;
	}

	public function getAudios(){
		return $this->audios;
	}

}