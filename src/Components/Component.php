<?php

namespace Irving\Components;

/**
 * Component.
 */
class Component implements \JsonSerializable
{
	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Component config.
	 *
	 * @var array
	 */
	public $config = [];

	/**
	 * Component children.
	 *
	 * @var array
	 */
	public $children = [];

	/**
	 * Component groups.
	 *
	 * @var array
	 */
	public $component_groups = [
		// Add default to make sure it's sent over as a JSON object.
		'default' => [],
	];

	/**
	 * Determine which config keys should be passed into result.
	 *
	 * @var array
	 */
	public $whitelist = [];

	/**
	 * Determine which config keys should not be transformed into camelCase.
	 *
	 * NOTE: this will not prevent the key itself from being camelcased,
	 * only the keys of the config value if it is an associative array.
	 *
	 * @var array
	 */
	public $preserve_inner_keys = [];

	/**
	 * Flag to determine if the component has encountered a "fatal" error.
	 * Rather than returning `null`, we set this flag so that the component can
	 * be removed during the `to_array()` or render equivalent function. This
	 * approach allows us to preserve method chaining without using an
	 * `optional()` helper or similar functionality.
	 *
	 * @var boolean
	 */
	public $is_valid = true;

	/**
	 * Available themes for this component. If you attempt to set a theme that is
	 * not in this array, it will fail (and fall back to 'default').
	 *
	 * @var array
	 */
	public $themes = [ 'default' ];

	/**
	 * Component constructor.
	 */
	public function __construct()
	{
		// Set default configs using set_config to ensure callbacks fire.
		foreach ( $this->default_config() as $key => $value ) {
			$this->set_config( $key, $value );
		}

		$this->children = $this->default_children();
	}

	/**
	 * Helper to change a components name.
	 *
	 * @param  string $name New component name.
	 * @return self
	 */
	public function set_name( string $name ): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [];
	}

	/**
	 * Check if a provided component group is valid.
	 *
	 * @param mixed $group Group to check.
	 * @return bool
	 */
	public function is_valid_group( $group ): bool {
		if (
			! is_array( $group )
			&& ! $group instanceof Component
			&& ! is_callable( $group )
			&& in_array( $group, array_keys( $this->component_groups ), true )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a provided value is a component or array of components.
	 *
	 * @param mixed $components Components to check.
	 * @return bool
	 */
	public function is_component( $components ): bool {
		// Is it a component?
		if ( $components instanceof Component ) {
			return true;
		}

		// Is it an array of components?
		if ( is_array( $components ) ) {
			return array_reduce(
				$components,
				function ( $carry, $component ) {
					return ! $carry ? $carry : $component instanceof Component;
				},
				true
			);
		}

		return false;
	}

	/**
	 * Helper to set a top level config value.
	 *
	 * @param array|string $key         Config key or entire config array.
	 * @param mixed        $value       Config value.
	 * @param bool         $do_callback Enable/disable the callback on this
	 *                                  method.
	 * @return self
	 */
	public function set_config( $key, $value = null, bool $do_callback = true ): self
	{
		if ( is_array( $key ) && is_null( $value ) ) {
			$this->config = $key;
		} else {
			$this->config[ $key ] = $value;

			// Allow hooking into a config being set.
			$callback_method = "{$key}_config_has_set";
			if ( method_exists( $this, $callback_method ) && $do_callback ) {
				$this->$callback_method();
			}
		}

		return $this;
	}

	/**
	 * Helper to get a top level config value.
	 *
	 * @param string $key Config key.
	 * @return mixed Config value or null.
	 */
	public function get_config( $key )
	{
		if ( array_key_exists( $key, $this->config ) ) {
			return $this->config[ $key ];
		}
		return null;
	}

	/**
	 * Merge new values into the current config.
	 *
	 * @param array $new_config Config array to merge in.
	 * @return self
	 */
	public function merge_config( array $new_config ): self
	{
		foreach ( $new_config as $key => $value ) {
			$this->set_config( $key, $value );
		}
		return $this;
	}

	/**
	 * Define default children.
	 *
	 * @return array Default children.
	 */
	public function default_children() : array {
		return [];
	}

	/**
	 * Helper to set children components.
	 *
	 * @param  array   $children Children for this component.
	 * @param  boolean $append   Append children to existing children.
	 * @return self
	 */
	public function set_children( array $children, $append = false ) : self {
		if ( $append ) {
			$this->children = array_merge(
				$this->children,
				array_filter( $children )
			);
		} else {
			$this->children = array_filter( $children );
		}
		return $this;
	}

	/**
	 * Append an array of components to the children array or component group.
	 *
	 * @param string|array $group Group or array of components.
	 * @param array        $children Child components.
	 * @return self
	 */
	public function append_children( $group, array $children = [] ) : self {
		// Append to group if group is valid, otherwise prepend to children.
		if ( $this->is_valid_group( $group ) && ! empty( $children ) ) {
			$this->component_groups[ $group ] = array_merge( $this->component_groups[ $group ], array_filter( $children ) );
		} elseif ( $this->is_component( $group ) ) {
			$this->children = array_merge( $this->children, array_filter( $group ) );
		} elseif ( '' === $group && $this->is_component( $children ) ) {
			$this->children = array_merge( $this->children, array_filter( $children ) );
		}

		return $this;
	}

	/**
	 * Prepend an array of components to the children array or component group.
	 *
	 * @param string|array $group Group or array of components.
	 * @param array        $children Child components.
	 * @return self
	 */
	public function prepend_children( $group, array $children = [] ) : self {
		// Prepend to group if group is valid, otherwise prepend to children.
		if ( $this->is_valid_group( $group ) && ! empty( $children ) ) {
			$this->component_groups[ $group ] = array_merge( array_filter( $children ), $this->component_groups[ $group ] );
		} elseif ( $this->is_component( $group ) ) {
			$this->children = array_merge( array_filter( $group ), $this->children );
		} elseif ( '' === $group && $this->is_component( $children ) ) {
			$this->children = array_merge( array_filter( $children ), $this->children );
		}

		return $this;
	}

	/**
	 * Append a component to the children array or component group.
	 *
	 * @param string|Component $group Group or array of components.
	 * @param bool|Component   $child Child component.
	 * @return self
	 */
	public function append_child( $group, $child = false ) : self {
		if ( is_array( $child ) ) {
			$child = $child[0];
		}

		// Push to group if group is valid, otherwise push to children.
		if ( $this->is_valid_group( $group ) && ! empty( $child ) ) {
			array_push( $this->component_groups[ $group ], $child );
		} elseif ( $this->is_component( $group ) ) {
			array_push( $this->children, $group );
		} elseif ( '' === $group && $this->is_component( $child ) ) {
			array_push( $this->children, $child );
		}

		return $this;
	}

	/**
	 * Prepend a component to the children array or component group.
	 *
	 * @param string|Component $group Group or array of components.
	 * @param bool|Component   $child Child component.
	 * @return self
	 */
	public function prepend_child( $group, $child = [] ) : self {
		if ( is_array( $child ) && ! empty( $child ) ) {
			$child = $child[0];
		}

		// Unshift to group if group is valid, otherwise unshift to children.
		if ( $this->is_valid_group( $group ) && ! empty( $child ) ) {
			array_unshift( $this->component_groups[ $group ], $child );
		} elseif ( $this->is_component( $group ) ) {
			array_unshift( $this->children, $group );
		} elseif ( '' === $group && $this->is_component( $child ) ) {
			array_unshift( $this->children, $child );
		}

		return $this;
	}

	/**
	 * Render the frontend component.
	 */
	public function render() {
		// Override me.
	}

	/**
	 * Run a user callback on this class. This can be used to create a fork in
	 * the method chain.
	 *
	 * @param callable $callable Callable.
	 * @param mixed    ...$args  Additional args to pass to the callback. Optional.
	 * @return function
	 */
	public function callback( $callable, ...$args ) {
		return call_user_func_array( $callable, array_merge( [ &$this ], $args ) );
	}

	/**
	 * Execute a function on each child of this component.
	 *
	 * @param string        $group Component group on which to call the callback.
	 * @param callable|null $callback Callback function.
	 * @return self
	 */
	public function children_callback( $group, $callback = null ) : self {
		// If valid group, map over the group, otherwise map over children.
		if ( $this->is_valid_group( $group ) ) {
			$this->component_groups[ $group ] = array_map( $callback, $this->component_groups[ $group ] );
		} elseif ( is_callable( $group ) ) {
			$this->children = array_map( $group, $this->children );
		}

		return $this;
	}

	/**
	 * Helper to set this component's is_valid flag to false.
	 *
	 * @return self
	 */
	public function set_invalid() : self {
		$this->is_valid = false;
		return $this;
	}

	/**
	 * Helper to set this component's is_valid flag to true.
	 *
	 * @return self
	 */
	public function set_valid() : self
	{
		$this->is_valid = true;
		return $this;
	}

	/**
	 * Helper to set this component's is_valid flag to false.
	 *
	 * @return self
	 */
	public function is_invalid() : self
	{
		return $this->set_invalid();
	}

	/**
	 * Helper to set this component's is_valid flag to true.
	 *
	 * @return self
	 */
	public function is_valid(): self
	{
		return $this->set_valid();
	}

	/**
	 * Trigger a fatal error on this component and log a message.
	 *
	 * @param string $error_message Optional error message.
	 * @return self
	 */
	public function has_error( $error_message = '' ): self
	{

		// If a message exists and WP debugging is enabled for logging.
		if ( ! empty( $error_message ) ) {
			error_log( $error_message );
		}

		return $this->set_invalid();
	}

	/**
	 * Helper to set theme on this component.
	 *
	 * @param string $theme_name Name of theme to set.
	 * @param bool   $force      Force the theme name, ignore the validation.
	 * @return self
	 */
	public function set_theme( string $theme_name, bool $force = false ): self
	{
		// Only set theme if it's configured in the themes property OR no
		// other themes are configured (besides `default`), implicitly
		// indicating theme validation should not be used.
		if (
			in_array( $theme_name, $this->themes, true ) // Is a valid theme.
			|| ( 1 === count( $this->themes ) && 'default' === $this->themes[0] ) // No custom themes have been white-listed.
			|| true === $force // Forcing.
		) {
			// Return camel cased theme name, if it isn't camel cased already.
			return $this->set_config( 'theme_name', $theme_name );
		}

		// Set theme to 'default' if the theme is not configured.
		return $this->set_config( 'theme_name', 'default' );
	}

	/**
	 * Helper to recursively set themes on child components.
	 *
	 * @param string $group Group for which to set themes.
	 * @param array  $theme_mapping Array in which keys are component $name properties and values are the theme to use for that component.
	 * @return self
	 */
	public function set_child_themes( $group, $theme_mapping = [] ): self
	{
		// Recursively set themes for children.
		if ( $this->is_valid_group( $group ) ) {
			$components = $this->component_groups[ $group ];
		} else {
			$components    = $this->children;
			$theme_mapping = $group;
		}

		foreach ( $components as $component ) {
			if ( ! empty( $theme_mapping[ $component->name ] ) ) {
				$component->set_theme( $theme_mapping[ $component->name ] );
			}

			$component->set_child_themes( $theme_mapping );
		}

		return $this;
	}

	/**
	 * Helper to output this class as an array.
	 *
	 * @return array
	 */
	public function to_array(): array
	{

		// For invalid components, append `-invalid` to the name to indicate
		// that there was a fatal error and it should not be rendered. This
		// approach allows us to still see the data in the endpoint for
		// debugging purposes (and even create a fallback component if
		// desired).
		if ( ! $this->is_valid ) {
			$this->name = $this->name . '-invalid';
		}

		// Camel case theme name on output.
		if ( '' !== ( $this->get_config( 'theme_name' ) ?? '' ) ) {
			$this->set_config( 'theme_name', $this->camel_case_string( $this->get_config( 'theme_name' ) ) );
		}

		return [
			'name'            => $this->name,
			'config'          => (object) $this->camel_case_keys( $this->config ),
			'children'        => array_filter( $this->children ),
			'componentGroups' => array_map(
				function ( $group ) {
					return array_filter( $group );
				},
				$this->component_groups
			),
		];
	}

	/**
	 * Convert all array keys to camel case.
	 *
	 * @param array $array        Array to convert.
	 * @return array Updated array with camel-cased keys.
	 */
	public function camel_case_keys( array $array ): array
	{
		// Setup for recursion.
		$camel_case_array = [];

		// Loop through each key.
		foreach ( $array as $key => $value ) {
			// Only return keys that are white-listed. Leave $whitelist empty
			// to disable.
			if (
				! empty( $this->whitelist )
				&& ! in_array( $key, $this->whitelist, true )
			) {
				unset( $array[ $key ] );
				continue;
			}

			// Camel case the key.
			$new_key = $this->camel_case_string( $key );

			if (
				! is_array( $value )
				// Don't recursively camelCase if this key is in the $preserve_inner_keys property.
				|| ( ! empty( $this->preserve_inner_keys ) && in_array( $key, $this->preserve_inner_keys, true ) )
			) {
				// Set new key value.
				$camel_case_array[ $new_key ] = $value;
			} else {
				// Set new key value, but process the nested array.
				$camel_case_array[ $new_key ] = $this->camel_case_keys( $array[ $key ] );
			}
		}

		return $camel_case_array;
	}

	/**
	 * Convert a string to camel case.
	 *
	 * @param string $string String to convert.
	 * @return string
	 */
	public function camel_case_string( string $string ): string
	{
		// Replace any dashes with underscores.
		$string = str_replace( '-', '_', $string );

		// Explode each part by underscore.
		$words = explode( '_', $string );

		// Capitalize each key part.
		array_walk(
			$words,
			function( &$word ) {
				$word = ucwords( $word );
			}
		);

		// Reassemble string.
		$string = implode( '', $words );

		// Lowercase the first character.
		$string[0] = strtolower( $string[0] );

		return $string;
	}

	/**
	 * Use custom to_array method when component is serialized for API response.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->to_array();
	}
}
