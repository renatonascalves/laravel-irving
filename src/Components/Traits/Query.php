<?php

namespace Irving\Components\Traits;

/**
 * Query trait.
 */
trait Query
{
	/**
	 * Query Model.
	 *
	 * @var null
	 */
	public $query = null;

	/**
	 * Get the query posts.
	 *
	 * @return array
	 */
	public function get_posts(): array
	{
		return $this->query->posts ?? [];
	}

	/**
	 * Get the queried object.
	 *
	 * @return object
	 */
	public function get_queried_object()
	{
		return $this->query->get_queried_object();
	}

	/**
	 * Get the queried object ID.
	 *
	 * @return int
	 */
	public function get_queried_object_id(): int
	{
		return absint( $this->query->get_queried_object_id() ?? 0 );
	}

	/**
	 * Set the query object.
	 *
	 * @param mixed $query Model object.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_query( $query = null ): self
	{
		// Set object.
		if ( ! is_null( $query ) ) {
			$this->query = $query;
			$this->query_has_set();
		}

		if ( false === \method_exists( $this, 'has_error' ) ) {
			return $this;
		}

		// Something else went wrong.
		return $this->has_error( 'Query was not an instance of Query' );
	}

	/**
	 * Callback function for classes to override.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function query_has_set(): self
	{
		return $this;
	}
}
