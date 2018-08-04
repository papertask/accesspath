<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php echo __( sprintf( '%s has been purchased', $listing ), 'inventor-shop' ); ?>

<br><br>

<?php if ( ! empty( $listing_type ) ) : ?>
    <strong><?php echo __( 'Listing type', 'inventor' ); ?>: </strong> <?php echo esc_attr( $listing_type ); ?><br>
<?php endif; ?>

<?php if ( ! empty( $url ) ) : ?>
    <strong><?php echo __( 'URL', 'inventor' ); ?>: </strong> <?php echo esc_attr( $url ); ?><br>
<?php endif; ?>

<?php if ( ! empty( $price ) ) : ?>
    <strong><?php echo __( 'Price', 'inventor' ); ?>: </strong> <?php echo esc_attr( $price ); ?><br>
<?php endif; ?>

<?php if ( ! empty( $currency_code ) ) : ?>
    <strong><?php echo __( 'Currency', 'inventor' ); ?>: </strong> <?php echo esc_attr( $currency_code ); ?><br>
<?php endif; ?>

<?php if ( ! empty( $gateway ) ) : ?>
    <strong><?php echo __( 'Gateway', 'inventor' ); ?>: </strong> <?php echo esc_attr( $gateway ); ?><br>
<?php endif; ?>

<?php if ( ! empty( $payment_id ) ) : ?>
    <strong><?php echo __( 'Payment ID', 'inventor' ); ?>: </strong> <?php echo esc_attr( $payment_id ); ?><br>
<?php endif; ?>

<br>

<strong><?php echo __( 'Billing details', 'inventor' ); ?></strong><br>

<br>

<?php if ( ! empty( $billing_details ) ) : ?>
    <?php $billing_fields = apply_filters( 'inventor_billing_fields', array() );  ?>

    <?php foreach( $billing_details as $key => $value ): ?>
        <?php $label = empty( $billing_fields[ $key ] ) ? $key : $billing_fields[ $key ]; ?>
        <strong><?php echo esc_attr( $label ); ?>: </strong> <?php echo esc_attr( $value ); ?><br>
    <?php endforeach; ?>
<?php endif; ?>