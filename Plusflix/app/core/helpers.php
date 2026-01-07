<?php
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function url(string $c, string $a, array $params = []): string {
    $q = array_merge(['c' => $c, 'a' => $a], $params);
    return 'index.php?' . http_build_query($q);
}

function splitList(string $s): array {
    $parts = array_map('trim', explode(',', $s));
    $parts = array_filter($parts, fn($x) => $x !== '');
    return array_values(array_unique($parts));
}
