<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Atico\Bundle\SpreadsheetTranslatorBundle\SpreadsheetTranslatorBundle;

return [
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    SpreadsheetTranslatorBundle::class => ['all' => true],
];
