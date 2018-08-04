<?php
/**
 * 旧数据转换
 * @since 4.0
 *
 */
if (!defined('ABSPATH')) {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['lastid'])) {
		include "../../../../wp-config.php";
		echo wp_connect_export_4_0(0, $_POST['lastid'] ? $_POST['lastid'] : 1, $_POST['number'] ? $_POST['number'] : 200);
		die();
	} 
} 
function wp_connect_tmp_get_media($id) {
	switch ($id) {
		case "weixinid":
		case "login_weixin":
			$media = 'weixin';
			break;
		case "login_wechat":
			$media = 'wechat';
			break;
		case "wxunionid":
			$media = 'wx';
			break;
		case "qqid":
		case "login_qzone":
			$media = 'qzone';
			break;
		case "stid":
		case "login_sina":
		case "wptm_sina":
			$media = 'sina';
			break;
		case "tqqid":
		case "login_qq":
		case "wptm_qq":
			$media = 'qq';
			break;
		case "renrenid":
		case "login_renren":
		case "wptm_renren":
			$media = 'renren';
			break;
		case "kaixinid":
		case "login_kaixin":
		case "wptm_kaixin001":
			$media = 'kaixin001';
			break;
		case "dtid":
		case "login_douban":
		case "wptm_douban":
			$media = 'douban';
			break;
		case "taobaoid":
		case "login_taobao":
			$media = 'taobao';
			break;
		case "alipayid":
		case "login_alipay":
			$media = 'alipay';
			break;
		case "sohuid":
		case "login_sohu":
		case "wptm_sohu":
			$media = 'sohu';
			break;
		case "baiduid":
		case "login_baidu":
			$media = 'baidu';
			break;
		case "guard360id":
		case "login_guard360":
			$media = '360';
			break;
		case "tytid":
		case "login_tianya":
		case "wptm_tianya":
			$media = 'tianya';
			break;
		case "yixinid":
		case "login_yixin":
		case "wptm_yixin":
			$media = 'yixin';
			break;
		case "twitterid":
		case "login_twitter":
		case "wptm_twitter":
			$media = 'twitter';
			break;
		case "facebookid":
		case "login_facebook":
		case "wptm_facebook":
			$media = 'facebook';
			break;
		case "linkedinid":
		case "login_linkedin":
		case "wptm_linkedin":
			$media = 'linkedin';
			break;
		case "msnid":
		case "login_msn":
			$media = 'msn';
			break;
		default:
	} 
	return $media;
} 
function wp_connect_tmp_get_user($uid) {
	global $wpdb;
	$user = $wpdb -> get_row("SELECT user_email,user_registered FROM $wpdb->users WHERE ID = '$uid'", ARRAY_A);
	return $user;
} 
function wp_connect_tmp_get_update_date($media) {
	switch ($media) {
		case "qzone":
		case "qq":
			$day = 90;
			break;
		case "sina":
		case "douban":
			$day = 7;
			break;
		case "renren":
		case "kaixin001":
			$day = 30;
			break;
		case "facebook":
		case "linkedin":
			$day = 60;
			break;
		default:
	} 
	return $day;
} 
function wp_connect_tmp_update_date($date) {
	global $wpdb;
	$table = $wpdb -> base_prefix . 'connect_user';
	$wpdb -> query("UPDATE $table SET create_date = '$date',update_date = '$date' WHERE create_date = '0000-00-00 00:00:00' AND update_date = '0000-00-00 00:00:00'");
	$wpdb -> query("UPDATE $table SET update_date = create_date WHERE update_date = '0000-00-00 00:00:00'");
	$wpdb -> query("UPDATE $table SET create_date = update_date WHERE create_date = '0000-00-00 00:00:00'");
	$wpdb -> query("UPDATE $table SET create_date = update_date WHERE create_date > update_date");
	if (time() < 1427731200) { // 2015/6/29
		$tmp = date('Y-m-d', time()-7776000);
		$wpdb -> query("UPDATE $table SET expires = expires - 7171200 WHERE media='sina' AND (update_date BETWEEN '$tmp' AND '2015-03-31') AND expires != '0'");
	}
	// 头像
	$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'qtid' AND meta_value = '/100'");
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = replace(meta_value, 'http://app.qlogo.cn/mbloghead/', 'qq,') WHERE meta_key = 'qtid'");
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = replace(meta_value, 'http://q.qlogo.cn/qqapp/', 'qzone,') WHERE meta_key = 'qqtid'");
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = replace(meta_value, 'http://qzapp.qlogo.cn/qz/', 'qzone,') WHERE meta_key = 'qqtid'");
	//$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('sina,', meta_value) WHERE meta_key = 'stid'");
	//$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('douban,', meta_value) WHERE meta_key = 'dtid'");
	//$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('tianya,', meta_value) WHERE meta_key = 'tytid'");
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar' WHERE meta_key IN ('rtid','ktid','wxtid','weixintid','ttid','guard360tid','yxtid')");
	$wpdb -> query("UPDATE $wpdb->usermeta SET meta_key = 'wp_connect_avatar',meta_value = CONCAT('baidu,', meta_value) WHERE meta_key = 'bdtid' AND meta_value NOT LIKE 'http://%'");
	// 只保留最后记录的一个头像
	$wpdb -> query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'wp_connect_avatar' AND umeta_id NOT IN (SELECT a.umeta_id FROM (SELECT max(umeta_id) AS umeta_id FROM $wpdb->usermeta WHERE meta_key = 'wp_connect_avatar' group by user_id) AS a)");
} 
function get_connect_tmp_user_ids_by_access_token($media, $access_token) {
	if (!$media || !$access_token) return;
	global $wpdb;
	$table = $wpdb -> base_prefix . 'connect_user';
	$sql = "SELECT ID FROM $table WHERE media = '$media' AND token LIKE '%$access_token%'";
	return $wpdb -> get_var($sql);
} 
function wp_connect_tmp_update_sync() {
	global $wpdb;
	$sql = "SELECT * FROM $wpdb->usermeta WHERE meta_key IN ('wptm_sina','wptm_qq','wptm_renren','wptm_kaixin001','wptm_sohu', 'wptm_twitter','wptm_facebook','wptm_linkedin','wptm_yixin','wptm_douban','wptm_tianya') AND meta_value !='' ORDER BY umeta_id ASC";
	$result = $wpdb -> get_results($sql, ARRAY_A); 
	// return var_dump($result);
	$wp_sync = array();
	foreach ($result as $u) {
		$meta_value = maybe_unserialize($u['meta_value']);
		$media = wp_connect_tmp_get_media($u['meta_key']);
		if ($media && is_array($meta_value)) {
			$id = (int)get_connect_tmp_user_ids_by_access_token($media, $meta_value['access_token'] ? $meta_value['access_token'] : $meta_value['oauth_token']);
			if ($id) {
				$wp_sync[$u['user_id']][$media] = $id;
			} 
		} 
	} 
	// return var_dump($wp_sync);
	if ($wp_sync) {
		foreach ($wp_sync as $user_id => $value) {
			update_user_meta($user_id, 'wp_connect_sync', $value);
		} 
		return $wp_sync;
	} 
} 
function wp_connect_tmp_update_options_sync() {
	global $wpdb;
	$sql = "SELECT * FROM $wpdb->options WHERE option_name IN ('wptm_sina','wptm_qq','wptm_renren','wptm_kaixin001','wptm_sohu', 'wptm_twitter','wptm_facebook','wptm_linkedin','wptm_yixin','wptm_douban','wptm_tianya') AND option_value !=''";
	$result = $wpdb -> get_results($sql, ARRAY_A); 
	// return var_dump($result);
	$wp_sync = array();
	foreach ($result as $u) {
		$meta_value = maybe_unserialize($u['option_value']);
		$media = wp_connect_tmp_get_media($u['option_name']);
		if ($media && is_array($meta_value)) {
			$id = (int)get_connect_tmp_user_ids_by_access_token($media, $meta_value['access_token'] ? $meta_value['access_token'] : $meta_value['oauth_token']);
			if ($id) {
				$wp_sync[$media] = $id;
			} 
		} 
	} 
	// return var_dump($wp_sync);
	if ($wp_sync) {
		update_option('wp_connect_sync', $wp_sync);
		return $wp_sync;
	} 
} 
function wp_connect_tmp_update_users($umeta_id, $number = 200) {
	global $wpdb;
	$t1 = time();
	$number = (int)$number;
	if (!$number) $number = 200;
	$sql = "SELECT * FROM $wpdb->usermeta WHERE umeta_id > $umeta_id AND meta_key IN ('weixinid', 'wxunionid', 'qqid', 'stid', 'tqqid', 'renrenid', 'kaixinid', 'dtid', 'taobaoid', 'alipayid', 'sohuid', 'baiduid', 'guard360id', 'tytid', 'yixinid', 'twitterid', 'facebookid', 'linkedinid', 'msnid', 'login_weixin', 'login_wechat', 'login_qzone', 'login_sina', 'login_qq', 'login_renren', 'login_kaixin', 'login_douban', 'login_taobao', 'login_alipay', 'login_sohu', 'login_baidu', 'login_guard360', 'login_tianya', 'login_yixin', 'login_twitter', 'login_facebook', 'login_linkedin', 'login_msn') AND meta_value !='' ORDER BY umeta_id ASC LIMIT $number";
	$result = $wpdb -> get_results($sql, ARRAY_A);
	if (!$result) return 'ok';
	$table_name = $wpdb->prefix . "connect_log";
	$has_log = $wpdb->get_var("show tables like '$table_name'") == $table_name;
	$out = array();
	foreach ($result as $u) {
		$user = get_user_by_user_id($u['user_id']);
		if ($user -> user_email) {
			if (strpos($u['meta_value'], 'a:') === 0) {
				$meta_value = maybe_unserialize($u['meta_value']);
				$media = wp_connect_tmp_get_media($u['meta_key']);
				if ($media && is_array($meta_value)) {
					$one = array('uid' => $meta_value['uid'], 'media' => $media, 'expires' => $meta_value['expires_in']);
					$one['name'] = $meta_value['name'];
					$one['token'] = array_intersect_key($meta_value, array('access_token' => '', 'expires_in' => '', 'refresh_token' => '', 'openid' => '', 'oauth_token' => '', 'oauth_token_secret' => '', 'weiboname' => ''));
					if ($media == 'qq' && $meta_value['media'] == 'qzone') {
						$one['token']['media'] = 'qzone';
					} elseif ($media == 'twitter' && $meta_value['username']) {
						$one['uid'] = $meta_value['uid'] = $meta_value['username'];
					}
					if (strpos($user -> user_email, $meta_value['uid'] . '@') !== false) {
						$one['user_id'] = $u['user_id'];
					} 
					$userdata = array();
					if ($meta_value['username']) {
						$userdata['username'] = $meta_value['username'];
					} 
					if ($userdata) {
						$one['userdata'] = $userdata;
					} 
					// 注册时间
					if (strpos($user -> user_email, $meta_value['uid'] . '@') !== false) {
						$reg_time = $user -> user_registered;
					} else {
						$reg_time = $has_log ? get_connect_log_time($media, $meta_value['uid'], 1) : '';
					}
					if ($reg_time) {
						$one['create_date'] = $one['update_date'] = $reg_time;
					} else {
						$one['create_date'] = $one['update_date'] = $user -> user_registered;
					}
					// 最后登录时间
					$login_time = $has_log ? get_connect_log_time($media, $meta_value['uid'], 2) : '';
					if ($login_time) {
						$one['update_date'] = $login_time;
					} elseif ($meta_value['expires_in']) {
						$update_day = wp_connect_tmp_get_update_date($media);
						if ($update_day) {
							$update_date = $meta_value['expires_in'] - $update_day * 86400;
							if (time() > $update_date && $update_date - strtotime($one['create_date']) > $update_day * 86400) {
								$one['update_date'] = date('Y-m-d H:i:s', $update_date);
							} 
						} 
					} 
					$out[$u['umeta_id']] = $one;
					update_connect_user($one);
				} 
			} else {
				$media = wp_connect_tmp_get_media($u['meta_key']);
				$one = array('uid' => $u['meta_value'], 'user_id' => $u['user_id'], 'media' => $media);
				$one['create_date'] = $one['update_date'] = '';
				if (strpos($user -> user_email, $u['meta_value'] . '@') !== false) {
					$one['create_date'] = $user -> user_registered;
				} 
				if ($media == 'twitter' && is_numeric($u['meta_value'])) {
					unset($one);
				} 
				if ($one) {
					$out[$u['umeta_id']] = $one;
					update_connect_user($one);
				} 
			} 
		} 
	} 
	// 实际条数
	$number = count($result);
	if ($number) {
		$last_id = $result[$number - 1]['umeta_id'];
		$t2 = time();
		$time = $t2 - $t1;
		if (!$time) $time = 1;
		$number = $number / $time * 10;
		$data = array('lastid' => $last_id, 'number' => (int)$number, 'time' => $time);
		update_site_option('wp_connect_export_data', $data);
		return $data;
	} 
} 
