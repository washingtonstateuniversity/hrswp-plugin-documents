/**
 * External dependencies
 */
import {
	get,
	filter,
	includes,
	invoke,
	isUndefined,
	pickBy,
	remove,
} from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { Component, RawHTML } = wp.element;
const {
	BaseControl,
	CheckboxControl,
	PanelBody,
	Placeholder,
	QueryControls,
	RangeControl,
	Spinner,
	ToggleControl,
	ToolbarGroup,
} = wp.components;
const { __ } = wp.i18n;
const {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
	__experimentalImageSizeControl,
} = wp.blockEditor;
const { withSelect } = wp.data;
const { dateI18n, format, __experimentalGetSettings } = wp.date;

/**
 * Internal dependencies
 */
import { file, pin, list, grid } from './icons';
import {
	MIN_EXCERPT_LENGTH,
	MAX_EXCERPT_LENGTH,
	MAX_POSTS_COLUMNS,
	TERMS_LIST_QUERY,
	taxonomyListToIds,
} from './shared';

class DocumentsListEdit extends Component {
	/**
	 * Adds or removes a taxonomy term from the selected terms attribute.
	 *
	 * @param {string} taxonomy A WP taxonomy `rest_base` value.
	 * @param {Object} term The selected term to add or remove.
	 */
	toggleSelectedTerms( taxonomy, term ) {
		const { attributes, setAttributes } = this.props;
		const { selectedTermLists } = attributes;

		const allTerms = ! isUndefined( selectedTermLists )
			? selectedTermLists
			: {};
		const taxonomyTerms = ! isUndefined( allTerms[ taxonomy ] )
			? allTerms[ taxonomy ]
			: ( allTerms[ taxonomy ] = [] );
		const hasTerm = includes(
			taxonomyListToIds( allTerms, taxonomy ),
			term.id
		);

		const newTerms = hasTerm
			? remove( taxonomyTerms, ( value ) => {
					return value.id !== term.id;
			  } )
			: [ ...taxonomyTerms, term ];

		allTerms[ taxonomy ] = newTerms;

		setAttributes( { selectedTermLists: allTerms } );
	}

	render() {
		const {
			attributes,
			setAttributes,
			className,
			imageSizeOptions,
			documentsList,
			taxonomies,
			termLists,
			defaultImageWidth,
			defaultImageHeight,
		} = this.props;
		const {
			displayFeaturedImage,
			displayDocumentExcerpt,
			displayDocumentDate,
			displayDocumentTitle,
			documentLayout,
			columns,
			order,
			orderBy,
			selectedTermLists,
			documentsToShow,
			excerptLength,
			featuredImageAlign,
			featuredImageSizeSlug,
			featuredImageSizeWidth,
			featuredImageSizeHeight,
		} = attributes;

		if ( ! featuredImageSizeSlug ) {
			setAttributes( { featuredImageSizeSlug: 'thumbnail' } );
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Display settings' ) }>
					<ToggleControl
						label={ __( 'Display title' ) }
						checked={ displayDocumentTitle }
						onChange={ ( value ) =>
							setAttributes( { displayDocumentTitle: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Display excerpt' ) }
						checked={ displayDocumentExcerpt }
						onChange={ ( value ) =>
							setAttributes( { displayDocumentExcerpt: value } )
						}
					/>
					{ displayDocumentExcerpt && (
						<RangeControl
							label={ __( 'Max number of words in excerpt' ) }
							value={ excerptLength }
							onChange={ ( value ) =>
								setAttributes( { excerptLength: value } )
							}
							min={ MIN_EXCERPT_LENGTH }
							max={ MAX_EXCERPT_LENGTH }
						/>
					) }
					<ToggleControl
						label={ __( 'Display document date' ) }
						checked={ displayDocumentDate }
						onChange={ ( value ) =>
							setAttributes( { displayDocumentDate: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Display document image' ) }
						checked={ displayFeaturedImage }
						onChange={ ( value ) =>
							setAttributes( { displayFeaturedImage: value } )
						}
					/>
					{ displayFeaturedImage && (
						<>
							<__experimentalImageSizeControl
								onChange={ ( value ) => {
									const newAttrs = {};
									if ( value.hasOwnProperty( 'width' ) ) {
										newAttrs.featuredImageSizeWidth =
											value.width;
									}
									if ( value.hasOwnProperty( 'height' ) ) {
										newAttrs.featuredImageSizeHeight =
											value.height;
									}
									setAttributes( newAttrs );
								} }
								slug={ featuredImageSizeSlug }
								width={ featuredImageSizeWidth }
								height={ featuredImageSizeHeight }
								imageWidth={ defaultImageWidth }
								imageHeight={ defaultImageHeight }
								imageSizeOptions={ imageSizeOptions }
								onChangeImage={ ( value ) =>
									setAttributes( {
										featuredImageSizeSlug: value,
										featuredImageSizeWidth: undefined,
										featuredImageSizeHeight: undefined,
									} )
								}
							/>
							<BaseControl className="block-editor-image-alignment-control__row">
								<BaseControl.VisualLabel>
									{ __( 'Image alignment' ) }
								</BaseControl.VisualLabel>
								<BlockAlignmentToolbar
									value={ featuredImageAlign }
									onChange={ ( value ) =>
										setAttributes( {
											featuredImageAlign: value,
										} )
									}
									controls={ [ 'left', 'center', 'right' ] }
									isCollapsed={ false }
								/>
							</BaseControl>
						</>
					) }
				</PanelBody>

				<PanelBody
					className={ `${ className } taxonomy-filter` }
					title={ __( 'Filtering' ) }
					initialOpen={ false }
				>
					{ taxonomies.map( ( taxonomy ) => (
						<PanelBody
							className={ 'taxonomy-filter--body' }
							key={ taxonomy.slug }
							title={ taxonomy.name }
							initialOpen={ false }
						>
							<ul className="edit__checklist">
								{ termLists[ taxonomy.slug ] &&
									termLists[ taxonomy.slug ].map(
										( term ) => (
											<li
												key={ term.id }
												className="components-checkbox-control__label"
											>
												<CheckboxControl
													label={ term.name }
													checked={ includes(
														taxonomyListToIds(
															selectedTermLists,
															taxonomy.rest_base
														),
														term.id
													) }
													onChange={ () => {
														this.toggleSelectedTerms(
															taxonomy.rest_base,
															term
														);
													} }
												/>
											</li>
										)
									) }
							</ul>
						</PanelBody>
					) ) }
				</PanelBody>

				<PanelBody
					title={ __( 'Order and number' ) }
					initialOpen={ false }
				>
					<QueryControls
						{ ...{ order, orderBy } }
						numberOfItems={ documentsToShow }
						onOrderChange={ ( value ) =>
							setAttributes( { order: value } )
						}
						onOrderByChange={ ( value ) =>
							setAttributes( { orderBy: value } )
						}
						onNumberOfItemsChange={ ( value ) =>
							setAttributes( { documentsToShow: value } )
						}
					/>

					{ documentLayout === 'grid' && (
						<RangeControl
							label={ __( 'Maximum columns' ) }
							value={ columns }
							onChange={ ( value ) =>
								setAttributes( { columns: value } )
							}
							min={ 2 }
							max={
								! hasPosts
									? MAX_POSTS_COLUMNS
									: Math.min(
											MAX_POSTS_COLUMNS,
											documentsList.length
									  )
							}
							required
						/>
					) }
				</PanelBody>
			</InspectorControls>
		);

		const hasPosts = Array.isArray( documentsList ) && documentsList.length;
		if ( ! hasPosts ) {
			return (
				<>
					{ inspectorControls }
					<Placeholder icon={ pin } label={ __( 'Posts' ) }>
						{ ! Array.isArray( documentsList ) ? (
							<Spinner />
						) : (
							__( 'No posts found.' )
						) }
					</Placeholder>
				</>
			);
		}

		// Removing posts from display should be instant.
		const displayPosts =
			documentsList.length > documentsToShow
				? documentsList.slice( 0, documentsToShow )
				: documentsList;

		const layoutControls = [
			{
				icon: list,
				title: __( 'List view' ),
				onClick: () => setAttributes( { documentLayout: 'list' } ),
				isActive: documentLayout === 'list',
			},
			{
				icon: grid,
				title: __( 'Grid view' ),
				onClick: () => setAttributes( { documentLayout: 'grid' } ),
				isActive: documentLayout === 'grid',
			},
		];

		const dateFormat = __experimentalGetSettings().formats.date;

		return (
			<>
				{ inspectorControls }
				<BlockControls>
					<ToolbarGroup controls={ layoutControls } />
				</BlockControls>
				<ul
					className={ classnames( className, {
						'image-only':
							displayFeaturedImage &&
							! displayDocumentDate &&
							! displayDocumentExcerpt &&
							! displayDocumentTitle,
						'is-grid': documentLayout === 'grid',
						'has-feature-image': displayFeaturedImage,
						'has-date': displayDocumentDate,
						'has-excerpt': displayDocumentExcerpt,
						[ `columns-${ columns }` ]: documentLayout === 'grid',
					} ) }
				>
					{ displayPosts.map( ( post, i ) => {
						const titleTrimmed = invoke( post, [
							'title',
							'rendered',
							'trim',
						] );

						let excerpt = post.excerpt.rendered;
						const excerptElement = document.createElement( 'div' );
						excerptElement.innerHTML = excerpt;
						excerpt =
							excerptElement.textContent ||
							excerptElement.innerText ||
							'';

						const imageSourceUrl = post.featuredImageSourceUrl;
						const imageClasses = classnames( {
							'wp-block-hrswp-documents-list--featured-image': true,
							[ `size-${ featuredImageSizeSlug }` ]: !! featuredImageSizeSlug,
							[ `align${ featuredImageAlign }` ]: !! featuredImageAlign,
						} );

						const needsReadMore =
							excerptLength <
								excerpt.trim().split( ' ' ).length &&
							post.excerpt.raw === '';

						const postExcerpt = needsReadMore ? (
							<>
								{ excerpt
									.trim()
									.split( ' ', excerptLength )
									.join( ' ' ) }
								{ /* translators: excerpt truncation character, default …  */ }
								{ __( ' … ' ) }
							</>
						) : (
							excerpt
						);

						return (
							<li
								className="wp-block-hrswp-documents-list--list-item"
								key={ i }
							>
								<a
									href={ post.link }
									target="_blank"
									rel="noreferrer noopener"
								>
									{ displayFeaturedImage && (
										<figure
											className={ imageClasses }
											style={ {
												maxWidth: featuredImageSizeWidth,
												maxHeight: featuredImageSizeHeight,
											} }
										>
											{ imageSourceUrl ? (
												<img
													src={ imageSourceUrl }
													alt=""
												/>
											) : (
												file
											) }
										</figure>
									) }
									<div className="wp-block-hrswp-documents-list--body">
										{ displayDocumentTitle && (
											<span className="wp-block-hrswp-documents-list--heading">
												{ titleTrimmed ? (
													<RawHTML>
														{ titleTrimmed }
													</RawHTML>
												) : (
													__( '(no title)' )
												) }
											</span>
										) }
										{ displayDocumentExcerpt && (
											<span className="wp-block-hrswp-documents-list--post-excerpt">
												{ postExcerpt }
											</span>
										) }
										{ displayDocumentDate &&
											post.date_gmt && (
												<time
													className="wp-block-hrswp-documents-list--post-date"
													dateTime={ format(
														'c',
														post.date_gmt
													) }
												>
													{ dateI18n(
														dateFormat,
														post.date_gmt
													) }
												</time>
											) }
									</div>
								</a>
							</li>
						);
					} ) }
				</ul>
			</>
		);
	}
}

export default withSelect( ( select, props ) => {
	const {
		featuredImageSizeSlug,
		documentsToShow,
		order,
		orderBy,
		selectedTermLists,
	} = props.attributes;
	const { getEntityRecords, getMedia, getTaxonomies } = select( 'core' );
	const { getSettings } = select( 'core/block-editor' );
	const { imageSizes, imageDimensions } = getSettings();

	const DocumentsListQuery = pickBy(
		{
			order,
			orderby: orderBy,
			per_page: documentsToShow,
		},
		( value ) => ! isUndefined( value )
	);
	if ( ! isUndefined( selectedTermLists ) ) {
		Object.entries( selectedTermLists ).forEach( ( [ slug, terms ] ) => {
			DocumentsListQuery[ slug ] = terms.map( ( term ) => term.id );
		} );
	}

	const posts = getEntityRecords(
		'postType',
		'hrswp_documents',
		DocumentsListQuery
	);

	const allTaxonomies = getTaxonomies( TERMS_LIST_QUERY );
	const taxonomies = filter( allTaxonomies, ( taxonomy ) =>
		includes( taxonomy.types, 'post' )
	);
	const termLists = {};
	taxonomies.forEach( ( { slug } ) => {
		Object.defineProperty( termLists, slug, {
			value: getEntityRecords( 'taxonomy', slug, TERMS_LIST_QUERY ),
		} );
	} );

	const imageSizeOptions = imageSizes
		.filter( ( { slug } ) => slug !== 'full' )
		.map( ( { name, slug } ) => ( { value: slug, label: name } ) );

	const documentsList = ! Array.isArray( posts )
		? posts
		: posts.map( ( post ) => {
				let image, url;

				if ( 0 !== post.featured_media ) {
					image = getMedia( post.featured_media );
				} else if ( 0 !== post.meta._hrswp_document_file_id ) {
					image = getMedia( post.meta._hrswp_document_file_id );
				}

				if ( image ) {
					// Returns the appropriate image src url from the image object.
					url = get(
						image,
						[
							'media_details',
							'sizes',
							featuredImageSizeSlug,
							'source_url',
						],
						null
					);

					if ( ! url ) {
						url = get( image, 'source_url', null );
					}
				}

				return { ...post, featuredImageSourceUrl: url };
		  } );

	return {
		defaultImageWidth: get(
			imageDimensions,
			[ featuredImageSizeSlug, 'width' ],
			0
		),
		defaultImageHeight: get(
			imageDimensions,
			[ featuredImageSizeSlug, 'height' ],
			0
		),
		imageSizeOptions,
		taxonomies,
		termLists,
		documentsList,
	};
} )( DocumentsListEdit );
