<?php

$files = glob('app/Filament/**/*.php', GLOB_BRACE);

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix malformed type declarations
    $content = preg_replace('/protected static \\\\BackedEnum\|string\|null/', 'protected static ?string', $content);
    $content = preg_replace('/protected static \\\\UnitEnum\|string\|null/', 'protected static ?string', $content);
    $content = preg_replace('/protected static BackedEnum\|string\|null/', 'protected static ?string', $content);
    $content = preg_replace('/protected static UnitEnum\|string\|null/', 'protected static ?string', $content);
    
    // Remove duplicate imports
    $content = preg_replace('/^use BackedEnum;$/m', '', $content);
    $content = preg_replace('/^use UnitEnum;$/m', '', $content);
    
    // Remove empty lines
    $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
    
    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "Done!\n";
