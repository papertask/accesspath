<?php
include "../../../wp-config.php";
@session_start();

$act = isset($_GET['do']) ? $_GET['do'] : '';

if ($act == "profile") {
	if (is_user_logged_in()) {
		if (isset($_POST['button_add']) && !empty($_POST['media_name'])) {
			wp_connect_admin_init();
		} else {
			$user_id = get_current_user_id();
			wp_user_profile_update($user_id);
			header('Location:' . admin_url('profile.php'));
		} 
	} 
} elseif ($act == "page") {
	$wptm_options = get_option('wptm_options');
	$password = $_POST['password'];
	if (isset($_POST['message'])) {
		if (($wptm_options['page_password'] && $password == $wptm_options['page_password']) || (is_user_logged_in() && $wptm_options['registered_users'])) {
			wp_update_page();
		} else {
			echo 'pwderror';
		} 
	} 
} elseif ($act == "bind" && is_user_logged_in()) {
	$user = wp_connect_get_cookie("userbind");
	if ($user && $wpuid = get_uid_by_url($user[2])) {
		$connect_user = $user[0];
		if ($uid = email_exists($user[1])) {
			if ($uid == $wpuid) {
				wp_connect_clear_cookie("userbind");
				header('Location:' . $user[2]);
			} else {
				wp_update_user_email($uid, 'delete_' . $user[1]);
			} 
		} 
		$connect_user['user_id'] = $wpuid;
		update_connect_user($connect_user);
		do_action('wp_connect_user_bind', $wpuid, $connect_user);
		wp_connect_clear_cookie("userbind");
		header('Location:' . $user[2]);
	} else {
		wp_die("session已过期！请检查session路径是否正确，请返回<a href=\"" . get_bloginfo('url') . "\">首页</a>");
	} 
} 

?>