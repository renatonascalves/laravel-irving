<?php

namespace Irving\Components;

/**
 * Social Link.
 */
class SocialLinks extends Component
{
	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-links';

	/**
	 * Define a default config shape.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array
	{
		return [
			'services'      => [],
			'display_icons' => true,
		];
	}

	/**
	 * Retrieve service labels for use in custom fields.
	 *
	 * @param  array $link_configs Array of configs to use for creating new social item components.
	 * @return self
	 */
	public function create_link_components(array $link_configs): self
	{
		foreach ($this->config['services'] as $service => $enabled) {
			if ((bool) $enabled && ! empty($link_configs[ $service ])) {
				$this->append_child((new SocialItem())->merge_config($link_configs[ $service ]));
			}
		}
		return $this;
	}
}
