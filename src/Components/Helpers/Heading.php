<?php

namespace Irving\Components\Helpers;

use Irving\Components\Component;

/**
 * Class for a heading.
 */
class Heading extends Component
{

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'heading';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array
	{
		return [
			'class_name'  => '',
			'font_family' => '',
			'link'        => '',
			'tag'         => '',
			'type_style'  => '',
		];
	}
}
