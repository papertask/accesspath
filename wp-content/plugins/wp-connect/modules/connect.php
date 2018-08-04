<?php
$login_loaded = 1;

// 添加登录按钮
if ($wptm_connect['enable_connect']) {
	if (!$wptm_connect['manual'] || $wptm_connect['manual'] == 2)
		add_action('comment_form', 'wp_connect');
	add_action('login_form', 'wp_connect_button');
	add_action('register_form', 'wp_connect_button', 12);
	// WooCommerce
	if (class_exists('WooCommerce')) {
		add_action('woocommerce_login_form', 'wp_connect_button', 12);
		add_action('woocommerce_before_edit_account_form', 'wp_connect_login_bind', 12);
	}
	// Ultimate Member
	if (defined('um_url')) {
		add_action('um_after_login_fields', 'wp_connect_button', 1001);
		add_action('um_before_register_fields', 'wp_connect_button');
		add_action('um_after_account_general_button', 'wp_connect_login_bind');
	}
} 
/**
 * 登录 按钮显示 1.9.19 (V3.0)
 */
function user_denglu_platform() {
	global $wptm_connect;
	if ($wptm_connect['wechat']) {
		$weixin = 1;
		if (!$wptm_connect['weixin'] && is_weixin_client()) {
			$weixin = '';
		}
	} elseif ($wptm_connect['weixin'] && is_weixin_client()) {
		$weixin = 1;
	} else {
		$weixin = '';
	}
	$account = array('weixin' => $weixin,
		'qzone' => $wptm_connect['qqlogin'],
		'sina' => $wptm_connect['sina'],
		'qq' => $wptm_connect['qq'],
		'taobao' => $wptm_connect['taobao'],
		'alipay' => $wptm_connect['alipay'],
		'douban' => $wptm_connect['douban'],
		'renren' => $wptm_connect['renren'],
		'kaixin001' => $wptm_connect['kaixin001'],
		'facebook' => $wptm_connect['facebook'],
		'twitter' => $wptm_connect['twitter'],
		'google' => $wptm_connect['google'],
		'yahoo' => $wptm_connect['yahoo'],
		'linkedin' => $wptm_connect['linkedin'],
		'github' => $wptm_connect['github'],
		'baidu' => $wptm_connect['baidu'],
		'360' => $wptm_connect['360'],
		'tianya' => $wptm_connect['tianya'],
		'yixin' => $wptm_connect['yixin'],
		'msn' => $wptm_connect['msn'],
		//'sohu' => $wptm_connect['sohu'],
		//'netease' => $wptm_connect['netease'],
		);
	return array_filter($account);
}
function login_button_count() {
	$count = count(user_denglu_platform());
	if (!$count) {
		return;
	} elseif ($count == 1) {
		return 1;
	} else {
		return 2;
	} 
}
function sync_account($user_id) {
	if (!$user_id) return;
	$users = get_connect_users($user_id, 'name,media');
	if (!$users) return;
	$account = array();
	if ($users) { // 已绑定的帐号
		foreach ($users as $user) {
			$account[$user['media']] = 1;
		} 
	} 
	$media_sync = media_sync();
	return array_intersect_key($media_sync, $account) + array('last_login' => $users[0]['media']);
} 
// 社交登录简码
add_shortcode('wp_connect', 'wp_connect_shortcode');
function wp_connect_shortcode($atts){
	if (!in_the_loop()) return '';
	global $wptm_connect;
	return wp_connect_button($wptm_connect['icon'], 1, 0);
}
// 登录链接
function wp_connect_login_url($media, $redirect = '', $unbound = '') {
	$login_url = apply_filters('wp_connect_login_url', '', $media, $redirect, $unbound);
	if (!$login_url) {
		$plugin_url = apply_filters('wp_connect_plugin_url', WP_CONNECT_URL, 'login');
		if ($unbound) {
			$login_url = $plugin_url . '/login.php?go=' . $media . '&act=delete';
		} else {
			$login_url = $plugin_url . '/login.php?go=' . $media . $redirect;
		} 
	} 
	return $login_url;
} 
/**
 * 登录按钮 V4.0
 * $style 图标类型，0-中图标(24px)， 1-小图标(16px) 2-长图标(120*24px) 3-文本 默认为0
 * $text 是否加上文字“您可以用合作网站帐号登录” 0-不加 1- 加 ，默认为 1
 * $echo 0-return 1-echo ，默认为 1
 */
function wp_connect_button($style = 0, $text = 1, $echo = 1) {
	if (is_user_logged_in()) return;
	global $login_loaded;
	$plugin_url = WP_CONNECT_URL;
	$redirect = apply_filters('wp_redirect_url', '');
	$account = media_cn();
	if (function_exists('user_denglu_platform')) {
		$platform = user_denglu_platform();
		if (!$platform) {
			$platform = array('qzone' => 1, 'sina' => 1);
		} 
		$platform = array_intersect_key($account, $platform);
	} 
	$button = '';
	if ($style == 3) { // 文本
		$button .= '<!-- 使用社交帐号登录 来自 WordPress连接微博 插件 -->';
		if ($text) {
			$button .= '<div class="t_login_text t_login_text' . $login_loaded . '">'.__('您可以用合作网站帐号登录', 'wp-connect').':</div>';
			$div = 'div';
		} else {
			$div = 'span';
		} 
		$button .= '<' . $div . ' class="connectBox' . $login_loaded . ' t_login_button">';
		foreach($platform as $media => $vaule) {
			$vaule = __($vaule, 'wp-connect');
			$login_link = wp_connect_login_url($media, $redirect);
			$button .= "<a href=\"{$login_link}\" title=\"{$vaule}\" class=\"connect_{$media}\" rel=\"nofollow\">{$vaule}</a> ";
		} 
		$button .= '</' . $div . '>';
	} else { // 图标
		if ($style == 1) {
			$btn = 'small';
		} elseif ($style == 2) {
			$btn = 'btnbig';
		} else {
			$btn = 'btn';
			if (count($platform) == 1) {
				$btn .= 'big';
			} 
		} 
		if ($login_loaded == 1) {
			$button .= '<style type="text/css">.t_login_text{margin:0; padding:0;}.t_login_button{margin:0; padding: 5px 0;}.t_login_button a{margin:0; padding-right:3px;border:0;line-height:25px}.t_login_button a img{display:inline; border:none;}</style>';
		} 
		$button .= '<!-- 使用社交帐号登录 来自 WordPress连接微博 插件 -->';
		if ($text) {
			$button .= '<div class="t_login_text t_login_text' . $login_loaded . '">'.__('您可以用合作网站帐号登录', 'wp-connect').':</div>';
			$div = 'div';
		} else {
			$div = 'span';
		} 
		$button .= '<' . $div . ' class="connectBox' . $login_loaded . ' t_login_button">';
		foreach($platform as $media => $vaule) {
			$vaule = __($vaule, 'wp-connect');
			$login_link = wp_connect_login_url($media, $redirect);
			$button .= "<a href=\"{$login_link}\" title=\"{$vaule}\" rel=\"nofollow\"><img src=\"{$plugin_url}/images/login/{$btn}_{$media}.png\" /></a>";
		} 
		$button .= '</' . $div . '>';
	} 
	$login_loaded += 1;
	if ($echo) {
		echo $button;
	} else {
		return $button;
	} 
} 
// 获取单个/多个[,隔开]社交帐号的链接
function wp_connect_link($medias, $alink = 0, $style = 0) {
	$plugin_url = WP_CONNECT_URL;
	$redirect = apply_filters('wp_redirect_url', '');
	if ($alink == 9) { // 直接输出url
		return wp_connect_login_url($medias, $redirect);
	} else {
		$medias = explode(',', $medias);
		$account = media_cn();
		if ($style == 1) {
			$btn = 'small';
		} elseif ($style == 2) {
			$btn = 'btnbig';
		} else {
			$btn = 'btn';
		} 
		foreach($medias as $media) {
			$name = $account[$media];
			if ($name) {
				$url = wp_connect_login_url($media, $redirect);
				if ($alink) { // 文本按钮
					$show = $name;
					if ($alink == 2) $show .= '帐号登录';
					$class = ' class="quick-login-' . $media . '"';
				} else { // 图片按钮
					$show = "<img src=\"{$plugin_url}/images/login/{$btn}_{$media}.png\" />";
				} 
				echo '<a' . $class . ' href="' . $url . '" title="' . $name . '" rel="nofollow">' . $show . '</a> ';
			} 
		} 
	} 
} 
// 获取多个登录链接 V 3.6.9
function wp_connect_links($medias, $class = '', $before = '', $after = '') {
	$redirect = apply_filters('wp_redirect_url', '');
	$medias = explode(',', $medias);
	$account = media_cn();
	$account['sina'] = '微博';
	foreach($medias as $media) {
		$name = $account[$media];
		if ($name) {
			$url = wp_connect_login_url($media, $redirect);
			$style = ' class="' . $class . $media . '"';
			echo '<a' . $style . ' href="' . $url . '" title="' . $name . '" rel="nofollow">' . $before . $name . $after . '</a> ';
		} 
	} 
} 

function wp_connect($id = "") {
	if (is_user_logged_in() && is_singular()) { // V4.0 仅在文章/页面加上同步选项
		global $user_ID, $wptm_connect;
		if (!$wptm_connect['comment_no_sync'] && $sync = sync_account($user_ID)) {
			$select = 0; // 假如设置为1，表示使用复选框(checkbox)的形式，否则使用选择框(select)的形式。
			if ($select == 1) {
				if ($last = $sync['last_login']) {
					$$last = ' checked';
				} 
				unset($sync['last_login']);
				if ($sync) {
					echo '<!-- 同步评论到微博 来自 WordPress连接微博 插件 -->';
					echo '<p>'.__('同步评论到').' ';
					foreach($sync as $key => $vaule) {
						$vaule = __($vaule, 'wp-connect');
						echo '<label><input name="sync_comment[' . $key . ']" type="checkbox" value="1" ' . $$key . '/>' . $vaule . '</label>';
					} 
					echo '</p>';
				}
			} else {
				if ($last = $sync['last_login']) {
					$$last = ' selected';
				} 
				unset($sync['last_login']);
				if ($sync) {
					echo '<!-- 同步评论到微博 来自 WordPress连接微博 插件 -->';
					echo '<p><label>'.__('同步评论到').' <select name="sync_comment"><option value="">'.__('选择(不同步)').'</option>';
					foreach($sync as $key => $vaule) {
						$vaule = __($vaule, 'wp-connect');
						echo '<option value="' . $key . '" ' . $$key . '>' . $vaule . '</option>';
					} 
					echo '</select></label></p>';
				}
			} 
		}
	} else {
		global $wptm_connect;
		wp_connect_button($wptm_connect['icon']);
	} 
} 

/**
 * 登录 写入用户数据
 *
 * @since 2.0 （4.5）
 */ 
// 注册
function wp_connect_reg() {
	global $wptm_connect;
	$to = '';
	if ($wptm_connect['reg'] == 1) {
		$page_id = $wptm_connect['regid'];
		if ($page_id) $to = get_permalink($page_id);
		if (!$to) { // 生成一个页面
			$data = array('post_title' => '完善用户信息', 'post_name' => 'reg_info', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => '', 'comment_status' => 'closed', 'ping_status' => 'closed');
			remove_all_actions('publish_page');
			remove_all_actions('save_post');
			remove_all_actions('wp_insert_post');
			$post_id = wp_insert_post($data);
			if ($post_id) {
				$wptm_connect = get_option('wptm_connect');
				$wptm_connect['regid'] = $post_id;
				update_option('wptm_connect', $wptm_connect);
				$to = get_permalink($post_id);
			}
		}
	}
	$to = apply_filters('wp_connect_reg', $to);
	if (!$to) $to = plugins_url('wp-connect/signup.php');
	header('Location:' . $to);
	die();
}
// 登录 (V3.0)
function wp_connect_login($userinfo, $tmail, $uid = '', $reg = false) {
	global $wpdb, $wptm_options, $wptm_connect;
	$redirect_to = wp_connect_get_cookie('redirect_url');
	if (!$redirect_to) $redirect_to = get_bloginfo('url');
	if (!$uid && !$reg) { // 新用户
	    wp_connect_set_cookie("user", array($userinfo, $tmail, $redirect_to));
		$new_user = true;
	}
	if ($new_user && $wptm_connect['reg']) { // 强制填写注册信息
		return wp_connect_reg();
	} 
	$media = $userinfo[0];
	$user_name = $userinfo[1];
	$user_screenname = $userinfo[2];
	$user_head = $userinfo[3];
	$user_siteurl = $userinfo[4];
	$user_uid = $userinfo[5];
	$connect_user = $userinfo[6];

	if ($user_name) {
		if ($new_user && in_array($user_name, explode(',', $wptm_connect['disable_username']))) {
			return wp_connect_reg();
		} 
	} else {
		wp_die(sprintf(__('获取用户授权信息失败，请重新<a href="%s">登录</a> 或者 清除浏览器缓存再试!', 'wp-connect'), site_url('wp-login.php', 'login')) . " [ <a href='$redirect_to'>".__('Back')."</a> ]");
	} 
	if ($uid) {
		$wpuid = $uid;
	} elseif ($new_user) {
		$user_name = ifuser($user_name); // 新注册，但是数据库存在相同的用户名
	}
	if ($reg) { // 二次验证
		$uid = get_connect_uid($media, $user_uid);
		if ($uid) {
			wp_die(__('您已经注册过了，请直接登录。', 'wp-connect') . " [ <a href='$redirect_to'>".__('Back')."</a> ]");
		}
	}
	if (!$wpuid) {
		if (!function_exists('wp_insert_user')) {
			include_once(ABSPATH . WPINC . '/registration.php');
		}
		$userdata = array(
			'user_login' => $user_name,
			'user_pass' => ifab($userinfo[8], wp_generate_password()),
			'user_email' => $tmail,
			'user_url' => $user_siteurl,
			'user_nicename' => $user_name,
			'nickname' => $user_screenname,
			'display_name' => $user_screenname
			);
		$wpuid = wp_insert_user($userdata);
		if (!is_numeric($wpuid)) {
			$errors = $wpuid->errors;
			if ($errors['existing_user_email']) {
				wp_die(sprintf(__('该电子邮件地址 %s 已被注册。', 'wp-connect'), $tmail) . " [ <a href='$redirect_to'>".__('Back')."</a> ]");
			} elseif ($errors['existing_user_login']) {
				wp_die(sprintf(__('该用户名 %s 已被注册。', 'wp-connect'), $user_name) . " [ <a href='$redirect_to'>".__('Back')."</a> ]");
			} 
		} 
		do_action('wp_connect_register', $wpuid, $connect_user);
	} else {
		do_action('wp_connect_registered', $wpuid, $connect_user);
	}
	if ($wpuid) {
		$connect_user['user_id'] = $wpuid;
		$insert_id = update_connect_user($connect_user);
		wp_set_auth_cookie($wpuid, true);
		if ($uid) { // 已经存在用户
			do_action('wp_connect_user_update', $wpuid);
		} else { // 新注册
			if ($insert_id && $wptm_connect['multiple_authors'] && $wptm_connect['sync']) { // 用户首次登录的时候也绑定同步帐号
				$wp_connect_sync = get_post_meta($wpuid, 'wp_connect_sync', true);
				if (!$wp_connect_sync) $wp_connect_sync = array();
				$wp_connect_sync[$media] = $insert_id;
				update_usermeta($wpuid, 'wp_connect_sync', $wp_connect_sync);
				update_usermeta($wpuid, 'wptm_profile', array('sync_option' => ifab($wptm_options['sync_option'], 2), 'update_days' => 0));
			} 
			do_action('wp_connect_user_register', $wpuid);
		}
		$user = wp_set_current_user($wpuid);
		do_action('wp_login', $user->user_login, $user);
	} 
	wp_connect_clear_cookie("user");
	return $wpuid;
} 
add_action("login_form_logout", "connect_login_form_logout");
if (!function_exists('connect_login_form_login')) {
	function connect_login_form_logout() {
		wp_connect_clear_cookie('redirect_url');
	} 
} 

// 错误信息
function wp_noauth() {
	$redirect_to = wp_connect_get_cookie('redirect_url');
	if (!$redirect_to) $redirect_to = get_bloginfo('url');
	wp_die(sprintf(__('获取用户授权信息失败，请重新<a href="%s">登录</a> 或者 清除浏览器缓存再试!', 'wp-connect'), site_url('wp-login.php', 'login')) . " [ <a href='$redirect_to'>".__('Back')."</a> ]");
}

/**
 * 完善注册信息
 * 
 * @since 1.9.19 (V4.5)
 */
// 注册验证
function wp_check_new_user($user_login, $user_email) {
	global $wptm_connect;
	$user_login = sanitize_user($user_login);
	$user_email = apply_filters('user_registration_email', $user_email);
	// Check the username
	if ($user_login == '') {
		return __('<strong>ERROR</strong>: Please enter a username.');
	} elseif (!validate_username($user_login)) {
		return __('<strong>错误</strong>：用户名只能包含字母、数字、空格、下划线、连字符（-）、点号（.）和 @ 符号。', 'wp-connect');
		$user_login = '';
	} elseif (username_exists($user_login) || in_array($user_login, explode(',', $wptm_connect['disable_username']))) {
		return __('<strong>ERROR</strong>: This username is already registered, please choose another one.');
	} 
	// Check the e-mail address
	if ($user_email == '') {
		return __('<strong>ERROR</strong>: Please type your e-mail address.');
	} elseif (!is_email($user_email)) {
		return __('<strong>ERROR</strong>: The email address isn&#8217;t correct.');
		$user_email = '';
	} elseif (email_exists($user_email)) {
		return __('<strong>ERROR</strong>: This email is already registered, please choose another one.');
	} 
} 
// 绑定验证
function wp_check_bind_user($username, $password) {
	if (empty($password))
		return __('<strong>ERROR</strong>: The password field is empty.');

	$userdata = get_userdatabylogin($username);

	if (!$userdata)
		return sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), site_url('wp-login.php?action=lostpassword', 'login'));

	if (is_multisite()) {
		// Is user marked as spam?
		if (1 == $userdata -> spam)
			return __('<strong>ERROR</strong>: Your account has been marked as a spammer.'); 
		// Is a user's blog marked as spam?
		if (!is_super_admin($userdata -> ID) && isset($userdata -> primary_blog)) {
			$details = get_blog_details($userdata -> primary_blog);
			if (is_object($details) && $details -> spam == 1)
				return __('Site Suspended.');
		} 
	} 

	$userdata = apply_filters('wp_authenticate_user', $userdata, $password);
	if (is_wp_error($userdata))
		return;
	if (!wp_check_password($password, $userdata -> user_pass, $userdata -> ID))
		return sprintf(__('<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?'), $username, site_url('wp-login.php?action=lostpassword', 'login'));
} 
// 完善用户信息 处理
function wp_connect_add_reg($user) {
	if ($user && is_array($user)) {
		if (isset($_POST['skip_this_step'])) {
			$user[0][1] = ifuser($user[0][1]);
			wp_connect_login($user[0], $user[1], '', true);
			header('Location:' . $user[2]);
			exit;
		} 
		if (isset($_POST['user_login'])) {
			if ($_POST['user_login']) {
				$user_login = trim($_POST['user_login']);
				$user_pass = trim($_POST['user_pass']);
				if (!$_POST['user_bind']) { // 登录
					$user_login = __filter_emoji($user_login);
					$user_email = trim($_POST['user_email']);
					$user_name = __filter_emoji(trim($_POST['user_name']));
					$errors = wp_check_new_user($user_login, $user_email);
					if (!$errors) {
						$user[0][1] = $user_login;
						if ($user_name) {
							$user[0][2] = $user_name;
						} 
						$user[1] = $user_email;
						$user[0][8] = $user_pass;
						$wpuid = wp_connect_login($user[0], $user[1], '', true);
						header('Location:' . $user[2]);
						exit;
					} 
				} elseif ($_POST['user_bind']) { // 绑定已有帐号并登录
					$errors = wp_check_bind_user($user_login, $user_pass);
					if (!$errors) {
						$uid = username_exists($user_login);
						$wpuid = wp_connect_login($user[0], $user[1], $uid, true);
						header('Location:' . $user[2]);
						exit;
					} 
				} 
			} else {
				$errors = __('<strong>ERROR</strong>: Please enter a username.');
			} 
		} 
		return $errors;
	} else {
		wp_die(sprintf(__('Session已过期！请重新<a href="%s">登录</a>', 'wp-connect'), site_url('wp-login.php', 'login')));
	} 
}
// 输出填写信息模块
function wp_connect_add_reg_info() {
	$user = wp_connect_get_cookie("user");
	if ($user && is_array($user)) {
	} else {
		printf(__('Session已过期！请重新<a href="%s">登录</a>', 'wp-connect'), site_url('wp-login.php', 'login'));
		return;
	} 
	global $errors;
	echo '<style type="text/css">.uc-login form p{text-indent:0}.uc-login input{margin:5px 0;padding:3px;background:#fff;border:1px solid #ddd;width:auto;line-height:26px;-moz-box-shadow:inset 0 1px 1px rgba(0,0,0,0.1);-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.1);box-shadow:inset 0 1px 1px rgba(0,0,0,0.1)}.submit #submit{display:inline-block;text-decoration:none;font-size:13px;line-height:28px;height:30px;margin:0;padding:0 12px 2px;cursor:pointer;border-width:1px;border-style:solid;-webkit-appearance:none;-webkit-border-radius:3px;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;background:#00a0d2;border-color:#0073aa;-webkit-box-shadow:inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);box-shadow:inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);color:#fff}.submit #submit:hover{background:#0091cd;border-color:#0073aa;-webkit-box-shadow:inset 0 1px 0 rgba(120,200,230,.6);box-shadow:inset 0 1px 0 rgba(120,200,230,.6);color:#fff}</style>';
	echo '<div class="uc-login">';
	include(WP_CONNECT_PATH . '/login.inc.php');
	echo '</div>';
} 

/**
 * 用户信息
 */
// 绑定登录帐号
if ($wptm_connect['enable_connect']) {
	add_action('show_user_profile', 'wp_connect_profile_fields');
	add_action('edit_user_profile', 'wp_connect_profile_fields');
	add_action('personal_options_update', 'wp_connect_save_profile_fields');
	add_action('edit_user_profile_update', 'wp_connect_save_profile_fields');
} 

function wp_connect_save_profile_fields($user_id) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	} 
	return wp_edit_username();
} 
// 修改用户名
function wp_edit_username() {
	global $wpdb, $user_ID;
	$new_username = trim($_POST['new_username']);
	$old_username = trim($_POST['old_username']);
	if ($new_username && $new_username != $old_username) {
		if (!validate_username($new_username)) {
			wp_die(__('<strong>错误</strong>：用户名只能包含字母、数字、空格、下划线、连字符（-）、点号（.）和 @ 符号。', 'wp-connect') . " [ <a href='javascript:onclick=history.go(-1)'>".__('Back')."</a> ]");
		} elseif (username_exists($new_username)) {
			wp_die(__('<strong>ERROR</strong>: This username is already registered, please choose another one.') . " [ <a href='javascript:onclick=history.go(-1)'>".__('Back')."</a> ]");
		} else {
			$userid = trim($_POST['user_id']);
			clean_user_cache($userid);
			$wpdb->update($wpdb->users, array('user_login' => $new_username, 'user_nicename' => $new_username), array('ID' => $userid));
			update_user_meta($userid, 'edit_username', 1);
			if ($user_ID == $userid)
				wp_set_auth_cookie($user_ID, true); // 更新缓存
		} 
	} 
} 

function wp_connect_profile_fields($user) {
	$user_id = $user->ID;
	$user_login = $user->user_login;
	echo '<h3>'.__('登录绑定', 'wp-connect').'</h3><table class="form-table">';
	if (!get_user_meta($user_id, 'edit_username', true) && !is_super_admin($user_id)) {
		echo '<tr><th><label for="new_username">'.__('修改用户名', 'wp-connect').'</label></th><td><input type="text" name="new_username" id="new_username" value="' . $user_login . '" size="16" /><input type="hidden" name="old_username" id="old_username" value="' . $user_login . '" /> <span class="description">'.__('只允许修改一次!', 'wp-connect').'</span></td></tr>';
	} 
	set_user_loginbind($user);
	echo '</table>';
} 
// 绑定/解绑登录帐号，也可以使用 wp_connect_login_bind(); (V3.4)
function get_login_bind($args = array(), $user = '') {
	$defaults = array('class' => 'bind', 'before' => '', 'after' => '', 'lang' => true);

	$args = wp_parse_args($args, $defaults);

	extract($args, EXTR_SKIP);
	if (!$user) $user = wp_get_current_user();
	$account = get_user_loginbind($user);
	$binds = function_exists('filter_value') ? array_filter($account, filter_value) + $account : $account;

	foreach($binds as $key => $vaule) {
		if ($lang) $vaule[1] = __($vaule[1], 'wp-connect');
		if ($vaule[0]) {
			$login_link = wp_connect_login_url($key, '', true);
			echo "{$before}<a href=\"{$login_link}\" title=\"$vaule[1] (".sprintf(__('已绑定 %s', 'wp-connect'), $vaule[0]['name']) .")\" class=\"{$class}-{$key} pl-bound\" onclick=\"return confirm('".__('是否解除绑定? ', 'wp-connect')."')\">$vaule[1]<b></b></a>{$after} ";
		} else {
			$login_link = wp_connect_login_url($key);
			echo "{$before}<a href=\"{$login_link}\" title=\"$vaule[1]\" class=\"{$class}-{$key} pl-unbound\">$vaule[1]</a>{$after} ";
		}
	}
}
// 个人资料-显示登录帐号 (V3.0)
function set_user_loginbind($user) {
	global $wptm_options;
	if (!$wptm_options['multiple_authors'] || !$wptm_options['registered_users']) {
		wp_connect_css('css/bind.css');
	} 
	echo '<tr><th>'.__('绑定帐号', 'wp-connect').'</th><td><span id="login_bind">';
	get_login_bind(array(), $user);
	echo '</span><p class="description">'.__('绑定后，您可以使用用户名或者用合作网站帐号登录本站，再次点击可以解绑。', 'wp-connect').'</p></td></tr>';
}
// 绑定登录帐号 (V4.1.5)
function wp_connect_login_bind() {
	global $wptm_options;
	wp_connect_css('css/bind.css');
	echo '<div class="sns-bind">';
	echo '<h3>'.__('绑定登录帐号', 'wp-connect').'</h3><div id="login_bind">';
	get_login_bind();
	echo '</div><p class="description">'.__('绑定后，您可以使用用户名或者用合作网站帐号登录本站，再次点击可以解绑。', 'wp-connect').'</p>';
	echo '</div>';
}
// 已绑定帐号 1.9.22 (V3.4)
function get_user_loginbind($userdata) {
	if (is_object($userdata)) {
		$user_id = $userdata -> ID;
	} elseif (is_numeric($userdata)) {
		$user_id = (int)$userdata;
	}
	if (!$user_id) return;
	$users = get_connect_users($user_id, 'name,media');
	$account = array();
	if ($users) { // 已绑定的帐号
		foreach ($users as $user) {
			$account[$user['media']][0] = $user;
		} 
	} 
	if ($account['weixin'] || $account['wechat']) {
		$account['weixin'] = ifab($account['weixin'], $account['wechat']); // weixin - 公众号 wechat - 网页
	}
	$media_cn = media_cn();
	foreach ($media_cn as $media => $cn) {
		$account[$media][1] = $cn;
	} 
	if (function_exists('user_denglu_platform')) { // V1.9.19 or V2.2.2
		$platform = user_denglu_platform();
		if (!$platform) {
			$platform = array('sina' => 1, 'qq' => 1);
		} 
		return array_intersect_key($account, $platform);
	} 
	return $account;
} 

/**
 * 用户头像
 * 
 * @since 1.0 (V1.9.21)
 */
if ($wptm_connect['show_head']) {
	add_filter("get_avatar", "wp_connect_avatar", 9, 5); 
	// BuddyPress
	if (function_exists('bp_core_fetch_avatar_filter')) {
		add_filter("bp_core_fetch_avatar", "bp_avatar_filter", 100, 2);
		add_filter("bp_core_fetch_avatar_url", "bp_avatar_filter", 100, 2);
		function bp_avatar_filter($avatar, $params = array()) {
			if (strpos($avatar, 'gravatar.com/avatar/')) {
				if ($params['item_id']) {
					return wp_connect_avatar($avatar, $params['item_id'], $params['width'], '', $params['alt']);
				} 
				return false;
			} else {
				return $avatar;
			} 
		} 
	} 
} 
// 自定义头像
function get_c_avatar($id_or_email, $size = '36', $default = '', $alt = '') {
	global $wptm_connect;
	if (!$default) $default = $wptm_connect['comment_head'];
	if ($wptm_connect['disable_gravatar'] || !get_option('show_avatars')) {
		$avatar = wp_connect_avatar($avatar, $id_or_email, $size, $default, null);
	} elseif ($avatar = get_avatar($id_or_email, $size, $default, null)) {
		return $avatar;
	}
	if (!$avatar && $default) {
		$avatar = "<img alt='{$alt}' src='{$default}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
	} 
	return $avatar;
}
// 用户头像
function wp_connect_avatar($avatar, $id_or_email = '', $size = '32', $default = '', $alt = '') {
	if ($alt === null) $show_url = true; // 只在评论框及插件自带的最新评论添加URL
	if (is_numeric($id_or_email)) {
		$uid = $userid = (int) $id_or_email;
		$user = get_userdata($uid);
		if ($user) $email = $user -> user_email;
		if ($email && $avatar1 = get_weibo_head('', $size, $email, '', $show_url)) {
			return $avatar1;
		} 
	} elseif (is_object($id_or_email)) {
		$user = $id_or_email;
		$uid = $user -> user_id;
		$email = ifab($user -> comment_author_email, $user -> user_email);
		if ($show_url) {
			$author_url = $user -> comment_author_url;
		} 
		if ($avatar1 = get_weibo_head($user, $size, $email, $author_url, $show_url)) {
			return $avatar1;
		} 
		if ($uid && !$user -> user_email) $user = get_userdata($uid);
	} else {
		global $parent_file;
		$email = $id_or_email;
		if ($parent_file != 'options-general.php') {
			if ($email && $avatar1 = get_weibo_head('', $size, $email, '', $show_url)) {
				return $avatar1;
			} 
			$user = get_user_by_email($email);
			$uid = $user -> ID;
		} 
	} 
	if (!$email) {
		// V4.2.2
		return apply_filters('wp_connect_avatar', $avatar, $id_or_email, $size, $default, $alt);
	} 
	if ($uid) {
		$user_avatar = $user -> wp_connect_avatar;
		if ($user_avatar) {
			if (strpos($user_avatar, '://')) {
				$out = !is_ssl() ? $user_avatar : str_replace(array('http://tva', 'http://wx.qlogo.cn'), array('https://tva', 'https://wx.qlogo.cn'), $user_avatar);
			} else {
				$tmp = explode(',', $user_avatar);
				$out = media_avatar($tmp[0], $tmp[1], $size);
				if ($show_url && !$author_url) {
					$author_url = media_url($tmp[0], $tmp[1]);
				} 
			} 
		} 
		if ($out) {
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			if ($show_url && $author_url) {
				$avatar = "<a href='{$author_url}' rel='nofollow' target='_blank'>$avatar</a>";
			} 
		} elseif (function_exists('um_get_avatar') && $user_avatar = um_get_avatar($uid, $size, 'customize')) { // 插件兼容性
			$avatar = $user_avatar;
		}
	} 
	// V4.2.2
	return apply_filters('wp_connect_avatar', $avatar, $id_or_email, $size, $default, $alt);
}  

/**
 * 评论函数
 */
//  微博帐号(过滤重复) v1.9.12 (V1.9.23)
function at_username($a, $b, $c, $d) {
	$a = ($a) ? '@' . $a . ' ':''; //评论
	$b = ($b) ? '@' . $b . ' ':''; //回复
	$c = ($c) ? '@' . $c . ' ':''; //管理员
	if ($a != $b) {
		if ($b != $c) { // a!=b, b!=c
			$at = $a . $c;
		} elseif ($a == $c) { // a!=b, a=c
			$at = $c;
		} else { // a!=b, b=c
			$at = $a;
		} 
	} else {
		if ($b == $c) { // a=b=c
			$at = '';
		} else { // a=b, b!=c
			$at = $c;
		} 
	} 
	return $at . str_replace(array($a, $b, $c), '', $d);
} 
// 评论检查 v1.9.23
add_action('preprocess_comment', 'wp_connect_check_comment', 1);
function wp_connect_check_comment($commentdata) {
	if (!is_user_logged_in()) {
		if (wp_connect_check_email($commentdata['comment_author_email'])) {
			wp_fail(__('<strong>ERROR</strong>: please enter a valid email address.'));
		} 
	}
	return $commentdata;
} 
// 同步评论 V3.4
add_action('wp_insert_comment', 'wp_connect_comment', 100, 2);
function wp_connect_comment($id, $comment) {
	global $post, $wptm_options, $wptm_connect;
	$post_id = (isset($_POST['comment_post_ID'])) ? $_POST['comment_post_ID'] : $post->ID;
	if (!$post_id) {
		return;
	} 
	if (is_object($comment) && $user_id = $comment->user_id) {
		@ini_set("max_execution_time", 60);
		$author = $comment->comment_author;
		$comment_content = wp_replace($comment->comment_content);
		$parent_id = $comment->comment_parent;
		$weibodata = get_post_meta($post_id, 'weibodata', true);
		if ($weibodata && is_array($weibodata)) {
			if (isset($weibodata['sid']) || isset($weibodata['qid'])) { // 旧数据
				$weibodata = wp_connect_old_weibodata($weibodata);
				update_post_meta($post_id, 'weibodata', $weibodata);
			}
			if ($weibodata['sina']['id']) $tweetid['sina'] = $weibodata['sina']['id'];
			if ($weibodata['qq']['id']) $tweetid['qq'] = $weibodata['qq']['id'];
		} else {
			$tweetid = array();
		}
		if ($parent_id) {
			// $scid = get_comment_meta($parent_id, 'scid', true);
			$qcid = get_comment_meta($parent_id, 'qcid', true);
			$comment_parent = get_comment($parent_id);
			$parent_uid = $comment_parent->user_id;
			if ($parent_uid) {
				$name = get_connect_at($parent_uid);
			} else { // 回复那些抓回的评论
				$parent_email = $comment_parent->comment_author_email;
				$tmail = strstr($parent_email, '@');
				$name = array();
				if ($tmail == '@weibo.com') {
					$name['sina'] = $comment_parent->comment_author;
				} elseif ($tmail == '@t.qq.com') {
					$name['qq'] = str_replace($tmail, '', $parent_email);
				} 
			} 
		} 
		if ($qcid) $tweetid['qq'] = $qcid;
		if ($_POST['syncList']) { // new
			$sync = array_flip(explode(',', $_POST['syncList']));
		} elseif ($_POST['sync_comment']) { // old
			if (is_array($_POST['sync_comment'])) {
				$sync = $_POST['sync_comment'];
			} else {
				$sync[$_POST['sync_comment']] = 1;
			}
		}
		if ($sync || $tweetid) {
			if (!is_object($post)) {
				$post = get_post($post_id);
			} 
			$title = wp_replace($post->post_title);
			if ($wptm_options['enable_shorten']) { // wp短网址
				$post_url = get_link_short($post);
			} else {
				$post_url = get_permalink($post_id);
			} 
			$url = get_url_short($post_url . '#comment-' . $id); // 其他短网址
			$tweet = array();
			$username = get_connect_at($user_id);
			class_exists('classOAuthV2') or require(WP_CONNECT_CORE_PATH . 'class/oauth2.php');
			$openkeys = custom_openkey();
			if (mb_strlen($comment_content, 'utf-8') < 50) {
				$post_title = " #$title# ";
			} 
			$post_content = $post->post_content;
			$richMedia = wp_multi_media_url($post_content, $post_id);
			if ($richMedia[0]) { // 图片
				$picture = array('image', $richMedia[0]);
			} 
			// 新浪微博
			if (isset($sync['sina']) || $tweetid['sina']) {
				$token = get_connect_token($user_id, 'sina');
				if (!$token && $wptm_connect['user_id']) {
					$token = get_connect_token($wptm_connect['user_id'], 'sina');
					$relay = 1;
				} 
				if ($token['access_token']) {
					$content = at_username($name['sina'], $username['sina'], $wptm_connect['sina_username'], $comment_content);
					if ($tweetid['sina']) {
						if ($relay == 1) {
							$content = '网友(' . $author . ')在网站上的回复：' . $content;
						} 
						$status = wp_status($content, '', 140, 1);
						// $result = wp_reply_t_sina($openkeys['sina'], $token, $tweetid['sina'], $tweetid['scid'], $status);
						$result = wp_comment_t_sina($openkeys['sina'], $token, $tweetid['sina'], $status);
						$tweet['sina'] = $result['idstr'];
					} else {
						$status = wp_status('评论: ' . $content, $post_title. $url, 140, 1);
						$result = wp_update_t_sina($openkeys['sina'], $token, $status, $picture, '评论: ' . $content, $url);
						if ($result['idstr']) {
							$tweet['sina'] = 't' . $result['idstr']; // t表示微博
						} 
					} 
					if ($tweet['sina']) add_comment_meta($id, 'scid', $tweet['sina']);
				} 
			} 
			// 腾讯微博
			if (isset($sync['qq']) || $tweetid['qq']) {
				$token = get_connect_token($user_id, 'qq');
				if (!$token && $wptm_connect['user_id']) {
					$token = get_connect_token($wptm_connect['user_id'], 'qq');
					$relay = 2;
				} 
				if ($token) {
					$content = at_username($name['qq'], $username['qq'], $wptm_connect['qq_username'], $comment_content);
					if ($tweetid['qq']) {
						if ($relay == 2) {
							$content = '网友(' . $author . ')在网站上的回复：' . $content;
						} 
						$status = wp_status($content, '', 140, 1);
						$result = wp_comment_t_qq($openkeys['qq'], $token, $tweetid['qq'], $status);
						$tweet['qq'] = $result['data']['id'];
					} else {
						$status = wp_status('评论：' . $content, $post_title. $url, 140, 1);
						$result = wp_update_t_qq($openkeys['qq'], $token, $status, $richMedia);
						$tweet['qq'] = $result;
					} 
					if ($tweet['qq']) add_comment_meta($id, 'qcid', $tweet['qq']);
				} 
			} 
			// 人人网
			if (isset($sync['renren'])) {
				$token = get_connect_token($user_id, 'renren');
				if ($token['access_token']) {
					$status = wp_status($comment_content, $url, 140);
					$tweet['renren'] = wp_update_renren($openkeys['renren'], $token, $status);
				} 
			} 
			// 开心网
			if (isset($sync['kaixin001'])) {
				$token = get_connect_token($user_id, 'kaixin001');
				if ($token['access_token']) {
					$status = wp_status('评论：' . $comment_content, $post_title. $url, 140, 1);
					$tweet['kaixin001'] = wp_update_kaixin001($openkeys['kaixin001'], $token, $status, $picture);
				} 
			} 
			// 豆瓣
			if (isset($sync['douban'])) {
				if ($token = get_connect_token($user_id, 'douban')) {
					if ($token['access_token']) {
						$status = wp_status('评论：' . $comment_content, $post_title. $url, 140);
						$tweet['douban'] = wp_update_douban($openkeys['douban'], $token, $status);
					} 
				} 
			} 
			// 天涯微博
			if (isset($sync['tianya'])) {
				if ($token = get_connect_token($user_id, 'tianya')) {
					if ($token['oauth_token']) {
						$status = wp_status('评论：' . $comment_content, $post_title. $url, 140, 1);
						$tweet['tianya'] = wp_update_tianya($openkeys['tianya'], $token, $status);
					} 
				} 
			} 
			// twitter
			if (isset($sync['twitter'])) {
				$token = get_connect_token($user_id, 'twitter');
				if ($token['oauth_token']) {
					$status = wp_status('评论：' . $comment_content, $post_title. $url, 140);
					$tweet['twitter'] = wp_update_twitter($openkeys['twitter'], $token, $status);
				} 
			} 
			// facebook
			if (isset($sync['facebook'])) {
				$token = get_connect_token($user_id, 'facebook');
				if ($token['access_token']) {
					$facebook = array();
					$facebook['message'] = wp_status($comment_content, '', 140, 1);
					$facebook['description'] = wp_status(wp_trim_replace($post_content), '', 100, 1);
					$facebook['picture'] = $richMedia[0];
					$facebook['caption'] = $post_url;
					$facebook['name'] = $title;
					$facebook['link'] = $post_url;
					$tweet['facebook'] = wp_update_facebook($openkeys['facebook'], $token, $facebook);
				} 
			}
			// 易信朋友圈
			if (isset($sync['yixin'])) {
				$token = get_connect_token($user_id, 'yixin');
				if ($token['access_token']) {
					$yixin = array();
					$yixin['ps'] = wp_status($comment_content, '', 140, 1);
					$yixin['desc'] = wp_status(wp_trim_replace($post_content), '', 100, 1);
					$yixin['image'] = $richMedia[0];
					$yixin['url'] = $post_url;
					$yixin['title'] = $title;
					$tweet['yixin'] = wp_update_yixin($openkeys['yixin'], $token, $yixin);
				} 
			} 
			// linkedin
			if (isset($sync['linkedin'])) {
				$token = get_connect_token($user_id, 'linkedin');
				if ($token['access_token']) {
					$linkedin = array();
					$linkedin['comment'] = wp_status($comment_content, '', 140, 1);
					$linkedin['content'] = array();
					$linkedin['content']['title'] = $title;
					$linkedin['content']['description'] = wp_status(wp_trim_replace($post_content), '', 100, 1);
					//$linkedin['content']['submitted-image-url'] = $richMedia[0];
					$linkedin['content']['submitted-url'] = $post_url;
					$tweet['linkedin'] = wp_update_linkedin($openkeys['linkedin'], $token, $linkedin);
				} 
			}
			if ($tweet) {
				$tweet = array_filter($tweet);
				do_action('wp_connect_comment', $id, $user_id, $tweet);
			}
		} 
	} 
} 