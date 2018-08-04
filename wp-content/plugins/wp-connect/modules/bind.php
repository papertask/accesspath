<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && 'bind.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
$plugin_url = WP_CONNECT_URL;
$wptm_options = get_option('wptm_options');
$action = IS_PROFILE_PAGE && $user_id ? $plugin_url.'/save.php?do=profile' : '';
wp_connect_css('css/bind.css');
wp_connect_js('js/floatdialog.js', array('jquery', 'jquery-migrate'));
?>
<div class="option">
  <h3>绑定同步帐号</h3>
  <span class="text-tips">请点击下面图标绑定微博账号，绑定成功后，会在图标右下角打勾。</span>
<?php if (!$wptm_options['bind'] && $connect_plugin) {
	echo '<span class="text-tips">如果同步时要在微博显示自己的“来自XXX”，请到【<a href="#tab_menu-11-key" class="gotab">自定义key</a>】页面填写申请的key，更换APP key后，相应的帐号请重新绑定！</span>';
}?>
</div>
<br />
<div class="option">
<div id="tlist">
<?php
foreach (wp_sync_list() as $media => $name) {
	if ($account[$media]['access_token'] || $account[$media]['oauth_token']) {
		echo '<a href="javascript:;" id="bind_' . $media . '" class="' . $media . '" title="' . $name . get_bind_expires_in($account[$media]) . '"><b></b></a> ';
	} elseif ($account[$media]['password']) {
		echo '<a href="javascript:;" id="' . $media . '" class="' . $media . ' bound" title="' . $name . '"><b></b></a> ';
	} else {
		echo '<a href="javascript:;" id="' . $media . '" class="' . $media . '" title="' . $name . '"><b></b></a> ';
	} 
} 
?>
</div>
<?php
if ($wptm_options['multiple_authors'] || $wptm_options['registered_users']) {
	if ($connect_plugin) {
		if ($wptm_options['multiple_authors']) {
		    echo '<p>您已经开启了多作者博客，假如管理员只想同步自己发布的文章，请到 <a href="' . admin_url('profile.php') . '">我的资料</a> 里面绑定帐号。否则请在这里绑定 (即所有作者的文章都会同步到您绑定的微博上)。<br/>每位作者都可以自定义设置，互不干扰！</p>';
			// echo '<p>如果您需要微博评论回推，一定要在这个页面绑定新浪微博和腾讯微博，如果您不希望其他作者同步到您的微博，在同步设置那里的“同步内容设置”选择“不同步”即可，你自己可以在 <a href="' . admin_url('profile.php') . '">我的资料</a> 绑定。</p>';
		}
		echo '<p>“我的资料”页面的设置或绑定优先级最大。当管理员在资料页有绑定任何一个帐号，则这里的帐号绑定将失效。</p>';
	} else {
		if ($wptm_options['multiple_authors']) {
			echo '<p>您可以在这里绑定帐号，当您发布文章时将同步该文章的信息到您的微博上。</p>';
			echo '<p><strong>记得在上面的“同步设置”里的“文章同步设置”选择一个值。</strong></p>';
		}
		if ($wptm_options['registered_users']) {
			echo '<p>绑定帐号后，您可以登录本站，在本站的微博自定义发布页面发布信息到您绑定的帐号上。</p>';
		}
		//echo '<p><strong>请您再三确定您信任本站站长，否则导致微博等账户信息泄漏，插件开发者概不负责！</strong></p>';
	}
}
?>
<div class="dialog" id="dialog"> <a href="javascript:void(0);" class="close"></a>
<?php
if (!$connect_plugin) {
	echo '<form method="post" action="' . $action . '">';
	wp_nonce_field('dialog_options');
}
?>
<p><img src="<?php echo $plugin_url;?>/images/bind/qq.png" class="title_pic" /></p>
<table class="form-table">
<tr valign="top">
<th scope="row"><span class="token">Access token</span><span class="account">帐&nbsp;&nbsp;&nbsp;&nbsp;号</span> :</th>
<td><input type="text" class="bind_username" id="username" name="username" /></td>
</tr>
<tr valign="top">
<th scope="row"><span class="token">Token secret</span><span class="account">密&nbsp;&nbsp;&nbsp;&nbsp;码</span> :</th>
<td><input type="password" class="bind_password" id="password" name="password" /></td>
</tr>
</table>
<p class="submit">
<input type="hidden" class="media_name" name="media_name"/>
<input type="submit" name="update_token" class="button-primary update_token" value="<?php _e('Save Changes') ?>" /> &nbsp;
<input type="submit" name="button_delete" class="button-primary button_delete" value="解除绑定" onclick="return confirm('Are you sure? ')" />
</p>
<?php
if (!$connect_plugin) {
	echo '</form>';
}
?>
</div>

<div class="dialog_add" id="dialog_add"> <a href="javascript:void(0);" class="close"></a>
<?php
if (!$connect_plugin) {
	echo '<form method="post" action="' . $action . '">';
	wp_nonce_field('dialog_add');
}
?>
<p><img src="<?php echo $plugin_url;?>/images/bind/qq.png" class="title_pic" /></p>
  <p>您还没有绑定同步授权，您可以</p>
  <p>
    <input type="hidden" class="media_name" name="media_name"/>
    <input type="submit" class="button-primary" name="button_add" value="绑定" /> &nbsp;
	<input type="button" class="button-primary close" value="关闭" /> 
  </p>
<?php
if (!$connect_plugin) {
	echo '</form>';
}
?>
</div>

<div class="dialog_delete" id="dialog_delete"> <a href="javascript:void(0);" class="close"></a>
<?php
if (!$connect_plugin) {
	echo '<form method="post" action="' . $action . '">';
	wp_nonce_field('dialog_delete');
}
?>
<p><img src="<?php echo $plugin_url;?>/images/bind/qq.png" class="title_pic" /></p>
  <p>您已经绑定了同步授权，您可以</p>
  <p>
	<input type="hidden" class="media_name" name="media_name"/>
	<input type="submit" class="button-primary" name="button_add" value="更新" /> &nbsp;
    <input type="submit" class="button-primary" name="button_delete" value="解绑" onclick="return confirm('Are you sure? ')" /> &nbsp;
	<input type="button" class="button-primary close" value="关闭" /> 
  </p>
<?php
if (!$connect_plugin) {
	echo '</form>';
}
?>
</div>
</div>
<script type="text/javascript">
jQuery(".close").show();
jQuery("<?php if($wptm_options['bind']) {echo '#twitter, #qq, #sina, #sohu, #netease, #tianya,';}/* elseif($wptm_options['enable_proxy']) {echo '#twitter,';}*/?>#digu, #fanfou, #renjian, #wbto").click(function () {
  var id = jQuery(this).attr("id").replace('_porxy', '');
  jQuery(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/bind/" + id + ".png");
  jQuery('input[name="username"]').attr("id", "username_" + id);
  jQuery('input[name="password"]').attr("id", "password_" + id);
  //jQuery("#username_digu").attr("value", "<?php echo $account['digu']['username'];?>");
  //jQuery("#username_fanfou").attr("value", "<?php echo $account['fanfou']['username'];?>");
  //jQuery("#username_renjian").attr("value", "<?php echo $account['renjian']['username'];?>");
  jQuery("#username_wbto").attr("value", "<?php echo $account['wbto']['username'];?>");
  jQuery(".password").attr("value", "");
  if (id == "twitter" || id == "qq" || id == "sina" || id == "sohu" || id == "netease" || id == "tianya") {
    jQuery(".account").hide();
    jQuery(".token").show();
  } else {
    jQuery(".token").hide();
	jQuery(".account").show();
  }
  jQuery(".button_delete").hide();
  jQuery(".dialog").attr("id", "dialog_" + id);
  jQuery(".media_name").attr("value", id);
});
jQuery(".bound").click(function () {
  jQuery(".button_delete").show();
});
jQuery("#shuoshuo, #renren, #kaixin001, #douban, #facebook, #linkedin, #yixin<?php if (!$wptm_options['bind']) echo ' ,#twitter, #qq, #sina, #sohu, #netease, #tianya';?>").click(function () {
  var id = jQuery(this).attr("id");
  jQuery(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/bind/" + id + ".png");
  jQuery(".dialog_add").attr("id", "dialog_" + id);
  jQuery(".media_name").attr("value", id);
});
jQuery("#bind_shuoshuo, #bind_twitter, #bind_qq, #bind_sina, #bind_sohu, #bind_netease, #bind_douban, #bind_tianya, #bind_renren, #bind_kaixin001, #bind_facebook, #bind_linkedin, #bind_yixin").click(function () {
  var id = jQuery(this).attr("id").replace('bind_', '');
  jQuery(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/bind/" + id + ".png");
  jQuery(".dialog_delete").attr("id", "dialog_" + id);
  jQuery(".media_name").attr("value", id);
});
jQuery("#demo").floatdialog("dialog");
jQuery("#demo_add").floatdialog("dialog_add");
jQuery("#demo_delete").floatdialog("dialog_delete");
jQuery("#qq, #bind_qq").floatdialog("dialog_qq");
jQuery("#sina, #bind_sina").floatdialog("dialog_sina");
jQuery("#sohu, #bind_sohu").floatdialog("dialog_sohu");
jQuery("#netease, #bind_netease").floatdialog("dialog_netease");
jQuery("#douban, #bind_douban").floatdialog("dialog_douban");
jQuery("#tianya, #bind_tianya").floatdialog("dialog_tianya");
jQuery("#shuoshuo, #bind_shuoshuo").floatdialog("dialog_shuoshuo");
jQuery("#renren, #bind_renren").floatdialog("dialog_renren");
jQuery("#kaixin001, #bind_kaixin001").floatdialog("dialog_kaixin001");
jQuery("#twitter, #bind_twitter").floatdialog("dialog_twitter");
jQuery("#facebook, #bind_facebook").floatdialog("dialog_facebook");
jQuery("#linkedin, #bind_linkedin").floatdialog("dialog_linkedin");
jQuery("#yixin, #bind_yixin").floatdialog("dialog_yixin");
//jQuery("#digu").floatdialog("dialog_digu");
//jQuery("#fanfou").floatdialog("dialog_fanfou");
//jQuery("#renjian").floatdialog("dialog_renjian");
jQuery("#wbto").floatdialog("dialog_wbto");
jQuery('.update_token').click(function () {
  if ((jQuery(".bind_username").val() == '') || (jQuery(".bind_password").val() == '')) {
    alert("值不能为空!  ");
    return false;
  }
});
//jQuery('.wrap').click(function () {
//   jQuery('.updated').slideUp("normal");
//});
jQuery(function ($) {
   $('.show_botton').append( $('.hide_botton').html() );
   $('.hide_botton').hide();
   $('#tlist a').each(function () {
	if ($(this).attr('title').indexOf('过期')>=0) {
		$(this).addClass('expired');
	}
  });
});
</script>