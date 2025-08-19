<?php
/**
 * Archive File Upload Template
 * 
 * Template for displaying file upload archives
 */

get_header(); ?>

<div class="file-upload-archive">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php
            if (is_post_type_archive('file_upload')) {
                _e('File Library', 'file-upload-app');
            } elseif (is_tax('file_category')) {
                printf(__('Category: %s', 'file-upload-app'), single_term_title('', false));
            } elseif (is_tax('file_type')) {
                printf(__('Type: %s', 'file-upload-app'), single_term_title('', false));
            } elseif (is_tag()) {
                printf(__('Tag: %s', 'file-upload-app'), single_tag_title('', false));
            }
            ?>
        </h1>
        
        <?php if (is_tax() && term_description()) : ?>
            <div class="archive-description">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
        
        <div class="archive-stats">
            <?php
            global $wp_query;
            $total_files = $wp_query->found_posts;
            printf(
                _n('Showing %s file', 'Showing %s files', $total_files, 'file-upload-app'),
                '<strong>' . number_format($total_files) . '</strong>'
            );
            ?>
        </div>
    </header>
    
    <!-- Enhanced Filter Section -->
    <div class="archive-filters">
        <form method="get" class="filter-form enhanced-filters">
            <div class="filter-sections">
                
                <!-- Search Section -->
                <div class="filter-section search-section">
                    <h3><?php _e('Search Files', 'file-upload-app'); ?></h3>
                    <div class="search-controls">
                        <input type="text" 
                               name="search" 
                               id="file-search" 
                               placeholder="<?php _e('Search titles and descriptions...', 'file-upload-app'); ?>" 
                               value="<?php echo esc_attr(get_query_var('search')); ?>">
                        <button type="submit" class="search-btn"><?php _e('Search', 'file-upload-app'); ?></button>
                    </div>
                </div>
                
                <!-- Taxonomy Filters -->
                <div class="filter-section taxonomy-section">
                    <h3><?php _e('Filter by Category', 'file-upload-app'); ?></h3>
                    <div class="filter-grid">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'file_category',
                            'hide_empty' => true,
                        ));
                        
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                                $is_active = is_tax('file_category', $category->slug);
                                ?>
                                <div class="filter-item <?php echo $is_active ? 'active' : ''; ?>">
                                    <a href="<?php echo get_term_link($category); ?>" class="filter-link">
                                        <span class="filter-name"><?php echo esc_html($category->name); ?></span>
                                        <span class="filter-count"><?php echo $category->count; ?></span>
                                    </a>
                                </div>
                            <?php endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                
                <div class="filter-section taxonomy-section">
                    <h3><?php _e('Filter by Type', 'file-upload-app'); ?></h3>
                    <div class="filter-grid">
                        <?php
                        $types = get_terms(array(
                            'taxonomy' => 'file_type',
                            'hide_empty' => true,
                        ));
                        
                        if ($types && !is_wp_error($types)) :
                            foreach ($types as $type) :
                                $is_active = is_tax('file_type', $type->slug);
                                ?>
                                <div class="filter-item <?php echo $is_active ? 'active' : ''; ?>">
                                    <a href="<?php echo get_term_link($type); ?>" class="filter-link">
                                        <span class="filter-name"><?php echo esc_html($type->name); ?></span>
                                        <span class="filter-count"><?php echo $type->count; ?></span>
                                    </a>
                                </div>
                            <?php endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                
                <!-- Sort Options -->
                <div class="filter-section sort-section">
                    <h3><?php _e('Sort By', 'file-upload-app'); ?></h3>
                    <select name="orderby" id="sort-by">
                        <option value="date" <?php selected(get_query_var('orderby'), 'date'); ?>>
                            <?php _e('Date Added', 'file-upload-app'); ?>
                        </option>
                        <option value="title" <?php selected(get_query_var('orderby'), 'title'); ?>>
                            <?php _e('Title', 'file-upload-app'); ?>
                        </option>
                        <option value="download_count" <?php selected(get_query_var('orderby'), 'download_count'); ?>>
                            <?php _e('Most Downloaded', 'file-upload-app'); ?>
                        </option>
                        <option value="modified" <?php selected(get_query_var('orderby'), 'modified'); ?>>
                            <?php _e('Last Modified', 'file-upload-app'); ?>
                        </option>
                    </select>
                    
                    <select name="order" id="sort-order">
                        <option value="DESC" <?php selected(get_query_var('order'), 'DESC'); ?>>
                            <?php _e('Descending', 'file-upload-app'); ?>
                        </option>
                        <option value="ASC" <?php selected(get_query_var('order'), 'ASC'); ?>>
                            <?php _e('Ascending', 'file-upload-app'); ?>
                        </option>
                    </select>
                </div>
                
                <!-- View Options -->
                <div class="filter-section view-section">
                    <h3><?php _e('View', 'file-upload-app'); ?></h3>
                    <div class="view-toggles">
                        <button type="button" class="view-toggle grid-view active" data-view="grid">
                            <span class="dashicons dashicons-grid-view"></span>
                            <?php _e('Grid', 'file-upload-app'); ?>
                        </button>
                        <button type="button" class="view-toggle list-view" data-view="list">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php _e('List', 'file-upload-app'); ?>
                        </button>
                    </div>
                    
                    <div class="per-page-control">
                        <label for="posts-per-page"><?php _e('Files per page:', 'file-upload-app'); ?></label>
                        <select name="posts_per_page" id="posts-per-page">
                            <option value="12" <?php selected(get_query_var('posts_per_page'), '12'); ?>>12</option>
                            <option value="24" <?php selected(get_query_var('posts_per_page'), '24'); ?>>24</option>
                            <option value="48" <?php selected(get_query_var('posts_per_page'), '48'); ?>>48</option>
                            <option value="-1" <?php selected(get_query_var('posts_per_page'), '-1'); ?>><?php _e('All', 'file-upload-app'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="apply-filters-btn">
                    <?php _e('Apply Filters', 'file-upload-app'); ?>
                </button>
                <a href="<?php echo get_post_type_archive_link('file_upload'); ?>" class="clear-filters-btn">
                    <?php _e('Clear All', 'file-upload-app'); ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Results Section -->
    <div class="archive-results">
        <?php if (have_posts()) : ?>
            
            <div class="results-header">
                <div class="results-info">
                    <?php
                    global $wp_query;
                    $current_page = max(1, get_query_var('paged'));
                    $posts_per_page = $wp_query->query_vars['posts_per_page'];
                    $total_posts = $wp_query->found_posts;
                    $start = (($current_page - 1) * $posts_per_page) + 1;
                    $end = min($current_page * $posts_per_page, $total_posts);
                    
                    printf(
                        __('Showing %d-%d of %d files', 'file-upload-app'),
                        $start,
                        $end,
                        $total_posts
                    );
                    ?>
                </div>
            </div>
            
            <div class="file-grid grid-view" id="file-results">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="file-item" data-id="<?php the_ID(); ?>">
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="file-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                                <div class="thumbnail-overlay">
                                    <a href="<?php the_permalink(); ?>" class="view-details">
                                        <?php _e('View Details', 'file-upload-app'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="file-thumbnail no-image">
                                <div class="file-icon">
                                    <?php
                                    $file_type = get_post_meta(get_the_ID(), '_file_type', true);
                                    $icon = '📄'; // Default icon
                                    
                                    if ($file_type) {
                                        switch (strtolower($file_type)) {
                                            case 'pdf': $icon = '📕'; break;
                                            case 'doc':
                                            case 'docx': $icon = '📘'; break;
                                            case 'xls':
                                            case 'xlsx': $icon = '📊'; break;
                                            case 'ppt':
                                            case 'pptx': $icon = '📙'; break;
                                            case 'zip':
                                            case 'rar': $icon = '🗜️'; break;
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif': $icon = '🖼️'; break;
                                            case 'mp3':
                                            case 'wav': $icon = '🎵'; break;
                                            case 'mp4':
                                            case 'avi': $icon = '🎬'; break;
                                        }
                                    }
                                    echo $icon;
                                    ?>
                                </div>
                                <div class="thumbnail-overlay">
                                    <a href="<?php the_permalink(); ?>" class="view-details">
                                        <?php _e('View Details', 'file-upload-app'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-content">
                            <h3 class="file-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <?php if (has_excerpt()) : ?>
                                <div class="file-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="file-meta">
                                <?php
                                $file_size = get_post_meta(get_the_ID(), '_file_size', true);
                                $file_type = get_post_meta(get_the_ID(), '_file_type', true);
                                $download_count = get_post_meta(get_the_ID(), '_download_count', true);
                                ?>
                                
                                <div class="meta-grid">
                                    <?php if ($file_size) : ?>
                                        <span class="meta-item size">
                                            <strong><?php _e('Size:', 'file-upload-app'); ?></strong> 
                                            <?php echo esc_html($file_size); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($file_type) : ?>
                                        <span class="meta-item type">
                                            <strong><?php _e('Type:', 'file-upload-app'); ?></strong> 
                                            <?php echo esc_html($file_type); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="meta-item date">
                                        <strong><?php _e('Added:', 'file-upload-app'); ?></strong> 
                                        <?php echo get_the_date(); ?>
                                    </span>
                                    
                                    <?php if ($download_count) : ?>
                                        <span class="meta-item downloads">
                                            <strong><?php _e('Downloads:', 'file-upload-app'); ?></strong> 
                                            <span class="download-count"><?php echo intval($download_count); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="file-taxonomies">
                                <?php
                                $categories = get_the_terms(get_the_ID(), 'file_category');
                                $types = get_the_terms(get_the_ID(), 'file_type');
                                $tags = get_the_tags(get_the_ID());
                                ?>
                                
                                <?php if ($categories && !is_wp_error($categories)) : ?>
                                    <div class="taxonomy-group categories">
                                        <?php foreach (array_slice($categories, 0, 3) as $category) : ?>
                                            <a href="<?php echo get_term_link($category); ?>" class="term-tag category">
                                                <?php echo esc_html($category->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <?php if (count($categories) > 3) : ?>
                                            <span class="more-terms">+<?php echo count($categories) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($tags && !is_wp_error($tags)) : ?>
                                    <div class="taxonomy-group tags">
                                        <?php foreach (array_slice($tags, 0, 3) as $tag) : ?>
                                            <a href="<?php echo get_tag_link($tag); ?>" class="term-tag tag">
                                                <?php echo esc_html($tag->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <?php if (count($tags) > 3) : ?>
                                            <span class="more-terms">+<?php echo count($tags) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="file-actions">
                                <?php
                                $file_url = get_post_meta(get_the_ID(), '_file_url', true);
                                if ($file_url) : ?>
                                    <a href="<?php echo esc_url($file_url); ?>" 
                                       class="download-btn" 
                                       data-file-id="<?php the_ID(); ?>" 
                                       target="_blank">
                                        <span class="download-icon">⬇</span>
                                        <?php _e('Download', 'file-upload-app'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="details-btn">
                                    <?php _e('View Details', 'file-upload-app'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="archive-pagination">
                <?php
                $pagination = paginate_links(array(
                    'total' => $wp_query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'type' => 'array',
                    'end_size' => 2,
                    'mid_size' => 1,
                    'prev_next' => true,
                    'prev_text' => __('‹ Previous', 'file-upload-app'),
                    'next_text' => __('Next ›', 'file-upload-app'),
                    'add_args' => false,
                    'add_fragment' => '',
                ));
                
                if ($pagination) {
                    echo '<nav class="pagination-nav">';
                    echo '<ul class="pagination">';
                    foreach ($pagination as $link) {
                        echo '<li>' . $link . '</li>';
                    }
                    echo '</ul>';
                    echo '</nav>';
                }
                ?>
            </div>
            
        <?php else : ?>
            
            <div class="no-files-found">
                <div class="no-files-icon">📁</div>
                <h2><?php _e('No files found', 'file-upload-app'); ?></h2>
                <p><?php _e('Sorry, no files match your current criteria. Try adjusting your filters or search terms.', 'file-upload-app'); ?></p>
                <a href="<?php echo get_post_type_archive_link('file_upload'); ?>" class="reset-filters-btn">
                    <?php _e('View All Files', 'file-upload-app'); ?>
                </a>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<?php
// Include the stylesheet
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . '../assets/css/frontend.css'; ?>">

<style>
/* Archive-specific styles */
.file-upload-archive {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.archive-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.archive-title {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.archive-description {
    font-size: 16px;
    color: #666;
    margin-bottom: 15px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.archive-stats {
    font-size: 14px;
    color: #888;
}

.archive-filters {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.filter-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 20px;
}

.filter-section h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 5px;
}

.search-controls {
    display: flex;
    gap: 10px;
}

.search-controls input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.search-btn {
    background: #007cba;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.search-btn:hover {
    background: #005a87;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
}

.filter-item {
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.filter-item.active {
    background: #007cba;
}

.filter-item.active .filter-link {
    color: white;
}

.filter-link {
    display: block;
    padding: 8px 12px;
    color: #333;
    text-decoration: none;
    background: #e9ecef;
    transition: all 0.2s ease;
}

.filter-link:hover {
    background: #007cba;
    color: white;
}

.filter-name {
    display: block;
    font-size: 12px;
    font-weight: 600;
}

.filter-count {
    display: block;
    font-size: 10px;
    opacity: 0.8;
}

.sort-section select,
.view-section select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
}

.view-toggles {
    display: flex;
    gap: 5px;
    margin-bottom: 15px;
}

.view-toggle {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.view-toggle.active {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.per-page-control label {
    display: block;
    font-size: 12px;
    margin-bottom: 5px;
}

.filter-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.apply-filters-btn,
.clear-filters-btn {
    padding: 12px 24px;
    margin: 0 5px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
}

.apply-filters-btn {
    background: #28a745;
    color: white;
    border: none;
    cursor: pointer;
}

.apply-filters-btn:hover {
    background: #218838;
}

.clear-filters-btn {
    background: #6c757d;
    color: white;
}

.clear-filters-btn:hover {
    background: #545b62;
    color: white;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.results-info {
    font-size: 14px;
    color: #666;
}

.file-grid.grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.file-grid.list-view {
    display: block;
}

.file-grid.list-view .file-item {
    display: flex;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.file-grid.list-view .file-thumbnail {
    width: 150px;
    flex-shrink: 0;
}

.file-grid.list-view .file-content {
    flex: 1;
}

.file-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.file-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.file-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-thumbnail.no-image {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.file-icon {
    font-size: 48px;
}

.thumbnail-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.file-thumbnail:hover .thumbnail-overlay {
    opacity: 1;
}

.view-details {
    color: white;
    text-decoration: none;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
    font-size: 14px;
}

.file-content {
    padding: 20px;
}

.file-title {
    font-size: 18px;
    margin-bottom: 10px;
    line-height: 1.4;
}

.file-title a {
    color: #333;
    text-decoration: none;
}

.file-title a:hover {
    color: #007cba;
}

.file-excerpt {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 15px;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 8px;
    margin-bottom: 15px;
}

.meta-item {
    font-size: 11px;
    padding: 4px 8px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    text-align: center;
}

.taxonomy-group {
    margin-bottom: 8px;
}

.term-tag {
    display: inline-block;
    font-size: 10px;
    padding: 2px 6px;
    margin-right: 4px;
    margin-bottom: 2px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
}

.term-tag.category {
    background: #28a745;
}

.term-tag.tag {
    background: #ffc107;
    color: #333;
}

.more-terms {
    font-size: 10px;
    color: #666;
    font-style: italic;
}

.file-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
}

.download-btn,
.details-btn {
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}

.download-btn {
    background: #28a745;
    color: white;
}

.download-btn:hover {
    background: #218838;
    color: white;
}

.details-btn {
    background: #007cba;
    color: white;
}

.details-btn:hover {
    background: #005a87;
    color: white;
}

.pagination-nav {
    text-align: center;
    margin-top: 40px;
}

.pagination {
    display: inline-flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 5px;
}

.pagination li a,
.pagination li span {
    display: block;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    color: #007cba;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.pagination li a:hover,
.pagination li .current {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.no-files-found {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-files-icon {
    font-size: 72px;
    margin-bottom: 20px;
}

.no-files-found h2 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #333;
}

.reset-filters-btn {
    background: #007cba;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
}

.reset-filters-btn:hover {
    background: #005a87;
    color: white;
}

@media (max-width: 768px) {
    .file-upload-archive {
        padding: 15px;
    }
    
    .archive-filters {
        padding: 20px;
    }
    
    .filter-sections {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .filter-grid {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
    
    .file-grid.grid-view {
        grid-template-columns: 1fr;
    }
    
    .file-grid.list-view .file-item {
        flex-direction: column;
    }
    
    .file-grid.list-view .file-thumbnail {
        width: 100%;
        height: 200px;
    }
    
    .search-controls {
        flex-direction: column;
    }
    
    .view-toggles {
        flex-direction: column;
    }
    
    .meta-grid {
        grid-template-columns: 1fr;
    }
    
    .file-actions {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // View toggle functionality
    $('.view-toggle').on('click', function() {
        var view = $(this).data('view');
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        $('.file-grid').removeClass('grid-view list-view').addClass(view + '-view');
        
        // Store preference
        localStorage.setItem('file-view-preference', view);
    });
    
    // Load saved view preference
    var savedView = localStorage.getItem('file-view-preference');
    if (savedView) {
        $('.view-toggle[data-view="' + savedView + '"]').click();
    }
    
    // Auto-submit on select changes
    $('.sort-section select, .view-section select[name="posts_per_page"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>

<?php get_footer(); ?>
