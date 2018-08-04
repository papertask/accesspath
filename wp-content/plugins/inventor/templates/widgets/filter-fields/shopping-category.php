<?php $field_type = apply_filters( 'inventor_filter_field_type', 'SELECT', 'shopping-category' ); ?>
<?php $field_name = $field_type == 'SELECT' ? 'shopping-category' : 'shopping-categories'; ?>
<?php $taxonomy = 'shopping_categories'; ?>
<?php $selected = empty( $_GET[ $field_name ] ) ? null : $_GET[ $field_name ]; ?>

<?php if ( empty( $instance['hide_shopping-category'] ) ) : ?>
    <div class="form-group form-group-shopping-category">
        <?php if ( 'labels' == $input_titles ) : ?>
            <label for="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_shopping_category"><?php echo __( 'Shopping Category', 'inventor' ); ?></label>
        <?php endif; ?>

        <?php $terms = get_terms( $taxonomy, array(
            'hide_empty' 	=> false,
        ) ); ?>

        <?php if ( $field_type == 'SELECT' ): ?>

            <select class="form-control"
                    name="<?php echo $field_name; ?>"
                    data-size="10"
                    <?php if ( count( $terms ) > 10 ) : ?>data-live-search="true"<?php endif;?>
                    id="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_shopping_category">
                <option value="">
                    <?php if ( 'placeholders' == $input_titles ) : ?>
                        <?php echo __( 'Shopping Category', 'inventor' ); ?>
                    <?php else : ?>
                        <?php echo __( 'All categories', 'inventor' ); ?>
                    <?php endif; ?>
                </option>

                <?php global $wp_query; ?>
                <?php //$parent = is_tax( $taxonomy ) ? $wp_query->queried_object : null; ?>
                <?php $parent = null; ?>
                <?php $post_type = is_post_type_archive() && $wp_query->queried_object->name != 'listing' ? $wp_query->queried_object->name : null; ?>
                <?php $options = Inventor_Utilities::build_hierarchical_taxonomy_select_options( $taxonomy, $selected, $parent, 1, false, $post_type ); ?>
                <?php echo $options; ?>
            </select>

        <?php elseif( $field_type == 'CHECKBOXES' ): ?>
            <?php
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
