<?php
/**
 * V1.9.15 - V4.0
 * 应一些网友要求，增加使用其他媒体登录插件的用户数据转换，以便旧用户支持WordPress连接微博插件，不会删除原有插件数据。
 * 支持以下插件：
 * 1、新浪连接: http://fairyfish.net/project/sina-connect/  或者 http://wordpress.org/extend/plugins/sina-connect/
 * 2、腾讯连接: http://fairyfish.net/2010/12/20/qq-connect/
 * 3、qq-connect: http://wordpress.org/extend/plugins/qq-connect/
 * 4、Douban Connect: http://fairyfish.net/2009/06/15/douban-connect/
 * 5、Sina_Weibo_Plus: http://www.ecihui.com/tech/227.htm 或者 http://wordpress.org/extend/plugins/sina-weibo-plus/
 * 6、Social Medias Connect: http://wordpress.org/extend/plugins/social-medias-connect/
 * 7、sina weibo wordpress plugin: http://wordpress.org/extend/plugins/sina-weibo-wordpress-plugin-by-wwwresult-searchcom
 * 8、EzEngage: http://wordpress.org/extend/plugins/ezengage
 */
function smc_get_id($name) {
	$weibo = array('sinaweibo' => array('sina'),
		'qqweibo' => array('qq'),
		'sohuweibo' => array('sohu'), 
		// '163weibo' => array('netease'),
		'twitter' => array('twitter'),
		'tianya' => array('tianya'),
		'renren' => array('renren'),
		'kaixin' => array('kaixin001'),
		'douban' => array('douban')
		);
	return $weibo[$name];
} 
function smc_import_user() {
	global $wpdb;
	@ini_set("max_execution_time", 180);
	$users = $wpdb -> get_results("SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = 'smcdata'", ARRAY_A);
	foreach ($users as $user) {
		smc_save_user($user['user_id'], maybe_unserialize($user['meta_value']));
	} 
} 
function smc_save_user($uid, $ret) {
	if(isset($ret['socialmedia'])){ // v2.0及以上版本
		foreach ($ret['socialmedia'] as $media => $data) {
			if ($media == 'sinaweibo') {
				$path = explode('/', $data['avatar']);
				update_connect_user(array('media' => 'sina', 'uid' => (int)$path[3], 'user_id' => $uid));
			} elseif (in_array($media, array('kaixin', 'renren', 'sohuweibo', 'taobao', 'tianya', 'baidu', '360cn', 'facebook', 'google', 'msnlive', 'yahoo'))) {
				if ($media == 'kaixin') {$media = 'kaixin001';}elseif ($media == 'sohuweibo') {$media = 'sohu';}elseif ($media == '360cn') {$media = '360';}elseif ($media == 'msnlive') {$media = 'msn';}
				update_connect_user(array('media' => $media, 'uid' => $data['uid'], 'user_id' => $uid));
			}
		}
	} elseif (isset($ret['smcweibo']) && $id = smc_get_id($ret['smcweibo'])) {
		if (empty($ret['smcid'])) { // v1.5及以上版本
			if ($id[0] == 'sina') {
				$path = explode('/', $ret['avatar']);
				$openid = (int)$path[3];
			} elseif (in_array($id[0], array('qq', 'douban', 'renren', 'kaixin001'))) {
				$openid = $ret['username'];
			} elseif ($id[0] == 'tianya') {
				$path = explode('/', $ret['userurl']);
				$openid = $path[3];
			} elseif ($id[0] == 'sohu') {
				$openid = str_replace('http://t.sohu.com/u/', '', $ret['userurl']);
			} elseif ($id[0] == 'twitter') {
				$openid = str_replace('@twitter.com', '', $ret['useremail']);
			} 
		} else {
			if ($id[0] == 'sina' || $id[0] == 'douban') {
				$openid = $ret['smcid'];
			} 
		}
		if ($openid)
			update_connect_user(array('media' => $id[0], 'uid' => $openid, 'user_id' => $uid));
	
	}
} 
// var_dump(smc_import_user());
function sc_import_user() {
	global $wpdb;
	@ini_set("max_execution_time", 180);
	$users = $wpdb -> get_results("SELECT user_id,meta_key,meta_value FROM $wpdb->usermeta WHERE (meta_key = 'scid' OR meta_key = 'qcid' OR meta_key = 'dcid')", ARRAY_A);
	foreach ($users as $user) {
		if ($user['meta_key'] == 'scid') {
			$media = 'sina';
		} elseif ($user['meta_key'] == 'qcid') {
			$media = 'qq';
		} elseif ($user['meta_key'] == 'dcid') {
			$media = 'douban';
		} 
		update_connect_user(array('media' => $media, 'uid' => $user['meta_value'], 'user_id' => $user['user_id']));
	} 
} 
// var_dump(sc_import_user());
function yaha_import_user() {
	global $wpdb;
	@ini_set("max_execution_time", 180);
	$users = $wpdb -> get_results("SELECT user_id FROM $wpdb->usermeta WHERE meta_key like '%sina_open_token_array%'", ARRAY_A);
	foreach ($users as $user) {
		$open_id = get_user_meta($user['user_id'], 'open_id', true);
		if ($open_id) {
			update_connect_user(array('media' => 'sina', 'uid' => $open_id, 'user_id' => $user['user_id']));
		} 
	} 
} 
// var_dump(yaha_import_user());
function ezengage_import_user() {
	global $wpdb;
	@ini_set("max_execution_time", 180);
	$table_name = $wpdb -> prefix . 'ezengage_identity';
	$users = $wpdb -> get_results("SELECT user_id,provider,identity,avatar_url FROM $table_name", ARRAY_A);

	$name = array('sinaweibo' => array('sina', 'http://t.sina.com.cn/'),
		'tencentweibo' => array('qq', 'http://t.qq.com/'),
		'renren' => array('renren', 'http://www.renren.com/home?id='),
		'sohuweibo' => array('sohu', 'http://t.sohu.com/'),
		'qzone' => array('qzone', 'http://qzone.qq.com/')
		);

	foreach ($users as $user) {
		$weibo = $name[$user['provider']];
		if ($weibo) {
			update_connect_user(array('media' => $weibo[0], 'uid' => str_replace($weibo[1], '', $user['identity']), 'user_id' => $user['user_id']));
		} 
	} 
} 
function open_social_import_user() {
	global $wpdb;
	$sql = "SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE 'open_type_%'";
	$outs = $wpdb -> get_results($sql);
	foreach ($outs as $out) {
		$media = str_replace('open_type_', '', $out->meta_key);
		if ($media == 'qq') {$media = 'qzone';} elseif ($media == 'kaixin') {$media = 'kaixin001';} elseif ($media == 'live') {$media = 'msn';}
		if ($media == 'wechat' || media_cn($media)) {
			update_connect_user(array('media' => $media, 'uid' => $out->meta_value, 'user_id' => $out->user_id));
			// if ($media == 'sina' || $media == 'douban' || $media == 'github') update_usermeta($out->user_id, 'wp_connect_avatar', $media . ',' . $out->meta_value);
		}
	}
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar' WHERE meta_key = 'open_img'");
}
// var_dump(ezengage_import_user());
function all_import_user() {
	@ini_set("max_execution_time", 300);
	sc_import_user();
	smc_import_user();
	yaha_import_user();
	ezengage_import_user();
	open_social_import_user();
} 

?>