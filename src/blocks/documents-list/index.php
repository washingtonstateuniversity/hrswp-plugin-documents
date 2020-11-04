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
		$args = array(
			'post_type'        => admin\get_plugin_info( 'post_type' ),
			'posts_per_page'   => $attributes['documentsToShow'],
			'post_status'      => 'publish',
			'order'            => $attributes['order'],
			'orderby'          => $attributes['orderBy'],
			'suppress_filters' => false,
		);

		$this->excerpt_length = $attributes['excerptLength'];
		add_filter( 'excerpt_length', array( $this, 'get_excerpt_length' ), 25 );

		// Taxonomy handling.
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		if ( isset( $attributes['selectedTermLists'] ) && ! empty( $attributes['selectedTermLists'] ) ) {
			// Begin the query.
			$args['tax_query'] = array( 'relation' => 'AND' );

			// Build each query array.
			foreach ( $attributes['selectedTermLists'] as $slug => $terms ) {
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

		$posts = get_posts( $args );

		$list_items_markup = '';
		foreach ( $posts as $post ) {
			$document_id = get_post_meta( $post->ID, '_hrswp_document_file_id', true );

			$image_style = '';
			if ( isset( $attributes['featuredImageSizeWidth'] ) ) {
				$image_style .= sprintf( 'max-width:%spx;', $attributes['featuredImageSizeWidth'] );
			}
			if ( isset( $attributes['featuredImageSizeHeight'] ) ) {
				$image_style .= sprintf( 'max-height:%spx;', $attributes['featuredImageSizeHeight'] );
			}

			$list_items_markup .= '<div class="wp-block-hrswp-documents-list--list-item">';
			if ( $attributes['displayFeaturedImage'] ) {
				// If there is a feature image selected it overrides the thumbnail.
				if ( has_post_thumbnail( $post ) ) {
					$image_html = get_the_post_thumbnail(
						$post,
						$attributes['featuredImageSizeSlug'],
						array( 'style' => $image_style )
					);
				} else {
					$image_html = wp_get_attachment_image(
						$document_id,
						$attributes['featuredImageSizeSlug'],
						true,
						array( 'style' => $image_style )
					);
				}

				$image_classes = 'wp-block-hrswp-documents-list--featured-image';
				if ( isset( $attributes['featuredImageSizeSlug'] ) ) {
					$image_classes .= ' size-' . $attributes['featuredImageSizeSlug'];
				}
				if ( isset( $attributes['featuredImageAlign'] ) ) {
					$image_classes .= ' align' . $attributes['featuredImageAlign'];
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
				'<h3 class="wp-block-hrswp-documents-list--heading"><a href="%1$s">%2$s</a></h3>',
				esc_url( get_permalink( $post ) ),
				$title
			);

			if ( isset( $attributes['displayDocumentExcerpt'] ) && $attributes['displayDocumentExcerpt'] ) {
				$trimmed_excerpt = get_the_excerpt( $post );

				$list_items_markup .= sprintf(
					'<p class="wp-block-hrswp-documents-list--post-excerpt">%1$s</p>',
					$trimmed_excerpt
				);
			}

			$post_meta_markup = '';
			if (
				isset( $attributes['displayDocumentCategory'] ) ||
				isset( $attributes['displayDocumentTag'] ) ||
				isset( $attributes['displayDocumentTaxonomy'] )
			) {
				$taxonomy_names = get_object_taxonomies( $post->post_type );

				// Move `post_tags` to the end.
				$taxonomy_names[] = array_splice(
					$taxonomy_names,
					array_search( 'post_tag', $taxonomy_names, true ),
					1
				)[0];

				foreach ( $taxonomy_names as $taxonomy_name ) {
					if (
						'category' === $taxonomy_name &&
						isset( $attributes['displayDocumentCategory'] ) &&
						$attributes['displayDocumentCategory']
					) {
						$prefix = sprintf(
							'<p class="wp-block-hrswp-documents-list--%1$s-list"><span>%2$s: </span>',
							esc_attr( $taxonomy_name ),
							__( 'More on', 'hrswp-documents' )
						);

						$post_meta_markup .= get_the_term_list( $post->ID, $taxonomy_name, $prefix, ', ', '</p>' );
					} elseif (
						'post_tag' === $taxonomy_name &&
						isset( $attributes['displayDocumentTag'] ) &&
						$attributes['displayDocumentTag']
					) {
						$prefix = sprintf(
							'<p class="wp-block-hrswp-documents-list--%1$s-list"><span>%2$s: </span>',
							esc_attr( $taxonomy_name ),
							__( 'Tagged', 'hrswp-documents' )
						);

						$post_meta_markup .= get_the_term_list( $post->ID, $taxonomy_name, $prefix, ', ', '</p>' );
					} else {
						if (
							'post_tag' !== $taxonomy_name &&
							'category' !== $taxonomy_name &&
							isset( $attributes['displayDocumentTaxonomy'] ) &&
							$attributes['displayDocumentTaxonomy']
						) {
							$taxonomy_object = get_taxonomy( $taxonomy_name );
							$prefix          = sprintf(
								'<p class="wp-block-hrswp-documents-list--%1$s-list"><span>%2$s: </span>',
								esc_attr( $taxonomy_name ),
								esc_html( $taxonomy_object->labels->singular_name )
							);

							$post_meta_markup .= get_the_term_list( $post->ID, $taxonomy_name, $prefix, ', ', '</p>' );
						}
					}
				}
			}
			if ( isset( $attributes['displayDocumentDate'] ) && $attributes['displayDocumentDate'] ) {
				$post_meta_markup .= sprintf(
					'<p class="wp-block-hrswp-documents-list--post-date">%1$s <time datetime="%2$s">%3$s</time></p>',
					__( 'Published on', 'hrswp-documents' ),
					esc_attr( get_the_date( 'c', $post ) ),
					esc_html( get_the_date( '', $post ) )
				);
			}

			if ( '' !== $post_meta_markup ) {
				$list_items_markup .= sprintf(
					'<div class="wp-block-hrswp-documents-list--meta">%1$s</div>',
					$post_meta_markup
				);
			}

			$list_items_markup .= "</div></div>\n";
		}

		remove_filter( 'excerpt_length', array( $this, 'get_excerpt_length' ), 20 );

		$class = array( 'wp-block-hrswp-documents-list' );

		if ( isset( $attributes['displayFeaturedImage'] ) && $attributes['displayFeaturedImage'] ) {
			$class[] = 'has-feature-image';
		}

		if ( isset( $attributes['displayDocumentDate'] ) && $attributes['displayDocumentDate'] ) {
			$class[] = 'has-date';
		}

		if ( isset( $attributes['align'] ) ) {
			$class[] = 'align' . $attributes['align'];
		}

		if ( isset( $attributes['documentLayout'] ) && 'grid' === $attributes['documentLayout'] ) {
			$class[] = 'is-grid';
		}

		if ( isset( $attributes['columns'] ) && 'grid' === $attributes['documentLayout'] ) {
			$class[] = 'columns-' . $attributes['columns'];
		}

		if ( isset( $attributes['displayDocumentExcerpt'] ) && $attributes['displayDocumentExcerpt'] ) {
			$class[] = 'has-excerpt';
		}

		if ( isset( $attributes['className'] ) ) {
			$class[] = $attributes['className'];
		}

		return sprintf(
			'<div class="%1$s">%2$s</div>',
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
					'displayDocumentCategory' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'displayDocumentTag'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'displayDocumentTaxonomy' => array(
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
