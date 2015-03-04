<?php
/*
Plugin Name: Openname
Plugin URI: http://wordpress.org/extend/plugins/openname/
Description: Enables <a href="https://openname.org">Openname</a> support for your blog.
Version: 1.0
Author: Larry Salibra
Author URI: https://www.larrysalibra.com/
*/





if (!class_exists("Openname")) {
  class Openname {
    private $openname_endpoint;
    private $json;
    private $loaded = false;
    public static $default_endpoint = 'https://onename.com/';

    function __construct($openname) {
      $this->$openname = $openname;
      $this->openname_endpoint = get_option('openname_endpoint');
      $url = $this->openname_endpoint.$openname.".json";

      $results = wp_remote_get( $url, array( 'timeout' => -1 ) );
      // Checking for WP Errors
      if ( is_wp_error( $results ) || wp_remote_retrieve_response_code($results) != 200) {
        throw new Exception("There was an error loading the Openname: '".$openname."'");
      }
      $this->json = json_decode( $results['body'], true);
      if($this->json["v"] != "0.2") {
        error_log(print_r("The Openname plugin only current supports Openname schema v0.2.",false));
      }
      $this->loaded = true;

    }

    function openname() {
      return $this->$openname;
    }


    function angellist_username() {
      return sanitize_text_field($this->json["angelist"]["username"]);
    }

    function avatar_url() {
      return sanitize_text_field($this->json["avatar"]["url"]);
    }

    function bio() {
      return sanitize_text_field($this->json["bio"]);
    }

    function bitcoin_address() {
      return sanitize_text_field($this->json["bitcoin"]["address"]);
    }

    function cover_url() {
      return sanitize_text_field($this->json["cover"]["url"]);
    }

    function facebook_username() {
      return sanitize_text_field($this->json["facebook"]["username"]);
    }

    function github_username() {
      return sanitize_text_field($this->json["github"]["username"]);
    }

    function instagram_username() {
      return sanitize_text_field($this->json["instagram"]["username"]);
    }

    function linkedin_url() {
      return sanitize_text_field($this->json["linkedin"]["url"]);
    }

    function location_formatted() {
      return sanitize_text_field($this->json["location"]["formatted"]);
    }

    function name_formatted() {
      return sanitize_text_field($this->json["name"]["formatted"]);
    }

    function pgp_fingerprint() {
      return sanitize_text_field($this->json["pgp"]["fingerprint"]);
    }

    function pgp_url() {
      return sanitize_text_field($this->json["pgp"]["url"]);
    }

    function twitter_username() {
      return sanitize_text_field($this->json["twitter"]["username"]);
    }

    function website() {
      return sanitize_text_field($this->json["website"]);
    }

    function schema_version() {
      return sanitize_text_field($this->json["v"]);
    }
  }
}




function activate_openname() {
  add_option('openname_endpoint', Openname::$default_endpoint);
}

function deactive_openname() {
  delete_option('openname_endpoint');
}

function admin_init_openname() {
  register_setting('openname', 'openname_endpoint', 'openname_endpoint_validate');
}

function admin_menu_openname() {
  add_options_page('Openname', 'Openname', 'manage_options', 'openname', 'options_page_openname');
}

function options_page_openname() {
  include(WP_PLUGIN_DIR.'/openname/options.php');
}


register_activation_hook(__FILE__, 'activate_openname');
register_deactivation_hook(__FILE__, 'deactive_openname');

if (is_admin()) {
  add_action('admin_init', 'admin_init_openname');
  add_action('admin_menu', 'admin_menu_openname');
}


function openname_endpoint_validate($input) {
  $newinput = esc_url_raw($input);
  $newinput = empty($newinput) ? Openname::$default_endpoint : $newinput;
  return $newinput;
}


/**
 * Adds the Openname section in the user profile page.
 *
 * @param object $profileuser Contains the details of the current profile user
 *
 * @return string $html Openname section in the user profile page
 */
function openname_add_extra_profile_fields( $profileuser ) {

	// Getting the usermeta
	$openname_handle = get_user_meta( $profileuser->ID, 'openname_handle', true );
  $openname_avatar_enabled = get_user_meta( $profileuser->ID, 'openname_avatar_enabled', true );

	// Openname section html in the user profile page.
	$html  = '';
	$html .= '<h3>Openname</h3>';
	$html .= '<table class="form-table">';
	$html .= '<tr><th><label for="openname_handle">Your Openname</label></th>';
	$html .= '<td><input type="text" name="openname_handle" id="openname_handle" value="' . $openname_handle . '" class="regular-text" required pattern="[a-z0-9_]{1,60}" /></td>';
	$html .= '<tr><th><label for="openname_avatar_enabled">Use Openname Avatar as Avatar</label></th>';
	$html .= '<td><input id="openname_avatar_enabled" type="checkbox" name="openname_avatar_enabled" value="openname_avatar_enabled" ' . checked( $openname_avatar_enabled, TRUE, false ) . '></td></tr>';
	$html .= '</table>';

	echo $html;
}
add_action( 'show_user_profile', 'openname_add_extra_profile_fields' );
add_action( 'edit_user_profile', 'openname_add_extra_profile_fields' );

/**
 * Save Openname details in the wp usermeta table.
 *
 * @param int $user_id id of the current user.
 *
 * @return void
 */
function openname_save_extra_profile_fields( $user_id ) {
  $safe_openname_handle =  sanitize_text_field($_POST['openname_handle']);
  $safe_openname_avatar_enabled = $_POST['openname_avatar_enabled'] == null ? FALSE : TRUE;

  if(preg_match("/^[a-z0-9_]{1,60}$/", $safe_openname_handle)) {
    update_usermeta( $user_id, 'openname_handle', $safe_openname_handle );
    update_usermeta( $user_id, 'openname_avatar_enabled', $safe_openname_avatar_enabled);
    delete_transient( "openname_avatar_url_{$user_id}" );
  }
}
add_action( 'personal_options_update', 'openname_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'openname_save_extra_profile_fields' );



/**
* Replaces the default avatar with Openname avatar
*
* @param string $avatar The default avatar
*
* @param int $id_or_email The user id
*
* @param int $size The size of the avatar
*
* @param string $default The url of the Wordpress default avatar
*
* @param string $alt Alternate text for the avatar.
*
* @return string $avatar The modified avatar
*/
function openname_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

  // Getting the user id.
  if ( is_int( $id_or_email ) )
  $user_id = $id_or_email;

  if ( is_object( $id_or_email ) )
  $user_id = $id_or_email->user_id;

  if ( is_string( $id_or_email ) ) {
    $user = get_user_by( 'email', $id_or_email );
    if ( $user )
    $user_id = $user->ID;
    else
    $user_id = $id_or_email;
  }



  // Getting the user details
  $openname_avatar_enabled    = get_user_meta( $user_id, 'openname_avatar_enabled', true );
  $openname_handle     = get_user_meta( $user_id, 'openname_handle', true );
  if ( "1" == $openname_avatar_enabled && ! empty( $openname_handle ) ) {
    if ( false === ( $openname_avatar_url = get_transient( "openname_avatar_url_{$user_id}" ) ) ) {

      try {
        $openname = new Openname($openname_handle);
      } catch(Exception $e) {
        error_log(print_r($e->getMessage(),true));
        add_action( 'admin_notices', 'openname_cant_be_loaded' );
        return $avatar;
      }

      $openname_avatar_url = $openname->avatar_url();

      // Setting Gplus url for 48 Hours
      set_transient( "openname_avatar_url_{$user_id}", $openname_avatar_url, 1 * HOUR_IN_SECONDS );



      $avatar = "<img alt='+{$openname_handle}'s avatar'' src='{$openname_avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

    } else {
      $avatar = "<img alt='+{$openname_handle}'s avatar'' src='{$openname_avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    }
    return $avatar;
  } else {
    return $avatar;
  }
}

add_filter( 'get_avatar', 'openname_avatar', 10, 5 );

function openname_cant_be_loaded() {
  echo "<div class=\"error\"><p>The Openname you've entered either doesn't exist or there's a problem with the endpoint.</p></div>";
}

?>
