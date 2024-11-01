$s = jQuery.noConflict();
$s( document ).ready(
	function ($) {

		/**
		 * ===============================================
		 * Add a new destination row
		 */
		$( document ).on(
			'click',
			'.btn-multi-desti-add',
			function (e) {
				e.preventDefault();
				const controlForm = $( this ).parents( '.mulit_desti_row_section' ),
				currentEntry      = $( this ).parents( '.mulit_desti_row_section .mulit_desti_row:first' ),
				newEntry          = $( currentEntry.clone() ).appendTo( controlForm );

				newEntry.addClass( 'srm-desti-empty' );
				newEntry.find( 'input' ).val( '' );
				newEntry.find( '.mtd_icon_preview' ).find( ".faicon_preview" ).addClass( 'fas fa-question' );
				newEntry.find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' ).removeAttr( 'style' );
				newEntry.find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' ).removeClass().addClass( "multi-desti-buttons" );
				controlForm.find( '.mulit_desti_row:not(:last) .mr_add_remove' )
				.html( '<div class="btn-multi-desti-remove" title="Remove Destination"><span class="fas fa-minus"></span></div>' );
			}
		).on(
			'click',
			'.btn-multi-desti-remove',
			function (e) {
				$( this ).parents( '.mulit_desti_row:first' ).remove();
				e.preventDefault();
				return false;
			}
		);
		/**
		 * END: Add a new destination row
		 * ===============================================
		 */

		/**
		 * ===============================================
		 * Drag-n-Drop Destination Row
		 */
		var fixHelperModified = function (e, tr) {
			var $originals = tr.children();
			var $helper    = tr.clone();
			$helper.children().each(
				function (index) {
					$( this ).width( $originals.eq( index ).width() )
				}
			);
			return $helper;
		};

		$( "#srm-multi-destinations tbody" ).sortable(
			{
				helper: fixHelperModified,
				cursor: "move",
				containment: "parent",
				handle: ".srm-sort-handle",
				revert: false,
				placeholder: "srm-sortable-placeholder",
				items: '.mulit_desti_row:not(.srm-desti-empty)',
				start: function (e, ui) {
					ui.placeholder.height( ui.item.height() );
				}
			}
		).disableSelection();
		/**
		 * END: Drag-n-Drop Destination Row
		 * ===============================================
		 */

		/**
		 * ===============================================
		 * Present a preview of selected icon
		 */
		$( '.desti_icon' ).on(
			'change',
			function () {
				const optionVal = $( this ).val();
				const icons     = funnel_vars.icons; // Obtain Icons from funnel PHP localization object
				if (optionVal.toUpperCase() in icons) {
					const previewEle = $( this ).parents( '.mulit_desti_row' ).find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' );
					const icon       = icons[optionVal.toUpperCase()].class;
					previewEle.removeClass().find( 'span' ).removeClass();
					previewEle.addClass( 'multi-desti-buttons ' + optionVal.toLowerCase() ).html( ` < span class = 'faicon_preview ${icon}' > < / span > ` );
					$( this ).parents( '.mulit_desti_row' ).find( '.td-color-field' ).find( '[name="srm_desti_color[]"]' ).prop( 'readonly', false ).attr( 'placeholder', '#666' );
				}
			}
		);

		$( '[name="srm_desti_color[]"]' ).on(
			'keyup',
			function () {
				const val = $( this ).val();
				$( this ).parents( '.mulit_desti_row' ).find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' ).css( 'color', val );
			}
		);

		$( '[name="srm_desti_bg_color[]"]' ).on(
			'keyup',
			function () {
				const val = $( this ).val();
				$( this ).parents( '.mulit_desti_row' ).find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' ).css( 'background', val );
			}
		);

		$( '[name="desti_icon[]"]' ).change(
			function () {
				$( '[name="srm_desti_color[]"]' ).prop( 'readonly', false );
			}
		);
		/**
		 * END: Present a preview of selected icon
		 * ===============================================
		 */

		/**
		 * ===============================================
		 * Select WP Media for Icon, clear any previously selected predefined icon
		 */
		$( '.upload_icon_button' ).on(
			'click',
			function (event) {
				event.preventDefault();
				let fileFrame;
				const parent        = $( this ).parents( '.mulit_desti_row' );
				const wpMediaPostId = wp.media.model.settings.post.id; // Store the old id
				const setToPostId   = $( this ).parents( '.photo_upload_area' ).find( '.icon_photo_id' ).val();
				// If the media frame already exists, reopen it.
				if (fileFrame) {
					// Set the post ID to what we want
					fileFrame.uploader.uploader.param( 'post_id', setToPostId );
					// Open frame
					fileFrame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = setToPostId;
				}
				// Create the media frame.
				fileFrame = wp.media.frames.file_frame = wp.media(
					{
						title: 'Select Destination Icon', library: {type: 'image'}, button: {
							text: 'Select Image',
						}, multiple: false
					}
				);
				// When an image is selected, run a callback.
				fileFrame.on(
					'select',
					function () {
						// We set multiple to false so only get one image from the uploader
						const attachment = fileFrame.state().get( 'selection' ).first().toJSON();
						parent.find( '.desti_icon' ).val( $( ".desti_icon option:first" ).val() );
						const previewEle = parent.find( '.mtd_icon_preview' ).find( '.multi-desti-buttons' );
						previewEle.removeClass();
						const attachmentUrl   = attachment.url;
						const attachmentTitle = attachment.title;
						previewEle.addClass( 'multi-desti-buttons' ).html( ` < img class = 'icon_image' src = '${attachmentUrl}' alt = '${attachmentTitle}' / > ` );
						parent.find( '.photo_upload_area' ).find( '.icon_photo_id' ).val( attachment.id );
						parent.find( '.td-color-field' ).find( '[name="srm_desti_color[]"]' ).prop( 'readonly', true ).attr( 'placeholder', 'Not Available' ).val( "" );
						// Restore the main post ID
						wp.media.model.settings.post.id = wpMediaPostId;
					}
				);
				// Finally, open the modal
				fileFrame.open();
			}
		);
		// Restore the main ID when the add media button is pressed
		$( 'a.add_media' ).on(
			'click',
			function () {
				wp.media.model.settings.post.id = wp_media_post_id;
			}
		);
		/**
		 * END: Select WP Media for Icon, clear any previously selected predefined icon
		 * ===============================================
		 */

	}
);
