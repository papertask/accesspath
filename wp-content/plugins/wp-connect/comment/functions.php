<?php
// 评论输入框位置
if ($wptm_connect['comment_position']) { // 底部
	add_action('pinglun_footer', 'comment_position', 1);
} else { // 顶部
	add_action('pinglun_header', 'comment_position', 1);
}
// 置顶
if ($wptm_connect['comment_sticky']) {
	add_action('pinglun_bottom_right', 'pinglun_sticky', 10, 3);
}
// 评价
if ($wptm_connect['comment_rating']) {
	add_action('pinglun_bottom_right', 'pinglun_rating', 10, 1);
}
// 回复
add_action('pinglun_bottom_right', 'pinglun_reply', 10, 3);
// 日期
add_action('pinglun_bottom_left', 'pinglun_date', 10, 1);
// 评论来源，包括来自微博，移动端等
add_action('pinglun_bottom_left', 'pinglun_from', 10, 1);
// 评论用户级别，标注是否是作者的评论
add_action('pinglun_meta', 'pinglun_user_level', 10, 1);
// 登录框
add_action('pinglun_footer', 'pinglun_login', 10);
// 插入表情
add_action('pinglun_footer', 'pinglun_face');
add_action('pinglun_tool', 'pinglun_addface');
// 插入图片
if ($wptm_connect['comment_addimage']) {
	if(!CUSTOM_TAGS){
		$allowedtags['img'] = array('src' => true);
	}
	add_action('pinglun_tool', 'pinglun_addimage');
}
// 有人回复时邮件通知
if ($wptm_connect['comment_mail_notify']) {
	add_action('pinglun_tool_right', 'pinglun_mail_notify');
	add_action('comment_post', 'wp_connect_comment_mail_notify', 101);
}
function pinglun_mail_notify() {
	global $wptm_connect;
	if ($wptm_connect['comment_mail_notify'] == 2) {
		$checked = ' checked';
	}
	echo '<li class="pl-tool-mail"><input type="checkbox" name="pinglun_mail_notify" id="pinglun_mail_notify"' . $checked . ' /> <label for="pinglun_mail_notify" class="pl-mail-text">'.__('新回复邮件提醒', 'wp-connect').'</label></li>';
} 
// 允许部分文章不显示社会化评论框
add_action('post_comment_status_meta_box-options', 'wp_connect_comment_status_boxOptions');
add_action('save_post', 'wp_connect_comment_status_saveBoxOptions');
function wp_connect_comment_status_boxOptions($post) {
	$comment_status = get_option('pinglun_status');
	$postid = $post -> ID;
	$value = 1;
	if ($postid && is_array($comment_status) && in_array($postid, $comment_status)) {
		$checked = " checked='checked'";
		$value = -1;
	} 
	echo '<br /><input name="pinglun_old_status" type="hidden" value="' . $value . '" /><label for="pinglun_status" class="selectit"><input name="pinglun_status" type="checkbox" id="pinglun_status" value="' . $value . '"' . $checked . ' /> 这个页面不使用连接微博提供的评论框</label>';
} 
function wp_connect_comment_status_saveBoxOptions($postid) {
	if ($_POST['pinglun_old_status'] == $_POST['pinglun_status']) {
		if ($_POST['pinglun_status'] == 1) {
			$comment_status = get_option('pinglun_status');
			if (!is_array($comment_status)) $comment_status = array();
			if (!in_array($postid, $comment_status)) {
				$comment_status[] = $postid;
				update_option('pinglun_status', $comment_status);
			} 
		} 
	} elseif ($_POST['pinglun_old_status'] == '-1') {
		$comment_status = get_option('pinglun_status');
		if (is_array($comment_status)) {
			$key = array_search($postid, $comment_status);
			if ($key !== false) {
				unset($comment_status[$key]);
				sort($comment_status);
				update_option('pinglun_status', $comment_status);
			} 
		} 
	} 
} 
// 评论框js语言
function pinglun_language() {
	$lang = array('a' => __('请填写昵称！', 'wp-connect'),
		'b' => __('昵称长度不能大于20个字符', 'wp-connect'),
		'c' => __('请填写邮箱！', 'wp-connect'),
		'd' => __('请填写正确的邮箱！', 'wp-connect'),
		'e' => __('请填写正确的网址！', 'wp-connect'),
		'f' => __('展开回复', 'wp-connect'),
		'g' => __('收起回复', 'wp-connect'),
		'h' => __('请输入图片地址', 'wp-connect'),
		'i' => __('请输入评论内容。', 'wp-connect'),
		'j' => __('发布中...', 'wp-connect'),
		);
	return $lang;
} 
if ( defined('WP_USE_THEMES') && WP_USE_THEMES ) {
	// 评论排序 V4.2.3
	add_filter('pre_option_default_comments_page', 'wp_connect_comments_page');
	add_filter('pre_option_comment_order', 'wp_connect_comment_order');
	function wp_connect_comments_page() {
		global $wp_version;
		$meta_key = $_COOKIE['comment_order_'.COOKIEHASH];
		if ($meta_key == 'rating-up' && version_compare($wp_version, '4.4', '<')) $meta_key = '';
		return (!$meta_key || $meta_key == 'desc') ? 'newest' : 'oldest';
	} 
	function wp_connect_comment_order() {
		return $_COOKIE['comment_order_'.COOKIEHASH] == 'asc' ? 'asc' : 'desc';
	} 
}
// 替换评论框
add_filter('comments_template', 'pinglun_modules_template', 11);
function pinglun_modules_template($file) {
	global $post;
	$comment_status = get_option('pinglun_status');
	if ($comment_status && is_array($comment_status)) {
		$postid = $post->ID;
		if ($postid && in_array($postid, $comment_status)) {
			return $file;
		}
	}
	return dirname(__FILE__) . '/comments.php';
} 
// add_shortcode('wp_connect_comments', 'wp_connect_comments');
// 自定义函数
function wp_connect_comments() {
	global $post;
	return dirname(__FILE__) . '/comments.php';
} 
// 缓存插件 WP Super Cache
if (function_exists('wp_cache_post_change')) {
	// add_action('pinglun_ajax', 'wp_cache_pinglun_ajax', 10, 3);
	function wp_cache_pinglun_ajax($type, $comment_id, $post_id = 0) {
		if ($post_id) {
			wp_cache_post_change($post_id);
		} elseif ($comment_id) {
			wp_cache_get_postid_from_comment($comment_id);
		}
	} 
} 
// 评论UI
function pinglun_ui($comment, $args = array(), $depth = ''){
	global $post;
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
			<div class="pinglun-meta">
				<?php echo get_c_avatar( $comment, 36 ); ?>
				<span class="pl-author"><?php echo get_comment_author_link();?></span>
				<?php do_action('pinglun_meta', $comment, $args, $depth);?>
				<?php edit_comment_link(__('编辑', 'wp-connect'));?>
			</div>
			<?php if ( $comment->comment_approved == '0' ) : ?>
				<em class="pl-awaiting-moderation"><?php _e('您的评论正在等待审核。', 'wp-connect');?></em>
				<br />
			<?php endif; ?>
			<div class="pinglun-body"><?php comment_text(); ?></div>
			<div class="pinglun-bottom"><?php do_action('pinglun_bottom_left', $comment, $args, $depth); ?><span class="pinglun-bottom-right"><?php do_action('pinglun_bottom_right', $comment, $args, $depth); ?></span></div>
		</div>
<?php }
// 评论框
function comment_position($post_id="") {
if ( comments_open() ) : 
	global $wptm_connect, $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $overridden_cpage;
	$commenter = wp_get_current_commenter();
	// V4.2.3
	if ($wptm_connect['comment_login_icon']) {
		$login_icon = ' pl-icon-30';
		if ($wptm_connect['comment_login_icon'] == 1) {
			$login_class = 'pl-round pl-icon30';
		} elseif ($wptm_connect['comment_login_icon'] == 2) {
			$login_class = 'pl-bound pl-icon30';
		}
	} else {
		$login_icon = ' pl-icon-16';
		$login_class = 'pl-icon pl-icon';
	}
?>
<div id="pinglun-box">
<a name="respond"></a>
<!--头部 start-->
<div class="pl-head">
  <?php if (is_user_logged_in()) : ?>
  <ul class="pl-head-link">
	<li class="pl-bind-link"><a href="javascript:;" onClick="showbox('pinglun-login-area')" title="<?php _e('绑定帐号', 'wp-connect');?>"><?php _e('绑定帐号', 'wp-connect');?></a></li>
  </ul>
  <?php endif; ?>
</div>
<!--头部 end-->
<!--评论框部分 start-->
<div id="responds" class="pl-form">
  <div class="pl-form-head">
	<?php if ( !is_user_logged_in() ) : ?>
	<div class="pl-login-area<?php echo $login_icon;?>" <?php if ( !empty($commenter['comment_author']) ) echo ' style="display:none;"'?>>
	  <div class="pl-login-title"><?php _e('快捷登录', 'wp-connect');?>: </div>
	  <ul class="pl-login-list" >
		<?php echo get_login_button(array('class' => $login_class, 'number' => 4, 'more_html' => '<li><a href="javascript:;" class="pl-icon-more">'.__('更多', 'wp-connect') .'&gt;&gt;</a></li>', 'before' => '<li>', 'after' => '</li>', 'lang' => true, 'wp_login'=> $wptm_connect['wp_login_link'] ? '<li><a href="' . esc_url(wp_login_url(get_permalink())) . '" title="'.__('使用帐号/密码登录', 'wp-connect').'" class="'.$login_class.'-username">'.__('帐号登录', 'wp-connect').'</a></li>' : ''));?>
	  </ul>
	</div>
	<div id="pl-logged-user"<?php if ( empty($commenter['comment_author']) ) echo ' style="display:none;"'?>><?php printf(__('亲爱的<span>%s</span>，您好！', 'wp-connect'), $commenter['comment_author'])?> <a href="javascript:;" onClick="commentExit()"><?php _e('登出？', 'wp-connect')?></a></div>
	<?php else : ?>
	<div id="pl-logged-user"><?php printf(__('亲爱的<span>%s</span>，您好！', 'wp-connect'), sprintf('<a href="%1$s">%2$s</a>', admin_url('profile.php'), $user_identity))?> <a href="<?php echo wp_logout_url( get_permalink() );?>"><?php _e('登出？', 'wp-connect')?></a></div>
	<?php endif;?>
  </div>
  <?php echo cancel_pinglun_reply_link( '<i>' . __('取消回复', 'wp-connect') . '</i>' ); ?>
  <form action="" method="post" id="pinglun-form">
  <div class="pl-form-body">
	<div class="pl-form-avatar"><?php echo get_c_avatar( get_current_user_id(), 36 ); ?></div>
	<div class="pl-input-area">
	  <div class="pl-input-left"></div>
	  <div class="pl-textarea">
		<textarea id="pinglun-content" name="comment" cols="45" rows="3" placeholder="<?php echo $wptm_connect['comment_placeholder'];?>"></textarea>
	  </div>
	  <div class="pl-toolbar layout">
		<ul class="pl-tool layout">
		  <?php do_action('pinglun_tool'); /*插入表情、图片入口*/?>
		</ul>
		<ul class="pl-tool pl-tool-right layout">
		  <?php do_action('pinglun_tool_right');?>
		  <li class="pl-tool-sync"><?php pinglun_sync_button();?></li>
		  <li class="pl-tool-submit">
			<a href="javascript:;" id="pinglun-submit"><?php _e( '发表评论', 'wp-connect'); ?></a>
			<?php comment_id_fields(); ?>
		  </li>
		</ul>
	  </div>
	</div>
  </div>
 </form>
</div>
</div>
<!--评论框部分 end-->
<?php
endif;
}