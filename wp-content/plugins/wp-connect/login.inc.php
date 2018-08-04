<?php
/**
 * @since 4.5
 */
if (!defined('ABSPATH')) {
	exit;
} 
?>
<div id="message" >
<?php
if ($errors) {
	echo '<p class="uc-error" id="login_error">' . $errors . '</p>';
} else {
	echo '<p class="message uc-message">';
	echo !empty($_POST['user_bind'])? '已有帐号？绑定我的帐号' : '首次登录，请先完善用户信息。 <a href="javascript:;" onClick="wptm_next()">跳过这一步</a>';
	echo '</p>';
} 
$user_login = (isset($_POST['user_login'])) ? $_POST['user_login'] : $user[0][2];
$user_email = (!isset($_POST['user_email']) && !wp_connect_check_email($user[1])) ? $user[1] : $_POST['user_email'];
$user_name = (isset($_POST['user_name'])) ? $_POST['user_name'] : $user[0][2];
$user_pass = $_POST['user_pass'];
?>
</div>
<form name="registerform" id="registerform" action="" method="post">
	<p>
		<label for="user_login"><?php _e('Username') ?><br /></label>
		<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="25" />
	</p>
	<p id="password">
		<label for="user_pass"><?php _e('Password') ?><br /></label>
		<input type="password" name="user_pass" id="user_pass" class="input" value="<?php echo esc_attr(stripslashes($user_pass)); ?>" size="25" autocomplete="off" /></label>
	</p>
	<div id="registerdiv"<?php echo !empty($_POST['user_bind'])?' style="display: none;"':"";?>>
	<p>
		<label for="user_email"><?php _e('E-mail') ?>(*)<br /></label>
		<input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($user_email)); ?>" size="25" /></label>
	</p>
	<p>
		<label for="user_name">昵称<br /></label>
		<input type="text" name="user_name" id="user_name" class="input" value="<?php echo esc_attr(stripslashes($user_name)); ?>" size="25" /></label>
	</p>
	</div>
	<br class="clear" />
	<input type="hidden" name="wp_connect_add_reg" value="1" />
	<input type="hidden" name="user_bind" id="user_bind" value="<?php echo $_POST['user_bind']; ?>" />
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary button-large" value="确定" /> <a href="javascript:;" onClick="wptm_show(this)"><?php echo !empty($_POST['user_bind'])?"没有帐号？注册帐号":"已有帐号？绑定我的帐号";?></a></p>
</form>
<script type="text/JavaScript">
function wptm_show(objN){var registerdiv = document.getElementById('registerdiv');var password = document.getElementById('password');var message = document.getElementById('message');var bind = document.getElementById('user_bind');if(registerdiv.style.display == "none"){registerdiv.style.display = "block";message.innerHTML = "<p class=\"message uc-message\">首次登录，请先完善用户信息。 <a href=\"javascript:;\" onClick=\"wptm_next()\">跳过这一步</a></p>";objN.innerText = "已有帐号？绑定我的帐号";bind.value = "";}else{registerdiv.style.display = "none";message.innerHTML = "<p class=\"message uc-message\">已有帐号？绑定我的帐号</p>";objN.innerText = '没有帐号？注册帐号';bind.value = "1";}}
function js_post(a,b){var d,e,c=document.createElement("form");c.action=a,c.method="post",c.style.display="none";for(d in b)e=document.createElement("input"),e.name=d,e.value=b[d],c.appendChild(e);return document.body.appendChild(c),c.submit(),c}
function wptm_next(){js_post("",{skip_this_step:1,wp_connect_add_reg:1})}
</script>