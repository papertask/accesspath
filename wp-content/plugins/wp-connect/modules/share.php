<?php
/**
 * 社会化分享按钮
 * 
 * @since 1.5
 */
// V3.6.5
function wp_connect_share_wechat_html($url, $text = '') {
	global $wptm_share;
	if ($wptm_share['qrcode_api'] && strpos($wptm_share['qrcode_api'], '%url%')) {
		$qrurl = strtr($wptm_share['qrcode_api'], array('%url%' => $url));
	} else {
		// $qrurl = 'http://qr.liantu.com/api.php?w=180&m=1&text=' . $url;
		$qrurl = 'https://bshare.optimix.asia/barCode?site=weixin&url=' . $url;
	} 
	return '<a href="javasrcipt:;" onClick=\'var wcs=document.getElementById("wcs-image");if(!wcs.innerHTML) wcs.innerHTML="<im"+"g s"+"rc=\"' . $qrurl . '\" alt=\"二维码加载失败\" width=180 height=180 />";document.getElementById("wp-connect-share-wechat").style.display="block";return false\' class="wechat" title="分享到微信" rel="nofollow" >' . $text . '</a><div id="wp-connect-share-wechat" style="display:none"><div class="masking"></div><table><tr><td class="col"><div class="wcs-box"><span class="wcs-close" onClick="document.getElementById(\'wp-connect-share-wechat\').style.display=\'none\'" title="关闭">×</span><div class="wcs-body"><div class="wcs-title">分享到微信朋友圈:</div><div id="wcs-image"></div><div align="center">打开微信，点击右上角的<strong>十</strong>，<br />使用<strong>扫一扫</strong>打开网页后，<br />点击右上角<strong>···</strong>可分享到朋友圈。</div></div></div></td></tr></table></div>';
} 

function wp_social_share($number = '') {
	echo wp_social_share_button($number);
} 

function wp_socialshare($number = '') {
	echo strip_tags(wp_social_share_button($number), '<a>');
} 
// V3.6.9
function wp_share_button($select = '', $button = 1, $size = 16, $css = '') {
	if ($select) $select = array('select' => $select, 'button' => $button, 'size' => $size, 'css' => $css, 'analytics' => '');
	echo wp_social_share_button($select);
} 
function wp_social_share_add($content) {
	if (!in_the_loop()) return $content;
	global $wptm_share;
	if ($wptm_share['add'] == 1) {
		$add = 1;
	} elseif ($wptm_share['add'] == 2) {
		if (is_home()) $add = 2;
	} elseif (is_singular()) {
		if ($wptm_share['add'] == 3) {
			$add = 3;
		} elseif ($wptm_share['add'] == 4) {
			if (is_single()) $add = 4;
		} elseif ($wptm_share['add'] == 5) {
			if (is_page()) $add = 5;
		} 
		if ($wptm_share['selection']) {
			$content = "<div id=\"ContainerArea\">\n$content\n</div>";
		} 
	} 
	if (!empty($add)) {
		$share_button = wp_social_share_button();
		if ($share_button) {
			if ($wptm_share['enable_share'] == 1) {
				$content .= '<br />' . $share_button;
			} elseif ($wptm_share['enable_share'] == 3) {
				$content = $share_button . '<br />' . $content;
			} 
		}
	} 
	return $content;
} 

// 添加到文章前面或者末尾
if ($wptm_share['selection'] || $wptm_share['enable_share'] == 1 || $wptm_share['enable_share'] == 3) {
	add_action('the_content', 'wp_social_share_add', 12);
} 
// 选择文本分享 V1.5
if ($wptm_share['selection']) {
	add_action('wp_footer', 'wp_selection_share');
} 
function wp_selection_share() {
	if (is_singular()) {
		global $post;
		if (function_exists('wp_connect_has_bought')) {
			if (!wp_connect_has_bought()) {
				return;
			} 
		} else {
			return;
		} 
		$plugin_url = WP_CONNECT_URL;
		$post_id = $post -> ID;
		$post_url = urlencode(apply_filters('wp_connect_post_link', get_permalink($post_id), $post_id, 2));
		$pic = get_image_by_post($post -> post_content, $post_id);
		$openkey = custom_openkey();
		$key_qq = $openkey['qq'][0];
		$key_sina = $openkey['sina'][0];
		echo <<<EOT
<!-- 选择文本内容分享 来自 WordPress连接微博 插件 -->
<span id="t_btn_share" style="display:none; position:absolute; cursor:pointer;">
<a href="javascript:;" rel="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=$post_url&desc=[sharetxt]&pics=$pic[0]"><img title="将选中内容分享到QQ空间" src="{$plugin_url}/images/share/share_qzone.gif"></a>
<a href="javascript:;" rel="http://service.weibo.com/share/share.php?title=[sharetxt]&appkey=$key_sina&pic=$pic[0]&url=$post_url"><img title="将选中内容分享到新浪微博" src="{$plugin_url}/images/share/share_sina.gif"></a>
</span>
EOT;
wp_connect_js('js/share.js');
	} 
} 

function wp_social_share_js() {
	global $wptm_share;
	$siteurl = get_bloginfo('url');
	$id = $wptm_share['id'];
	?>
<script type="text/javascript">
  function social_share(post_id, share) {
	  <?php if ($id) { ?>
      _gaq.push(["_trackEvent", "ShareSocial", "Share", share, 1]);
	  <?php }?>
      window.open('<?php echo $siteurl;?>/?share=' + share + ',' + post_id, share, "width=600,height=400,left=150,top=100,scrollbar=no,resize=no");
  }
<?php if ($id) { ?>
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $id;?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
<?php } ?>
</script>
<?php } 

function sociables_google_analytics() {
	$share = explode(",", $_GET['share']);
	$get_the_title = get_the_title($share[1]);
	$urls = wp_social_share_url($share[1]);
	if (empty($get_the_title)) {
	} else {
		foreach($urls as $key => $url) {
			if ($share[0] == $key) {
				do_action('save_post_share', $share);
				header("Location: $url");
				die();
			} 
		} 
	} 
} 
// 使用 Google Analytics V1.3
if ($wptm_share['analytics']) {
	add_action('wp_head', 'wp_social_share_js');
	add_action('template_redirect', 'sociables_google_analytics');
} 
// Google+1
if ($wptm_share['enable_plusone']) {
	// Google+1 V1.3.3
	function wp_google_plusone($content = '') {
		if (!in_the_loop()) return $content;
		global $wptm_share;
		$post_link = rawurlencode(get_permalink());
		$size = $wptm_share['plusone_size'];
		if ($wptm_share['plusone_count']) {
			$button = "<g:plusone size=\"$size\" href=\"$post_link\"></g:plusone>";
		} else {
			$button = "<g:plusone size=\"$size\" count=\"false\" href=\"$post_link\"></g:plusone>";
		} 
		if ($wptm_share['plusone'] == 1) {
			$content = $button . '<br />' . $content;
		} elseif ($wptm_share['plusone'] == 2) {
			$content = $content . "<br />" . $button;
		} elseif ($wptm_share['plusone'] == 3) {
			echo $button;
		} 

		if ($wptm_share['plusone'] == 1 || $wptm_share['plusone'] == 2) {
			if ($wptm_share['plusone_add'] == 1) {
				return $content;
			} elseif (is_home() && $wptm_share['plusone_add'] == 2) {
				return $content;
			} elseif (is_singular() && $wptm_share['plusone_add'] == 3) {
				return $content;
			} elseif (is_single() && $wptm_share['plusone_add'] == 4) {
				return $content;
			} elseif (is_page() && $wptm_share['plusone_add'] == 5) {
				return $content;
			} else {
				return $content;
			} 
		} 
	} 

	function wp_google_plusone_button_js() {
		global $wptm_share;
		$js = "<script type=\"text/javascript\" src=\"https://apis.google.com/js/plusone.js\">{lang: 'zh-CN'}</script>\n";
		if ($wptm_share['plusone_add'] == 1 || $wptm_share['plusone'] == 3) {
			echo $js;
		} elseif (is_home() && $wptm_share['plusone_add'] == 2) {
			echo $js;
		} elseif (is_singular() && $wptm_share['plusone_add'] == 3) {
			echo $js;
		} elseif (is_single() && $wptm_share['plusone_add'] == 4) {
			echo $js;
		} elseif (is_page() && $wptm_share['plusone_add'] == 5) {
			echo $js;
		} 
	} 
	add_action('wp_head', 'wp_google_plusone_button_js'); 
	// 添加到文章前面或者末尾
	if ($wptm_share['plusone'] == 1 || $wptm_share['plusone'] == 2) {
		add_action('the_content', 'wp_google_plusone');
	} 
} 
