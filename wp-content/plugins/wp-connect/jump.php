<?php 
// WPMU
include "../../../wp-config.php";
$url = wp_sanitize_redirect($_GET['url']);
$to = wp_sanitize_redirect($_GET['to']);
if (!empty($url) && !empty($to)) {
	session_start();
	wp_connect_set_cookie('redirect_to', $url);
	header('location:' . $to);
} 

?>