<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
$description = ! empty( $instance['description'] ) ? $instance['description'] : '';
$locations = ! empty( $instance['locations'] ) ? $instance['locations'] : array();
$show_count = ! empty( $instance['show_count'] ) ? $instance['show_count'] : '';
$appearance = ! empty( $instance['appearance'] ) ? $instance['appearance'] : 'cards';
$per_row = ! empty( $instance['per_row'] ) ? $instance['per_row'] : 4;
?>

<!-- TITLE -->
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
        <?php echo __( 'Title', 'inventor' ); ?>
    </label>

    <input  class="widefat"
            id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
            name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
            type="text"
            value="<?php echo esc_attr( $title ); ?>">
</p>

<!-- DESCRIPTION -->
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>">
        <?php echo __( 'Description', 'inventor' ); ?>
    </label>

	<textarea class="widefat"
              rows="4"
              id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"
              name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo esc_attr( $description ); ?></textarea>
</p>

<!-- CATEGORIES -->
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>">
        <?php echo __( 'Listing categories', 'inventor' ); ?>
    </label>

    <select id="<?php echo esc_attr( $this->get_field_id( 'locations' ) ); ?>" style="width: 100%;"
            multiple="multiple"
            name="<?php echo esc_attr( $this->get_field_name( 'locations' ) ); ?>[]">
        <?php $terms = get_terms( 'locations', array( 'parent' => 0, 'hide_empty' => false ) ); ?>

        <?php if ( is_array( $terms ) ) : ?>
            <?php foreach ( $terms as $term ) : ?>
                <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php if ( in_array( $term->term_id, $locations ) ) : ?>selected="selected"<?php endif; ?>><?php echo esc_attr( $term->name ); ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</p>

<!-- SHOW COUNT -->
<p>
    <label>
        <input  type="checkbox"
                class="checkbox"
            <?php echo ( ! empty( $show_count ) ) ? 'checked="checked"' : ''; ?>
                id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>">
        <?php echo __( 'Show count', 'inventor' ); ?>
    </label>
</p>

<!-- DISPLAY -->
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'appearance' ) ); ?>">
        <?php echo __( 'Display as', 'inventor' ); ?>
    </label>

    <select id="<?php echo esc_attr( $this->get_field_id( 'appearance' ) ); ?>"
            name="<?php echo esc_attr( $this->get_field_name( 'appearance' ) ); ?>">
        <option value="posters" <?php echo ( 'posters' == $appearance || empty( $appearance ) ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Posters', 'inventor' ); ?></option>
    </select>
</p>

<!-- PER ROW -->
<p class="per-row">
    <label for="<?php echo esc_attr( $this->get_field_id( 'per_row' ) ); ?>">
        <?php echo __( 'Items per row', 'inventor' ); ?>
    </label>

    <select id="<?php echo esc_attr( $this->get_field_id( 'per_row' ) ); ?>"
            name="<?php echo esc_attr( $this->get_field_name( 'per_row' ) ); ?>">
        <option value="1" <?php echo ( '1' == $per_row ) ? 'selected="selected"' : ''; ?>>1</option>
        <option value="2" <?php echo ( '2' == $per_row ) ? 'selected="selected"' : ''; ?>>2</option>
        <option value="3" <?php echo ( '3' == $per_row ) ? 'selected="selected"' : ''; ?>>3</option>
        <option value="4" <?php echo ( '4' == $per_row ) ? 'selected="selected"' : ''; ?>>4</option>
        <option value="5" <?php echo ( '5' == $per_row ) ? 'selected="selected"' : ''; ?>>5</option>
        <option value="6" <?php echo ( '6' == $per_row ) ? 'selected="selected"' : ''; ?>>6</option>
    </select>
</p>