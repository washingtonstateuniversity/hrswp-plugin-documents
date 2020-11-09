/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { getBlobByURL, isBlobURL, revokeBlobURL } = wp.blob;
const {
	Animate,
	Button,
	ClipboardButton,
	Disabled,
	PanelBody,
	PanelRow,
	ToggleControl,
	withNotices,
} = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { dateI18n } = wp.date;
const {
	BlockControls,
	BlockIcon,
	InspectorControls,
	MediaPlaceholder,
	MediaReplaceFlow,
	URLInput,
} = wp.blockEditor;
const { Component } = wp.element;
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import { icon } from './icons';

/**
 * Constants
 */
const MEDIA_ID_META_NAME = '_hrswp_document_file_id';
const MEDIA_HREF_META_NAME = '_hrswp_document_file_href';

class FileEdit extends Component {
	constructor() {
		super( ...arguments );

		this.onSelectFile = this.onSelectFile.bind( this );
		this.confirmCopyURL = this.confirmCopyURL.bind( this );
		this.resetCopyConfirmation = this.resetCopyConfirmation.bind( this );
		this.onUploadError = this.onUploadError.bind( this );
		this.clearMedia = this.clearMedia.bind( this );

		this.state = {
			hasError: false,
			showCopyConfirmation: false,
		};
	}

	componentDidMount() {
		const { mediaHref, mediaUpload, noticeOperations } = this.props;

		// Upload a file drag-and-dropped into the editor
		if ( isBlobURL( mediaHref ) ) {
			const file = getBlobByURL( mediaHref );

			mediaUpload( {
				filesList: [ file ],
				onFileChange: ( [ media ] ) => this.onSelectFile( media ),
				onError: ( message ) => {
					this.setState( { hasError: true } );
					noticeOperations.createErrorNotice( message );
				},
			} );

			revokeBlobURL( mediaHref );
		}
	}

	componentDidUpdate( prevProps ) {
		// Reset copy confirmation state when block is deselected
		if ( prevProps.isSelected && ! this.props.isSelected ) {
			this.setState( { showCopyConfirmation: false } );
		}
	}

	onSelectFile( media ) {
		if ( media && media.url ) {
			this.setState( { hasError: false } );
			this.props.updateMeta( media.id, MEDIA_ID_META_NAME );
			this.props.updateMeta( media.url, MEDIA_HREF_META_NAME );
		}
	}

	clearMedia() {
		this.setState( { hasError: false } );
		this.props.updateMeta( 0, MEDIA_ID_META_NAME );
	}

	onUploadError( message ) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
	}

	confirmCopyURL() {
		this.setState( { showCopyConfirmation: true } );
	}

	resetCopyConfirmation() {
		this.setState( { showCopyConfirmation: false } );
	}

	render() {
		const {
			attributes,
			className,
			isSelected,
			noticeUI,
			media,
			mediaHref,
			permalink,
			setAttributes,
			useFeatureImage,
		} = this.props;
		const { hasError, showCopyConfirmation } = this.state;
		const { useExternalFile } = attributes;
		const image = media ? media.media_details.sizes.medium : undefined;
		const mediaId = media ? media.id : undefined;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Document source settings' ) }>
					<ToggleControl
						label={ __( 'Use external file' ) }
						checked={ useExternalFile }
						onChange={ ( value ) =>
							setAttributes( { useExternalFile: value } )
						}
					/>
					{ ! useExternalFile && (
						<PanelRow>
							<Button
								isSecondary
								isSmall
								className="block-library-hrswp-documents__reset-button"
								onClick={ this.clearMedia }
							>
								{ __( 'Clear Document' ) }
							</Button>
						</PanelRow>
					) }
				</PanelBody>
			</InspectorControls>
		);

		if ( ! useExternalFile ) {
			if ( ! media || hasError ) {
				return (
					<>
						{ inspectorControls }
						<MediaPlaceholder
							icon={ <BlockIcon icon={ icon } /> }
							labels={ {
								title: __( 'File' ),
								instructions: __(
									'Upload a file or pick one from your media library.'
								),
							} }
							onSelect={ this.onSelectFile }
							notices={ noticeUI }
							onError={ this.onUploadError }
							accept="*"
						/>
					</>
				);
			}
		}

		const classes = classnames( className, {
			'is-transient': isBlobURL( mediaHref ),
		} );

		return (
			<>
				{ inspectorControls }
				{ ! useExternalFile && (
					<>
						<BlockControls>
							<MediaReplaceFlow
								mediaId={ mediaId }
								mediaURL={ mediaHref }
								accept="*"
								onSelect={ this.onSelectFile }
								onError={ this.onUploadError }
							/>
						</BlockControls>
						<Animate
							type={ isBlobURL( mediaHref ) ? 'loading' : null }
						>
							{ ( { className: animateClassName } ) => (
								<div
									className={ classnames(
										classes,
										animateClassName
									) }
								>
									<Disabled>
										{ media && (
											<>
												<img
													src={ image.source_url }
													alt={ '' }
													width={ image.width }
													height={ image.height }
												/>
												<p>
													<strong>
														{ __( 'File title: ' ) }
													</strong>
													{ media.title.rendered }
												</p>
												<p>
													<strong>
														{ __( 'Uploaded: ' ) }
													</strong>
													<time
														dateTime={ dateI18n(
															'c',
															media.date
														) }
													>
														{ dateI18n(
															'F j, Y',
															media.date
														) }
													</time>
												</p>
											</>
										) }
									</Disabled>
									{ isSelected && (
										<ClipboardButton
											isSecondary
											text={ permalink }
											className={
												'wp-block-file__copy-url-button'
											}
											onCopy={ this.confirmCopyURL }
											onFinishCopy={
												this.resetCopyConfirmation
											}
											disabled={ isBlobURL( permalink ) }
										>
											{ showCopyConfirmation
												? __( 'Copied!' )
												: __( 'Copy URL' ) }
										</ClipboardButton>
									) }
								</div>
							) }
						</Animate>
					</>
				) }
				{ useExternalFile && (
					<div
						className={ classnames(
							className,
							'use-external-file'
						) }
					>
						<div
							className={
								'components-placeholder block-editor-media-placeholder is-large'
							}
						>
							<div className={ 'components-placeholder__label' }>
								{ __( 'File URL' ) }
							</div>
							<div
								className={
									'components-placeholder__instructions'
								}
							>
								{ __(
									'Enter the full URL of the external document.'
								) }
							</div>
							<URLInput
								value={ mediaHref }
								onChange={ ( value ) =>
									this.props.updateMeta(
										value,
										MEDIA_HREF_META_NAME
									)
								}
							/>
							{ useFeatureImage && media && (
								<img
									src={ image.source_url }
									alt={ '' }
									width={ image.width }
									height={ image.height }
								/>
							) }
						</div>
					</div>
				) }
			</>
		);
	}
}

export default compose( [
	withDispatch( ( dispatch ) => {
		return {
			updateMeta: ( value, metaField ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { [ metaField ]: value },
				} );
			},
		};
	} ),
	withSelect( ( select ) => {
		const { getMedia } = select( 'core' );
		const { getSettings } = select( 'core/block-editor' );
		const { mediaUpload } = getSettings();
		const featureId = select( 'core/editor' ).getEditedPostAttribute(
			'featured_media'
		);
		const mediaId = select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)[ MEDIA_ID_META_NAME ];
		let media;
		let useFeatureImage = false;

		if ( 0 !== featureId ) {
			media = featureId === undefined ? undefined : getMedia( featureId );
			useFeatureImage = true;
		} else {
			media = mediaId === 0 ? undefined : getMedia( mediaId );
		}

		return {
			media,
			permalink: select( 'core/editor' ).getPermalink(),
			mediaHref: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[
				MEDIA_HREF_META_NAME
			],
			mediaUpload,
			useFeatureImage,
		};
	} ),
	withNotices,
] )( FileEdit );
