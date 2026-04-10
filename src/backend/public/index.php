<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'ok' => true,
    'service' => 'backend-api',
    'message' => 'API activa. Usa /api.php?action=... para consumir endpoints.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
