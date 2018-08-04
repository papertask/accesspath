<?php
include "../../../wp-config.php";
if (!function_exists('send_post_and_weibo')) {
	return;
}
if ($_GET['do'] == 't') {
	$_title = "发微博";
	$act = 2;
} elseif ($_GET['do'] == 'h') {
	$_title = "写文章/微博 帮助";
	$act = 3;
} else {
	$_title = "写文章";
	$act = 1;
} 
$copyright = '程序: <a href="https://wptao.com/wp-connect.html" target="_blank">WordPress连接微博</a>';
if (isset($_POST['submit'])) {
	$password = $_POST['password'];
	$send_post = send_post_and_weibo();
	$error = $send_post['text'];
	if ($send_post['ret']) {
		if ($error == -3) {
			$pwderror = 1;
		} 
	} else {
		$succeed = 1;
	} 
}

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_title;?></title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="robots" content="noindex,nofollow,noarchive">
<style type="text/css">
@-ms-viewport{width:device-width}html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,menu,input,textarea{margin:0;padding:0;border:0;vertical-align:baseline;font:inherit;font-size:100%}table{border-collapse:collapse;border-spacing:0;table-layout:fixed}s{text-decoration:none}ol,ul{list-style:none}input[type=submit],input[type=image],button{-webkit-appearance:none}hr{border:0;height:1px;color:#ccc;background-color:#ccc}body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:14px;line-height:1em;color:#333;background-color:#e8e8e8;-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%}.nojs .js-visible{visibility:hidden!important}a:link,a:active,a:visited{color:#2477b3;text-decoration:none}.button-link{border:0 none;background-color:inherit;cursor:pointer}strong{font-weight:bold}h1{line-height:1.5em;font-weight:bold}h2{font-weight:bold;padding:.75em;border-bottom:1px solid #e8e8e8}button{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif}p{line-height:22px;}#container{margin:0;padding:0;width:100%;background-color:#eee}#main_content{background-color:#fff}.username{color:#999}.username span{color:#ccc}#brand_bar{background-color:#50a7e6;line-height:28px;padding:0 .75em;_width:100%}#brand_bar table,#brand_bar td,#brand_bar tr,#brand_bar span,#brand_bar img,#brand_bar a{vertical-align:top;_vertical-align:middle}#brand_bar table{width:100%}#brand_bar td{min-height:28px}#brand_bar span{color:#fff}#brand_bar .left,#brand_bar .modal-left{font-weight:bold}#brand_bar .left{width:30%}#brand_bar .modal-left:not(#welcome-modal-left){width:70%}#brand_bar .title{font-weight:bold}.images #brand_bar .title{height:28px;display:inline-block;line-height:28px;font-size:16px;}.w-button-common{text-align:center;font-weight:bold;display:inline-block;padding:0 .75em;vertical-align:middle}.w-button-common input{line-height:2em;_line-height:1.75em;vertical-align:middle;font-weight:bold;border-width:0;border-style:none}.w-button{background-color:#eee;border:1px solid #ccc}.w-button input{background-color:#eee}.w-button-bright,a.w-button-bright:link,a.w-button-bright:active,a.w-button-bright:visited{background-color:#f7be0f;border:1px solid #f3a418;color:#000}.w-button-bright input,a.w-button-bright:link input,a.w-button-bright:active input,a.w-button-bright:visited input{background-color:#f7be0f}.toast{padding:.313em .75em;background-color:#fee091;color:#000;text-shadow:#fff 0 1px 0;word-wrap:break-word}.toast .message{vertical-align:middle;width:100%}.toast-error{background-color:#ede0df;color:#b03a32;text-shadow:#fff 0 1px 0}.sessions-page #main_content{padding:.75em;border-bottom:1px solid #ddd}.sessions-page #main_content h2{padding:0;border:0}.sessions-page #main_content .w-button-bright{display:block;margin-top:1em}.sessions-page #main_content .w-button-bright input{width:100%}.sessions-page #main_content a{font-weight:bold}.sessions-page #main_content .text-input{padding:6px 0;border:1px solid #ccc;background-color:#fff;margin:0;width:100%}.sessions-page #main_content .text-input input{border:0 none;margin:0;padding:0;border:0;display:block;width:100%;height:1.188em}.sessions-page #main_content .text-input input:focus{outline:0}.sessions-page #main_content label{line-height: 22px;}.sessions-page #main_content .input-wrapper{margin:.25em 0;}.sessions-page #brand_bar td{padding-top:.625em;padding-bottom:.625em}.toast{height:auto}@media only screen and (min-width:768px){body{font-size:.875em;line-height:1.25em;text-align:center}#container{margin:0 auto;width:520px;height:100%;border-width:0 1px 1px 1px;border-color:#ddd;border-style:solid;text-align:left}}#footer{padding:.75em;_width:100%;text-align:right;}#brand_bar a.active{color:#fff;}.q{font-weight:bold;}#wordage em{color:#690}
</style>
</head>
<body class="images nojs sessions-page sessions-new-page">
<div id="container">
<div id="brand_bar">
<table id="top"><tr><td class="modal-left"><span class="title"><a href="wap.php"<?php if ($act == 1) echo ' class="active"';?>>写文章</a> &nbsp;<a href="?do=t"<?php if ($act == 2) echo ' class="active"';?>>发微博</a> &nbsp;<a href="?do=h" <?php if ($act == 3) echo 'class="active"';?>>帮助</a></span></td></tr></table>
</div>
<?php if($error) { ?>
<div class="toast toast-error">
<div class="message"><?php echo $error;?></div>
</div>
<?php } ?>
<div id="main_content">
<div class="body">
<?php
if ($act == 1) { // 发文章
?>
<form action="" method="post">
<fieldset class="inputs">
<label for="title">标 题:</label>
<div class="input-wrapper">
<input type="text" class="text-input" name="title" autocapitalize="off" autocorrect="off" value="<?php echo (!$succeed)?$_POST['title']:'';?>" />
</div>
<label for="name">别 名（选填）</label>
<div class="input-wrapper">
<input type="text" class="text-input" name="name" autocapitalize="off" autocorrect="off" value="<?php echo (!$succeed)?$_POST['name']:'';?>" />
</div>
<label for="name">内 容:</label>
<div class="input-wrapper">
<textarea class="text-input" name="content" cols="25" rows="4"><?php echo (!$succeed)?$_POST['content']:'';?></textarea>
</div>
<label for="category">分 类</label>
<div class="input-wrapper">
<select class="text-input" name="category">
<?php
$args = array('hide_empty' => 0);
$categories = get_categories($args);
foreach ($categories as $cat) {
echo '<option value="'.$cat->cat_ID.'">'.$cat->cat_name.'</option>';
} ?>
</select>
</div>
<label for="tag">标 签（多个标签用 , 隔开）</label>
<div class="input-wrapper">
<input type="text" class="text-input" name="tag" autocapitalize="off" autocorrect="off" value="<?php echo (!$succeed)?$_POST['tag']:'';?>" />
</div>
<label for="name">密 码:</label>
<div class="input-wrapper">
<input type="password" class="text-input" name="password" autocapitalize="off" value="<?php echo (!$pwderror)?$_POST['password']:'';?>" />
</div>
</fieldset>
<span class="w-button-common w-button-bright">
<input name="submit" type="submit" value="发布" />
</span>
</form>
<?php } elseif ($act == 2) { ?>
<script type="text/javascript">
function textCounter(field,maxlimit){if(field.value.length>maxlimit){field.value=field.value.substring(0,maxlimit)}else{document.getElementById("wordage").childNodes[1].innerHTML=maxlimit-field.value.length}}
function selectall(form){for(var i=0;i<form.elements.length;i++){var box = form.elements[i];if (box.name != "chkall")box.checked = form.clickall.checked;}}
</script>
<form action="" method="post" enctype="multipart/form-data" id="tform">
<fieldset class="inputs">
<label for="title"> 新鲜事（<span id="wordage">您可以输入<em>140</em>字</span>）</label>
<div class="input-wrapper">
<textarea class="text-input" id="message" name="message" cols="25" rows="4" onblur="textCounter(this.form.message,140);" onKeyDown="textCounter(this.form.message,140);" onKeyUp="textCounter(this.form.message,140);"><?php echo (!$succeed)?$_POST['message']:'';?></textarea>
</div>
<label for="title">网址（选填）:</label>
<div class="input-wrapper">
<input type="text" class="text-input" name="weburl" autocapitalize="off" autocorrect="off" value="<?php echo (!$succeed)?$_POST['weburl']:'';?>" />
</div>
<label for="name">上传图片:</label>
<div class="input-wrapper">
<input type="file" name="pic" class="text-input" />
<input type="hidden" name="subject" value="2" />
</div>
<label for="name">密 码:</label>
<div class="input-wrapper">
<input type="password" class="text-input" name="password" autocapitalize="off" value="<?php echo (!$pwderror)?$_POST['password']:'';?>" />
</div>
<label for="name">同步到:</label>
<div class="input-wrapper">
<p><label><input type="checkbox" id="clickall" onclick="selectall(this.form);" checked />全选</label>
<?php
$weibo_sync = wp_sync_list();
foreach($weibo_sync as $key => $name) {
	$checked = (!$_POST['submit'] || $_POST[$key]) ? 'checked' : '';
	echo "<label><input name=\"$key\" type=\"checkbox\" value=\"1\" $checked />$name</label>\r\n";
}
?>
</p>
</div>
</fieldset>
<span class="w-button-common w-button-bright">
<input name="submit" type="submit" value="发布" />
</span>
</form>
<?php } elseif ($act == 3) { ?>
<p class="q">1、如何设置密码？</p>
<p>在 WordPress连接微博 插件后台“同步微博”栏目里面的“说说”项设置密码！</p>
<p class="q">2、发布文章</p>
<p>多个文章标签用半角逗号[ , ]隔开。</p>
<?php } ?>
</div>
</div>
<div id="footer"><?php echo $copyright;?></div>
</div>
</body>
</html>