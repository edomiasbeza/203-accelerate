( function( api ) {

	// Extends our custom "vw-painter" section.
	api.sectionConstructor['vw-painter'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );