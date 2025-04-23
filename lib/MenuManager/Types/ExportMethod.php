<?php

namespace MenuManager\Types;

enum ExportMethod: string {
    case File = 'file';
    case Download = 'download';
}
