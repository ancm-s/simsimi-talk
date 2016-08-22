<?php

/*
* simsimi.php
* @link https://github.com/ancm-s/simsimi-talk
* @license MIT
*/

$sim = new SimsimI;
extract($_GET);
if(isset($chatMsg)){
	$sim->_Chat($chatMsg);
	$sim->_TTS();
	exit();
}
class SimsimI{
	public 	$_api,
			$_response;
	public function __construct(){
		$this->_api = 'http://newapp.simsimi.com/v1/simsimi/talkset?isFilter=[FILTER]&uid=[UID]&os=a&tz=Hanoi&message_sentence=[MESSAGE]&lc=vi&cc=CL&av=6.7.1.7';
	}
	public function _Chat($msg, $filter = 1, $uid = ''){
		$uid = preg_replace('/([a-zA-Z]+)/', '', md5($_SERVER['REMOTE_ADDR']));
		$build_api = str_replace(array('[FILTER]', '[UID]', '[MESSAGE]'), array($filter, $uid, urlencode($msg)), $this->_api);
		//print $build_api;
		$this->_response = $this->_cURL($build_api);
		//print_r($this->_response);
		return $this->_response;
	}
	public function _TTS(){
		//https://translate.google.com/translate_tts?ie=UTF-8&q="ahihi%20đồ%20chó"&tl=vi&total=1&idx=0&textlen=16&tk=155643.287890&client=t&prev=input
		if($this->_response){
			
			if(isset($this->_response->error)){
				/*print_r($this->_response);
				exit();*/
				$text = 'lỗi rồi';
			}
			else	$text = $this->_response->simsimi_talk_set->answers[array_rand($this->_response->simsimi_talk_set->answers)]->sentence;
		}else{
			$text = 'không thấy gì cả';
		}
		$_googleTTS = 'https://translate.google.com/translate_tts?ie=UTF-8&q='.urlencode($text).'&tl=vi&total=1&idx=0&textlen='.strlen($text).'&tk=155643.287890&client=tw-ob&prev=input';
		header('cache-control:private, max-age=86400');
		header('content-length:'.strlen(file_get_contents($_googleTTS)));
		header('content-type:audio/mpeg');
		header('status:200');
		header('x-content-type-options:nosniff');
		//print $this->_cURL($_googleTTS, false, false);
		readfile($_googleTTS);
		exit();
	}
	protected function _cURL($url, $postArray = false, $json = true){
		$s = curl_init();
		$opts = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36 Vivaldi/1.2.490.43"
		);
		if($postArray){
			$opts[CURLOPT_POST] = true;
			$opts[CURLOPT_POSTFIELDS] = $postArray;
		}
		curl_setopt_array($s, $opts);
		$return = curl_exec($s);
		//print_r(curl_getinfo($s));
		curl_close($s);
		
		return $json?json_decode($return):$return;
	}
}
?>
<!DOCTYPE html>
<html lang="vi-vn">
	<head>
		<meta charset="utf-8" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.js"></script>
	</head>
	<body>
		<form method="POST" action="javascript: void(0)">
			<p><label for="msg">Chat Message</label></p>
			<input type="text" id="msg" value="xin chào" />
			<p><button>Chat with me !!</button></p>
		</form>
		<script type="text/javascript">
		$('form:first').submit(AnCMS => new Audio('?chatMsg='+encodeURI($('#msg').val())).play());
		</script>
	</body>
</html>
