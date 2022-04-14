<?php

if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array(
    'key' => 'group_600e6aed4862a',
    'title' => __('Apply event (page)', 'keima-event-manager'),
    'fields' => array(
      array(
        'key' => 'field_600e6afe74534',
        'label' => __('Applicable events', 'keima-event-manager'),
        'name' => 'kem_allowed_event',
        'type' => 'post_object',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'post_type' => array(
          0 => 'keima_event_manager',
        ),
        'taxonomy' => '',
        'allow_null' => 0,
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page',
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