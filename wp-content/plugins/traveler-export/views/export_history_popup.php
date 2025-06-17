<?php
$start = STInput::get('start', "");
$end = STInput::get('end', "");
$date = STInput::get('date', date('d/m/Y h:i a'). '-'. date('d/m/Y h:i a', strtotime('+1 day')));

if(!empty($start)){
    $starttext = $start;
    $start = $start;
} else {
    $starttext = TravelHelper::getDateFormatMomentText();
    $start = "";
}

if(!empty($end)){
    $endtext = $end;
    $end = $end;
} else {
    $endtext = TravelHelper::getDateFormatMomentText();
    $end = "";
}

?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#stt-history-popup"><?php echo esc_html__('Download Booking History','traveler-export') ?></button>            
<div class="modal fade" id="stt-history-popup" tabindex="-1" role="dialog" >
    <form class="modal-dialog" role="document" action="<?php echo esc_url(home_url()) ?>" target="_blank">
        <div class="modal-content">
            <div class="export-header">
                        <img src="<?php echo esc_url(STTTravelerExport::inst()->pluginUrl) ?>/assets/images/logo.svg" alt="logo">
                        <img class="close" data-dismiss="modal" src="<?php echo esc_url(STTTravelerExport::inst()->pluginUrl) ?>/assets/images/close.svg" alt="close">
                    </div>
            <div class="modal-body">
                <div class="filter-title"><?php echo esc_html__('Choose the filter','traveler-export') ?></div>
                <div class="export-content">
                    <input type="hidden" name ="st_export" value="pdf">
                    <input type="hidden" name = "st_export_type" value="all">
                    <input type="hidden" name = "st_screen" value="<?php echo esc_attr($screen) ?>">
                    <div class="date-history-swapper">
                        <div class="check-in-wrapper">
                            <label><?php echo esc_html__('Date: From - To', 'traveler-export'); ?></label>
                            <div class="date">
                                <div class="render check-in-render"><?php echo esc_html($starttext); ?></div>
                                <span> - </span>
                                <div class="render check-out-render"><?php echo esc_html($endtext); ?></div>
                            </div>
                        </div>
                        <input type="hidden" class="check-in-input" value="<?php echo esc_attr($start) ?>" name="start">
                        <input type="hidden" class="check-out-input" value="<?php echo esc_attr($end) ?>" name="end">
                        <input type="text" class="check-in-out" value="<?php echo esc_attr($date); ?>" name="date">
                    </div>
                    <div class="status-swapper">
                        <label><?php echo esc_html__('Status', 'traveler-export'); ?></label>
                        <select name="export_status" id="export-status" class="form-select form-select-2">
                            <option value="<?php echo esc_attr__('all','traveler-export') ?>"><?php echo esc_html__('All', 'traveler-export'); ?></option>
                            <option value="<?php echo esc_attr__('pending','traveler-export') ?>"><?php echo esc_html__('Pending', 'traveler-export'); ?></option>
                            <option value="<?php echo esc_attr__('complete','traveler-export') ?>"><?php echo esc_html__('Completed', 'traveler-export'); ?></option>
                            <option value="<?php echo esc_attr__('incomplete','traveler-export') ?>"><?php echo esc_html__('Incomplete', 'traveler-export'); ?></option>
                            <option value="<?php echo esc_attr__('canceled','traveler-export') ?>"><?php echo esc_html__('Cancelled', 'traveler-export'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary stt-export-button" type="submit" ><?php echo esc_html__('Download PDF Booking','traveler-export') ?></button>
            </div>
        </div>
    </form>
</div>