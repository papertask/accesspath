<?php if ( apply_filters( 'inventor_metabox_allowed', true, 'location', get_the_author_meta('ID') ) ): ?>
    <?php $map_latitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'map_location_latitude', true );?>
    <?php $map_longitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'map_location_longitude', true );?>
    <?php $map_polygon = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'map_location_polygon', true );?>
    <?php $map_location_address = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'map_location_address', true );?>
    <?php $map_type = get_theme_mod( 'inventor_general_listing_map_type', 'ROADMAP' );?>
    <?php $map = ! empty( $map_latitude ) || ! empty ( $map_longitude ); ?>

    <?php $street_view = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view', true );?>
    <?php $inside_view = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view', true );?>
    <?php $show_directions = get_theme_mod( 'inventor_show_directions_button', true );?>

    <?php if ( class_exists( 'Inventor_Google_Map' ) && ( ! empty ( $map ) || ! empty ( $street_view ) ) ) : ?>
        <?php $default_tab = get_theme_mod( 'inventor_default_location_tab', 'MAP' ); ?>
        <?php $default_tab = $default_tab == 'STREET_VIEW' && empty( $street_view ) ? 'MAP' : $default_tab; ?>
        <?php $default_tab = $default_tab == 'INSIDE_VIEW' && empty( $inside_view ) ? 'MAP' : $default_tab; ?>
        <?php $default_tab = $default_tab == 'MAP' && empty( $map ) ? 'STREET_VIEW' : $default_tab; ?>

        <div class="listing-detail-section" id="listing-detail-section-location">
            <h2 class="page-header"><?php echo $section_title; ?></h2>

            <div class="listing-detail-location-wrapper">
                <!-- Nav tabs -->
                <ul id="listing-detail-location" class="nav nav-tabs" role="tablist">

                    <?php if ( ! empty( $map ) ) : ?>
                        <li role="presentation" class="nav-item <?php if ( $default_tab == 'MAP' ): ?>active<?php endif; ?>">
                            <a href="#simple-map-panel" aria-controls="simple-map-panel" role="tab" data-toggle="tab" class="nav-link">
                                <i class="fa fa-map"></i><?php echo __( 'Map', 'inventor' ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ( ! empty( $street_view ) ) : ?>
                        <li role="presentation" class="nav-item <?php if ( $default_tab == 'STREET_VIEW' ): ?>active<?php endif; ?>">
                            <a href="#street-view-panel" aria-controls="street-view-panel" role="tab" data-toggle="tab" class="nav-link">
                                <i class="fa fa-street-view"></i><?php echo __( 'Street View', 'inventor' ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ( ! empty( $inside_view ) ) : ?>
                        <li role="presentation" class="nav-item <?php if ( $default_tab == 'INSIDE_VIEW' ): ?>active<?php endif; ?>">
                            <a href="#inside-view-panel" aria-controls="inside-view-panel" role="tab" data-toggle="tab" class="nav-link">
                                <i class="fa fa-home"></i><?php echo __( 'See Inside', 'inventor' ); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ( $show_directions ): ?>
                        <li class="nav-item directions">
                            <a href="https://maps.google.com?daddr=<?php echo esc_attr( $map_latitude ); ?>,<?php echo esc_attr( $map_longitude ); ?>" class="nav-link" target="_blank">
                                <i class="fa fa-level-down"></i><?php echo __( 'Directions', 'inventor' ) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <?php if ( ! empty( $map ) ) : ?>
                        <div role="tabpanel" class="tab-pane fade in <?php if ( $default_tab == 'MAP' ): ?>active<?php endif; ?>" id="simple-map-panel">
                            <div class="detail-map">
                                <div class="map-position">
                                    <div id="listing-detail-map"
                                         data-transparent-marker-image="<?php echo get_template_directory_uri(); ?>/assets/img/transparent-marker-image.png"
                                         data-latitude="<?php echo esc_attr( $map_latitude ); ?>"
                                         data-longitude="<?php echo esc_attr( $map_longitude ); ?>"
                                         data-polygon-path="<?php echo esc_attr( $map_polygon ); ?>"
                                         data-zoom="15"
                                         data-fit-bounds="false"
                                         data-marker-style="simple"
                                         <?php if ( ! empty( $map_location_address ) ): ?>data-marker-content='<span class="marker-content"><?php echo $map_location_address; ?></span>'<?php endif; ?>
                                         data-map-type="<?php echo esc_attr( $map_type ); ?>">
                                    </div><!-- /#map-property -->
                                </div><!-- /.map-property -->
                            </div><!-- /.detail-map -->
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $street_view ) ) : ?>
                        <?php $street_view_latitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view_location_latitude', true );?>
                        <?php $street_view_longitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view_location_longitude', true );?>
                        <?php $street_view_heading = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view_location_heading', true );?>
                        <?php $street_view_pitch = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view_location_pitch', true );?>
                        <?php $street_view_zoom = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'street_view_location_zoom', true );?>

                        <div role="tabpanel" class="tab-pane fade <?php if ( $default_tab == 'STREET_VIEW' ): ?>in active<?php endif; ?>" id="street-view-panel">
                            <div id="listing-detail-street-view"
                                 data-latitude="<?php echo esc_attr( $street_view_latitude ); ?>"
                                 data-longitude="<?php echo esc_attr( $street_view_longitude ); ?>"
                                 data-heading="<?php echo esc_attr( $street_view_heading ); ?>"
                                 data-pitch="<?php echo esc_attr( $street_view_pitch ); ?>"
                                 data-zoom="<?php echo esc_attr( $street_view_zoom ); ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $inside_view ) ) : ?>
                        <?php $inside_view_latitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view_location_latitude', true );?>
                        <?php $inside_view_longitude = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view_location_longitude', true );?>
                        <?php $inside_view_heading = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view_location_heading', true );?>
                        <?php $inside_view_pitch = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view_location_pitch', true );?>
                        <?php $inside_view_zoom = get_post_meta( get_the_ID(), INVENTOR_LISTING_PREFIX . 'inside_view_location_zoom', true );?>

                        <div role="tabpanel" class="tab-pane fade <?php if ( $default_tab == 'INSIDE_VIEW' ): ?>in active<?php endif; ?>" id="inside-view-panel">
                            <div id="listing-detail-inside-view"
                                 data-latitude="<?php echo esc_attr( $inside_view_latitude ); ?>"
                                 data-longitude="<?php echo esc_attr( $inside_view_longitude ); ?>"
                                 data-heading="<?php echo esc_attr( $inside_view_heading ); ?>"
                                 data-pitch="<?php echo esc_attr( $inside_view_pitch ); ?>"
                                 data-zoom="<?php echo esc_attr( $inside_view_zoom ); ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div><!-- /.listing-detail-section -->
    <?php endif; ?>
<?php endif; ?>