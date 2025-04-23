<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\Renderer;

/**
 * Jpgraph is not oficially maintained in Composer, so the version there
 * could be out of date. For that reason, all unit test requiring Jpgraph
 * are skipped. So, do not measure code coverage for this class till that
 * is fixed.
 *
 * This implementation uses abandoned package
 * https://packagist.org/packages/jpgraph/jpgraph
 *
 * @codeCoverageIgnore
 */
class JpGraph extends \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraphRendererBase
{
    protected static function init() : void
    {
        static $loaded = \false;
        if ($loaded) {
            return;
        }
        // JpGraph is no longer included with distribution, but user may install it.
        // So Scrutinizer's complaint that it can't find it is reasonable, but unfixable.
        \MenuManager\Vendor\JpGraph\JpGraph::load();
        \MenuManager\Vendor\JpGraph\JpGraph::module('bar');
        \MenuManager\Vendor\JpGraph\JpGraph::module('contour');
        \MenuManager\Vendor\JpGraph\JpGraph::module('line');
        \MenuManager\Vendor\JpGraph\JpGraph::module('pie');
        \MenuManager\Vendor\JpGraph\JpGraph::module('pie3d');
        \MenuManager\Vendor\JpGraph\JpGraph::module('radar');
        \MenuManager\Vendor\JpGraph\JpGraph::module('regstat');
        \MenuManager\Vendor\JpGraph\JpGraph::module('scatter');
        \MenuManager\Vendor\JpGraph\JpGraph::module('stock');
        $loaded = \true;
    }
}
