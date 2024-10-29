<?php
/**
 * @package BWDABPB Blocks
 * Blocks Loader
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blocks Loader Class
 */
class BWDABPB_ADVANCED_BLOG_POST_BLOCKS_LOADER {

    /**
     * Constructor
     */
    public function __construct() {
        
         // Register Blocks
        add_action( 'init', [ $this, 'register_blocks' ] );
        
        //Register Block Category
        if ( version_compare( BWDABPB_ADVANCED_BLOG_POST_WP_VERSION, '5.8', '>=' ) ) {
            add_filter( 'block_categories_all', [ $this, 'register_advanced_blog_post_block_category' ], 99999, 2 );
        } else {
            add_filter( 'block_categories', [ $this, 'register_advanced_blog_post_block_category' ], 99999, 2 );
        }

        // Enqueue Inline style on render block
        add_filter( 'render_block', [ $this, 'generate_inline_style_on_render_block' ], 10, 2 );

        // enqueue editor assets
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );

        // enqueue assets for frontend
        add_action( 'enqueue_block_assets', [ $this, 'enqueue_assets' ] );

         // Add filter for comment count
        add_filter('rest_prepare_post', [ $this, 'bwdabpb_add_comment_count_to_rest_response' ], 10, 3);
    }

    /**
     * Enqueue Editor Assets
     */
    public function enqueue_editor_assets(){
        wp_enqueue_script(
            'bwdabpb-blocks-global-js',
            BWDABPB_ADVANCED_BLOG_POST_ADMIN_URL . '/dist/global.js',
            [],
            BWDABPB_ADVANCED_BLOG_POST_VERSION,
            true
        );
    }

    /**
     * Enqueue Assets
     */
     public function enqueue_assets(){
         wp_enqueue_script( 'bwdabpb-blocks-frontend', BWDABPB_ADVANCED_BLOG_POST_ADMIN_URL . './includes/assets/js/main.js', [], BWDABPB_ADVANCED_BLOG_POST_VERSION, true);
    }
    /**
     * Blocks Category
     */
    public function register_advanced_blog_post_block_category( $categories, $post ) {
        return array_merge(
            [
                [
                    'slug' => 'bwdabpb-advanced-post-blocks',
                    'title' => __( 'Advanced Blog Post Block', 'advanced-blog-post-block' )
                ],
            ],
            $categories
        );
    }

    /**
     * Load Blocks
     */
    public function register_blocks() {
        
        // get all blocks from includes/blocks/blocks.php
        require_once BWDABPB_ADVANCED_BLOG_POST_DIR_PATH . './includes/blocks/blocks.php';

        // Register Blocks
        if( ! empty( $bwdabpb_advanced_blog_post_blocks ) && is_array( $bwdabpb_advanced_blog_post_blocks ) ) {
            foreach( $bwdabpb_advanced_blog_post_blocks as $block ) {
                $this->register_single_block( $block );
            }
        }

    }

    /**
     * Register Single Block
     */
    public function register_single_block( $block ) {
        register_block_type(
            BWDABPB_ADVANCED_BLOG_POST_DIR_PATH . './build/blocks/' . $block['name'],
            [
                'render_callback' => [ $this, 'bwdabpb_advanced_blog_post_render_callback']
            ]
        );
        
    }
   /**
     * Add Comment Count to REST API Response for a Post
    */
    public function bwdabpb_add_comment_count_to_rest_response($data, $post, $context) {

        $data->data['comment_count'] = get_comments_number($post->ID);

        return $data;
    }


    /**
     * Render callback for the BWD Advanced Post Block.
     */
        public function bwdabpb_advanced_blog_post_render_callback ($attributes, $content){
            $selectedCategory = isset($attributes['selectedCategory']) ? $attributes['selectedCategory'] : [];
            $categoryIds = array_column($selectedCategory, 'value'); 
            $selectedTag = isset($attributes['selectedTag']) ? $attributes['selectedTag'] : [];
            $tagIds = array_column($selectedTag, 'value'); 
            $selectedAuthor = isset($attributes['selectedAuthor']) ? $attributes['selectedAuthor'] : [];
            $authorIds = array_column($selectedAuthor, 'value');
            $selectCustomPost = isset($attributes['includePosts']) ? $attributes['includePosts'] : [];
            $includeId = array_column($selectCustomPost, 'value');
            $removePost = isset($attributes['excludePosts']) ? $attributes['excludePosts'] : [];
            $excludeId = array_column($removePost, 'value');
            
            // Building the query arguments based on attributes
            $args = [
                'post_type' => 'post',
                "posts_per_page" => isset($attributes['numberOfPost']) ? $attributes['numberOfPost'] : 4,
                'category__in' => $categoryIds,
                "order" => isset($attributes['order']) ? $attributes['order'] : '',
                "orderby" => isset($attributes['orderBy']) ? $attributes['orderBy'] : '',
                'author__in' => $authorIds,
                'tag__in' => $tagIds,
                'post__in' => $includeId,
                'post__not_in' => $excludeId,
                'paged' => get_query_var('paged') ? get_query_var('paged') : 1, 
            ];
            // Creating a new WP_Query instance
            $query = new WP_Query($args);

             // Extracting additional attributes
            $style = isset( $attributes['style'] ) ? $attributes['style'] : 'style-1';
            $container = isset( $attributes['containerWidth'] ) ? $attributes['containerWidth'] : 'container';
            $author_prefix = isset( $attributes['authorPrefix'] ) ? $attributes['authorPrefix'] : 'Written By';
            $read_time_suffix = isset( $attributes['readTimeSuffix'] ) ? $attributes['readTimeSuffix'] : 'Min Read';
            $show_header = isset( $attributes['showHeading'] ) ? $attributes['showHeading'] : false;
            $show_sub_header = isset( $attributes['showSubHeading'] ) ? $attributes['showSubHeading'] : false;
            $show_separator = isset( $attributes['showSeparator'] ) ? $attributes['showSeparator'] : false;
            $show_img_overlay = isset( $attributes['showImageOverlay'] ) ? $attributes['showImageOverlay'] : false;
            $header_title = isset( $attributes['headerTitle'] ) ? $attributes['headerTitle'] : 'Header Title Here';
            $sub_heading = isset( $attributes['subHeading'] ) ? $attributes['subHeading'] : 'Sub Title Here';
            $show_featured_image = isset( $attributes['showFeaturedImage'] ) ? $attributes['showFeaturedImage'] : true;
            $show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
            $title_tag = isset( $attributes['titleTag'] ) ? $attributes['titleTag'] : 'h2';
            $show_excerpt = isset( $attributes['showExcerpt'] ) ? $attributes['showExcerpt'] : true;
            $show_excerpt_content = isset( $attributes['showExcerptContent'] ) ? $attributes['showExcerptContent'] : true;
            $show_read_more = isset( $attributes['showReadMore'] ) ? $attributes['showReadMore'] : true;
            $show_date = isset( $attributes['showDate'] ) ? $attributes['showDate'] : true;
            $show_tags = isset($attributes['showTags']) ? $attributes['showTags'] : false;
            $show_comments = isset($attributes['showComments']) ? $attributes['showComments'] : false;
            $show_category = isset( $attributes['showCategory'] ) ? $attributes['showCategory'] : true;
            $excerpt_length = isset($attributes['wordsExcerpt']) ? absint($attributes['wordsExcerpt']) : 10;
            $title_length = isset($attributes['titleLength']) ? absint($attributes['titleLength']) : 5;
            $tail_indicator = isset( $attributes['wordsTailIndicator'] ) ? $attributes['wordsTailIndicator'] : '.....';
            $author_icon = isset( $attributes['authorIcon'] ) ? $attributes['authorIcon'] : 'gravatar';
            $show_pagination = isset( $attributes['showPagination'] ) ? $attributes['showPagination'] : false;
            $pagi_prev_text = isset( $attributes['pagiPrevText'] ) ? $attributes['pagiPrevText'] : 'Previous';
            $pagi_next_text = isset( $attributes['pagiNextText'] ) ? $attributes['pagiNextText'] : 'Next';
            $read_more_text = isset( $attributes['readMoreText'] ) ? $attributes['readMoreText'] : 'Read More';
            $item_animation = isset( $attributes['animation'] ) ? $attributes['animation'] : '';
            $customCss = isset($attributes['customCSS']) ? $attributes['customCSS'] : '';
            $custom_id = isset($attributes['id']) ? $attributes['id'] : '';
            $date_format = isset( $attributes['customDate'] ) ? $attributes['customDate'] : 'F j, Y';
            // Get block wrapper attributes
            $blocks_props = get_block_wrapper_attributes([
                'class' => $attributes['uniqueId'] . ' ' . $style . ' ' . $container . ' bwdabpb-responsive',
            ]);
            
             // Start output buffering
            ob_start();

            // Ensure that the custom CSS is not empty
            if (!empty($customCss)) {
                echo '<style type="text/css">' . esc_html($customCss) . '</style>';
            }
           
            // Building the main markup
            $markup = '<div '. $blocks_props .'>';
            // Post Header
             if ($show_header) {
                $markup .= '<div class="bwdabpb-header-title">';
                $markup .= '<h2>' . esc_html($header_title) . '</h2>';
                if ($show_sub_header) {
                    $markup .= '<p>' . esc_html($sub_heading) . '</p>';
                }
                if ($show_separator) {
                    $markup .= '<div></div>';
                }
                $markup .= '</div>';
            }
            $markup .= '<div class="bwdabpb-item-wrapper ' . esc_attr($item_animation) . '"' . ($custom_id ? ' id="' . esc_attr($custom_id) . '"' : '') . '>';
           
            // Loop through the query results
            if( $query->have_posts() ) {
                while( $query->have_posts() ) {
                    $query->the_post();

                    // Get post categories
                    $categories = get_the_category();
                    // Get post permalink
                    $permalink = get_permalink();

                    // Start building the post item markup
                    $markup .= '<div class="bwdabpb-post-item">';
                    $markup .= '<div class="bwdabpb-post-image';
                    $markup .= $show_img_overlay ? esc_attr(' show-image-overlay') : '';
                    $markup .= '">';

                    // Featured image handling
                    if ($show_featured_image && has_post_thumbnail()) {
                        $featured_image = get_the_post_thumbnail();
                        $markup .= '<a href="' . esc_url($permalink) . '">' . $featured_image . '</a>';
                    } else {
                        // Placeholder image if featured image is not available
                        $placeholder_image = plugin_dir_url(__FILE__) . 'assets/img/bwd-placeholder.jpg';
                        $markup .= '<a href="' . esc_url($permalink) . '"><img src="' . esc_url($placeholder_image) . '"></a>';
                    }
                    $markup .= '</div>'; 
                    // Post content section
                    $markup .= '<div class="bwdabpb-post-content">';
                    $markup .= '<div class="bwdabpb-post-dateTime">';
                     // Post date
                    if ( $show_date ) {
                        $published_date = get_the_date($date_format);
                        $markup .= '<div class="bwdabpb-post-date">';
                        $markup .= '<span class="bwdabpb-calender-icon"><span class="dashicons dashicons-calendar-alt"></span></span>'; 
                        $markup .= '<span class="bwdabpb-date">' . esc_html($published_date) . '</span>';
                        $markup .= '</div>';
                    }
                    // Post tags
                    if( $show_tags ) {
                        $tags = get_the_tags();
                        if ($show_tags && $tags) {
                            $markup .= '<div class="bwdabpb-post-tags">';
                            $markup .= '<span class="bwdabpb-tag-icon dashicons dashicons-tag"></span>';
                            foreach ($tags as $tag) {
                                $tag_link = get_tag_link($tag->term_id);
                                $tag_name = $tag->name;
                                $markup .= '<a href="' . esc_url($tag_link) . '">' . esc_html($tag_name) . '</a>';
                            }
                            $markup .= '</div>';
                        }
                    }
                    // Comment count
                    if( $show_comments ) {
                        $comment_count = get_comments_number();
                        $markup .= '<div class="bwdabpb-comment-count">';
                        $markup .= '<span class="bwdabpb-comment-icon dashicons dashicons-admin-comments"></span>';
                        $markup .= '<span class="bwdabpb-comment-number">' . esc_html($comment_count) . '</span>';
                        $markup .= '</div>';
                    }
                    
                    // Post estimate time
                    $markup .= '<div class="bwdabpb-post-estimate">';
                    $words_per_minute = 200;
                    $post_word_count = str_word_count(wp_strip_all_tags(get_the_content()));
                    $reading_time_minutes = ceil($post_word_count / $words_per_minute);
                    $markup .= '<span class="bwdabpb-clock-icon"><span class="dashicons dashicons-clock"></span></span>';
                    $markup .= '<span class="bwdabpb-estimate-time">' . esc_html($reading_time_minutes) . '</span>';
                    $markup .= '<span class="bwdabpb-estimate-text">'. esc_html($read_time_suffix) .'</span>';

                    $markup .= '</div>';
                    $markup .= '</div>'; 
                    // Post title
                    if ($show_title) {
                        $title = wp_trim_words(get_the_title(), $title_length, '');
                        $title = rtrim($title, '.');
                        $markup .= '<' . esc_html($title_tag) . ' class="bwdabpb-post-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></' . esc_html($title_tag) . '>';
                    }
                    // Post excerpt
                    if ($show_excerpt) {
                        $markup .= '<div class="excerpt">';
                        if ($show_excerpt_content) {
                            $excerpt_source = get_the_content();
                        } else {
                            $excerpt_source = get_the_excerpt();
                        }
                        $excerpt = wp_trim_words($excerpt_source, $excerpt_length, '');
                        $markup .= '<p>' . wp_kses_post($excerpt) . esc_html($tail_indicator) . '</p>';
                        $markup .= '</div>';
                    }
                    // Read more link
                    if ($show_read_more) {
                        $markup .= '<a href="' . esc_url($permalink) . '" class="bwdabpb-read-more">' . esc_html($read_more_text) . '</a>';
                    }
                    $markup .= '</div>';
                    // Post categories
                    if ($show_category && $categories) {
                        $markup .= '<span class="bwdabpb-post-category">';
                        foreach ($categories as $category) {
                            $category_link = get_category_link($category->term_id);
                            $category_name = $category->name;
                            $markup .= '<a href="' . esc_url($category_link) . '">' . esc_html($category_name) . '</a>';
                        }
                        $markup .= '</span>';
                    }
                    $markup .= '<div class="bwdabpb-post-meta-box">';
                    // Author icon handling
                    if ($author_icon === 'gravatar') {
                        $author_avatar = get_avatar(get_the_author_meta('user_email'), 80);
                        $markup .= '<span class="bwdabpb-author-icon">' . $author_avatar . '</span>';
                    } elseif ($author_icon === 'icon') {
                        $markup .= '<span class="bwdabpb-author-icon"><span class="dashicons dashicons-admin-users"></span></span>';
                    }
                     // Post meta content
                    $markup .= '<div class="bwdabpb-post-meta-content">';
                    $markup .= '<span class="bwdabpb-author-prefix">' . esc_html($author_prefix) . '</span>';
                    // Author link
                    $author_link = get_author_posts_url(get_the_author_meta('ID'));
                    $markup .= '<a class="bwdabpb-author-name" href="' . esc_url($author_link) . '">' . esc_html(get_the_author()) . '</a>';
                    $markup .= '</div>';
                    $markup .= '</div>';
                    $markup .= '</div>';
                }
                // Reset post data after the loop
                wp_reset_postdata();
            }
            $markup .= '</div>';
            // Pagination
            if ($show_pagination) {
                $pagination_args = array(
                    'total'      => $query->max_num_pages,
                    'prev_text' => '<svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m145.19 238.58 215.5-215.5c5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0l-225.1 225.1c-5.3 5.3-5.3 13.8 0 19.1l225.1 225c2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4c5.3-5.3 5.3-13.8 0-19.1l-215.4-215.5z"></path></svg><span>'. esc_html($pagi_prev_text) .'</span>',
                    'next_text' => '<span>'. esc_html($pagi_next_text) .'</span><svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m360.73 229.08-225.1-225.1c-5.3-5.3-13.8-5.3-19.1 0s-5.3 13.8 0 19.1l215.5 215.5-215.5 215.5c-5.3 5.3-5.3 13.8 0 19.1 2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4l225.1-225.1c5.3-5.2 5.3-13.8 0.1-19z"></path></svg>',
                    'type'       => 'list',
                );

                $pagination_links = paginate_links($pagination_args);
                // Sanitize and echo the pagination links
                echo wp_kses_post($pagination_links);
            }
            $markup .= '</div>';


            // End output buffering and return the markup
            $markup .= ob_get_clean();

            return $markup;   
        }


    /**
     * Register Inline Style
     */
    function generate_inline_style_on_render_block($block_content, $block ) {

        if (isset($block['blockName']) && str_contains($block['blockName'], 'bwdabpb/')) {
            if (isset($block['attrs']['blockStyle'])) {

                $style = $block['attrs']['blockStyle'];
                $handle = isset( $block['attrs']['uniqueId'] ) ? $block['attrs']['uniqueId'] : 'bwdabpb-advanced-blog-post-block';

                // convert style array to string
                if ( is_array($style) ) {
                    $style = implode(' ', $style);
                }

                // minify style to remove extra space
                $style = preg_replace( '/\s+/', ' ', $style );

                wp_register_style(
                    $handle,
                    false,
                    [],
                    BWDABPB_ADVANCED_BLOG_POST_VERSION
                );
                wp_enqueue_style( $handle );
                wp_add_inline_style( $handle, $style );

            }
        }
        return $block_content;
    }

}

new BWDABPB_ADVANCED_BLOG_POST_BLOCKS_LOADER();