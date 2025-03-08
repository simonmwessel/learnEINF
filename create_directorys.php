<?php
$directories = [
    'public/assets/css/bootstrap',
    'public/assets/js/bootstrap',
    'public/assets/css/bootstrap-icons/fonts',
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}