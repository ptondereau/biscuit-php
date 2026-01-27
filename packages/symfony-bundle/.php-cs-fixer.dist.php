<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude("vendor")
    ->exclude("stubs")
    ->exclude("var")
    ->exclude("assets");

return new PhpCsFixer\Config()
    ->setRules([
        "@PER-CS" => true,
        "@Symfony" => true,
        "@PHP81Migration" => true,
        "declare_strict_types" => true,
        "strict_param" => true,
        "array_syntax" => ["syntax" => "short"],
        "ordered_imports" => ["sort_algorithm" => "alpha"],
        "no_unused_imports" => true,
        "single_quote" => true,
        "trailing_comma_in_multiline" => [
            "elements" => ["arrays", "arguments", "parameters"],
        ],
        "phpdoc_align" => ["align" => "left"],
        "phpdoc_separation" => true,
        "phpdoc_trim" => true,
        "blank_line_before_statement" => [
            "statements" => ["return", "throw", "try"],
        ],
        "class_attributes_separation" => [
            "elements" => ["method" => "one", "property" => "one"],
        ],
        "concat_space" => ["spacing" => "one"],
        "global_namespace_import" => [
            "import_classes" => true,
            "import_constants" => false,
            "import_functions" => false,
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
