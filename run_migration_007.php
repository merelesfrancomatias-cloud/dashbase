<?php
// Load environment first
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read migration file
    $sql = file_get_contents(__DIR__ . '/config/migrations/007_canchas_reservas.sql');
    
    // Split statements by semicolon and execute each one
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            // Remove comments
            $lines = explode("\n", $statement);
            $finalStatement = [];
            foreach ($lines as $line) {
                if (!preg_match('/^\s*--/', $line)) {
                    $finalStatement[] = $line;
                }
            }
            $stmt = implode("\n", $finalStatement);
            if (trim($stmt)) {
                $db->exec($stmt);
                echo "✓ Executed: " . substr(trim($stmt), 0, 50) . "...\n";
            }
        }
    }
    
    echo "\n✓ Migration 007 completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
