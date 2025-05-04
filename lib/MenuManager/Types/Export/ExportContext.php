<?php

namespace MenuManager\Types\Export;

enum ExportContext: string {
    case Cli = 'cli';
    case Download = 'download';
}
