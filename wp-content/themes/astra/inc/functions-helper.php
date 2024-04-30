<?php
add_shortcode( 'on_cf7_submit', 'on_cf7_submit_function' );

function on_cf7_submit_function(){
    return "<h2>Submitted Forms: </h2>";
}

add_action('wp_footer', 'add_scripts_in_footer');
function add_scripts_in_footer(){
    ?>
    <script>
        const current_user_id = "<?php echo get_current_user_id() ?>";

        document.addEventListener( 'wpcf7mailsent', function( event ) {
            console.log(event);

            fetch("<?php echo admin_url('admin-ajax.php') ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    'action': 'my_ajax_action',
                    'form_id': event.detail.apiResponse.contact_form_id
                })
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(function(data) {
                    // Handle response here
                    console.log(data);
                    jQuery(function ($){
                        $('.list-table-js .elementor-shortcode .contact-list-table').slideUp(1500, function (){
                            setTimeout(function (){
                                $('.list-table-js .elementor-shortcode').html(data);
                            },500)
                        });
                    })
                })
                .catch(function(error) {
                    console.error('There has been a problem with your fetch operation:', error);
                });
        }, false );

        const is_administrator = "<?php echo current_user_can( 'manage_options' ) ?>";
        const is_login_page = "<?php echo is_page('login') ?>";
        const is_user_logged_in = "<?php echo is_user_logged_in() ?>";
        if(is_login_page && is_user_logged_in){
            window.location.replace("<?php echo home_url('/') ?>");
        }
        document.querySelector('.user-id-js').value = current_user_id;
    </script>
<?php
}

// AJAX Handler
add_action('wp_ajax_my_ajax_action', 'my_ajax_function');
add_action('wp_ajax_nopriv_my_ajax_action', 'my_ajax_function');

function my_ajax_function(){
    $form_id = $_POST['form_id'];
    echo function_stored_contact_list();
    die();
}

function dbcf7_contact_list($form_id){
    global $wpdb;
    $table_name = $wpdb->prefix . 'db7_forms';
    return $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $form_id ORDER BY form_id DESC", OBJECT );
}

add_shortcode('stored_contact_list', 'function_stored_contact_list');
function function_stored_contact_list(){
    $contact_list = dbcf7_contact_list(6);
    ob_start();
    if(is_user_logged_in()):
    ?>
    <div class="contact-list-table">
        <?php if($contact_list): ?>
            <style>
                .table p{
                    margin: 0;
                    line-height: 1.2;
                }
                .elementor .table hr,
                .table hr{
                    margin: 8px -15px;
                    background: #ececec;
                }
                .table .contact-title{
                    margin-bottom: 10px;
                    font-weight: 600;
                }
            </style>
            <table class="table">
                <thead>
                <tr>
                    <td>#</td>
                    <td>Contact Details</td>
                    <td>Date</td>
                </tr>
                </thead>
                <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($contact_list as $item): ?>
                    <?php
                    $current_user_id = get_current_user_id();
                    $form_data = unserialize($item->form_value);
                    $user_id = $form_data['user_id'];
                    $name = $form_data['your-name'];
                    $email = $form_data['your-email'];
                    $message = $form_data['your-message'];
                    $date = $item->form_date;
                    // Split the string into date and time part
                    list($date, $time) = explode(' ', $item->form_date);
                    if($current_user_id == $user_id):
                    ?>
                    <tr>
                        <td><?php echo $counter ?></td>
                        <td>
                            <p class="contact-title"><?php echo $name ?></p>
                            <p><a href="mailto:<?php echo $email ?>"><?php echo $email ?></a> </p>
                            <hr />
                            <p><?php echo $message ?></p>
                        </td>
                        <td>
                            <p><?php echo $date ?></p>
                            <hr />
                            <p><?php echo $time ?></p>
                        </td>
                    </tr>
                    <?php $counter++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
        <h4>No messages found yet!</h4>
    <?php endif; ?>
    </div>
    <?php
    endif;
    return ob_get_clean();
}

add_shortcode('contact_form', 'function_contact_form');
function function_contact_form(){
    ob_start();
    ?>
    <div class="contact-form-holder">
        <?php if(is_user_logged_in()): ?>
            <style>
                .text-center{
                    text-align: center;
                }
            </style>
            <div class="text-center">
                <h3>Send us a message</h3>
                <p>Have something to say? Send us a message and let’s start the conversation.</p>
            </div>

            <?php echo do_shortcode('[contact-form-7 id="d223270" title="Contact Form"]') ?>
        <?php else: ?>
            <div>
                <p>Please <a href="<?php site_url() ?>/login?redirect_to=<?php echo urlencode(home_url('/')) ?>">login</a> or <a href="<?php site_url() ?>/register">register</a> to access the form. </p>
            </div>
        <?php endif; ?>

    </div>
<?php
    return ob_get_clean();
}