<?php

/**
 * Adds #[\ReturnTypeWillChange] to softcreatr/jsonpath JSONPath.php to fix PHP 8.1+ deprecations.
 * Idempotent: safe to run multiple times.
 */
$file = __DIR__ . '/../vendor/softcreatr/jsonpath/src/JSONPath.php';
if (!is_readable($file)) {
    return;
}

$contents = file_get_contents($file);
$attribute = "    #[\ReturnTypeWillChange]\n    public function ";

$replacements = [
    '    public function offsetGet($offset)' => $attribute . 'offsetGet($offset)',
    '    public function jsonSerialize()' => $attribute . 'jsonSerialize()',
    '    public function current()' => $attribute . 'current()',
    '    public function key()' => $attribute . 'key()',
];

foreach ($replacements as $search => $replacement) {
    if (strpos($contents, $replacement) !== false) {
        continue;
    }
    if (strpos($contents, $search) !== false) {
        $contents = str_replace($search, $replacement, $contents);
    }
}

file_put_contents($file, $contents);
