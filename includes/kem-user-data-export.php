<?php

function kem_dummy_function() {}

add_action('admin_menu', function () {
  // tools.php は不要。
  add_submenu_page( 'edit.php?post_type=keima_event_manager', __('User data export', 'keima-event-manager'), __('User data export', 'keima-event-manager'), 'manage_options', 'kem_user_data_export','kem_dummy_function');
});

function kem_user_data_export() {

  // ID, タイトルと拡張フィールド
  $fields = array('wp_user_id', 'user_login_id', 'user_password', 'user_email', 'user_last_name', 'user_first_name', 'user_display_name', 'user_role', 'user_description', 'user_allowed_event');
  $fopen = fopen('php://temp','r+');
  $users = get_users(array(
    'orderby' => 'ID',
    'order' => 'ASC',
  ));

  fputcsv($fopen, $fields, ',', '"');

  foreach($users as $user) {
    $user_id = $user->ID;
    $user_login = $user->user_login;
    $user_meta = get_userdata($user_id);
    $user_pass = '';
    $user_mail = $user->user_email;
    $user_last_name = $user->last_name;
    $user_first_name = $user->first_name;
    $user_display_name = $user->display_name;
    $user_role = $user_meta->roles;
    $user_role = kem_array_to_text($user_role);
    $user_description = $user_meta->description;
    $user_allowed_events = get_field('kem_allowed_event', 'user_' . $user_id);

    $user_allowed_events_text = '';
    if ( is_array($user_allowed_events) ) {
      foreach ( $user_allowed_events as $event ) {
        if ( is_object($event) ) {
          $slug = get_post_field( 'post_name', $event->ID );
          $user_allowed_events_text .= $slug . ',';
        } else {
          $slug = get_post_field( 'post_name', $event );
          $user_allowed_events_text .= $slug . ',';
        }
      }
    }
    $user_allowed_events_text = rtrim($user_allowed_events_text, ',');

    $data = array(
      $user_id,
      $user_login,
      $user_pass,
      $user_mail,
      $user_last_name,
      $user_first_name,
      $user_display_name,
      $user_role,
      $user_description,
      $user_allowed_events_text,
    );
    fputcsv($fopen, $data, ',', '"');
  }
  wp_reset_postdata();

  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=export.csv');
  rewind($fopen);

  while (($buf = fgets($fopen)) !== false) :
    echo mb_convert_encoding($buf, 'UTF-8', mb_internal_encoding());
  endwhile;

  fclose($fopen);
}

function kem_array_to_text ( $array ) {
  $text = '';
  foreach ( $array as $value ) {
    $text .= $value . ',';
  }
  $text = rtrim($text, ',');
  return $text;
}

add_action('admin_init', function () {
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($page === 'kem_user_data_export') :
    kem_user_data_export();
    exit();
  endif;
});