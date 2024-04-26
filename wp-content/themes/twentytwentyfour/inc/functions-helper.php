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
                    console.log(JSON.parse(data));
                    console.log(typeof JSON.parse(data));
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
    $response = [
        'status' => true,
        'message' => 'successful request!'
    ];
    $form_id = $_POST['form_id'];
    $results = dbcf7_contact_list($form_id);
    $stored_contact_list = [];
    foreach ($results as $item) {
        $form_data = unserialize( $item->form_value );
        $stored_contact_list[] = $form_data;
    }

    echo json_encode($stored_contact_list);
    die();
}

function dbcf7_contact_list($form_id){
    global $wpdb;
    $table_name = $wpdb->prefix . 'db7_forms';
    return $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $form_id", OBJECT );
}

add_shortcode('stored_contact_list', 'function_stored_contact_list');
function function_stored_contact_list(){
    $contact_list = dbcf7_contact_list(6);
    ob_start();
    ?>
    <div class="contact-list-table">
        <h2>The List</h2>
        <table>
            <thead>
            <tr>
                <td>#</td>
                <td>Name</td>
                <td>Email</td>
                <td>Message</td>
                <td>Date</td>
            </tr>
            </thead>
            <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($contact_list as $item): ?>
            <tr>
                <td><?php echo $counter ?></td>
                <td>Name</td>
                <td>Email</td>
                <td>Message</td>
                <td>Date</td>
            </tr>
            <?php $counter++; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
    return ob_get_clean();
}
