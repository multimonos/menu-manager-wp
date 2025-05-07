<?php

namespace MenuManager\Model\Types;

enum ImpexAction: string {
    case Create = 'create';
    case Update = 'update';
//    case Insert = 'insert';
    case Delete = 'delete';
    case Price = 'price';
}