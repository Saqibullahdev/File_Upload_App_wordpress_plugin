<?php
/**
 * Plugin Name: File Upload App
 * Plugin URI: https://example.com
 * Description: A custom post type plugin for file uploads with title, description, file link, and filtering capabilities through taxonomies and tags.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: file-upload-app
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FUA_PLUGIN_PATH', plugin_dir_path(__FILE__));

class FileUploadApp {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_filter('template_include', array($this, 'template_loader'));
        add_action('init', array($this, 'init_engagement_flow'));
        add_filter('query_vars', array($this, 'add_engagement_query_vars'));
        add_action('template_redirect', array($this, 'handle_engagement_flow'));
        
        // add_action('admin_menu', array($this, 'add_admin_menu')); // Temporarily disabled
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        $this->register_post_type();
        $this->register_taxonomies();
        $this->add_meta_boxes();
        $this->save_meta_data();
        $this->add_shortcode();
    }
    
    /**
     * Register the File Upload custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('File Uploads', 'Post Type General Name', 'file-upload-app'),
            'singular_name'         => _x('File Upload', 'Post Type Singular Name', 'file-upload-app'),
            'menu_name'             => __('File Uploads', 'file-upload-app'),
            'name_admin_bar'        => __('File Upload', 'file-upload-app'),
            'archives'              => __('File Archives', 'file-upload-app'),
            'attributes'            => __('File Attributes', 'file-upload-app'),
            'parent_item_colon'     => __('Parent File:', 'file-upload-app'),
            'all_items'             => __('All Files', 'file-upload-app'),
            'add_new_item'          => __('Add New File', 'file-upload-app'),
            'add_new'               => __('Add New', 'file-upload-app'),
            'new_item'              => __('New File', 'file-upload-app'),
            'edit_item'             => __('Edit File', 'file-upload-app'),
            'update_item'           => __('Update File', 'file-upload-app'),
            'view_item'             => __('View File', 'file-upload-app'),
            'view_items'            => __('View Files', 'file-upload-app'),
            'search_items'          => __('Search Files', 'file-upload-app'),
            'not_found'             => __('Not found', 'file-upload-app'),
            'not_found_in_trash'    => __('Not found in Trash', 'file-upload-app'),
            'featured_image'        => __('Featured Image', 'file-upload-app'),
            'set_featured_image'    => __('Set featured image', 'file-upload-app'),
            'remove_featured_image' => __('Remove featured image', 'file-upload-app'),
            'use_featured_image'    => __('Use as featured image', 'file-upload-app'),
            'insert_into_item'      => __('Insert into file', 'file-upload-app'),
            'uploaded_to_this_item' => __('Uploaded to this file', 'file-upload-app'),
            'items_list'            => __('Files list', 'file-upload-app'),
            'items_list_navigation' => __('Files list navigation', 'file-upload-app'),
            'filter_items_list'     => __('Filter files list', 'file-upload-app'),
        );
        
        $args = array(
            'label'                 => __('File Upload', 'file-upload-app'),
            'description'           => __('Custom post type for file uploads', 'file-upload-app'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'tags'),
            'taxonomies'            => array('file_category', 'file_type', 'post_tag'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-upload',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        
        register_post_type('file_upload', $args);
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // File Categories
        $category_labels = array(
            'name'                       => _x('File Categories', 'Taxonomy General Name', 'file-upload-app'),
            'singular_name'              => _x('File Category', 'Taxonomy Singular Name', 'file-upload-app'),
            'menu_name'                  => __('File Categories', 'file-upload-app'),
            'all_items'                  => __('All Categories', 'file-upload-app'),
            'parent_item'                => __('Parent Category', 'file-upload-app'),
            'parent_item_colon'          => __('Parent Category:', 'file-upload-app'),
            'new_item_name'              => __('New Category Name', 'file-upload-app'),
            'add_new_item'               => __('Add New Category', 'file-upload-app'),
            'edit_item'                  => __('Edit Category', 'file-upload-app'),
            'update_item'                => __('Update Category', 'file-upload-app'),
            'view_item'                  => __('View Category', 'file-upload-app'),
            'separate_items_with_commas' => __('Separate categories with commas', 'file-upload-app'),
            'add_or_remove_items'        => __('Add or remove categories', 'file-upload-app'),
            'choose_from_most_used'      => __('Choose from the most used', 'file-upload-app'),
            'popular_items'              => __('Popular Categories', 'file-upload-app'),
            'search_items'               => __('Search Categories', 'file-upload-app'),
            'not_found'                  => __('Not Found', 'file-upload-app'),
            'no_terms'                   => __('No categories', 'file-upload-app'),
            'items_list'                 => __('Categories list', 'file-upload-app'),
            'items_list_navigation'      => __('Categories list navigation', 'file-upload-app'),
        );
        
        $category_args = array(
            'labels'                     => $category_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        
        register_taxonomy('file_category', array('file_upload'), $category_args);
        
        // File Types
        $type_labels = array(
            'name'                       => _x('File Types', 'Taxonomy General Name', 'file-upload-app'),
            'singular_name'              => _x('File Type', 'Taxonomy Singular Name', 'file-upload-app'),
            'menu_name'                  => __('File Types', 'file-upload-app'),
            'all_items'                  => __('All Types', 'file-upload-app'),
            'new_item_name'              => __('New Type Name', 'file-upload-app'),
            'add_new_item'               => __('Add New Type', 'file-upload-app'),
            'edit_item'                  => __('Edit Type', 'file-upload-app'),
            'update_item'                => __('Update Type', 'file-upload-app'),
            'view_item'                  => __('View Type', 'file-upload-app'),
            'separate_items_with_commas' => __('Separate types with commas', 'file-upload-app'),
            'add_or_remove_items'        => __('Add or remove types', 'file-upload-app'),
            'choose_from_most_used'      => __('Choose from the most used', 'file-upload-app'),
            'popular_items'              => __('Popular Types', 'file-upload-app'),
            'search_items'               => __('Search Types', 'file-upload-app'),
            'not_found'                  => __('Not Found', 'file-upload-app'),
            'no_terms'                   => __('No types', 'file-upload-app'),
            'items_list'                 => __('Types list', 'file-upload-app'),
            'items_list_navigation'      => __('Types list navigation', 'file-upload-app'),
        );
        
        $type_args = array(
            'labels'                     => $type_labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        
        register_taxonomy('file_type', array('file_upload'), $type_args);
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_action('add_meta_boxes', array($this, 'add_file_meta_box'));
    }
    
    public function add_file_meta_box() {
        add_meta_box(
            'file_upload_meta',
            __('File Upload Details', 'file-upload-app'),
            array($this, 'file_meta_box_callback'),
            'file_upload',
            'normal',
            'high'
        );
    }
    
    /**
     * Meta box callback
     */
    public function file_meta_box_callback($post) {
        wp_nonce_field('save_file_meta_data', 'file_meta_nonce');
        
        $file_url = get_post_meta($post->ID, '_file_url', true);
        $file_size = get_post_meta($post->ID, '_file_size', true);
        $file_type = get_post_meta($post->ID, '_file_type', true);
        $download_count = get_post_meta($post->ID, '_download_count', true);
        $file_source = get_post_meta($post->ID, '_file_source', true);
        $engagement_enabled = get_post_meta($post->ID, '_engagement_enabled', true);
        $engagement_title = get_post_meta($post->ID, '_engagement_title', true);
        $engagement_description = get_post_meta($post->ID, '_engagement_description', true);
        $engagement_screenshots = get_post_meta($post->ID, '_engagement_screenshots', true);
        $intermediate_content = get_post_meta($post->ID, '_intermediate_content', true);
        
        echo '<div class="file-upload-meta-box">';
        echo '<table class="form-table">';
        
        // File URL - Main field
        echo '<tr>';
        echo '<th><label for="file_url">' . __('File Link/URL', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="url" id="file_url" name="file_url" value="' . esc_attr($file_url) . '" class="large-text" placeholder="https://drive.google.com/file/d/..." />';
        echo '<div class="url-suggestions">';
        echo '<p class="description">';
        echo __('Enter the direct link to your file from any cloud service:', 'file-upload-app') . '<br>';
        echo '• <strong>' . __('Google Drive:', 'file-upload-app') . '</strong> https://drive.google.com/file/d/...<br>';
        echo '• <strong>' . __('Dropbox:', 'file-upload-app') . '</strong> https://www.dropbox.com/s/...<br>';
        echo '• <strong>' . __('OneDrive:', 'file-upload-app') . '</strong> https://1drv.ms/...<br>';
        echo '• <strong>' . __('Box:', 'file-upload-app') . '</strong> https://app.box.com/s/...<br>';
        echo '• <strong>' . __('Other:', 'file-upload-app') . '</strong> ' . __('Any direct download link', 'file-upload-app');
        echo '</p>';
        echo '</div>';
        echo '<div class="quick-actions">';
        echo '<button type="button" id="test_link_button" class="button">' . __('Test Link', 'file-upload-app') . '</button>';
        echo '<button type="button" id="upload_file_button" class="button button-secondary">' . __('Upload to Media Library', 'file-upload-app') . '</button>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        // File Source
        echo '<tr>';
        echo '<th><label for="file_source">' . __('File Source', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<select id="file_source" name="file_source" class="regular-text">';
        echo '<option value="">' . __('Select source...', 'file-upload-app') . '</option>';
        echo '<option value="google_drive"' . selected($file_source, 'google_drive', false) . '>' . __('Google Drive', 'file-upload-app') . '</option>';
        echo '<option value="dropbox"' . selected($file_source, 'dropbox', false) . '>' . __('Dropbox', 'file-upload-app') . '</option>';
        echo '<option value="onedrive"' . selected($file_source, 'onedrive', false) . '>' . __('OneDrive', 'file-upload-app') . '</option>';
        echo '<option value="box"' . selected($file_source, 'box', false) . '>' . __('Box', 'file-upload-app') . '</option>';
        echo '<option value="aws_s3"' . selected($file_source, 'aws_s3', false) . '>' . __('AWS S3', 'file-upload-app') . '</option>';
        echo '<option value="other"' . selected($file_source, 'other', false) . '>' . __('Other Cloud Service', 'file-upload-app') . '</option>';
        echo '<option value="direct"' . selected($file_source, 'direct', false) . '>' . __('Direct Download Link', 'file-upload-app') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select the cloud service hosting your file.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // File Type
        echo '<tr>';
        echo '<th><label for="file_type_meta">' . __('File Type', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="file_type_meta" name="file_type_meta" value="' . esc_attr($file_type) . '" class="regular-text" placeholder="PDF, DOC, ZIP, etc." />';
        echo '<p class="description">' . __('Enter the file type manually (e.g., PDF, DOC, ZIP) or it will be detected from the URL.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // File Size
        echo '<tr>';
        echo '<th><label for="file_size">' . __('File Size', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="file_size" name="file_size" value="' . esc_attr($file_size) . '" class="regular-text" placeholder="e.g., 2.5 MB" />';
        echo '<p class="description">' . __('Enter the file size manually (e.g., 2.5 MB, 1.2 GB) or leave blank.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Download Count
        echo '<tr>';
        echo '<th><label for="download_count">' . __('Download Count', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="download_count" name="download_count" value="' . esc_attr($download_count ? $download_count : '0') . '" class="regular-text" min="0" />';
        echo '<p class="description">' . __('Number of times this file has been downloaded. Updates automatically when users click download.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Engagement Flow Settings Section
        echo '<tr>';
        echo '<th colspan="2"><h3 style="margin: 20px 0 10px 0; border-top: 1px solid #eee; padding-top: 20px;">' . __('Engagement Flow Settings', 'file-upload-app') . '</h3></th>';
        echo '</tr>';
        
        // Enable Engagement Flow
        echo '<tr>';
        echo '<th><label for="engagement_enabled">' . __('Enable Engagement Flow', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="checkbox" id="engagement_enabled" name="engagement_enabled" value="1" ' . checked($engagement_enabled, '1', false) . ' />';
        echo '<p class="description">' . __('Enable multi-step engagement flow for this file download.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Engagement Title
        echo '<tr class="engagement-field">';
        echo '<th><label for="engagement_title">' . __('Engagement Title', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="engagement_title" name="engagement_title" value="' . esc_attr($engagement_title) . '" class="large-text" placeholder="Amazing New App - Download Now!" />';
        echo '<p class="description">' . __('Catchy title for the engagement page (Page 1).', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Engagement Description
        echo '<tr class="engagement-field">';
        echo '<th><label for="engagement_description">' . __('Engagement Description', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<textarea id="engagement_description" name="engagement_description" rows="4" class="large-text" placeholder="Enter a compelling description that will engage users...">' . esc_textarea($engagement_description) . '</textarea>';
        echo '<p class="description">' . __('Engaging description for the first page. Keep it exciting to encourage scrolling.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Screenshots/Media
        echo '<tr class="engagement-field">';
        echo '<th><label for="engagement_screenshots">' . __('Screenshots/Media URLs', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<textarea id="engagement_screenshots" name="engagement_screenshots" rows="3" class="large-text" placeholder="https://example.com/screenshot1.jpg' . "\n" . 'https://example.com/screenshot2.jpg">' . esc_textarea($engagement_screenshots) . '</textarea>';
        echo '<p class="description">' . __('Enter image URLs (one per line) for screenshots or teaser images.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Intermediate Content
        echo '<tr class="engagement-field">';
        echo '<th><label for="intermediate_content">' . __('Intermediate Page Content', 'file-upload-app') . '</label></th>';
        echo '<td>';
        echo '<textarea id="intermediate_content" name="intermediate_content" rows="4" class="large-text" placeholder="Additional details, features, testimonials...">' . esc_textarea($intermediate_content) . '</textarea>';
        echo '<p class="description">' . __('Content for the intermediate page (Page 2). Add features, testimonials, or additional details.', 'file-upload-app') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        // Link Preview Section
        echo '<div id="link_preview" class="link-preview" style="display:none;">';
        echo '<h4>' . __('Link Preview', 'file-upload-app') . '</h4>';
        echo '<div class="preview-content"></div>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Save meta data
     */
    public function save_meta_data() {
        add_action('save_post', array($this, 'save_file_meta_data'));
    }
    
    public function save_file_meta_data($post_id) {
        if (!isset($_POST['file_meta_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['file_meta_nonce'], 'save_file_meta_data')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['post_type']) && 'file_upload' == $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        if (isset($_POST['file_url'])) {
            update_post_meta($post_id, '_file_url', sanitize_url($_POST['file_url']));
        }
        
        if (isset($_POST['file_size'])) {
            update_post_meta($post_id, '_file_size', sanitize_text_field($_POST['file_size']));
        }
        
        if (isset($_POST['file_type_meta'])) {
            update_post_meta($post_id, '_file_type', sanitize_text_field($_POST['file_type_meta']));
        }
        
        if (isset($_POST['file_source'])) {
            update_post_meta($post_id, '_file_source', sanitize_text_field($_POST['file_source']));
        }
        
        if (isset($_POST['download_count'])) {
            update_post_meta($post_id, '_download_count', intval($_POST['download_count']));
        }
        
        // Save engagement flow settings
        if (isset($_POST['engagement_enabled'])) {
            update_post_meta($post_id, '_engagement_enabled', '1');
        } else {
            update_post_meta($post_id, '_engagement_enabled', '');
        }
        
        if (isset($_POST['engagement_title'])) {
            update_post_meta($post_id, '_engagement_title', sanitize_text_field($_POST['engagement_title']));
        }
        
        if (isset($_POST['engagement_description'])) {
            update_post_meta($post_id, '_engagement_description', sanitize_textarea_field($_POST['engagement_description']));
        }
        
        if (isset($_POST['engagement_screenshots'])) {
            update_post_meta($post_id, '_engagement_screenshots', sanitize_textarea_field($_POST['engagement_screenshots']));
        }
        
        if (isset($_POST['intermediate_content'])) {
            update_post_meta($post_id, '_intermediate_content', sanitize_textarea_field($_POST['intermediate_content']));
        }
    }
    
    /**
     * Add shortcode for frontend display
     */
    public function add_shortcode() {
        add_shortcode('file_upload_list', array($this, 'file_upload_shortcode'));
        add_shortcode('file_search_with_categories', array($this, 'file_search_shortcode'));
    }
    
    /**
     * Shortcode callback
     */
    public function file_upload_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
            'category' => '',
            'type' => '',
            'tag' => '',
            'show_filter' => 'true'
        ), $atts);
        
        ob_start();
        
        if ($atts['show_filter'] === 'true') {
            $this->render_filter_form();
        }
        
        $this->render_file_list($atts);
        
        return ob_get_clean();
    }
    
    /**
     * Render filter form
     */
    private function render_filter_form() {
        $categories = get_terms(array(
            'taxonomy' => 'file_category',
            'hide_empty' => false,
        ));
        
        $types = get_terms(array(
            'taxonomy' => 'file_type',
            'hide_empty' => false,
        ));
        
        $tags = get_terms(array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ));
        
        echo '<div class="file-upload-filter">';
        echo '<form method="get" class="filter-form">';
        
        echo '<div class="filter-row">';
        
        // Category filter
        echo '<div class="filter-group">';
        echo '<label for="filter_category">' . __('Category:', 'file-upload-app') . '</label>';
        echo '<select name="filter_category" id="filter_category">';
        echo '<option value="">' . __('All Categories', 'file-upload-app') . '</option>';
        foreach ($categories as $category) {
            $selected = (isset($_GET['filter_category']) && $_GET['filter_category'] == $category->slug) ? 'selected' : '';
            echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Type filter
        echo '<div class="filter-group">';
        echo '<label for="filter_type">' . __('Type:', 'file-upload-app') . '</label>';
        echo '<select name="filter_type" id="filter_type">';
        echo '<option value="">' . __('All Types', 'file-upload-app') . '</option>';
        foreach ($types as $type) {
            $selected = (isset($_GET['filter_type']) && $_GET['filter_type'] == $type->slug) ? 'selected' : '';
            echo '<option value="' . esc_attr($type->slug) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Tag filter
        echo '<div class="filter-group">';
        echo '<label for="filter_tag">' . __('Tag:', 'file-upload-app') . '</label>';
        echo '<select name="filter_tag" id="filter_tag">';
        echo '<option value="">' . __('All Tags', 'file-upload-app') . '</option>';
        foreach ($tags as $tag) {
            $selected = (isset($_GET['filter_tag']) && $_GET['filter_tag'] == $tag->slug) ? 'selected' : '';
            echo '<option value="' . esc_attr($tag->slug) . '" ' . $selected . '>' . esc_html($tag->name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="filter-group">';
        echo '<input type="submit" value="' . __('Filter', 'file-upload-app') . '" class="button" />';
        echo '<a href="' . esc_url(remove_query_arg(array('filter_category', 'filter_type', 'filter_tag'))) . '" class="button">' . __('Clear', 'file-upload-app') . '</a>';
        echo '</div>';
        
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render file list
     */
    private function render_file_list($atts) {
        $args = array(
            'post_type' => 'file_upload',
            'posts_per_page' => intval($atts['posts_per_page']),
            'post_status' => 'publish'
        );
        
        $tax_query = array();
        
        // Handle filters from URL parameters
        if (isset($_GET['filter_category']) && !empty($_GET['filter_category'])) {
            $tax_query[] = array(
                'taxonomy' => 'file_category',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['filter_category'])
            );
        } elseif (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'file_category',
                'field' => 'slug',
                'terms' => $atts['category']
            );
        }
        
        if (isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'file_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['filter_type'])
            );
        } elseif (!empty($atts['type'])) {
            $tax_query[] = array(
                'taxonomy' => 'file_type',
                'field' => 'slug',
                'terms' => $atts['type']
            );
        }
        
        if (isset($_GET['filter_tag']) && !empty($_GET['filter_tag'])) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['filter_tag'])
            );
        } elseif (!empty($atts['tag'])) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => $atts['tag']
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            echo '<div class="file-upload-list">';
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_file_item();
            }
            echo '</div>';
            
            // Pagination
            $pagination = paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
                'format' => '?paged=%#%',
                'show_all' => false,
                'type' => 'plain',
                'end_size' => 2,
                'mid_size' => 1,
                'prev_next' => true,
                'prev_text' => __('« Previous', 'file-upload-app'),
                'next_text' => __('Next »', 'file-upload-app'),
                'add_args' => false,
                'add_fragment' => '',
            ));
            
            if ($pagination) {
                echo '<div class="file-upload-pagination">' . $pagination . '</div>';
            }
        } else {
            echo '<p>' . __('No files found.', 'file-upload-app') . '</p>';
        }
        
        wp_reset_postdata();
    }
    
    /**
     * Render individual file item
     */
    private function render_file_item() {
        $file_url = get_post_meta(get_the_ID(), '_file_url', true);
        $file_size = get_post_meta(get_the_ID(), '_file_size', true);
        $file_type = get_post_meta(get_the_ID(), '_file_type', true);
        $file_source = get_post_meta(get_the_ID(), '_file_source', true);
        $download_count = get_post_meta(get_the_ID(), '_download_count', true);
        
        $categories = get_the_terms(get_the_ID(), 'file_category');
        $types = get_the_terms(get_the_ID(), 'file_type');
        $tags = get_the_tags(get_the_ID());
        
        echo '<div class="file-item">';
        
        if (has_post_thumbnail()) {
            echo '<div class="file-thumbnail">';
            the_post_thumbnail('thumbnail');
            echo '</div>';
        }
        
        echo '<div class="file-content">';
        echo '<h3 class="file-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        
        if (has_excerpt()) {
            echo '<div class="file-excerpt">' . get_the_excerpt() . '</div>';
        }
        
        echo '<div class="file-meta">';
        if ($file_size) {
            echo '<span class="file-size">' . __('Size:', 'file-upload-app') . ' ' . esc_html($file_size) . '</span>';
        }
        if ($file_type) {
            echo '<span class="file-type">' . __('Type:', 'file-upload-app') . ' ' . esc_html($file_type) . '</span>';
        }
        if ($file_source) {
            $source_label = $this->get_source_label($file_source);
            echo '<span class="file-source">' . __('Source:', 'file-upload-app') . ' ' . esc_html($source_label) . '</span>';
        }
        if ($download_count) {
            echo '<span class="download-count">' . __('Downloads:', 'file-upload-app') . ' ' . esc_html($download_count) . '</span>';
        }
        echo '</div>';
        
        if ($categories || $types || $tags) {
            echo '<div class="file-taxonomies">';
            if ($categories) {
                echo '<div class="file-categories">';
                echo '<strong>' . __('Categories:', 'file-upload-app') . '</strong> ';
                $cat_links = array();
                foreach ($categories as $category) {
                    $cat_links[] = '<a href="' . get_term_link($category) . '">' . esc_html($category->name) . '</a>';
                }
                echo implode(', ', $cat_links);
                echo '</div>';
            }
            
            if ($types) {
                echo '<div class="file-types">';
                echo '<strong>' . __('Types:', 'file-upload-app') . '</strong> ';
                $type_links = array();
                foreach ($types as $type) {
                    $type_links[] = '<a href="' . get_term_link($type) . '">' . esc_html($type->name) . '</a>';
                }
                echo implode(', ', $type_links);
                echo '</div>';
            }
            
            if ($tags) {
                echo '<div class="file-tags">';
                echo '<strong>' . __('Tags:', 'file-upload-app') . '</strong> ';
                $tag_links = array();
                foreach ($tags as $tag) {
                    $tag_links[] = '<a href="' . get_tag_link($tag) . '">' . esc_html($tag->name) . '</a>';
                }
                echo implode(', ', $tag_links);
                echo '</div>';
            }
            echo '</div>';
        }
        
        if ($file_url) {
            echo '<div class="file-actions">';
            
            $engagement_enabled = get_post_meta(get_the_ID(), '_engagement_enabled', true);
            
            if ($engagement_enabled === '1') {
                // Show engagement flow link - use proper URL generation
                $engagement_url = $this->get_engagement_url(get_the_ID(), 'step1');
                echo '<a href="' . esc_url($engagement_url) . '" class="download-btn primary engagement-flow-btn" data-file-id="' . get_the_ID() . '">';
                echo '<span class="download-icon">🚀</span>';
                echo __('Get This App', 'file-upload-app');
                echo '</a>';
                
                // Show small direct link for power users
                echo '<div class="direct-download-link">';
                echo '<a href="' . esc_url($file_url) . '" class="direct-link" data-file-id="' . get_the_ID() . '" target="_blank">';
                echo __('Direct Download', 'file-upload-app');
                echo '</a>';
                echo '</div>';
            } else {
                // Download App button with source-specific logic
                $app_url = $this->get_app_download_url($file_source);
                if ($app_url) {
                    echo '<a href="' . esc_url($app_url) . '" class="download-app-btn" target="_blank">';
                    echo '<span class="app-icon">' . $this->get_source_icon($file_source) . '</span>';
                    echo sprintf(__('Download %s App', 'file-upload-app'), $this->get_source_label($file_source));
                    echo '</a>';
                }
                
                // Main download/access button
                $download_url = $this->process_download_url($file_url, $file_source);
                echo '<a href="' . esc_url($download_url) . '" class="download-btn primary" data-file-id="' . get_the_ID() . '" data-file-source="' . esc_attr($file_source) . '" target="_blank">';
                echo '<span class="download-icon">⬇</span>';
                
                if ($file_source === 'google_drive') {
                    echo __('Open in Drive', 'file-upload-app');
                } elseif ($file_source === 'dropbox') {
                    echo __('Open in Dropbox', 'file-upload-app');
                } elseif ($file_source === 'onedrive') {
                    echo __('Open in OneDrive', 'file-upload-app');
                } else {
                    echo __('Download/Access File', 'file-upload-app');
                }
                echo '</a>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Get source label for display
     */
    private function get_source_label($source) {
        $labels = array(
            'google_drive' => 'Google Drive',
            'dropbox' => 'Dropbox',
            'onedrive' => 'OneDrive',
            'box' => 'Box',
            'aws_s3' => 'AWS S3',
            'other' => 'Cloud Service',
            'direct' => 'Direct Link'
        );
        
        return isset($labels[$source]) ? $labels[$source] : 'Unknown';
    }
    
    /**
     * Get source icon for display
     */
    private function get_source_icon($source) {
        $icons = array(
            'google_drive' => '📂',
            'dropbox' => '📦',
            'onedrive' => '☁️',
            'box' => '📁',
            'aws_s3' => '🗄️',
            'other' => '☁️',
            'direct' => '🔗'
        );
        
        return isset($icons[$source]) ? $icons[$source] : '📄';
    }
    
    /**
     * Get app download URL based on source
     */
    private function get_app_download_url($source) {
        $app_urls = array(
            'google_drive' => 'https://play.google.com/store/apps/details?id=com.google.android.apps.docs',
            'dropbox' => 'https://play.google.com/store/apps/details?id=com.dropbox.android',
            'onedrive' => 'https://play.google.com/store/apps/details?id=com.microsoft.skydrive',
            'box' => 'https://play.google.com/store/apps/details?id=com.box.android'
        );
        
        return isset($app_urls[$source]) ? $app_urls[$source] : '';
    }
    
    /**
     * Process download URL based on source
     */
    private function process_download_url($url, $source) {
        // Convert sharing URLs to direct access URLs when possible
        if ($source === 'google_drive') {
            // Convert Google Drive sharing URL to direct view URL
            if (preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
                return 'https://drive.google.com/file/d/' . $matches[1] . '/view';
            }
        } elseif ($source === 'dropbox') {
            // Convert Dropbox sharing URL to direct download
            if (strpos($url, 'dropbox.com') !== false && strpos($url, '?dl=0') !== false) {
                return str_replace('?dl=0', '?dl=1', $url);
            }
        }
        
        return $url;
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        global $post_type;
        
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if ('file_upload' === $post_type) {
                wp_enqueue_media();
                wp_enqueue_script('file-upload-admin', FUA_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
                wp_enqueue_style('file-upload-admin', FUA_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.0');
            }
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_scripts() {
        wp_enqueue_script('file-upload-frontend', FUA_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);
        wp_localize_script('file-upload-frontend', 'file_upload_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('file_upload_nonce')
        ));
        wp_enqueue_style('file-upload-frontend', FUA_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.0');
        
        // Enqueue engagement flow assets when needed
        if (get_query_var('file_upload_engagement') || $this->is_engagement_flow_page()) {
            wp_enqueue_script('engagement-flow', FUA_PLUGIN_URL . 'assets/js/engagement-flow.js', array('jquery'), '1.0.0', true);
            wp_localize_script('engagement-flow', 'engagement_flow_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('engagement_analytics')
            ));
            wp_enqueue_style('engagement-flow', FUA_PLUGIN_URL . 'assets/css/engagement-flow.css', array(), '1.0.0');
        }
    }
    
    /**
     * Check if current page is an engagement flow page
     */
    private function is_engagement_flow_page() {
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        return preg_match('/\/app\/\d+\/(step1|step2|download)/', $current_url);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_post_type();
        $this->register_taxonomies();
        $this->init_engagement_flow();
        flush_rewrite_rules();
    }
    
    /**
     * Initialize engagement flow URL rewriting
     */
    public function init_engagement_flow() {
        add_rewrite_rule(
            '^app/([0-9]+)/(step1|step2|download)/?$',
            'index.php?file_upload_engagement=1&file_id=$matches[1]&engagement_step=$matches[2]',
            'top'
        );
        
        // Add analytics tracking endpoint
        add_rewrite_rule(
            '^app/([0-9]+)/track/?$',
            'index.php?file_upload_analytics=1&file_id=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_engagement_query_vars($vars) {
        $vars[] = 'file_upload_engagement';
        $vars[] = 'file_upload_analytics';
        $vars[] = 'file_id';
        $vars[] = 'engagement_step';
        return $vars;
    }
    
    /**
     * Handle engagement flow requests
     */
    public function handle_engagement_flow() {
        global $wp_query;
        
        // Handle analytics tracking
        if (get_query_var('file_upload_analytics')) {
            $this->handle_analytics_tracking();
            return;
        }
        
        // Handle engagement flow
        if (get_query_var('file_upload_engagement')) {
            $file_id = intval(get_query_var('file_id'));
            $step = sanitize_text_field(get_query_var('engagement_step'));
            
            // Verify file exists and engagement is enabled
            if (!$this->is_engagement_enabled($file_id)) {
                // Redirect to normal single post view
                wp_redirect(get_permalink($file_id));
                exit;
            }
            
            // Set appropriate template
            switch ($step) {
                case 'step1':
                    $this->load_engagement_template($file_id, 'step1');
                    break;
                case 'step2':
                    $this->load_engagement_template($file_id, 'step2');
                    break;
                case 'download':
                    $this->load_engagement_template($file_id, 'download');
                    break;
                default:
                    wp_redirect(home_url());
                    exit;
            }
        }
    }
    
    /**
     * Get proper engagement URL for any step
     */
    private function get_engagement_url($file_id, $step) {
        // Get the site URL with proper subdirectory handling
        $site_url = get_site_url();
        
        // Ensure trailing slash
        $site_url = rtrim($site_url, '/');
        
        // Build the engagement URL
        return $site_url . '/app/' . $file_id . '/' . $step;
    }
    
    /**
     * Check if engagement flow is enabled for a file
     */
    private function is_engagement_enabled($file_id) {
        if (!$file_id || !get_post($file_id)) {
            return false;
        }
        
        $post_type = get_post_type($file_id);
        if ($post_type !== 'file_upload') {
            return false;
        }
        
        $engagement_enabled = get_post_meta($file_id, '_engagement_enabled', true);
        return $engagement_enabled === '1';
    }
    
    /**
     * Load engagement template
     */
    private function load_engagement_template($file_id, $step) {
        global $post;
        $post = get_post($file_id);
        setup_postdata($post);
        
        $template_file = FUA_PLUGIN_PATH . 'templates/engagement-' . $step . '.php';
        
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            // Fallback to generating template content
            $this->generate_engagement_content($file_id, $step);
        }
        
        wp_reset_postdata();
        exit;
    }
    
    /**
     * Generate engagement content if template doesn't exist
     */
    private function generate_engagement_content($file_id, $step) {
        $post = get_post($file_id);
        $engagement_title = get_post_meta($file_id, '_engagement_title', true) ?: $post->post_title;
        $engagement_description = get_post_meta($file_id, '_engagement_description', true) ?: $post->post_content;
        $screenshots = get_post_meta($file_id, '_engagement_screenshots', true);
        $intermediate_content = get_post_meta($file_id, '_intermediate_content', true);
        $file_url = get_post_meta($file_id, '_file_url', true);
        
        $this->render_engagement_page($step, $file_id, compact(
            'post', 'engagement_title', 'engagement_description', 
            'screenshots', 'intermediate_content', 'file_url'
        ));
    }
    
    /**
     * Render engagement page content
     */
    private function render_engagement_page($step, $file_id, $data) {
        extract($data);
        
        get_header();
        
        echo '<div class="engagement-flow-container step-' . esc_attr($step) . '" data-file-id="' . esc_attr($file_id) . '">';
        
        switch ($step) {
            case 'step1':
                $this->render_step1_content($file_id, $data);
                break;
            case 'step2':
                $this->render_step2_content($file_id, $data);
                break;
            case 'download':
                $this->render_download_content($file_id, $data);
                break;
        }
        
        echo '</div>';
        
        get_footer();
    }
    
    /**
     * Render Step 1 content (Engagement Page)
     */
    private function render_step1_content($file_id, $data) {
        extract($data);
        ?>
        <div class="engagement-step1">
            <div class="hero-section">
                <h1 class="engagement-title"><?php echo esc_html($engagement_title); ?></h1>
                <?php if (has_post_thumbnail($file_id)): ?>
                    <div class="hero-image">
                        <?php echo get_the_post_thumbnail($file_id, 'large'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="engagement-content">
                <div class="description-section">
                    <?php echo wpautop(esc_html($engagement_description)); ?>
                </div>
                
                <?php if ($screenshots): ?>
                    <div class="screenshots-section">
                        <h3><?php _e('Screenshots', 'file-upload-app'); ?></h3>
                        <div class="screenshots-grid">
                            <?php 
                            $screenshot_urls = explode("\n", $screenshots);
                            foreach ($screenshot_urls as $url): 
                                $url = trim($url);
                                if (!empty($url)):
                            ?>
                                <img src="<?php echo esc_url($url); ?>" alt="Screenshot" class="screenshot-image" loading="lazy">
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="scroll-indicator">
                    <div class="scroll-text"><?php _e('Scroll down to continue', 'file-upload-app'); ?></div>
                    <div class="scroll-arrow">↓</div>
                </div>
            </div>
            
            <div class="continue-section" style="opacity: 0;">
                <button class="continue-btn" data-next-step="step2" disabled>
                    <?php _e('Continue to Download', 'file-upload-app'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Step 2 content (Intermediate Page)
     */
    private function render_step2_content($file_id, $data) {
        extract($data);
        ?>
        <div class="engagement-step2">
            <div class="intermediate-header">
                <h1><?php echo esc_html($engagement_title); ?></h1>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 66%;"></div>
                </div>
                <p class="step-indicator"><?php _e('Step 2 of 3', 'file-upload-app'); ?></p>
            </div>
            
            <div class="intermediate-content">
                <?php if ($intermediate_content): ?>
                    <div class="detailed-content">
                        <?php echo wpautop(esc_html($intermediate_content)); ?>
                    </div>
                <?php endif; ?>
                
                <div class="app-features">
                    <h3><?php _e('What you\'ll get:', 'file-upload-app'); ?></h3>
                    <ul class="features-list">
                        <li>✓ <?php _e('High-quality app download', 'file-upload-app'); ?></li>
                        <li>✓ <?php _e('Regular updates and support', 'file-upload-app'); ?></li>
                        <li>✓ <?php _e('Safe and secure download', 'file-upload-app'); ?></li>
                        <li>✓ <?php _e('No hidden fees or charges', 'file-upload-app'); ?></li>
                    </ul>
                </div>
                
                <div class="trust-indicators">
                    <div class="download-stats">
                        <?php 
                        $download_count = get_post_meta($file_id, '_download_count', true);
                        if ($download_count > 0):
                        ?>
                            <span class="download-count"><?php echo number_format($download_count); ?>+ <?php _e('downloads', 'file-upload-app'); ?></span>
                        <?php endif; ?>
                        <span class="security-badge">🔒 <?php _e('Secure Download', 'file-upload-app'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="continue-section">
                <button class="continue-btn primary" data-next-step="download">
                    <?php _e('Get Download Link', 'file-upload-app'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Download content (Final Page)
     */
    private function render_download_content($file_id, $data) {
        extract($data);
        ?>
        <div class="engagement-download">
            <div class="download-header">
                <h1><?php _e('Your Download is Ready!', 'file-upload-app'); ?></h1>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%;"></div>
                </div>
                <p class="step-indicator"><?php _e('Step 3 of 3', 'file-upload-app'); ?></p>
            </div>
            
            <div class="download-content">
                <div class="download-success">
                    <div class="success-icon">✓</div>
                    <h2><?php echo esc_html($engagement_title); ?></h2>
                    <p><?php _e('Thank you for your interest! Click the button below to start your download.', 'file-upload-app'); ?></p>
                </div>
                
                <div class="download-actions">
                    <?php if ($file_url): ?>
                        <a href="<?php echo esc_url($file_url); ?>" 
                           class="download-btn final-download" 
                           data-file-id="<?php echo esc_attr($file_id); ?>" 
                           target="_blank">
                            <span class="download-icon">⬇</span>
                            <?php _e('Download Now', 'file-upload-app'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="download-instructions">
                    <h4><?php _e('Download Instructions:', 'file-upload-app'); ?></h4>
                    <ol>
                        <li><?php _e('Click the download button above', 'file-upload-app'); ?></li>
                        <li><?php _e('Allow the download to complete', 'file-upload-app'); ?></li>
                        <li><?php _e('Install and enjoy your new app!', 'file-upload-app'); ?></li>
                    </ol>
                </div>
                
                <div class="social-share">
                    <p><?php _e('Love this app? Share it with friends!', 'file-upload-app'); ?></p>
                    <div class="share-buttons">
                        <a href="#" class="share-btn twitter" data-share="twitter"><?php _e('Share on Twitter', 'file-upload-app'); ?></a>
                        <a href="#" class="share-btn facebook" data-share="facebook"><?php _e('Share on Facebook', 'file-upload-app'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle analytics tracking
     */
    private function handle_analytics_tracking() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'engagement_analytics')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $file_id = intval($_POST['file_id'] ?? 0);
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $event_data = array_map('sanitize_text_field', $_POST['event_data'] ?? array());
        
        // Store analytics data
        $analytics_data = get_post_meta($file_id, '_engagement_analytics', true) ?: array();
        
        $analytics_data[] = array(
            'timestamp' => time(),
            'event_type' => $event_type,
            'event_data' => $event_data,
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        // Keep only last 1000 entries to prevent database bloat
        if (count($analytics_data) > 1000) {
            $analytics_data = array_slice($analytics_data, -1000);
        }
        
        update_post_meta($file_id, '_engagement_analytics', $analytics_data);
        
        wp_send_json_success(array('tracked' => true));
    }

    /**
     * Template loader
     */
    public function template_loader($template) {
        global $post;
        
        // Check if this is a file_upload post type
        if (is_singular('file_upload')) {
            $plugin_template = FUA_PLUGIN_PATH . 'templates/single-file_upload.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        // Check if this is a file_upload archive
        if (is_post_type_archive('file_upload')) {
            $plugin_template = FUA_PLUGIN_PATH . 'templates/archive-file_upload.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        // Check if this is a file category or type taxonomy
        if (is_tax('file_category') || is_tax('file_type')) {
            $term = get_queried_object();
            $taxonomy = $term->taxonomy;
            $plugin_template = FUA_PLUGIN_PATH . 'templates/taxonomy-' . $taxonomy . '.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
            
            // Fallback to generic taxonomy template
            $plugin_template = FUA_PLUGIN_PATH . 'templates/taxonomy.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=file_upload',
            'Engagement Flow Tools',
            'Engagement Tools',
            'manage_options',
            'engagement-tools',
            array($this, 'engagement_tools_page')
        );
    }
    
    /**
     * Engagement tools admin page
     */
    public function engagement_tools_page() {
        if (isset($_POST['flush_rules'])) {
            $this->init_engagement_flow();
            flush_rewrite_rules(true);
            echo '<div class="notice notice-success"><p>✅ Rewrite rules flushed successfully!</p></div>';
        }
        
        if (isset($_POST['test_urls'])) {
            $this->test_engagement_urls();
        }
        ?>
        <div class="wrap">
            <h1>🚀 Engagement Flow Tools</h1>
            
            <div class="card">
                <h2>Fix URL Issues</h2>
                <p>If your engagement flow URLs (like /app/123/step1) are not working, click this button:</p>
                <form method="post">
                    <input type="submit" name="flush_rules" value="Fix Engagement URLs" class="button button-primary">
                </form>
            </div>
            
            <div class="card">
                <h2>Test Your URLs</h2>
                <form method="post">
                    <input type="submit" name="test_urls" value="Test All Engagement URLs" class="button">
                </form>
            </div>
            
            <div class="card">
                <h2>Quick Setup Guide</h2>
                <ol>
                    <li><strong>Create/Edit a File Upload post</strong></li>
                    <li><strong>Enable "Engagement Flow"</strong> in the File Upload Details</li>
                    <li><strong>Fill in engagement content</strong> (title, description, screenshots)</li>
                    <li><strong>Save the post</strong></li>
                    <li><strong>Use this URL format for TikTok:</strong> <code>yoursite.com/app/[POST_ID]/step1</code></li>
                </ol>
            </div>
            
            <?php $this->show_engagement_posts(); ?>
        </div>
        <?php
    }
    
    /**
     * Show posts with engagement flow enabled
     */
    private function show_engagement_posts() {
        $args = array(
            'post_type' => 'file_upload',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_engagement_enabled',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        $posts = get_posts($args);
        
        if (!empty($posts)) {
            echo '<div class="card">';
            echo '<h2>📱 Your Engagement Flow Apps</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>App Title</th><th>Post ID</th><th>Engagement URLs</th><th>Analytics</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($posts as $post) {
                $base_url = home_url();
                $analytics = get_post_meta($post->ID, '_engagement_analytics', true) ?: array();
                $events_count = count($analytics);
                
                echo '<tr>';
                echo '<td><strong>' . esc_html($post->post_title) . '</strong></td>';
                echo '<td>' . $post->ID . '</td>';
                echo '<td>';
                echo '<a href="' . $base_url . '/app/' . $post->ID . '/step1" target="_blank" class="button button-small">Step 1</a> ';
                echo '<a href="' . $base_url . '/app/' . $post->ID . '/step2" target="_blank" class="button button-small">Step 2</a> ';
                echo '<a href="' . $base_url . '/app/' . $post->ID . '/download" target="_blank" class="button button-small">Download</a>';
                echo '</td>';
                echo '<td>' . $events_count . ' events</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        }
    }
    
    /**
     * Test engagement URLs
     */
    private function test_engagement_urls() {
        $args = array(
            'post_type' => 'file_upload',
            'posts_per_page' => 5,
            'meta_query' => array(
                array(
                    'key' => '_engagement_enabled',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        $posts = get_posts($args);
        
        if (!empty($posts)) {
            echo '<div class="notice notice-info">';
            echo '<h3>🔍 URL Test Results</h3>';
            
            foreach ($posts as $post) {
                $base_url = home_url();
                echo '<p><strong>' . esc_html($post->post_title) . ' (ID: ' . $post->ID . ')</strong></p>';
                echo '<ul>';
                echo '<li>Step 1: <a href="' . $base_url . '/app/' . $post->ID . '/step1" target="_blank">' . $base_url . '/app/' . $post->ID . '/step1</a></li>';
                echo '<li>Step 2: <a href="' . $base_url . '/app/' . $post->ID . '/step2" target="_blank">' . $base_url . '/app/' . $post->ID . '/step2</a></li>';
                echo '<li>Download: <a href="' . $base_url . '/app/' . $post->ID . '/download" target="_blank">' . $base_url . '/app/' . $post->ID . '/download</a></li>';
                echo '</ul>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning"><p>No posts found with engagement flow enabled.</p></div>';
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    

}

// Initialize the plugin
new FileUploadApp();

// AJAX handler for download tracking
add_action('wp_ajax_track_download', 'track_file_download');
add_action('wp_ajax_nopriv_track_download', 'track_file_download');

function track_file_download() {
    check_ajax_referer('file_upload_nonce', 'nonce');
    
    $file_id = intval($_POST['file_id']);
    if ($file_id) {
        $current_count = get_post_meta($file_id, '_download_count', true);
        $new_count = $current_count ? $current_count + 1 : 1;
        update_post_meta($file_id, '_download_count', $new_count);
        
        wp_send_json_success(array('new_count' => $new_count));
    }
    
    wp_send_json_error();
}

// AJAX handler for engagement analytics
add_action('wp_ajax_engagement_analytics', 'handle_engagement_analytics');
add_action('wp_ajax_nopriv_engagement_analytics', 'handle_engagement_analytics');

function handle_engagement_analytics() {
    check_ajax_referer('engagement_analytics', 'nonce');
    
    $file_id = intval($_POST['file_id'] ?? 0);
    $event_type = sanitize_text_field($_POST['event_type'] ?? '');
    $event_data = $_POST['event_data'] ?? array();
    
    if (!$file_id || !$event_type) {
        wp_send_json_error('Missing required data');
    }
    
    // Sanitize event data recursively
    $event_data = array_map_recursive('sanitize_text_field', $event_data);
    
    // Get existing analytics data
    $analytics_data = get_post_meta($file_id, '_engagement_analytics', true) ?: array();
    
    // Add new event
    $analytics_data[] = array(
        'timestamp' => time(),
        'event_type' => $event_type,
        'event_data' => $event_data,
        'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
    );
    
    // Keep only last 1000 entries to prevent database bloat
    if (count($analytics_data) > 1000) {
        $analytics_data = array_slice($analytics_data, -1000);
    }
    
    update_post_meta($file_id, '_engagement_analytics', $analytics_data);
    
    wp_send_json_success(array('tracked' => true, 'events_count' => count($analytics_data)));
}

// Helper function to recursively sanitize arrays
function array_map_recursive($callback, $array) {
    $func = function ($item) use (&$func, &$callback) {
        return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
    };
    
    return array_map($func, $array);
}

// AJAX handler for getting engagement analytics
add_action('wp_ajax_get_engagement_analytics', 'get_engagement_analytics_summary');

function get_engagement_analytics_summary() {
    check_ajax_referer('_wpnonce', 'nonce');
    
    $file_id = intval($_POST['file_id'] ?? 0);
    
    if (!$file_id || !current_user_can('edit_post', $file_id)) {
        wp_send_json_error('Permission denied');
    }
    
    $analytics_data = get_post_meta($file_id, '_engagement_analytics', true) ?: array();
    
    if (empty($analytics_data)) {
        wp_send_json_success(array('total_events' => 0));
    }
    
    // Calculate summary statistics
    $thirty_days_ago = time() - (30 * 24 * 60 * 60);
    $recent_events = array_filter($analytics_data, function($event) use ($thirty_days_ago) {
        return $event['timestamp'] >= $thirty_days_ago;
    });
    
    $total_events = count($recent_events);
    $unique_visitors = count(array_unique(array_column($recent_events, 'user_ip')));
    
    // Calculate average time spent
    $time_events = array_filter($recent_events, function($event) {
        return $event['event_type'] === 'batch_update' && 
               isset($event['event_data']['time_spent']);
    });
    
    $avg_time_spent = 0;
    if (!empty($time_events)) {
        $total_time = array_sum(array_column(array_column($time_events, 'event_data'), 'time_spent'));
        $avg_time_spent = round($total_time / count($time_events) / 1000); // Convert to seconds
    }
    
    // Calculate average scroll depth
    $scroll_events = array_filter($recent_events, function($event) {
        return $event['event_type'] === 'batch_update' && 
               isset($event['event_data']['scroll_depth']);
    });
    
    $avg_scroll_depth = 0;
    if (!empty($scroll_events)) {
        $total_scroll = array_sum(array_column(array_column($scroll_events, 'event_data'), 'scroll_depth'));
        $avg_scroll_depth = round($total_scroll / count($scroll_events));
    }
    
    // Calculate conversion rate (downloads vs visits)
    $download_events = array_filter($recent_events, function($event) {
        return $event['event_type'] === 'final_download_clicked';
    });
    
    $conversion_rate = 0;
    if ($unique_visitors > 0) {
        $conversion_rate = round((count($download_events) / $unique_visitors) * 100, 1);
    }
    
    wp_send_json_success(array(
        'total_events' => $total_events,
        'unique_visitors' => $unique_visitors,
        'avg_time_spent' => $avg_time_spent,
        'avg_scroll_depth' => $avg_scroll_depth,
        'conversion_rate' => $conversion_rate,
        'total_downloads' => count($download_events)
    ));
}
