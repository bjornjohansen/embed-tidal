/**
 * Shortcake integration.
 *
 * @package EmbedTidal
 */

/**
 * Shortcake UI update listener.
 */
function TidalEmbedShortcodeUIUpdateTypeListener( changed, collection, shortcode ) {
	var updatedVal     = changed.value,
	relatedIDfield = attributeByName( 'related-id' );

	function attributeByName( name ) {
		return _.find(
			collection,
			function( viewModel ) {
				return name === viewModel.model.get( 'attr' );
			}
		);
	}

	if ( 'v' === updatedVal ) {
		relatedIDfield.$el.show();
	} else {
		relatedIDfield.$el.hide();
	}
}

wp.shortcake.hooks.addAction( 'tidal.type', TidalEmbedShortcodeUIUpdateTypeListener );
