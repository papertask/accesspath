<div class="option">
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Plugin: Request Sent', $this->base->plugin->name ); ?></th>
                <th><?php echo sprintf( __( '%s: Status Created?', $this->base->plugin->name ), $this->base->plugin->account ); ?></th>
                <th><?php _e( 'Response', $this->base->plugin->name ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( is_array( $log ) ) {
                foreach ( $log as $count => $result ) {
                    ?>
                    <tr class="<?php echo ( $result['success'] ? 'success' : 'error' ); ?> <?php echo ( ( $count % 2 > 0 ) ? ' alternate' : '' ); ?>">
                        <td><?php echo date( 'Y-m-d H:i:s', $result['date'] ); ?></td>
                        <td><?php echo ( $result['success'] ? __( 'Yes', $this->base->plugin->name ) : __( 'No', $this->base->plugin->name ) ); ?></td>
                        <td>
                            <?php
                            if ( ! $result['success'] ) {
                                // Show error
                                echo ( isset( $result['message'] ) ? $result['message'] : '' );
                            } else {
                                // Show dates
                                ?>
                                <strong><?php echo sprintf( __( '%s: Created At: ', $this->base->plugin->name ), $this->base->plugin->account ); ?></strong>
                                <?php echo date( 'Y-m-d H:i:s', $result['status_created_at'] ); ?><br />

                                <strong><?php echo sprintf( __( '%s: Status Publication Due At: ', $this->base->plugin->name ), $this->base->plugin->account ); ?></strong>
                                <?php echo date( 'Y-m-d H:i:s', $result['status_due_at'] ); ?>
                                <?php
                            }
                            ?>

                            <br />

                            <?php
                            // Output Profile data, if available
                            if ( isset( $result['status'] ) ) {
                                ?>
                                <strong><?php echo sprintf( __( '%s: Profile: ', $this->base->plugin->name ), $this->base->plugin->account ); ?></strong>
                                <?php
                                // Use the Profile Name, if it's stored in the Log Entry
                                if ( isset( $result['profile_name'] ) ) {
                                    echo $result['profile_name'];
                                } else {
                                    // Try to get Profile Name
                                    if ( is_array( $profiles ) && isset( $profiles[ $result['status']['profile_ids'][0] ] ) ) {
                                        echo $profiles[ $result['status']['profile_ids'][0] ]['formatted_service'] . ': ' . $profiles[ $result['status']['profile_ids'][0] ]['formatted_username']; 
                                    } else {
                                        // Output Profile ID
                                        echo $result['status']['profile_ids'][0];
                                    }
                                }
                                ?>
                                <br />

                                <strong><?php echo sprintf( __( '%s: Status Text: ', $this->base->plugin->name ), $this->base->plugin->account ); ?></strong>
                                <?php echo ( isset( $result['status_text'] ) ? $result['status_text'] : $result['status']['text'] ); ?><br />
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="6"><?php echo sprintf( __( 'No status updates have been sent to %s.', $this->base->plugin->name ), $this->base->plugin->account ); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>      
    </table>
</div>

<?php
if ( is_array( $log ) ) {
    ?>
    <div class="option">
        <a href="post.php?post=<?php echo $post->ID; ?>&action=edit&<?php echo $this->base->plugin->name; ?>-export-log=1" class="button">
            <?php _e( 'Export Log', $this->base->plugin->name ); ?>
        </a>
        <a href="post.php?post=<?php echo $post->ID; ?>&action=edit&<?php echo $this->base->plugin->name; ?>-clear-log=1" class="button clear-log" data-action="<?php echo $this->base->plugin->filter_name; ?>_clear_log" data-target="#<?php echo $this->base->plugin->name; ?>-log">
            <?php _e( 'Clear Log', $this->base->plugin->name ); ?>
        </a>
    </div>
    <?php
}