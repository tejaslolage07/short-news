<?php

// https://cs.symfony.com/doc/ruleSets/PhpCsFixer.html
$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PhpCsFixer' => true,
])
    ->setLineEnding("\n")
;
