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
	protected $client=array{
		'id'=>'',
		'type'=>'',
		'version'=>'',
		'phone_imei'=>''
	}
	public function __construct($cookie){
		$cookie=trim($cookie);
		$cookielen=strlen($cookie);
		if($cookielen==192){
			$this->bduss=$cookie;
		}elseif($cookielen>=198){
			if($cookielen==198) $cookie=$cookie.";";
			if($preg_match('/BDUSS=(.*?);/',$cookie,$matches)&&strlen($matches[1])==192){
				$this->bduss=$cookie;
			}else{
				return false;
			};
		}else{
			return false;
		}
		$this->cookies='BDUSS='.$this->bduss.';';
	}
	protected function fetch($url,$usecookie=true){
		$ch=curl_init("$url");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'User-Agent: Mozilla/5.0 (SymbianOS/9.3; Series60/3.2 NokiaE72-1/021.021; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.1.16352'));
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		$array = array(
			'BDUSS' => $BDUSS,
			'_client_id' => $this->client['id'],
			'_client_type' => $this->client['type'],
			'_client_version' => $this->client['version'],
			'_phone_imei' => $this->client['phone_imei'],
			'fid' => $tieba['fid'],
			'kw' => urldecode($tieba['unicode_name']),
			'net_type' => '3',
			'tbs' => get_tbs($uid),
		);
		$sign_str = '';
		foreach($array as $k=>$v) $sign_str .= $k.'='.$v;
		$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
		$array['sign'] = $sign;
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
		$sign_json = curl_exec($ch);
		curl_close($ch);
		$res = @json_decode($sign_json, true);
	}
	protected function post($content){
	
	}
 }