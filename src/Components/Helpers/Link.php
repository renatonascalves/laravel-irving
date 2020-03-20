<?php

namespace Irving\Components\Helpers;

use Irving\Components\Component;

/**
 * Class for a link.
 */
class Link extends Component
{

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'link-to';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array
	{
		return [
			'blank' => false,
			'to'    => '',
		];
	}
}
