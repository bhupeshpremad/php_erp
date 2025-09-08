<?php
// Quick deployment check script
echo "<h2>Deployment Status Check</h2>";

// Check current git branch
$branch = trim(shell_exec('git branch --show-current 2>/dev/null') ?? 'unknown');
echo "<strong>Current Branch:</strong> " . htmlspecialchars($branch) . "<br>";

// Check BASE_URL
echo "<strong>BASE_URL:</strong> " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "<br>";

// Check if we're on live or local
$host = $_SERVER['HTTP_HOST'] ?? 'unknown';
echo "<strong>Host:</strong> " . htmlspecialchars($host) . "<br>";

if (strpos($host, 'localhost') !== false) {
    echo "<span style='color: green;'>✅ Running on LOCAL</span><br>";
} else {
    echo "<span style='color: blue;'>🌐 Running on LIVE SERVER</span><br>";
}

// Check last commit
$lastCommit = trim(shell_exec('git log -1 --pretty=format:"%h - %s (%cr)" 2>/dev/null') ?? 'unknown');
echo "<strong>Last Commit:</strong> " . htmlspecialchars($lastCommit) . "<br>";

echo "<br><strong>Next Steps for Live:</strong><br>";
echo "1. git fetch origin<br>";
echo "2. git checkout development<br>";
echo "3. git pull origin development<br>";
?>