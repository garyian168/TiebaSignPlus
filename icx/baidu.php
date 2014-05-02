<?php
/*
 * baidu class
 * http://baiduclass.icx.me
 * Version 0.1.1
 * 
 * Copyright 2014, Cai Cai
 * Released under the MIT license
 */
 class baidu{
	protected $bduss='';
	protected $client=array();
	protected $formdata1=array();
	protected $formdata2=array();
	public function __construct($cookie,$client=NULL){
		$cookie=trim($cookie);
		if($stripos($cookie,'bduss=')===false&&$stripos($cookie,';')===false){
			$this->bduss=$cookie;
		}elseif($preg_match('/bduss\s?=\s?([^ ;]*)/i',$cookie,$matches)){
			$this->bduss=$matches[1];
		}else{
			throw new exception('illegal cookie');
		}
		$this->cookies='BDUSS='.$this->bduss.';';
		if(is_null($client)) $this->client=self::get_client();
	}
	protected function fetch($url,$mobile=true,$usecookie=true){
		$ch=curl_init("$url");
		if($mobile===true){
			$st_data=array(
				'stErrorNums'=>'0',
				'stMethod'=>'1',
				'stMode'=>'1',
				'stSize'=>rand(50,2000),
				'stTime'=>rand(50,500),
				'stTimesNum'=>'0'
			)
			$formdata=array('BDUSS'=>$this->bduss) + $this->client + $this->formdata1 + $st_data + $this->formdata2;
			$sign_str = '';
			foreach($formdata as $key=>$value) $sign_str.= $key.'='.$value;
			$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
			$formdata['sign'] = $sign;
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: BaiduTieba for Android 6.0.1', 'Content-Type: application/x-www-form-urlencoded', 'Accept-Encoding: gzip', 'Host: c.tieba.baidu.com', 'Connection: Keep-Alive'));
		}else{
			$formdata = $this->formdata1;
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:29.0) Gecko/20100101 Firefox/29.0','Connection: Keep-Alive'));
		}
		curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_POST,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($formdata));
		$res_json = curl_exec($ch);
		curl_close($ch);
		$res = @json_decode($res_json, true);
		return $res;
	}
	static function get_client($type=NULL,$version=NULL){
		$client=array(
			'id'=>'wappc_138'.self::random(10, true).'_'.self::random(3, true),
			'type'=>is_null($type)?rand(1,4):$type,
			'version'=>is_null($version)?'6.0.1':$version,
			'phone_imei'=>md5(self::random(16, true)),
			'cuid'=>strtoupper(md5(self::random(16))).'|'.self::random(15, true)
		)
		return $client;
	}
	static function random($length, $numeric = 0) {
		$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}
	public function tbs(){
		$this->formdata1=array();
		$result=$this->fetch('http://tieba.baidu.com/dc/common/imgtbs',false);
		if(!stripos($result,'is_login=1')) throw new exception('unusable cookie');
		return $result['data']['tbs'];
	}
	public function baidu_userinfo(){
		$result = $this->fetch('http://tieba.baidu.com/f/user/json_userinfo',false);
		return $result['data'];
	}
	public function login($un,$passwd,$vcode=NULL,$vcode_md5=NULL){
		$this->formdata1=array(
		
		)
		$this->formdata2=array(
		
		)
		$result = $this->fetch('http://c.tieba.baidu.com/c/s/login');
		return array(
		
		);
	}
	public function sign(){
	
	}
	public function post($content){
		
	}
 }