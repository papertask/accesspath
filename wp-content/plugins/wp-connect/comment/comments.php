<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( !comments_open() && !get_comments_number() ) {
		return;
	} elseif ( post_password_required() ) { ?>
		<p class="pinglun-closed"><?php _e('This post is password protected. Enter the password to view comments.'); ?></p>
	<?php
		return;
	}
global $wptm_connect, $user_email;
$post_id = get_the_ID();
$pl_data = array('id' => $post_id,
	'blog_url' => get_bloginfo('url'),
	'notify' => (int)$wptm_connect['comment_mail_notify'],
	'sticky_comments' => get_post_meta($post_id, 'sticky_comments', true),
	'cookiehash' => COOKIEHASH
	);
if (!is_user_logged_in()) {
	$pl_data['require_reg'] = (bool)get_option('comment_registration');
	$pl_data['require_email'] = (bool)get_option('require_name_email');
	$pl_data['login'] = false;
	$pl_data['login_box'] = defined('WP_USER_CENTER_V') && $pl_data['require_reg'] ? '#wp-uc-login' : '#pinglun-login-area';
} else {
	$pl_data['email'] = ($user_email && !wp_connect_check_email($user_email)) ? $user_email : '';
	$pl_data['login'] = true;
	$pl_data['login_box'] = '#pinglun-login-area';
}
?>
<script type="text/javascript">
var pl_data = <?php echo json_encode($pl_data);?>;
var pl_lang = <?php echo json_encode(pinglun_language());?>;
</script>
<?php
// 如果选择模板1，对应的css文件为style1.css，以此类推！
$comment_theme = $wptm_connect['comment_theme'] ? $wptm_connect['comment_theme'] : 1;
$pinglun_url = custom_comment_theme() . '/comment';
wp_register_style('wp-connect-comment', $pinglun_url .'/css/style'.$comment_theme.'.css', array(), WP_CONNECT_VERSION);
wp_print_styles('wp-connect-comment');
wp_print_scripts('jquery');
wp_print_scripts('jquery-migrate');
echo '<script type="text/javascript" src="'.$pinglun_url .'/js/comment.php?ver='.WP_CONNECT_VERSION.'"></script>';
//wp_register_script('wp-connect-comment', $pinglun_url .'/js/comment.js', array('jquery'), WP_CONNECT_VERSION);
//wp_print_scripts('wp-connect-comment');
if ($wptm_connect['comment_style']) {
	echo "<style type='text/css'>".$wptm_connect['comment_style']."</style>\n";
}
if ($wptm_connect['comment_top']) echo $wptm_connect['comment_top']; // V4.2.5
?>
<!--评论start-->
<div id="pinglun-container" class="pinglun-container" data-id="<?php the_ID();?>">
<!--评论头部 start-->
<div id="pinglun-header">
<a name="comments"></a>
<a name="reviews"></a>
<?php do_action('pinglun_header', $post->ID); ?>
<!--评论信息 start-->
<div class="pl-info layout">
   <div class="pl-info-count"><?php printf(__('已有 <strong>%d</strong> 条评论', 'wp-connect'), get_comments_number());?> <?php echo pinglun_weibo_link();?></div>
   <div class="pl-order">
	 <a href="javascript:;" onclick="pl_order('desc')" title="<?php _e('最新', 'wp-connect');?>" class="pl-order-desc"><?php _e('最新', 'wp-connect');?></a>
	 <a href="javascript:;" onclick="pl_order('asc')" title="<?php _e('最早', 'wp-connect');?>" class="pl-order-asc"><?php _e('最早', 'wp-connect');?></a>
	 <a href="javascript:;" onclick="pl_order('rating-up')" title="<?php _e('最佳', 'wp-connect');?>" class="pl-order-rating-up"><?php _e('最佳', 'wp-connect');?></a>
   </div>
</div>
<!--评论信息 end-->
</div>
<!--评论头部 end-->
<!--评论列表 start-->
<ol id="pinglun-list" class="pinglun-list">
	<?php wp_list_comments( array( 'callback' => 'pinglun_ui') ); ?>
</ol>
<!--评论列表 end-->
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
<!--评论分页 start-->
<div class="pinglun-page">
	<?php paginate_comments_links(array('prev_text'=>__('&laquo; 上一页'), 'next_text'=>__('下一页 &raquo;')));?>
</div>
<!--评论分页 end-->
<?php endif;?>
<!--评论关闭-->
<?php
if ( !comments_open() && get_comments_number() ) : ?>
<p class="pinglun-closed"><?php _e('评论已关闭。', 'wp-connect')?></p>
<?php endif; ?>
<?php do_action('pinglun_footer', $post->ID); ?>
</div>
<!--评论end-->
<?php if ($wptm_connect['comment_bottom']) echo $wptm_connect['comment_bottom'];?>