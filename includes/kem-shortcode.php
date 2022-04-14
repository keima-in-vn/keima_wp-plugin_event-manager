<?php

function kem_login_form_html($atts) {
  extract( shortcode_atts( array(
    'event_slug' => null,
    'mail' => null,
  ), $atts) );

  if ( is_null($event_slug) ) {
    return __('Please specify "event_slug" as follows.<br>[kem_login_form event_slug="slug_name"]', 'keima-event-manager');
  }

  $event_obj = get_page_by_path( $event_slug, 'OBJECT', 'keima_event_manager' );
  if ( ! $event_login_type = get_field('kem_login_type', $event_obj) ) {
    return __('Please check the entered "event_slug".', 'keima-event-manager');
  }
  $event_login_type_value = $event_login_type['value'];
  if ( $event_login_type_value === 'pw' && is_null($mail) ) {
    return __('Please specify "mail" as below.<br>[kem_login_form mail="user@domain.com"]', 'keima-event-manager');
  }

  $error_code = false;
  $error_message = '';
  if ( isset($_SESSION['kem_login_error']) ) {
    $error_code = $_SESSION['kem_login_error'];
    unset($_SESSION['kem_login_error']);

    if ( $error_code === 'code01' ) {
      $error_message = '<p class="form-error">' . __('An unexpected error has occurred. Please reload the page and try accessing it again.', 'keima-event-manager') . '</p>';
    } else if ( $error_code === 'code02' ) {
      $error_message = '<p class="form-error">' . __('Invalid access. Please reload the page and try accessing it again.', 'keima-event-manager') . '</p>';
    } else if ( $error_code === 'code03'  || $error_code === 'code04' ) {
      if ( $event_login_type_value === 'email_pw' ) {
        $error_message = '<p class="form-error">' . __('The email address or password is incorrect.', 'keima-event-manager') . '</p>';
      } else if ( $event_login_type_value === 'id_pw' ) {
        $error_message = '<p class="form-error">' . __('The ID or password is incorrect.', 'keima-event-manager') . '</p>';
      } else {
        $error_message = '<p class="form-error">' . __('The password is incorrect.', 'keima-event-manager') . '</p>';
      }
    } else if ( $error_code === 'code05' ) {
      $error_message = '<p class="form-error">' . __('The account you entered does not have permission.', 'keima-event-manager') . '</p>';
    }
  }

  $posted_id = isset($_POST['kem_id']) ? $_POST['kem_id'] : '';
  $posted_password = isset($_POST['kem_password']) ? $_POST['kem_password'] : '';

  $login_page_url = get_page_link( get_the_ID() );

  $html = '<div class="kem-login-form is__login-type--' . $event_login_type_value . '"><form method="POST" action="' . $login_page_url . '">';

  $html .= '<dl class="__field">';

  if ( $event_login_type_value === 'mail_pw' ) {
    $html .= '<dt>' . __('Mail', 'keima-event-manager') . '</dt><dd><div class="__input-form"><input type="email" name="kem_id" required class="js__login-form-id" value="' . $posted_id . '"></div></dd>';
  } else if ( $event_login_type_value === 'id_pw' ) {
    $html .= '<dt>' . __('ID', 'keima-event-manager') . '</dt><dd><div class="__input-form"><input type="text" name="kem_id" required class="js__login-form-id" value="' . $posted_id . '"></div></dd>';
  } else if ( $event_login_type_value === 'pw' ) {
    $html .= '<input type="hidden" name="kem_id" required class="js__login-form-id" value="' . $mail . '">';
  }

  $html .= '<dt>' . __('Password', 'keima-event-manager') . '</dt><dd><div class="__input-form"><input type="password" name="kem_password" required class="js__login-form-pass" value="' . $posted_password . '"></div></dd>';
  $html .= '</dl>';
  if ( $error_code ) {
    $html .= '<div class="__error">' . $error_message . '</div>';
  }
  $html .= '<div class="__button">';
  $html .= '<button type="submit"><span>' . __('Login', 'keima-event-manager') . '</span></button>';
  $html .= '</div>';
  $html .= '<input type="hidden" name="kem_event_slug" value="' . $event_slug . '">';
  $html .= '</form></div>';

  $html .= '<style>';
  $html .= '.kem-login-form * { box-sizing: border-box; }';
  $html .= '.kem-login-form dl.__field { display: flex; flex-wrap: wrap; align-items: center; }';
  $html .= '.kem-login-form dl.__field dt, .kem-login-form dl.__field dd { margin: 0 0 10px; padding: 0; }';
  $html .= '.kem-login-form dl.__field dd input { width: 100%; margin: 0; }';
  $html .= '.kem-login-form .__button { text-align: center; }';
  $html .= '.kem-login-form .__button button { width: 100%; }';
  $html .= '.kem-login-form button, .kem-login-form input[type="text"], .kem-login-form input[type="email"], .kem-login-form input[type="password"] { padding: .5em .75em; }';
  $html .= '@media screen and (min-width: 768px) {';
    if ( $event_login_type_value === 'mail_pw' ) {
      $html .= '.kem-login-form dl.__field dt { width: 8em; }';
      $html .= '.kem-login-form dl.__field dd { width: calc(100% - 8em); }';
      $html .= '.kem-login-form .__button { padding-left: 8em; }';
      $html .= '.kem-login-form .__button { padding-right: 8em; }';
    } else {
      $html .= '.kem-login-form dl.__field dt { width: 6em; }';
      $html .= '.kem-login-form dl.__field dd { width: calc(100% - 6em); }';
      $html .= '.kem-login-form .__button { padding-left: 6em; }';
      $html .= '.kem-login-form .__button { padding-right: 6em; }';
    }
  $html .= '}';
  $html .= '@media screen and (max-width: 767px) {';
    $html .= '.kem-login-form dl.__field dt { width: 100%; }';
    $html .= '.kem-login-form dl.__field dd { width: 100%; }';
  $html .= '}';
  $html .= '</style>';

  return $html;
}
add_shortcode('kem_login_form', 'kem_login_form_html');
