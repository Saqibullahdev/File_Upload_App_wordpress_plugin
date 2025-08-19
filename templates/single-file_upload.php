<?php
/**
 * Single File Upload Template
 * 
 * Template for displaying individual file upload posts
 */

get_header(); ?>

<div class="file-upload-single">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('file-upload-article'); ?>>
            
            <header class="file-header">
                <h1 class="file-title"><?php the_title(); ?></h1>
                
                <div class="file-meta">
                    <span class="file-date"><?php echo get_the_date(); ?></span>
                    <span class="file-author">by <?php the_author(); ?></span>
                </div>
            </header>
            
            <?php if (has_post_thumbnail()) : ?>
                <div class="file-featured-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>
            
            <div class="file-content">
                <?php
                the_content();
                
                wp_link_pages(array(
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'file-upload-app'),
                    'after'  => '</div>',
                ));
                ?>
            </div>
            
            <div class="file-details">
                <?php
                $file_url = get_post_meta(get_the_ID(), '_file_url', true);
                $file_size = get_post_meta(get_the_ID(), '_file_size', true);
                $file_type = get_post_meta(get_the_ID(), '_file_type', true);
                $download_count = get_post_meta(get_the_ID(), '_download_count', true);
                ?>
                
                <div class="file-info-grid">
                    <?php if ($file_size) : ?>
                        <div class="file-info-item">
                            <strong><?php _e('File Size:', 'file-upload-app'); ?></strong>
                            <span><?php echo esc_html($file_size); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($file_type) : ?>
                        <div class="file-info-item">
                            <strong><?php _e('File Type:', 'file-upload-app'); ?></strong>
                            <span><?php echo esc_html($file_type); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($download_count) : ?>
                        <div class="file-info-item">
                            <strong><?php _e('Downloads:', 'file-upload-app'); ?></strong>
                            <span class="download-count"><?php echo intval($download_count); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($file_url) : ?>
                    <div class="file-download-section">
                        <a href="<?php echo esc_url($file_url); ?>" 
                           class="download-btn primary" 
                           data-file-id="<?php the_ID(); ?>" 
                           target="_blank">
                            <span class="download-icon">⬇</span>
                            <?php _e('Download File', 'file-upload-app'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="file-taxonomies">
                <?php
                $categories = get_the_terms(get_the_ID(), 'file_category');
                $types = get_the_terms(get_the_ID(), 'file_type');
                $tags = get_the_tags(get_the_ID());
                ?>
                
                <?php if ($categories && !is_wp_error($categories)) : ?>
                    <div class="taxonomy-section">
                        <h3><?php _e('Categories', 'file-upload-app'); ?></h3>
                        <div class="taxonomy-terms">
                            <?php foreach ($categories as $category) : ?>
                                <a href="<?php echo get_term_link($category); ?>" class="term-link category-term">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($types && !is_wp_error($types)) : ?>
                    <div class="taxonomy-section">
                        <h3><?php _e('Types', 'file-upload-app'); ?></h3>
                        <div class="taxonomy-terms">
                            <?php foreach ($types as $type) : ?>
                                <a href="<?php echo get_term_link($type); ?>" class="term-link type-term">
                                    <?php echo esc_html($type->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($tags && !is_wp_error($tags)) : ?>
                    <div class="taxonomy-section">
                        <h3><?php _e('Tags', 'file-upload-app'); ?></h3>
                        <div class="taxonomy-terms">
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo get_tag_link($tag); ?>" class="term-link tag-term">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="file-navigation">
                <?php
                $prev_post = get_previous_post(false, '', 'file_category');
                $next_post = get_next_post(false, '', 'file_category');
                ?>
                
                <?php if ($prev_post || $next_post) : ?>
                    <nav class="post-navigation">
                        <?php if ($prev_post) : ?>
                            <div class="nav-previous">
                                <a href="<?php echo get_permalink($prev_post); ?>">
                                    <span class="nav-subtitle"><?php _e('Previous File', 'file-upload-app'); ?></span>
                                    <span class="nav-title"><?php echo get_the_title($prev_post); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($next_post) : ?>
                            <div class="nav-next">
                                <a href="<?php echo get_permalink($next_post); ?>">
                                    <span class="nav-subtitle"><?php _e('Next File', 'file-upload-app'); ?></span>
                                    <span class="nav-title"><?php echo get_the_title($next_post); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>
            
        </article>
        
        <?php
        // Related files
        $categories = wp_get_post_terms(get_the_ID(), 'file_category', array('fields' => 'ids'));
        if ($categories) {
            $related_args = array(
                'post_type' => 'file_upload',
                'posts_per_page' => 4,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'file_category',
                        'field' => 'term_id',
                        'terms' => $categories
                    )
                )
            );
            
            $related_query = new WP_Query($related_args);
            
            if ($related_query->have_posts()) : ?>
                <section class="related-files">
                    <h2><?php _e('Related Files', 'file-upload-app'); ?></h2>
                    <div class="related-files-grid">
                        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                            <div class="related-file-item">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="related-file-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('thumbnail'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="related-file-content">
                                    <h3 class="related-file-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <?php if (has_excerpt()) : ?>
                                        <div class="related-file-excerpt">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $related_file_url = get_post_meta(get_the_ID(), '_file_url', true);
                                    if ($related_file_url) : ?>
                                        <a href="<?php echo esc_url($related_file_url); ?>" 
                                           class="related-download-btn" 
                                           data-file-id="<?php the_ID(); ?>" 
                                           target="_blank">
                                            <?php _e('Download', 'file-upload-app'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php
                wp_reset_postdata();
            endif;
        }
        ?>
        
    <?php endwhile; ?>
</div>

<style>
.file-upload-single {
    max-width: 100%;
    margin: 0;
    padding: 10px;
    word-wrap: break-word;
}

.file-upload-article {
    background: transparent;
    border-radius: 0;
    box-shadow: none;
    padding: 15px;
    margin-bottom: 20px;
}

.file-header {
    text-align: left;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.file-title {
    font-size: 1.8em;
    margin-bottom: 10px;
    color: inherit;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.file-meta {
    color: #666;
    font-size: 14px;
}

.file-meta span {
    margin-right: 15px;
    display: inline-block;
}

.file-featured-image {
    text-align: center;
    margin-bottom: 20px;
}

.file-featured-image img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.file-content {
    font-size: inherit;
    line-height: 1.6;
    margin-bottom: 20px;
    word-wrap: break-word;
}

.file-details {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    overflow: hidden;
}

.file-info-grid {
    display: block;
    margin-bottom: 15px;
}

.file-info-item {
    display: block;
    padding: 8px 0;
    background: transparent;
    border-radius: 0;
    border-left: none;
    border-bottom: 1px solid #eee;
}

.file-info-item:last-child {
    border-bottom: none;
}

.file-download-section {
    text-align: center;
    margin-top: 15px;
}

.download-btn.primary {
    background: #0073aa;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 16px;
    font-weight: normal;
    display: inline-block;
    transition: background-color 0.3s ease;
    box-shadow: none;
    max-width: 100%;
    word-wrap: break-word;
}

.download-btn.primary:hover {
    background: #005a87;
    color: white;
    transform: none;
    box-shadow: none;
}

.download-icon {
    font-size: 16px;
    margin-right: 5px;
}

.file-taxonomies {
    margin-bottom: 20px;
    overflow: hidden;
}

.taxonomy-section {
    margin-bottom: 15px;
}

.taxonomy-section h3 {
    font-size: 16px;
    margin-bottom: 8px;
    color: inherit;
}

.taxonomy-terms {
    display: block;
    word-wrap: break-word;
}

.term-link {
    background: #f0f0f0;
    color: #333;
    padding: 4px 8px;
    border-radius: 3px;
    text-decoration: none;
    font-size: 12px;
    display: inline-block;
    margin: 2px 4px 2px 0;
    transition: background-color 0.2s ease;
}

.term-link:hover {
    background: #0073aa;
    color: white;
}

.post-navigation {
    display: block;
    margin-bottom: 20px;
    overflow: hidden;
}

.nav-previous,
.nav-next {
    margin-bottom: 10px;
}

.nav-previous a,
.nav-next a {
    display: block;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s ease;
    word-wrap: break-word;
}

.nav-previous a:hover,
.nav-next a:hover {
    background: #f0f0f0;
}

.nav-subtitle {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.nav-title {
    display: block;
    font-weight: 600;
    word-wrap: break-word;
}

.related-files {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    overflow: hidden;
}

.related-files h2 {
    text-align: center;
    margin-bottom: 20px;
    color: inherit;
    font-size: 1.5em;
}

.related-files-grid {
    display: block;
}

.related-file-item {
    background: white;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #eee;
}

.related-file-thumbnail {
    height: 120px;
    overflow: hidden;
    margin-bottom: 10px;
}

.related-file-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-file-content {
    padding: 0;
}

.related-file-title {
    font-size: 14px;
    margin-bottom: 8px;
}

.related-file-title a {
    color: inherit;
    text-decoration: none;
    word-wrap: break-word;
}

.related-file-title a:hover {
    color: #0073aa;
}

.related-file-excerpt {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
    word-wrap: break-word;
}

.related-download-btn {
    background: #0073aa;
    color: white;
    padding: 6px 12px;
    border-radius: 3px;
    text-decoration: none;
    font-size: 12px;
    display: inline-block;
}

.related-download-btn:hover {
    background: #005a87;
    color: white;
}

@media (max-width: 768px) {
    .file-upload-single {
        padding: 5px;
    }
    
    .file-upload-article {
        padding: 10px;
    }
    
    .file-title {
        font-size: 1.5em;
    }
    
    .file-meta span {
        display: block;
        margin-right: 0;
        margin-bottom: 5px;
    }
    
    .download-btn.primary {
        padding: 10px 15px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
    }
}
</style>

<?php get_footer(); ?>
