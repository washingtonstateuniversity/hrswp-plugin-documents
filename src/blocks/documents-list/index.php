<?php
/**
 * Server-side rendering of the `hrswp/documents-list` block.
 *
 * @package HRSWP_Blocks
 */

namespace HRSWP\Documents\DocumentsList;

use HrswpDocuments as admin;

/**
 * Registers and renders the `hrswp/documents-list` block
 *
 * @since 1.1.0
 */
class DocumentsList {
	/**
	 * The excerpt length set by the `hrswp/documents-list` block.
	 *
	 * @since 1.1.0
	 * @var int
	 */
	public $excerpt_length = 0;

	/**
	 * Initializes the `DocumentsList` class.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Returns the excerpt length set by the `hrswp/documents-list` block.
	 *
	 * @since 1.1.0
	 *
	 * @return int The excerpt length.
	 */
	public function get_excerpt_length() {
		return $this->excerpt_length;
	}

	/**
	 * Renders the `hrswp/documents-list` block on the server.
	 *
	 * @since 1.1.0
	 *
	 * @param array $attributes The block attributes.
	 * @return string Returns a list of posts.
	 */
	public function render( $attributes ) {
		// Define possibly undefined attributes.
		$attributes['align'] = isset( $attributes['align'] )
			? $attributes['align']
			: '';

		$attributes['className'] = isset( $attributes['className'] )
			? $attributes['className']
			: '';

		$attributes['selectedTermLists'] = isset( $attributes['selectedTermLists'] )
			? $attributes['selectedTermLists']
			: '';

		$attributes['featuredImageSizeHeight'] = isset( $attributes['featuredImageSizeHeight'] )
			? $attributes['featuredImageSizeHeight']
			: 0;

		$attributes['featuredImageSizeWidth'] = isset( $attributes['featuredImageSizeWidth'] )
			? $attributes['featuredImageSizeWidth']
			: 0;

		$attributes['featuredImageAlign'] = isset( $attributes['featuredImageAlign'] )
			? $attributes['featuredImageAlign']
			: '';

		// Destructure attributes for readability.
		list(
			'align'                   => $align,
			'className'               => $classnames,
			'selectedTermLists'       => $selected_term_lists,
			'documentsToShow'         => $documents_to_show,
			'excerptLength'           => $excerpt_length,
			'displayDocumentDate'     => $display_date,
			'displayFeaturedImage'    => $display_image,
			'displayDocumentExcerpt'  => $display_excerpt,
			'documentLayout'          => $layout,
			'columns'                 => $columns,
			'featuredImageSizeHeight' => $image_height,
			'featuredImageSizeWidth'  => $image_width,
			'featuredImageAlign'      => $image_align,
			'order'                   => $order,
			'orderBy'                 => $order_by,
			'featuredImageSizeSlug'   => $image_size_slug,
		) = $attributes;

		// Override the site default if it has been set at the block level.
		$this->excerpt_length = $excerpt_length;
		add_filter( 'excerpt_length', array( $this, 'get_excerpt_length' ), 25 );

		// Define query vars.
		$args = array(
			'post_type'        => admin\get_plugin_info( 'post_type' ),
			'posts_per_page'   => $documents_to_show,
			'post_status'      => 'publish',
			'order'            => $order,
			'orderby'          => $order_by,
			'suppress_filters' => false,
		);

		// Add taxonomies to query vars if selected.
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		if ( '' !== $selected_term_lists ) {
			// Begin the taxonomy query.
			$args['tax_query'] = array( 'relation' => 'AND' );

			// Build each taxonomy query array.
			foreach ( $selected_term_lists as $slug => $terms ) {
				// WP_Query uses some different props than the Rest API \(°-°)/.
				if ( 'categories' === $slug ) {
					$slug = 'category';
				}
				if ( 'tags' === $slug ) {
					$slug = 'post_tag';
				}
				if ( ! empty( $terms ) ) {
					$args['tax_query'][] = array(
						'taxonomy' => $slug,
						'field'    => 'id',
						'terms'    => array_column( $terms, 'id' ),
					);
				}
			}
		}
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		// Run the query.
		$posts = get_posts( $args );

		// Build the markup.
		$list_items_markup = '';

		foreach ( $posts as $post ) {
			$document_id = get_post_meta( $post->ID, '_hrswp_document_file_id', true );

			$image_style = '';
			if ( 0 !== $image_width ) {
				$image_style .= sprintf( 'max-width:%spx;', $image_width );
			}
			if ( 0 !== $image_height ) {
				$image_style .= sprintf( 'max-height:%spx;', $image_height );
			}

			$list_items_markup .= sprintf(
				'<li class="wp-block-hrswp-documents-list--list-item"><a href="%s">',
				esc_url( get_permalink( $post ) )
			);

			if ( $display_image ) {
				// If there is a feature image selected it overrides the thumbnail.
				if ( has_post_thumbnail( $post ) ) {
					$image_html = get_the_post_thumbnail(
						$post,
						$image_size_slug,
						array( 'style' => $image_style )
					);
				} else {
					$image_html = wp_get_attachment_image(
						$document_id,
						$image_size_slug,
						false,
						array( 'style' => $image_style )
					);
				}

				if ( ! $image_html ) {
					$registered_sizes = wp_get_registered_image_subsizes();

					$image_html = sprintf(
						'<img width="%1$s" height="%2$s" src="%3$s" class="attachment-%4$s size-%4$s" alt loading="lazy" style="%5$s">',
						$registered_sizes[ $image_size_slug ]['width'],
						$registered_sizes[ $image_size_slug ]['height'],
						plugins_url( 'build/images/document.svg', admin\get_plugin_info( 'plugin_file_uri' ) ),
						$image_size_slug,
						$image_style
					);
				}

				$image_classes = 'wp-block-hrswp-documents-list--featured-image';
				if ( isset( $image_size_slug ) ) {
					$image_classes .= ' size-' . $image_size_slug;
				}
				if ( isset( $image_align ) ) {
					$image_classes .= ' align' . $image_align;
				}

				$list_items_markup .= sprintf(
					'<figure class="%1$s">%2$s</figure>',
					$image_classes,
					$image_html
				);
			}

			$list_items_markup .= '<div class="wp-block-hrswp-documents-list--body">';

			$title = get_the_title( $post );
			if ( ! $title ) {
				$title = __( '(no title)', 'hrswp-documents' );
			}
			$list_items_markup .= sprintf(
				'<span class="wp-block-hrswp-documents-list--heading">%s</span>',
				$title
			);

			if ( isset( $display_excerpt ) && $display_excerpt ) {
				$trimmed_excerpt = get_the_excerpt( $post );

				$list_items_markup .= sprintf(
					'<span class="wp-block-hrswp-documents-list--post-excerpt">%1$s</span>',
					$trimmed_excerpt
				);
			}

			if ( isset( $display_date ) && $display_date ) {
				$list_items_markup .= sprintf(
					'<time class="wp-block-hrswp-documents-list--post-date" datetime="%1$s">%2$s</time>',
					esc_attr( get_the_date( 'c', $post ) ),
					esc_html( get_the_date( '', $post ) )
				);
			}

			$list_items_markup .= "</div></a></li>\n";
		}

		remove_filter( 'excerpt_length', array( $this, 'get_excerpt_length' ), 20 );

		$class = array( 'wp-block-hrswp-documents-list' );

		if ( isset( $display_image ) && $display_image ) {
			$class[] = 'has-feature-image';
		}

		if ( isset( $display_date ) && $display_date ) {
			$class[] = 'has-date';
		}

		if ( isset( $align ) ) {
			$class[] = 'align' . $align;
		}

		if ( isset( $layout ) && 'grid' === $layout ) {
			$class[] = 'is-grid';
		}

		if ( isset( $columns ) && 'grid' === $layout ) {
			$class[] = 'columns-' . $columns;
		}

		if ( isset( $display_excerpt ) && $display_excerpt ) {
			$class[] = 'has-excerpt';
		}

		if ( isset( $classnames ) ) {
			$class[] = $classnames;
		}

		return sprintf(
			'<ul class="%1$s">%2$s</ul>',
			esc_attr( implode( ' ', $class ) ),
			$list_items_markup
		);
	}

	/**
	 * Registers the `hrswp/documents-list` block on the server.
	 */
	public function register() {
		/* translators: Maximum number of words in a post excerpt. */
		$excerpt_length = intval( _x( '55', 'excerpt_length', 'hrswp-documents' ) );
		$excerpt_length = (int) apply_filters( 'excerpt_length', $excerpt_length );

		$post_to_show = (int) get_option( 'posts_per_page', 5 );

		register_block_type(
			'hrswp/documents-list',
			array(
				'attributes'      => array(
					'align'                   => array(
						'type' => 'string',
						'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
					),
					'className'               => array(
						'type' => 'string',
					),
					'selectedTermLists'       => array(
						'type' => 'object',
					),
					'documentsToShow'         => array(
						'type'    => 'number',
						'default' => $post_to_show,
					),
					'displayDocumentExcerpt'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'excerptLength'           => array(
						'type'    => 'number',
						'default' => $excerpt_length,
					),
					'displayDocumentDate'     => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'documentLayout'          => array(
						'type'    => 'string',
						'default' => 'list',
					),
					'columns'                 => array(
						'type'    => 'number',
						'default' => 3,
					),
					'order'                   => array(
						'type'    => 'string',
						'default' => 'desc',
					),
					'orderBy'                 => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'displayFeaturedImage'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'featuredImageAlign'      => array(
						'type' => 'string',
						'enum' => array( 'left', 'center', 'right' ),
					),
					'featuredImageSizeSlug'   => array(
						'type'    => 'string',
						'default' => 'thumbnail',
					),
					'featuredImageSizeWidth'  => array(
						'type'    => 'number',
						'default' => null,
					),
					'featuredImageSizeHeight' => array(
						'type'    => 'number',
						'default' => null,
					),
				),
				'render_callback' => array( $this, 'render' ),
			)
		);
	}
}

/**
 * Creates a new instance of the `DocumentsList` class.
 *
 * @since 1.1.0
 *
 * @return DocumentsList An instance of the DocumentsList class.
 */
function load() {
	return new DocumentsList();
}
add_action( 'init', __NAMESPACE__ . '\load', 25 );
