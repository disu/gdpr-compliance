(function( $ ) {
	var CF = function( element ) {
        this.$el = $( element );
		if( this.$el.length ) {
            this.init();
        }
    };
 
    CF.prototype = {
        init: function() {
            this.selectImage();
        },
        selectImage: function() {
            this.$el.each(function() {
                var $btn = $( this ),
                    $input = $btn.parent().prev(),
                    $img = $input.prev().find( "img" );
 
				$btn.click(function() {
					if( wp.media.frames.gk_frame ) {
						wp.media.frames.gk_frame.open();
					} else {
						wp.media.frames.gk_frame = wp.media({
							title: "Seleziona immagine",
							multiple: false,
							library: { type: "image" },
							button: { text: "Usa immagine" }
						});
						var selectMedia = function() {
							var selection = wp.media.frames.gk_frame.state().get( "selection" );
							if( !selection ) { return; }
							selection.each(function( attachment ) {
								var attrs = attachment.attributes;
								var imageID = attrs.id; // ID immagine
								var url = attrs.url;  // URL immagine
								$input.val( imageID );
								//$img.parent().css( "background-image", "url(" + url + ")" ); // Scribit ???
								$img.attr( 'src', url );
								$img.css( 'cursor', 'pointer' );
								$img.show();
							});
						};
						wp.media.frames.gk_frame.on( "close", selectMedia );
						wp.media.frames.gk_frame.open();
					}
				});
				$img.click(function() { $btn.click() });
            });
        }
    };
 
})( jQuery );