/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { getBlobByURL, isBlobURL, revokeBlobURL } = wp.blob;
const { Animate, ClipboardButton, Disabled, withNotices } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { dateI18n } = wp.date;
const {
	BlockControls,
	BlockIcon,
	MediaPlaceholder,
	MediaReplaceFlow,
} = wp.blockEditor;
const { Component } = wp.element;
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import { file as icon } from './icons';

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
			className,
			isSelected,
			noticeUI,
			media,
			mediaId,
			mediaHref,
		} = this.props;
		const { hasError, showCopyConfirmation } = this.state;

		const image = media ? media.media_details.sizes.medium : undefined;

		if ( ! mediaHref || hasError ) {
			return (
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
			);
		}

		const classes = classnames( className, {
			'is-transient': isBlobURL( mediaHref ),
		} );

		return (
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
				<Animate type={ isBlobURL( mediaHref ) ? 'loading' : null }>
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
									text={ mediaHref }
									className={
										'wp-block-file__copy-url-button'
									}
									onCopy={ this.confirmCopyURL }
									onFinishCopy={ this.resetCopyConfirmation }
									disabled={ isBlobURL( mediaHref ) }
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
		const mediaId = select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)[ MEDIA_ID_META_NAME ];

		return {
			media: mediaId === undefined ? undefined : getMedia( mediaId ),
			postType: select( 'core/editor' ).getCurrentPostType(),
			mediaId,
			mediaHref: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[
				MEDIA_HREF_META_NAME
			],
			mediaUpload,
		};
	} ),
	withNotices,
] )( FileEdit );
