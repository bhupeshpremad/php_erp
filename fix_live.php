<?php
// Live server fix script
echo "<h2>Live Server Fix Commands</h2>";
echo "<pre>";
echo "# Run these commands on live server:\n\n";
echo "cd /path/to/your/website/directory\n";
echo "git fetch origin\n";
echo "git checkout development\n";
echo "git pull origin development\n\n";
echo "# Or manually replace sidebar.php with:\n";
echo "# BASE_URL instead of hardcoded URLs\n\n";
echo "# Clear any cache if using caching\n";
echo "</pre>";

// Show what the URLs should be
echo "<h3>URLs should be:</h3>";
echo "<code>&lt;a href=\"&lt;?php echo BASE_URL; ?&gt;modules/purchase/add.php\"&gt;</code><br>";
echo "<code>&lt;a href=\"&lt;?php echo BASE_URL; ?&gt;modules/purchase/index.php\"&gt;</code><br>";
?>