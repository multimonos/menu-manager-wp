jQuery( function ( $ ) {

    const onClick = function ( e ) {
        e.preventDefault();

        $link = $( this )
        const postId = $link.data( 'post-id' );
        const nonce = $link.data( 'nonce' )

        const payload = {
            action: jobRunActionData.actionId,
            // nonce: config.nonce,
            _wpnonce: nonce,
            post_id: postId,
        }

        const onSuccess = function ( res ) {
            console.log( 'success', { res } )
            window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
            if ( res.success ) {
                window.dispatchEvent( new CustomEvent( 'mm-success', { detail: { message: res.data.message } } ) )
            } else {
                window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: res.data.message } } ) )
            }
        }

        const onFailure = function ( e ) {
            console.log( 'err', { e } )
            window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
            window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: 'Failed yo' } } ) )
        }

        // if ( ! confirm( 'Run jobi ' + postId + ' ... action cannot be undone.' ) ) {
        //     return;
        // }

        window.dispatchEvent( new Event( 'mm-spinner-show' ) )
        $.post( jobRunActionData.ajaxUrl, payload, onSuccess ).fail( onFailure )
    }


    $( '.job-run-action__link' ).on( 'click', onClick );
} );