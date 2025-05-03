<?php

namespace MenuManager\Utils;

class DownloadHelper {
    public static function sendHeaders( string $content_type, string $filename ): void {
        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: max-age=0' );

        // If you're serving to IE over HTTPS, remove the Cache-Control header
        header( 'Cache-Control: max-age=1' );

        // If you're serving to IE
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: cache, must-revalidate' );
        header( 'Pragma: public' );
    }
}