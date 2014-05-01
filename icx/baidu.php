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
		
	}
 }