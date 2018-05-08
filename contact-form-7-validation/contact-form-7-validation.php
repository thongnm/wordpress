<?php
/*
Plugin Name: Contact Form 7 Custom Validation
Description: Contact Form 7 Custom Validation. 
*/
session_start();
define('POSTED_FORM_ID', 2676);
Class ContactForm7Validation { 
  protected static $instance = NULL;

  public static function get_instance() {
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}
  function __construct() {
		add_action('init', array($this, 'init'));
  }
  function init() {
    add_filter('wpcf7_validate_email*', array($this, 'custom_email_confirmation_validation_filter'), 20, 2);
    add_filter('wpcf7_validate_text*', array($this, 'custom_text_confirmation_validation_filter'), 20, 2);
  }
  function custom_email_confirmation_validation_filter( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );
    $results = $this->get_current_form_data();
    
    foreach ($results as $form) {
        $form_data  = unserialize( $form->form_value );
        $email = $form_data['id:contact_email'];
        // Validate contact email existed
        if (trim($email) == trim($_POST['id:contact_email'])) {
          $result->invalidate( $tag, "This email has already registered.");
          break;
        }
    }
    
    return $result;
  }
  function custom_text_confirmation_validation_filter( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );
    $results = $this->get_current_form_data();
    
    foreach ($results as $form) {
        $form_data  = unserialize( $form->form_value );
        $team_name= $form_data['id:team_name'];
        // Validate team name existed
        if (trim($team_name) == trim($_POST['id:team_name'])) {
          $result->invalidate( $tag, "This Team name has already registered. Please choose another name");
          break;
        }
    }
    
    return $result;
  }
  function get_current_form_data() {
    global $wpdb;
    $table_name = $wpdb->prefix.'db7_forms';
    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = ". POSTED_FORM_ID, OBJECT );
    return $results;
  }
}
ContactForm7Validation::get_instance();
?>