<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Imagick extension: " . (extension_loaded('imagick') ? 'Yes' : 'No') . "\n";
echo "GD extension: " . (extension_loaded('gd') ? 'Yes' : 'No') . "\n";

$tools = ['pdftocairo', 'convert', 'magick', 'pdfimages'];
foreach ($tools as $tool) {
    $output = [];
    $status = 0;
    exec("where $tool 2>&1", $output, $status);
    echo "$tool: " . ($status === 0 ? reset($output) : 'Not found') . "\n";
}
