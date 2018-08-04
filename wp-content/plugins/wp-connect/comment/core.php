<?php
/**
 * V3.4
 * 
 * @since 4.4
 */
// WooCommerce 禁掉评分 V4.2.2
if (class_exists('WC_Comments')) {
	remove_filter('preprocess_comment', array('WC_Comments', 'check_comment_rating'), 0);
}
// 插入表情
function pinglun_addface() {
	echo '<li class="pl-tool-face"><a href="javascript:;" class="pl-face-link" title="'.__('插入表情', 'wp-connect').'">'.__('表情', 'wp-connect').'</a><div id="pl-face-div"></div></li>';
} 
// 插入图片
function pinglun_addimage() {
	echo '<li class="pl-tool-image"><a href="javascript:;" class="pl-image-link" title="'.__('插入图片', 'wp-connect').'">'.__('图片', 'wp-connect').'</a></li>';
} 
// 评论时间
function pinglun_date($comment) {
	$date = strtotime($comment->comment_date);
	$time = current_time('timestamp');
	$since = abs($time - $date);
	if (floor($since / 3600)) {
		if (date('Y-m-d', $date) == date('Y-m-d', $time)) {
			$output = __('今天', 'wp-connect');
			$output .= date(__(' H:i', 'wp-connect'), $date);
		} else {
			if (date('Y', $date) == date('Y', $time)) {
				$output = date(__('n月j日 H:i', 'wp-connect'), $date);
			} else {
				$output = date(__('Y年n月j日 H:i', 'wp-connect'), $date);
			} 
		} 
	} else {
		if ($output = floor($since / 60)) {
			$output = sprintf(__('%d分钟前', 'wp-connect'), $output);
		} else {
			$output = __('刚刚', 'wp-connect');
		}
	} 
	echo '<span class="pl-date" title="' . $comment->comment_date . '">' . $output . '</span>';
} 
// 评论用户级别
function pinglun_user_level($comment) {
	global $post;
	if (!$post) return;
	$user_id = $comment->user_id;
	if ($user_id > 0 ) {
		if ($user_id === $post->post_author ) {
			echo '<span class="pl-post-author">'.__('(文章作者)', 'wp-connect').'</span>';
		}
	}
}
// 评论来源
function pinglun_from($comment) {
	$user_agent = $comment->comment_agent;
	preg_match('/(iPhone|iPad|iPod|Windows Phone|Android|BlackBerry|Opera Mini|Opera Mobi|Symbian|Mobile)/i', $user_agent, $matches);
	if ($matches[0]) {
		$mobile = strtolower($matches[0]);
		if ($mobile == 'iphone') {
			$from = array('iphone', 'iPhone');
		} elseif ($mobile == 'ipad') {
			$from = array('ipad', 'iPad');
		} elseif ($mobile == 'ipod') {
			$from = array('ipod', 'iPod');
		} elseif ($mobile == 'windows phone') {
			$from = array('windowsphone', 'Windows Phone');
		} elseif ($mobile == 'blackberry') {
			$from = array('blackberry', __('黑莓', 'wp-connect'));
		} else {
			$from = array('mobile', __('移动端', 'wp-connect'));
		} 
	} elseif ($email = $comment->comment_author_email) {
		$tmail = strstr($email, '@');
		if ($tmail == '@weibo.com') {
			$from = array('sina', __('新浪微博', 'wp-connect'));
		} elseif ($tmail == '@t.qq.com') {
			$from = array('qq', __('腾讯微博', 'wp-connect'));
		} elseif ($tmail == '@qzone.qq.com') {
			$from = array('qzone', __('QQ', 'wp-connect'));
		}
	}
	$from = apply_filters('pinglun_from', $from, $comment);
	if ($from) {
		echo '<span class="pl-from pl-icon-' . $from[0] . '">'.__('来自', 'wp-connect') . $from[1] . '</span>';
	} 
} 
// 登录框
function pinglun_login($id="") {
	if ( !is_user_logged_in() ) {
		if (!get_option('comment_registration')) {
			$commenter = wp_get_current_commenter();
		}
		// elseif (defined('WP_USER_CENTER_V')) return;
		$is_login = false;
	} else {
		$is_login = true;
	}
	global $wptm_connect, $user_ID;
?>
<!-- 登录/绑定模块 begin -->
<div id="pinglun-login-area" class="pl-popup-area" style="display:none;">
  <table>
    <tbody>
      <tr>
        <td class="col">
		<?php if ( !$is_login ) { ?>
		<div class="pl-popup-container">
            <div class="pl-popup-head">
              <p class="pl-popup-title"><?php _e('使用社交账号登录', 'wp-connect');?></p>
              <a href="javascript:;" onclick="hidebox('pinglun-login-area')" title="<?php _e('关闭', 'wp-connect');?>" class="pl-close"></a>
			</div>
            <div class="pl-popup-body">
              <ul class="pl-popup-login-list layout">
                <?php echo get_login_button(array('class' => 'pl-bound pl-icon30', 'before' => '<li>', 'after' => '</li>', 'lang' => true));?>
              </ul>
              <?php if (isset($commenter['comment_author'])) { ?>
              <div class="pl-popup-guest">
                <p class="pl-popup-guest-title"><?php _e('作为游客留言:', 'wp-connect');?></p>
                <div class="pl-popup-guest-body layout">
                  <div class="pl-popup-guest-login">
                    <p>
                      <label><?php _e('昵称', 'wp-connect');?></label>
                      <input id="comment_author" type="text" value="<?php echo $commenter['comment_author'];?>">
                    </p>
                    <p>
                      <label><?php _e('邮箱', 'wp-connect');?></label>
                      <input id="comment_email" type="text" value="<?php echo $commenter['comment_author_email'];?>">
                    </p>
                    <p>
                      <label><?php _e('网址', 'wp-connect');?></label>
                      <input id="comment_url" type="text" value="<?php echo $commenter['comment_author_url'];?>">
                    </p>
                    <p><a href="javascript:;" onclick="commentLogin();" id="pinglun-submit" hidefocus="true"><?php _e('登录', 'wp-connect');?></a></p>
                  </div>
                  <p class="pl-popup-gravata"><?php _e('支持用 <a href="http://gravatar.com/" target="_blank" rel="nofollow">Gravatar头像</a>', 'wp-connect');?><br /><br /><?php printf(__('技术支持: %s', 'wp-connect'), get_comment_copyright(2));?></p>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
		  <?php } else { ?>
		  <div class="pl-popup-container">
            <div class="pl-popup-head">
              <p class="pl-popup-title"><?php _e('绑定更多社交账号登录', 'wp-connect');?></p>
              <a href="javascript:;" onclick="hidebox('pinglun-login-area')" title="<?php _e('关闭', 'wp-connect');?>" class="pl-close"></a>
			</div>
            <div class="pl-popup-body">
              <ul class="pl-popup-login-list layout">
                 <?php echo get_login_bind(array('class' => 'pl-icon30', 'before' => '<li>', 'after' => '</li>', 'lang' => true));?>
              </ul>
              <div class="pl-popup-guest">
			  <?php _e('绑定后，图标会显示亮色并打勾，再次点击可以解绑。', 'wp-connect');?>
			  </div>
			  <?php if ($wptm_connect['comment_mail_notify']) { ?>
              <div class="pl-popup-guest pl-popup-email">
                <p class="pl-popup-guest-title"><?php _e('新回复邮件提醒', 'wp-connect');?></p>
				<?php _e('建议您去个人资料修改注册邮箱，此处邮箱仅用于提醒。', 'wp-connect');?>
                <div class="pl-popup-guest-body layout">
                  <div class="pl-popup-guest-login">
                    <p>
                      <label><?php _e('邮箱', 'wp-connect');?></label>
                      <input type="text" id="comment_email" value="<?php echo ($user_ID > 0) ? get_user_meta($user_ID, 'email', true) : '';?>" />
                    </p>
                    <p><a href="javascript:;" class="pl_setting" id="pinglun-submit" hidefocus="true"><?php _e('提交', 'wp-connect');?></a></p>
                  </div>
                </div>
              </div>
			  <?php } ?>
            </div>
		  </div>
		  <?php }?></td>
      </tr>
    </tbody>
  </table>
</div>
<!-- 登录/绑定模块 end -->
<?php }
function pinglun_face($id="") { ?>
  <!-- 表情模块 begin -->
  <div id="pinglun-face-area" class="pinglun-face-area" style="display:none;">
    <div class="pl-face-head">
      <ul class="pl-face-menu">
        <li class="pl-face-default pl-face-current" onclick="selectEmotionTab(event, 'default', this);">默认</li>
        <li class="pl-face-ali" onclick="selectEmotionTab(event, 'ali', this);">阿狸</li>
        <li class="pl-face-lang" onclick="selectEmotionTab(event, 'lang', this);">浪小花</li>
      </ul>
      <a href="javascript:;" onClick="hidebox('pinglun-face-area')" title="<?php _e('关闭', 'wp-connect');?>" class="pl-close"></a> </div>
    <div class="pl-face-list">
      <div class="pl-face-box-default"></div>
    </div>
  </div>
  <!-- 表情模块 end -->
<?php }