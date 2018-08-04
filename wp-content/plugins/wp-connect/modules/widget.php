<?php
// 登录
if ( $wptm_connect['enable_connect'] && !class_exists( 'WP_Connect_Login_Widget' ) ) {

add_action( 'widgets_init', 'wp_connect_login_load_widgets' );

// Register widget.
function wp_connect_login_load_widgets() {
	register_widget( 'WP_Connect_Login_Widget' );
}

// Widget class.
class WP_Connect_Login_Widget extends WP_Widget {
	// Widget setup.
    function __construct() {
        parent::__construct(
                'wp-connect-login-widget', '连接微博(登录)', // Name
                array('classname' => 'widget_wp_connect_login', 'description' => 'Wordpress连接微博 边栏登录按钮') // Args
        );
    }

	// outputs the content of the widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );
		$meta = $instance['meta'];
		$nick = $instance['nick'];
		$style = $instance['style'];
		if ($meta || $nick || !is_user_logged_in()) {
		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		if (!is_user_logged_in()) {
			wp_connect_button($style);
		} else {
			global $user_identity;
			$cur_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		?>
			<ul>
			<?php if ($nick) { ?>
			<li><?php printf('<span>%s</span>，您好！', sprintf('<a href="%1$s">%2$s</a>', admin_url('profile.php'), get_avatar( get_current_user_id(), 36 ). ' ' . $user_identity))?></li>
			<?php } ?>
			<?php if ($meta) { ?>
			<?php if (current_user_can('publish_posts')) { ?>
			<li><?php echo '<a href="' . admin_url() . '">' . __('Site Admin') . '</a>'; ?></li>
			<?php } ?>
			<li><?php wp_loginout($cur_url); ?></li>
			<?php } ?>
			</ul>
        <?php }
		echo $after_widget;
		}
	}

	// processes widget options to be saved
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['meta'] = $new_instance['meta'];
		$instance['nick'] = $new_instance['nick'];
		$instance['style'] = $new_instance['style'];
		return $instance;
	}

    // outputs the options form on admin
	function form( $instance ) {
		$title = esc_attr($instance['title']);
		$meta = esc_attr($instance['meta']);
		$nick = esc_attr($instance['nick']);
		$style = esc_attr($instance['style']);
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">标题：</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>">选择图标:
				<select name="<?php echo $this->get_field_name( 'style' ); ?>" id="<?php echo $this->get_field_id( 'style' ); ?>" class="widefat">
					<option value="">中图标（24px）</option>
					<option value="1"<?php selected($style == 1);?>>小图标（16px）</option>
					<option value="2"<?php selected($style == 2);?>>长图标（120*24px）</option>
					<option value="3"<?php selected($style == 3);?>>文字</option>
				</select>
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'nick' ); ?>" name="<?php echo $this->get_field_name( 'nick' ); ?>"  value="1" <?php if($nick) echo "checked "; ?> />
			<label for="<?php echo $this->get_field_id( 'nick' ); ?>">登录后显示头像昵称</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'meta' ); ?>" name="<?php echo $this->get_field_name( 'meta' ); ?>"  value="1" <?php if($meta) echo "checked "; ?> />
			<label for="<?php echo $this->get_field_id( 'meta' ); ?>">登录后显示站点管理和退出链接</label>
		</p>
	<?php
	}
}
}
// 最新评论
if (!class_exists('WP_Connect_Comment_Widget')) {
	add_action('widgets_init', 'WP_Connect_Comment_load_widgets'); 
	// Register widget.
	function WP_Connect_Comment_load_widgets() {
		register_widget('WP_Connect_Comment_Widget');
	} 
	// Widget class.
	class WP_Connect_Comment_Widget extends WP_Widget {
		// Widget setup.
		function __construct() {
			parent::__construct(
					'wp-connect-comment-widget', '连接微博(最新评论)', // Name
					array('classname' => 'widget_wp_connect_comment', 'description' => 'Wordpress连接微博 最新评论') // Args
			);
		}
		// outputs the content of the widget
		function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
			$number = $instance['number'] ? absint($instance['number']) : 5;
			$avatar = $instance['avatar'];
			$uids = $instance['uids'];
			echo $before_widget;
			if ($title)
				echo $before_title . $title . $after_title;
			wp_connect_recent_comments($number, $avatar, $uids);
			echo $after_widget;
		} 
		// processes widget options to be saved
		function update($new_instance, $old_instance) {
			$instance = $old_instance;

			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number'] = absint($new_instance['number']);
			$instance['avatar'] = absint($new_instance['avatar']);
			$instance['uids'] = strip_tags($new_instance['uids']);
			return $instance;
		} 
		// outputs the options form on admin
		function form($instance) {
			$title = isset($instance['title']) ? esc_attr($instance['title']) : '最新评论';
			$number = isset($instance['number']) ? absint($instance['number']) : 10;
			$avatar = isset($instance['avatar']) ? absint($instance['avatar']) : '';
			$uids = isset($instance['uids']) ? esc_attr($instance['uids']) : '';
			?>
		<p><label for="<?php echo $this -> get_field_id('title');?>"><?php _e('Title:');?></label>
		<input class="widefat" id="<?php echo $this -> get_field_id('title');?>" name="<?php echo $this -> get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></p>
		<p><label for="<?php echo $this -> get_field_id('number');?>"><?php _e('Number of comments to show:');?></label>
		<input id="<?php echo $this -> get_field_id('number');?>" name="<?php echo $this -> get_field_name('number');?>" type="text" value="<?php echo $number;?>" size="3" /></p>
		<p><input type="checkbox" id="<?php echo $this->get_field_id( 'avatar' ); ?>" name="<?php echo $this->get_field_name( 'avatar' ); ?>"  value="1" <?php if($avatar) echo "checked "; ?> /><label for="<?php echo $this->get_field_id( 'avatar' ); ?>"> 显示评论者头像</label></p>
		<p><label for="<?php echo $this -> get_field_id('uids');?>"> 不显示某些用户的评论，请填用户ID：</label>
		<input class="widefat" id="<?php echo $this -> get_field_id('uids');?>" name="<?php echo $this -> get_field_name('uids');?>" type="text" value="<?php echo $uids;?>" />(多个用英文逗号(,)分开)</p>
	<?php
		} 
	} 
} 
// 热门评论
if (!class_exists('WP_Connect_Top_Comment_Widget')) {
	add_action('widgets_init', 'WP_Connect_Top_Comment_load_widgets'); 
	// Register widget.
	function WP_Connect_Top_Comment_load_widgets() {
		register_widget('WP_Connect_Top_Comment_Widget');
	} 
	// Widget class.
	class WP_Connect_Top_Comment_Widget extends WP_Widget {
		// Widget setup.
		function __construct() {
			parent::__construct(
					'wp-connect-top-comment-widget', '连接微博(热门评论)', // Name
					array('classname' => 'widget-wp-connect-top-comment', 'description' => 'Wordpress连接微博 热门评论，需要开启社会化评论框，根据顶排序。') // Args
			);
		}
		// outputs the content of the widget
		function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
			$number = $instance['number'] ? absint($instance['number']) : 5;
			$avatar = $instance['avatar'];
			echo $before_widget;
			if ($title)
				echo $before_title . $title . $after_title;
			wp_connect_top_comments($number, $avatar);
			echo $after_widget;
		} 
		// processes widget options to be saved
		function update($new_instance, $old_instance) {
			$instance = $old_instance;

			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number'] = absint($new_instance['number']);
			$instance['avatar'] = absint($new_instance['avatar']);
			return $instance;
		} 
		// outputs the options form on admin
		function form($instance) {
			$title = isset($instance['title']) ? esc_attr($instance['title']) : '热门评论';
			$number = isset($instance['number']) ? absint($instance['number']) : 10;
			$avatar = isset($instance['avatar']) ? absint($instance['avatar']) : '';
			?>
		<p><label for="<?php echo $this -> get_field_id('title');?>"><?php _e('Title:');?></label>
		<input class="widefat" id="<?php echo $this -> get_field_id('title');?>" name="<?php echo $this -> get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></p>
		<p><label for="<?php echo $this -> get_field_id('number');?>"><?php _e('Number of comments to show:');?></label>
		<input id="<?php echo $this -> get_field_id('number');?>" name="<?php echo $this -> get_field_name('number');?>" type="text" value="<?php echo $number;?>" size="3" /></p>
		<p><input type="checkbox" id="<?php echo $this->get_field_id( 'avatar' ); ?>" name="<?php echo $this->get_field_name( 'avatar' ); ?>"  value="1" <?php if($avatar) echo "checked "; ?> /><label for="<?php echo $this->get_field_id( 'avatar' ); ?>"> 显示评论者头像</label></p>
	<?php
		} 
	} 
} 
// 微博秀
if ( !class_exists( 'WP_Connect_Show_Widget' ) ) {

add_action( 'widgets_init', 'wp_connect_show_load_widgets' );

// Register widget.
function wp_connect_show_load_widgets() {
	register_widget( 'WP_Connect_Show_Widget' );
}

// Widget class.
class WP_Connect_Show_Widget extends WP_Widget {
	// Widget setup.
	function __construct() {
		parent::__construct(
				'wp-connect-show-widget', '微博秀', // Name
				array('classname' => 'widget_wp_connect_show', 'description' => '微博秀') // Args
		);
	}

	// outputs the content of the widget
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$code = $instance['code'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* Display name from widget settings if one was input. */
		if ( $code )
			echo $code;

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	// processes widget options to be saved
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['code'] = $new_instance['code'];
		return $instance;
	}

    // outputs the options form on admin
	function form( $instance ) {
		$title = esc_attr($instance['title']);
		$code = esc_attr($instance['code']);
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">标题：</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'code' ); ?>">Html代码：</label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'code' ); ?>" name="<?php echo $this->get_field_name( 'code' ); ?>" cols="30" rows="5"><?php echo $code; ?></textarea>
		</p>
	<?php
	}
}
}
?>