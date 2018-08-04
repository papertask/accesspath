<?php $filter_field_type = get_theme_mod( 'inventor_filter_multivalue_field_type', 'SELECT'); ?>
<?php $filter_field_type = apply_filters( 'inventor_filter_field_type', $filter_field_type, 'listing_categories' ); ?>
<?php $field_name = $filter_field_type == 'SELECT' ? 'listing_category' : 'listing_categories'; ?>
<?php $taxonomy = 'listing_categories'; ?>
<?php $selected = empty( $_GET[ $field_name ] ) ? null : $_GET[ $field_name ]; ?>
<?php global $wp_query; ?>
<?php $parent_term = is_tax( $taxonomy ) ? $wp_query->queried_object : null; ?>
<?php $post_type = is_post_type_archive() && $wp_query->queried_object->name != 'listing' ? $wp_query->queried_object->name : null; ?>

<?php if ( empty( $instance['hide_category'] ) ) : ?>
    <div class="form-group form-group-listing-categories">
        <?php if ( 'labels' == $input_titles ) : ?>
            <label for="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_listing_categories"><?php echo __( 'Category', 'inventor' ); ?></label>
        <?php endif; ?>

        <?php $terms = get_terms( 'listing_categories', array(
            'hide_empty' 	=> false,
        ) ); ?>

        <?php if ( $filter_field_type == 'SELECT' ): ?>

            <select class="form-control"
                    name="<?php echo $field_name; ?>"
                    data-size="10"
                    <?php if ( count( $terms ) > 10 ) : ?>data-live-search="true"<?php endif;?>
                    id="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_shopping_category">
                <option value="">
                    <?php if ( 'placeholders' == $input_titles ) : ?>
                        <?php echo __( 'Category', 'inventor' ); ?>
                    <?php else : ?>
                        <?php echo __( 'All categories', 'inventor' ); ?>
                    <?php endif; ?>
                </option>

                <?php $options = Inventor_Utilities::build_hierarchical_taxonomy_select_options( $taxonomy, $selected, $parent, 1, false, $post_type ); ?>
                <?php echo $options; ?>
            </select>

        <?php elseif( $filter_field_type == 'CHECKBOXES' ): ?>
            <?php

            $terms_query_args = array(
                'hide_empty'    => false,
            );

            if ( $parent_term ) {
                $terms_query_args['parent'] = $parent_term->term_id;
            }

            if ( ! empty( $post_type ) ) {
                $terms_query_args['meta_query'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => INVENTOR_LISTING_CATEGORY_PREFIX . 'listing_types',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'       => INVENTOR_LISTING_CATEGORY_PREFIX . 'listing_types',
                        'value'     => serialize( strval( $post_type ) ),
                        'compare'   => 'LIKE'
                    )
                );
            }

            $terms = get_terms( $taxonomy, $terms_query_args );

            foreach( $terms as $term ) {
                $is_selected = is_array( $selected ) && in_array( $term->slug, $selected ) || $term->slug == $selected;

                $checked = $is_selected ? 'checked="checked"' : '';

                echo '<div class="checkbox">';
                echo '<label>';
                echo '<input type="checkbox" name="'. $field_name .'[]" value="'. $term->slug .'" '. $checked .'>';
                echo $term->name;
                echo '</label>';
                echo '</div>';
            }
            ?>
        <?php endif; ?>
    </div><!-- /.form-group -->
<?php endif; ?>
