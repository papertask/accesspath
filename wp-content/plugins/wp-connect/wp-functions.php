<?php
/**
 * 自定义函数
 */
if (!function_exists('mb_substr')) {
	function mb_substr($str, $start = 0, $length = 0, $encode = 'utf-8') {
		$encode_len = ($encode == 'utf-8') ? 3 : 2;
		for($byteStart = $i = 0; $i < $start; ++$i) {
			$byteStart += ord($str{$byteStart}) < 128 ? 1 : $encode_len;
			if ($str{$byteStart} == '') return '';
		} 
		for($i = 0, $byteLen = $byteStart; $i < $length; ++$i)
		$byteLen += ord($str{$byteLen}) < 128 ? 1 : $encode_len;
		return substr($str, $byteStart, $byteLen - $byteStart);
	} 
} 
if (!function_exists('mb_strlen')) {
	function mb_strlen($str, $encode = 'utf-8') {
		return ($encode == 'utf-8') ? strlen(utf8_decode($str)) : strlen($str);
	} 
} 
if (!function_exists('mb_strimwidth')) {
	function mb_strimwidth($str, $start, $width, $trimmarker, $encode = 'utf-8') {
		return mb_substr($str, $start, $width, $encode) . $trimmarker;
	} 
} 
/*
// 使用键名比较计算数组的差集 array_diff_key  < 5.1.0
if (!function_exists('array_diff_key')) {
	function array_diff_key() {
		$arrs = func_get_args();
		$result = array_shift($arrs);
		foreach ($arrs as $array) {
			foreach ($result as $key => $v) {
				if (array_key_exists($key, $array)) {
					unset($result[$key]);
				} 
			} 
		} 
		return $result;
	} 
} 
// 根据键名、键值对比,得到数组的差集 array_diff_assoc  < 4.3.0
if (!function_exists('array_diff_assoc')) {
	function array_diff_assoc($a1, $a2) {
		foreach($a1 as $key => $value) {
			if (isset($a2[$key])) {
				if ((string) $value !== (string) $a2[$key]) {
					$r[$key] = $value;
				} 
			} else {
				$r[$key] = $value;
			} 
		} 
		return $r;
	} 
} 
// 使用键名比较计算数组的交集 array_intersect_key  < 5.1.0
if (!function_exists('array_intersect_key')) {
	function array_intersect_key($isec, $keys) {
		$argc = func_num_args();
		if ($argc > 2) {
			for ($i = 1; !empty($isec) && $i < $argc; $i++) {
				$arr = func_get_arg($i);
				foreach (array_keys($isec) as $key) {
					if (!isset($arr[$key])) {
						unset($isec[$key]);
					} 
				} 
			} 
			return $isec;
		} else {
			$res = array();
			foreach (array_keys($isec) as $key) {
				if (isset($keys[$key])) {
					$res[$key] = $isec[$key];
				} 
			} 
			return $res;
		} 
	} 
} 
// 从数组中取出一段，保留键值 array_slice  < 5.0.2
if (!function_exists('php_array_slice')) {
	function php_array_slice($array, $offset, $length = null, $preserve_keys = false) {
		if (!$preserve_keys || version_compare(PHP_VERSION, '5.0.1', '>')) {
			return array_slice($array, $offset, $length, $preserve_keys);
		} 
		if (!is_array($array)) {
			user_error('The first argument should be an array', E_USER_WARNING);
			return;
		} 
		$keys = array_slice(array_keys($array), $offset, $length);
		$ret = array();
		foreach ($keys as $key) {
			$ret[$key] = $array[$key];
		} 
		return $ret;
	} 
} 
*/
if (!function_exists('parse_url_detail')) {
	function parse_url_detail($url) {
		$parts = parse_url($url);
		if (isset($parts['query'])) {
			parse_str(urldecode($parts['query']), $str);
		} 
		return $str;
	} 
} 
// 删除数组值的首尾空格
if (!function_exists('__array_trim')) {
	function __array_trim($str) {
		if (is_array($str)) {
			$ret = array();
			foreach ($str as $k => $v) {
				$ret[$k] = __array_trim($v);
			} 
		} else {
			$ret = trim($str);
		} 
		return $ret;
	} 
} 
// json_encode能显示中文
if (!function_exists('json_encode_zh_cn')) {
	function json_encode_zh_cn($var) {
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			return json_encode($var, JSON_UNESCAPED_UNICODE);
		} else {
			return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($var));
		} 
	} 
} 
// 过滤emoji
if (!function_exists('__filter_emoji')) {
	function __filter_emoji($str) {
		$str = preg_replace_callback('/./u', '__filter_emoji_callback', $str);
		return $str;
	} 
	function __filter_emoji_callback($match) {
		return strlen($match[0]) >= 4 ? '' : $match[0];
	} 
} 

// 获取本地文件数据
if (!function_exists('__get_file_data')) {
	function __get_file_data($file) {
		if (function_exists('fopen')) {
			$fp = fopen($file, 'r');
			$file_data = fread($fp, filesize($file));
			fclose($fp);
			return $file_data;
		} elseif (function_exists('file_get_contents')) {
			return file_get_contents($file);
		}
	} 
} 
// 含有中文的url使用urlencode
if (!function_exists('url_utf8_zh_cn')) {
	function url_utf8_zh_cn($url) {
		if (strlen($url) == mb_strlen(urldecode($url), 'utf-8')) {
			return $url;
		} else {
			return urlencode($url);
		}
	} 
}
// 字符长度(一个汉字代表一个字符，两个字母代表一个字符)
if (!function_exists('wp_strlen')) {
	function wp_strlen($text) {
		if (!$text) return 0;
		$a = mb_strlen($text, 'utf-8');
		$b = strlen($text);
		$c = $b / 3 ;
		$d = ($a + $b) / 4;
		if ($a == $b) { // 纯英文、符号、数字
			return $b / 2;
		} elseif ($a == $c) { // 纯中文
			return $a;
		} elseif ($a != $c) { // 混合
			return $d;
		} 
	} 
} 
// 截取字数
if (!function_exists('wp_status')) {
	function wp_status($content, $url, $length, $num = '') {
		if ($num) {
			$temp_length = (wp_strlen($content)) + (wp_strlen($url));
		} else {
			$temp_length = (mb_strlen($content, 'utf-8')) + (mb_strlen($url, 'utf-8'));
		}
		if ($url) {
			$url = ' ' . $url;
		} 
		if ($temp_length > $length) {
			$chars = $length - 3 - mb_strlen($url, 'utf-8'); // '...'
			if ($num) {
				$chars = $length - wp_strlen($url);
				$str = mb_substr($content, 0, $chars, 'utf-8');
				preg_match_all("/([\x{0000}-\x{00FF}]){1}/u", $str, $half_width); // 半角字符
				$chars = $chars + count($half_width[0]) / 2 - 1.5; // '...'
			} 
			$content = mb_substr($content, 0, (int)$chars, 'utf-8');
			$content = $content . "...";
		} 
		$status = $content . $url;
		return trim($status);
	} 
} 

if (!function_exists('wp_urlencode')) {
	function wp_urlencode($url) {
		$a = array('+', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
		$b = array(" ", "!", "*", "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
		$url = str_replace($a, $b, urlencode($url));
		return $url;
	} 
} 
// 过滤html
if (!function_exists('wp_replace')) {
	function wp_replace($str) {
		$a = array('&#160;', '&#038;', '&#39;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&ldquo;', '&rdquo;', '&nbsp;', 'Posted by Wordmobi');
		$b = array(' ', '&', "'", '-', '‘', '’', '“', '”', '&', '<', '>', '“', '”', ' ', '');
		$str = str_replace($a, $b, strip_tags($str));
		return trim($str);
	} 
} 
// 过滤简码和html
if (!function_exists('wp_trim_replace')) {
	function wp_trim_replace($str) {
		return wp_replace(strip_shortcodes($str));
	} 
} 
// 过滤简码和html,换行
if (!function_exists('wp_wrap_replace')) {
	function wp_wrap_replace($str) {
		return str_replace(array("\r\n", "\r", "\n"), '', wp_replace(strip_shortcodes($str)));
	} 
} 
// 保留部分简码
if (!function_exists('_strip_shortcodes')) {
	function _strip_shortcodes($str) {
		$pattern = "/\[caption\s(.*?)\](.*?)\[\/caption\]/i";
		$str = preg_replace($pattern, '\\2', $str);
		return strip_shortcodes($str);
	} 
} 
// 只取图片url，去掉图片a链接，文本a链接保留
if (!function_exists('html2imgurl_a')) {
	function html2imgurl_a($str) {
		$str = preg_replace('/>\s*<img/i', " tmpaimg=1><img", $str);
		$str = preg_replace('/<a[^>]+tmpaimg=1><img[^>]+src=[\'"](http[^\'"]+)[\'"].*><\/a>/isU', " $1 ", $str);
		$str = preg_replace('/<[img|embed][^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', " $1 ", $str);
		$str = str_replace(' tmpaimg=1', '', $str);
		$str = preg_replace('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/i', "<a href=\"$1\">$2</a>", $str);
		$a = array('&#160;', '&#038;', '&#39;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&ldquo;', '&rdquo;', '&nbsp;');
		$b = array(' ', '&', "'", '-', '‘', '’', '“', '”', '&', '<', '>', '“', '”', ' ');
		$str = str_replace($a, $b, strip_tags($str, '<a>'));
		return trim($str);
	} 
} 
if (!function_exists('class_http')) {
	function close_curl() {
		if (!extension_loaded('curl')) {
			return " <span style=\"color:blue\">请在php.ini中打开扩展extension=php_curl.dll</span>";
		} else {
			$func_str = '';
			if (!function_exists('curl_init')) {
				$func_str .= "curl_init() ";
			} 
			if (!function_exists('curl_setopt')) {
				$func_str .= "curl_setopt() ";
			} 
			if (!function_exists('curl_exec')) {
				$func_str .= "curl_exec()";
			} 
			if ($func_str)
				return " <span style=\"color:blue\">不支持 $func_str 等函数，请在php.ini里面的disable_functions中删除这些函数的禁用！</span>";
		} 
	} 
	// SSL
	function http_ssl($url) {
		$arrURL = parse_url($url);
		$r['ssl'] = $arrURL['scheme'] == 'https' || $arrURL['scheme'] == 'ssl';
		$is_ssl = isset($r['ssl']) && $r['ssl'];
		if ($is_ssl && !extension_loaded('openssl'))
			return wp_die('您的主机不支持openssl，请查看<a href="' . WP_CONNECT_URL . '/check.php" target="_blank">环境检查</a>');
	} 
	function class_http($url, $params = array()) {
		if ($params['http']) {
			$class = 'WP_Http_' . ucfirst($params['http']);
		} else {
			if (!close_curl()) {
				global $wp_version; 
				// $class = 'WP_Http_Curl';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, ($params['user-agent']) ? $params['user-agent'] : 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'));
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_TIMEOUT, ($params['timeout']) ? (int)$params['timeout'] : 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ($params['sslverify']) ? $params['sslverify'] : false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($params['sslverify'] === true) ? 2 : false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				if ($params['referer']) {
					curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
				} 
				switch ($params['method']) {
					case 'POST':
						curl_setopt($ch, CURLOPT_POST, true);
						if (!empty($params['body'])) {
							curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
						} 
						break;
					case 'DELETE':
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
						if (!empty($params['body'])) {
							$url = $url . $params['body'];
						} 
				} 
				if (!empty($params['headers'])) {
					$headers = array();
					foreach ($params['headers'] as $k => $v) {
						$headers[] = "{$k}: $v";
					} 
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				} 
				curl_setopt($ch, CURLINFO_HEADER_OUT, true);
				curl_setopt($ch, CURLOPT_URL, $url);
				$response = curl_exec($ch);
				curl_close ($ch);
				return $response;
			} else {
				http_ssl($url);
				if (@ini_get('allow_url_fopen') && function_exists('fopen')) {
					$class = 'WP_Http_Streams';
				} elseif (function_exists('fsockopen')) {
					$class = 'WP_Http_Fsockopen';
				} else {
					return wp_die('没有可以完成请求的 HTTP 传输器，请查看<a href="' . WP_CONNECT_URL . '/check.php" target="_blank">环境检查</a>');
				} 
			} 
		} 
		$http = new $class;
		$response = $http -> request($url, $params);
		if (!is_array($response)) {
			if ($params['method'] == 'GET' && @ini_get('allow_url_fopen') && function_exists('file_get_contents')) {
				return file_get_contents($url . '?' . $params['body']);
			} 
			$errors = $response -> errors;
			$error = $errors['http_request_failed'][0];
			if (!$error)
				$error = $errors['http_failure'][0];
			if ($error == "couldn't connect to host" || strpos($error, 'timed out') !== false) {
				return;
			} 
			wp_die('出错了: ' . $error . '<br /><br />可能是您的主机不支持，请查看<a href="' . WP_CONNECT_URL . '/check.php" target="_blank">环境检查</a>');
		} 
		return $response['body'];
	} 
} 
if (!function_exists('get_remote_contents')) {
	function get_remote_contents($url, $timeout = 30, $referer = '', $useragent = '') {
		if (!close_curl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout ? $timeout : 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			if ($referer) {
				curl_setopt($ch, CURLOPT_REFERER, $referer);
			} 
			if ($useragent) {
				curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
			}
			$content = curl_exec($ch);
			curl_close($ch);
			return $content;
		} else {
			$params = array();
			if (@ini_get('allow_url_fopen')) {
				if (function_exists('file_get_contents')) {
					return file_get_contents($url);
				} 
				if (function_exists('fopen')) {
					$params['http'] = 'streams';
				} 
			} elseif (function_exists('fsockopen')) {
				$params['http'] = 'fsockopen';
			} else {
				return wp_die('没有可以完成请求的 HTTP 传输器，请查看<a href="' . WP_CONNECT_URL . '/check.php" target="_blank">环境检查</a>');
			} 
			$params += array("method" => 'GET',
				"timeout" => $timeout,
				"sslverify" => false
				);
			if ($useragent) $params['user-agent'] = $useragent;
			return class_http($url, $params);
		} 
	} 
} 
/*
function close_socket() {
	if (function_exists('fsockopen')) {
		$fp = 'fsockopen()';
	} elseif (function_exists('pfsockopen')) {
		$fp = 'pfsockopen()';
	} elseif (function_exists('stream_socket_client')) {
		$fp = 'stream_socket_client()';
	} 
	if (!$fp) {
		return " <span style=\"color:blue\">必须支持以下函数中的其中一个： fsockopen() 或者 pfsockopen() 或者 stream_socket_client() 函数，请在php.ini里面的disable_functions中删除这些函数的禁用！</span>";
	} 
} 

function sfsockopen($host, $port, $errno, $errstr, $timeout) {
	if (function_exists('fsockopen')) {
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
	} elseif (function_exists('pfsockopen')) {
		$fp = @pfsockopen($host, $port, $errno, $errstr, $timeout);
	} elseif (function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($host . ':' . $port, $errno, $errstr, $timeout);
	} 
	return $fp;
} 
*/
if (!function_exists('key_authcode')) {
	function key_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = ($key) ? md5($key) : '';
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), - $ckey_length)) : '';

		$cryptkey = $keya . md5($keya . $keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		} 

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		} 

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		} 

		if ($operation == 'DECODE') {
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			} 
		} else {
			return $keyc . str_replace('=', '', base64_encode($result));
		} 
	} 
} 
// 旧版本
if (!function_exists('key_encode')) {
	function key_encode($string, $expiry = 0) {
		return key_authcode($string, 'ENCODE', 'WP-CONNECT', $expiry);
	} 
	function key_decode($string) {
		return key_authcode($string, 'DECODE', 'WP-CONNECT');
	} 
} 
if (!function_exists('base64encode')) {
	function base64encode($string) {
		return str_replace('=', '', base64_encode($string));
	} 
} 
// 删除数组的某一个键值
if (!function_exists('unset_array_value')) {
	function unset_array_value($value, $array, $preserve_keys = true) {
		if (is_array($array)) {
			$key = array_search($value, $array);
			if ($key !== false) {
				unset($array[$key]);
				if (!$preserve_keys) {
					$array = array_values($array);
				}
			} 
		} 
		return $array;
	} 
} 
if (!function_exists('filter_value')) {
	function filter_value($v) { // array_filter $callback
		if (is_array($v)) $v = $v[0];
		if ($v !== "") {
			return true;
		} 
		return false;
	} 
} 

if (!function_exists('wp_in_array')) {
	function wp_in_array($a, $b) {
		$arrayA = explode(',', rtrim($a, ','));
		$arrayB = explode(',', rtrim($b, ','));
		if (array_intersect($arrayA, $arrayB)) {
			return true;
		} 
		return false;
	} 
} 
// 设置默认值
if (!function_exists('default_values')) {
	function default_values($key, $vaule, $array) {
		if (!is_array($array)) {
			return true;
		} else {
			if ($array[$key] == $vaule || !array_key_exists($key, $array)) {
				return true;
			} 
		} 
	} 
} 
// 判断
if (!function_exists('ifabc')) {
	function ifab($a, $b) {
		return $a ? $a : $b;
	} 
	function ifb($a, $b) {
		return $a ? $b : '';
	} 
	function ifac($a, $b, $c) {
		return $a ? $a : ($b ? $c : '');
	} 
	function ifabc($a, $b, $c) {
		return $a ? $a : ($b ? $b : $c);
	} 
	function ifold($str, $old, $new) { // 以旧换新
		return (empty($str) || $str == $old) ? $new : $str;
	} 
} 
// 检测用户名，如果重复后面加随机数
if (!function_exists('rand_username')) {
	function rand_username($username) {
		return username_exists($username) ? rand_username($username . mt_rand(10, 99)) : $username;
	} 
}
function ifuser($username) {
	return rand_username($username);
} 

/**
 * 接口函数
 */
// Wordpress短网址
function get_link_short($id = 0) {
	if ( is_object($id)) {
		$post = $id;
	} else {
		$post = get_post($id);
	} 
	if ( empty($post->ID) )
		return false;

	if ( $post->post_type == 'post' ) {
		return home_url('?p=' . $post->ID);
	} elseif ( $post->post_type == 'page' ) {
		return home_url( '?page_id=' . $post->ID );
	} else {
		return get_permalink($post->ID);
	}
}
// 自定义短网址
function get_url_short($url) {
	global $wptm_options;
	if ($wptm_options['t_cn'] == 1) {
		$url = url_short_t_cn($url);
	} elseif ($wptm_options['t_cn'] == 2) {
		$url = url_short_dwz_cn($url);
	} 
	return $url;
} 
// 自定义URL V4.6.1
function get_url_format($url, $short = 1) {
	if (!$url) return '';
	global $wptm_options;
	if ($short) $url = url_short_t_cn($url);
	if ($wptm_options['format_url'] && strpos($wptm_options['format_url'], '%url%') !== false) {
		$url = str_replace('%url%', $url, $wptm_options['format_url']);
	}
	return $url;
} 
// 新浪t.cn短网址
function url_short_t_cn($long_url) {
	$api_url = 'http://api.weibo.com/2/short_url/shorten.json?source=8003029170&url_long=' . urlencode($long_url);
	$request = new WP_Http;
	$result = $request -> request($api_url, array("method" => 'GET', "timeout" => 0.2));
	if (is_array($result)) {
		$result = $result['body'];
		$result = json_decode($result, true);
		$result = $result['urls'];
		$url_short = $result[0]['url_short'];
		if ($url_short) $long_url = $url_short;
	} 
	return $long_url;
} 
// 兼容旧版
if (!function_exists('get_t_cn')) {
	function get_t_cn($long_url) {
		return url_short_t_cn($long_url);
	} 
} 
// 百度dwz.cn短网址
function url_short_dwz_cn($long_url) {
	$request = new WP_Http;
	$api_url = 'http://dwz.cn/create.php';
	$result = $request -> request($api_url , array('method' => 'POST', "timeout" => 0.2, 'body' => 'url=' . urlencode($long_url)));
	if (is_array($result)) {
		$result = $result['body'];
		$result = json_decode($result, true);
		$url_short = $result['tinyurl'];
		if ($url_short) $long_url = $url_short;
	} 
	return $long_url;
} 
// mycred 分享时，文章链接转为邀请链接
if (function_exists('mycred_plugin_activation')) {
	function _mycred_get_affiliate_link() {
		$user = wp_get_current_user();
		if ($user -> ID) {
			if (function_exists('mycred_get_option')) {
				$mycred = mycred_get_option('mycred_pref_hooks');
			} else {
				$mycred = get_option('mycred_pref_hooks');
			}
			if ($mycred && is_array($mycred['active']) && in_array('affiliate', $mycred['active'])) {
				$affiliate = $mycred['hook_prefs']['affiliate']['setup']['links'];
				if ($affiliate == 'username') {
					$ref = $user -> user_login;
				} elseif ($affiliate == 'numeric') {
					$ref = $user -> ID;
				} 
			} 
		} 
		return $ref;
	} 
	add_filter('wp_connect_post_link', '_mycred_share_link', 10, 2);
	function _mycred_share_link($url, $id) {
		if (strpos($url, 'mref=') === false) {
			$ref = _mycred_get_affiliate_link();
			if ($ref) {
				$url .= strpos($url, '?') ? '&' : '?';
				$url .= 'mref=' . $ref;
			}
		} 
		return $url;
	} 
} 

/**
 * 插件函数
 */
function wp_fail($s) {
	if (!defined('DOING_AJAX')) {
		wp_die($s);
		return;
	} 
	if (is_string($s)) {
		die(strip_tags($s));
	} else {
		$s;
		die;
	} 
} 
if (!function_exists('wp_mail_content_type_text_html')) {
	function wp_mail_content_type_text_html($var) {
		return 'text/html';
	} 
} 
// ajax时移除admin_init
if (!function_exists('remove_admin_init_on_ajax')) {
	function remove_admin_init_on_ajax() {
		global $pagenow;
		if ($pagenow == 'admin-ajax.php' && isset($_REQUEST['admininit'])) {
			remove_all_actions('admin_init');
		} 
	} 
	add_action('admin_init', 'remove_admin_init_on_ajax', 0);
} 
// media中文名
function media_cn($id = '') {
	$name = array('weixin' => '微信',
		'qzone' => 'QQ',
		'sina' => '新浪微博',
		'qq' => '腾讯微博',
		'taobao' => '淘宝网',
		'alipay' => '支付宝',
		'douban' => '豆瓣',
		'renren' => '人人网',
		'kaixin001' => '开心网',
		'facebook' => 'Facebook',
		'twitter' => 'Twitter',
		'google' => 'Google',
		'yahoo' => 'Yahoo',
		'linkedin' => 'LinkedIn',
		'github' => 'GitHub',
		'baidu' => '百度',
		'360' => '360',
		'tianya' => '天涯微博',
		'yixin' => '易信',
		'msn' => 'Microsoft',
		// 'sohu' => '搜狐微博', 
		// 'netease' => '网易微博',
		);
	if ($id) {
		if ($name[$id]) {
			return $name[$id];
		} 
	} else {
		return $name;
	} 
} 
// 支持同步的SNS
function media_sync() {
	$name = array('sina' => '新浪微博',
		"qq" => "腾讯微博",
		"douban" => "豆瓣",
		"renren" => "人人网",
		"kaixin001" => "开心网",
		"tianya" => "天涯微博",
		"facebook" => "Facebook",
		"twitter" => "Twitter",
		"linkedin" => "LinkedIn",
		"yixin" => "易信朋友圈",
		// "sohu" => "搜狐微博",
		// "netease" => "网易微博",
		);
	return $name;
} 
// 临时邮箱
function media_email($id = '') {
	$name = array('weixin' => '@weixin.qq.com',
		'qzone' => '@qzone.qq.com',
		'sina' => '@weibo.com',
		'qq' => '@t.qq.com',
		'renren' => '@renren.com',
		'kaixin001' => '@kaixin001.com',
		'douban' => '@douban.com',
		'taobao' => '@taobao.com',
		'alipay' => '@alipay.com',
		'sohu' => '@t.sohu.com', 
		'netease' => '@t.163.com',
		'baidu' => '@baidu.com',
		'360' => '@360.cn',
		'tianya' => '@tianya.cn',
		'yixin' => '@yixin.im',
		'twitter' => '@twitter.com',
		'facebook' => '@facebook.com',
		'github' => '@github.com',
		'linkedin' => '@linkedin.com',
		'google' => '@googleapis.com',
		'yahoo' => '@yahooapis.com',
		// 'msn' => ''
		);
	if ($id) {
		if ($name[$id]) {
			return $name[$id];
		} 
	} else {
		return $name;
	} 
} 
// media URL
function media_url($media, $uid) {
	if (!$uid) return;
	switch ($media) {
		case "sina":
			$url = 'http://weibo.com/u/' . $uid;
			break;
		case "qq":
			$url = 'http://t.qq.com/' . $uid;
			break;
		case "renren":
			$url = 'http://www.renren.com/profile.do?id=' . $uid;
			break;
		case "kaixin001":
			$url = 'http://www.kaixin001.com/home/?uid=' . $uid;
			break;
		case "douban":
			$url = 'http://www.douban.com/people/' . $uid;
			break;
		case "sohu":
			$url = 'http://t.sohu.com/u/' . $uid;
			break;
		case "tianya":
			$url = 'http://my.tianya.cn/' . $uid;
			break;
		case "twitter":
			$url = 'http://twitter.com/' . $uid;
			break;
		case "facebook":
			$url = 'https://www.facebook.com/app_scoped_user_id/' . $uid;
			break;
		case "google":
			$url = 'https://plus.google.com/' . $uid;
			break;
		default:
	} 
	return $url;
} 
// media avatar
function media_avatar($media, $uid, $size = 0) {
	if (!$uid) return;
	switch ($media) {
		case "sina":
			$avatar = (is_ssl() ? 'https://i' . rand(1, 3) . '.wp.com/tp2' : 'http://tp' . rand(1, 4)) . '.sinaimg.cn/' . $uid . '/50/0/1';
			break;
		case "qzone":
			$size = $size > 64 ? 100 : 40;
			$avatar = strpos($uid, '://') ? str_replace('http:', 'https:', $uid) : 'https://thirdqq.qlogo.cn/qqapp/' . $uid;
			$avatar .= '/' . $size;
			$avatar = str_replace('/40/' . $size, '/' . $size, $avatar);
			break;
		case "qq":
			$size = $size > 64 ? 100 : 50;
			$avatar = strpos($uid, '://') ? str_replace('http:', 'https:', $uid) : 'https://app.qlogo.cn/mbloghead/' . $uid;
			$avatar .= '/' . $size;
			$avatar = str_replace('/100/' . $size, '/' . $size, $avatar);
			break;
		case "douban":
			$avatar = 'https://img3.doubanio.com/icon/u' . $uid . '-1.jpg';
			break;
		case "baidu":
			$avatar = 'https://himg.bdimg.com/sys/portraitn/item/' . $uid . '.jpg';
			break;
		case "tianya":
			$avatar = (is_ssl() ? 'https://i' . rand(1, 3) . '.wp.com/' : 'http://') . 'tx.tianyaui.com/logo/small/' . $uid;
			break;
		case "github":
			$avatar = 'https://avatars.githubusercontent.com/u/' . $uid . '?v=3';
			break;
		default:
	} 
	if (!$avatar) {
		$avatar = $uid;
	} 
	return $avatar;
} 
// 微博头像
if (!function_exists('get_weibo_head')) {
	function get_weibo_head($comment, $size, $email, $author_url, $show_url = false) {
		$tname = array('@weibo.com' => 'sina',
			'@t.qq.com' => 'qq',
			'@douban.com' => 'douban',
			'@qzone.qq.com' => 'qzone',
			'@tianya.cn' => 'tianya',
			'@github.com' => 'github',
			'@t.sina.com.cn' => 'sina'
			);
		$tmail = strstr($email, '@');
		if ($media = $tname[$tmail]) {
			$weibo_uid = str_replace($tmail, '', $email);
			if ($media == 'qq') {
				if ($comment && $comment->comment_author_IP == '' && $comment->comment_agent && strpos($comment->comment_agent, 'head_t_qq') === 0) {
					//$head = str_replace('head_t_qq_', '', $comment->comment_agent);
					$head = ltrim($comment->comment_agent, 'head_t_qq_');
					if ($head) {
						$out = 'https://app.qlogo.cn/mbloghead/' . $head . '/50';
					} else {
						$out = 'https://mat1.gtimg.com/www/mb/img/p1/head_normal_50.png';
					} 
				} 
			} else {
				$out = media_avatar($media, $weibo_uid, $size);
			}
			if ($out) {
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
				if ($show_url) {
					if (!$author_url) $author_url = media_url($media, $weibo_uid);
					if ($author_url) {
						$avatar = "<a href='{$author_url}' rel='nofollow' target='_blank'>$avatar</a>";
					} 
				}
				return $avatar;
			} 
		} 
	} 
}
// 评论头像，用于显示回推回来的头像
function wp_connect_comments_avatar($avatar, $id_or_email = '', $size = '32') {
	if (is_object($id_or_email)) {
		$email = ifab($id_or_email->comment_author_email, $id_or_email->user_email);
		if ($avatar1 = get_weibo_head($id_or_email, $size, $email, '')) {
			return $avatar1;
		} 
	} 
	return $avatar;
}
function get_weibo_info($name) {
	switch ($name) {
		case "sina":
		case "scid":
			return array('sina', '@weibo.com', 'http://weibo.com/');
		case "qq":
		case "qcid":
			return array('qq', '@t.qq.com', 'http://t.qq.com/');
		default:
	} 
} 
// 性别
function wp_connect_sex($sex) {
	$sex = strtolower($sex);
	switch ($sex) {
		case "m":
		case "male":
		case "男":
			return 1;
		case "f":
		case "female":
		case "女":
			return 2;
		default:
	} 
	return 0;
} 
// 省份
function wp_connect_province() {
	return array(11 => '北京', 12 => '天津', 13 => '河北', 14 => '山西', 15 => '内蒙古', 21 => '辽宁', 22 => '吉林', 23 => '黑龙江', 31 => '上海', 32 => '江苏', 33 => '浙江', 34 => '安徽', 35 => '福建', 36 => '江西', 37 => '山东', 41 => '河南', 42 => '湖北', 43 => '湖南', 44 => '广东', 45 => '广西', 46 => '海南', 50 => '重庆', 51 => '四川', 52 => '贵州', 53 => '云南', 54 => '西藏', 61 => '陕西', 62 => '甘肃', 63 => '青海', 64 => '宁夏', 65 => '新疆', 71 => '台湾', 81 => '香港', 82 => '澳门');
} 
function wp_connect_css($file = '', $deps = array()) {
	$id = str_replace('/', '-', rtrim($file, '.css'));
	wp_register_style('wp-connect-' . $id, WP_CONNECT_URL . '/' . $file, $deps, WP_CONNECT_VERSION);
	wp_print_styles('wp-connect-' . $id);
} 
function wp_connect_js($file = '', $deps = array()) {
	$id = str_replace('/', '-', rtrim($file, '.js'));
	wp_register_script('wp-connect-' . $id, WP_CONNECT_URL . '/' . $file, $deps, WP_CONNECT_VERSION);
	wp_print_scripts('wp-connect-' . $id);
} 
// debug
function wp_connect_dev($content, $name, $params = '', $media = '') {
	if (defined('WP_CONNECT_DEBUG') && $content) {
		if (is_array($content) && ($content['error'] || $content['error_code'] || $content['request'] || $content['errcode'] || $content['ret'] || $content['errors'] || $content['errorMsg'] || $content['message'])) {
			$type = 'error_';
		} elseif (defined('WP_CONNECT_DEVELOPER')) {
			$type = 'dev_';
		} 
		if ($type) {
			if ($params) {
				if (is_array($params)) $params = json_encode_zh_cn($params);
				$params .= "\r\n";
			}
			$start = "<?php exit;?>\r\n### " . gmdate('Y-m-d H:i:s', current_time('timestamp')) . ' ';
			if ($media) {
				$start .= $name . ' ' . $_SERVER['HTTP_REFERER'] . "\r\n" . $params;
				@file_put_contents(WP_CONNECT_PATH . '/debug/' . $type . $media . '.txt', $start . var_export($content, 1), FILE_APPEND);
			} else {
				$start .= $_SERVER['HTTP_REFERER'] . "\r\n" . $params;
				@file_put_contents(WP_CONNECT_PATH . '/debug/' . $type . $name . '.txt', $start . var_export($content, 1), FILE_APPEND);
			} 
		} 
	} 
} 
// debug-error
function wp_connect_error($content, $name, $params = '', $media = '') {
	if (defined('WP_CONNECT_DEBUG') && $content) {
		$type = 'error_';
		if ($params) {
			if (is_array($params)) $params = json_encode_zh_cn($params);
			$params .= "\r\n";
		} 
		$start = "<?php exit;?>\r\n### " . gmdate('Y-m-d H:i:s', current_time('timestamp')) . ' ';
		if ($media) {
			$start .= $name . ' ' . $_SERVER['HTTP_REFERER'] . "\r\n" . $params;
			@file_put_contents(WP_CONNECT_PATH . '/debug/' . $type . $media . '.txt', $start . var_export($content, 1), FILE_APPEND);
		} else {
			$start .= $_SERVER['HTTP_REFERER'] . "\r\n" . $params;
			@file_put_contents(WP_CONNECT_PATH . '/debug/' . $type . $name . '.txt', $start . var_export($content, 1), FILE_APPEND);
		} 
	} 
} 
// 设置cookie
function wp_connect_set_cookie($name, $value, $expiry = 0) {
	$name = 'wp_connect_' . $name;
	$_SESSION[$name] = $value;
	if (is_array($value)) {
		$value = json_encode($value);
		$expiry = 900;
	} 
	setcookie($name . COOKIEHASH, key_authcode($value, 'ENCODE', WP_CONNECT_KEY, $expiry), null, COOKIEPATH, COOKIE_DOMAIN);
} 
// 清空cookie
function wp_connect_clear_cookie($name) {
	$name = 'wp_connect_' . $name;
	if (isset($_SESSION[$name])) {
		unset($_SESSION[$name]);
	}
	if (isset( $_COOKIE[$name . COOKIEHASH] )) {
		setcookie($name . COOKIEHASH, '', time() - 36000, COOKIEPATH, COOKIE_DOMAIN);
	}
} 
// 获取cookie
function wp_connect_get_cookie($name) {
	$name = 'wp_connect_' . $name;
	//$cookie = $_SESSION[$name];
	if (!$cookie) {
		if (isset( $_COOKIE[$name . COOKIEHASH] )) {
			$cookie = key_authcode($_COOKIE[$name . COOKIEHASH], 'DECODE', WP_CONNECT_KEY);
			$first = substr($cookie, 0, 1);
			if ($first == '{' || $first == '[') {
				$cookie = json_decode($cookie, true);
			}
		}
	}
	return $cookie;
} 
// 后台table
if (!function_exists('wp_admin_add_table')) {
function wp_admin_add_table($data, $field, $name, $edit_url) {
	$thead_tr = "<tr><th scope='col' style='width: 2.2em;'>序号</th><th scope='col' style='width: 12em;'><span>$name</span></th><th scope='col'>数量(个)</span></th></tr>";
	foreach ((array) $data as $key => $row) {
		$key = $key + 1;
		$class = $key%2==0 ? "":" class='alternate'";
		$title = $row['zh'] ? $row['zh'] : $row[$field];
		if (!$title) $title = '未知';
		$list .= "<tr{$class}><th scope='row'>{$key}</th><td>{$title}<div class='row-actions'><span class='edit'><a href='{$edit_url}&k={$field}&v={$row[$field]}' target='_blank'>显示全部</a></span></div></td><td>{$row['num']}</td></tr>";
	} 
?>
<table class="wp-list-table widefat fixed" cellspacing="0">
  <thead>
	<?php echo $thead_tr;?>
  </thead>
  <tfoot>
	<?php echo $thead_tr;?>
  </tfoot>
  <tbody id="the-list">
	<?php echo ($list) ? $list : '<tr class="no-items"><td class="colspanchange" colspan="3">'.__("No items found.").'</td></tr>';?>
  </tbody>
</table>
<?php
}
}
// wp-list-table 统计数量
if (!function_exists('show_count')) {
	function show_count($number, $html=0) {
		$number = $number ? number_format_i18n($number) : 0;
		return $html ? ' <span class="count">(<span>' . $number . '</span>)</span>' : $number;
	}
}
// 判断微信客户端
function is_weixin_client() {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return true;
	} 
	return false;
} 
// 判断IE内核
function is_ie_client() {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
		return true;
	} 
	return false;
} 
function _wptm() {
	global $wpdb;
	$wpdb -> query("DELETE FROM $wpdb->options WHERE option_name like 'wptm_%'");
}
if (!function_exists('get_default_timezone')) {
	function get_default_timezone() {
		$current_offset = get_option('gmt_offset');
		$tzstring = get_option('timezone_string');
		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( false !== strpos($tzstring,'Etc/GMT') )
			$tzstring = '';

		if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
			if ( 0 == $current_offset )
				$tzstring = 'Etc/GMT+0';
			elseif ($current_offset < 0)
				$tzstring = 'Etc/GMT+' . abs($current_offset);
			else
				$tzstring = 'Etc/GMT-' . $current_offset;
		}
		date_default_timezone_set($tzstring);
	}
}
function custom_comment_theme($echo=0) {
	global $wptm_connect;
	if ($wptm_connect['comment_custom'] == 1) {
		$dir = get_stylesheet_directory();
		if (file_exists($dir . '/comment/functions.php')) {
			if ($echo == 1) {
				return $dir; // 路径
			} else {
				return get_stylesheet_directory_uri(); // 网址
			}
		}
	}
	if ($echo == 1) {
		return WP_PLUGIN_DIR . "/wp-connect"; // 路径
	} else {
		return plugins_url('wp-connect'); // 网址
	}
} 
// 模版输出前处理
add_action('template_redirect', 'wp_connect_template_redirect');
function wp_connect_template_redirect() {
	global $wptm_connect;
	if ($wptm_connect['regid'] && is_page($wptm_connect['regid'])) { // 社交帐号注册强制填写用户信息
		if (is_user_logged_in()) {
			header('Location:' . admin_url('profile.php'));
			die();
		} elseif (isset($_POST['wp_connect_add_reg'])) {
			global $errors;
			$user = wp_connect_get_cookie("user");
			$errors = wp_connect_add_reg($user);
		}
		remove_all_filters('the_content');
		add_action('the_content', 'wp_connect_add_reg_info');
	} elseif ($wptm_connect['weixin'] && $wptm_connect['wx_login'] && is_weixin_client()) { // 微信强制登录
		nocache_headers();
		if (!is_user_logged_in()) {
			$schema = is_ssl() ? 'https://' : 'http://';
			$url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = wp_connect_login_url('weixin', '&redirect_url=' . urlencode($url));
			header('Location:' . $url);
			die();
		} 
	} 
} 
// 检查key
if (!function_exists('check_openkey')) {
	function check_openkey($apikey) {
		if (!$apikey[0] || !$apikey[1]) {
			if ($_GET['act'] == 'blog') {
				$weizhi = "同步博客-><a href='" . admin_url('admin.php?page=wp-connect#tab_menu-5-blog') . "'>帐号绑定</a>";
			} else {
				$weizhi = "<a href='" . admin_url('admin.php?page=wp-connect#tab_menu-11-key') . "'>自定义key</a>";
			} 
			wp_die("请先在 WordPress连接微博 插件里面的 $weizhi 页面填写申请的Key [ <a href='javascript:onclick=history.go(-1)'>返回</a> ]");
		} 
	} 
} 
// 如果没有自定义key，选择哪一个线路授权登录？
function wp_connect_authorize_url($url, $media) {
	if (in_array($media, array('qq', 'douban', 'diandian', '360'))) {
		$url = 'http://smyx.sinaapp.com';
	} 
	return $url;
} 
add_action('wp_connect_authorize_url', 'wp_connect_authorize_url', 10, 2);
// 检查用户的临时邮箱，用于过滤
function wp_connect_check_email($mail) {
	if ($mail && in_array(strstr($mail, '@'), media_email() + array('@t.sina.com.cn'))) {
		return true;
	} 
} 
// 不发送邮件
add_filter('wp_mail', 'wp_mail_check_email', 1);
function wp_mail_check_email($data) {
	if (is_array($data) && wp_connect_check_email($data['to'])) {
		$data['to'] = '';
	} 
	return $data;
} 
// 获得user_id
if (!function_exists('get_user_by_meta_value')) {
	function get_user_by_meta_value($meta_key, $meta_value) {
		global $wpdb;
		$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
		return $wpdb -> get_var($wpdb -> prepare($sql, $meta_key, $meta_value));
	} 
} 
// 保存wp_comments表某个字段
if (!function_exists('wp_update_comment_key')) {
	function wp_update_comment_key($comment_ID, $comment_key, $vaule) {
		global $wpdb;
		$$comment_key = $vaule;
		$result = $wpdb -> update($wpdb -> comments, compact($comment_key), compact('comment_ID'));
		return $result;
	} 
} 
// 获取当前登录的user ID
if (!function_exists('get_current_user_id')) {
	function get_current_user_id() {
		if (! function_exists('wp_get_current_user'))
			return 0;
		$user = wp_get_current_user();
		return (isset($user -> ID) ? (int) $user -> ID : 0);
	} 
} 
// 根据user ID获取用户信息
if (!function_exists('get_user_by_user_id')) {
	function get_user_by_user_id($user_id) {
		if (function_exists('get_userdata')) {
			return get_userdata($user_id);
		} 
		$userdata = WP_User :: get_data_by('id', $user_id);
		if (!$userdata)
			return false;
		$user = new WP_User;
		$user -> init($userdata);
		return $user;
	} 
} 
// 通过用户ID，获得用户某个字段
function get_user_by_uid($user_id, $field = '') {
	if (empty($field)) {
		return get_user_by_user_id($user_id);
	} 
	global $wpdb;

	if (!is_numeric($user_id))
		return false;

	$user_id = absint($user_id);
	if (!$user_id)
		return false;

	$user = wp_cache_get($user_id, 'users');

	if ($user) {
		return array($field => $user -> $field);
	} 

	if (!$user = $wpdb -> get_row("SELECT $field FROM $wpdb->users WHERE ID = $user_id LIMIT 1", ARRAY_A))
		return false;
	return $user;
} 
 // 通过用户ID，获得用户名
function get_username($uid) {
	$user = get_user_by_uid($uid, 'user_login');
	return $user['user_login'];
} 
 // 通过用户ID，获得用户邮箱
function get_useremail($uid) {
	$user = get_user_by_uid($uid, 'user_email');
	return $user['user_email'];
} 
// 通过用户ID，修改用户邮箱 v4.0
function wp_update_user_email($uid, $email) {
	global $wpdb;
	$uid = (int)$uid;
	if ($uid && is_email($email)) {
		$wpdb->update($wpdb->users, array('user_email' => $email), array('ID' => $uid));
	}
} 
// 根据链接或者用户ID
if (!function_exists('get_uid_by_url')) {
	function get_uid_by_url($url) {
		if (is_user_logged_in()) {
			if (strpos($url, 'user_id=') && current_user_can('manage_options')) { // 用户
				$parse_str = parse_url_detail($url);
				$wpuid = $parse_str['user_id'];
			}
			if (!$wpuid) {
				$wpuid = get_current_user_id();
			}
		} 
		return $wpuid;
	} 
} 
// 判断是否启用了某个插件
if (!function_exists('is_plugin_activate')) {
	function is_plugin_activate($plugin) {
		return in_array($plugin, (array) get_option('active_plugins', array())) || is_plugin_activate_for_network($plugin);
	} 
} 
// 判断是否在"整个网络"启用了某个插件
if (!function_exists('is_plugin_activate_for_network')) {
	function is_plugin_activate_for_network($plugin) {
		if (!is_multisite())
			return false;
		$plugins = get_site_option('active_sitewide_plugins');
		if (isset($plugins[$plugin]))
			return true;
		return false;
	} 
} 
// 根据某个标签截取文章
function get_post_extended($post, $tag) {
	if ( preg_match("/<!--$tag(.*?)?-->/", $post, $matches) ) {
		list($main, $extended) = explode($matches[0], $post, 2);
		$more_text = $matches[1];
	} else {
		$main = $post;
		$extended = '';
		$more_text = '';
	}

	// Strip leading and trailing whitespace
	$main = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $main);
	$extended = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $extended);
	$more_text = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $more_text);

	return array( 'main' => $main, 'extended' => $extended, 'more_text' => $more_text );
}
// 支持中文用户名
if ($wptm_connect['chinese_username']) {
	if (!function_exists('wp_sanitize_user_chinese_username')) {
		function wp_sanitize_user_chinese_username($username, $raw_username, $strict) {
			if ($strict && $username != $raw_username) {
				$username = $raw_username;
				$username = wp_strip_all_tags($username);
				$username = remove_accents($username); 
				// Kill octets
				$username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
				$username = preg_replace('/&.+?;/', '', $username); // Kill entities
				// If strict, reduce to ASCII for max portability.
				$username = preg_replace('|[^a-z0-9 _.\-@\x80-\xff]|i', '', $username);

				$username = trim($username); 
				// Consolidate contiguous whitespace
				$username = preg_replace('|\s+|', ' ', $username);
			} 
			return $username;
		} 
		add_filter('sanitize_user', 'wp_sanitize_user_chinese_username', 10, 3);
	}
	if (!function_exists('wp_sanitize_title_chinese_nicename')) {
		function wp_sanitize_title_chinese_nicename($user_login) {
			remove_filter('sanitize_title', 'sanitize_title_with_dashes');
			return $user_login;
		} 
		add_filter('pre_user_login', 'wp_sanitize_title_chinese_nicename');
	} 
}
// 获取同步帐号及设置
function wp_connect_get_account($id) {
	global $wptm_options;
	if (is_object($id)) {
		$post = $id;
	} else {
		$post = get_post($id);
	} 
	$post_author_ID = $post -> post_author;
	$sync_set = array();
	if ($post_author_ID) {
		if ($wptm_options['multiple_authors']) {
			$wptm_profile = get_user_meta($post_author_ID, 'wptm_profile', true);
			if (!$wptm_profile) $wptm_profile = array();
			$sync_set['sync_option'] = $wptm_profile['sync_option'];
			$sync_set['new_prefix'] = $wptm_profile['new_prefix'];
			$sync_set['update_prefix'] = $wptm_profile['update_prefix'];
			$sync_set['update_days'] = $wptm_profile['update_days'] * 60 * 60 * 24;
			$sync_set['is_author'] = true;
			$sync_set['post_author_ID'] = $post_author_ID;
			$sync_set['comment_push'] = true;
			$sync_set['multiple_authors'] = true;
			$sync_set['sync_status'] = 2; // 1、OK，2、没有开启同步，3、没有绑定帐号
			if ($wptm_profile['sync_option']) {
				$sync_set['sync_status'] = 3;
				$account = wp_usermeta_account($post_author_ID);
			} 
		} 
		// 是否开启了多作者博客
		if ($account) {
			$sync_set['sync_status'] = 1;
		} else {
			$sync_set['sync_option'] = $wptm_options['sync_option'];
			$sync_set['new_prefix'] = $wptm_options['new_prefix'];
			$sync_set['update_prefix'] = $wptm_options['update_prefix'];
			$sync_set['update_days'] = $wptm_options['update_days'] * 60 * 60 * 24;
			$sync_set['is_author'] = false;
			$sync_set['post_author_ID'] = $post_author_ID;
			$sync_set['comment_push'] = true;
			if (!$wptm_options['sync_option']) {
				return array('sync' => $sync_set, 'account' => array());
			} 
			$account = wp_option_account();
		} 
		// 是否绑定了帐号
		if (!$account) {
			return array('sync' => $sync_set, 'account' => array());
		} 
		if ($_POST['sync_account'] && is_array($_POST['sync_account'])) {
			$account = array_intersect_key($account, $_POST['sync_account']);
		} 
		return array('sync' => $sync_set, 'account' => $account);
	} 
} 
// 判断授权期限
function get_bind_expires_in($data, $html = 0, $before = ' (', $after = ')') {
	if (isset($data['name'])) {
		$name = $data['name'];
	}
	if ($data['expires_in']) {
		$expires = $data['expires_in'] - BJTIMESTAMP;
		if ($expires < 300) {
			if ($expires < 60) {
				$ret = '<span style="color:orange">' . $name . ' 授权已过期，请重新绑定</span>';
			} else {
				$ret = '<span style="color:red">' . $name . ' 授权即将过期！</span>';
			} 
		} else {
			$ret = $name;
			if ($html != 2)
			$ret .= ' 授权有效期至' . date('Y-m-d', $data['expires_in']);
		} 
	} elseif ($data['oauth_token']) {
		$ret = $name;
		if ($html != 2)
		$ret .= ' 授权长期有效';
	} 
	if ($ret) {
		if (!$html) {
			$ret = strip_tags($ret);
		} 
		return $before . $ret . $after;
	} 
} 
// 同步后的微博旧数据转换
function wp_connect_old_weibodata($weibodata) {
	$data = array();
	$data['sina']['id'] = $weibodata['sid'];
	$data['sina']['cid'] = $weibodata['scid'];
	$data['sina']['uid'] = $weibodata['suid'];
	$data['qq']['id'] = $weibodata['qid'];
	$data['qq']['cid'] = $weibodata['qcid'];
	$data['qq']['uid'] = $weibodata['quid'];
	$data['qq']['time'] = $weibodata['qctime'];
	$data['time'] = $weibodata['time'];
	return array_filter($data);
} 
// 从postmeta中获取图片
function get_picurl_by_postmeta($post_ID, $meta = '') {
	if (!$post_ID || !$meta) return '';
	$image = !empty($_POST[$meta]) ? $_POST[$meta] : get_post_meta($post_ID, $meta, true);
	if ($image)
		return $image;
} 
// 从简码中获取图片
function get_picurl_by_shortcode_tag($content, $tag = 'thumb') {
	if (!$content || !$tag) return '';
	preg_match('/' . $tag . '=[\'"](http[^\'"]+)[\'"].*/isU', $content, $image);
	if ($image[1])
		return $image[1];
} 
// 匹配视频,图片 v1.9.19 (v4.2)
function wp_multi_media_url($content, $post_ID = '') {
	global $wptm_options;
	$richMedia = apply_filters('wp_multi_media_url', '', $content, $post_ID);
	if (is_array($richMedia) && array_filter($richMedia)) {
		return $richMedia;
	} 
	if (!$wptm_options['disable_video'] && $content) {
		preg_match('/<embed[^>]+src=[\"\']{1}(([^\"\'\s]+)\.swf)[\"\']{1}[^>]+>/isU', $content, $video);
		if ($video[1]) {
			$v = $video[1];
		} else {
			$content1 = str_replace(array("[/", "</", '"', "'"), "\n", $content);
			preg_match('/http:\/\/(v.youku.com\/v_show|www.tudou.com\/(programs\/view|albumplay|listplay))+(?(?=[\/])(.*))/', $content1, $match);
			$v = trim($match[0]);
		} 
	}
	if ($wptm_options['enable_pic']) {
		if ($content) {
			preg_match('/<img[^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', $content, $image);
			$p = $image[1];
		}
		if (!$p) { // V4.2
			if ($wptm_options['pic_postmeta']) {
				$p = get_picurl_by_postmeta($post_ID, $wptm_options['pic_postmeta']);
			} elseif ($wptm_options['pic_tag']) {
				$p = get_picurl_by_shortcode_tag($content, $wptm_options['pic_tag']);
			}
			if ($p) {
				unset($wptm_options['thumbnail']);
			}
		}
		if (!$p || $wptm_options['thumbnail']) {
			if (is_numeric($post_ID) && function_exists('has_post_thumbnail') && has_post_thumbnail($post_ID)) { // 特色图像 WordPress v2.9.0
				if ($_img = get_the_post_thumbnail($post_ID, 'full')) { // 兼容部分自动生成特色图像的主题
					preg_match('/<img[^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', $_img, $img);
					$p = $img[1];
				}
			}
		} 
	} 
	if ($p || $v)
		return apply_filters('wp_get_multi_media_url', array($p, $v));
} 
// 得到图片url
if (!function_exists('get_image_by_content')) {
	function get_image_by_content($content, $post_ID = '', $size = 'full') { // thumbnail, medium, large or full
		$picurl = apply_filters('get_image_by_content', '', $content, $post_ID, $size);
		if ($picurl) {
			return $picurl;
		} 
		if ($post_ID) {
			if (is_numeric($post_ID) && function_exists('has_post_thumbnail') && has_post_thumbnail($post_ID)) { // 特色图像 WordPress v2.9.0
				if ($_img = get_the_post_thumbnail($post_ID, $size)) { // 兼容部分自动生成特色图像的主题
					preg_match('/<img[^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', $_img, $img);
					$picurl = $img[1];
				}
			} 
		} 
		if (!$picurl && $content) {
			preg_match('/<img[^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', $content, $image);
			$picurl = $image[1];
		} 
		return $picurl;
	} 
} 
// 根据内容获取图片，视频url
function get_image_by_post($content, $post_ID = '') {
	$v = apply_filters('get_image_by_post', '', $content, $post_ID);
	if (is_array($v)) {
		return $v;
	} 
	if (!$content) {
		return array();
	}
	preg_match_all('/<img[^>]+src=[\'"](http[^\'"]+)[\'"].*>/isU', $content, $image);
	$p_sum = count($image[1]);
	if ($p_sum > 0) {
		$img = $imgs = $image[1][0];
		$imgs = implode("|", array_slice($image[1], 0, 2));
	} 
	$content1 = str_replace(array("[/", "</", '"', "'"), "\n", $content);
	preg_match('/http:\/\/(v.youku.com\/v_show|www.tudou.com\/(programs\/view|albumplay|listplay))+(?(?=[\/])(.*))/', $content1, $match);
	if ($match[0]) {
		$v = trim($match[0]);
	} else {
		preg_match('/<embed[^>]+src=[\"\']{1}(([^\"\'\s]+)\.swf)[\"\']{1}[^>]+>/isU', $content, $video);
		$v = $video[1];
	} 
	return array($img, $imgs, $v);
} 
function kses_img($str) {
	$str = preg_replace('/<img[^>]+src=[\'"](http:\/\/[^\'"]+)[\'"].*>/isU', "<image src='$1' />", stripslashes($str));
	$str = preg_replace('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/isU', "", $str);
	return addslashes(str_replace('<image src=', '<img src=', $str));
} 


/**
 * add_filter('user_contactmethods', 'wp_connect_author_page');
 * function wp_connect_author_page($input) {
 * $input['imqq'] = 'QQ';
 * //$input['msn'] = 'MSN';
 * //unset($input['yim']);
 * //unset($input['aim']);
 * return $input;
 * }
 */

/**
 * 分享按钮
 */
// 社会化分享按钮，共54个
function wp_social_share_title() {
	$socialShare_title = array("qqconnect" => "QQ好友",
		"sina" => "新浪微博",
		"wechat" => "微信",
		"qzone" => "QQ空间",
		"douban" => "豆瓣",
		"youdao" => "有道书签",
		"renren" => "人人网",
		"kaixin001" => "开心网",
		"tieba" => "百度贴吧",
		"baidu" => "百度搜藏",
		"twitter" => "Twitter",
		"gplus" => "Google+",
		"facebook" => "Facebook",
		"linkedin" => "LinkedIn",
		"google" => "Google",
		"digg" => "Digg");
	return array_merge(apply_filters('socialShare_title', array()), $socialShare_title);
} 
// 分享设置 V1.1 (V1.6.7)
function wp_social_share_options() {
	$wptm_share = get_option('wptm_share');
	$social = wp_social_share_title();
	$options = array_keys($social);
	$select = ($wptm_share['select']) ? explode(",", $wptm_share['select']) : array_slice($options, 0, 15);
	$options = array_merge(array_intersect($select, $options), array_diff($options, $select));
	$selects = array_combine($select, $select);
	wp_connect_js('js/drag.js');
	foreach($options as $option) {
		echo "<li id=\"drag\"><input name=\"share_{$option}\" id=\"$option\" type=\"checkbox\" value=\"$option\"";
		echo ($selects[$option]) ? ' checked':'';
		echo " />$social[$option]</li>";
	} 
} 

/**
 * 旧数据处理 v4.0
 */
// 兼容其他插件
function wp_connect_other_plugin_4_0() {
	global $wpdb;
	// 数据备份 WordPress Database Backup
	if ($wp_cron_backup_tables = get_option('wp_cron_backup_tables')) {
		$prefix = $wpdb->prefix;
		$base_prefix = $wpdb->base_prefix;
		if (!in_array($prefix . 'connect_log', $wp_cron_backup_tables)) {
			$wp_cron_backup_tables[] = $prefix . 'connect_log';
			$backup = 1;
		}
		if (!in_array($base_prefix . 'connect_user', $wp_cron_backup_tables)) {
			$wp_cron_backup_tables[] = $base_prefix . 'connect_user';
			$backup = 1;
		}
		if ($backup) {
			update_option('wp_cron_backup_tables', $wp_cron_backup_tables);
		}
	}
} 
// 开放平台KEY v1.9.12
function get_appkey() {
	global $wpdb;
	$sohu = get_option('wptm_opensohu');
	// $netease = get_option('wptm_opennetease');
	return array('2' => array($wptm_connect['msn_api_key'], $wptm_connect['msn_secret']),
		'5' => array($sohu['app_key'], $sohu['secret']),
		//'6' => array($netease['app_key'], $netease['secret']),
		'7' => array($wptm_connect['renren_api_key'], $wptm_connect['renren_secret']),
		'8' => array($wptm_connect['kaixin001_api_key'], $wptm_connect['kaixin001_secret']),
		'13' => array($wptm_connect['qq_app_id'], $wptm_connect['qq_app_key']),
		'16' => array($wptm_connect['taobao_api_key'], $wptm_connect['taobao_secret']),
		'19' => array($wptm_connect['baidu_api_key'], $wptm_connect['baidu_secret'])
		);
} 
// 转换旧数据
function wp_connect_export_4_0($locktime, $last_id = 0, $number = 200) {
	if (!function_exists('wp_connect_tmp_update_users')) {
		@include_once(dirname(__FILE__) . '/modules/old.php');
	} 
	$lock = get_site_option('wp_connect_lock');
	if ($lock) {
		if ($last_id || ($locktime && $lock == $locktime)) {
			$wp_connect_lock = sprintf('%.22F', microtime(true));
			update_site_option('wp_connect_lock', $wp_connect_lock);
			$out = wp_connect_tmp_update_users($last_id, $number);
			wp_connect_dev($out, 'export');
			if (is_array($out)) {
				$count = (int)wp_connect_update_count_4_0($out['lastid']); // 剩余
				if ($count === 0) {
					$out = 'ok';
				} else {
					$out['count'] = $count;
					return json_encode($out);
				} 
			} 
			if ($out == 'ok') {
				delete_site_option('wp_connect_lock');
				update_site_option('wp_connect_export_data', WP_CONNECT_VERSION * 100000);
				wp_connect_tmp_update_date(current_time('mysql'));
				//wp_connect_tmp_update_sync();
				//wp_connect_tmp_update_options_sync();
				return '{"ret":1}';
			} 
		} 
	} else {
		return '{"ret":1}';
	} 
} 
// 需要升级的数据条数
function wp_connect_update_count_4_0($last_id = 0) {
	global $wpdb;
	$sql = "SELECT COUNT(1) FROM $wpdb->usermeta WHERE umeta_id > $last_id AND meta_key IN ('weixinid', 'wxunionid', 'qqid', 'stid', 'tqqid', 'renrenid', 'kaixinid', 'dtid', 'taobaoid', 'alipayid', 'sohuid', 'baiduid', 'guard360id', 'tytid', 'yixinid', 'twitterid', 'facebookid', 'linkedinid', 'msnid', 'login_weixin', 'login_wechat', 'login_qzone', 'login_sina', 'login_qq', 'login_renren', 'login_kaixin', 'login_douban', 'login_taobao', 'login_alipay', 'login_sohu', 'login_baidu', 'login_guard360', 'login_tianya', 'login_yixin', 'login_twitter', 'login_facebook', 'login_linkedin', 'login_msn')";
	return $wpdb -> get_var($sql); 
}
// 删除旧数据
function wp_connect_delete_4_1($locktime) {
	$lock = get_option('wp_connect_lock_time');
	if ($lock && $locktime && $lock == $locktime) {
		global $wpdb;
		// 头像
		$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('sina,', meta_value) WHERE meta_key = 'stid'");
		$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('douban,', meta_value) WHERE meta_key = 'dtid'");
		$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('tianya,', meta_value) WHERE meta_key = 'tytid'");
		// 只保留最后记录的一个头像
		$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'wp_connect_avatar' AND umeta_id NOT IN (SELECT a.umeta_id FROM (SELECT max(umeta_id) AS umeta_id FROM $wpdb->usermeta WHERE meta_key = 'wp_connect_avatar' group by user_id) AS a)");
		// 其他
		$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'last_login' AND meta_value LIKE '%id'");
		$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'login_name' AND meta_value LIKE '%a:%'");
		$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key IN ('weixinid', 'wxunionid', 'qqid', 'stid', 'tqqid', 'renrenid', 'kaixinid', 'dtid', 'taobaoid', 'alipayid', 'sohuid', 'neteaseid', 'baiduid', 'guard360id', 'tytid', 'yixinid', 'twitterid', 'facebookid', 'linkedinid', 'msnid', 'login_weixin', 'login_wechat', 'login_qzone', 'login_sina', 'login_qq', 'login_renren', 'login_kaixin', 'login_douban', 'login_taobao', 'login_alipay', 'login_sohu', 'login_baidu', 'login_guard360', 'login_tianya', 'login_yixin', 'login_twitter', 'login_facebook', 'login_linkedin', 'login_msn','rtid','ktid','wxtid','weixintid','ttid','qtid','qqtid','fbtid','shtid','tbtid','bdtid','guard360tid','yxtid','ntid')");
		$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key IN ('wptm_sina','wptm_qq','wptm_renren','wptm_kaixin001','wptm_sohu', 'wptm_twitter','wptm_netease','wptm_shuoshuo','wptm_qzone','wptm_fanfou','wptm_renjian','wptm_digu','wptm_facebook','wptm_linkedin','wptm_yixin','wptm_douban','wptm_tianya')");
		$wpdb -> query("DELETE FROM $wpdb->options WHERE option_name IN ('wptm_sina','wptm_qq','wptm_renren','wptm_kaixin001','wptm_sohu', 'wptm_twitter','wptm_netease','wptm_shuoshuo','wptm_qzone','wptm_fanfou','wptm_renjian','wptm_digu','wptm_facebook','wptm_linkedin','wptm_yixin','wptm_douban','wptm_tianya')");
		delete_site_option('wp_connect_export_number');
		delete_site_option('wp_connect_lock_time');
	}
} 
// ajax
function wp_connect_export_data_ajax() {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "connect_user";
	if (!wp_connect_user_install_sql()) {echo '数据库表【' . $table_name . '】不存在！请联系插件作者！';return;}
	$data = get_site_option('wp_connect_export_data');
	if ($data) {
		if (is_array($data)) {
			$lastid = $data['last_id'];
			$number = $data['number'];
		} else {
			return;
		}
	} ?>
<p>这是一次全新的开始。当初（2010-12-27）开发插件时想法还不够成熟，很多用户数据直接保存在默认的数据库表wp_usermeta，一旦用户多了，会导致该数据库表臃肿无比，对开发插件造成阻碍，更别说大数据分析了，纠结了一年多，终于下定决心完善插件，花了一个星期整理，把社交数据全部无缝地转移到新的数据库表 <?php echo $table_name;?>，可能会花您几分钟时间，但是绝对值得这么做！<strong>提醒：所有用户的同步微博帐号必须重新绑定！</strong></p>
<p><input class="button button-primary" type="button" id="exportConnect" value="点击这里将社交数据转移到新表:<?php echo $table_name;?>" /> <span id="exportStatus">(可能需要一些时间，请耐心等待！)</span></p>
<script type="text/javascript">
jQuery(function ($) {
  $("#exportConnect").click(function () {
    $(this).attr("disabled", true);
		exportConnect(<?php echo $lastid ? $lastid : 1;?>, <?php echo $number ? $number : 200;?>, 1);
  });
  function exportConnect(lastid, number, times) {
    var plugins_url = "<?php echo plugins_url('wp-connect');?>";
    $('#exportStatus').html('处理中...<img src="' + plugins_url + '/images/loading16.gif" />');
    $.post(plugins_url + "/modules/old.php", {
      lastid: lastid,
      number: number,
    }, function (data) {
      if (data.lastid) {
        exportConnect(data.lastid, data.number, 1);
        $('#exportStatus').html('还有 ' + data.count + ' 条数据 <img src="' + plugins_url + '/images/loading16.gif" />');
      } else if (data.ret === 1) {
		 alert('数据转移成功！');
		 window.location.reload();
        // $('#exportStatus').html('数据转移成功！');
        // $("#exportConnect").attr("disabled", false);
      } else {
		 if (times > 3) {
			 alert('出错了，即将进行刷新重试1！');
			 window.location.reload();
		 } else {
			 exportConnect(lastid, 200, times+1);
		 }
      }
    },'json').error(function() {
		 if (times > 3) {
			 alert('出错了，即将进行刷新重试2！');
			 window.location.reload();
		 } else {
			 exportConnect(lastid, 200, times+1);
		 }
	});
  }
});
</script>
<?php }

/**
 * 评论
 */
// 调用本地的最新评论 v2.4.4
if (!function_exists('wp_connect_recent_comments')) {
	function wp_connect_recent_comments($number, $avatar = '', $uids = '') { 
		global $wpdb, $comment;
		if ($uids) {
			$comments = $wpdb -> get_results("SELECT * FROM $wpdb->comments JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID WHERE user_id not in ($uids) AND comment_approved = '1' AND {$wpdb->posts}.post_status = 'publish' ORDER BY comment_date_gmt DESC LIMIT $number");
		} else {
			$comments = get_comments(array('number' => $number, 'status' => 'approve', 'post_status' => 'publish'));
		} 
		echo '<ul id="pl_recentcomments">';
		if (!$avatar) {
			foreach((array) $comments as $comment) {
				echo '<li>' . $comment -> comment_author . ': <a href="' . esc_url(get_comment_link($comment -> comment_ID)) . '">' . $comment -> comment_content . '</a></li>';
			} 
		} else {
			echo "<style type=\"text/css\" media=\"screen\">#pl_recentcomments li{margin:5px 0 0;display:block}#pl_recentcomments div{margin:0;padding:0}#pl_recentcomments .avatar{width:36px;height:36px;display:inline;float:left;margin-right:8px;border-radius:3px 3px 3px 3px;}#pl_recentcomments .rc-info, #pl_recentcomments .rc-content{overflow:hidden;text-overflow:ellipsis;-o-text-overflow:ellipsis;white-space:nowrap;}</style>";
			foreach((array) $comments as $comment) {
				echo '<li>' . get_c_avatar($comment, 36) . '<div class="rc-info"><a href="' . esc_url(get_comment_link($comment -> comment_ID)) . '">' . $comment -> comment_author . '</a></div><div class="rc-content">' . $comment -> comment_content . '&nbsp;</div></li>';
			} 
		} 
		echo '</ul>';
	} 
}

// 调用本地的热门评论，根据顶排列
if (!function_exists('wp_connect_top_comments')) {
	function wp_connect_top_comments($number, $avatar = '', $uids = '') { 
		global $wpdb, $comment;
		$comments = get_comments(array('number' => $number, 'status' => 'approve', 'orderby' => 'meta_value_num', 'meta_key' => 'rating_up', 'post_status' => 'publish'));
		echo '<ul id="pl_recentcomments">';
		if ($comments) {
			if (!$avatar) {
				foreach((array) $comments as $comment) {
					echo '<li>' . $comment -> comment_author . ': <a href="' . esc_url(get_comment_link($comment -> comment_ID)) . '">' . $comment -> comment_content . '</a></li>';
				} 
			} else {
				echo "<style type=\"text/css\" media=\"screen\">#pl_recentcomments li{margin:5px 0 0;display:block}#pl_recentcomments div{margin:0;padding:0}#pl_recentcomments .avatar{width:36px;height:36px;display:inline;float:left;margin-right:8px;border-radius:3px 3px 3px 3px;}#pl_recentcomments .rc-info, #pl_recentcomments .rc-content{overflow:hidden;text-overflow:ellipsis;-o-text-overflow:ellipsis;white-space:nowrap;}</style>";
				foreach((array) $comments as $comment) {
					echo '<li>' . get_c_avatar($comment, 36) . '<div class="rc-info"><a href="' . esc_url(get_comment_link($comment -> comment_ID)) . '">' . $comment -> comment_author . '</a></div><div class="rc-content">' . $comment -> comment_content . '&nbsp;</div></li>';
				} 
			} 
		} else {
			echo '<li>暂无相关评论，快去顶一条评论吧</li>';
		}
		echo '</ul>';
	} 
}
/**
 * 隐藏内容可见
 * 
 * @since 3.2.8
 */
// 是否已经评论（评论可见）
function is_has_comment($post_id = 0, $user_id = 0) {
	global $wpdb;
	if (!$post_id) $post_id = get_the_ID();
	if ($user_id) {
		$query = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = '$post_id' AND user_id = '$user_id' AND comment_approved= '1'";
		return $wpdb->get_var($query);
	} elseif ($email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH])) {
		$query = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = '$post_id' AND comment_author_email = '$email' AND comment_approved= '1'";
		return $wpdb->get_var($query);
	} 
} 
// 分享按钮
function show_share_button($id, $a) {
	global $wptm_share;
	$plugin_url = plugins_url('wp-connect');
	if ($a) {
		$a = 1;
		$plugin_url = apply_filters('wp_connect_plugin_url', $plugin_url, 'share');
	}
	if ($wptm_share['share']) {
		$account = array_intersect_key(media_sync(), $wptm_share['share']);
	} else {
		$account = array(
			'sina' => '新浪微博',
			'qq' => '腾讯微博',
			);
	}
	foreach($account as $key => $vaule) {
		$ret .= "<a rel=\"nofollow\" href=\"{$plugin_url}/share.php?share=" . base64encode(http_build_query(array('id' => $id, 's' => $key, 'a' => $a), '', '&')) . "\" title=\"{$vaule}\" class=\"shareButton_{$key}\">{$vaule}</a> ";
	} 
	return $ret;
} 
function view_hide_content($atts, $content = '') {
	if (!in_the_loop()) return '';
	global $post;
	$user_id = get_current_user_id(); 
	// 管理员和文章作者可见
	if ($user_id == 1 || ($user_id == $post->post_author)) {
		return show_hide_content('', '', '', $content);
	} 
	$post_id = get_the_ID();
	$post_url = get_permalink();
	$login = $reply = $share = '';
	if (!empty($atts['l'])) {
		if (!$user_id) {
			$login = '<a href="' . wp_login_url($post_url) . '" class="uc-login-button" rel="nofollow">登录</a>';
		} 
	} 
	if (!empty($atts['r'])) {
		if ($login || !is_has_comment($post_id, $user_id)) {
			$reply = '<a href="' . $post_url . '#respond" title="评论本文">评论本文</a>';
		} 
	} 
	if (!empty($atts['s'])) {
		if (isset($_COOKIE['wp_connect_share_' . COOKIEHASH])) {
			$cookie = wp_uncookies_array(stripslashes($_COOKIE['wp_connect_share_' . COOKIEHASH]));
		} else {
			$cookie = array();
		} 
		if (!in_array($post_id, $cookie)) {
			$share = '分享本文到: ' . show_share_button($post_id, $login);
		}
	} 
	return show_hide_content($login, $reply, $share, $content);
} 
// 隐藏内容可见Html
$hide_loaded = 1;
function show_hide_content($login, $reply, $share, $content) {
	global $hide_loaded, $wptm_connect, $wptm_share;
	if ($hide_loaded == 1) {
		$style = $wptm_share['share_style'] ? $wptm_share['share_style'] : '.hideContent{text-align:center;border:1px dashed #FF9A9A;padding:8px;margin:10px auto;color:#F00; line-height:24px;}.showContent{border:1px dashed #FF9A9A;}';
		echo '<style type="text/css">' . $style . '</style>';
	} 
	$hide_loaded += 1;
	$reload = '<a href="javascript:window.location.reload();" title="刷新">刷新本页</a>才能查看。';
	if ($login && $reply && $share) {
		$out = sprintf(' %s 并分享本文，以及 %s 后%s<p>登录并%s</p>', $login, $reply, $reload, $share);
	} elseif ($login && $reply) {
		$out = sprintf(' %s 并 %s 后%s %s', $login, $reply, $reload, $wptm_connect['enable_connect'] ? wp_connect_button($wptm_connect['icon'], 1, 0) : '');
	} elseif ($login && $share) {
		$out = sprintf(' %s 并分享本文后%s<p>登录并%s</p>', $login, $reload, $share);
	} elseif ($reply && $share) {
		$out = sprintf('分享本文并 %s 后%s<p>%s</p>', $reply, $reload, $share);
	} elseif ($login) {
		$out = sprintf(' %s 后%s %s', $login, $reload, $wptm_connect['enable_connect'] ? wp_connect_button($wptm_connect['icon'], 1, 0) : '');
	} elseif ($reply) {
		$out = sprintf(' %s 后%s', $reply, $reload);
	} elseif ($share) {
		$out = sprintf('分享本文后%s<p>%s</p>', $reload, $share);
	} else {
		return '<div class="showContent">' . $content . '</div>';
	} 
	return apply_filters('hide_content_text', '<div class="hideContent"><!-- 隐藏内容操作后可见 来自 WordPress连接微博 插件 -->温馨提示：此处内容已隐藏，您必须' . $out . '</div>', $out);
} 

?>