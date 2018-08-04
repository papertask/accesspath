<?php
/**
 * @since 4.5
 */

include "../../../wp-config.php";
@session_start();

$user = wp_connect_get_cookie("user");
$errors = wp_connect_add_reg($user); // modules/connect.php

?>
<!DOCTYPE html>
<!--[if IE 8]>
	<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
	<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php bloginfo('name'); ?> &rsaquo; 登录</title>
<?php
if ( wp_is_mobile() ) {
	$bodyclass = ' mobile';
	echo '<meta name="viewport" content="width=device-width" />';
}
if ( version_compare($wp_version, '3.9', '<') ) {
	wp_admin_css( 'wp-admin', true );
	wp_admin_css( 'colors-fresh', true );
	wp_admin_css( 'ie', true );
} else {
	wp_admin_css( 'login', true );
}
do_action( 'login_head' );
?>
</head>
<body class="login wp-core-ui<?php echo $bodyclass;?>">
<div id="login">
<h1><a href="<?php bloginfo('url'); ?>/" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a></h1>
<?php include('login.inc.php');?>
<p id="nav"><a href="<?php bloginfo('url'); ?>/" title="<?php esc_attr_e('Are you lost?') ?>"><?php printf(__('&larr; Back to %s'), get_bloginfo('title', 'display' )); ?></a> | <a href="https://wptao.com/wp-connect.html" target="_blank" title="程序：WordPress连接微博">连接微博</a></p>
</div>
</body>
</html>