<?php
/*
Plugin Name: WordPress连接微博 专业版
Author: 水脉烟香
Author URI: https://wptao.com/smyx
Plugin URI: https://wptao.com/wp-connect.html
Description: 支持使用QQ、新浪微博、微信等20个社交帐号登录您的网站。同步文章信息到10大微博和社区。同步全文到新浪博客、人人网、豆瓣等。使用本地化社会化评论框（包括新回复邮件通知）及微博评论回推到网站。支持数据统计，使用社会化分享按钮等。
Version: 4.6.5
*/

define('WP_CONNECT_VERSION', '4.6.5');
$wpurl = get_bloginfo('wpurl');
$siteurl = get_bloginfo('url'); // 首页
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');
$wp_blog_options = get_option('wp_blog_options');
$wptm_share = get_option('wptm_share');
$wptm_version = get_option('wptm_version');
define("WP_CONNECT", admin_url('admin.php?page=wp-connect'));
define("WP_CONNECT_PATH", WP_PLUGIN_DIR . "/wp-connect");
define("WP_CONNECT_URL", plugins_url('wp-connect'));
define("WP_CONNECT_KEY", md5(LOGGED_IN_KEY));
//define("WP_CONNECT_DEBUG", true);
//define("WP_CONNECT_DEVELOPER", true);
define("BJTIMESTAMP", ($wptm_options['char'] && !empty($wptm_options['minutes'])) ? time() + ($wptm_options['char'] * $wptm_options['minutes'] * 60) : time()); //服务器时间校正
define("WP_CONNECT_CORE_PATH", WP_CONNECT_PATH . '/');
include(dirname(__FILE__) . '/wp-functions.php');
// include core
@include(WP_CONNECT_CORE_PATH . 'modules/functions.php');
include(dirname(__FILE__) . '/options.php');
include(dirname(__FILE__) . '/sync.php');
// 同步微博 模块
if ($wptm_options['enable_wptm']) {
	include(dirname(__FILE__) . '/modules/weibo.php');
} 
// 联合登录 模块
if ($wptm_connect['comment_box'] || $wptm_connect['enable_connect']) {
	include(dirname(__FILE__) . '/modules/connect.php');
	if ($wptm_connect['comment_box']) { // 评论框
		if ($wptm_connect['comment_mobile'] && wp_is_mobile()) {} else {
			include(dirname(__FILE__) . '/comment/core.php'); // V4.4
			include(custom_comment_theme(1) . '/comment/functions.php');
		}
	}
} elseif ($wptm_connect['w2l'] && $wptm_connect['show_head']) {
	add_filter("get_avatar", "wp_connect_comments_avatar", 9, 3);
}
// 社会化分享 模块
if ($wptm_share['enable_share'] && $wptm_share['enable_share'] != 4) {
	include(dirname(__FILE__) . '/modules/share.php');
} 
// 是否开启隐藏内容简码
if (!isset($wptm_share['enable_hide']) || $wptm_share['enable_hide']) {
	add_shortcode('hide', 'view_hide_content');
}
include(dirname(__FILE__) . '/modules/widget.php');

if (is_admin() && isset($_REQUEST['page'])) {
	if ($_REQUEST['page'] == 'wp-connect-user') {
		include(WP_CONNECT_CORE_PATH . 'inc/user.php');
	} elseif ($_REQUEST['page'] == 'wp-connect-log' || $_REQUEST['page'] == 'wp-connect-analytics') {
		include(WP_CONNECT_CORE_PATH . 'inc/log.php');
	}
}

register_activation_hook(__FILE__, 'wp_connect_log_install_sql');
// wp_connect_log_install_sql();
function wp_connect_log_install_sql() {
	global $wpdb;
	$table_name = $wpdb->prefix . "connect_log";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
		`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
		`ref` int(2) NOT NULL DEFAULT '0',
		`refid` varchar(255) NOT NULL DEFAULT '',
		`data` text NOT NULL,
		`time` int(10) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY  (`ID`),
		KEY `user_id` (`user_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	} 
} 
// WP MU共用
register_activation_hook(__FILE__, 'wp_connect_user_install_sql');
// wp_connect_user_install_sql();
function wp_connect_user_install_sql() {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "connect_user";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
		`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
		`media` varchar(10) NOT NULL DEFAULT '',
		`uid` varchar(50) NOT NULL,
		`name` varchar(255) NOT NULL DEFAULT '',
		`gender` int(1) unsigned NOT NULL DEFAULT '0',
		`location` varchar(255) NOT NULL DEFAULT '',
		`token` text NOT NULL,
		`expires` int(10) unsigned NOT NULL DEFAULT '0',
		`userdata` text NOT NULL,
		`create_date` datetime NOT NULL default '0000-00-00 00:00:00',
		`update_date` datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`ID`),
		KEY (`user_id`),
		KEY `openid` (`media`, `uid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		return dbDelta($sql);
	} else {
		return true;
	}
} 
if ($wptm_version != WP_CONNECT_VERSION) {
	if ($wptm_version) { // 升级
		if (version_compare($wptm_version, '4.1.1', '<')) {
			if (version_compare($wptm_version, '4.0', '<')) {
				if (version_compare($wptm_version, '3.5', '<')) {
					if (version_compare($wptm_version, '3.4', '<')) {
						if (version_compare($wptm_version, '3.2.6', '<')) {
							if (version_compare($wptm_version, '3.2', '<')) {
								if (version_compare($wptm_version, '3.1', '<')) {
									if (version_compare($wptm_version, '3.0', '<')) {
										$wptm_advanced = get_option('wptm_advanced'); 
										// $blog_token = get_option('wp_blog_token');
										if ($wptm_advanced) {
											$wptm_options['registered_users'] = $wptm_advanced['registered_users'];
											$wptm_options['user_id'] = $wptm_advanced['user_id'];
											update_option('wptm_options', $wptm_options);
											delete_option("wptm_advanced");
											function wp_connect_delete_commentmeta_old_tweetid() {
												global $wpdb;
												$wpdb -> query("DELETE FROM $wpdb->commentmeta WHERE meta_key = 'tweetid'");
											} 
											wp_connect_delete_commentmeta_old_tweetid();
										} 
										/**
										 * if ($blog_token['qq'] || $blog_token['kaixin']) {
										 * update_option('wp_blog_token', array('qzone' => $blog_token['qq'], 'renren' => $blog_token['renren'], 'kaixin001' => $blog_token['kaixin'], 'diandian' => $blog_token['diandian']));
										 * }
										 */
										$wptm_key = get_option('wptm_key');
										if ($wptm_connect && !$wptm_key) { // < 1.9.12
											update_option('wptm_key', get_appkey());
										} 
										delete_option('wp_blog_token');
									} 
									// < 3.1
									$openkey = get_option('wptm_key');
									if ($openkey && !$openkey[3]) {
										$opensina = get_option('wptm_opensina');
										$openqq = get_option('wptm_openqq');
										if ($opensina['app_key'] || $openqq['app_key']) {
											$openkey[3] = array($opensina['app_key'], $opensina['secret']);
											$openkey[4] = array($openqq['app_key'], $openqq['secret']);
											update_option('wptm_key', $openkey);
											delete_option("wptm_opensina");
											delete_option("wptm_openqq");
										} 
									} 
									if ($wptm_connect) {
										$wptm_connect['w2l'] = 1;
									} 
								} 
								// < 3.2
								if ($wptm_options && array_key_exists('disable_pic', $wptm_options) && !$wptm_options['disable_pic']) {
									unset($wptm_options['disable_pic']);
									$wptm_options['enable_pic'] = 1;
									update_option('wptm_options', $wptm_options);
								} 
								if ($wptm_connect && !$wptm_connect['comment_push']) {
									if (array_key_exists('head', $wptm_connect) && !$wptm_connect['head']) {
										unset($wptm_connect['head']);
										$wptm_connect['show_head'] = 1;
									} 
									$wptm_connect['comment_push'] = 30;
									$wptm_connect['comment_expires'] = 5;
								} 
							} 
						} 
						// < 3.4
						if (!isset($wptm_connect['comment_box'])) {
							$wptm_connect['comment_box'] = 1;
							$wptm_connect['comment_theme'] = 1;
							$wptm_connect['comment_sticky'] = 1;
							$wptm_connect['comment_rating'] = 2;
							$wptm_connect['comment_addimage'] = 1;
							$wptm_connect['comment_placeholder'] = '点评一下吧!'; 
							// update_option('wptm_connect', $wptm_connect);
						} 
					} 
					// < 3.5
					if (!isset($wptm_connect['enable_data'])) {
						wp_connect_log_install_sql();
						$wptm_connect['enable_data'] = 1;
						update_option('wptm_connect', $wptm_connect);
					} 
				} 
				// < 4.0
				if ($wp_connect_lock = get_site_option('wp_connect_lock')) {
					define('WP_CONNECT_LOCK', true);
				} elseif (!get_site_option('wp_connect_export_data')) {
					define('WP_CONNECT_LOCK', true);
					@chmod(dirname(__FILE__) . '/debug', 0777);
					$wp_connect_lock = sprintf('%.22F', microtime(true));
					update_site_option('wp_connect_lock', $wp_connect_lock);
					update_site_option('wp_connect_export_data', 0);
					if (wp_connect_user_install_sql()) {
						wp_connect_export_4_0($wp_connect_lock, 0, 200);
					} 
				} else {
					$wp_blog_bind = get_option('wp_blog_bind');
					if (!isset($wp_blog_bind['renren_page_id'])) {
						$wptm_key = get_option('wptm_key');
						$wptm_key[24] = $wp_blog_bind['diandian'];
						$wptm_key[25] = $wp_blog_bind['tumblr'];
						unset($wp_blog_bind['qq'], $wp_blog_bind['renren'], $wp_blog_bind['kaixin001'], $wp_blog_bind['douban'], $wp_blog_bind['diandian'], $wp_blog_bind['tumblr']);
						$wp_blog_bind['renren_page_id'] = $wp_blog_options['renren_page_id'];
						$wp_blog_bind['diandiandomain'] = $wp_blog_options['diandiandomain'];
						$wp_blog_bind['tumblrdomain'] = $wp_blog_options['tumblrdomain'];
						update_option('wptm_key', $wptm_key);
						update_option('wp_blog_bind', $wp_blog_bind);
						wp_connect_other_plugin_4_0();
					} 
				} 
			} 
			// < 4.1.1
			if (version_compare($wptm_version, '4.0', '>=')) {
				function wp_connect_delete_unknown_location() {
					global $wpdb;
					$table_name = $wpdb->base_prefix . "connect_user";
					$wpdb -> query("UPDATE $table_name SET location = '' WHERE location = ' 未知'");
				} 
				wp_connect_delete_unknown_location();
			} 
		} 
	} 
	if (!defined('WP_CONNECT_LOCK')) {
		update_option('wptm_version', WP_CONNECT_VERSION); 
		// 删除旧数据
		if (version_compare($wptm_version, '4.0', '>')) {
			$wp_connect_export_data = (int)get_site_option('wp_connect_export_data');
			if ($wp_connect_export_data) {
				$wp_connect_export_data = $wp_connect_export_data / 100000;
				if ($wp_connect_export_data > 1) {
					if (version_compare($wp_connect_export_data, WP_CONNECT_VERSION, '<')) {
						$lock_time = sprintf('%.22F', microtime(true));
						update_site_option('wp_connect_lock_time', $lock_time);
						wp_connect_delete_4_1($lock_time);
					} 
				} 
			} 
		} 
	} 
} 

add_action('wp_redirect_url', 'wp_redirect_url');
function wp_redirect_url() {
	if (is_home()) {
		return;
	} elseif (!empty($_GET['redirect_to'])) {
		return "&redirect_url=" . urlencode($_GET['redirect_to']);
	} 
	global $action;
	if (empty($action) && !empty($_SERVER['REQUEST_URI'])) {
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$action = trim($uri[0], '/');
		if (in_array($action, array('login', 'user-login'))) $action = 'login';
	} 
	if ($action == "login" || $action == "register") {
		return "&redirect_url=" . urlencode(get_bloginfo('url'));
	} elseif (is_singular()) {
		// return '&redirect_url=' . urlencode(get_permalink());
		$schema = is_ssl() ? 'https://' : 'http://';
		return '&redirect_url=' . urlencode($schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	} 
} 
// 语言 V4.6.5
function wp_connect_locale($locale) {
	global $wptm_options;
    return $wptm_options['lang'] ? $wptm_options['lang'] : $locale;
}
add_action('init', 'wp_connect_init');
function wp_connect_init() { // 4.4
	global $wptm_options, $pagenow;
	if (!$wptm_options['lang'] || $wptm_options['lang'] != 'zh_CN') {
		if (!is_admin() || $pagenow == 'profile.php') {
			add_filter('locale', 'wp_connect_locale', 11);
			load_plugin_textdomain( 'wp-connect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
	}
	// WPMU 不同站点的用户权限
	if (is_multisite() && is_user_logged_in() && !current_user_can('read')) {
		global $user_ID;
		$user = new WP_User($user_ID);
		$user -> set_role(get_option('default_role'));
	} 
}

// 提示信息
add_action('admin_notices', 'wp_connect_warning');
function wp_connect_warning() {
	if (!empty($_COOKIE['sina_expired_' . COOKIEHASH])) {
		echo '<div class="error"><p>';
		echo stripslashes($_COOKIE['sina_expired_' . COOKIEHASH]);
		echo '</p></div>';
	} 
	if (current_user_can('manage_options')) {
		global $wptm_options;
		if (!$wptm_options) {
			echo '<div class="updated">';
			echo '<p><strong>您还没有对“WordPress连接微博”进行设置，<a href="admin.php?page=wp-connect">现在去设置</a></strong></p>';
			echo '</div>';
		}
		if (defined('WP_CONNECT_LOCK') && get_site_option('wp_connect_lock')) {
			echo '<div class="updated"><h3>WordPress连接微博 专业版 v4.0</h3>';
			wp_connect_export_data_ajax();
			echo '</div>';
		}
	} 
} 
add_action('network_admin_notices', 'wp_connect_warning_network');
function wp_connect_warning_network() {
	if (current_user_can('manage_options')) {
		if (defined('WP_CONNECT_LOCK') && get_site_option('wp_connect_lock')) {
			echo '<div class="updated"><h3>WordPress连接微博 专业版 v4.0</h3>';
			wp_connect_export_data_ajax();
			echo '</div>';
		}
	} 
} 

add_action('admin_menu', 'wp_connect_add_page');

function wp_connect_add_page() {
	global $wptm_connect;
	// add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
	if (function_exists('add_menu_page')) {
		add_menu_page('WordPress连接微博', '连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page', 'dashicons-share');
	} 
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-connect', 'WordPress连接微博', '连接微博', 'manage_options', 'wp-connect');
		add_submenu_page('wp-connect', '社交用户', '社交用户', 'manage_options', 'wp-connect-user', 'wp_connect_user_do_page');
		if ($wptm_connect['enable_data']) {
			add_submenu_page('wp-connect', '数据统计日志', '数据统计日志', 'manage_options', 'wp-connect-log', 'wp_connect_log_do_page');
			add_submenu_page('wp-connect', '数据统计图表', '数据统计图表', 'manage_options', 'wp-connect-analytics', 'wp_connect_analytics_do_page');
		}
	} 
}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'wp_connect_plugin_actions');
function wp_connect_plugin_actions($links) {
    $new_links = array();
    $new_links[] = '<a href="admin.php?page=wp-connect">' . __('Settings') . '</a>';
    return array_merge($new_links, $links);
}
// 设置
function wp_connect_do_page() {
	global $wpdb, $wpurl;
	date_default_timezone_set("PRC");
	wp_connect_update(); // 保存设置
	$plugin_url = WP_CONNECT_URL;
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	$wptm_key = get_option('wptm_key');
	$wp_blog = get_option('wp_blog_bind');
	$blog_options = get_option('wp_blog_options');
	$wptm_share = get_option('wptm_share');
	$wptm_weixin = get_option('wptm_weixin');
	$blog_token = wp_blog_option_account();
	$medias = array('qzone', 'renren', 'kaixin001', 'douban', 'diandian');
	foreach($medias as $media) {
		$expires_in_text = ($media == 'qzone') ? ' ，请在到期前更新授权' : '，默认会自动更新授权，一旦不能同步，请重新绑定';
		$bindinfo[$media] = $blog_token[$media] ? '<a href="' . $plugin_url . '/login.php?go=' . $media . '&act=blog">更新授权</a> ( ' . $blog_token[$media]['name'] . ' 授权有效期至 ' . date('Y年n月j日 H:i', $blog_token[$media]['expires_in']) . $expires_in_text . ' ) <a href="' . $plugin_url . '/login.php?go=' . $media . '&act=blog&del=1">解除绑定</a>' : '<a href="' . $plugin_url . '/login.php?go=' . $media . '&act=blog">绑定帐号</a>';
	} 
	$account = wp_option_account(); // 获取微博帐号
	$connect_plugin = true; // bind.php
	$authorize = connect_authorize_code();
	if ($authorize) {
		if (!wp_connect_has_bought($authorize)) {
			$authorize = get_option('wptm_authorize_code');
			echo '<div class="error"><p><strong>请填写正确的插件授权码。</strong></p></div>';
			$code_yes = '( x )';
		} elseif ($authorize['bought']) {
			$is_network = true;
			$code_yes = '( √ )';
		} else {
			$code_yes = '( √ )';
		}
	} 
	$validation = validation_error('wp-connect');
	if ($validation) {
		echo '<div class="error">' . $validation . '</div>';
	}
	$tuijian = get_site_option('wptao_tuijian');
	if ($tuijian) {
		foreach ($tuijian as $tjkey => $tjvalue) {
			if (!is_numeric($tjvalue) && !function_exists($tjkey)) {
				$tjcontent .= $tjvalue;
			} 
		} 
		if ($tjcontent) {
			echo '<div class="updated">' . $tjcontent . '<p>您可以点击当前页面的【保存更改】取消该提示！</p></div>';
		}
	} 
	$current_user_id = get_current_user_id();
	wp_connect_css('css/admin.css');
	wp_connect_js('js/jquery-co.js');
	wp_connect_js('js/options.js');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_media();
?>
<script>
jQuery(document).ready(function($) {
	connectOptions.init({cookie:true});
    $('#comment_head_button').click(function() {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var id = button.attr('id').replace('_button', '');
        wp.media.editor.send.attachment = function(props, attachment) {
            $("#" + id).val(attachment.url);
        }
        wp.media.editor.open(button);
        return false;
    });
});
</script>
<form method="post" id="pexeto-options">
<?php wp_nonce_field('form-connect');?>
<div id="pexeto-content-container">
	<div id="sidebar">
	  <div id="logo"></div>
	  <div id="navigation">
		<ul>
		  <li><span><a href="#menu-1"><img src="<?php echo $plugin_url;?>/images/icon_general.png" />基本设置</a></span></li>
		  <li><span><a href="#menu-2"><img src="<?php echo $plugin_url;?>/images/icon_weibo.png" />同步微博</a></span></li>
		  <li><span><a href="#menu-3"><img src="<?php echo $plugin_url;?>/images/icon_login.png" />社交登录</a></span></li>
		  <li><span><a href="#menu-11"><img src="<?php echo $plugin_url;?>/images/icon_key.png" />自定义key</a></span></li>
		  <li><span><a href="#menu-4"><img src="<?php echo $plugin_url;?>/images/icon_comment.png" />社交评论</a></span></li>
		  <li><span><a href="#menu-6"><img src="<?php echo $plugin_url;?>/images/icon_share.png" />社交分享</a></span></li>
		  <li><span><a href="#menu-5"><img src="<?php echo $plugin_url;?>/images/icon_blog.png" />同步博客</a></span></li>
		  <li><span><a href="#menu-9"><img src="<?php echo $plugin_url;?>/images/icon_data.png" />数据统计</a></span></li>
		  <?php if (!function_exists('wp_wechat_init')) { ?>
		  <li><span><a href="#menu-7"><img src="<?php echo $plugin_url;?>/images/icon_weixin.png" />微信功能</a></span></li>
		  <?php } ?>
		  <?php if (!function_exists('blog_optimize_add_page')) { ?>
		  <li><span><a href="#menu-10"><img src="<?php echo $plugin_url;?>/images/icon_optimize.png" />博客优化</a></span></li>
		  <?php } ?>
		  <li><span><a href="#menu-8"><img src="<?php echo $plugin_url;?>/images/icon_about.png" />关于插件</a></span></li>
		</ul>
	  </div>
	</div>
	<div id="content">
	  <div id="header">
		<h3 id="theme_name">WordPress连接微博 专业版 v<?php echo WP_CONNECT_VERSION;?></h3>
		<a class="more-button" href="http://www.smyx.net"></a>
	  </div>
	  <div id="options_container">
		<div id="menu-2" class="main-navigation-container">
		  <div id="tab_menu-2" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-2-settings" class="tab"><span>同步设置</span></a></li>
			  <li><a href="#tab_menu-2-weibo" class="tab"><span>绑定微博</span></a></li>
			  <li><a href="#tab_menu-2-other" class="tab"><span>可选设置</span></a></li>
			  <li><a href="#tab_menu-2-shuoshuo" class="tab"><span>说说</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-2-settings" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“文章同步到微博”功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[enable_wptm]" type="hidden" value="<?php echo $wptm_options['enable_wptm']; ?>" />
			</div>
			<div class="option">
			  <h4>同步内容设置</h4>
			  <select name="wptm_options[sync_option]" class="option-select">
			    <option value=""<?php selected($wptm_options['sync_option'] == '');?>>不同步</option>
				<option value="6"<?php selected($wptm_options['sync_option'] == 6);?>>标题</option>
				<option value="1"<?php selected($wptm_options['sync_option'] == 1);?>>标题 + 链接</option>
				<option value="5"<?php selected($wptm_options['sync_option'] == 5);?>>标题 + 摘要/内容</option>
				<option value="2"<?php selected(!$wptm_options || $wptm_options['sync_option'] == 2);?>>标题 + 摘要/内容 + 链接</option>
				<option value="3"<?php selected($wptm_options['sync_option'] == 3);?>>文章摘要/内容</option>
				<option value="4"<?php selected($wptm_options['sync_option'] == 4);?>>文章摘要/内容 + 链接</option>
			  </select>
			  <span class="text-tips">如果您开启了多作者博客，又不希望其他作者同步到您的微博，这里选择不同步，然后在<a href="<?php echo admin_url('profile.php');?>">我的资料</a>绑定。</span>
			</div>
			<div class="option">
			  <h4>多作者博客</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[multiple_authors]" type="hidden" value="<?php echo $wptm_options['multiple_authors']; ?>" />
			  <span class="text-tips">让每个作者发布的文章同步到他们各自绑定的微博上。<br />如果都同步到您的官方微博，请不用开启，否则请通知作者们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。</span>
			</div>
			<div class="option">
			  <h4>自定义标题格式：</h4>
			  <input name="wptm_options[format]" class="inputs" type="text" size="25" value="<?php echo $wptm_options['format']; ?>" />
              <span class="text-tips">标题: <code>%title%</code>，会继承上面的设置，可留空。例如:<code>【%title%】</code></span>
			</div>
			<div class="option">
			  <h4>自定义链接格式：</h4>
			  <input name="wptm_options[format_url]" class="inputs" type="text" size="25" value="<?php echo $wptm_options['format_url']; ?>" />
              <span class="text-tips">文章链接: <code>%url%</code>，会继承上面的设置，可留空。例如:<code>详情请点击:%url%</code></span>
			</div>
			<div class="option">
			  <h4>其他同步设置</h4>
			  <div class="option-check">
			    <label class="label_check"><input name="wptm_options[enable_pic]" type="checkbox" value="1" <?php checked(!$wptm_options || $wptm_options['enable_pic']); ?>/>同步图片</label>
			    <label class="label_check"><input name="wptm_options[thumbnail]" type="checkbox" value="1" <?php checked($wptm_options['thumbnail']); ?>/>优先同步特色图像</label>
				<label class="label_check"><input name="wptm_options[disable_video]" type="checkbox" value="1" <?php checked($wptm_options['disable_video']); ?>/>不同步视频</label>
				<label class="label_check"><input name="wptm_options[disable_excerpt]" type="checkbox" value="1" <?php checked($wptm_options['disable_excerpt']); ?>/>不同步摘要</label>
			  </div>
			</div>
			<div class="option">
			  <h4>把以下内容当成微博话题</h4>
			  <div class="option-check">
				 <label class="label_check"><input name="wptm_options[enable_cats]" type="checkbox" value="1" <?php checked($wptm_options['enable_cats']); ?>/>文章分类</label>
				 <label class="label_check"><input name="wptm_options[enable_tags]" type="checkbox" value="1" <?php checked($wptm_options['enable_tags']); ?>/>文章标签</label>
			  </div>
			</div>
			<div class="option">
			  <h4>国外SNS使用代理？</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[enable_proxy]" type="hidden" value="<?php echo $wptm_options['enable_proxy']; ?>" />
			  <span class="text-tips">(可选) 如果您主机在海外（包括港澳台）不要开启，国内主机用户开启后才能使用Twitter、Facebook、Google等，记得翻墙~</span>
			</div>
		  </div>
		  <div id="tab_menu-2-weibo" class="sub-navigation-container">
			<?php include( dirname(__FILE__) . '/modules/bind.php' );?>
		  </div>
		  <div id="tab_menu-2-other" class="sub-navigation-container">
			<div class="option">
			  <h3>黑白名单</h3>
			</div>
			<div class="option">
			  <h4>选择</h4>
			  <select name="wptm_options[list_type]" class="option-select">
			    <option value=""<?php selected($wptm_options['list_type'] == '');?>>黑名单</option>
				<option value="1"<?php selected($wptm_options['list_type'] == 1);?>>白名单</option>
			  </select>
			  <span class="text-tips">选择【黑名单】表示下面设置的情况不同步，选择【白名单】表示仅下面设置的情况同步，未设置的不同步</span>
			</div>
			<div class="option">
			  <h4>当文章分类ID为:</h4>
			  <input name="wptm_options[cat_ids]" class="inputs" type="text" size="30" value="<?php echo $wptm_options['cat_ids']; ?>" />
			  <span class="text-tips">如何查看分类ID？&nbsp;&nbsp;打开<a href="edit-tags.php?taxonomy=category" target="_blank">分类目录页面</a>，查看ID栏 （多个分类请用用英文逗号(,)分开）</span>
			</div>
			<div class="option">
			  <h4>当自定义文章类型为:</h4>
			  <input name="wptm_options[post_types]" class="inputs" type="text" size="30" value="<?php echo $wptm_options['post_types']; ?>" /><span class="text-tips">例如post_type=xxx ,请填写xxx（多个分类请用用英文逗号(,)分开）</span>
			</div>
			<div class="option">
			  <h3>自定义短网址</h3>
			</div>
			<div class="option">
			  <h4>博客默认</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[enable_shorten]" type="hidden" value="<?php echo $wptm_options['enable_shorten']; ?>" />
              <span class="text-tips">&nbsp;&nbsp;如：http://yourblog.com/?p=1</span>
			</div>
			<div class="option">
			  <h4>第三方短网址</h4>
			  <select class="option-select" name="wptm_options[t_cn]"><option value="">选择</option><option value="1"<?php selected($wptm_options['t_cn'] == "1");?>>http://t.cn/xxxxxx (新浪微博)</option></select>
			</div>
			<div class="option">
			  <h3>自定义消息</h3>
			</div>
			<div class="option">
			  <h4>发布新文章时添加前缀:</h4>
			  <input name="wptm_options[new_prefix]" class="inputs" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" />
			</div>
			<div class="option">
			  <h4>更新文章时添加前缀:</h4>
			  <input name="wptm_options[update_prefix]" class="inputs" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" />
			</div>
			<div class="option">
			  <h4>更新文章时是否要同步，间隔:</h4>
			  <input name="wptm_options[update_days]" class="inputs" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> <span class="text-tips">&nbsp;&nbsp;天</span>
              <span class="text-tips"> 填 0 表示修改文章时不同步，建议设置为 0，想要同步时在[编辑文章]页面勾选同步即可。 </span>
			</div>
			<div class="option">
			  <h3>图片URL</h3>
			  <span class="text-tips">只有在您无法获取到图片时填写，否则留空，请根据您的实际情况选择其中一种来填写。</span>
			</div>
			<div class="option">
			  <h4>自定义栏目名称</h4>
			  <input name="wptm_options[pic_postmeta]" class="inputs" type="text" size="30" value="<?php echo $wptm_options['pic_postmeta']; ?>" />
			  <span class="text-tips">如果您图片URL保存在自定义栏目（post_meta），请在上面写名称，否则留空。</span>
			</div>
			<div class="option">
			  <h4>从简码中提取</h4>
			  <input name="wptm_options[pic_tag]" class="inputs" type="text" size="30" value="<?php echo $wptm_options['pic_tag']; ?>" />
			  <span class="text-tips">如果您图片URL保存在简码（shortcode），如<code>[img src="图片URL"]</code>，在上面写<code>src</code>，否则留空。</span>
			</div>
			<div class="option">
			  <h3>其他设置</h3>
			</div>
			<div class="option">
			  <h4>我不能在“绑定微博”那边绑定帐号</h4>
			  <div class="on-off"><span></span></div>
			   <input name="wptm_options[bind]" type="hidden" value="<?php echo $wptm_options['bind']; ?>" />
			   <span class="text-tips">(可选) 开启后在“绑定微博”下面手动填写授权码</span>
			   <a href="http://open.smyx.net/weibo/oauth.php" target="_blank" class="help-btn"></a>
			</div>
			<div class="option">
			  <h4>同步时文章URL强制去掉www.</h4>
			  <div class="on-off"><span></span></div>
			   <input name="wptm_options[delete_www]" type="hidden" value="<?php echo $wptm_options['delete_www']; ?>" />
			   <span class="text-tips">(可选) 如果新浪微博同步提示：<code>text not find domain!</code>，并且您是自定义key的【连接网站】类型，且加不加www.都可以打开文章，请开启它。如果你是【网页应用】类型，不需要开启。</span>
			</div>
			<div class="option">
			  <h4>同步时替换文章URL的域名，新域名是</h4>
			  <input name="wptm_options[new_domain]" class="inputs" type="text" size="30" value="<?php echo $wptm_options['new_domain']; ?>" />
			  <span class="text-tips"><code>请加http://或者https://</code> 用于特殊情况，比如使用不同域名的读写分离，如果不懂，请咨询插件作者！</span>
			</div>
		  </div>
		  <div id="tab_menu-2-shuoshuo" class="sub-navigation-container">
			<div class="option">
			  <h3>说说</h3>
			  <span class="text-tips">“说说” 也就是在新页面一键发布新鲜事到各大微博和社区。[ <a href="http://www.smyx.net/say" target="_blank">查看演示</a> ]</span>
			</div>
			<div class="option">
			  <h4>自定义页面密码</h4>
			  <input name="wptm_options[page_password]" class="inputs" type="password" value="<?php echo $wptm_options['page_password']; ?>" autocomplete="off" />
			</div>
			<div class="option">
			  <h4>禁用AJAX无刷新提交</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[disable_ajax]" type="hidden" value="<?php echo $wptm_options['disable_ajax']; ?>" />
			</div>
			<div class="option">
			  <h4>开放给所有注册用户使用</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_options[registered_users]" type="hidden" value="<?php echo $wptm_options['registered_users']; ?>" />
			  <span class="text-tips">用户需要在个人资料页面绑定微博。</span>
			</div>
			<div class="option">
			  <h4>默认发文章的用户ID</h4>
			  <input name="wptm_options[user_id]" class="inputs" type="text" size="2" maxlength="4" value="<?php echo $wptm_options['user_id'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" />
			  <span class="text-tips">当前登录的用户ID是<?php echo $current_user_id;?>，<a href="<?php echo $plugin_url . '/wap.php';?>" target="_blank">写文章/发微博WAP页面（适用于手机浏览器）</a></span>
			</div>
			<div class="option">
			  <h2>安装方式</h2>
			  <p>首先新建页面，切换到HTML模式，然后输入简码 <code>[wp_to_microblog]</code> 即可，如果只是管理员使用，请在上面填写自定义页面密码，在同步微博那里绑定微博帐号；如果开放给所有注册用户使用，请在个人资料页面绑定微博，登录后即可使用一键同步功能。[ <a href="http://wiki.smyx.net/wordpress/faqs#page" target="_blank">查看详细描述</a> ]</p>
			</div>
		  </div>
		</div>
		<div id="menu-3" class="main-navigation-container">
		  <div id="tab_menu-3" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-3-login" class="tab"><span>登录设置</span></a></li>
			  <li><a href="#tab_menu-3-user" class="tab"><span>用户设置</span></a></li>
			  <li><a href="#tab_menu-3-other" class="tab"><span>其他插件转换</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-3-login" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“社交帐号登录网站”的功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[enable_connect]" type="hidden" value="<?php echo $wptm_connect['enable_connect']; ?>" />
			</div>
			<div class="option">
			  <h4>选择图标</h4>
			  <select name="wptm_connect[icon]" class="option-select">
			    <option value=""<?php selected($wptm_connect['icon'] == '');?>>中图标（24px）</option>
				<option value="1"<?php selected($wptm_connect['icon'] == 1);?>>小图标（16px）</option>
			  </select>
			  <span class="text-tips">提示：如需使用长图标或者文字，请用小工具或者看底下的函数。</span>
			</div>
			<div class="option">
			  <h4>添加按钮</h4>
			  <div class="option-check">
				<label class="label_check"><input name="wptm_connect[weixin]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['weixin']);?>/> 微信（公众号-微信客户端显示）</label>
				<label class="label_check"><input name="wptm_connect[wechat]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['wechat']);?>/> 微信（网页应用-PC端显示）</label>
				<label class="label_check"><input name="wptm_connect[qqlogin]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['qqlogin']);?>/> QQ登录</label>
				<label class="label_check"><input name="wptm_connect[sina]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['sina']);?> /> 新浪微博</label>
				<label class="label_check"><input name="wptm_connect[qq]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['qq']);?> /> 腾讯微博</label>
				<label class="label_check"><input name="wptm_connect[douban]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['douban']);?> /> 豆瓣</label>
				<label class="label_check"><input name="wptm_connect[renren]" type="checkbox" value="1" <?php checked($wptm_connect['renren']);?> /> 人人网</label>
				<label class="label_check"><input name="wptm_connect[kaixin001]" type="checkbox" value="1" <?php checked($wptm_connect['kaixin001']);?>/> 开心网</label>
				<label class="label_check"><input name="wptm_connect[baidu]" type="checkbox" value="1" <?php checked($wptm_connect['baidu']);?>/> 百度</label>
				<label class="label_check"><input name="wptm_connect[taobao]" type="checkbox" value="1" <?php checked(!$wptm_connect || $wptm_connect['taobao']);?>/> 淘宝网</label>
				<label class="label_check"><input name="wptm_connect[alipay]" type="checkbox" value="1" <?php checked($wptm_connect['alipay']);?>/> <span class="blue">支付宝</span></label>
				<label class="label_check"><input name="wptm_connect[tianya]" type="checkbox" value="1" <?php checked($wptm_connect['tianya']); ?> /> 天涯</label>
				<label class="label_check"><input name="wptm_connect[360]" type="checkbox" value="1" <?php checked($wptm_connect['360']);?>/> 360</label>
				<!--<label class="label_check"><input name="wptm_connect[sohu]" type="checkbox" value="1" <?php checked($wptm_connect['sohu']);?> /> 搜狐微博</label>-->
				<label class="label_check"><input name="wptm_connect[yixin]" type="checkbox" value="1" <?php checked($wptm_connect['yixin']);?>/> <span class="blue">易信</span></label>
				<label class="label_check"><input name="wptm_connect[twitter]" type="checkbox" value="1" <?php checked($wptm_connect['twitter']);?> /> Twitter</label>
				<label class="label_check"><input name="wptm_connect[facebook]" type="checkbox" value="1" <?php checked($wptm_connect['facebook']);?>/> Facebook</label>
				<label class="label_check"><input name="wptm_connect[github]" type="checkbox" value="1" <?php checked($wptm_connect['github']);?>/> GitHub</label>
				<label class="label_check"><input name="wptm_connect[google]" type="checkbox" value="1" <?php checked($wptm_connect['google']);?>/> 谷歌</label>
				<label class="label_check"><input name="wptm_connect[yahoo]" type="checkbox" value="1" <?php checked($wptm_connect['yahoo']);?>/> 雅虎</label>
				<label class="label_check"><input name="wptm_connect[linkedin]" type="checkbox" value="1" <?php checked($wptm_connect['linkedin']);?>/> LinkedIn</label>
				<label class="label_check"><input name="wptm_connect[msn]" type="checkbox" value="1" <?php checked($wptm_connect['msn']);?>/> Microsoft</label>
			  </div>
			  <span class="text-tips">在使用社交帐号登录时，<span class="blue">蓝色文字部分</span>需要去合作网站的开放平台处申请app key，然后在 本插件的【<a href="#tab_menu-11-key" class="gotab">自定义key</a>】页面填写。</span>
			</div>
			<div class="option">
			  <h4>评论框处显示登录按钮</h4>
			  <div class="option-check">
			    <label class="label_radio"><input name="wptm_connect[manual]" type="radio" value="2" <?php checked(!$wptm_connect['manual'] || $wptm_connect['manual'] == 2); ?>/>显示(默认)，包括同步评论到xxx</label>
			    <label class="label_radio"><input name="wptm_connect[manual]" type="radio" value="1" <?php checked($wptm_connect['manual'] == 1);?>/>手动添加函数 <code>&lt;?php wp_connect();?&gt; </code><a href="http://wiki.smyx.net/wordpress/faqs#connect-manual" target="_blank" class="help-btn" title="打开新窗口"></a></label>
			  </div>
			  <span class="text-tips">如果不显示同步评论到xxx，请在【评论设置】中开启相应功能。</span>
			</div>
			<div class="option">
			  <span class="text-tips">
				  <p><strong>“社交帐号登录”的函数参考:</strong></p>
				  <p>中图标(24px)：<br /><code>&lt;?php if (function_exists('wp_connect_button')) wp_connect_button(0);?&gt;</code></p>
				  <p>小图标(16px)：<br /><code>&lt;?php if (function_exists('wp_connect_button')) wp_connect_button(1);?&gt;</code></p>
				  <p>长图标(120*24px)：<br /><code>&lt;?php if (function_exists('wp_connect_button')) wp_connect_button(2);?&gt;</code></p>
				  <p>文字：<br /><code>&lt;?php if (function_exists('wp_connect_button')) wp_connect_button(3);?&gt;</code></p>
				  <p>如果不显示文字【您可以用合作网站帐号登录】，可以设置第二个参数为0：<br /><code>&lt;?php if (function_exists('wp_connect_button')) wp_connect_button(0,0);?&gt;</code></p>
				  <p>◆ 或者使用链接，不能直接打开噢，要加在a标签里面。</p>
				  <p>比如QQ登录的链接：<code><?php echo $plugin_url.'/login.php?go=';?><strong>qzone</strong></code></p>
				  <p>链接后面的<code>qzone</code>代表QQ登录，其他的可以对照下面：</p>
				  <p><?php
					foreach (media_cn() as $kk => $vv) {
						echo $vv.'：<code>'.$kk.'</code>，';
					}
					?></p>
				  <p>◆ 根据设置自动选择图标，登录后会显示同步评论到xxx： <code>&lt;?php wp_connect();?&gt; </code></p>
				  <p>◆ 社交帐号绑定/解绑的函数： <br /><code>&lt;?php wp_connect_login_bind();?&gt;</code>或者<code>&lt;?php get_login_bind();?&gt;</code> (文字版，可以自定义CSS)</p>
				  <p><strong>“社交帐号登录”的简码: <code>[wp_connect]</code> 可以在文章中调用。</strong></p>
				  <p><strong>如果您的主题支持“小工具”，请用小工具拖拽到适当位置。</strong></p>
			  </span>
			</div>
		  </div>
		  <div id="tab_menu-3-user" class="sub-navigation-container">
			<div class="option">
			  <h4>用户首次登录时，强制要求用户填写个人信息</h4>
			  <select name="wptm_connect[reg]" class="option-select">
			    <option value=""<?php selected($wptm_connect['reg'] == '');?>>关闭</option>
				<option value="1"<?php selected($wptm_connect['reg'] == 1);?>>开启（使用主题页面）</option>
				<option value="2"<?php selected($wptm_connect['reg'] == 2);?>>开启（使用WP经典登录页面）</option>
			  </select>
			</div>
			<div class="option">
			  <h4>微信客户端内强制用微信登录网站</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="wptm_connect[wx_login]" value="<?php echo $wptm_connect['wx_login']; ?>" />
			  <span class="text-tips">只要在微信客服端内打开您的网页，未登录时将强制用微信登录网站，需要在【登录设置】添加按钮 开启 微信（公众号-微信客户端显示）。</span>
			</div>
			<div class="option">
			  <h4>用户首次登录的时候也绑定同步帐号</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="wptm_connect[sync]" value="<?php echo $wptm_connect['sync']; ?>" />
			  <span class="text-tips">个人资料的帐号绑定，在开启【多作者博客】时生效。</span>
			</div>
			<div class="option">
			  <h4>使用登录者的微博/社区头像作为她的头像</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="wptm_connect[show_head]" value="<?php if(!$wptm_connect || $wptm_connect['show_head']) echo 1; ?>" />
			</div>
			<div class="option">
			  <h4>支持中文用户名</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="wptm_connect[chinese_username]" value="<?php if(!$wptm_connect || $wptm_connect['chinese_username']) echo 1; ?>" />
			</div>
			<div class="option">
			  <h4>禁止注册的用户名</h4>
			  <input name="wptm_connect[disable_username]" class="inputs" type="text" size="60" value="<?php echo ($wptm_connect['disable_username']) ? $wptm_connect['disable_username'] : 'admin';?>" /><span class="text-tips">用英文逗号(,)分开</span>
			</div>
		  </div>
		  <div id="tab_menu-3-other" class="sub-navigation-container">
			<div class="option">
			  假如你以前使用过其他类似的登录插件（<a href="http://wiki.smyx.net/wordpress/plugins" target="_blank">查看列表</a>），可以点击以下按钮进行数据转换，以便旧用户能使用本插件正常登录。<br /><strong>如果您的社交用户数据很多或者不在支持的列表中，请联系我QQ: 3249892</strong>
			</div>
			<div class="option">
			  <h4>其他登录插件数据转换</h4>
			  <input type="submit" class="input-btn" name="other_plugins" value="开始转换" />
              <span class="text-tips">可能需要一些时间，请耐心等待！</span>
			</div>
		  </div>
		</div>
		<div id="menu-11" class="main-navigation-container">
		  <div id="tab_menu-11" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-11-key" class="tab"><span>自定义key</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-11-key" class="sub-navigation-container">
			<div class="option">
			  <h3>用处：登录/同步（即微博消息的来自xxx）显示自己网站名称</h3>
			  <p style="color:red">本插件提供了默认key，可以不填写，QQ登录、微信建议自己申请key，如果您打算使用支付宝、易信必须自己申请并填写。</p>
			</div>
<?php
$redirect_uri = $plugin_url . '/dl_receiver.php';
$parse_url = parse_url(get_bloginfo('url'));
$redirect_domain = $parse_url['host'];
$redirect_url = $parse_url['scheme'] . '://' . $parse_url['host'];
$appkeys = array('weixin' => array('id' => 31, 'key' => 'AppID', 'secret' => 'AppSecret', 'title' => '微信(公众号，也可以写网站应用的KEY)', 'desc' => '<a href="http://www.smyx.net/login-wordpress-blog-by-using-wechat.html#app" target="_blank">如何获取?</a> <p>授权回调页面域名填写:<code>' . $redirect_domain . '</code> 或者 自定义域名: <input name="appkeys[31][3]" type="text" value="'.$wptm_key[31][3] .'"></p><p>如果您多个网站想使用同一个key，也可以在微信平台及上面自定义域名同时填写<code>sso.wptao.com</code></p>'),
	'wechat' => array('id' => 32, 'key' => 'AppID', 'secret' => 'AppSecret', 'title' => '微信(网站应用，不可以写公众号的KEY)', 'desc' => '<a href="http://www.smyx.net/login-wordpress-blog-by-using-wechat.html#web" target="_blank">如何获取?</a> <p>授权回调页面域名填写:<code>' . $redirect_domain . '</code> 或者 自定义域名: <input name="appkeys[32][3]" type="text" value="'.$wptm_key[32][3] .'"></p><p>如果您多个网站想使用同一个key，也可以在微信平台及上面自定义域名同时填写<code>sso.wptao.com</code></p>'),
	'qq' => array('id' => 13, 'key' => 'APP ID', 'secret' => 'APP Key', 'title' => 'QQ登录', 'desc' => '<a href="https://wptao.com/help/open-qq.html" target="_blank">如何获取?</a> <p>回调地址填写:<code>' . $redirect_uri . '</code></p>'),
	'sina' => array('id' => 3, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => '新浪微博', 'desc' => '<a href="http://open.weibo.com/apps/new?sort=web" target="_blank">去申请</a><p>您申请key的微博的授权期限长达5年，其他帐号授权为7天。</p>'),
	'tqq' => array('id' => 4, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => '腾讯微博(已禁止申请)'),
	'douban' => array('id' => 9, 'key' => 'App Key', 'secret' => 'Secret', 'title' => '豆瓣', 'desc' => '<a href="https://wptao.com/help/open-douban.html" target="_blank">如何获取?</a> <p>回调地址填写:<code>' . $redirect_uri . '</code></p>'),
	'renren' => array('id' => 7, 'key' => 'App Key', 'secret' => 'Secret Key', 'title' => '人人网', 'desc' => '<a href="https://wptao.com/help/open-renren.html" target="_blank">如何获取?</a> <p>应用根域名填写:<code>' . str_replace('www.', '', $redirect_domain) . '</code></p>'),
	'kaixin' => array('id' => 8, 'key' => 'App Key', 'secret' => 'Secret Key', 'title' => '开心网', 'desc' => '<a href="http://wiki.open.kaixin001.com/index.php?id=%E5%BC%80%E5%BF%83%E8%BF%9E%E6%8E%A5" target="_blank">如何获取?</a>'),
	'tianya' => array('id' => 17, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => '天涯微博', 'desc' => '<a href="http://open.tianya.cn/" target="_blank">去申请</a>'),
	'baidu' => array('id' => 19, 'key' => 'App Key', 'secret' => 'Secret Key', 'title' => '百度', 'desc' => '<a href="https://wptao.com/help/open-baidu.html" target="_blank">如何获取?</a> <p>根域名绑定填写:<code>' . str_replace('www.', '', $redirect_domain) . '</code></p>'),
	'taobao' => array('id' => 16, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => '淘宝网', 'desc' => '<a href="http://open.taobao.com/doc/detail.htm?spm=0.0.0.179.d7fwt4&id=1028" target="_blank">如何获取?</a>'),
	'open360' => array('id' => 23, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => '360', 'desc' => '<a href="http://open.app.360.cn/" target="_blank">去申请</a><p>回调地址填写:<code>' . $redirect_url . '</code>'),
	'alipay' => array('id' => 18, 'key' => 'partner', 'secret' => 'key', 'title' => '支付宝 (使用时必须)', 'desc' => '<a href="https://b.alipay.com/order/productDetail.htm?productId=2011042200323155" target="_blank">去申请</a>'),
	'facebook' => array('id' => 27, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => 'Facebook', 'desc' => '<a href="https://wptao.com/help/open-facebook.html" target="_blank">如何获取?</a> <p>Valid OAuth Redirect URIs填写:<code>' . $redirect_uri . '</code></p>'),
	'google' => array('id' => 1, 'key' => 'Client ID', 'secret' => 'Client secret', 'title' => 'Google', 'desc' => '<a href="https://wptao.com/help/open-google.html" target="_blank">如何获取?</a> <p>Authorized redirect URIs填写:<code>' . $redirect_uri . '</code></p>'),
	'yahoo' => array('id' => 12, 'key' => 'Client ID', 'secret' => 'Client secret', 'title' => 'Yahoo', 'desc' => '<a href="https://wptao.com/help/open-yahoo.html" target="_blank">如何获取?</a> <p>Callback Domain填写:<code>' . $redirect_url . '</code></p>'),
	'twitter' => array('id' => 28, 'key' => 'App Key', 'secret' => 'App Secret', 'title' => 'Twitter', 'desc' => '<a href="https://apps.twitter.com/" target="_blank">去申请</a> <p>Callback URLs填写:<code>' . $plugin_url . '/login.php' . '</code></p>'),
	'linkedin' => array('id' => 29, 'key' => 'App Key', 'secret' => 'Secret Key', 'title' => 'LinkedIn', 'desc' => '<a href="https://www.linkedin.com/secure/developer" target="_blank">去申请</a> <p>回调地址填写:<code>' . $redirect_uri . '</code></p>'),
	'github' => array('id' => 33, 'key' => 'Client ID', 'secret' => 'Client secret', 'title' => 'GitHub', 'desc' => '<a href="https://github.com/settings/applications/new" target="_blank">去申请</a> <p>Authorization callback URL填写:<code>' . $redirect_uri . '</code></p>'),
	'msn' => array('id' => 2, 'key' => 'Client ID', 'secret' => 'Client secret', 'title' => 'Microsoft(原MSN)', 'desc' => '<a href="https://wptao.com/help/open-msn.html" target="_blank">如何获取?</a>'),
	'yixin' => array('id' => 30, 'key' => 'AppID', 'secret' => 'AppSecret', 'title' => '易信 (使用时必须)', 'desc' => '<a href="https://wptao.com/help/open-yixin.html" target="_blank">如何获取?</a> <p>回调地址填写:<code>' . $redirect_uri . '</code></p>'),
	);
foreach ($appkeys as $kk1 => $vv1) {
	echo '<div class="option"><h4>' . $vv1['title'] . '</h4>' . $vv1['key'] . ': <input name="appkeys['.$vv1['id'].'][0]" type="text" value="' . $wptm_key[$vv1['id']][0] . '" /><br />' . $vv1['secret'] . ' : <input name="appkeys['.$vv1['id'].'][1]" type="text" value="' . $wptm_key[$vv1['id']][1] . '" /><br />';
	if ($vv1['desc']) {
		echo '<span class="text-tips">' . $vv1['desc'] . '</span>';
	} 
	echo '</div>';
} 

?>
		  </div>
		</div>
		<div id="menu-4" class="main-navigation-container">
		  <div id="tab_menu-4" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-4-comment" class="tab"><span>社会化评论框</span></a></li>
			  <li><a href="#tab_menu-4-push" class="tab"><span>微博回推</span></a></li>
			  <li><a href="#tab_menu-4-mail" class="tab"><span>邮件提醒</span></a></li>
			  <li><a href="#tab_menu-4-at" class="tab"><span>@微博帐号</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-4-comment" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“社会化评论框”</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_box]" type="hidden" value="<?php if (!isset($wptm_connect['comment_box']) || $wptm_connect['comment_box']) echo 1; ?>" />
			  <span class="text-tips">开启后，会将主题的评论框替换为插件提供的评论框（非第三方评论框）。<br />如果某些页面想使用主题自带的评论框，可以在文章发布/编辑页面的【讨论】里面勾选“这个页面不使用连接微博提供的评论框”的选项。</span>
			</div>
			<div class="option">
			  <h4>选择评论框模板</h4>
			  <select name="wptm_connect[comment_theme]" class="option-select">
			    <option value="1"<?php selected($wptm_connect['comment_theme'] == '' || $wptm_connect['comment_theme'] == 1);?>>模板1</option>
				<option value="2"<?php selected($wptm_connect['comment_theme'] == 2);?>>模板2</option>
				<option value="3"<?php selected($wptm_connect['comment_theme'] == 3);?>>模板3（适合深色背景网页）</option>
			  </select>
			  <span class="text-tips">如果只是微调css样式，可以在底部的<strong>CSS调整</strong>中填写。如果需要改变评论框框架，请开启下面的<strong>自定义评论框模板</strong>，插件也会陆续增加多套模板。</span>
			</div>
			<div class="option">
			  <h4>自定义评论框模板</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_custom]" type="hidden" value="<?php if ($wptm_connect['comment_custom']) echo 1; ?>" />
			  <span class="text-tips">如果您选择了“自定义评论框”，<strong>请将插件的整个comment目录复制到您当前主题文件夹内</strong>，所有改动请在主题文件夹那边改动，否则升级时会被覆盖噢。</span>
			</div>
			<div class="option">
			  <h4>移动端关闭评论框</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_mobile]" type="hidden" value="<?php if ($wptm_connect['comment_mobile']) echo 1; ?>" />
			  <span class="text-tips">开启后，移动端不会显示评论框，默认显示您主题自带的评论框。</span>
			</div>
			<div class="option">
			  <h4>登录按钮</h4>
			  <select name="wptm_connect[comment_login_icon]" class="option-select">
			    <option value="0"<?php selected($wptm_connect['comment_login_icon'] == '0');?>>图文小图标 (16px)</option>
				<option value="1"<?php selected($wptm_connect['comment_login_icon'] == 1);?>>中图标-圆形 (30px)</option>
				<option value="2"<?php selected($wptm_connect['comment_login_icon'] == 2);?>>中图标-正方形 (30px)</option>
			  </select>
			</div>
			<div class="option">
			  <h4>评论框位置</h4>
			  <select name="wptm_connect[comment_position]" class="option-select">
			    <option value="0"<?php selected($wptm_connect['comment_position'] == '0');?>>顶部</option>
				<option value="1"<?php selected($wptm_connect['comment_position'] == 1);?>>底部</option>
			  </select>
			</div>
			<div class="option">
			  <h4>评论框内的提示文字</h4>
			  <input name="wptm_connect[comment_placeholder]" class="inputs" type="text" size="60" value="<?php echo isset($wptm_connect['comment_placeholder']) ? $wptm_connect['comment_placeholder'] : '点评一下吧!';?>" />
			</div>
			<div class="option">
			  <h4>默认头像</h4>
			  <input name="wptm_connect[comment_head]" id="comment_head" class="inputs" type="text" size="60" value="<?php echo $wptm_connect['comment_head'];?>" /> <input type="button" class="button" id="comment_head_button" value="上传" />
			  <span class="text-tips">http:// 开头的图片URL，最佳尺寸：40x40px</span>
			</div>
			<div class="option">
			  <h4>禁用Gravatar头像</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[disable_gravatar]" type="hidden" value="<?php if ($wptm_connect['disable_gravatar']) echo 1; ?>" />
			  <span class="text-tips">Gravatar头像在中国大陆被屏蔽了，如果你没有找到替代方法，请开启它。</span>
			</div>
			<div class="option">
			  <h4>评论置顶</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_sticky]" type="hidden" value="<?php if (!isset($wptm_connect['comment_sticky']) || $wptm_connect['comment_sticky']) echo 1; ?>" />
			  <span class="text-tips">开启后，管理员或者文章的作者可以将用户的评论置顶。</span>
			</div>
			<div class="option">
			  <h4>评论中插入图片</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_addimage]" type="hidden" value="<?php if (!isset($wptm_connect['comment_addimage']) || $wptm_connect['comment_addimage']) echo 1; ?>" />
			  <span class="text-tips">开启后，评论者可以在评论中插入外部图片链接。</span>
			</div>
			<div class="option">
			  <h4>不显示同步评论到xxx</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[comment_no_sync]" type="hidden" value="<?php echo $wptm_connect['comment_no_sync']; ?>" />
			</div>
			<div class="option">
			  <h4>评论评价</h4>
			  <select name="wptm_connect[comment_rating]" class="option-select">
			    <option value="0"<?php selected($wptm_connect['comment_rating'] == '0');?>>不使用</option>
			    <option value="1"<?php selected($wptm_connect['comment_rating'] == 1);?>>顶</option>
				<option value="2"<?php selected($wptm_connect['comment_rating'] == '' || $wptm_connect['comment_rating'] == 2);?>>顶和踩</option>
			  </select>
			</div>
			<div class="option">
			  <h4>评论评价限制</h4>
			  <select name="wptm_connect[comment_rating_limit]" class="option-select">
			    <option value="0"<?php selected(!$wptm_connect['comment_rating_limit']);?>>任何人可以评价，包括游客</option>
			    <option value="1"<?php selected($wptm_connect['comment_rating_limit'] == 1);?>>必须注册用户登录后才能评价</option>
			  </select>
			</div>
			<div class="option">
			  <h4>版权信息</h4>
			  <select name="wptm_connect[comment_powered_by]" class="option-select">
				<option value="0"<?php selected($wptm_connect['comment_powered_by'] == 0); ?>>Powered by 连接微博</option>
				<option value="1"<?php selected($wptm_connect['comment_powered_by'] == 1); ?>>连接微博</option>
				<option value="-1"<?php selected($wptm_connect['comment_powered_by'] == '-1'); ?>>不显示</option>
			  </select>
			</div>
			<div class="option">
			  <h4>版权信息中使用推广链接，推广ID：</h4>
			  <input type="text" class="inputs" name="wptm_connect[tuiguang_id]" value="<?php echo $wptm_connect['tuiguang_id'];?>" onmouseup="value=value.replace(/[^\d]/g,'')" onkeyup="value=value.replace(/[^\d]/g,'')" />
			  <span class="text-tips">推广返利:10%，<a href="https://wptao.com/tuiguang" target="_blank">获取推广ID</a></span>
			</div>
			<div class="option">
			  <h4>在快捷登录处增加使用Wordpress帐号密码登录的链接</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="wptm_connect[wp_login_link]" value="<?php if($wptm_connect['wp_login_link']) echo 1; ?>" />
			</div>
			<div class="option">
			  <p>更多开关设置，请移步到<a href="<?php echo admin_url('options-discussion.php');?>" target="_blank">WP设置-讨论</a>页面。评论框功能与wp无缝连接噢。</p>
			</div>
			<div class="option">
			  <h4>评论框【头部】添加额外代码</h4>
			  <textarea name="wptm_connect[comment_top]" class="option-textarea" cols="180" rows="4"><?php echo $wptm_connect['comment_top'];?></textarea>
			  <span class="text-tips">可以根据你的主题添加html标签</span>
		    </div>
			<div class="option">
			  <h4>评论框【底部】添加额外代码</h4>
			  <textarea name="wptm_connect[comment_bottom]" class="option-textarea" cols="180" rows="4"><?php echo $wptm_connect['comment_bottom'];?></textarea>
			  <span class="text-tips">可以根据你的主题添加html标签</span>
		    </div>
			<div class="option">
			  <h4>CSS调整<p>如果改动较大，请修改插件目录的css文件，位于wp-connect/comment/css/</p></h4>
			  <textarea name="wptm_connect[comment_style]" class="option-textarea" cols="180" rows="4"><?php echo $wptm_connect['comment_style'];?></textarea>
			  <span class="text-tips">使用默认模板时出现跟主题样式不兼容时，可以使用浏览器工具调整，如火狐的firebug，ie8以上的[工具]-[开发人员工具]，Google Chrome右键的[审查元素]等等。</span>
		    </div>
		  </div>
		  <div id="tab_menu-4-mail" class="sub-navigation-container">
			<div class="option">
			  <h4>注意事项</h4>
			  <span class="text-tips">1. 邮件提醒功能需要您的服务器支持mail函数，可以使用<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" target="_blank">找回密码</a>的方式验证是否正常。<br />2. 如果服务器不支持mail函数，请使用SMTP的方式发邮件，建议安装SMTP相关插件，比如<a href="<?php echo (!function_exists('blog_optimize_phpmailer_init')) ? 'https://wptao.com/blog-optimize.html':'admin.php?page=blog-optimize#tab-add_smtp';?>" target="_blank">WordPress优化与增强插件-博客优化</a>插件，邮箱需要开启IMAP/SMTP服务，部分邮箱有限制。<br />3. 如果以上两种方式都不行，请关闭邮件提醒功能，只能建议你换主机了。</span>
			</div>
			<div class="option">
			  <h4>邮件提醒</h4>
			  <select name="wptm_connect[comment_mail_notify]" class="option-select">
				<option value="0"<?php selected($wptm_connect['comment_mail_notify'] == 0); ?>>关闭</option>
				<option value="1"<?php selected(!isset($wptm_connect['comment_mail_notify']) || $wptm_connect['comment_mail_notify'] == 1); ?>>开启（默认不选）</option>
				<option value="2"<?php selected($wptm_connect['comment_mail_notify'] == 2); ?>>开启（默认勾选）</option>
			  </select>
			  <span class="text-tips">开启后，会在评论框工具栏显示“新回复邮件提醒”复选框。如果选择默认勾选，只有用户填写正确邮箱时才有效果。</span>
			</div>
			<div class="option">
			  <h4>邮件主题</h4>
			  <br /><input name="wptm_connect[comment_mail_subject]" class="inputs" type="text" size="60" value="<?php echo $wptm_connect['comment_mail_subject'] ? $wptm_connect['comment_mail_subject'] : '您在 [[blogname]] 上的评论有新的回复';?>" style="width: 500px;">
			  <span class="text-tips">你可以使用下列标签: <code>[blogname]</code> 表示博客名字, <code>[postname]</code> 表示文章名</span>
		    </div>
			<div class="option">
			  <h4>邮件内容</h4>
			  <br /><textarea name="wptm_connect[comment_mail_content]" class="option-textarea" cols="180" rows="4" style="width: 500px;height:200px;"><?php echo $wptm_connect['comment_mail_content'] ? $wptm_connect['comment_mail_content'] : "<p></p>\n<p><strong>[pc_author]: </strong></p>\n<p><strong>您好</strong>，您之前在文章《[postname]》上的评论现在有了新的回复</p>\n<p>您之前的评论发表于<a href=\"[pc_link]\">[pc_date]</a>，您当时说:</p>\n<p style=\"border:1px solid #d9d9d9;padding:10px\">[pc_content]</p>\n<p>[<strong>[cc_author]</strong>]回复您说: —— [cc_date]</p>\n<p style=\"border:1px solid #d9d9d9;padding:10px\">[cc_content]</p>\n<p>您可以点击以下链接查看具体内容:<br />\n<a href=\"[cc_link]\">[cc_link]</a></p>\n<br /><strong>感谢您对 <a href=\"[blogurl]\">[blogname]</a> 的关注</strong>\n<br /><strong>该邮件由系统自动发出, 请勿回复, 谢谢！</strong></p>";?></textarea>
			  <span class="text-tips">你可以使用下列标签:<br /><br /><code>[pc_author]</code> 表示父评论的作者, <br /><code>[pc_content]</code> 表示父评论的内容, <br /><code>[pc_date]</code> 表示父评论的日期, <br /><code>[pc_link]</code>表示父评论的链接, <br /><code>[cc_author]</code> 表示子评论的作者, <br /><code>[cc_url]</code> 表示子评论的作者链接, <br /><code>[cc_content]</code> 表示子评论的内容, <br /><code>[cc_date]</code> 表示子评论的日期, <br /><code>[cc_link]</code>表示子评论的链接, <br /><code>[blogname]</code> 表示博客名, <br /><code>[blogurl]</code> 表示博客URL, <br /><code>[postname]</code> 表示文章名.</span>
		    </div>
		  </div>
		  <div id="tab_menu-4-push" class="sub-navigation-container">
			<div class="option">
			  <h4>把微博的评论抓回您的网站</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[w2l]" type="hidden" value="<?php if (!$wptm_connect || $wptm_connect['w2l']) echo 1; ?>" />
			  <span class="text-tips">即微博评论回推，目前仅支持新浪、腾讯微博。</span>
			  <span class="text-tips">只有开启“同步微博”，并且在“绑定微博”页面绑定了新浪微博和腾讯微博时有效。</span>
			</div>
			<div class="option">
			  <h4>评论回推频率</h4>
			  <select name="wptm_connect[comment_push]" class="option-select">
				<option value="5"<?php selected($wptm_connect['comment_push'] == 5);?>>每 5 分钟</option>
				<option value="10"<?php selected($wptm_connect['comment_push'] == 10);?>>每 10 分钟</option>
			    <option value="15"<?php selected($wptm_connect['comment_push'] == 15);?>>每 15 分钟</option>
				<option value="30"<?php selected(!$wptm_connect || $wptm_connect['comment_push'] == 30);?>>每 30 分钟</option>
				<option value="60"<?php selected($wptm_connect['comment_push'] == 60);?>>每小时</option>
				<option value="360"<?php selected($wptm_connect['comment_push'] == 360);?>>每 6 小时</option>
				<option value="720"<?php selected($wptm_connect['comment_push'] == 720);?>>每 12 小时</option>
				<option value="1440"<?php selected($wptm_connect['comment_push'] == 1440);?>>每天</option>
			  </select>
			  <span class="text-tips">每篇文章每隔<?php echo $wptm_connect['comment_push'];?>分钟抓取一次微博评论。</span>
			</div>
			<div class="option">
			  <h4>评论回推期限</h4>
			  <select name="wptm_connect[comment_expires]" class="option-select">
			    <option value="1"<?php selected($wptm_connect['comment_expires'] == 1);?>>1 天</option>
				<option value="2"<?php selected($wptm_connect['comment_expires'] == 2);?>>2 天</option>
				<option value="3"<?php selected($wptm_connect['comment_expires'] == 3);?>>3 天</option>
			    <option value="5"<?php selected(!$wptm_connect || $wptm_connect['comment_expires'] == 5);?>>5 天</option>
				<option value="7"<?php selected($wptm_connect['comment_expires'] == 7);?>>7 天</option>
				<option value="10"<?php selected($wptm_connect['comment_expires'] == 10);?>>10 天</option>
				<option value="15"<?php selected($wptm_connect['comment_expires'] == 15);?>>15 天</option>
				<option value="30"<?php selected($wptm_connect['comment_expires'] == 30);?>>30 天</option>
				<option value="90"<?php selected($wptm_connect['comment_expires'] == 90);?>>90 天</option>
				<option value="365"<?php selected($wptm_connect['comment_expires'] == 365);?>>1 年</option>
				<option value="1825"<?php selected($wptm_connect['comment_expires'] == 1825);?>>5 年</option>
				<option value="3650"<?php selected($wptm_connect['comment_expires'] == 3650);?>>10 年</option>
			  </select>
			  <span class="text-tips">文章发布<?php echo $wptm_connect['comment_expires'];?>天后不再抓取微博评论。</span>
			</div>
			<div class="option">
			  <h4>默认用户ID</h4>
			  <input name="wptm_connect[user_id]" class="inputs" type="text" size="2" maxlength="4" value="<?php echo $wptm_connect['user_id'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" />
			  <span class="text-tips">当前登录的用户ID是<?php echo $current_user_id;?></span>
			</div>
			<div class="option">
			  <h2>使用指南</h2>
			  <p>如果要设置评论黑名单，请到<a href="<?php echo admin_url('options-discussion.php');?>" target="_blank">讨论</a>页面的“评论黑名单”处填写。</p>
			  <p><strong>默认用户ID使用场景</strong></p>
			  <p>我们知道相同微博的评论会@对方，可以理解为“回复通知”，当遇到跨微博评论时，如“新浪微博”用户回复“腾讯微博”用户的评论时就短路了，所以我们用了一个折衷的方法，就是利用管理员绑定的微博帐号去间接的通知对方，格式为 “XX微博网友(微博帐号)在网站上的评论: 评论内容”。要使用该功能，请用 默认用户ID 对应的WP帐号 <?php echo get_username($wptm_connect['user_id']);?> 登录本站，然后在<a href="<?php echo admin_url('profile.php');?>">我的资料</a>页面绑定<strong>“登录帐号”(腾讯微博和新浪微博)</strong>！</p>
			</div>
		  </div>
		  <div id="tab_menu-4-at" class="sub-navigation-container">
			<div class="option">
			  <h4>新浪微博昵称:</h4>
			  <input name="wptm_connect[sina_username]" class="inputs" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' />
			  <span class="text-tips">填写的是@xxx中的xxx，不是填写帐号</span>
			</div>
			<div class="option">
			  <h4>腾讯微博帐号:</h4>
			  <input name="wptm_connect[qq_username]" class="inputs" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' />
			  <span class="text-tips">填写的是@xxx中的xxx，不是填写昵称或者QQ帐号</span>
			</div>
			<div class="option">
			  <div class="note_box"><p><strong>使用场景</strong></p>
			  <p>(1) 有新的评论时，将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码。</p>
			  <p>(2) 使用分享按钮时，分享的内容会自动加上 @微博帐号。</p></div>
			</div>
		  </div>
		</div>
		<div id="menu-5" class="main-navigation-container">
		  <div id="tab_menu-5" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-5-settings" class="tab"><span>同步设置</span></a></li>
			  <li><a href="#tab_menu-5-blog" class="tab"><span>绑定帐号</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-5-settings" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“同步博客”功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="blog_options[enable_blog]" type="hidden" value="<?php echo $blog_options['enable_blog']; ?>" />
			</div>
			<div class="option">
			  <h4>同步内容</h4>
			  <select name="blog_options[blog_sync]" class="option-select"><option value=""<?php selected($blog_options['blog_sync'] == ''); ?>>同步全文</option><option value="1"<?php selected($blog_options['blog_sync'] == 1); ?> >同步摘要</option><option value="3"<?php selected($blog_options['blog_sync'] == 3); ?>>截取文章内容前400个字符</option><option value="2"<?php selected($blog_options['blog_sync'] == 2); ?> >截取文章中&lt;!--tongbu--&gt;的前部分</option></select>
			  <a href="" class="help-button">
			  <div class="help-dialog" title="提示">
				<p>若选择“同步摘要”，同步的顺序依次为：同步摘要 ->> 截取文章&lt;!--tongbu--&gt;的前部分 ->> 同步全文，以此类推；<br />若选择“截取文章&lt;!--tongbu--&gt;的前部分”，同步的顺序依次为：截取文章&lt;!--tongbu--&gt;的前部分 ->> 同步全文，以此类推</p>
			  </div>
			  </a></div>
			<div class="option">
			  <h4>文章标题为空时</h4>
			  <select name="blog_options[notitle]" class="option-select" onchange="if(this.options[3].selected == true) document.getElementById('blog_title').style.display=''; else document.getElementById('blog_title').style.display='none'"><option value="1"<?php selected($blog_options['notitle'] == 1); ?>>不同步</option><option value="3"<?php selected(!$blog_options || $blog_options['notitle'] == 3); ?> >文章发布时间</option><option value="2"<?php selected($blog_options['notitle'] == 2); ?> >截取文章内容的前10个字</option><option value="0"<?php selected($blog_options['notitle'] == '0'); ?> >自定义标题</option></select>
			</div>
			<div class="option" id="blog_title"<?php if ($blog_options['notitle'] != '0') echo ' style="display:none"';?>>
			  <h4><em>文章标题为空时，自定义标题：</em></h4>
			  <input type="text" class="inputs" name="blog_options[title]" value="<?php echo $blog_options['title'];?>" />
			</div>
			<div class="option">
			  <h4>添加标题前缀：</h4>
			  <input type="text" class="inputs" name="blog_options[title_prefix]" value="<?php echo $blog_options['title_prefix'];?>" />
			</div>
			<div class="option">
			  <h4>允许同步的用户ID</h4>
			  <input type="text" class="inputs" name="blog_options[user_ids]" size="50" value="<?php echo $blog_options['user_ids'];?>" />
			  <span class="text-tips">当前登录的用户ID是<?php echo $current_user_id;?>，<a href="#userid">如何查看用户ID</a> &nbsp;&nbsp;留空表示所有用户发布文章都同步，否则只有填写的用户发布文章才同步。(多个用英文逗号(,)分开)</span>
			</div>
			<div class="option">
			  <h3>黑白名单</h3>
			</div>
			<div class="option">
			  <h4>选择</h4>
			  <select name="blog_options[list_type]" class="option-select">
			    <option value=""<?php selected($blog_options['list_type'] == '');?>>黑名单</option>
				<option value="1"<?php selected($blog_options['list_type'] == 1);?>>白名单</option>
			  </select>
			  <span class="text-tips">选择【黑名单】表示下面设置的情况不同步，选择【白名单】表示仅下面设置的情况同步，未设置的不同步</span>
			</div>
			<div class="option">
			  <h4>当文章分类ID为:</h4>
			  <input name="blog_options[cat_ids]" class="inputs" type="text" size="30" value="<?php echo $blog_options['cat_ids']; ?>" />
			  <span class="text-tips">如何查看分类ID？&nbsp;&nbsp;打开<a href="edit-tags.php?taxonomy=category" target="_blank">分类目录页面</a>，查看ID栏 （多个分类请用用英文逗号(,)分开）</span>
			</div>
			<div class="option">
			  <h4>当自定义文章类型为:</h4>
			  <input name="blog_options[post_types]" class="inputs" type="text" size="30" value="<?php echo $blog_options['post_types']; ?>" /><span class="text-tips">例如post_type=xxx ,请填写xxx（多个分类请用用英文逗号(,)分开）</span>
			</div>
			<div class="option">
			  <h3>设置文章版权</h3>
			  <span class="text-tips">有利于搜索引擎收录及网站回流。由于豆瓣审核严格容易导致封号，在豆瓣日记中不会添加。</span>
			</div>
			<div class="option">
			  <h4>同步时添加文章版权信息</h4>
			  <div class="on-off"><span></span></div>
			  <input type="hidden" name="blog_options[copyright]" value="<?php if(!$blog_options || $blog_options['copyright']) echo 1; ?>" />
			</div>
			<div class="option">
			  <h4>自定义版权内容<p>&nbsp;&nbsp;网站名称: <code>%%BlogName%%</code><br />&nbsp;&nbsp;网站地址: <code>%%BlogURL%%</code><br />&nbsp;&nbsp;文章链接: <code>%%PostURL%%</code></p></h4>
			  <textarea name="blog_options[copyright_html]" class="option-textarea" cols="180" rows="4"><?php echo ($blog_options['copyright_html']) ? stripslashes($blog_options['copyright_html']) : '<p>本文来自：<a href="%%BlogURL%%" target="_blank">%%BlogName%%</a></p><p>原文地址：<a href="%%PostURL%%" target="_blank">%%PostURL%%</a></p></textarea></p>';?></textarea>
			  <span class="text-tips">提示：如果不添加，请关闭版权，会自动跟正文内容换行隔开。</span>
		    </div>
			<div class="option">
			  <h4>内容前缀<p>&nbsp;&nbsp;网站名称: <code>%%BlogName%%</code><br />&nbsp;&nbsp;网站地址: <code>%%BlogURL%%</code><br />&nbsp;&nbsp;文章链接: <code>%%PostURL%%</code></p></h4>
			  <textarea name="blog_options[prefix]" class="option-textarea" cols="180" rows="4"><?php echo stripslashes($blog_options['prefix']);?></textarea>
			  <span class="text-tips">提示：如果不添加，请留空，不受版权开关影响，会自动跟正文内容换行隔开。</span>
		    </div>
			<div class="option">
			  <h2>安装文档</h2>
			  <p id="userid"><strong>如何查看用户ID</strong></p>
			  <p>打开<a href="users.php" target="_blank">用户页面</a>，编辑要添加的用户，可以看到地址栏中有user_id=之后的<strong>数字</strong>就是用户ID，多个用户请用半角逗号隔开。 </p>
			</div>
		  </div>
		  <div id="tab_menu-5-blog" class="sub-navigation-container">
			<div class="option">
			  同步全文/部分内容到新浪博客、网易博客、人人网、开心网、豆瓣、Tumblr
			</div>
			<div class="option">
			  <h3>新浪博客</h3>
			</div>
			<div class="option">
			  <h4>帐 号: </h4>
			  <input type="text" class="inputs" name="user_sina" size="25" value="<?php echo $wp_blog['u_sina'][0];?>" />
			</div>
			<div class="option">
			  <h4>密 码: </h4>
			  <input type="password" class="inputs" name="pass_sina" size="25" autocomplete="off" /><?php if($wp_blog['u_sina'][1]) echo '<span class="text-tips">密码留空表示不修改</span>';?>
			</div>
			<div class="option">
			  <h3>网易博客</h3>
			</div>
			<div class="option">
			  <h4>邮 箱: </h4>
			  <input type="text" class="inputs" name="user_163" size="25" value="<?php echo $wp_blog['u_163'][0];?>" />
			  <span class="text-tips">格式为xxx@xxx.xxx</span>
			</div>
			<div class="option">
			  <h4>密 码: </h4>
			  <input type="password" class="inputs" name="pass_163" size="25" autocomplete="off" /><?php if($wp_blog['u_163'][1]) echo '<span class="text-tips">密码留空表示不修改</span>';?>
			</div>
			<!--
			<div class="option">
			  <h3>QQ空间(已经失效)</h3>
			  <span class="text-tips">由于腾讯关闭了“<a href="http://wiki.connect.qq.com/add_one_blog" target="_blank">同步至QQ空间日志</a>”的API接口，暂时改用邮箱接口，请确保您对应的QQ已经开通邮箱，并且<a href="http://service.mail.qq.com/cgi-bin/help?subtype=1&&no=166&&id=28" target="_blank">开启了SMTP功能</a>，<strong>如果您测试过无法同步，请先关闭【IMAP/SMTP服务】，然后再打开，给QQ邮箱设置一个【独立密码】，下面的密码写的也是独立密码。如果依然无法使用，记得把帐号密码删除，谢谢。</strong></span>
			</div>
			<div class="option">
			  <h4>QQ号码: </h4>
			  <input type="text" class="inputs" name="user_qzone" size="25" value="<?php echo $wp_blog['u_qzone'][0];?>" onkeyup="value=value.replace(/[^\d]/g,'')" />
			</div>
			<div class="option">
			  <h4>密 码: </h4>
			  <input type="password" class="inputs" name="pass_qzone" size="25" autocomplete="off" /><?php if($wp_blog['u_qzone'][1]) echo '<span class="text-tips">密码留空表示不修改</span>';?>
			</div>
			<div class="option">
			  <h3>Lofter</h3>
			  <span class="text-tips">网易轻博客LOFTER：<a href="http://www.lofter.com/" target="_blank">http://www.lofter.com/</a>，采用模拟登录的方式发布文章。</span>
			</div>
			<div class="option">
			  <h4>邮 箱: </h4>
			  <input type="text" class="inputs" name="user_lofter" size="25" value="<?php echo $wp_blog['u_lofter'][0];?>" />
			</div>
			<div class="option">
			  <h4>密 码: </h4>
			  <input type="password" class="inputs" name="pass_lofter" size="25" autocomplete="off" /><?php if($wp_blog['u_lofter'][1]) echo '<span class="text-tips">密码留空表示不修改</span>';?>
			</div>
			<div class="option">
			  <h4>博客地址: </h4>
			  <span class="text-tips">http://</span><input type="text" class="inputs" name="lofterblogname" style="width:100px" value="<?php echo $wp_blog['lofterblogname'];?>" /><span class="text-tips">.lofter.com</span>
			</div>
			-->
			<div class="option">
			  <h3>人人网</h3>
			  <span class="text-tips">您可以使用默认KEY，也可以到【<a href="#tab_menu-11-key" class="gotab">自定义key</a>】填写</span>
			</div>
			<div class="option">
			  <h4>您的人人网公共主页<a href="#pageid">数字ID</a>: </h4>
			  <input type="text" class="inputs" name="renren_page_id" size="20" value='<?php echo $wp_blog['renren_page_id'];?>' onkeyup="value=value.replace(/[^\d]/g,'')" /><span class="text-tips">&nbsp;选填</span>
			  <span class="text-tips">留空则默认为个人主页</span>
			</div>
			<div class="option">
			  <p class="h4"><?php echo $bindinfo['renren'];?></p>
			</div>
			<div class="option">
			  <h3>开心网</h3>
			  <span class="text-tips">您可以使用默认KEY，也可以到【<a href="#tab_menu-11-key" class="gotab">自定义key</a>】填写</span>
			</div>
			<div class="option">
			  <p class="h4"><?php echo $bindinfo['kaixin001'];?></p>
			</div>
			<div class="option">
			  <h3>豆瓣日记</h3>
			  <span class="text-tips">您可以使用默认KEY，也可以到【<a href="#tab_menu-11-key" class="gotab">自定义key</a>】填写</span>
			</div>
			<div class="option">
			  <p class="h4"><?php echo $bindinfo['douban'];?></p>
			</div>
<!--
			<div class="option">
			  <h3>点点网</h3>
			  <span class="text-tips">您可以使用默认KEY，也可以在下面填写在<a href="http://dev.diandian.com/" target="_blank">点点网开放平台</a>申请的key。[ <a href="http://wiki.smyx.net/blog/diandian" target="_blank">申请指南</a> ]</span>
			</div>
			<div class="option">
			  <h4>自定义 Client ID: </h4>
			  <input name="appkeys[24][0]" class="inputs" type="text" value='<?php echo $wptm_key[24][0];?>' /><span class="text-tips">&nbsp;选填</span>
			</div>
			<div class="option">
			  <h4>Client Secret: </h4>
			  <input name="appkeys[24][1]" class="inputs" type="text" value='<?php echo $wptm_key[24][1];?>' /><span class="text-tips">&nbsp;选填</span>
			</div>
			<div class="option">
			  <h4>您的点点网个性域名:</h4>
			  <input type="text" class="inputs" name="diandiandomain" size="35" value='<?php echo $wp_blog['diandiandomain'];?>' /><span class="text-tips">&nbsp;必填</span>
			  <span class="text-tips">格式为 xxx.diandian.com</span>
			</div>
			<div class="option">
			  <p class="h4"><?php echo $bindinfo['diandian'];?></p>
			</div>
-->
			<div class="option">
			  <h3>Tumblr</h3>
			  <span class="text-tips">Tumblr是目前全球最大的轻博客网站，是英文站的首选平台，查看 <a href="http://www.tumblr.com" target="_blank">tumblr.com</a></span>
			  <span class="text-tips">您可以使用默认KEY，也可以在下面填写在<a href="http://www.tumblr.com/oauth/apps" target="_blank">developers</a>申请的key。</span>
			</div>
			<div class="option">
			  <h4>自定义 consumer key: </h4>
			  <input name="appkeys[25][0]" class="inputs" type="text" value='<?php echo $wptm_key[25][0];?>' /><span class="text-tips">&nbsp;选填</span>
			</div>
			<div class="option">
			  <h4>Secret Key: </h4>
			  <input name="appkeys[25][1]" class="inputs" type="text" value='<?php echo $wptm_key[25][1];?>' /><span class="text-tips">&nbsp;选填</span>
			</div>
			<div class="option">
			  <h4>您的Tumblr域名:</h4>
			  <input type="text" class="inputs" name="tumblrdomain" size="35" value='<?php echo $wp_blog['tumblrdomain'];?>' /><span class="text-tips">&nbsp;必填</span>
			  <span class="text-tips">格式为 xxx.tumblr.com</span>
			</div>
			<div class="option">
			  <p class="h4"><?php echo $blog_token['tumblr'] ? '已经绑定！ <a href="' . $plugin_url . '/login.php?go=tumblr&act=blog&del=1">解除绑定</a>' : '<a href="' . $plugin_url . '/login.php?go=tumblr&act=blog">绑定帐号</a>';?></p>
			</div>
			<div class="option">
			  <h2>注意事项</h2>
			  <p>1、新浪博客、网易博客修改文章时会同步修改对应的博客文章，而不是创建新的博客文章。<br />
				2、人人网、开心网、豆瓣日记只会同步一次，下次修改文章时不会再同步。<br />
				3、快速编辑和密码保护的文章不会同步或更新。<br />
				4、友情提示：同步过多的帐号会导致发布文章缓慢或者响应超时！
			  </p>
			  <p id="pageid"><strong>如何查看人人网公共主页数字ID</strong></p>
			  <p>比如水脉烟香的公共主页是 <a href="http://page.renren.com/601586799" target="_blank">http://page.renren.com/<strong>601586799</strong></a> ，那么601586799即为公共主页数字ID。欢迎关注我噢~~</p>
			</div>
		  </div>
		</div>
		<div id="menu-6" class="main-navigation-container">
		  <div id="tab_menu-6" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-6-share" class="tab"><span>分享设置</span></a></li>
			  <li><a href="#tab_menu-6-google" class="tab"><span>Google+1</span></a></li>
			  <li><a href="#tab_menu-6-hide" class="tab"><span>隐藏内容可见</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-6-share" class="sub-navigation-container">
			<div class="option">
			  <h4>添加按钮</h4>
			  <div class="option-check">
			    <select name="wp_share[enable_share]" class="option-select"><option value="4"<?php selected($wptm_share['enable_share'] == 4); ?>>不使用</option><option value="3"<?php selected($wptm_share['enable_share'] == 3); ?> >文章前面</option><option value="1"<?php selected($wptm_share['enable_share'] == 1); ?> >文章末尾</option><option value="2"<?php selected($wptm_share['enable_share'] == 2); ?> >调用函数</option></select>
			    <span class="text-tips">自定义函数： &lt;?php wp_social_share();?&gt;</span>
			    <a href="http://wiki.smyx.net/wordpress/share#调用函数" target="_blank" class="help-btn" title="打开新窗口"></a>
			  </div>
			</div>
			<div class="option">
			  <h4>添加到</h4>
			  <select name="wp_share[add]" class="option-select"><option value="1"<?php selected($wptm_share['add'] == 1);?>>所有页面</option><option value="2"<?php selected($wptm_share['add'] == 2);?>>首页</option><option value="3"<?php selected(!$wptm_share['add'] || $wptm_share['add'] == 3);?> >文章页和页面</option><option value="4"<?php selected($wptm_share['add'] == 4);?> >文章页</option><option value="5"<?php selected($wptm_share['add'] == 5);?> >页面</option></select>
			</div>
			<div class="option">
			  <h4>选择按钮</h4>
			  <select name="wp_share[button]" class="option-select"><option value="1"<?php selected($wptm_share['button'] == 1); ?>>图标按钮</option><option value="2"<?php selected($wptm_share['button'] == 2); ?> >图文按钮</option><option value="3"<?php selected($wptm_share['button'] == 3); ?> >文字按钮</option></select>
			</div>
			<div class="option">
			  <h4>选择图标尺寸</h4>
			  <select name="wp_share[size]" class="option-select"><option value="16" <?php selected($wptm_share['size'] == 16);?>>小图标</option><option value="32" <?php selected($wptm_share['size'] == 32);?>>大图标</option></select>
			</div>
			<div class="option">
			  <h4>分享内容设置</h4>
			  <select name="wp_share[content]" class="option-select">
				<option value="1"<?php selected($wptm_share['content'] == 1);?>>标题 + 链接</option>
				<option value="0"<?php selected(!$wptm_share['content']);?>>标题 + 摘要/内容 + 链接</option>
				<option value="2"<?php selected($wptm_share['content'] == 2);?>>文章摘要/内容 + 链接</option>
			  </select>
			</div>
			<div class="option">
			  <h4>分享按钮前面的文字:</h4>
			  <input name="wp_share[text]" class="inputs" type="text" value="<?php echo (!$wptm_share) ? '分享到：' : $wptm_share['text'];?>" />
			</div>
			<div class="option">
			  <h4>不显示分享按钮的文章ID</h4>
			  <input name="wp_share[ids]" class="inputs" type="text" size="60" value="<?php echo $wptm_share['ids'];?>" /><span class="text-tips">多个用英文逗号(,)分开</span>
			</div>
			<div class="option">
			  <h4>划词分享</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[selection]" type="hidden" value="<?php echo $wptm_share['selection']; ?>" />
			  <span class="text-tips">在文章页面选中任何一段文本可以点击按钮分享到QQ空间、新浪微博、腾讯微博。</span>
			</div>
			<div class="option">
			  <h4>使用插件自带share.css文件</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[css]" type="hidden" value="<?php if(!$wptm_share || $wptm_share['css']) echo 1; ?>" />
			  <span class="text-tips">如果有修改样式，建议添加样式到主题css文件中，以免升级时被覆盖！</span>
			</div>
			<div class="option">
			  <h4>使用 Google Analytics 跟踪社会化分享按钮的使用效果</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[analytics]" type="hidden" value="<?php echo $wptm_share['analytics']; ?>" />
			  <a href="http://wiki.smyx.net/wordpress/share#ga" target="_blank" class="help-btn" title="打开新窗口"></a>
			</div>
			<div class="option">
			  <h4>配置 Google Analytics 文件ID:</h4>
			  <input type="text" class="inputs" name="wp_share[id]" value="<?php echo $wptm_share['id'];?>" />
			</div>
			<div class="option">
			  <h4>微信二维码API (选填):</h4>
			  <input type="text" class="inputs" name="wp_share[qrcode_api]" value="<?php echo $wptm_share['qrcode_api'];?>" />
			  <span class="text-tips">必须加参数: <code>%url%</code> 表示文章URL<br />默认API是: <code>http://s.jiathis.com/qrcode.php?url=%url%</code></span>
			</div>
			<div class="option">
			  <h3>添加社会化分享按钮，可以上下左右拖拽排序 (记得保存！)</h3>
			  <ul id="dragbox">
			    <?php wp_social_share_options();?>
			    <div class="clear"></div>
			  </ul>
			  <div id="dragmarker">
			    <img src="<?php echo $plugin_url;?>/images/marker_top.gif">
			    <img src="<?php echo $plugin_url;?>/images/marker_middle.gif" id="dragmarkerline">
			    <img src="<?php echo $plugin_url;?>/images/marker_bottom.gif">
			  </div>
			  <input type="hidden" id="formdrag" name="wp_share[select]" />
			</div>
		  </div>
		  <div id="tab_menu-6-google" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“Google+1”功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[enable_plusone]" type="hidden" value="<?php echo $wptm_share['enable_plusone']; ?>" />
			  <span class="text-tips">提示: Google+1在国内使用不稳定，如果发现网站打开速度变慢，请关闭该功能。</span>
			</div>
			<div class="option">
			  <h4>添加按钮</h4>
			  <select name="wp_share[plusone]" class="option-select"><option value="4"<?php selected($wptm_share['plusone'] == 1); ?>>文章前面</option><option value="2"<?php selected(!$wptm_share['plusone'] || $wptm_share['plusone'] == 2); ?> >文章末尾</option><option value="3"<?php selected($wptm_share['plusone'] == 3); ?> >调用函数</option></select>
			  <span class="text-tips">自定义函数： &lt;?php wp_google_plusone();?&gt;</span>
			</div>
			<div class="option">
			  <h4>添加到</h4>
			  <select name="wp_share[plusone_add]" class="option-select"><option value="1"<?php selected($wptm_share['plusone_add'] == 1);?>>所有页面</option><option value="2"<?php selected($wptm_share['plusone_add'] == 2);?>>首页</option><option value="3"<?php selected($wptm_share['plusone_add'] == 3);?> >文章页和页面</option><option value="4"<?php selected(!$wptm_share['plusone_add'] || $wptm_share['plusone_add'] == 4);?> >文章页</option><option value="5"<?php selected($wptm_share['plusone_add'] == 5);?> >页面</option></select>
			</div>
			<div class="option">
			  <h4>选择尺寸</h4>
			  <select name="wp_share[plusone_size]" class="option-select"><option value="small"<?php selected($wptm_share['plusone_size'] == 'small');?>>小（15 像素）</option><option value="medium"<?php selected($wptm_share['plusone_size'] == 'medium');?> >中（20 像素）</option><option value="standard"<?php selected(!$wptm_share['plusone_size'] || $wptm_share['plusone_size'] == 'standard');?> >标准（24 像素）</option><option value="tall"<?php selected($wptm_share['plusone_size'] == 'tall');?> >高（60 像素）</option></select>
			</div>
			<div class="option">
			  <h4>包含计数</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[plusone_count]" type="hidden" value="<?php echo $wptm_share['plusone_count']; ?>" />
			</div>
		  </div>
		  <div id="tab_menu-6-hide" class="sub-navigation-container">
			<div class="option">
			  隐藏文章的部分或者全部内容，用户通过登录、回复、分享等行为后才能显示隐藏的内容。管理员和文章作者默认为可见。
			</div>
			<div class="option">
			  <h4>开启“隐藏内容可见”功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wp_share[enable_hide]" type="hidden" value="<?php echo !isset($wptm_share['enable_hide']) || $wptm_share['enable_hide']; ?>" />
			</div>
			<div class="option">
			  <h4>分享平台</h4>
			  <div class="option-check">
				<label class="label_check"><input name="wp_share[share][sina]" type="checkbox" value="1" <?php checked(!$wptm_share['share'] || $wptm_share['share']['sina']);?> /> 新浪微博</label>
				<label class="label_check"><input name="wp_share[share][qq]" type="checkbox" value="1" <?php checked(!$wptm_share['share'] || $wptm_share['share']['qq']);?> /> 腾讯微博</label>
				<label class="label_check"><input name="wp_share[share][douban]" type="checkbox" value="1" <?php checked($wptm_share['share']['douban']);?> /> 豆瓣</label>
				<label class="label_check"><input name="wp_share[share][twitter]" type="checkbox" value="1" <?php checked($wptm_share['share']['twitter']);?> /> Twitter</label>
				<label class="label_check"><input name="wp_share[share][facebook]" type="checkbox" value="1" <?php checked($wptm_share['share']['facebook']);?>/> Facebook</label>
				<label class="label_check"><input name="wp_share[share][linkedin]" type="checkbox" value="1" <?php checked($wptm_share['share']['linkedin']);?>/> LinkedIn</label>
			  </div>
			  <span class="text-tips">选择一个或者多个您想要让用户分享的平台</span>
			</div>
			<div class="option">
			  <h4>使用方法</h4>
			  <span class="text-tips"><p>登录可见： <code>[hide l=1]隐藏的内容[/hide]</code> (l 是login(登录)的第一个字母)</p>
			  <p>回复可见： <code>[hide r=1]隐藏的内容[/hide]</code> (r 是reply(回复)的第一个字母)</p>
			  <p>分享可见： <code>[hide s=1]隐藏的内容[/hide]</code> (s 是share(分享)的第一个字母)</p>
			  <p>以上三种也可以任意组合使用，比如登录并分享可见： <code>[hide l=1 s=1]隐藏的内容[/hide]</code></p>
			  </span>
			</div>
			<div class="option">
			  <h4>自定义样式<span class="text-tips"><p>隐藏时的class:<code>.hideContent</code></p><p>显示时的class:<code>.showContent</code></p></span></h4>
			  <textarea name="wp_share[share_style]" class="option-textarea" cols="180" rows="4"><?php echo ($wptm_share['share_style']) ? $wptm_share['share_style'] : '.hideContent{text-align:center;border:1px dashed #FF9A9A;padding:8px;margin:10px auto;color:#F00; line-height:24px;}.showContent{border:1px dashed #FF9A9A;}';?></textarea>
		    </div>
		  </div>
		</div>
		<div id="menu-9" class="main-navigation-container">
		  <div id="tab_menu-9" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-9-data" class="tab"><span>数据统计</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-9-data" class="sub-navigation-container">
			<div class="option">
			  <h4>开启“社交数据统计”的功能</h4>
			  <div class="on-off"><span></span></div>
			  <input name="wptm_connect[enable_data]" type="hidden" value="<?php if(!$wptm_connect || $wptm_connect['enable_data']) echo 1; ?>" />
			  <span class="text-tips">数据包括注册、登录、同步评论、登录绑定、解除登录绑定、分享(不是分享按钮)等</span>
			</div>
			<?php if ($wptm_connect['enable_data']) { ?>
			<div class="option">
			  <h4><a href="admin.php?page=wp-connect-log">进入“数据统计”页面</a></h4>
			</div>
			<?php } ?>
		  </div>
		</div>
		<div id="menu-7" class="main-navigation-container">
		  <?php if (isset($wptm_weixin['token'])) : ?>
		  <div id="tab_menu-7" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-7-weixin" class="tab"><span>微信设置</span></a></li>
			  <li><a href="#tab_menu-7-sendweibo" class="tab"><span>发微博</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-7-weixin" class="sub-navigation-container">
			<div class="option">
			  建议使用<a href="https://wptao.com/wechat.html" target="_blank">连接微信</a>插件，功能增加了后台自定义回复、消息记录和数据分析等。专业版用户可以优惠购买！<br />
			  可以让关注您微信公众平台的用户搜索您网站的文章，您也可以自定义关键词回复。
			</div>
			<div class="option">
			  <h4>URL：</h4><?php echo $plugin_url . '/weixin.php';?>
			</div>
			<div class="option">
			  <h4>Token：</h4>
			  <input name="weixin_token" class="inputs" type="text" value="<?php echo $wptm_weixin['token'] ? $wptm_weixin['token'] : substr(md5($wpurl),8,16);?>" />
			</div>
			<div class="option">
			  <h4>被关注时显示的欢迎词</h4>
			  <textarea name="weixin_welcome" class="option-textarea" cols="60" rows="2"><?php echo ($wptm_weixin['welcome']) ? stripslashes($wptm_weixin['welcome']) : '你好，欢迎关注'. get_bloginfo('name') . '！';?></textarea>
		    </div>
			<div class="option">
			  <h4>设置一张默认图片</h4>
			  <input name="weixin_picurl" class="inputs" type="text" size="60" value="<?php echo $wptm_weixin['picurl'];?>" />
			  <span class="text-tips">http:// 开头的图片URL</span>
			</div>
			<div class="option">
			  <h2>使用指南</h2>
			  <p>1、打开<a href="http://mp.weixin.qq.com/" target="_blank">微信公众平台</a> ，点击右上角的“注册”。<br />
				2、登录QQ后，填写信息，其中第一项“帐号名称”，尽量取得好听写，支持中文噢。（注册后不可修改）<br />
				3、选择“普通公众帐号类型”<br />
				4、注册完成后，您可以在“设置”里面，看到您的微信号和二维码，建议把二维码下载下来并上传到您的网站，放在醒要位置。<br />
				5、点击菜单栏的“高级功能”，先进入"编辑模式"，点击“关闭”。之后进入“开发模式“，点击“开启”，再点击下面的“成为开发者”，在“接口配置信息”里面填写上面提示的URL和Token。<br />
				6、发挥您的聪明才智，让更多的人关注你的微信吧！“微信公众平台”的其他玩法请自己摸索。<br />
			  </p>
			</div>
			<div class="option">
			  <h2>“自定义关键字回复”</h2>
			  <p>目前已经定义的关键词：最新文章 —— 返回10条最新文章，输入其他内容将搜索您的wordpress文章，并返回结果。</p>
			  <p><a href="http://www.smyx.net/wp-connect-weixin-custom-reply.html" target="_blank">点击这里查看详细的自定义回复文档</a></p>
			</div>
		  </div>
		  <div id="tab_menu-7-sendweibo" class="sub-navigation-container">
			<?php 
				$weixin_start_weibo = ifab($wptm_weixin['start_weibo'], '@');
				$weixin_user = get_option('weixin_user');
			?>
			<div class="option">
			  管理员可以使用微信发布内容到微博/SNS，请在“同步微博”-“绑定微博”页面绑定帐号。
			</div>
			<div class="option">
			  <h3>指令说明：</h3>
			  <span class="text-tips">发送 /help 将返回指令小贴士。所有指令只作用于绑定的“私人微信”。</span>
			</div>
			<div class="option">
			  <h4>绑定私人微信，用于发布微博</h4>
			  <?php
				if ($weixin_user['weixin']) {
					echo '<span class="text-tips"><strong>已绑定</strong>，解除绑定请用微信发送 /soff</span>';
				} elseif ($weixin_user['verification'] && time() - $weixin_user['time'] < 3600) {
					echo $weixin_user['verification'];
					echo ' <input type="submit" name="weixin_verify_result" value="查看结果" /><span class="text-tips">请用您的“私人微信”发送上面8位验证码给“微信公众帐号”</span>';
				} else {
					echo '<input type="submit" name="weixin_verification" class="input-btn" value="生成验证码" /><span class="text-tips">点击“生成验证码”后，会出现8位随机验证码，用您的“私人微信”发送给“微信公众帐号”</span>';
				} 
			  ?>
			</div>
			<div class="option">
			  <h4>发微博，以某个指令作为开头</h4>
			  <input name="weixin_start_weibo" class="inputs" type="text" size="30" value="<?php echo $weixin_start_weibo; ?>" />
			  <span class="text-tips">例如：<?php echo $weixin_start_weibo; ?>发条微博吧，在微博将显示“发条微博吧”</span>
			</div>
			<div class="option">
			  <h2>使用指南</h2>
			  <p><strong>如何发布图文微博？</strong></p>
			  <p>先给微信发一张图片，然后<?php echo $weixin_start_weibo; ?>加上文本内容，发布时会带上这张图片，发布后会被删除。您也可以发送 /dpic 删除这张图片。</p>
			  <p><strong>微信服务器在五秒内收不到响应会断掉连接，所以有可能不会返回发布结果，但是可能已经发布成功！</strong></p>
			</div>
		  </div>
		  <?php else : ?>
		  <div id="tab_menu-7" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-7-weixin" class="tab"><span>微信功能</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-7-weixin" class="sub-navigation-container">
			<div class="option">
			  <p>建议购买<a href="https://wptao.com/wechat.html" target="_blank">连接微信</a>插件，支持使用微信搜索Wordpress文章，wp后台添加关键字自定义回复，消息记录和数据分析等。专业版用户可以优惠购买！</p>
			</div>
		  </div>
		  <?php endif;?>
		</div>
		<div id="menu-10" class="main-navigation-container">
		  <div id="tab_menu-10" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-10-optimize" class="tab"><span>博客优化</span></a></li>
			  <li><a href="#tab_menu-10-uc" class="tab"><span>用户中心</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-10-optimize" class="sub-navigation-container">
			<div class="option">
			  <p><a href="https://wptao.com/blog-optimize.html" target="_blank">WordPress优化与增强插件-博客优化</a>插件，支持WordPress优化、功能增强、使用SMTP发邮件、CDN加速、站点地图(sitemap，包括移动sitemap)、数据库清理等。专业版用户可以优惠购买！</p>
			</div>
		  </div>
		  <div id="tab_menu-10-uc" class="sub-navigation-container">
			<div class="option">
			  <p><a href="https://wptao.com/wp-user-center.html" target="_blank">WordPress 用户中心</a>插件，是一个增强用户粘度、分析用户喜好、激励用户，提高网站活跃的辅助型插件。目前功能包括前台注册登录、前台个人资料、每日签到、积分(包括积分商城)、收藏、赞、踩、推广、我的文章等。</p>
			</div>
		  </div>
		</div>
		<div id="menu-1" class="main-navigation-container">
		  <div id="tab_menu-1" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-1-main" class="tab"><span>授权设置</span></a></li>
			  <li><a href="#tab_menu-1-server" class="tab"><span>环境检查</span></a></li>
			  <li><a href="#tab_menu-1-time" class="tab"><span>时间校正</span></a></li>
			  <li><a href="#tab_menu-1-export" class="tab"><span>导入导出设置</span></a></li>
			  <li><a href="#tab_menu-1-uninstall" class="tab"><span>卸载插件</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-1-main" class="sub-navigation-container">
			<div class="option">
			  <h3>插件授权</h3>
			  <div class="custom-option1">
				<?php if ($is_network) { // WPMU
					echo '<span class="text-tips">该插件已经被 《<a href="' . get_site_option('siteurl') . '" target="_blank">' . get_site_option('site_name') . '</a>》授权允许在整个网络使用，插件提供者: <a href="https://wptao.com/wp-connect.html" target="_blank">水脉烟香</a>。</span>';
				} else {
					echo '<h4>填写插件授权码：</h4><input type="text" class="inputs" name="authorize_code" size="50" value="' . $authorize['authorize_code'] . '" /> ' . $code_yes;
					if (is_multisite()) echo '<span class="text-tips">您正在使用WPMU，您可以在“管理网络”的插件页面“整个网络启用“Wordpress连接微博”，然后在 管理网络 -> 设置 -> <a href="' . admin_url('network/settings.php?page=wp-connect') . '">Wordpress连接微博</a> 填写插件“根域名”授权码。<a href="https://wptao.com/wp-connect.html" target="_blank">如何获得根域名授权码</a></span>';
				}?>
			  </div>
			</div>
			<div class="option">
			  <h4>选择语言</h4>
			  <select name="wptm_options[lang]" class="option-select">
			    <option value=""<?php selected($wptm_options['lang'] == '');?>>站点默认语言</option>
				<option value="zh_CN"<?php selected($wptm_options['lang'] == 'zh_CN');?>>简体中文</option>
				<option value="zh_TW"<?php selected($wptm_options['lang'] == 'zh_TW');?>>繁體中文</option>
				<option value="en_US"<?php selected($wptm_options['lang'] == 'en_US');?>>English (United States)</option>
			  </select>
			</div>
			<div class="option">
			  <h3>数据库表</h3>
			  <span class="text-tips">本插件会自动添加数据库表:<p><code><?php echo $wpdb->prefix . 'connect_log';?></code> ( 数据统计 )</p><p><code><?php echo $wpdb->base_prefix . 'connect_user';?></code> ( 社交数据 )</p>备份数据库时不要忘记噢。</span>
			</div>
			<?php if ( class_exists( 'DBCacheReloaded' ) ) { ?>
			<div class="option">
			  <h3>其他</h3>
			  <span class="text-tips">检测到您安装了插件：<code>DB Cache Reloaded Fix</code><p>请在【缓存过滤器】后面增加<code>|_usermeta</code> 否则前台无法实时登录。</p></span>
			</div>
			<?php } ?>
		  </div>
		  <div id="tab_menu-1-server" class="sub-navigation-container">
			<div class="option">
				<iframe allowTransparency="true" width="100%" height="660" frameborder="0" scrolling="no" src="<?php echo $plugin_url.'/check.php'?>"></iframe>
			</div>
		  </div>
		  <div id="tab_menu-1-time" class="sub-navigation-container">
			<div class="option">
			  <h3>服务器时间校正</h3>
			  <span class="text-tips">假如在使用中出错，请点击上面的 <strong>环境检查</strong>，里面有一个当前服务器时间，跟你电脑(北京时间)比对一下，看相差几分钟！如果时间不正常才需要填写。</span>
			</div>
			<div class="option">
			  比北京时间<br /><br /><select name="wptm_options[char]"><option value="-1"<?php selected($wptm_options['char'] == "-1");?>>快了</option><option value="1"<?php selected($wptm_options['char'] == "1");?> >慢了</option></select> <input name="minutes" class="inputs" type="text" size="2" value="<?php echo $wptm_options['minutes'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" /><span class="text-tips">&nbsp;&nbsp;分钟</span>
			</div>
		  </div>
		  <div id="tab_menu-1-export" class="sub-navigation-container">
			<?php 
				$export = array();
				$export['wptm_options'] = $wptm_options;
				$export['wptm_connect'] = $wptm_connect;
				$export['wptm_key'] = $wptm_key;
				$export['wptm_share'] = $wptm_share;
				$export['wptm_weixin'] = $wptm_weixin;
				$export['wp_blog_bind'] = $wp_blog_bind;
				$export['wp_blog_options'] = $wp_blog_options;
				$export = array_filter($export);
				if ($export) {
					$export = maybe_serialize($export);
				}
				if ($export) { ?>
			<div class="option">
			  <h4>备份数据，请复制出来</h4>
			  <span class="text-tips">文本框显示的是插件的所有配置，但不包括微博绑定。</span>
			  <textarea class="option-textarea" cols="180" rows="4" style="width: 500px;"><?php echo $export;?></textarea>
			</div>
			<?php } ?>
			<div class="option">
			  <h4>导入设置</h4>
			  <span class="text-tips">当你从旧网站迁移过来时，请将旧网站的“备份数据”复制到文本框。</span>
			  <textarea name="wptm_import_data" class="option-textarea" cols="180" rows="4" style="width: 500px;"></textarea>
			</div>
		  </div>
		  <div id="tab_menu-1-uninstall" class="sub-navigation-container">
			<div class="option">
			  <h4>卸载插件</h4>
			  <input type="submit" name="wptm_delete" class="input-btn" value="点击卸载" onclick="return confirm('您确定要卸载WordPress连接微博？')" />
			  <span class="text-tips">点击卸载后，会删除插件配置，如果您以后还会使用，请到插件页面“暂停插件”即可。</span>
			</div>
		  </div>
		</div>
		<div id="menu-8" class="main-navigation-container">
		  <div id="tab_menu-8" class="tab_navigation">
			<ul>
			  <li><a href="#tab_menu-8-help" class="tab"><span>关于</span></a></li>
			  <li><a href="#tab_menu-8-log" class="tab"><span>升级日志</span></a></li>
			</ul>
		  </div>
		  <div id="tab_menu-8-help" class="sub-navigation-container">
			<div class="option">
			  <h2>关于</h2>
			  <p>Wordpress连接微博 是由“水脉烟香”一人开发的Wordpress插件。插件于2011年1月20日发布第1版，目前包括免费版、专业版、基础版等。</p>
			  <p><strong>感谢400多位热心用户的捐赠和支持</strong> <a href="http://www.smyx.net/wp-connect-donate.html" target="_blank">查看名单</a></p>
			  <h2><a target="_blank" href="https://wptao.com/doc/wp-connect.html">详细文档</a></h2>
			  <h2><a target="_blank" href="https://wptao.com/question-category/wordpress">问题求助</a></h2>
			  <ul class="list">
				  <li>联系QQ: 3249892 <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes" rel="nofollow"><img border="0" src="http://wpa.qq.com/pa?p=2:3249892:42" alt="在线咨询" title="在线咨询"></a></li>
				  <li>微信号: <a target="_blank" href="https://img.wptao.com/3/small/62579065gy1fqx11pit2mj20by0bygme.jpg">wptaocom</a></li>
				  <li>新浪微博: <a target="_blank" href="http://weibo.com/smyx">@水脉烟香</a></li>
				  <li>购买插件: <a target="_blank" href="https://wptao.com">https://wptao.com</a></li>
				  <li>推广返利: <a target="_blank" href="https://wptao.com/tuiguang">https://wptao.com/tuiguang</a></li>
			  </ul>
			  <h2>产品推荐</h2>
			  <?php wptao_tuijian();?>
			</div>
		  </div>
		  <div id="tab_menu-8-log" class="sub-navigation-container">
			<div class="option">
			<h3>提醒：插件每年收取最新售价25%的升级费用，首年免费，不升级不收费，可以永久使用。</h3>
			<p><a target="_blank" href="https://wptao.com/wp-connect.html#Changelog">查看更多日志</a></p>
			</div>
		  </div>
		</div>
	  </div>
	</div>
	<div id="pexeto-footer">
	  <div id="follow-pexeto">
		<p>Follow me:</p>
		<ul>
		  <li><a target="_blank" href="http://weibo.com/smyx" title="新浪微博"><img src="<?php echo $plugin_url;?>/images/logo-weibo.png" /></a></li>
		  <li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes" title="QQ:3249892"><img src="<?php echo $plugin_url;?>/images/logo-qq.png" /></a></li>
		  <li><a href="javascript:;" id="wxqr_code"><img src="<?php echo $plugin_url;?>/images/logo-weixin.png" title="微信号:wptaocom" /></a></li>
		</ul>
		<div class="wxqr_code" style="display:none;"> <div class="code_con"> <span><img src="<?php echo $plugin_url;?>/images/wptaocom.png"></span> <b>加水脉烟香为微信好友</b> </div></div>
	  </div>
	  <input type="hidden" name="action" value="save" />
	  <input type="hidden" name="wptm_connect[regid]" value="<?php echo $wptm_connect['regid']; ?>" />
	  <input type="submit" name="wp_connect_update" class="save-button" value="<?php _e('Save Changes') ?>" onclick="saveData()" />
	</div>
</div>
</form>
<?php
}
// WPMU
add_action('network_admin_plugin_action_links_' . plugin_basename(__FILE__), 'wp_connect_network_plugin_actions');
function wp_connect_network_plugin_actions($links) {
    $new_links = array();
    $new_links[] = '<a href="settings.php?page=wp-connect">' . __('Settings') . '</a>';
	$new_links[] = '<a href="users.php?page=wp-connect-user">社交用户</a>';
    return array_merge($new_links, $links);
}
function wp_connect_network_pages() {
	add_submenu_page('settings.php', 'WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_network_admin');
	add_submenu_page('users.php', '社交用户', '社交用户', 'manage_options', 'wp-connect-user', 'wp_connect_user_do_page');
}
add_action( 'network_admin_menu', 'wp_connect_network_pages' );
function wp_connect_network_admin() {
	if (isset($_POST['update_options'])) {
		do_action('wp_connect_update_network');
		$authorize_code = trim($_POST['authorize_code']);
		if ($authorize_code) {
			if (substr($authorize_code, -4) == 'WPMU') {
				$authorizecode = substr($authorize_code, 0, -4);
				$is_wpmu = 1;
			} else {
				$authorizecode = $authorize_code;
				$is_wpmu = '';
			}
			$apikey = substr($authorizecode, 0, -32);
			$secret = substr($authorizecode, -32);
			$options = array('apikey' => $apikey, 'secret' => $secret, 'wpmu' => $is_wpmu, 'authorize_code' => $authorize_code);
			if (strpos($apikey, '.') >= 1 && wp_connect_has_bought($options)) { // 请勿修改，否则插件会出现未知错误
				$options['bought'] = 1;
			} else {
				$options['bought'] = '';
			}
			update_site_option('network_connect', $options);
		} else {
			update_site_option('network_connect', array());
		}
	}
	$options = get_site_option('network_connect');
	if ($options) {
		if (!$options['bought'] || !wp_connect_has_bought($options)) {
			global $current_site;
			echo '<div class="error"><p><strong>请填写正确的插件“根域名/WPMU”授权码。';
			if ($options['bought'] && $options['apikey'] != $current_site -> domain) {
				echo '您当前的多站点（WPMU）域名是<code>' . $current_site -> domain . '</code>，您购买的域名是<code>' . $options['apikey'] . '</code>，您需要把您当前的多站点（WPMU）域名改为<code>' . $options['apikey'] . '</code>';
				if ($options['wpmu'] && strpos($current_site -> domain, $options['apikey'])) {
					echo '，或者下面的授权码清空并保存，然后在每个站点的插件页面分别填写授权码，（此种方式不支持绑定其他域名。）';
				}
			} 
			echo '</strong></p></div>';
			$code_yes = '( x )';
		} else {
			$code_yes = '( √ )';
		} 
		$validation = validation_error('wp-connect');
		if ($validation) {
			echo '<div class="error">' . $validation . '</div>';
		} 
	} 
	?>
<style type="text/css">
/*.wptao-container{margin-top:15px}*/
.wptao-grid a{text-decoration:none}
.wptao-main{width:80%;float:left}
.wptao-sidebar{width:19%;float:right}
.wptao-sidebar ol{margin-left:10px}
.wptao-box{margin:10px 0px;padding:10px;border-radius:3px 3px 3px 3px;border-color:#cc99c2;border-style:solid;border-width:1px;clear:both}
.wptao-box.yellow{background-color:#FFFFE0;border-color:#E6DB55}
@media (max-width:782px){
.wptao-grid{display:block;float:none;width:100%}
}
</style>
<div class="wrap">
  <h2>WordPress连接微博 <code>网络设置</code></h2>
  <div id="poststuff">
    <div id="post-body">
      <div class="wptao-container">
        <div class="wptao-grid wptao-main">
          <form method="post" action="">
            <?php wp_nonce_field('network-options');?>
            <div id="group-network" class="group" style="display:block;">
              <div class="postbox">
                <h3 class="hndle">
                  <label for="title">整个网络设置</label>
                </h3>
                <div class="inside">
                  <table class="form-table">
                    <tbody>
					<tr>
					  <th scope="row">填写插件“根域名/WPMU”授权码：</span></th>
					  <td><input type="text" name="authorize_code" size="50" value="<?php echo $options['authorize_code'];?>" /> <?php echo $code_yes;?></td>
					</tr>
                    </tbody>
                  </table>
				  <p class="wptao-box">提示：所有站点以站点ID为 <strong>1</strong> 设置的“自定义key”为默认key，设置自定义key主要用于显示来源。</p>
				  <p><a href="https://wptao.com/wp-connect.html" target="_blank">如何获得授权码?</a> (请选择根域名或者WPMU)</p>
                </div>
                <!-- end of inside -->
              </div>
              <!-- end of postbox -->
            </div>
            <p class="submit">
              <input type="submit" name="update_options" class="button-primary" value="<?php _e('Save Changes') ?>">
            </p>
          </form>
        </div>
        <div class="wptao-grid wptao-sidebar">
          <div class="postbox" style="min-width:inherit;">
            <h3 class="hndle">
              <label for="title">联系作者</label>
            </h3>
            <div class="inside">
              <p>QQ群①：<a href="http://shang.qq.com/wpa/qunwpa?idkey=ad63192d00d300bc5e965fdd25565d6e141de30e4f6b714708486ab0e305f639" target="_blank">88735031</a></p>
              <p>QQ群②：<a href="http://shang.qq.com/wpa/qunwpa?idkey=c2e8566f2ab909487224c1ebfc34d39ea6d84ddff09e2ecb9afa4edde9589391" target="_blank">149451879</a></p>
              <p>QQ：<a href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes" target="_blank">3249892</a></p>
			  <p><a href="https://wptao.com/wp-connect.html" target="_blank">官方网站</a></p>
			</div>
          </div>
          <div class="postbox" style="min-width:inherit;">
            <h3 class="hndle">
              <label for="title">产品推荐</label>
            </h3>
            <div class="inside">
              <?php wptao_tuijian();?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
}