<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
    ])
    ->withSkipPath(__DIR__.'/app/Filament')
    ->withSkipPath(__DIR__.'/vendor')
    // PHP Sets
    ->withPhpSets(php82: true)
    // Rector Sets
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::STRICT_BOOLEANS,
        SetList::NAMING,
    ])
    // PHPUnit sets
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
    ])
    ->withImportNames(false, true);
