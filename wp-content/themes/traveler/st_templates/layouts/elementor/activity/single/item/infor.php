<div class="st-service-feature">
    <div class="row">
        <div class="col-6 col-sm-3">
            <div class="item d-flex align-items-center">
                <div class="icon">
                    <?php echo htmlspecialchars_decode($icon_duration_single_activity);?>
                </div>
                <div class="info">
                    <div class="name"><?php echo __( 'Duration', 'traveler' ); ?></div>
                    <p class="value">
                        <?php
                            $duration = get_post_meta( get_the_ID(), 'duration', true );
                            echo esc_html( $duration );
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="item d-flex align-items-center">
                <div class="icon">
                    <?php echo htmlspecialchars_decode($icon_cancel_single_activity); ?>
                </div>
                <div class="info">
                    <div class="name"><?php echo __( 'Cancellation', 'traveler' ); ?></div>
                    <p class="value">
                        <?php
                            $cancellation= get_post_meta( get_the_ID(), 'st_allow_cancel', true );
                            $cancellation_day= (int)get_post_meta( get_the_ID(), 'st_cancel_number_days', true );
                            if ( $cancellation== 'on' ) {
                                echo sprintf(_n( 'Up to %s day','Up to %s days', $cancellation_day,'traveler' ), $cancellation_day);
                            } else {
                                echo __( 'No Cancellation', 'traveler' );
                            }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="item d-flex align-items-center">
                <div class="icon">
                    <?php echo htmlspecialchars_decode($icon_groupsize_single_activity); ?>
                </div>
                <div class="info">
                    <div class="name"><?php echo __( 'Group Size', 'traveler' ); ?></div>
                    <p class="value">
                        <?php
                            $max_people = get_post_meta( get_the_ID(), 'max_people', true );
                            if ( empty( $max_people ) or $max_people == 0 or $max_people < 0 ) {
                                echo __( 'Unlimited', 'traveler' );
                            } else {
                                echo sprintf( __( '%s people', 'traveler' ), $max_people );
                            }
                        ?>
                    </p>
                </div>
            </div>
        </div>
       <div class="col-xs-6 col-lg-3">
    <div class="item">
        <div class="icon">
            <!-- Add your icon for Difficulty here, example using Font Awesome -->
            <i class="fas fa-tachometer-alt"></i> <!-- Replace this with the actual icon you want to use. -->
        </div>
        <div class="info">
            <div class="name"><?php echo __( 'Difficulty', 'traveler' ); ?></div>
            <p class="value">
                Intermediate
            </p>
        </div>
    </div>
</div>

    </div>
</div>