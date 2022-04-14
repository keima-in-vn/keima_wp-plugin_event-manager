<?php

if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array(
    'key' => 'group_600e69119eda1',
    'title' => __('Manage event', 'keima-event-manager'),
    'fields' => array(
      array(
        'key' => 'field_600e823c73969',
        'label' => __('Login page', 'keima-event-manager'),
        'name' => 'kem_login_page',
        'type' => 'post_object',
        'instructions' => '',
        'required' => 1,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'post_type' => array(
          0 => 'page',
        ),
        'taxonomy' => array(
        ),
        'allow_null' => 0,
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
      ),
      array(
        'key' => 'field_618110ea77914',
        'label' => __('Logout page', 'keima-event-manager'),
        'name' => 'kem_logout_page',
        'type' => 'post_object',
        'instructions' => '',
        'required' => 1,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'post_type' => array(
          0 => 'page',
        ),
        'taxonomy' => array(
        ),
        'allow_null' => 0,
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
      ),
      array(
        'key' => 'field_600e691acffb0',
        'label' => __('Top page after login', 'keima-event-manager'),
        'name' => 'kem_event_top_page',
        'type' => 'post_object',
        'instructions' => '',
        'required' => 1,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'post_type' => array(
          0 => 'page',
        ),
        'taxonomy' => '',
        'allow_null' => 0,
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
      ),
      array(
        'key' => 'field_6181113c77915',
        'label' => __('Login type', 'keima-event-manager'),
        'name' => 'kem_login_type',
        'type' => 'radio',
        'instructions' => '',
        'required' => 1,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'choices' => array(
          'mail_pw' => __('Mail and Password', 'keima-event-manager'),
          'id_pw' => __('ID and Password', 'keima-event-manager'),
          'pw' => __('Only Password', 'keima-event-manager'),
        ),
        'allow_null' => 0,
        'other_choice' => 0,
        'default_value' => 'mail_pw',
        'layout' => 'horizontal',
        'return_format' => 'array',
        'save_other_choice' => 0,
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'keima_event_manager',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
  ));

endif;