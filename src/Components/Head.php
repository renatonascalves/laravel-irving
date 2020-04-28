<?php

namespace Irving\Components;

use Irving\Components\Traits\Query;

/**
 * Head.
 */
class Head extends Component
{
	use Query;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'head';

	/**
	 * Define default children.
	 *
	 * @return array Default children.
	 */
	public function default_children(): array
	{
		return [
			(new Component())
				->set_name('title')
				->set_children([ $this->query->site() ]), // @todo Change it.
		];
	}

	/**
	 * Set the title tag.
	 *
	 * @param string $value The title value.
	 * @return self
	 */
	public function set_title(string $value): self
	{
		// Loop through children and update the title component (which should
		// exist since it's a default child).
		foreach ($this->children as &$child) {
			if ('title' === $child->name) {
				$child->children[0] = html_entity_decode($value, ENT_QUOTES);
			}
		}

		return $this;
	}

	/**
	 * Helper function for setting a canonical url.
	 *
	 * @param  string $url Canonical URL.
	 * @return self
	 */
	public function set_canonical_url(string $url): self
	{
		return $this->add_link('canonical', $url);
	}

	/**
	 * Helper function for adding a new meta tag.
	 *
	 * @param string $property Property value.
	 * @param string $content  Content value.
	 * @return self
	 */
	public function add_meta(string $property, string $content): self
	{
		return $this->add_tag(
			'meta',
			[
				'property' => $property,
				'content'  => html_entity_decode($content),
			]
		);
	}

	/**
	 * Helper function for adding a new link tag.
	 *
	 * @param string $rel  Rel value.
	 * @param string $href Href value.
	 * @return self
	 */
	public function add_link(string $rel, string $href)
	{
		return $this->add_tag(
			'link',
			[
				'rel'  => $rel,
				'href' => $href,
			]
		);
	}

	/**
	 * Helper function for add a new script tag.
	 *
	 * @param string $src Script tag src url.
	 * @param bool   $defer If script should defer loading until DOMContentLoaded.
	 * @param bool   $async If script should load asynchronous.
	 * @return self
	 */
	public function add_script(string $src, $defer = true, $async = true): self
	{
		// Attributes.
		$attrs = [ 'src' => $src ];

		if ($defer) {
			$attrs['defer'] = $defer;
		}

		if ($async) {
			$attrs['async'] = $async;
		}

		return $this->add_tag('script', $attrs);
	}

	/**
	 * Helper function to quickly add a new tag.
	 *
	 * @param string $tag        Tag value.
	 * @param array  $attributes Tag attributes.
	 * @return self
	 */
	public function add_tag(string $tag, array $attributes = []): self
	{
		$component = new Component();
		$component->set_name($tag);
		$component->merge_config($attributes);

		// Append this tag as a child component.
		return $this->append_child($component);
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function query_has_set() : self
	{
		$title = $this->get_the_head_title();
		$title .= $this->get_trailing_title();
		$this->set_title($title);
		$this->set_additional_meta_tags();
		return $this;
	}

	/**
	 * Get the head title based on the query set.
	 *
	 * @return string
	 */
	public function get_the_head_title(): string
	{
		return $this->query->get_title();
	}

	/**
	 * Get the trailing title.
	 *
	 * @return string
	 */
	public function get_trailing_title()
	{
		return ' | ';
	}

	/**
	 * Apply default, additional, meta tags.
	 */
	public function set_additional_meta_tags()
	{
		$tags = [];

		if (! empty($tags) && is_array($tags)) {
			foreach ($tags as $name => $content) {
				if (! empty($content)) {
					$this->add_tag(
						'meta',
						[
							'name'    => $name,
							'content' => $content,
						]
					);
				}
			}
		}
	}

	/**
	 * Apply basic meta tags.
	 */
	public function set_standard_meta()
	{
		// Meta description.
		$meta_description = $this->get_meta_description();
		if (! empty($meta_description)) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'description',
					'content' => \esc_attr($meta_description),
				]
			);
		}

		if ( ! empty( $meta_keywords ) ) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'keywords',
					'content' => esc_attr( implode( ',', array_filter( $meta_keywords ) ) ),
				]
			);
		}

		// Canoncial url.
		if (! empty($canonical_url)) {
			$this->set_canonical_url($canonical_url);
		}

		// Deindex URL.
		if (absint($deindex_url)) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'robots',
					'content' => 'noindex',
				]
			);
		}
	}

	/**
	 * Add basic open graph tags.
	 */
	public function set_open_graph_meta()
	{

		// Define values that are used multiple times.
		$description  = $this->get_social_description();
		$image_source = $this->get_image_source();
		$image_url    = '';
		$permalink    = $this->query->permalink();
		$title        = $this->get_social_title();

		// Open graph meta.
		$this->add_meta('og:url', $permalink);
		$this->add_meta('og:type', 'article');
		$this->add_meta('og:title', $title);
		$this->add_meta('og:description', $description);
		$this->add_meta('og:site_name', get_bloginfo('name'));

		// Images.
		if ( ! empty( $image_source ) ) {
			$image_url = $image_source[0];
			$this->add_meta(
				'og:image',
				apply_filters( 'wp_components_head_og_image', $image_source[0], $this->post->ID )
			);
			$this->add_meta(
				'og:width',
				apply_filters( 'wp_components_head_og_image_width', $image_source[1], $this->post->ID )
			);
			$this->add_meta(
				'og:height',
				apply_filters( 'wp_components_head_og_image_height', $image_source[2], $this->post->ID )
			);
			$this->add_meta(
				'og:image:width',
				apply_filters( 'wp_components_head_og_image_width', $image_source[1], $this->post->ID )
			);
			$this->add_meta(
				'og:image:height',
				apply_filters( 'wp_components_head_og_image_height', $image_source[2], $this->post->ID )
			);
		}

		// Property specific meta.
		$twitter_meta = [
			'twitter:card'        => 'summary_large_image',
			'twitter:title'       => apply_filters( 'wp_components_head_twitter_title', $title, $this->post->ID ),
			'twitter:description' => apply_filters( 'wp_components_head_twitter_description', $description, $this->post->ID ),
			'twitter:image'       => apply_filters( 'wp_components_head_twitter_image_url', $image_url, $this->post->ID ),
			'twitter:url'         => apply_filters( 'wp_components_head_twitter_url', $permalink, $this->post->ID ),
		];

		// Twitter account.
		if ( ! empty( $twitter_account ) ) {
			$twitter_meta['twitter:site'] = '@' . str_replace( '@', '', $twitter_account );
		}

		// Add Twitter tags.
		foreach ($twitter_meta as $name => $content) {
			if (empty($content)) {
				return;
			}

			$this->add_tag(
				'meta',
				[
					'name'    => $name,
					'content' => $content,
				]
			);
		}
	}

	/**
	 * Get the title used by the head tag.
	 *
	 * @return string
	 */
	public function get_meta_title(): string
	{
		return $this->query->get_title();
	}

	/**
	 * Get the title used by open graph tags/social.
	 *
	 * @return string
	 */
	public function get_social_title(): string
	{
		return $this->query->get_title();
	}

	/**
	 * Get the meta description used by the head tag.
	 *
	 * @return string
	 */
	public function get_meta_description(): string
	{
		return $this->query->get_description();;
	}

	/**
	 * Get the meta description used by open graph tags/social.
	 *
	 * @return string
	 */
	public function get_social_description(): string
	{
		return $this->get_meta_description();
	}

	/**
	 * Get image source with its info.
	 *
	 * @return array
	 */
	protected function get_image_source(): array
	{
		// Remove query string from url.
		$image_source[0] = strtok( $image_source[0], '?' ) . '?resize=1200,600';
		return $image_source;
	}
}
