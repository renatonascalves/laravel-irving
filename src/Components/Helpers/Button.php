<?php

namespace Irving\Components\Helpers;

use Irving\Components\Component;

/**
 * Class for a button.
 */
class Button extends Component
{

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'button';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array
	{
		return [
			'button_style' => '',
			'class_name'   => '',
			'link'         => '',
			'type'         => '',
		];
	}
}
