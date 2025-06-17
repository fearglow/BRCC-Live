<?php
$data_post = get_post($postID);
?>
<div class="duplicate-popup">
    
    <div class="duplicate-wrapper">
        <div class="duplicate-content">
            <div class="duplicate-header">
                <img src="<?php echo esc_url(STTTravelerDuplicate::inst()->pluginUrl) ?>/assets/images/logo.svg" alt="logo">
                <img class="close-filter" src="<?php echo esc_url(STTTravelerDuplicate::inst()->pluginUrl) ?>/assets/images/close.svg" alt="close">
                
            </div>
            <div class="duplicate-body">
                <div class="duplicate-title">
                    <p class="title">
                    <?php
                        $title = $data_post->post_title;
                        echo esc_html($title);
                    ?>
                    </p>
                </div>
                <div class="duplicate-number">
                    <span><?php echo esc_html__('Number of copies','traveler-duplicate')?></span>
                    <input type="number" min="1" step="1" name="stt_duplicate_number" class="stt-duplicate-number">
                </div>
                <?php
                $post_type = $data_post->post_type;
                if($post_type == 'st_hotel'){
                ?>
                <div class="duplicate-room">
                    <input type="checkbox" name="stt_duplicate_room" id="stt_duplicate_room">
                    <span><?php echo esc_html__('Duplicate Room','traveler-duplicate')?></span>
                </div>
                <div class="duplicate-room-availability">
                    <input type="checkbox" name="stt_duplicate_room_availability_" id="stt_duplicate_room_availability">
                    <span><?php echo esc_html__('Duplicate Availability','traveler-duplicate')?></span>
                </div>
                <?php }elseif($post_type =='st_tours' || $post_type == 'st_activity' || $post_type == 'st_flight' || $post_type =='st_rental' || $post_type =='hotel_room'){ ?>
                <div class="duplicate-availability">
                    <input type="checkbox" name="stt_duplicate_availability" id="stt_duplicate_availability">
                    <span><?php echo esc_html__('Duplicate Availability','traveler-duplicate')?></span>
                </div>
                <?php }?>
                <div class="duplicate-button">
                    <button data-id="<?php echo esc_html($postID); ?>" data-finish="<?php echo esc_html__('Duplicate now','traveler-duplicate') ?>"  data-process="<?php echo esc_html__('Processing...','traveler-duplicate')?>" class = "stt-dup-button" data-action="stt_duplicate"><?php echo esc_html__('Duplicate now','traveler-duplicate') ?></button>
                </div>
                <div class="duplicate-message"></div>
            </div>
        </div>
    </div>
</div>