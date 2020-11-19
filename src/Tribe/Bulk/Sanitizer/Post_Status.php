<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Post_Status extends Sanitizer {
	protected $alias = [
		'draft'          => 'draft',
		'private'        => 'private',
		'publish'        => 'publish',
		'published'      => 'publish',
		'pending review' => 'pending',
		'pending_review' => 'pending',
		'pending'        => 'pending',
		'review'         => 'pending',
	];

	public function sanitize( $value ) {
		if ( $value === null || empty( $value ) ) {
			return null;
		}

		$value = strtolower( $value );

		if ( isset( $this->alias[ $value ] ) ) {
			return $this->alias[ $value ];
		}

		return null;
	}
}
