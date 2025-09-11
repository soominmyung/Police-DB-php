<?php
// config.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$DEMO_MODE = getenv('DEMO_MODE') === '1';

function demo_block_or_continue(string $actionLabel, bool $asJson = false): void {
    global $DEMO_MODE;
    if (!$DEMO_MODE) return;

    if ($asJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'    => false,
            'error' => 'This public demo is read only. '
                     . $actionLabel . ' is temporarily disabled. '
                     . 'Please run the project locally to try the full create update delete features.'
        ]);
    } else {
        http_response_code(200); // 
        header('Content-Type: text/html; charset=utf-8');
        echo '<div style="max-width:720px;margin:48px auto;font-family:system-ui,Arial">'
           . '<h2>This action is disabled in the public demo</h2>'
           . '<p>This instance is read only for demonstration. '
           . htmlspecialchars($actionLabel, ENT_QUOTES, "UTF-8")
           . ' is temporarily disabled.</p>'
           . '<p>Please run the app locally to use full create update delete features.</p>'
           . '<p><a href="/index.php">Return to home</a></p>'
           . '</div>';
    }
    exit;
}
