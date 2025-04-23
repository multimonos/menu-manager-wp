<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\Renderer;

use MenuManager\Vendor\mitoteam\jpgraph\MtJpGraph;
/**
 * Jpgraph is not officially maintained by Composer at packagist.org.
 *
 * This renderer implementation uses package
 * https://packagist.org/packages/mitoteam/jpgraph
 *
 * This package is up to date for June 2023 and has PHP 8.2 support.
 */
class MtJpGraphRenderer extends \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraphRendererBase
{
    protected static function init() : void
    {
        static $loaded = \false;
        if ($loaded) {
            return;
        }
        MtJpGraph::load(['bar', 'contour', 'line', 'pie', 'pie3d', 'radar', 'regstat', 'scatter', 'stock'], \true);
        // enable Extended mode
        $loaded = \true;
    }
}
