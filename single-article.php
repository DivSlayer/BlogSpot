<?php get_header(); ?>
<style>
    body {
        overflow-y: auto;
    }
</style>
<main class="article-single">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article class="article-content">
                <header class="article-header">
                    <h1 class="article-title"><?php the_title(); ?></h1>
                    <div class="article-meta">
                        <span class="author">By <?php the_author(); ?></span>
                        <span class="date"><?php echo get_the_date(); ?></span>
                        <span class="reading-time"><?php echo get_post_meta(get_the_ID(), 'reading_time', true); ?> min read</span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="article-featured-image">
                        <?php the_post_thumbnail('full'); ?>
                        <?php if (get_the_post_thumbnail_caption()) : ?>
                            <figcaption class="featured-caption"><?php echo get_the_post_thumbnail_caption(); ?></figcaption>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="article-body">
                    <?php the_content(); ?>
                </div>

                <footer class="article-footer">
                    <div class="article-tags">
                        <?php the_tags('<span class="tags-label">Tags:</span> ', ', '); ?>
                    </div>
                    
                    <div class="article-categories">
                        <span class="categories-label">Categories:</span>
                        <?php the_category(', '); ?>
                    </div>

                    <div class="article-share">
                        <h3>Share this article</h3>
                        <div class="share-buttons">
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share-twitter">Twitter</a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="share-facebook">Facebook</a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share-linkedin">LinkedIn</a>
                        </div>
                    </div>
                </footer>
            </article>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?> 