<?php

namespace Irving\Components;

/**
 * Social Item.
 */
class SocialItem extends Component
{
	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-item';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array
	{
		return [
			'display_icon' => true,
			'type'         => '',
			'url'          => '',
		];
	}
}
