<?php
/**
 * 后台头部
 */
add_action('admin_init', 'wp_connect_admin_init');
function wp_connect_admin_init() { // 4.4
	global $wpdb, $wptm_options;
	if (isset($_POST['button_add']) && !empty($_POST['media_name'])) {
		$media = $_POST['media_name'];
		if ($media == 'qq') $media = 'qzone';
		header('Location:' . WP_CONNECT_URL . '/login.php?go=' . $media . '&act=bind');
		die();
	} 
	// 删除数据库+停用插件
	if (isset($_POST['wptm_delete'])) {
		delete_option("wp_blog_options");
		delete_option("wp_blog_bind");
		delete_option("wp_blog_token");
		$wpdb -> query("DELETE FROM $wpdb->options WHERE option_name like 'wptm_%'");
		if (function_exists('wp_nonce_url')) {
			$deactivate_url = 'plugins.php?action=deactivate&plugin=wp-connect/wp-connect.php';
			$deactivate_url = str_replace('&amp;', '&', wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-connect/wp-connect.php'));
		    header('Location:' . $deactivate_url);
			die();
		}
	}
}

/**
 * 插件页面
 */
// 同步帐号 V4.0
function wp_connect_account($ids) {
	if (!$ids || !is_array($ids)) return array();
	foreach ($ids as $k=>$id) {
		if (!is_array($id)) {
			$ids2 .= $id . ',';
			unset($ids[$k]);
		} 
	} 
	if ($ids2) $account = get_connect_user_by_ids(trim($ids2, ','));
	if (!$account) {
		$account = array();
	} elseif ($account['qzone']) {
		$account['qq'] = $account['qzone'];
		unset($account['qzone']);
	}
	return $account + $ids;
} 
// 同步微博 V4.0
function wp_option_account() {
	return wp_connect_account(get_option('wp_connect_sync'));
} 
// 同步博客 V4.0
function wp_blog_option_account() {
	return wp_connect_account(get_option('wp_connect_sync_blog'));
} 
// 保存设置
function wp_connect_update() {
	$updated = '<div class="updated" id="saved_box"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
	// 授权码
	if (isset($_POST['wp_connect_update'])) {
		// 导入备份数据
		if ($_POST['wptm_import_data']) {
			$import_set = (array)maybe_unserialize(trim(stripslashes($_POST['wptm_import_data'])));
			if ($import_set) {
				foreach($import_set as $kk=>$vv) {
					if ($vv) {
						update_option($kk, $vv);
					}
				}
				return;
			}
		}
		// 同步微博设置
		$_POST['wptm_options']['update_days'] = ifab(trim($_POST['wptm_options']['update_days']), '0');
		if ($_POST['wptm_options']['new_domain']) {
			$_POST['wptm_options']['new_domain'] = rtrim(trim($_POST['wptm_options']['new_domain']), '/');
			if (strpos($_POST['wptm_options']['new_domain'], 'http') !== 0) {
				$_POST['wptm_options']['new_domain'] = 'http://' . $_POST['wptm_options']['new_domain'];
			}
		}
		// 登录设置
		$_POST['wptm_connect']['sina_username'] = ltrim($_POST['wptm_connect']['sina_username'], '@');
		$_POST['wptm_connect']['qq_username'] = ltrim($_POST['wptm_connect']['qq_username'], '@');
		$_POST['wptm_connect']['comment_top'] = stripslashes($_POST['wptm_connect']['comment_top']);
		$_POST['wptm_connect']['comment_bottom'] = stripslashes($_POST['wptm_connect']['comment_bottom']);
		$_POST['wptm_connect']['comment_style'] = stripslashes($_POST['wptm_connect']['comment_style']);
		$_POST['wptm_connect']['comment_mail_content'] = stripslashes($_POST['wptm_connect']['comment_mail_content']);
		$_POST['wptm_connect']['disable_username'] = ifab(trim($_POST['wptm_connect']['disable_username']), 'admin');
		$wptm_connect = array_map('__array_trim', $_POST['wptm_connect']);
		// 自定义key
		$keys = array_map('__array_trim', $_POST['appkeys']);
		// 分享设置
		$_POST['wp_share']['share_style'] = stripslashes($_POST['wp_share']['share_style']);
		$share_options = array_map('__array_trim', $_POST['wp_share']);
		// 同步博客
		$_POST['blog_options']['title'] = ifab(trim($_POST['blog_options']['title']), '无标题');
		$_POST['blog_options']['copyright_html'] = ifab(trim($_POST['blog_options']['copyright_html']), "<p>本文来自：<a href=\"%%BlogURL%%\" target=\"_blank\">%%BlogName%%</a></p>\n<p>原文地址：<a href=\"%%PostURL%%\" target=\"_blank\">%%PostURL%%</a></p></textarea></p>");
		$blog_options = array_map('__array_trim', $_POST['blog_options']);

		$wp_blog = get_option('wp_blog_bind');
		$bind_options = array('u_sina' => get_post_user($_POST['user_sina'], $_POST['pass_sina'], $wp_blog['u_sina'][1]),
			'u_163' => get_post_user($_POST['user_163'], $_POST['pass_163'], $wp_blog['u_163'][1]),
			'u_qzone' => get_post_user($_POST['user_qzone'], $_POST['pass_qzone'], $wp_blog['u_qzone'][1]),
			'u_baidu' => get_post_user($_POST['user_baidu'], $_POST['pass_baidu'], $wp_blog['u_baidu'][1]),
			'u_lofter' => get_post_user($_POST['user_lofter'], $_POST['pass_lofter'], $wp_blog['u_lofter'][1]),
			'lofterblogname' => trim($_POST['lofterblogname']),
			'lofterblogid' => $wp_blog['lofterblogid'],
			'lofterrefresh' => ($wp_blog['lofterblogname'] != trim($_POST['lofterblogname'])) ? 1 : '',
			'renren_page_id' => trim($_POST['renren_page_id']),
			'diandiandomain' => trim($_POST['diandiandomain']),
			'tumblrdomain' => trim($_POST['tumblrdomain'])
			);
		$authorize_code = trim($_POST['authorize_code']);
		if ($authorize_code) {
			if (substr($authorize_code, -4) == 'WPMU') {
				$authorizecode = substr($authorize_code, 0, -4);
				$is_wpmu = 1;
			} else {
				$authorizecode = $authorize_code;
				$is_wpmu = '';
			}
			update_option("wptm_authorize_code", array('apikey' => substr($authorizecode, 0, -32), 'secret' => substr($authorizecode, -32), 'wpmu' => $is_wpmu, 'authorize_code' => $authorize_code));
		} else {
			delete_option("wptm_authorize_code");
		}
		update_option("wptm_options", $_POST['wptm_options']);
		update_option("wptm_connect", $wptm_connect);
		update_option("wp_blog_options", $blog_options);
		update_option("wp_blog_bind", $bind_options);
		update_option("wptm_share", $share_options);
		if (isset($_POST['weixin_token'])) {
			update_option("wptm_weixin", array('token' => trim($_POST['weixin_token']), 'welcome' => trim($_POST['weixin_welcome']), 'picurl' => trim($_POST['weixin_picurl']), 'start_weibo' => ltrim($_POST['weixin_start_weibo'])));
		}
		update_option("wptm_key", $keys);
		if (is_multisite() && is_main_site()) { // WPMU
			foreach ($keys as $key => $value) {
				$keys[$key][2] = 0;
			}
			update_site_option('wptm_key', $keys);
		} 
		if (!empty($_POST['wptm_connect']['enable_data'])) {
			wp_connect_log_install_sql();
		} 
		// WP User Avatar
		if (!empty($_POST['wptm_connect']['show_head'])) {
			$avatar_default = get_option('avatar_default');
			if ($avatar_default == 'wp_user_avatar') {
				update_option('avatar_default', 'mystery');
			} 
		} 
		wp_connect_user_install_sql();
		do_action('wp_connect_update');
		echo $updated;
	} 
	// 其他登录插件数据转换
	if (isset($_POST['other_plugins'])) {
		include_once(dirname(__FILE__) . '/modules/other_plugins.php');
		all_import_user();
		echo '<div class="updated" id="saved_box"><p><strong>数据转换成功！</strong></p></div>';
		return;
	} 
	// 私人微信绑定
	if (isset($_POST['weixin_verification'])) {
		update_option("weixin_user", array('verification' => rand(12345,98765) . substr(time(), -3), 'time' => time()));
		update_option("wptm_weixin", array('token' => trim($_POST['weixin_token']), 'welcome' => trim($_POST['weixin_welcome']), 'picurl' => trim($_POST['weixin_picurl']), 'start_weibo' => ltrim($_POST['weixin_start_weibo'])));
		return;
	} 
	$media = !empty($_POST['media_name']) ? $_POST['media_name'] : '';
	if ($media) {
		// delete
		if (isset($_POST['button_delete'])) {
			// if ($media == 'shuoshuo') $media = 'qzone';
			// update_option('wptm_' . $media, '');
			$wp_connect_sync = get_option('wp_connect_sync');
			if ($wp_connect_sync) {
				unset($wp_connect_sync[$media]);
				update_option('wp_connect_sync', $wp_connect_sync);
			}
		} elseif (isset($_POST['update_token'])) { // save
			if ($media == 'wbto') {
				$update = array('username' => trim($_POST['username']),
					'password' => key_encode(trim($_POST['password']))
					);
			} elseif (in_array($media, array('sina', 'douban', 'qq'))) {
				$update = array('access_token' => trim($_POST['username']),
					'expires_in' => trim($_POST['password'])
					);
			} else {
				$update = array('oauth_token' => trim($_POST['username']),
					'oauth_token_secret' => trim($_POST['password'])
					);
			} 
			// update_option('wptm_' . $media, $update);
			$wp_connect_sync = get_option('wp_connect_sync');
			if (!$wp_connect_sync) $wp_connect_sync = array();
			$wp_connect_sync[$media] = $update;
			update_option('wp_connect_sync', $wp_connect_sync);
		} 
	} 
	// 钩子，方便自定义插件
	do_action('save_connent_options');
}

/**
 * 我的资料
 */
// 同步微博 V4.0
function wp_usermeta_account($user_id) {
	return wp_connect_account(get_user_meta($user_id, 'wp_connect_sync', true));
} 
// 保存设置
function wp_user_profile_update($user_id) {
	$media = !empty($_POST['media_name']) ? $_POST['media_name'] : '';
	if ($media) {
		// delete
		if (isset($_POST['button_delete'])) {
			$wp_connect_sync = get_user_meta($user_id, 'wp_connect_sync', true);
			if ($wp_connect_sync) {
				unset($wp_connect_sync[$media]);
				update_usermeta($user_id, 'wp_connect_sync', $wp_connect_sync);
			}
		} elseif (isset($_POST['update_token'])) { // save
			if ($media == 'wbto') {
				$update = array('username' => trim($_POST['username']),
					'password' => key_encode(trim($_POST['password']))
					);
			} elseif (in_array($media, array('sina', 'douban', 'qq'))) {
				$update = array('access_token' => trim($_POST['username']),
					'expires_in' => trim($_POST['password'])
					);
			} else {
				$update = array('oauth_token' => trim($_POST['username']),
					'oauth_token_secret' => trim($_POST['password'])
					);
			} 
			$wp_connect_sync = get_user_meta($user_id, 'wp_connect_sync', true);
			if (!$wp_connect_sync) $wp_connect_sync = array();
			$wp_connect_sync[$media] = $update;
			update_usermeta($user_id, 'wp_connect_sync', $wp_connect_sync);
		} 
	} 
} 
// 同步设置
if ( $wptm_options['enable_wptm'] && ($wptm_options['multiple_authors'] || $wptm_options['registered_users']) ) {
	add_action('show_user_profile', 'wp_user_profile_fields', 12);
	add_action('edit_user_profile', 'wp_user_profile_fields', 12);
	add_action('personal_options_update', 'wp_save_user_profile_fields', 12);
	add_action('edit_user_profile_update', 'wp_save_user_profile_fields', 12);
} 

function wp_save_user_profile_fields($user_id) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	} 
	$update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
	$wptm_profile = array('sync_option' => trim($_POST['sync_option']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => $update_days
		);
	update_usermeta($user_id, 'wptm_profile', $wptm_profile);
} 

function wp_user_profile_fields( $user ) {
	global $user_level, $wptm_options;
	$user_id = $user->ID;
	wp_user_profile_update($user_id);
	$account = wp_usermeta_account($user_id);
	$wptm_profile = get_user_meta($user_id, 'wptm_profile', true);
	if (!$wptm_profile) $wptm_profile= array();
	if ($wptm_options['multiple_authors'] && ($user_level >= 1 || is_super_admin())) { //是否开启多作者和判断用户等级
		$canbind = true;
?>
<a name="sync"></a>
<h3>同步设置</h3>
<table class="form-table">
<tr>
	<th>文章同步设置</th>
	<td><select name="sync_option"><option value=""<?php selected($wptm_profile['sync_option'] == '');?>>不同步</option>
		<option value="6"<?php selected($wptm_profile['sync_option'] == 6);?>>标题</option>
		<option value="1"<?php selected($wptm_profile['sync_option'] == 1);?>>标题 + 链接</option>
		<option value="5"<?php selected($wptm_profile['sync_option'] == 5);?>>标题 + 摘要/内容</option>
		<option value="2"<?php selected($wptm_profile['sync_option'] == 2);?>>标题 + 摘要/内容 + 链接 (推荐)</option>
		<option value="3"<?php selected($wptm_profile['sync_option'] == 3);?>>文章摘要/内容</option>
		<option value="4"<?php selected($wptm_profile['sync_option'] == 4);?>>文章摘要/内容 + 链接</option></select>
	</td>
</tr>
<tr>
	<th>自定义消息</th>
	<td>新文章前缀：<input name="new_prefix" type="text" size="10" value="<?php echo $wptm_profile['new_prefix']; ?>" /> 更新文章前缀：<input name="update_prefix" type="text" size="10" value="<?php echo $wptm_profile['update_prefix']; ?>" /> 更新间隔：<input name="update_days" type="text" size="2" maxlength="4" value="<?php echo $wptm_profile['update_days']; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=修改文章时不同步]
	</td>
</tr>
</table>
<?php
	}
    if ( $canbind || $wptm_options['registered_users'] ) {
?>
<p class="show_botton"></p>
</form>
</div>
<?php include( dirname(__FILE__) . '/modules/bind.php' );?>
<div class="hide_botton">
<?php } 
}