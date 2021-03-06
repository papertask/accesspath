<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-welcome-view">

	<header id="wpmudev-hustle-title" class="wpmudev-has-button">

		<h1><?php esc_attr_e( "Slide-ins Dashboard", Opt_In::TEXT_DOMAIN ); ?></h1>

		<a class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost" href="<?php echo esc_url( $new_url ); ?>"><?php esc_attr_e('New Slide-in', Opt_In::TEXT_DOMAIN); ?></a>

	</header>

	<section id="wpmudev-hustle-content">

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<div id="wph-welcome-box" class="wpmudev-box" data-nonce="<?php echo esc_attr( wp_create_nonce('hustle_new_welcome_notice') ); ?>">

					<div class="wpmudev-box-head">

						<h2><?php printf( esc_attr__('Hello there, %s', Opt_In::TEXT_DOMAIN), esc_html( $user_name ) ); ?></h2>

					</div>

					<div class="wpmudev-box-body wpmudev-box-hero">

						<div class="wpmudev-box-character" aria-hidden="true"><?php $this->render("general/characters/character-one" ); ?></div>

						<div class="wpmudev-box-content">

							<h2><?php esc_attr_e( "Promote Your Site", Opt_In::TEXT_DOMAIN ); ?></h2>

							<p><?php esc_attr_e( "You currently don't have any slide-ins. Create a new slide-in with any kind of content e.g. An advert or a promotion. You can also create slide-in for collecting your customers' emails.", Opt_In::TEXT_DOMAIN ); ?></p>

							<p><a href="<?php echo esc_url( $new_url ); ?>" class="wpmudev-button wpmudev-button-blue"><?php esc_attr_e('Create', Opt_In::TEXT_DOMAIN); ?></a></p>

						</div>

					</div>

				</div><?php // .wpmudev-box ?>

			</div><?php // .wpmudev-col ?>

		</div><?php // .wpmudev-row ?>

	</section>

	<?php $this->render( "admin/commons/footer", array() ); ?>

</main>
