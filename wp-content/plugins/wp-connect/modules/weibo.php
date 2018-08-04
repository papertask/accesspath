<?php
/**
 * 同步微博
 * 
 * @since 1.0
 */
// 是否开启微博同步功能
add_action('publish_post', 'wp_connect_publish');
add_action('publish_page', 'wp_connect_publish');
function wp_connect_publish($post_ID) {
	global $revision, $sync_loaded, $wptm_options;
	$edit_weibodata = edit_weibodata($post_ID); // 绑定单条微博地址
	if (isset($_POST['publish_no_sync']) || $_POST['post_password'] || $revision || (isset($_POST['action']) && $_POST['action'] == 'blc_edit')) {
		return;
	}
	// wp bug
	if (!$sync_loaded) {
		$sync_loaded = array();
	} elseif (in_array($post_ID, $sync_loaded)) {
		return;
	}
	$sync_loaded[] = $post_ID;
	@ini_set("max_execution_time", 120);
	$time = time();
	$post = get_post($post_ID);
	$get_account = apply_filters('custom_weibo_accounts', '', $post);
	if ($wptm_options['post_types']) {
		if (in_array($post -> post_type, explode(',', $wptm_options['post_types']))) {
			if (!$wptm_options['list_type']) return; // 黑名单
		} else {
			if ($wptm_options['list_type']) return; // 白名单
		}
	} 
	$title = wp_replace($post -> post_title);
	$content = apply_filters('get_the_post_content', $post->post_content, $post_ID, 'weibo');
	$excerpt = $post -> post_excerpt;
	$post_author_ID = $post -> post_author;
	$post_date = strtotime($post -> post_date_gmt);
	$post_modified = strtotime($post -> post_modified_gmt);
	$post_content = wp_wrap_replace($content);
	if (!$get_account) {
		$get_account = wp_connect_get_account($post);
	} 
	$account = $get_account['account'];
	// 是否绑定了帐号
	if (!$account) {
		return;
	} 
	$sync = $get_account['sync'];
	if ($sync && is_array($sync)) {
		extract($sync, EXTR_SKIP); // $sync_option,$new_prefix,$update_prefix,$update_days,$is_author,$comment_push
		if ($edit_weibodata) {
			$update_days = '0';
		}
		if ($update_days == 0 && (isset($_REQUEST['bulk_edit']) || isset($_REQUEST['_inline_edit']))) { // 批量编辑/快速编辑 V4.1.4
			return;
		}
	} else {
		return;
	}
	// 新浪微博授权码过期检查 V1.9.22
	if (!empty($account['sina']['expires_in'])) {
		$expires = $account['sina']['expires_in'] - BJTIMESTAMP;
		if ($expires < 20 * 3600) {
			if ($multiple_authors) {
				if ($is_author) {
					if (get_current_user_id() == $post_author_ID) {
						$redirect_url = admin_url('profile.php');
					} else {
						$redirect_url = admin_url("user-edit.php?user_id=$post_author_ID");
					} 
				} else {
					$redirect_url = admin_url('profile.php');
				} 
			} else {
				$redirect_url = admin_url('admin.php?page=wp-connect');
			} 
			$into = '<a href="' . plugins_url('wp-connect') . '/login.php?go=sina&act=bind&redirect_url=' . urlencode($redirect_url) . '" target="_blank">点击这里</a>';
			if ($expires < 60) {
				return setcookie('sina_expired_' . COOKIEHASH, '您的 新浪微博授权已经过期了，刚刚发布的文章已经取消了所有微博同步，请 ' . $into . ' 更新新浪微博授权，本条提示5分钟后自动消失。[<a href="javascript:window.location.reload(true);">刷新</a>]', BJTIMESTAMP + 600, COOKIEPATH, COOKIE_DOMAIN);
			} elseif ($expires < 3600) {
				$expires_in = (int) ($expires / 60) . '分钟';
			} else {
				$expires_in = (int) ($expires / 3600) . '小时';
			} 
			setcookie('sina_expired_' . COOKIEHASH, '您的 新浪微博授权再过 ' . $expires_in . ' 就过期了，请 ' . $into . ' 重新绑定下吧，否则不能同步到新浪微博噢，本条提示5分钟后自动消失。[<a href="javascript:window.location.reload(true);">刷新</a>]', BJTIMESTAMP + 600, COOKIEPATH, COOKIE_DOMAIN);
		} 
	}
	if (isset($_COOKIE['sina_expired_' . COOKIEHASH])) {
		setcookie('sina_expired_' . COOKIEHASH, '', BJTIMESTAMP - 86400, COOKIEPATH, COOKIE_DOMAIN);
	} 
	// 是否为新发布
	if (($post -> post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft' || $_POST['original_post_status'] == 'auto-draft' || $_POST['prev_status'] == 'pending' || $_POST['original_post_status'] == 'pending')) {
		$prefix = $new_prefix;
	} elseif ((($_POST['originalaction'] == "editpost") && (($_POST['prev_status'] == 'publish') || ($_POST['original_post_status'] == 'publish'))) && $post -> post_status == 'publish') { // 是否已发布
		if (isset($_POST['publish_new_sync'])) {
			$prefix = $new_prefix;
		} elseif (!isset($_POST['publish_update_sync'])) {
			if (!$_POST['sync_account']) {
				if ($update_days == 0 || ($time - $post_date < $update_days)) { // 判断当前时间与文章发布时间差
					return;
				} 
			}
		} else {
			$prefix = $update_prefix;
		} 
	} elseif (isset($_POST['_inline_edit'])) { // 是否是快速编辑
		$quicktime = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':00';
		$post_date = strtotime($quicktime);
		if ($update_days == 0 || ($time - $post_date < $update_days)) { // 判断当前时间与文章发布时间差
			return;
		} 
		$prefix = $update_prefix;
	} elseif (defined('DOING_CRON')) { // 定时发布
		$prefix = $new_prefix;
	} else { // 后台快速发布，xmlrpc等发布
		if ($post -> post_status == 'publish') {
			if ($post_modified == $post_date || $time - $post_date <= 30) { // 新文章(包括延迟<=30秒)
				$prefix = $new_prefix;
			} elseif ($update_days == 0 || ($time - $post_date < $update_days)) { // 判断当前时间与文章发布时间差
				return;
			}
		} else {
			return;
		}
	} 
	// 微博话题
	$cat_ids = $wptm_options['cat_ids'];
	$enable_cats = $wptm_options['enable_cats'];
	$enable_tags = $wptm_options['enable_tags'];
	if ($enable_cats || $cat_ids) {
		if ($postcats = get_the_category($post_ID)) {
			foreach($postcats as $cat) {
				$cat_id .= $cat -> cat_ID . ',';
				$cat_name .= $cat -> cat_name . ',';
			} 
			// 不想同步的文章分类ID
			if ($cat_ids) {
				if (wp_in_array($cat_ids, $cat_id)) {
					if (!$wptm_options['list_type']) return; // 黑名单
				} else {
					if ($wptm_options['list_type']) return; // 白名单
				}
			} 
			// 是否将文章分类当成话题
			if ($enable_cats) {
				$cats = $cat_name;
			} 
		} 
	} 
	// 是否将文章标签当成话题
	if (substr_count($cats, ',') < 2 && $enable_tags) {
		if ($posttags = get_the_tags($post_ID)) {
			foreach($posttags as $tag) {
				$tags .= $tag -> name . ',';
			} 
		} 
	} 
	$tags = $cats . $tags;
	if ($tags) {
		$tags = explode(',', rtrim($tags, ','));
		if (count($tags) == 1) {
			$tags = '#' . $tags[0] . '# ';
		} elseif (count($tags) >= 2) {
			$tags = '#' . $tags[0] . '# #' . $tags[1] . '# ';
		} 
	} 
	// 文章URL
	if ($wptm_options['enable_shorten']) { // 是否使用博客默认短网址
		$postlink = get_link_short($post);
	} else {
		$postlink = get_permalink($post_ID);
	} 
	if ($postlink && $wptm_options['new_domain']) {
		$postlink_parse = explode('/', $postlink, 4);
		$postlink = $wptm_options['new_domain'] . '/' . $postlink_parse[3];
	}
	$url = $postlink;
	if ($excerpt && !$wptm_options['disable_excerpt']) { // 是否有摘要
		$post_content = wp_replace($excerpt);
	} 
	$format = $wptm_options['format'];
	if ($format && strpos($format, '%title%') !== false) {
		$format_title = true;
		$title2 = str_replace('%title%', $title, $format);
	} else {
		$title2 = $title . ' | ';
	} 
	if ($sync_option == '2') { // 同步 前缀+标题+摘要/内容+链接
		$text = $tags . $prefix . $title2 . $post_content;
	} elseif ($sync_option == '3') { // 同步 文章摘要/内容
		$text = $tags . $prefix . $post_content;
		$url = "";
	} elseif ($sync_option == '4') { // 同步 文章摘要/内容+链接
		$text = $tags . $prefix . $post_content;
	} elseif ($sync_option == '5') { // 同步 标题 + 内容
		$text = $tags . $prefix . $title2 . $post_content;
		$url = "";
	} elseif ($sync_option == '6') { // 同步 标题
		$text = $tags . $prefix . $title2;
		$url = "";
	} else { // 同步 标题 + 链接
		$title2 = ($format_title) ? $title2 : $title;
		$text = $tags . $prefix . $title2;
	} 
	$richMedia = wp_multi_media_url($content, $post_ID);
	$list = array('title' => $title, // 标题
		'content' => $post_content, // 内容
		'postlink' => $postlink, // 链接
		'tags' => $tags, // 标签话题
		'text' => str_replace(array("[embed]", "[/embed]", $richMedia[1]), "", $text), // 同步的内容
		'url' => $url, // 同步的网址
		'richMedia' => $richMedia, // 匹配视频、图片
		'is_author' => $is_author, // 用户类型（站长 or 作者）
		'post_type' => $post->post_type, // 文章类型
		'comment_push' => $comment_push, // true - 抓取微博评论(默认)，false - 不抓取微博评论 // V3.2.1
		);
	$list = apply_filters('post_sync_weibo', $list, $post_ID, $post_author_ID); 
	// return var_dump($list);
	if (is_array($list)) {
		wp_update_list($list, $account, $post_ID);
	} 
} 

// 同步列表
function wp_update_list($list, $account, $post_id = '') {
	global $wptm_options;
	$text = $list['text'];
	$url = $list['url'];
	$value = $list['richMedia']; // $value[0] - picurl, $value[1] - videourl
	if (is_array($value)) {
		if ($value[0]) {
			$purl = $value[0]; // 图片
			$picture = array('image', $purl);
		} 
		if ($value[1]) { // 视频
			$vurl = $value[1];
		} 
	} 
	// 自定义网址
	$url = get_url_format($url, $wptm_options['t_cn']);
	// 处理完毕输出链接
	$postlink = trim($vurl . ' ' . $url);
	$status1 = $status4 = wp_status($text, trim($vurl . ' ' . $list['url']), 140); //豆瓣/网易/人人/饭否/做啥/twitter
	$status2 = $status3 = wp_status($text, $postlink, 140, 1); //新浪/腾讯/天涯/开心/人间/微博通
	$status5 = wp_status($list['content'], '', 100, 1); // QQ空间/易信朋友圈/facebook/linkedin
	// 开始同步
	require_once(WP_CONNECT_CORE_PATH . 'class/oauth2.php');
	$openkeys = custom_openkey();
	$output = array();
	if ($account['sina']) { // 新浪微博 /140*
		$ms = wp_update_t_sina($openkeys['sina'], $account['sina'], $status2, $picture, $text, $list['url'], $vurl);
		/*
		if (!$wptm_options['link_to_comment']) {
			$ms = wp_update_t_sina($openkeys['sina'], $account['sina'], $status2, $picture);
		} else {
			$ms = wp_update_t_sina($openkeys['sina'], $account['sina'], wp_status($text, '', 140, 1), $picture);
			$comment = wp_comment_t_sina($openkeys['sina'], $account['sina'], $ms['idstr'], '浏览链接-> ' . $url);
			if ($comment['idstr']) add_comment_meta($post_id, 'scid', $comment['idstr']);
		}*/
		if ($ms['error']) {
			if ($ms['error_code'] == '10017' && strpos($ms['error'], 'domain')) {
				$ms['error'] .= ' <a href="https://wptao.com/help/weibo-cannot-sync.html" target="_blank">查看错误说明</a>';
			}
			setcookie('sina_expired_' . COOKIEHASH, '新浪微博错误提示：' . $ms['error'], BJTIMESTAMP + 600, COOKIEPATH, COOKIE_DOMAIN);
		} else {
			$output['sina'] = $ms['idstr'];
		}
	} 
	if ($account['qq']) { // 腾讯微博 /140*
		$output['qq'] = wp_update_t_qq($openkeys['qq'], $account['qq'], $status2, $value);
	} 
	if ($account['sohu']) { // 搜狐微博 /+
		wp_update_t_sohu($openkeys['sohu'], $account['sohu'], $status3, $picture);
	} 
	if ($account['renren']) { // 人人网 /140
		wp_update_renren($openkeys['renren'], $account['renren'], $status1);
	} 
	if ($account['kaixin001']) { // 开心网 /140+
		wp_update_kaixin001($openkeys['kaixin001'], $account['kaixin001'], $status3, $picture);
	} 
	if ($account['douban']) { // 豆瓣 /128
		wp_update_douban($openkeys['douban'], $account['douban'], $status4, $picture);
	} 
	if ($account['yixin']) { // 易信
		$yixin['desc'] = $status5;
		$yixin['image'] = $purl;
		$yixin['url'] = $list['postlink'];
		$yixin['title'] = $list['title'];
		wp_update_yixin($openkeys['yixin'], $account['yixin'], $yixin);
	} 
	if ($wptm_options['enable_proxy']) {
		if ($account['twitter']) { // twitter /140
			$params['twitter'] = array($openkeys['twitter'], $account['twitter'], $status1, $picture);
		} 
		if ($account['facebook']) { // facebook /140+
			$facebook['description'] = $status5;
			$facebook['picture'] = $purl;
			$facebook['caption'] = $list['postlink'];
			$facebook['name'] = $list['title'];
			$facebook['link'] = $list['postlink'];
			$params['facebook'] = array($openkeys['facebook'], $account['facebook'], $facebook);
		} 
		if ($params) wp_update_proxy($params);
	} else {
		if ($account['twitter']) { // twitter /140
			wp_update_twitter($openkeys['twitter'], $account['twitter'], $status1, $picture);
		} 
		if ($account['facebook']) { // facebook /140+
			$facebook['description'] = $status5;
			$facebook['picture'] = $purl;
			$facebook['caption'] = $list['postlink'];
			$facebook['name'] = $list['title'];
			$facebook['link'] = $list['postlink'];
			wp_update_facebook($openkeys['facebook'], $account['facebook'], $facebook);
		} 
	}
	if ($account['linkedin']) { // linkedin /140+
		$linkedin = array();
		$linkedin['content'] = array();
		$linkedin['content']['title'] = $list['title'];
		$linkedin['content']['description'] = $status5;
		$linkedin['content']['submitted-image-url'] =  $purl;
		$linkedin['content']['submitted-url'] = $list['postlink'];
		wp_update_linkedin($openkeys['linkedin'], $account['linkedin'], $linkedin);
	} 
	if ($account['tianya']) { // 天涯 /140*
		wp_update_tianya($openkeys['tianya'], $account['tianya'], $status2, $picture);
	} 
/*
	if ($account['wbto']) { // 微博通 /140+
		wp_update_wbto($account['wbto'], $status3, $picture);
	} 
*/
	// 钩子，方便自定义插件
	if ($output) $output['comment_push'] = $list['comment_push']; // V3.2.1
	do_action('wp_update_list_update', $output, $ms, $post_id);
	return $output;
} 
// 保存单条微博ID
function wp_update_list_update_meta($output, $sinainfo, $post_id) {
	global $wptm_connect;
	if ($post_id && ($output['sina'] || $output['qq'])) {
		$weibodata = get_post_meta($post_id, 'weibodata', true);
		if (!$weibodata) {
			$weibodata = array();
		} elseif (isset($weibodata['sid']) || isset($weibodata['qid'])) { // 旧数据
			$weibodata = wp_connect_old_weibodata($weibodata);
		}
		if ($output['sina']) {
			$weibodata['sina'] = array();
			$weibodata['sina']['id'] = $output['sina'];
			if ($sinainfo['user']['idstr']) {
				$weibodata['sina']['uid'] = $sinainfo['user']['idstr'];
			}
		}
		if ($output['qq']) {
			$weibodata['qq'] = array();
			$weibodata['qq']['id'] = $output['qq'];
		}
		update_post_meta($post_id, 'weibodata', $weibodata); 
/*
		if ($wptm_connect['w2l'] && $output['comment_push']) { // 定时评论回推（已用其他方式代替）
			weiboToLocal_hock($post_id, time());
		} 
*/
	}
} 
add_action('wp_update_list_update', 'wp_update_list_update_meta', 10, 3);