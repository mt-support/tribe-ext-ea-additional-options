<div class="tribe-default-settings">
	<div class='tribe-dependent' data-depends='#tribe-ea-field-origin'
	     data-condition-not='["csv", "facebook-dev"]'>
		<h4>Additional Options</h4>
		<div class="tribe-refine tribe-active ">
			<label
				for="tribe-ea-field-timezone"><?php esc_html_e( 'Force Timezone:', 'tribe-ext-ea-additional-options' ); ?></label>
			<select name="aggregator[timezone]" id="tribe-ea-field-timezone" class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large">
				<option value="">Do not change the timezone.</option>
				<?php foreach ( $timezones as $tz ) : ?>
				<option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $selectedTimezone, $tz, true ); ?>>
					<?php esc_html_e( str_replace( '_', ' ', $tz ) ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<span
				class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help"
				data-bumpdown="<?php echo esc_attr__( 'You can choose to change the timezones of all events in this import. The times will be modified to match the chosen timezone.', 'tribe-ext-ea-additional-options' ); ?>"
				data-width-rule="all-triggers"
			></span>
		</div>
		<div class="tribe-refine tribe-active tribe-dependent">
			<label
				for="tribe-ea-field-prefix"><?php esc_html_e( 'Event Title Prefix:', 'tribe-ext-ea-additional-options' ); ?></label>
			<input id="tribe-ea-field-prefix" name="aggregator[prefix]"
			       class="tribe-ea-field tribe-ea-size-large" type="text"
			       value="<?php echo esc_attr( $prefixValue ); ?>"/>
			<span
				class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help"
				data-bumpdown="<?php echo esc_attr__( 'Add text before the title of each event.', 'tribe-ext-ea-additional-options' ); ?>"
				data-width-rule="all-triggers"
			></span>
		</div>
		<div class="tribe-refine tribe-active tribe-dependent">
			<label
				for="tribe-ea-field-link"><?php esc_html_e( 'Event URL:', 'tribe-ext-ea-additional-options' ); ?></label>
			<input id="tribe-ea-field-link" name="aggregator[link]"
			       class="tribe-ea-field tribe-ea-size-large" type="text"
			       value="<?php echo esc_attr( $linkValue ); ?>"/>
			<span
				class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help"
				data-bumpdown="<?php echo esc_attr__( 'Replace the website URL for each event with this value.', 'tribe-ext-ea-additional-options' ); ?>"
				data-width-rule="all-triggers"
			></span>
		</div>
		<div class="tribe-refine tribe-active tribe-dependent">
			<label for="tribe-ea-field-delete-upcoming-events">
				<?php esc_html_e( 'Delete upcoming events before running the next import.', 'tribe-ext-ea-additional-options' ); ?>
			</label>
			<input name="aggregator[delete_upcoming_events]" id="tribe-ea-field-delete-upcoming-events" type="checkbox" <?php checked( $delete_upcoming ); ?>>
		</div>
	</div>
</div>
