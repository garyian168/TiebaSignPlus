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
	protected $tbs='';
	protected $bduss='';
	protected $cookies='';
	protected $client=array();
	protected $formdata=array();
	protected $last_formdata=array();
	protected $use_z_lib=FALSE;
	public function __construct($cookie=NULL,$client=NULL){
		if(!is_null($cookie)){
			$cookie=trim($cookie);
			if(stripos($cookie,'bduss=')===FALSE&&stripos($cookie,';')===FALSE){
				$this->bduss=$cookie;
			}elseif(preg_match('/bduss\s?=\s?([^ ;]*)/i',$cookie,$matches)){
				$this->bduss=$matches[1];
			}else{
				throw new exception('请输入合法的cookie');
			}
			$this->cookies='BDUSS='.$this->bduss.';';
		}
		if(is_null($client)) $this->client=self::get_client();
	}
	protected function fetch($url,$mobile=TRUE,$usecookie=TRUE){
		$ch=curl_init($url);
		if($mobile===TRUE){
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
			if($usecookie===TRUE){
				$this->formdata['BDUSS']= $this->bduss;
			}
			$this->formdata += $predata;
			$sign_str = '';
			foreach($this->formdata as $key=>$value) $sign_str.= $key.'='.$value;
			$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
			$this->formdata['sign'] = $sign;
			$http_header=array('User-Agent: BaiduTieba for Android 6.0.1', 'Content-Type: application/x-www-form-urlencoded', 'Host: c.tieba.baidu.com', 'Connection: Keep-Alive');
			if($this->use_z_lib===TRUE) $http_header[]='Accept-Encoding: gzip';
		}else{
			$http_header=array('User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:29.0) Gecko/20100101 Firefox/29.0','Connection: Keep-Alive');
			curl_setopt($ch,CURLOPT_COOKIE,$this->cookies);
		}
		curl_setopt($ch,CURLOPT_HTTPHEADER,$http_header);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_POST,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->formdata));
		$res_json = curl_exec($ch);
		curl_close($ch);
		if(empty($res_json)) throw new exception('网络连接失败');
		if($this->use_z_lib===TRUE) $res_json=gzdecode($res_json);
		$result = @json_decode($res_json, TRUE);
		if($mobile===TRUE&&!empty($result['anti']['tbs'])) $this->tbs=$result['anti']['tbs'];
		$this->last_formdata=$this->formdata;
		$this->formdata=array();
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
	protected function common_return($data){
		$data=array();
		$result=array();
		if(!isset($data['error_code'])){
			$data['error_code']=-1;
			$data['error_msg']="未知的错误代码";
		}elseif($data['error_code']==0){
			$data['error_msg']="执行成功";
		}elseif(!isset($data['error_msg'])){
			$data['error_msg']="未知错误,错误代码".$data['error_code'];
		}else{
			$data['error_msg'].=" error_code=".$data['error_code'];
		}
		$result['c']=$data['error_code'];
		$result['m']=$data['error_msg'];
		if(isset($data['icx'])) $result['icx']=$data['icx'];
		return $result;
	}

	public static function random($length, $numeric = FALSE) {
		$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}
	public static function get_client($type=NULL,$model=NULL,$version=NULL){
		$client=array(
				'_client_id'=>'wappc_138'.self::random(10, TRUE).'_'.self::random(3, TRUE),
				'_client_type'=>is_null($type)?rand(1,4):$type,
				'_client_version'=>is_null($version)?'6.0.1':$version,
				'_phone_imei'=>md5(self::random(16, TRUE)),
				'cuid'=>strtoupper(md5(self::random(16))).'|'.self::random(15, TRUE),
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
	public static function get_random_tid($tieba){
		$result=self::simple_fetch('http://tieba.baidu.com/f?kw='.urlencode(iconv('utf-8', 'gbk', $tieba)).'&fr=index');
		preg_match_all('/<li class="j_thread_list clearfix" data-field=\'{(.*?)}\'/', $result, $matches);
		foreach ($matches[1] as $jsontid){
			$jsontid=str_replace('&quot;','"', '{'.$jsontid.'}');
			$tids[]=json_decode($jsontid)->id;
		}
		$tid=$tids[array_rand($tids)];
		return $tid;
	}
	public static function get_fid($arg,$use_tid=TRUE){
		if($use_tid===TRUE){
			$result=self::simple_fetch('http://tieba.baidu.com/p/'.$arg);
		}else{
			$result=self::simple_fetch('http://tieba.baidu.com/f?kw='.urlencode(iconv("utf-8", "gbk", $arg)).'&fr=index');
		}
		preg_match('/"forum_id"\s?:\s?(\d+)/', $result, $matches);
		return $matches[1];
	}
	public function tbs(){
		if(!empty($this->tbs)) return $this->tbs;
		$result=$this->fetch('http://tieba.baidu.com/dc/common/tbs',FALSE);
		if(array_key_exists('is_login',$result)===TRUE&&$result['is_login']===0) throw new exception(var_dump($result));
		return $result['tbs'];
	}
	public function userinfo(){
		$result = $this->fetch('http://tieba.baidu.com/f/user/json_userinfo',FALSE);
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
		$result = $this->fetch('http://c.tieba.baidu.com/c/s/login',TRUE,FALSE);
		$result['icx']=array(
			"need_vcode": $result['info']['need_vcode'],
			"vcode_md5": $result['info']['vcode_md5'],
			"vcode_type": $result['info']['vcode_type']
		);
		return $this->common_return($result);
	}
	public function sign($kw,$fid=NULL){
		if(is_null($fid)) $fid=self::get_fid($kw,FALSE);
		$this->formdata=array(
				'fid'=>$fid,
				'kw'=>$kw,
				'tbs'=>$this->tbs()
		);
		$result = $this->fetch('http://c.tieba.baidu.com/c/c/forum/sign');
		return array(
				'result'=>TRUE
		);
	}
	public function post($kw,$fid=NULL,$tid=NULL,$content=NULL){
		if(is_null($fid)) $fid=self::get_fid($kw,FALSE);
		if(is_null($tid)) $tid=self::get_random_tid($kw);
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
		$result['icx']=array(
			"need_vcode": $result['info']['need_vcode'],
			"vcode_md5": $result['info']['vcode_md5'],
			"vcode_type": $result['info']['vcode_type']
		);
		
		return $this->common_return($result);
		//(5=>"需要输入验证码"),(7=>"您的操作太频繁了！"),(8=>"您已经被封禁")
	}
	public function zan(){
		
	}
}