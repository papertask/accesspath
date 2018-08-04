<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

define( 'WP_ROCKET_ADVANCED_CACHE', true );
$rocket_cache_path = '/data/www/accesspath/wp-content/cache/wp-rocket/';
$rocket_config_path = '/data/www/accesspath/wp-content/wp-rocket-config/';

if ( file_exists( '/data/www/accesspath/wp-content/plugins/wp-rocket/inc/front/process.php' ) ) {
	include '/data/www/accesspath/wp-content/plugins/wp-rocket/inc/front/process.php';
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}