<?php
add_shortcode( 'on_cf7_submit', 'on_cf7_submit_function' );

function on_cf7_submit_function(){
    return "<h2>Submitted Forms: </h2>";
}

add_action('wp_footer', 'add_scripts_in_footer');
function add_scripts_in_footer(){
    ?>
    <script>
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
                    $form_data = unserialize($item->form_value);
                    $name = $form_data['your-name'];
                    $email = $form_data['your-email'];
                    $message = $form_data['your-message'];
                    $date = $item->form_date;
                    // Split the string into date and time part
                    list($date, $time) = explode(' ', $item->form_date);
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
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
        <h4>No messages found yet!</h4>
    <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
