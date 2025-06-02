<?php
/**
 * The template for displaying search results pages
 */

get_header(); ?>
<style>
    body {
        overflow-y: auto;
    }
</style>
<section class="posts-holder animate__animated animate__fadeIn">
    <header>
        <h1>نتایج جستجو برای: <?php echo get_search_query(); ?></h1>
    </header>
    <ul class="list">
        <?php
        // Get the current page number
        $c_page = get_query_var('paged') ? get_query_var('paged') : 1;
        
        // Modify the query to include both articles and podcasts
        $args = array(
            'post_type' => array('article', 'podcast'),
            'posts_per_page' => get_option('posts_per_page'),
            'paged' => $c_page,
            'post_status' => 'publish',
            's' => get_search_query()
        );

        $custom_query = new WP_Query($args);

        if ($custom_query->have_posts()):
            while ($custom_query->have_posts()):
                $custom_query->the_post();
                $post_type = get_post_type();
                $post_type_class = $post_type === 'podcast' ? 'podcast' : 'article';
                ?>
                <li class="<?php echo $post_type_class; ?>">
                    <a href="<?php the_permalink(); ?>">
                        <div class="image" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');"></div>
                        <div class="details">
                            <div class="label">
                                <span><?php echo get_the_date('M d, Y'); ?></span>
                                <span class="type"><?php echo $post_type === 'podcast' ? 'پادکست' : 'مقاله'; ?></span>
                            </div>
                            <h2 class="title"><?php the_title(); ?></h2>
                            <p class="description">
                                <?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?>
                            </p>
                        </div>
                    </a>
                </li>
                <?php
            endwhile;
        else:
            echo '<li class="no-posts">';
            echo '<p>هیچ نتیجه‌ای برای جستجوی شما یافت نشد.</p>';
            echo '</li>';
        endif;
        ?>
    </ul>
    <?php
    if ($custom_query->max_num_pages > 1) {
        echo '<div class="pagination">';
        $current_url = add_query_arg(array());
        $current_url = remove_query_arg('paged', $current_url);
        $current_url = rtrim($current_url, '/');
        
        echo '<ul class="page-numbers">';
        
        // Previous page link
        if ($c_page > 1) {
            $prev_url = add_query_arg('paged', $c_page - 1, $current_url);
            echo '<li><a class="prev page-numbers" href="' . esc_url($prev_url) . '"><i class="fas fa-chevron-left"></i></a></li>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $custom_query->max_num_pages; $i++) {
            $page_url = add_query_arg('paged', $i, $current_url);
            $class = $i == $c_page ? 'current' : '';
            echo '<li><a class="page-numbers ' . $class . '" href="' . esc_url($page_url) . '">' . $i . '</a></li>';
        }
        
        // Next page link
        if ($c_page < $custom_query->max_num_pages) {
            $next_url = add_query_arg('paged', $c_page + 1, $current_url);
            echo '<li><a class="next page-numbers" href="' . esc_url($next_url) . '"><i class="fas fa-chevron-right"></i></a></li>';
        }
        
        echo '</ul>';
        echo '</div>';
    }

    wp_reset_postdata();
    ?>
</section>

<?php get_footer(); ?> 