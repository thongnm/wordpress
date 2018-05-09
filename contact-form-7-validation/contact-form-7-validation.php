<?php
/*
Plugin Name: Contact Form 7 Custom Validation
Description: Contact Form 7 Custom Validation. 
*/
session_start();
define('POSTED_FORM_ID', 2676);
define('id_team_name', 'id:team_name');
define('id_contact_email', 'id:contact_email');
define('id_member1_email', 'id:member1_email');
define('id_member2_email', 'id:member2_email');
define('id_member3_email', 'id:member3_email');
define('id_member4_email', 'id:member4_email');
define('id_member5_email', 'id:member5_email');
define('id_member6_email', 'id:member6_email');
define('msg_email_registered', 'This email has already registered.');
define('msg_duplidate_member_email', 'Member email could not be duplicated.');
define('msg_team_name_registered', 'This team name has already registered. Please choose another name');


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
    add_filter('wpcf7_validate_email', array($this, 'custom_email_confirmation_validation_filter'), 20, 2);
    add_filter('wpcf7_validate_text', array($this, 'custom_text_confirmation_validation_filter'), 20, 2);
  }
  function custom_email_confirmation_validation_filter( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );
    $tag_name = $tag->name;
    // Validate duplicate member email
    if($tag_name != id_contact_email) {
      // All posted member emails
      $email_members = array(
        trim($_POST[id_member1_email]),
        trim($_POST[id_member2_email]),
        trim($_POST[id_member3_email]),
        trim($_POST[id_member4_email]),
        trim($_POST[id_member5_email]),
        trim($_POST[id_member6_email])
      );
      $counts = array_count_values($email_members);
      $current_email = trim($_POST[$tag->name]);

      if ($current_email != '' && $counts[$current_email] > 1) {
        $result->invalidate( $tag, msg_duplidate_member_email);
        return $result;
      }
    }
    $results = $this->get_current_form_data();
    $err_msg = '';

    foreach ($results as $form) {
        $form_data  = unserialize( $form->form_value );
        // Validate emails existed
        if ($this->invalid_email($form_data, $tag_name )) {
          $err_msg = msg_email_registered;
          break;
        }
    }

    if ($err_msg != '') {
      $result->invalidate( $tag, $err_msg);
    }
    
    return $result;
  }
  function invalid_email($form_data, $id_email) {
    $current_email = trim($_POST[$id_email]);
    $email_members_db = array(
      trim($form_data[id_contact_email]),
      trim($form_data[id_member1_email]),
      trim($form_data[id_member2_email]),
      trim($form_data[id_member3_email]),
      trim($form_data[id_member4_email]),
      trim($form_data[id_member5_email]),
      trim($form_data[id_member6_email])
    );
    
    return $current_email != '' && in_array($current_email, $email_members_db);
  }
  function custom_text_confirmation_validation_filter( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );
    $results = $this->get_current_form_data();
    
    foreach ($results as $form) {
        $form_data  = unserialize( $form->form_value );
        // Validate team name existed
        if($tag->name == id_team_name) {
          $team_name= $form_data[id_team_name];
          if (trim($team_name) == trim($_POST[id_team_name])) {
            $result->invalidate( $tag, msg_team_name_registered);
            break;
          }
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