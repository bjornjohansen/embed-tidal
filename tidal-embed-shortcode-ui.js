function TidalEmbedShortcodeUIUpdateTypeListener( changed, collection, shortcode ) {

	function attributeByName(name) {
		return _.find(
			collection,
			function( viewModel ) {
				return name === viewModel.model.get( 'attr' );
			}
		);
	}

	var updatedVal = changed.value
		relatedIDfield = attributeByName( 'related-id' );

	if ( updatedVal === 'v' ) {
		relatedIDfield.$el.show();
	} else {
		relatedIDfield.$el.hide();
	}
}

wp.shortcake.hooks.addAction( 'tidal.type', TidalEmbedShortcodeUIUpdateTypeListener );
