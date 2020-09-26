<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

class Website_link {
	public function hook() {
		if ( tribe_is_truthy( tribe_get_option( Settings::PREFIX . 'link_directly_to_website_url' ) ) ) {
			add_filter( 'tribe_get_event_link', [ $this, 'filter_event_link' ], 100, 2 );
		}
	}

	/**
	 * Checks website url setting and filters link
	 *
	 * @param string $link
	 * @param int    $postId
	 *
	 * @return string
	 */
	public function filter_event_link( $link, $postId ) {
		$website_url = tribe_get_event_website_url( $postId );
		if ( ! empty( $website_url ) ) {
			return $website_url;
		}

		return $link;
	}
}
