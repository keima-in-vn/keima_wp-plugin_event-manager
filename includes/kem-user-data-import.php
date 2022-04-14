<?php

add_action('admin_menu', function (){
  if ( current_user_can('administrator','editor') ) {
    add_submenu_page('edit.php?post_type=keima_event_manager', __('User data import', 'keima-event-manager'), __('User data import', 'keima-event-manager'),'manage_options', 'kem_user_data_import', 'kem_user_data_import_page');
  }
});

function kem_user_data_import_page () {
  ?>
  <div class="wrap">
    <h1><?php _e('User data import with apply event', 'keima-event-manager') ?></h1>

    <div class="kem-uploader-content">
      <h2><?php _e('CSV file selection', 'keima-event-manager') ?></h2>

      <p>
        <input type="file" name="kem_user_csv_data" accept="text/csv">
        <button type="button" id="kem_user_csv_data_submit_button" class="button" disabled><?php _e('Import user data', 'keima-event-manager') ?></button>
      </p>
      <p class="description">
        <?php _e('* Please use UTF-8 as the character code.', 'keima-event-manager') ?><br>
        <?php _e('* If it is a CSV file downloaded from Google Spread Sheet, the character code is UTF-8.', 'keima-event-manager') ?><br>
        <?php _e('* Below is the base Google Spread Sheet file.', 'keima-event-manager') ?><br>
        <a href="https://docs.google.com/spreadsheets/d/12Z6x9LvpH-InMv9AZDLI1QfGHKoj-vlW78zUOtCxq6Y/copy" target="_blank">https://docs.google.com/spreadsheets/d/12Z6x9LvpH-InMv9AZDLI1QfGHKoj-vlW78zUOtCxq6Y/copy</a>
      </p>

      <div class="message" id="kem_user_data_message">
        <?php _e('Select the file and click the "Import User Data" button.', 'keima-event-manager') ?>
      </div>

    </div>
  </div>
  <style>
    .kem-uploader-content h3 {
      padding-top: .5em;
      border-top: 1px solid #ccc;
      font-size: 1.0em;
    }
    .kem-uploader-content * + .message {
      margin-top: 2em;
    }
    .kem-uploader-content .message {
      padding: 1.5em;
      border: 3px solid #fff;
    }
    .kem-uploader-content .message h2 {
      margin-top: 0;
    }
  </style>
  <?php
}

add_action('admin_print_scripts', function () {
  global $plugin_page;
  if ( is_admin() || ($plugin_page == 'kem_user_data_import') ) :
    ?>
    <script type="text/javascript">

      window.addEventListener('load', function () {
        var $inputFile = jQuery('input[type="file"][name="kem_user_csv_data"]');
        var $submitButton = jQuery('#kem_user_csv_data_submit_button');
        var $messageArea = jQuery('#kem_user_data_message');

        $inputFile.on('change', function (e) {
          var file = jQuery(this).prop('files')[0];
          if ( file ) {
            if ( file.type.indexOf('csv') ) {
              $submitButton.prop('disabled', false);
            } else {
              $submitButton.prop('disabled', true);
            }
          }
        });
        $submitButton.on('click', function (e) {
          e.preventDefault();
          $messageArea.html('<?php _e('...processing...', 'keima-event-manager') ?>');

          var formData = new FormData;
          var individual_file = $inputFile.prop('files')[0];
          formData.append('file', individual_file);
          formData.append('action', 'kem_user_data_import');

          jQuery.ajax({
            type: 'POST',
            url: '<?php echo admin_url(); ?>admin-ajax.php',
            processData: false,
            contentType : false,
            data: formData,
          }).done(function( data, textStatus ) {
            console.log('done');
            $messageArea.html(data);
            $submitButton.prop('disabled', true);
            $inputFile.val('');
          }).fail(function( xhr, textStatus, errorThrown ) {
            console.log('fail');
            $messageArea.html(textStatus);
          });
        });
      });
    </script>
  <?php
  endif;
});


function kem_user_data_import_ajax () {
  if( empty($_FILES) ) {
    echo 'error: no file';
    return;
  }
  $temp_file = $_FILES['file']['tmp_name'];
  $file_handler = fopen($temp_file, 'r');

  // These variables will be used for report.
  $new_users = '';
  $updated_users = '';
  $error_users = '';
  $count_total = 0;
  $count_new = 0;
  $count_update = 0;
  $count_error = 0;

  while( $row = fgetcsv($file_handler) ) {
    $login_id = $row[1];
    $password = $row[2];
    $email = $row[3];
    $last_name = $row[4];
    $first_name = $row[5];
    $display_name = $row[6];
    $role = $row[7];
    $description = $row[8];
    $event_slugs = $row[9];

    $email = preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $email);

    if ( ! preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $email) ) {
      if ( $count_total !== 0 ) {
        $error_users .= $email . '<br>';
        $count_error++;
      }
      continue;
    }
    $count_total++;

    $login_id = $login_id !== '' ? $login_id : $email;
    $role = $role !== '' ? $role : 'subscriber';

    $import_event_ids = array();
    if ( $event_slugs !== '' ) {
      $event_slugs_array = explode(',', $event_slugs);
      foreach ($event_slugs_array as $_slug) {
        $_slug = trim($_slug);
        if ( $_event_obj = get_page_by_path($_slug, 'OBJECT', 'keima_event_manager') ) {
          array_push($import_event_ids, $_event_obj->ID );
        }
      }
    }

    $insert_data = array(
      'user_login' => $login_id,
      // If including user_pass, it makes the system send mail to update password.
      //'user_pass' => $password,
      //'user_nicename' => ,
      'user_email' => $email,
      'display_name' => $display_name,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'role' => $role,
      //'nickname' => ,
      'description' => $description,
    );

    if ( $exists_user_id = email_exists( $email ) ) {
      $existing_event_ids = get_field('kem_allowed_event', 'user_' . $exists_user_id);

      unset($insert_data['user_login']);
      $insert_data['ID'] = $exists_user_id;
      $updated_user_id = wp_update_user( $insert_data );

      if ( is_wp_error( $updated_user_id ) ) {
        $error_users .= $email . '<br>';
        $count_error++;
        continue;
      }
      if ( $password !== '' ) {
        wp_set_password($password, $exists_user_id);
      }

      // Crete array from object.
      $temp = array();
      if (
        is_array($existing_event_ids)
        && is_object($existing_event_ids[0])
      ) {
        foreach ( $existing_event_ids as $obj ) {
          array_push( $temp, $obj->ID );
        }
        $existing_event_ids = $temp;
      }
      if ( $event_slugs === 'null' ) {
        update_user_meta($exists_user_id, 'kem_allowed_event', array());
      } else if ( ! empty($import_event_ids) ) {
        if ( is_array($existing_event_ids) ) {
          $import_event_ids = array_merge($existing_event_ids, $import_event_ids);
          $import_event_ids = array_unique($import_event_ids);
          $import_event_ids = array_diff($import_event_ids,array(''));

        }
        update_user_meta($exists_user_id, 'kem_allowed_event', $import_event_ids);
      }

      $updated_users .= 'ID: ' . $exists_user_id . ' / email: ' . $email . '<br>';
      $count_update++;

    } else {

      $new_user_id = wp_insert_user( $insert_data );

      if ( is_wp_error( $new_user_id ) ) {
        $error_users .= $email . '<br>';
        $count_error++;
        continue;
      }

      wp_set_password($password, $new_user_id);
      if ( ! empty($import_event_ids) ) {
        update_user_meta($new_user_id, 'kem_allowed_event', $import_event_ids);
      }

      $new_users .= 'ID: ' . $new_user_id . ' / email: ' . $email . '<br>';
      $count_new++;

    }
  }

  $return = '<h2>' . __('Import process result', 'keima-event-manager') . '</h2>';

  $return .= '<h3>' . __('Newly added users', 'keima-event-manager') . '</h3>';
  if ( $new_users === '' ) {
    $return .= '<p>' . __('None', 'keima-event-manager') . '</p>';
  } else {
    $return .= '<p>' . $new_users . '</p>';
  }
  $return .= '<h3>' . __('Updated users', 'keima-event-manager') . '</h3>';
  if ( $updated_users === '' ) {
    $return .= '<p>' . __('None', 'keima-event-manager') . '</p>';
  } else {
    $return .= '<p>' . $updated_users . '</p>';
  }
  $return .= '<h3>' . __('Import failed users', 'keima-event-manager') . '</h3>';
  if ( $error_users === '' ) {
    $return .= '<p>' . __('None', 'keima-event-manager') . '</p>';
  } else {
    $return .= '<p>' . $error_users . '</p>';
  }
  $return .= '<h3>' . __('Summary', 'keima-event-manager') . '</h3>';
  $return
    .= '<p>'
    . __('Number of newly added users:', 'keima-event-manager') . $count_new . '<br>'
    . __('Number of updated users:', 'keima-event-manager') . $count_update . '<br>'
    . __('Number of error users:', 'keima-event-manager') . $count_error . '<br>'
    . __('Total number of lines:', 'keima-event-manager') . $count_total . '</p>';

  echo $return;
  die();
}
add_action( 'wp_ajax_kem_user_data_import', 'kem_user_data_import_ajax' );