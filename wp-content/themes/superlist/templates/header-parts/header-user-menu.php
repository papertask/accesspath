<?php $user_menu = get_theme_mod( 'superlist_general_user_menu', false ); ?>
<?php if ( ! empty( $user_menu ) ) : ?>
    <?php $is_logged_in = is_user_logged_in(); ?>

    <div class="header-user-menu <?php echo $is_logged_in ? 'logged-in' : ''; ?>">
        <div class="btn-group">
            <?php $user_id = get_current_user_id(); ?>
            <?php $image = Inventor_Post_Type_User::get_user_image( $user_id ); ?>
            <?php $name = Inventor_Post_Type_User::get_full_name( $user_id ); ?>

            <span class="nav-link dropdown-toggle" <?php if ( $is_logged_in ): ?>data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"<?php endif; ?>>
                <div class="header-user-menu-avatar" data-background-image="<?php echo esc_attr( $image ); ?>">
                    <img src="<?php echo esc_attr( $image ); ?>" alt="">
                </div><!-- /.header-user-menu-avatar-->
                <span class="header-user-menu-name">
                    <?php if ( $is_logged_in ): ?>
                        <?php echo esc_attr( $name ); ?>
                    <?php else: ?>
                        <?php $login_page = get_theme_mod( 'inventor_general_login_required_page', false ); ?>
                        <?php $url = get_permalink( $login_page ); ?>
                        <a href="<?php echo $url; ?>"><?php echo __( 'Log in', 'superlist' ); ?></a>
                    <?php endif; ?>
                </span>
            </span>

            <?php wp_nav_menu( array(
                'container_class'   => 'dropdown-menu',
                'fallback_cb'		=> '',
                'theme_location'    => 'user-menu',
            ) ); ?>
        </div>
    </div><!-- /.user-memnu -->
<?php endif; ?>