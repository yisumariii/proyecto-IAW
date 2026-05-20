<?php

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


function old($key, $default = '') {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}
