<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
\App\Core\Env::load(dirname(__DIR__) . '/.env');
$db = \App\Core\Database::connection();
try {
    $sql = file_get_contents(dirname(__DIR__) . '/database/migrations_erp.sql');
    if ($sql) {
        $db->exec($sql);
        echo "ERP Migration success\n";
    } else {
        echo "SQL file not found or empty\n";
    }
} catch (\Exception $e) {
    echo "ERP Migration error: " . $e->getMessage() . "\n";
}
echo "Done.";
