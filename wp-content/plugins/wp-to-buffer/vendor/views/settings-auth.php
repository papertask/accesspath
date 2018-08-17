<div class="postbox panel">
    <h3 class="hndle"><?php echo sprintf( __( '%s Authentication', $this->base->plugin->name ), $this->base->plugin->account ); ?></h3>
    
	<?php
    $access_token = $this->get_setting( '', 'access_token' );
	if ( ! empty ( $access_token ) ) {
		// Already authenticated
		?>
		<div class="option">
			<?php echo sprintf( __( 'Thanks - you\'ve authorized the plugin to post updates to your %s account.', $this->base->plugin->name ), $this->base->plugin->account ); ?>
        </div>
        <div class="option">
        	<a href="admin.php?page=<?php echo $this->base->plugin->name; ?>-settings&amp;<?php echo $this->base->plugin->name; ?>-disconnect=1" class="button button-red">
				<?php _e( 'Deauthorize Plugin', $this->base->plugin->name ); ?>
			</a>
		</div>
		<?php
	} else {
		// Need to authenticate
    	?>
        <div class="option">
            <p class="description">
                <?php echo sprintf( __( 'To allow this Plugin to post updates to your %s account, please authorize it by clicking the button below.', $this->base->plugin->name ), $this->base->plugin->account ); ?>
            </p>
        </div>
    	<div class="option">
            <?php
            if ( isset( $oauth_url ) ) {
                ?>
                <a href="<?php echo $oauth_url; ?>" class="button button-primary">
                    <?php _e( 'Authorize Plugin', $this->base->plugin->name ); ?>
                </a>
                <?php
            } else {
                echo sprintf( __( 'We\'re unable to fetch the oAuth URL needed to begin authorization with %s.  Please <a href="%s" target="_blank">contact us for support</a>.', $this->base->plugin->name ), $this->base->plugin->account, $this->base->plugin->support_url );
            }
            ?>
        </div>
    	<?php
	}
	?>
</div>

<div class="postbox panel">
    <h3 class="hndle"><?php _e( 'General Settings', $this->base->plugin->name ); ?></h3>

    <div class="option">
        <div class="left">
            <strong><?php _e( 'Enable Logging?', $this->base->plugin->name ); ?></strong>
        </div>
        <div class="right">
            <input type="checkbox" name="log" value="1" <?php checked( $this->get_setting( '', 'log' ), 1 ); ?> />
        </div>
        <div class="full">
            <p class="description">
                <?php echo sprintf( __( 'If enabled, each Post will display Log information detailing what information was sent to %s, and the response received. As this dataset can be quite large, we only recommend this be enabled when troubleshooting issues.', $this->base->plugin->name ), $this->base->plugin->account ); ?>
            </p>
        </div>
    </div>
</div>