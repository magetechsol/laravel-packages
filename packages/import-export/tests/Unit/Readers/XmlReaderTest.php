<?php

declare(strict_types=1);

namespace Tests\Unit\Readers;

use MageTech\ImportExport\Readers\XmlFileReader;

it('reads XML file with row elements', function () {
    $path = $this->getFixturePath('products.xml');

    $reader = new XmlFileReader(rowElement: 'product');
    $reader->open($path);

    $rows = iterator_to_array($reader->rows());
    expect($rows)->not->toBeEmpty();

    $reader->close();
});
