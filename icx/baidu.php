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
	protected $cookies='';
	protected $client=array();
	protected $formdata=array();
	public function __construct($cookie,$client=NULL){
		$cookie=trim($cookie);
		if(stripos($cookie,'bduss=')===false&&stripos($cookie,';')===false){
			$this->bduss=$cookie;
		}elseif(preg_match('/bduss\s?=\s?([^ ;]*)/i',$cookie,$matches)){
			$this->bduss=$matches[1];
		}else{
			throw new exception('illegal cookie');
		}
		$this->cookies='BDUSS='.$this->bduss.';';
		if(is_null($client)) $this->client=self::get_client();
	}
	protected function fetch($url,$mobile=true,$usecookie=true){
		$ch=curl_init($url);
		if($mobile===true){
			$common_data=array(
					'from'=>'baidu_appstore',
					'stErrorNums'=>'0',
					'stMethod'=>'1',
					'stMode'=>'1',
					'stSize'=>rand(50,2000),
					'stTime'=>rand(50,500),
					'stTimesNum'=>'0',
					'timestamp'=>time().self::random(3,TRUE)
			);
			$predata=$this->client+$this->formdata+$common_data;
			ksort($predata);
			$this->formdata=array();
			if($usecookie===true){
				$this->formdata['BDUSS']= $this->bduss;
			}
			$this->formdata += $predata;
			$sign_str = '';
			foreach($this->formdata as $key=>$value) $sign_str.= $key.'='.$value;
			$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
			$this->formdata['sign'] = $sign;
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: BaiduTieba for Android 6.0.1', 'Content-Type: application/x-www-form-urlencoded', 'Host: c.tieba.baidu.com', 'Connection: Keep-Alive'));
		}else{
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:29.0) Gecko/20100101 Firefox/29.0','Connection: Keep-Alive'));
			curl_setopt($ch,CURLOPT_COOKIE,$this->cookies);
		}
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_POST,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->formdata));
		$res_json = curl_exec($ch);
		curl_close($ch);
		$result = @json_decode($res_json, true);
		if(empty($result)) throw new exception($res_json);
		return $result;
	}
	protected function simple_fetch($url){
		$ch = curl_init ($url);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:29.0) Gecko/20100101 Firefox/29.0','Connection: Keep-Alive'));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$content=curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	static function random($length, $numeric = FALSE) {
		$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}
	static function get_client($type=NULL,$model=NULL,$version=NULL){
		$client=array(
				'_client_id'=>'wappc_138'.self::random(10, true).'_'.self::random(3, true),
				'_client_type'=>is_null($type)?rand(1,4):$type,
				'_client_version'=>is_null($version)?'6.0.1':$version,
				'_phone_imei'=>md5(self::random(16, true)),
				'cuid'=>strtoupper(md5(self::random(16))).'|'.self::random(15, true),
				'model'=>is_null($model)?'M1':$model
		);
		return $client;
	}
	static function get_content(){
	$text=<<<EOF
第一次的爱，始终无法轻描淡写。
我对你，只有放弃，没有忘记。
站在心碎的地方，轻轻打一个结，一种缝补，阻止伤痛再流出。
在这个城市，做一道路过的风景，做一次匆匆的过客，只为了一个人。
也许有一天，你回头了，而我却早已，不在那个路口。
EOF;
	$contents=explode("\n", $text);
	$content=$contents[array_rand($contents)];
	return $content;
	}
	static function get_rand_tid($tieba){
		'http://tieba.baidu.com/f?kw='.urlencode(iconv('utf-8', 'gbk', $tieba)).'&fr=index'
		preg_match_all('/<li class="j_thread_list clearfix" data-field=\'{(?<json>.*?)}\'/', $contents, $jsontids);
		foreach ($jsontids['json'] as $jsontid){
			$jsontid=str_replace('&quot;','"', '{'.$jsontid.'}');
			$tids[]=json_decode($jsontid)->id;
		}
		$tid=$tids[array_rand($tids)];
		return $tid;
	}
	public function tbs(){
		$this->formdata=array();
		$result=$this->fetch('http://tieba.baidu.com/dc/common/tbs',false);
		if(array_key_exists('is_login',$result)===TRUE&&$result['is_login']===0) throw new exception(var_dump($result));
		return $result['tbs'];
	}
	public function userinfo(){
		$result = $this->fetch('http://tieba.baidu.com/f/user/json_userinfo',false);
		return $result['data'];
	}
	public function login($un,$passwd,$vcode=NULL,$vcode_md5=NULL){
		$this->formdata=array(
				'isphone'=>'0',
				'passwd'=>$passwd,
				'un'=>$un
		);
		if(!is_null($vcode)&&!is_null($vcode_md5)){
			$vcode_data=array(
					'vcode'=>$vcode,
					'vcode_md5'=>$vcode_md5
			);
			$this->formdata += $vcode_data;
		}
		$result = $this->fetch('http://c.tieba.baidu.com/c/s/login',true,false);
		return array(

		);
	}
	public function sign($fid,$kw){
		$this->formdata=array(
				'fid'=>$fid,
				'kw'=>$kw,
				'tbs'=>$this->tbs()
		);
		$result = $this->fetch('http://c.tieba.baidu.com/c/c/forum/sign');
		return array(
				'result'=>true
		);
	}
	public function post($fid,$tid,$kw,$content=NULL){
		if(is_null($content)) $content=self::get_content();
		$this->formdata=array(
				'fid'=>$fid,
				'tid'=>$tid,
				'kw'=>$kw,
				'content'=>$content,
				'tbs'=>$this->tbs(),
				'is_ad'=>'0',
				'new_vcode'=>'1',
				'anonymous'=>'1',
				'vcode_tag'=>'11'
				
		);
		$result = $this->fetch('http://c.tieba.baidu.com/c/c/post/add');
		if($result['error_code']===0) $result ['error_code']=2;
		return array($result['error_code'],serialize($result));
	}
}