<?php 

require_once 'cms-init.php';
// Temporary test: Fetch ALL chapters regardless of name
$chapters = cockpit('content')->items('Chapters', [
    'sort'   => ['Order' => 1]
]);