<?php
/**
 * Template Name: Podcast Join Page
 */

get_header(); ?>
<style>
    .sections-holder{
        height: 90vh;
    }
</style>
<div class="sections-holder d-flex flex-column justify-content-between" >
    <div class="collabs animate__animated animate__fadeInRight">
        <h1>به پادکست ما بپیوندید</h1>
        <div class="collab-list">
            <?php
            // Get podcast collaborators
            $collaborators = get_posts(array(
                'post_type' => 'podcast',
                'posts_per_page' => 4,
                'orderby' => 'rand'
            ));

            foreach ($collaborators as $collaborator) :
                $thumbnail = get_the_post_thumbnail_url($collaborator->ID);
            ?>
                <li>
                    <a href="<?php echo get_permalink($collaborator->ID); ?>" 
                       style="background-image: url('<?php echo esc_url($thumbnail); ?>');">
                    </a>
                </li>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="radio-container animate__animated animate__fadeInUp">
        <div class="overlay"></div>
        <div class="content-wrapper">
            <div class="top-section">
                <div class="flex flex-col items-start relative z-10">
                    <div class="spot-box">
                        <div class="spot">۴۳.۹۵ اسپات</div>
                    </div>
                    <div class="radio-box">
                        <div class="radio-text">رادیو</div>
                    </div>
                </div>
            </div>

            <div class="join-section">
                <h2 class="join-title">همین حالا عضو شوید</h2>
                <form class="c-input-group" id="podcast-join-form" method="post">
                    <?php wp_nonce_field('podcast_join_nonce', 'podcast_join_nonce'); ?>
                    <input type="text" name="name" placeholder="نام شما" class="input-field" required />
                    <input type="email" name="email" placeholder="ایمیل شما" class="input-field" required />
                    <button type="submit" class="c-btn curve dark solid">ثبت نام</button>
                    <div id="podcast-subscription-message" class="podcast-message" style="display: none;color:#fff;"></div>
                </form>
            </div>

            <div class="bottom-section">
                <p class="experience-text">ما می‌خواهیم تجربه خوبی برای شما بسازیم!</p>
                <div class="contact-group">
                    <button class="contact-button" id="faq-button">سوالی دارید؟</button>
                    <a class="contact-button" href="<?php echo get_permalink(get_page_by_path('contact-us')); ?>">تماس با ما</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#podcast-join-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('#podcast-subscription-message');
        var $submitButton = $form.find('button[type="submit"]');
        
        // Disable submit button
        $submitButton.prop('disabled', true);
        
        // Clear previous message
        $message.removeClass('success error').hide();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'podcast_subscription',
                name: $form.find('input[name="name"]').val(),
                email: $form.find('input[name="email"]').val(),
                podcast_join_nonce: $form.find('input[name="podcast_join_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data).show();
                    $form[0].reset();
                } else {
                    $message.addClass('error').text(response.data).show();
                }
            },
            error: function() {
                $message.addClass('error').text('An error occurred. Please try again.').show();
            },
            complete: function() {
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script>

<?php get_footer(); ?> 