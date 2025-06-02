jQuery(document).ready(function($) {
    // Handle FAQ button click
    $('.faq-button').on('click', function(e) {
        e.preventDefault();
        
        // Get the FAQ content
        var faqContent = `
            <div class="faq-content">
                <h3>سوالات متداول</h3>
                <div class="faq-item">
                    <h4>چگونه می‌توانم به پادکست بپیوندم؟</h4>
                    <p>برای پیوستن به پادکست، کافیست فرم بالا را پر کنید. ما در اسرع وقت با شما تماس خواهیم گرفت.</p>
                </div>
                <div class="faq-item">
                    <h4>آیا نیاز به تجربه خاصی دارم؟</h4>
                    <p>خیر، ما به دنبال افراد با انگیزه و علاقه‌مند هستیم. تجربه قبلی در زمینه پادکست ضروری نیست.</p>
                </div>
                <div class="faq-item">
                    <h4>زمان‌بندی همکاری چگونه است؟</h4>
                    <p>زمان‌بندی همکاری منعطف است و بر اساس برنامه‌های شما تنظیم می‌شود.</p>
                </div>
                <button class="close-faq">بستن</button>
            </div>
        `;
        
        // Show FAQ content in a modal
        $('body').append('<div class="faq-modal"></div>');
        $('.faq-modal').html(faqContent).fadeIn();
        
        // Handle close button
        $('.close-faq').on('click', function() {
            $('.faq-modal').fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Close on outside click
        $('.faq-modal').on('click', function(e) {
            if ($(e.target).hasClass('faq-modal')) {
                $(this).fadeOut(function() {
                    $(this).remove();
                });
            }
        });
    });
    
    // Handle form submission
    $('.podcast-join-form').on('submit', function(e) {
        var name = $('#name').val();
        var email = $('#email').val();
        
        if (!name || !email) {
            e.preventDefault();
            alert('لطفا تمام فیلدها را پر کنید.');
            return false;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            alert('لطفا یک ایمیل معتبر وارد کنید.');
            return false;
        }
    });
    
    // Email validation function
    function isValidEmail(email) {
        var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }
}); 