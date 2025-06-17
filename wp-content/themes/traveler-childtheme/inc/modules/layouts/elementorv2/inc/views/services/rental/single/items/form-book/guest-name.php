<div class="guest_name_input"
	data-placeholder="<?php echo esc_html__( 'Guest %d name', 'traveler' ) ?>"
	data-hide-adult="<?php echo get_post_meta( get_the_ID(), 'disable_adult_name', true ) ?>"
	data-hide-children="<?php echo get_post_meta( get_the_ID(), 'disable_children_name', true ) ?>"
	>
	<label><span><?php echo esc_html__( 'Guest Weight Category', 'traveler' ) ?></span> <span class="required">*</span></label>
	<div class="guest_name_control">
		<?php
		$controls = STInput::request( 'guest_name' ); // Repurposed to hold weight category selections
		if ( ! empty( $controls ) and is_array( $controls ) ) {
			foreach ( $controls as $k => $control ) {
				?>
				<div class="control-item mb10">
					<select name="guest_name[]" class="form-control"> <!-- Repurposed name field for weight category -->
						<option value="up_to_125" <?php selected( 'up_to_125', $control ); ?>><?php echo esc_html__( 'Up to 125lbs', 'traveler' ) ?></option>
						<option value="up_to_300" <?php selected( 'up_to_300', $control ); ?>><?php echo esc_html__( 'Up to 300lbs', 'traveler' ) ?></option>
					</select>
				</div>
				<?php
			}
		}
		?>
	</div>
	<script type="text/html" id="guest_name_control_item">
		<div class="control-item mb10">
			<select name="guest_name[]" class="form-control"> <!-- Template for dynamic weight category addition -->
				<option value="up_to_125"><?php echo esc_html__( 'Up to 125lbs', 'traveler' ) ?></option>
				<option value="up_to_300"><?php echo esc_html__( 'Up to 300lbs', 'traveler' ) ?></option>
			</select>
		</div>
	</script>
</div>

<input type="hidden" name="adult_price" id="adult_price">
<input type="hidden" name="child_price" id="child_price">
