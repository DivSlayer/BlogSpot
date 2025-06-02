<?php
/**
 * Template Name: Contact Page
 */

get_header(); ?>

<div class="sections-holder d-flex flex-column" style="height: 100vh;">
    <div class="d-flex flex-column align-items-center justify-content-center" style="height: 100%;">
        <div class="radio-container animate__animated animate__fadeInUp">
            <div class="overlay"></div>
            <div class="content-wrapper">
                <?php
                // Display success/error messages
                if (isset($_GET['status'])) {
                    if ($_GET['status'] === 'success') {
                        echo '<div class="podcast-message success">پیام شما با موفقیت ارسال شد. به زودی با شما تماس خواهیم گرفت.</div>';
                    } elseif ($_GET['status'] === 'error') {
                        echo '<div class="podcast-message error">متأسفانه در ارسال پیام مشکلی پیش آمد. لطفاً دوباره تلاش کنید.</div>';
                    }
                }
                ?>

                <div class="join-section">
                    <h2 class="join-title">باما تماس بگیرید!</h2>
                    <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>
                        <input type="hidden" name="action" value="handle_contact_form">
                        
                        <div class="c-input-group">
                            <input type="text" name="name" placeholder="نام شما" class="input-field" required />
                            <input type="email" name="email" placeholder="ایمیل شما" class="input-field" required />
                        </div>
                        <div class="row d-flex align-items-center flex-column" style="margin-top: 20px;">
                            <textarea name="message" placeholder="متن پیام" class="input-field" style="min-width: 500px;" required></textarea>
                            <button type="submit" class="c-btn curve dark solid" style="width: fit-content;margin-top: 10px;">ارسال</button>
                        </div>
                    </form>
                </div>

                <div class="bottom-section">
                    <p class="experience-text">ما می‌خواهیم تجربه خوبی برای شما بسازیم!</p>
                    <div class="contact-group">
                        <button class="contact-button" id="faq-button">سوالی دارید؟</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 