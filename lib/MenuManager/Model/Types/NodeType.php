<?php

namespace MenuManager\Model\Types;

enum NodeType: string {
    case Root = 'root';
    case Page = 'page';
    case Category0 = 'category-0';
    case Category1 = 'category-1';
    case Category2 = 'category-2';
    case Item = 'item';
    case Wine = 'wine';
    case OptionGroup = 'option-group';
    case Option = 'option';
    case AddonGroup = 'addon-group';
    case Addon = 'addon';
}
