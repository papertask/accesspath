<?php
header("Content-type: text/html; charset=utf-8");
include "../../../wp-config.php";
if (is_user_logged_in()) {
	$user_id = get_current_user_id();
	if ( get_connect_openid('weixin', $user_id) ||  get_connect_openid('wechat', $user_id)) {
		$is_success = true;
	} 
} else {
	return;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php bloginfo('name'); ?> &rsaquo; 绑定结果</title>
<meta name="viewport" content="width=device-width" />
</head>
<body>
<?php
if ($is_success) {
	echo '绑定成功！进入<a href="' . get_bloginfo('url') . '">网站首页</a> 或者 <input type="button" value="关闭" onclick="WeixinJSBridge.call(\'closeWindow\');" />';
} else {
	echo '绑定失败！<input type="button" value="关闭" onclick="WeixinJSBridge.call(\'closeWindow\');" />';
} 
?>
</body>
</html>