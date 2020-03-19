<?php

namespace Irving\Components;

/**
 * HTML.
 */
class HTML extends Component
{
	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'html';

	/**
	 * Define the default config of a header.
	 *
	 * @return array
	 */
	public function default_config(): array
	{
		return [
			'content' => '',
		];
	}
}
