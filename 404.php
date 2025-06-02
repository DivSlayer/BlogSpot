<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Blogspot
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <div class="error-404 not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php esc_html_e('صفحه مورد نظر یافت نشد', 'blogspot'); ?></h1>
                    </header>

                    <div class="page-content">
                        <p><?php esc_html_e('متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا حذف شده است.', 'blogspot'); ?>
                        </p>

                        <div class="error-search">
                            <form role="search" method="get" id="searchform" class="searchform"
                                action="/">
                                <div>
                                    <label class="screen-reader-text" for="s">Search for:</label>
                                    <input type="text" placeholder="نام مقاله، پادکست و..." value="" name="s" id="s" style="width:50%">
                                    <input type="submit" id="searchsubmit" class="c-btn curve dark" value="جستجو">
                                </div>
                            </form>
                        </div>

                        <div class="error-suggestions mt-4">
                            <h2><?php esc_html_e('پیشنهادات', 'blogspot'); ?></h2>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h3><?php esc_html_e('آخرین مطالب', 'blogspot'); ?></h3>
                                            <ul class="list-unstyled">
                                                <?php
                                                $recent_posts = wp_get_recent_posts(array(
                                                    'numberposts' => 3,
                                                    'post_status' => 'publish'
                                                ));
                                                foreach ($recent_posts as $post) {
                                                    echo '<li><a href="' . get_permalink($post['ID']) . '">' . $post['post_title'] . '</a></li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h3><?php esc_html_e('دسته‌بندی‌ها', 'blogspot'); ?></h3>
                                            <ul class="list-unstyled">
                                                <?php
                                                wp_list_categories(array(
                                                    'title_li' => '',
                                                    'number' => 5
                                                ));
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h3><?php esc_html_e('برچسب‌ها', 'blogspot'); ?></h3>
                                            <div class="tag-cloud">
                                                <?php
                                                wp_tag_cloud(array(
                                                    'number' => 10,
                                                    'smallest' => 12,
                                                    'largest' => 20
                                                ));
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="error-home mt-4">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="c-btn curve dark">
                                <?php esc_html_e('بازگشت به صفحه اصلی', 'blogspot'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();