<?php
require_once 'db_config.php';

echo "<h2>Database Connection Test</h2>";

// Test Primary Connection
echo "<h3>Primary Connection ($conn):</h3>";
if (isset($conn)) {
    try {
        $stmt = $conn->query("SELECT VERSION()");
        $row = $stmt->fetch();
        echo "✅ Connected! MySQL Version: " . $row[0] . "<br>";
    } catch (Exception $e) {
        echo "❌ Failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Primary connection variable \$conn is not set.<br>";
}

// Test Secondary Connection
echo "<h3>Secondary (Docker) Connection ($conn_docker):</h3>";
if (isset($conn_docker)) {
    try {
        $stmt = $conn_docker->query("SELECT VERSION()");
        $row = $stmt->fetch();
        echo "✅ Connected! Docker MySQL Version: " . $row[0] . "<br>";
    } catch (Exception $e) {
        echo "❌ Failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Secondary connection variable \$conn_docker is not set.<br>";
}
?>
