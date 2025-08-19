<?php
/**
 * Manual URL fix - Run this once if engagement URLs don't work
 * Access this file directly in your browser: http://localhost/wordpress/wp-content/plugins/file-upload-app/manual-fix.php
 */

// Load WordPress
require_once('../../../../wp-config.php');

echo "<h1>🔧 Engagement Flow URL Fix</h1>";

// Check if we can add rewrite rules
if (function_exists('add_rewrite_rule')) {
    
    // Add rewrite rules
    add_rewrite_rule(
        '^app/([0-9]+)/(step1|step2|download)/?$',
        'index.php?file_upload_engagement=1&file_id=$matches[1]&engagement_step=$matches[2]',
        'top'
    );
    
    add_rewrite_rule(
        '^app/([0-9]+)/track/?$',
        'index.php?file_upload_analytics=1&file_id=$matches[1]',
        'top'
    );
    
    // Flush rewrite rules
    flush_rewrite_rules(true);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>Success!</strong> Rewrite rules have been added and flushed.";
    echo "</div>";
    
    // Show current rules
    $rules = get_option('rewrite_rules');
    echo "<h2>🔍 Current Rewrite Rules (showing 'app' related):</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    
    $found_rules = false;
    foreach ($rules as $pattern => $replacement) {
        if (strpos($pattern, 'app') !== false) {
            echo "<strong>Pattern:</strong> " . esc_html($pattern) . "<br>";
            echo "<strong>Replacement:</strong> " . esc_html($replacement) . "<br><br>";
            $found_rules = true;
        }
    }
    
    if (!$found_rules) {
        echo "<em>No 'app' related rewrite rules found. This might indicate an issue.</em>";
    }
    echo "</div>";
    
    // Show test URLs
    echo "<h2>🔗 Test Your URLs:</h2>";
    echo "<p>Replace <code>POST_ID</code> with your actual post ID:</p>";
    
    $base_url = get_site_url(); // Use get_site_url() instead of home_url() for proper subdirectory handling
    echo "<ul>";
    echo "<li><a href='{$base_url}/app/POST_ID/step1' target='_blank'>{$base_url}/app/POST_ID/step1</a></li>";
    echo "<li><a href='{$base_url}/app/POST_ID/step2' target='_blank'>{$base_url}/app/POST_ID/step2</a></li>";
    echo "<li><a href='{$base_url}/app/POST_ID/download' target='_blank'>{$base_url}/app/POST_ID/download</a></li>";
    echo "</ul>";
    
    // Find posts with engagement enabled
    $posts = get_posts(array(
        'post_type' => 'file_upload',
        'meta_key' => '_engagement_enabled',
        'meta_value' => '1',
        'numberposts' => 5
    ));
    
    if (!empty($posts)) {
        echo "<h2>📱 Your Engagement Flow Posts:</h2>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
        
        foreach ($posts as $post) {
            echo "<h3>" . esc_html($post->post_title) . " (ID: {$post->ID})</h3>";
            echo "<ul>";
            echo "<li><a href='{$base_url}/app/{$post->ID}/step1' target='_blank'>Step 1: {$base_url}/app/{$post->ID}/step1</a></li>";
            echo "<li><a href='{$base_url}/app/{$post->ID}/step2' target='_blank'>Step 2: {$base_url}/app/{$post->ID}/step2</a></li>";
            echo "<li><a href='{$base_url}/app/{$post->ID}/download' target='_blank'>Download: {$base_url}/app/{$post->ID}/download</a></li>";
            echo "</ul>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "⚠️ <strong>No posts found with engagement flow enabled.</strong><br>";
        echo "Make sure to:<br>";
        echo "1. Edit a File Upload post<br>";
        echo "2. Check 'Enable Engagement Flow'<br>";
        echo "3. Save the post<br>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ <strong>Error:</strong> WordPress functions not available. Make sure wp-config.php is accessible.";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>After running this fix, try your engagement URLs again. If they work, you can delete this file.</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1 { color: #333; }
h2 { color: #666; margin-top: 30px; }
code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; }
a { color: #0073aa; }
</style>
