<?php $field_type = apply_filters( 'inventor_filter_field_type', 'SELECT', 'contract-type' ); ?>
<?php $field_name = $field_type == 'SELECT' ? 'contract-type' : 'contract-types'; ?>
<?php $taxonomy = 'contract_types'; ?>
<?php $selected = empty( $_GET[ $field_name ] ) ? null : $_GET[ $field_name ]; ?>
<?php $options = Inventor_Properties_Post_Type_Property::contract_options(); ?>

<?php if ( empty( $instance['hide_contract-type' ] ) ) : ?>
    <div class="form-group form-group-contract-type">
        <?php if ( 'labels' == $input_titles ) : ?>
            <label for="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_contract_type"><?php echo __( 'Contract type', 'inventor-properties' ); ?></label>
        <?php endif; ?>

        <?php if ( $field_type == 'SELECT' ): ?>

            <select class="form-control"
                    name="<?php echo $field_name; ?>"
                    id="<?php echo ! empty( $field_id_prefix ) ? $field_id_prefix : ''; ?><?php echo esc_attr( $args['widget_id'] ); ?>_contract_type">

                <option value="">
                    <?php if ( 'placeholders' == $input_titles ) : ?>
                        <?php echo __( 'Contract type', 'inventor-properties' ); ?>
                    <?php else : ?>
                        <?php echo __( 'All types', 'inventor-properties' ); ?>
                    <?php endif; ?>
                </option>

                <?php global $wp_query; ?>

                <?php $choices = ''; ?>
                <?php foreach ( $options as $option_value => $option_display ): ?>
                    <?php if ( $option_value != '' ): ?>
                        <?php $choices .= sprintf( "\t" . '<option value="%s" %s>%s</option>', $option_value, selected( $selected, $option_value, false ), $option_display ) . "\n"; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php echo $choices; ?>
            </select>

        <?php elseif( $field_type == 'CHECKBOXES' ): ?>

            <?php foreach ( $options as $option_value => $option_display ): ?>
                <?php $is_selected = is_array( $selected ) && in_array( $option_value, $selected ); ?>

                <?php if ( $option_value != '' ): ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>[]" <?php echo $is_selected ? 'checked' : ''; ?> value="<?php echo esc_attr( $option_value ); ?>">
                            <?php echo esc_attr( $option_display ); ?>
                        </label>
                    </div><!-- /.checkbox -->
                <?php endif; ?>
            <?php endforeach; ?>

        <?php endif; ?>
    </div><!-- /.form-group -->
<?php endif; ?>
