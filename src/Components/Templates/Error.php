<?php

namespace Irving\Components\Templates;

use Irving\Components\Body;
use Irving\Components\Component;
use Irving\Components\Traits\Query;

/**
 * Class for the Error template.
 */
class Error extends Component {

	use Query;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'error-template';

	/**
	 * Hook into query being set.
	 *
	 * @return self
	 */
	public function query_has_set(): self
	{
		return $this
			->append_child(
				( new Body() )
					->append_children(
						array_filter( $this->get_components() )
					)
			);
	}

	/**
	 * Get an array of all components.
	 *
	 * @return array
	 */
	public function get_components(): array
	{
		return [];
	}
}
