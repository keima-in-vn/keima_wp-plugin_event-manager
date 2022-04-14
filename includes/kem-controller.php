<?php

function kem_process_login() {

  if ( is_admin() ) {
    return;
  }
  if ( isset($_GET['et_fb']) ) {
    return;
  }
  if ( class_exists('Elementor') && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
    return;
  }

  $access_page_event = get_field('kem_allowed_event');
  if ( ! $access_page_event ) {
    return;
  }

  if( session_status() !== PHP_SESSION_ACTIVE ) {
    session_start();
  }

  $posted_id = isset($_POST['kem_id']) ? _h($_POST['kem_id']) : null;
  $posted_pass = isset($_POST['kem_password']) ? _h($_POST['kem_password']) : null;
  $posted_event_slug = isset($_POST['kem_event_slug']) ? _h($_POST['kem_event_slug']) : null;

  $current_page_id = get_the_ID();
  $event_logout_page = get_field('kem_logout_page', $access_page_event->ID);
  $event_login_page = get_field('kem_login_page', $access_page_event->ID);
  $event_login_page_url = get_permalink( $event_login_page->ID );
  $event_top_page = get_field('kem_event_top_page', $access_page_event->ID);
  $event_top_page_url = get_permalink( $event_top_page->ID );
  $event_login_type = get_field('kem_login_type', $access_page_event->ID);

  $session_event_slugs = isset($_SESSION['kem_allowed_event_slugs']) ? $_SESSION['kem_allowed_event_slugs'] : null;

  $user_search_key = 'email';
  if ( isset($event_login_type['value']) && $event_login_type['value'] === 'id_pw' ) {
    $user_search_key = 'login';
  }
  $_SESSION['debug'] = $event_login_type;
  $user_allowed_event_slugs = array();
  if ( ! is_null($posted_id) && $posted_user_obj = get_user_by($user_search_key, $posted_id) ) {
    $user_allowed_events = get_field('kem_allowed_event', 'user_' . $posted_user_obj->ID);
    if ( $user_allowed_events ) {
      foreach ($user_allowed_events as $_event_id) {
        $_slug = get_post_field('post_name', $_event_id);
        array_push($user_allowed_event_slugs, $_slug);
      }
    }
  }

  $is_logout_page = $current_page_id === $event_logout_page->ID;
  $is_login_page = $current_page_id === $event_login_page->ID;

  if ( $is_logout_page ) {

    if ( isset($_SESSION['kem_allowed_event_slugs']) ) {
      unset($_SESSION['kem_allowed_event_slugs']);
    }
    if ( isset($_SESSION['kem_posted_event_slug']) ) {
      unset($_SESSION['kem_posted_event_slug']);
    }
    wp_logout();
    wp_redirect( $event_login_page_url );
    exit;

  } else if ( $is_login_page ) {

    if ( empty($_POST) ) {
      // No data submitted.
      return;
    } else if ( is_null($posted_id) || is_null($posted_pass) ) {
      // No email or No password is posted.
      $_SESSION['kem_login_error'] = 'code03'; return; // code03 = not match id
    } else if ( ! $posted_user_obj ) {
      // Posted email user is not existing.
      $_SESSION['kem_login_error'] = 'code03'; return; // code03 = not match id
    } else if ( ! wp_check_password( $posted_pass, $posted_user_obj->user_pass, $posted_user_obj->ID ) ) {
      // Posted password is not match.
      $_SESSION['kem_login_error'] = 'code04'; return; // code04 = not match password
    } else if ( ! $posted_event_obj = get_page_by_path( $posted_event_slug, 'OBJECT', 'keima_event_manager' ) ) {
      // Posted event_slug is not existing.
      $_SESSION['kem_login_error'] = 'code02'; return; // code02 = not match event
    } else if ( ! $posted_event_top_page = get_field('kem_event_top_page', $posted_event_obj) ) {
      // Event top page of posted event_slug is not existing.
      $_SESSION['kem_login_error'] = 'code02'; return; // code02 = not match event
    } else if ( ! in_array($posted_event_slug, $user_allowed_event_slugs) ) {
        // Posted email user doesn't have permission to access posted event_slug.
        $_SESSION['kem_login_error'] = 'code05'; return; // code05 = not match event
    } else {

      $_SESSION['kem_allowed_event_slugs'] = $user_allowed_event_slugs;
      $_SESSION['kem_posted_event_slug'] = $posted_event_slug;

      $creds = array();
      $creds['user_login'] = $posted_user_obj->data->user_login;
      $creds['user_password'] = $posted_pass;
      $creds['remember'] = true;
      $signon_user = wp_signon($creds, false);

      if ( is_wp_error($signon_user) ) {
        echo $signon_user->get_error_message();
      } else {
        wp_clear_auth_cookie();
        do_action('wp_login', $signon_user->ID);
        wp_set_current_user($signon_user->ID);
        wp_set_auth_cookie($signon_user->ID, true);
        wp_safe_redirect($event_top_page_url);
        exit;
      }
    }

  } else {

    if (
      // If user doesn't have session value, Redirect to login page.
      is_null($session_event_slugs)

      // Or session value is not same event of the event of target page, Redirect to login page.
      || ! in_array($access_page_event->post_name, $session_event_slugs)
    ) {
      wp_redirect( $event_login_page_url );
      exit;
    }

  }
}
add_action( 'get_header', 'kem_process_login', 1 );

function _h($s) {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}
