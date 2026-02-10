<?php
require 'frontend/components/content_helper.php';

echo "=== Test c() Function ===\n\n";

$title = c('home', 'hero', 'title', 'DEFAULT_TITLE');
echo "c('home', 'hero', 'title'): $title\n";

$desc = c('home', 'hero', 'description', 'DEFAULT_DESC');
echo "c('home', 'hero', 'description'): $desc\n";

$highlight = c('home', 'hero', 'title_highlight', 'DEFAULT');
echo "c('home', 'hero', 'title_highlight'): $highlight\n";

echo "\n=== Test getSiteContent ===\n\n";
$homeContent = getSiteContent('home');
echo "Home content sections:\n";
print_r(array_keys($homeContent));

echo "\nHero section:\n";
print_r($homeContent['hero'] ?? 'Not found');
