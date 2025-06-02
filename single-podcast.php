<?php get_header(); ?>
<style>
    body {
        overflow-y: auto;
    }
</style>
<main class="podcast-single">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article class="podcast-content">
                <div class="podcast-header">
                    <h1 class="podcast-title"><?php the_title(); ?></h1>
                    <div class="podcast-meta">
                        <span class="date"><?php echo get_the_date(); ?></span>
                        <span class="duration"><?php echo get_post_meta(get_the_ID(), 'podcast_duration', true); ?></span>
                    </div>
                </div>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="podcast-cover">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="podcast-player">
                    <?php 
                    $audio_url = get_post_meta(get_the_ID(), '_podcast_audio', true);
                    if ($audio_url) : ?>
                        <audio controls>
                            <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    <?php else : ?>
                        <p>No audio file found for this podcast.</p>
                    <?php endif; ?>
                </div>

                <div class="podcast-description">
                    <?php the_content(); ?>
                </div>

                <div class="podcast-details">
                    <div class="podcast-info">
                        <h3>درباره پادکست</h3>
                        <ul>
                            <?php 
                            $host_id = get_post_meta(get_the_ID(), '_podcast_host_id', true);
                            if ($host_id) {
                                $host = get_userdata($host_id);
                                if ($host) {
                                    echo '<li><span>میزبان:</span> ' . esc_html($host->display_name) . '</li>';
                                }
                            }

                            // Check if post has categories
                            if (has_category()) {
                                echo '<li><span>دسته بندی:</span> ';
                                the_category(', ');
                                echo '</li>';
                            }

                            // Check if post has tags
                            if (has_tag()) {
                                echo '<li><span>تگ ها:</span> ';
                                the_tags('', ', ');
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?> 