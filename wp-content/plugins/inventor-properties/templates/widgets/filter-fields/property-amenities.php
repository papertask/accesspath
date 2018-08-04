<?php if ( empty( $instance['hide_property-amenities'] ) ) : ?>
    <div class="form-group form-group-property-amenities">
        <?php if ( 'labels' == $input_titles ) : ?>
            <label for="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_property_amenities"><?php echo __( 'Property Amenities', 'inventor-properties' ); ?></label>
        <?php endif; ?>

        <?php $amenities = get_terms( 'property_amenities', array(
            'hide_empty' 	=> false,
        ) ); ?>

        <?php $selected = empty( $_GET['property-amenities'] ) ? null : $_GET['property-amenities']; ?>
        <?php $filter_field_type = get_theme_mod( 'inventor_filter_multivalue_field_type', 'SELECT'); ?>

        <?php if ( $filter_field_type == 'SELECT' ): ?>
            <select multiple class="form-control"
                    name="property-amenities[]"
                    data-size="10"
                    <?php if ( count( $amenities ) > 10 ) : ?>data-live-search="true"<?php endif;?>
                    id="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_property_amenities">
                <option value="">
                    <?php if ( 'placeholders' == $input_titles ) : ?>
                        <?php echo __( 'Property Amenities', 'inventor-properties' ); ?>
                    <?php else : ?>
                        <?php echo __( 'All amenities', 'inventor-properties' ); ?>
                    <?php endif; ?>
                </option>

                <?php global $wp_query; ?>
                <?php $parent = is_tax('property-amenities') ? $wp_query->queried_object : null; ?>
                <?php $post_type = is_post_type_archive() && $wp_query->queried_object->name != 'listing' ? $wp_query->queried_object->name : null; ?>
                <?php $options = Inventor_Utilities::build_hierarchical_taxonomy_select_options( 'property_amenities', $selected, $parent, 1, false, $post_type ); ?>
                <?php echo $options; ?>
            </select>
        <?php elseif( $filter_field_type == 'CHECKBOXES' ): ?>
            <?php
            foreach( $amenities as $term ) {
                $is_selected = is_array( $selected ) && in_array( $term->slug, $selected ) || $term->slug == $selected;

                $checked = $is_selected ? 'checked="checked"' : '';

                echo '<div class="checkbox">';
                    echo '<label>';
                        echo '<input type="checkbox" name="property-amenities[]" value="'. $term->slug .'" '. $checked .'>';
                        echo $term->name;
                    echo '</label>';
                echo '</div>';
            }
            ?>
        <?php endif; ?>
    </div><!-- /.form-group -->
<?php endif; ?>
