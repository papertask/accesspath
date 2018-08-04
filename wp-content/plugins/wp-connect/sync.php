<?php
/**
 * 同步函数
 */
// 支持同步的平台名称
function wp_sync_list() {
	return media_sync();
} 
// 同步微博/同步博客
if ($wptm_options['enable_wptm'] || $wp_blog_options['enable_blog']) {
	add_action('admin_menu', 'publish_custom_post_types', 100);
	function publish_custom_post_types() {
		global $wptm_options;
		if (function_exists('add_meta_box')) {
			add_meta_box('wp-connect-sidebox', '同步设置 [只对本页面有效]', 'wp_connect_sidebox', 'post', 'side', 'high');
			add_meta_box('wp-connect-sidebox', '同步设置 [只对本页面有效]', 'wp_connect_sidebox', 'page', 'side', 'high');
		} 
		if (function_exists('get_post_types')) { // 自定义文章类型
			if ($post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and')) {
				foreach($post_types as $type => $object) {
					add_meta_box('wp-connect-sidebox', '同步设置 [只对本页面有效]', 'wp_connect_sidebox', $type, 'side', 'high');
				} 
			} 
		} 
	} 
	// V4.4.3
	add_action('init', '_publish_custom_post_types', 100);
	function _publish_custom_post_types() {
		global $wptm_options, $wp_blog_options;
		if (function_exists('get_post_types')) { // 自定义文章类型
			if ($post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and')) {
				foreach($post_types as $type => $object) {
					if ($wp_blog_options['enable_blog']) {
						add_action('publish_' . $type, 'wp_blog_start_publish');
					} 
					if ($wptm_options['enable_wptm']) {
						add_action('publish_' . $type, 'wp_connect_publish');
					} 
				} 
			} 
		} 
	} 
	// 文章发布页面 面板
	function wp_connect_sidebox() {
		global $post, $wptm_options, $wp_blog_options;
		$get_account = wp_connect_get_account($post);
		$sync = $get_account['sync'];
		$post_author_ID = $sync['post_author_ID'];
		$account = $get_account['account'];
		if ($wp_blog_options['enable_blog']) {
			echo '<p><label><input type="checkbox" name="publish_no_sync_blog" value="1"';
			echo ($post -> post_status != 'publish') ? '' : ' checked';
			echo '/> 不同步到其他博客</label></p>';
		} 
		if ($wptm_options['enable_wptm']) {
			$weibourl = get_sync_weibo_url($post -> ID);
			$weibodatahtml = '<p>新浪微博单条微博URL:<input type="text" name="wpweibourl[sina]" autocomplete="off" value="' . $weibourl['sina'] . '">';
			if ($weibourl['sina']) {
				$weibodatahtml .= ' <a href="' . $weibourl['sina'] . '" target="_blank">查看</a>';
			} 
			$weibodatahtml .= '<br />腾讯微博单条微博URL:<input type="text" name="wpweibourl[qq]" autocomplete="off" value="' . $weibourl['qq'] . '">';
			if ($weibourl['qq']) {
				$weibodatahtml .= ' <a href="' . $weibourl['qq'] . '" target="_blank">查看</a>';
			} 
			echo '<script type="text/javascript">function checkSelectAll(chickId,divId,reserve){var chickId=document.getElementById(chickId);var divId=document.getElementById(divId);var input=divId.getElementsByTagName("input");var len=input.length;if(reserve){if(chickId.checked){chicked=false}else{chicked=true}}else{chicked=chickId.checked}if(len>0){var i=0;for(i=0;i<len;i++)input[i].checked=chicked}}function showHideText(divId){var text=document.getElementById(divId);if(text.style.display=="block"){text.style.display="none"}else{text.style.display="block"}}function showHideWeibo(){var weibodata=document.getElementById("weibodatahtml");if(weibodata.innerHTML==""){weibodata.innerHTML = \''.$weibodatahtml.'\'}else{weibodata.innerHTML=""}}</script>';
			if ($post -> post_status != 'publish') {
				$checked = ' checked';
				echo '<p><label><input type="checkbox" name="publish_no_sync" id="snsCheck" onclick="checkSelectAll(\'snsCheck\',\'snsCheckAll\',1);" value="1" /> 不同步到微博</label></p><p>同步到以下帐号：</p><div id="snsCheckAll">';
			} else {
				echo '<p><label><input type="checkbox" name="publish_new_sync" id="snsCheckNew" onclick="checkSelectAll(\'snsCheckNew\',\'snsCheckAll\',0);" value="1" /> 当作新文章同步微博</label></p>';
				echo '<p><label><input type="checkbox" name="publish_update_sync" id="snsCheck" onclick="checkSelectAll(\'snsCheck\',\'snsCheckAll\',0);" value="1" /> 同步到以下帐号：</label></p><div id="snsCheckAll">';
			} 
			if ($account) {
				$weibodata = get_post_meta($post -> ID, 'weibodata', true);
				if ($sync['sync_status'] == 3) {
					echo '<p style="color:red">由于您在个人资料没有【绑定同步帐号】，以下默认显示插件处绑定的帐号:</p>';
				} elseif ($sync['sync_status'] == 2) {
					echo '<p style="color:red">由于您在个人资料选择【不同步】，以下默认显示插件处绑定的帐号:</p>';
				}
				$sync_list = wp_sync_list();
				//$sync_list['shuoshuo'] = 'QQ空间动态';
				$i = 0;
				foreach($account as $key => $value) {
					$i += 1 ;
					if ($i == 4) {
						echo '<a href="javascript:;" onClick="showHideText(\'snsHideMore\')"> 更多</a><span id="snsHideMore" style="display:none">';
					} 
					echo '<label><input type="checkbox" name="sync_account[' . $key . ']" value="1"' . $checked . ' /> ' . $sync_list[$key] . get_bind_expires_in($account[$key], 2) . '</label> ';
				} 
				if ($i > 3) {
					echo '</span>';
				} 
			}
			echo '</div>';
		} 
		echo '<p>提示：保存为草稿、待审不会同步</p>';
		if ($wptm_options['enable_wptm']) {
			echo '<p><a href="javascript:;" onClick="showHideWeibo()">修改文章绑定的单条微博地址</a> <a href="javascript:;" onClick="alert(\'文章同步后，会在微博产生一条数据，从此它们确定了绑定关系，会影响到后续的同步评论和微博评论回推，当然，您也可以编辑它。编辑时请不要勾选同步，否则无效！\');return false">[？]</a></p><div id="weibodatahtml"></div>';
		}
		if ($sync['multiple_authors']) {
			if ($sync['is_author']) {
				if (get_current_user_id() == $post_author_ID) {
					echo "<a href=" . admin_url('profile.php') . " target=\"_blank\">绑定微博帐号</a>";
				} else {
					echo "<a href=" . admin_url("user-edit.php?user_id=$post_author_ID") . " target=\"_blank\">绑定微博帐号</a>";
				} 
			} else {
				echo "<a href=" . admin_url('profile.php#sync') . " target=\"_blank\">去开启同步及绑定微博帐号</a>";
			}
		} elseif (current_user_can('manage_options')) {
			echo "<a href=" . admin_url('admin.php?page=wp-connect') . " target=\"_blank\">绑定微博帐号</a>";
		} 
	} 
} 
// 腾讯微博
function wp_update_t_qq($appkey, $token, $status, $value = "") {
	if (!$token['access_token']) return;
	if ($token['media'] == 'qzone') {
		$openkeys = custom_openkey();
		$appkey = $openkeys['qzone'];
		$to = new qqconnectAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	} else {
		$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	}
	$result = $to -> update($status, $value);
	return $result['data']['id'];
} 
// 新浪微博
function wp_update_t_sina($appkey, $token, $status, $value = array(), $text = '', $url = '', $vurl = '') {
	if ($url) {
		if ($appkey[0] == '1624795996') { // 默认key
			$url_sina = explode('//', $url, 2);
			$url = 'http://smyx.net/'.(is_ssl() ? 's' : 'u').'/' . $url_sina[1]; // 安全域名跳转，不要修改域名，否则请自定义key
			$upload_url_text = wp_status($text, trim($vurl . ' '. get_url_format($url)), 140, 1);
			if (!$value[1]) $status = $upload_url_text;
		} else {
			// $upload_url_text = 1;
			global $wptm_options;
			if ($wptm_options['delete_www']) { // 去掉www.
				$url = str_replace('//www.', '//', $url);
				$status = wp_status($text, trim($vurl . ' '. get_url_format($url, 1)), 140, 1);
			}
		}
	}
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> update($status, $value, $upload_url_text);
	return $result;
} 
// QQ空间动态 (腾讯已关闭)
function wp_update_qqshare($appkey, $token, $content) {
	$o = new qqconnectAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$out = $o -> addShare($content);
	if (!is_array($out)) {
		return $out;
	} 
} 
// QQ空间说说 (腾讯已关闭)
function wp_update_shuoshuo($appkey, $token, $content, $value = "") {
	$o = new qqconnectAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$out = $o -> addTopic($content, $value);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 人人网
function wp_update_renren($appkey, $token, $status) {
	if (!$token['access_token']) return;
	$o = new renrenAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> update($status, $token['page_id']);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 开心网
function wp_update_kaixin001($appkey, $token, $status, $value = "") {
	if (!$token['access_token']) return;
	$o = new kaixinAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> update($status, $value);
	if (!is_array($out)) {
		return $out;
	} 
} 
// Twitter
function wp_update_twitter($appkey, $token, $status, $value = "") {
	global $wptm_options;
	if ($wptm_options['enable_proxy']) {
		$out = wp_update_proxy(array('twitter' => array($appkey, $token, $status, $value)));
		if ($out) {
			$out = json_decode($out, true);
			if (is_array($out)) {
				return $out['twitter'];
			}
		}
	} else {
		$to = new twitterOAuthV1($appkey[0], $appkey[1], $token['oauth_token'], $token['oauth_token_secret']);
		$result = $to -> update($status, $value);
		return $result['id_str'];
	} 
} 
// facebook
function wp_update_facebook($appkey, $token, $status) {
	if (!$token['access_token']) return;
	global $wptm_options;
	if ($wptm_options['enable_proxy']) {
		$out = wp_update_proxy(array('facebook' => array($appkey, $token, $status)));
		if ($out) {
			$out = json_decode($out, true);
			if (is_array($out)) {
				return $out['facebook'];
			}
		}
	} else {
		$o = new facebookAPP($appkey[0], $appkey[1], $token['access_token']);
		$out = $o -> update($status);
		if (!is_array($out)) {
			return $out;
		} 
	} 
} 
// linkedin
function wp_update_linkedin($appkey, $token, $content) {
	$o = new linkedinAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> update($content);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 豆瓣
function wp_update_douban($appkey, $token, $status, $value = "") {
	if (!$token['access_token']) return;
	$to = new doubanAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> update($status, $value);
	return $result['id'];
} 
// 天涯
function wp_update_tianya($appkey, $token, $status, $value = "") {
	$to = new tianyaOAuthV1($appkey[0], $appkey[1], $token['oauth_token'], $token['oauth_token_secret']);
	$result = $to -> update($status, $value);
	if (!is_array($out)) {
		return 1;
	} 
} 
// 嘀咕
function wp_update_digu($user, $status) {
	$api_url = 'http://api.minicloud.com.cn/statuses/update.json';
	$body = array('content' => $status);
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 饭否
function wp_update_fanfou($user, $status) {
	$api_url = 'http://api.fanfou.com/statuses/update.json';
	$body = array('status' => $status);
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 人间网
function wp_update_renjian($user, $status, $value = "") {
	$api_url = 'http://api.renjian.com/v2/statuses/create.json';
	$body = array();
	$body['text'] = $status;
	if ($value[0] == "image" && $value[1]) {
		$body['status_type'] = "PICTURE";
		$body['url'] = $value[1];
	} 
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// wbto
function wp_update_wbto($user, $status, $value = "") {
	$body = array();
	$body['source'] = 'wordpress';
	$body['content'] = rawurlencode($status);
	if ($value[0] == "image" && $value[1]) {
		$body['imgurl'] = $value[1];
		$api_url = 'http://wbto.cn/api/upload.json';
	} else {
		$api_url = 'http://wbto.cn/api/update.json';
	} 
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 发布到朋友圈/发给用户的某个好友
function wp_update_yixin($appkey, $token, $content, $toAccountId = '') {
	if (!$token['access_token']) return;
	$o = new yixinAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> addShare($content, $toAccountId);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 转播一条微博
function wp_repost_t_sina($appkey, $token, $sid, $text) {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> repost($sid, $text);
	return $result;
} 
function wp_repost_t_qq($appkey, $token, $sid, $text) {
	if ($token['media'] == 'qq') {
		if (!$token['access_token']) return;
		$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
		$result = $to -> repost($sid, $text);
		return $result;
	}
} 
// 回复一条微博
function wp_reply_t_sina($appkey, $token, $sid, $cid, $text) {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	if ($cid && substr($cid, 0, 1) == 't') {
		$sid = substr($cid, 1);
		$cid = '';
	} 
	$result = $to -> reply($sid, $cid, $text);
	return $result;
} 
// 对一条微博信息进行评论
function wp_comment_t_sina($appkey, $token, $sid, $text) {
	$to = new sinaweiboAPP($appkey[0], $appkey[1], $token['access_token']);
	$result = $to -> comment($sid, $text);
	return $result;
} 
function wp_comment_t_qq($appkey, $token, $sid, $text) {
	if ($token['media'] == 'qq') {
		if (!$token['access_token']) return;
		$to = new qqweiboAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
		$result = $to -> comment($sid, $text);
		return $result;
	}
} 
// proxy api V3.7
function wp_update_proxy($params) {
	$url = 'http://open.smyx.net/oauth/update.php';
	$params = array("method" => 'POST',
		"body" => http_build_query($params, '', '&')
		);
	return class_http($url, $params);
} 

/**
 * 同步日志
 */
// QQ空间
function wp_update_to_qzone($appkey, $token, $title, $content) {
	$o = new qqconnectAPP($appkey[0], $appkey[1], $token['access_token'], '', $token['openid']);
	$out = $o -> addBlog($title, $content);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 百度空间
function wp_update_to_baidu($user, $title, $content, $category='') {
	$o = new hibaiduAPP();
	return $o->addBlog($user, $title, $content, $category);
} 
// 人人网
function wp_update_to_renren($appkey, $token, $title, $content) {
	if (!$token['access_token']) return;
	$o = new renrenAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> addBlog($title, $content, $token['page_id']);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 开心网
function wp_update_to_kaixin($appkey, $token, $title, $content) {
	if (!$token['access_token']) return;
	$o = new kaixinAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> addBlog($title, $content);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 点点网
function wp_update_to_diandian($appkey, $token, $title, $content, $postid = '') {
	$token = wp_connect_refresh_token($token, 'diandian');
	if (!$token['access_token']) return;
	$o = new diandianAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> addBlog($title, $content, $token['domain'], $postid);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 豆瓣日记
function wp_update_to_douban($appkey, $token, $title, $content, $postid = '') {
	if (!$token['access_token']) return;
	$o = new doubanAPP($appkey[0], $appkey[1], $token['access_token']);
	$out = $o -> addBlog($title, $content, $postid);
	if (!is_array($out)) {
		return $out;
	} 
} 
// Lofter
function wp_update_to_lofter($user, $title, $content, $tag = '', $postid = '') {
	$o = new lofterAPP();
	return $o->addBlog($user, $title, $content, $tag, $postid);
} 
// tumblr
function wp_update_to_tumblr($appkey, $token, $title, $content, $postid = '') {
	$to = new tumblrOAuthV1($appkey[0], $appkey[1], $token['oauth_token'], $token['oauth_token_secret']);
	$out = $to -> addBlog($title, $content, $token['domain'], $postid);
	if (!is_array($out)) {
		return $out;
	} 
} 
// 自定义页面同步操作
function wp_update_page($echo = 1) {
	$wptm_options = get_option('wptm_options');
	$user_id = get_current_user_id();
	if ($user_id) {
		if ($wptm_options['registered_users']) {
			$account = wp_usermeta_account($user_id);
			if ($user_id == 1 && !$account) {
				$account = wp_option_account();
			} 
		} elseif (!empty($_POST['password'])) {
			$account = wp_option_account();
		} 
	} else {
		$account = wp_option_account();
	} 
	if (!$account) {
		if ($echo) {
			echo '你没有绑定微博帐号';
			return;
		} else {
			return '你没有绑定微博帐号。';
		}
	} 
	$content = trim(strip_tags($_POST['message']));
	$value = array();
	if (isset($_POST['url'])) {
		$value[0] = trim(stripslashes($_POST['url']));
	} elseif (isset($_FILES['pic']) && $_FILES['pic']['tmp_name']) {
		$value[0] = array($_FILES['pic']['type'], $_FILES['pic']['name'], file_get_contents($_FILES['pic']['tmp_name']));
	} else {
		$value = '';
	}
	$url = trim(stripslashes($_POST['weburl']));
	return batch_post_weibo($account, $content, $value, $url);
} 
// 说说 自定义页面 HTML
function wp_to_microblog() {
	if (!in_the_loop()) return '';
	$wptm_options = get_option('wptm_options');
	if (!$wptm_options['disable_ajax']) {
		wp_connect_js('js/page.js', array('jquery'));
	} 
	$password = $_POST['password'];
	if (!empty($_POST['message'])) {
		if (($wptm_options['page_password'] && $password == $wptm_options['page_password']) || (is_user_logged_in() && $wptm_options['registered_users'])) {
			wp_update_page();
		} else {
			$pwderror = ' style="display:inline;"';
		} 
	} 
	wp_connect_css('css/page.css');
	?>
<script type="text/javascript">
function textCounter(field,maxlimit){if(field.value.length>maxlimit){field.value=field.value.substring(0,maxlimit)}else{document.getElementById("wordage").childNodes[1].innerHTML=maxlimit-field.value.length}}
function checkSelectAll(chickId,divId,reserve){var chickId=document.getElementById(chickId);var divId=document.getElementById(divId);var input=divId.getElementsByTagName("input");var len=input.length;if(reserve){if(chickId.checked){chicked=false}else{chicked=true}}else{chicked=chickId.checked}if(len>0){var i=0;for(i=0;i<len;i++)input[i].checked=chicked}}
var wpurl = "<?php echo $wpurl;?>";
</script>
<form action="" method="post" id="tform">
  <fieldset>
    <div id="say">说说你的新鲜事
      <div id="wordage">你还可以输入 <span>140</span> 字</div>
    </div>
    <p id="v1"><textarea cols="60" rows="5" name="message" id="tongbuContent" onblur="textCounter(this.form.message,140);" onKeyDown="textCounter(this.form.message,140);" onKeyUp="textCounter(this.form.message,140);"><?php echo ($pwderror)?$_POST['message']:'';?></textarea></p>
    图片地址：<p>
    <p id="v2"><input name="url" id="tongbuUrl" size="50" type="text" /></p>
    发布到：
    <p><label><input type="checkbox" id="snsCheck" onclick="checkSelectAll('snsCheck','snsCheckAll',0);" checked /> 全选</label><span id="snsCheckAll">
<?php
$weibo_sync = wp_sync_list();
foreach($weibo_sync as $key => $name) {
	echo "<label><input name=\"$key\" id=\"$key\" type=\"checkbox\" value=\"1\" checked /> $name</label>\r\n";
}
?></span></p>
    <?php if (!is_user_logged_in() || !$wptm_options['registered_users']) {?>
    <p id="v3">密码：
    <input name="password" id="password" type="password" value="<?php echo (!$pwderror)?$_POST['password']:'';?>" /> <span<?php echo $pwderror;?>>密码错误！</span>
	</p>
	<?php } ?>
    <p><input type="submit" id="publish" value="发表" /></p>
    <p class="loading"><img src="<?php echo WP_CONNECT_URL;?>/images/loading.gif" alt="Loading" /></p>
	<p class="error">你没有绑定帐号，请到我的资料页面或者到插件页面绑定！</p>
	<p class="success">发表成功！</p>
  </fieldset>
</form>
<?php
}
add_shortcode('wp_to_microblog', 'wp_to_microblog'); //简码
?>