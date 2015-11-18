<?php
/**
 * @package Website Toolbox Forum
 * @author Website Toolbox
 */
/*
Plugin Name: Website Toolbox Forum
Description: Integrates single sign on and embeds your forum into your WordPress website.
Version: 1.3.0
Author: Team Website Toolbox | <a href="options-general.php?page=websitetoolboxoptions">Settings</a>
Purpose: Integrates your forum with your WordPress website
*/
set_time_limit(0);
ob_start();

// start new session if not started from another files.
if(!isset($_SESSION)) {
  session_start();
}

/* Purpose: insert forum title, default page content, plug-in status, in the option table.
Parameter: None
Return: None */
function create_websitetoolbox_page() {
    $my_post = array();
    $page_named_forum = get_page_by_title('Forum');
    $title = "Forum";
    if($page_named_forum) $title = "Forum";
    $my_post['post_title'] = $title;
    $my_post['post_content'] = "Please go to the admin section and change your Website Toolbox forum settings.";
    $my_post['post_status'] = 'publish';
    $my_post['post_author'] = 1;
    $my_post['post_category'] = array(1);
    $my_post['post_type'] = 'page';
    $my_post['comment_status'] = 'closed';
    $my_post['ping_status'] = 'closed';
    $pid = wp_insert_post( $my_post );
    update_option('websitetoolbox_pageid', $pid);
}

/* Purpose: Set page content on the front end according to the basic theme.
Parameter: None
Return: None */
function websitetoolbox_lol($content) {
	$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
	$page_content = get_page($websitetoolboxpage_id);
	$page_content = $page_content->post_content;
	$theme_data = wp_get_theme();
	$wrap_pre = "<style>.nocomments { display: block; }</style>";
	$wrap_post = "";
	if($theme_data['Name']=="WordPress Default" && strpos($theme_data['Description'], '>Kubrick<')==90) {
		$wrap_pre .= "<div style='background-color: white;'>";
		$wrap_post .= "</div>";
	}
	if($theme_data['Template'] == "twentyeleven") {
		$wrap_pre .= <<<STYLE
		<style type="text/css">
		.singular .entry-header, .singular .entry-content, .singular footer.entry-meta, .singular #comments-title {
		width: 100%;
		}
		.singular #content, .left-sidebar.singular #content {
		margin: 0 1.5%;
		}
		.page-id-$websitetoolboxpage_id  .entry-title {display: none;}

		#main { padding: 0; }
		.singular.page .hentry { padding: 0; }
		</style>
STYLE;
	}
	return <<<EMBED
	$wrap_pre
	$page_content
	$wrap_post
EMBED;
}

/* Purpose: create a new page for front end.
Parameter: None
Return: None */
function websitetoolbox_init() {
	$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
	if(is_page($websitetoolboxpage_id)) {
		$page = get_page($websitetoolboxpage_id);
		if($page && $page->post_status!='publish') {
			$page->post_status = 'publish';
			wp_update_post($page);
		}
		add_filter("the_content", "websitetoolbox_lol");
	}
}

/* Purpose: Create an forum settings menu under settings tab into WordPress admin menu.
Parameter: None
Return: None */
function websitetoolbox_add_admin_menu() {
    add_options_page( 'WebsiteToolbox', 'Website Toolbox', 'manage_options', 'websitetoolboxoptions', 'websitetoolbox_admin_options' );
}

/* Purpose: Create an Website Toolbox settings options page to set forum settings into WordPress admin section.
Parameter: None
Return: None */
function websitetoolbox_admin_init() {

	// To show settings description on Websitetoolbox settings page on WordPress admin panel.
    add_settings_section(
        'websitetoolbox_settings_section',
        '<h2>Website Toolbox Forum</h2>',
        'websitetoolbox_settings_desc',
        'websitetoolboxoptions'
    );

    // To show forum username option on forum settings page on WordPress admin panel.
    add_settings_field(
        'websitetoolbox_username',
        'Website Toolbox Username:',
        'websitetoolbox_username_option',
        'websitetoolboxoptions',
        'websitetoolbox_settings_section'
    );

	// To show forum API option on forum settings page on WordPress admin panel.
	add_settings_field(
        'websitetoolbox_api',
        'Forum API Key:',
        'websitetoolbox_api_option',
        'websitetoolboxoptions',
        'websitetoolbox_settings_section'
    );

	// To show forum embed option on forum settings page on WordPress admin panel.
	add_settings_field(
        'websitetoolbox_redirect',
        'Embed the forum:',
        'websitetoolbox_embed_option',
        'websitetoolboxoptions',
        'websitetoolbox_settings_section'
    );


	register_setting( 'websitetoolboxoptions', 'websitetoolbox_username' );
	register_setting( 'websitetoolboxoptions', 'websitetoolbox_api' );
	register_setting( 'websitetoolboxoptions', 'websitetoolbox_redirect' );
}

/* Purpose: Add forum settings description on forum settings options page into WordPress admin section.
Parameter: None
Return: None */
function websitetoolbox_settings_desc() {
	echo '<OL><LI><a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum on Website Toolbox</a> or <a href="http://www.websitetoolbox.com/tool/members/login" target="_blank">login to your existing Website Toolbox forum</a>.</LI>
	<LI>Click the <i>Settings</i> link in the navigation menu at the top. In the Settings menu, select the <i>Single Sign On</i> option.</LI>
	<LI>Copy the <i>API Key</i> from the Single Sign On settings page and paste it into the <i>Forum API Key</i> text box on this WordPress plugin setup page.</LI>
	<LI>Provide your <i>Website Toolbox Username</i> in the text box below and click the <i>Update</i> button.</LI>
	<p>Please <a href="http://www.websitetoolbox.com/contact?subject=WordPress+Plugin+Setup+Help" target="_blank">Contact Customer Support</a> if you need help getting setup.</p></OL>';
}

/* Purpose: Add username option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_username_option($args) {
	$websitetoolbox_username = isset($_POST['websitetoolbox_username']) ? $_POST['websitetoolbox_username'] : get_option('websitetoolbox_username');

	$html = '<input type="text" name="websitetoolbox_username" id="websitetoolbox_username" value="'.$websitetoolbox_username.'" size="50"/>';

	$html .= '<label for="websitetoolbox_username"> <a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum at Website Toolbox</a> to get your username.</label>';

	echo $html;
}

/* Purpose: Add API option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_api_option($args) {
	$websitetoolbox_api = isset($_POST['websitetoolbox_api']) ? $_POST['websitetoolbox_api'] : get_option('websitetoolbox_api');

	$html = '<input type="text" name="websitetoolbox_api" id="websitetoolbox_api" value="'.$websitetoolbox_api.'" size="50"/>';

	$html .= '<label for="websitetoolbox_api"> Get your <a href="http://www.websitetoolbox.com/support/252" target="_blank">API key</a>.</label><br>';

	echo $html;
}

/* Purpose: Add embed option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_embed_option($args) {

	// enable Embed option when user install the plugin.
	if(!get_option('websitetoolbox_username')) {
		$checked = 1;
	} else {
		$checked = get_option('websitetoolbox_redirect');
	}

	$html = '<input type="checkbox" name="websitetoolbox_redirect" id="websitetoolbox_redirect" value="1" ' . checked(1, $checked, false) . '/>';

	$html .= '<label for="websitetoolbox_redirect"> Enable this option to have your forum load within an iframe on your website. <br>Disable this option to have your forum load in a full-sized window. You can use the Layout section in your Website Toolbox account to <a href="http://www.websitetoolbox.com/support/148" target="_blank">customize your forum layout to match your website</a> or <a href="http://www.websitetoolbox.com/contact?subject=Customize+Forum+Layout" target="_blank">contact Website Toolbox support to customize it for you</a>.</label>';

	echo $html;
}

#create forum settings page for admin panel
function websitetoolbox_admin_options() {
	if($_POST) {
		global $wpdb;
		if($_POST['websitetoolbox_username'] && $_POST['websitetoolbox_api']) {
			// create URL to check authentication token.
			$URL = "http://www.websitetoolbox.com/tool/members/mb/settings";

			$websitetoolbox_login_url;
			$websitetoolbox_logout_url;
			$websitetoolbox_register_url;
			// If registration option is enable from WordPress site then sent login, logout, registration URL into HTTP request.
			if(get_option('users_can_register')) {
				$websitetoolbox_login_url = wp_login_url();
				$websitetoolbox_logout_url = add_query_arg( array('action' => 'logout'), wp_login_url() );
				$websitetoolbox_register_url = wp_registration_url();
			}
			// Get embed page URL
			if($_POST['websitetoolbox_redirect']) {
				$wtb_pageid = get_option('websitetoolbox_pageid');
				$embed_page = get_page($wtb_pageid);
				$embed_page->post_title = "Forum";
				$embed_page_url = $embed_page->guid;
			}

			// Append fileds email and password to create an account on the related forum if account not exist.
			$fields = array('action' => 'checkAPIKey','forumUsername' => $_POST['websitetoolbox_username'], 'forumApikey' => $_POST['websitetoolbox_api'], 'login_page_url' => $websitetoolbox_login_url, 'logout_page_url' => $websitetoolbox_logout_url, 'registration_url' => $websitetoolbox_register_url, 'embed_page_url' => $embed_page_url);

			// Send http or https request to get authentication token.
			$response_array = wp_remote_post($URL, array('method' => 'POST', 'body' => $fields));

			//Check if http/https request could not return any error then filter JSON from response
			if(!is_wp_error( $response_array )) {
				$response = trim(wp_remote_retrieve_body($response_array));
				$response = json_decode($response);
				// Get authentication token from JSON response.
				if($response->{'error'}) {
					#return an error message
					echo "<div id='setting-error-settings_updated' class='updated error'><p>".$response->{'error'}."</p></div>";
					wtbForumSettingsPage();
					return false;
				} else {
					$forum_address = $response->{'forumaddress'};
				}
			}

			# remove the backslash at the end for consistency
			$forum_address = preg_replace('#/$#', '', $forum_address);
			if(get_option("websitetoolbox_username")) {
				#update Website Toolbox forum user name in option table if exist
				update_option('websitetoolbox_username', $_POST['websitetoolbox_username']);
			} else {
				#insert Website Toolbox forum user name in option table
				add_option('websitetoolbox_username', $_POST['websitetoolbox_username']);
			}
			if(get_option("websitetoolbox_api")) {
				#update Website Toolbox forum API in option table if exist
				update_option('websitetoolbox_api', $_POST['websitetoolbox_api']);
			} else {
				#insert Website Toolbox forum API name in option table
				add_option('websitetoolbox_api', $_POST['websitetoolbox_api']);
        $firstSetup = 1;
			}
			if(get_option("websitetoolbox_url")) {
				#update Website Toolbox forum URL in option table if exist
				update_option('websitetoolbox_url', $forum_address);
			} else {
				#insert Website Toolbox forum URL name in option table
				add_option('websitetoolbox_url', $forum_address);
			}
			if(get_option("websitetoolbox_redirect")=="") {
				#insert Website Toolbox forum redirect type (New window or in iframe) in option table
				add_option('websitetoolbox_redirect', $_POST['websitetoolbox_redirect']);
			}
			update_option('websitetoolbox_redirect', $_POST['websitetoolbox_redirect']);

			$websitetoolbox_url		 = get_option("websitetoolbox_url");
			#Get Website Toolbox page id
			$post_ID = $wpdb->get_results( "SELECT ID FROM " ."$wpdb->posts WHERE post_title='Forum'" );
			foreach ($post_ID as $result) {
				$post_ID = $result->ID;
			}

			#check on post meta
			$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
			$page = get_page($websitetoolboxpage_id);


			$row_post_link = get_post_meta( $websitetoolboxpage_id, '_links_to', true );
			$row_post_target = get_post_meta( $websitetoolboxpage_id, '_links_to_target', true );
			$row_post_type = get_post_meta( $websitetoolboxpage_id, '_links_to_type', true );

			if(get_post_meta( $websitetoolboxpage_id, '_links_to', true )){
				update_post_meta( $post_ID, '_links_to', $websitetoolbox_url );
			} else {
				add_post_meta( $post_ID, '_links_to', $websitetoolbox_url );
			}
			if(get_post_meta( $websitetoolboxpage_id, '_links_to_target', true )) {
				update_post_meta( $post_ID, '_links_to_target', 'websitetoolbox' );
			} else {
				add_post_meta( $post_ID, '_links_to_target', 'websitetoolbox' );
			}
			if(get_post_meta( $websitetoolboxpage_id, '_links_to_type', true )) {
				update_post_meta( $post_ID, '_links_to_type', 'custom_post_type' );
			} else {
				add_post_meta( $post_ID, '_links_to_type', 'custom_post_type' );
				add_post_meta( $post_ID, '_wtbredirect_active', '1' );
			}
			#end of check post meta

			if(preg_match('#^https?://#', get_option('websitetoolbox_url'))) {
				$wtb_url = get_option('websitetoolbox_url');
			} else {
				$wtb_url = "http://".get_option('websitetoolbox_url');
			}

			if(get_option("websitetoolbox_redirect") == 1) {
				#open forum in iframe
				$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
				$page = get_page($websitetoolboxpage_id);
				$page->post_title = "Forum";
				wp_update_post($page);
				$page->post_content = '<script type="text/javascript" id="embedded_forum" src="'.$wtb_url.'/js/mb/embed.js"></script><noscript><a href="'.$wtb_url.'">Forum</a></noscript>';
				wp_update_post($page);
				update_post_meta( $post_ID, '_wtbredirect_active', '' );
			} else {
				#open forum in new window
				$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
				$page = get_page($websitetoolboxpage_id);
				$page->post_content = "";
				wp_update_post($page);
				update_post_meta( $post_ID, '_wtbredirect_active', '1' );
			}
			if($post_ID) {
        echo '<div id="setting-error-settings_updated" class="updated notice"><p>';
        if ($firstSetup) {
          echo 'Congrats! A link to your forum has been added to your website\'s navigation menu and single sign on has been integrated.';
        } else {
          echo 'Your settings have been saved.';
        }
        echo '</p></div>';
			}
		} else {
			/* Show error meesage */
			if(!$_POST['websitetoolbox_username']) {
				$error_message = "Enter your forum username";
			} elseif(!$_POST['websitetoolbox_api']) {
				$error_message = "Enter your forum API key.";
			}
			if($error_message) {
				echo "<div id='setting-error-settings_updated' class='updated error'><p>".$error_message."</p></div>";
			}
		}
	}
	?>
	<script language="javascript">
	/* validate admin form information */
	function ValidateForm(){
		var websitetoolbox_username = document.getElementById('websitetoolbox_username').value;
		var websitetoolbox_api = document.getElementById('websitetoolbox_api').value;
		if(websitetoolbox_username=="") {
			alert("Please enter your forum username.");
			document.getElementById('websitetoolbox_username').focus();
			return false;
		}
		if(websitetoolbox_api=="") {
			alert("Please enter your forum API key.");
			document.getElementById('websitetoolbox_api').focus();
			return false;
		}
	}
	</script>
	<?php
	wtbForumSettingsPage();
}

/* Purpose: This function is used to show forum settings page.
Param: none
Return: Nothing */
function wtbForumSettingsPage() {
	?>
	<!-- create a form in the wordpress admin panel -->
	<div class="wrap">
        <form name="form_lol" action="options-general.php?page=websitetoolboxoptions" method="POST" onsubmit="ValidateForm();">
            <?php settings_fields( 'websitetoolbox_settings_section' ); ?>
            <?php do_settings_sections( 'websitetoolboxoptions' ); ?>
            <?php submit_button('Update'); ?>
        </form>
    </div>
	<?php
}


#activate plugin
function websitetoolbox_activate() {
	/*
	*  We want to create a page for Website Toolbox when it's installed or when the page was deleted
	*  and otherwise publish an existing page
	*/
	$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
	if($websitetoolboxpage_id) {
		$page = get_page($websitetoolboxpage_id);
		if($page) {
			$page->post_status = 'publish';
			wp_update_post($page);
		} else {
			// someone might have deleted the page, recreate it
			create_websitetoolbox_page();
		}
	} else {
		create_websitetoolbox_page();
	}
}

#deactivate plugin
function websitetoolbox_deactivate() {
	// hide the Website Toolbox forum page if it exists
	$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
	if($websitetoolboxpage_id) {
		$page = get_page($websitetoolboxpage_id);
		if($page) {
			$page->post_status = 'draft';
			wp_update_post($page);
		}
	}
}

#logged-in a new user on the related forum
function wt_login_user($user_login) {
	$user_obj = new WP_User(0,$user_login);
	$username = $user_obj->user_login;
	$password = $user_obj->user_pass;
	$email 	  = $user_obj->user_email;

	$forum_api		= get_option("websitetoolbox_api");
	$forum_url		= get_option("websitetoolbox_url");

	// create URL to get authentication token.
	$URL = $forum_url."/register/setauthtoken";
	// Append fileds email and password to create an account on the related forum if account not exist.
	$fields = array('apikey' => $forum_api, 'user' => $username, 'email' => $email);

	// Send http or https request to get authentication token.
	$response_array = wp_remote_post($URL, array('method' => 'POST', 'body' => $fields));
	//Check if http/https request could not return any error then filter XML from response
	if(!is_wp_error( $response_array )) {
		$response = trim(wp_remote_retrieve_body($response_array));
		// Get authentication token from XML response.
		$response_xml = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/', 'filter_xml', $response);
		$response_xml = simplexml_load_string($response_xml);

		if(htmlentities($response_xml->error) != "" && $username != 'admin') {
			#return an error message
			wp_die(htmlentities($response_xml->error));
		} else {
			if(htmlentities($response_xml->authtoken)) {
				$resultdata = htmlentities($response_xml->authtoken);
			} else {
				$resultdata = '';
			}
			// If chat Room enable then get chat access token.
			if(htmlentities($response_xml->access_token)) {
				$access_token = htmlentities($response_xml->access_token);
				$chatroom_url = htmlentities($response_xml->chatroom_url);
			} else {
				$access_token = '';
				$chatroom_url = '';
			}
			#set cookie for 10 days if user logged-in with "remember me" option, to remain logged-in after closing browser. Otherwise set cookie 0 to logged-out after clossing browser.
			if(!empty($_POST['rememberme'])) {
				setcookie('wt_login_remember', "checked", time() + 864000, COOKIEPATH, COOKIE_DOMAIN);
			}
			// Save authentication token into cookie for one day to use into SSO logout.
			setcookie('wt_logout_token', $resultdata, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
			// Save access token into cookie for one day to use into SSO logout from Chat Room.
			if($access_token) {
				setcookie('wtchat_logout_token', $access_token, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
				setcookie('wtchat_url', $chatroom_url, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
			}
			#Save authentication token into session variable.
			save_authtoken($resultdata,$access_token,$chatroom_url);
			return true;
		}
	}
}

#delete user if username exist on the forum
function wp_delete_user_current( $id, $reassign = 'novalue' ) {
	global $wpdb;
	$id = (int) $id;
	// allow for transaction statement
	do_action('delete_user', $id);
	if ( 'novalue' === $reassign || null === $reassign ) {
		$post_ids = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_author = %d", $id) );
		if ( $post_ids ) {
			foreach ( $post_ids as $post_id )
			wp_delete_post($post_id);
		}
		// Clean links
		$link_ids = $wpdb->get_col( $wpdb->prepare("SELECT link_id FROM $wpdb->links WHERE link_owner = %d", $id) );
		if ( $link_ids ) {
			foreach ( $link_ids as $link_id )
			wp_delete_link($link_id);
		}
	} else {
		$reassign = (int) $reassign;
		$wpdb->update( $wpdb->posts, array('post_author' => $reassign), array('post_author' => $id) );
		$wpdb->update( $wpdb->links, array('link_owner' => $reassign), array('link_owner' => $id) );
	}
	clean_user_cache($id);
	// FINALLY, delete user
	if ( !is_multisite() ) {
		$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id = %d", $id) );
		$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->users WHERE ID = %d", $id) );
	} else {
		$level_key = $wpdb->get_blog_prefix() . 'capabilities'; // wpmu site admins don't have user_levels
		$wpdb->query("DELETE FROM $wpdb->usermeta WHERE user_id = $id AND meta_key = '{$level_key}'");
	}
	// allow for commit transaction
	do_action('deleted_user', $id);
	return true;
}

#create an account on the related forum
function wt_register_user($userid) {

	$user_obj = new WP_User($userid);
	$forum_api		= get_option("websitetoolbox_api");
	$forum_url		= get_option("websitetoolbox_url");

	$login_id = $user_obj->ID;
	$login 	  = $user_obj->user_login; # ie: JohnD223
	// In latest version (4.3) WordPress site generate encrypted password every time account created by front end user or administrator, so set blank password every time for SSO registration..
	$password = '';
	$email 	  = $user_obj->user_email;
	$display_name = $user_obj->display_name; # ie: John Doe
	$first_name = $user_obj->first_name; # ie: John
	$last_name = $user_obj->last_name; # ie: Doe
	$fullname = $first_name." ".$last_name;

	// URL to create a new account on forum.
	$URL = $forum_url."/register/create_account";
	// Fields array.
	$fields = array('apikey' => $forum_api, 'member' => $login, 'pw' => $password, 'email' => $email, 'name' => $fullname);
	// Sent https/https request on related forum to create an account on the related forum.
	$response_array = wp_remote_post($URL, array('method' => 'POST', 'body' => $fields));

	//Check if http/https request could not return any error then filter XML from response
	if(!is_wp_error( $response_array )) {
		$response = trim(wp_remote_retrieve_body($response_array));
		// Filter XML response to get message.
		$response_xml = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/', 'filter_xml', $response);
		$response_xml = simplexml_load_string($response_xml);
		$response = trim(htmlentities($response_xml->error));
		$full_length = strlen($response);

		#Remove HTML tag with content from the message, return from forum if email of user already exist.
		if(strpos($response,'&lt;')) {
			$bad_string = strpos($response,'&lt;');
			$response = substr($response, 0, $bad_string-1);
		}
		$SUCCESS_STRING = "Registration Complete";
		$USER_EMAIL_EXIST_STRING = "Error: It looks like you already have a forum account! A forum account for that username and email address combination already exists!";
		if($response == $SUCCESS_STRING || $response == $USER_EMAIL_EXIST_STRING) {
			return true;
		} else {
			wp_delete_user_current($login_id);
			wp_die($response);
		}
	}
}

/* Show to admin after activate the SSO plugin while SSO will not be configured.*/
function wtb_warning()
{
	if(!get_option("websitetoolbox_username") || !get_option("websitetoolbox_api") || !get_option("websitetoolbox_url")) {
		echo "
		<div id='wtb-warning' class='error'>
			<p>You will need to complete Website Toolbox <a href='options-general.php?page=websitetoolboxoptions'>Settings</a> in order for the plugin to work.</p>
		</div>
		";
	}
}

# for URl making
if (!function_exists('esc_attr')) {
	function esc_attr($attr){return attribute_escape( $attr );}
	function esc_url($url){return clean_url( $url );}
}

#get all link of the menu
if(get_option("websitetoolbox_redirect") == '') {
	function filter_page_links_wtb ($link, $post) {
		if(isset($post->ID)) {
			$id = $post->ID;
		} else {
			$id = $post;
		}
		#get array
		$newCheck = get_main_array();
		if(!is_array($newCheck)) { $newCheck = array(); }
		#check array according to the key if forum used external url
		if(array_key_exists($id, $newCheck)) {
			$matchedID = $newCheck[$id];
			$newURL = $matchedID['_links_to'];
			if(strpos($newURL,get_option('home'))>=0 || strpos($newURL,'www.')>=0 || strpos($newURL,'http://')>=0 || strpos($newURL,'https://')>=0) {
				if($matchedID['_links_to_target'] == 'websitetoolbox') {
					$newURL = trim($matchedID['_links_to']);
					// Added / at the end of forum url if open into parent window.
					if(!preg_match("/\/$/", $newURL)) {
						$link = $newURL."/";
					}
				} else {
					$link = esc_url( $newURL );
				}
			} else {
				if($matchedID['_links_to_target'] == 'websitetoolbox') {
					// Added / at the end of forum url if open into parent window.
					if(!preg_match("/\/$/", $newURL)) {
						$link = $newURL."/";
					}
				} else {
					$link = esc_url( get_option( 'home').'/'. $newURL );
				}
			}
		}
		return $link;
	}
	add_filter('page_link', filter_page_links_wtb, 20, 2);
}

# Get main array from the post meta and post table according to redirest url
function get_main_array(){
	global $wpdb;
	$theArray = array();

	$theqsl = "SELECT * FROM $wpdb->postmeta a, $wpdb->posts b  WHERE a.`post_id`=b.`ID` AND b.`post_status`!='trash' AND (a.`meta_key` = '_wtbredirect_active' || a.`meta_key` = '_links_to' || a.`meta_key` = '_links_to_target' || a.`meta_key` = '_links_to_type') ORDER BY a.`post_id` ASC;";
	$thetemp = $wpdb->get_results($theqsl);
	if(count($thetemp)>0){
		foreach($thetemp as $key){
			$theArray[$key->post_id][$key->meta_key] = $key->meta_value;
		}
		foreach($thetemp as $key){
			// defaults
			if(!isset($theArray[$key->post_id]['_links_to'])){$theArray[$key->post_id]['_links_to']	= 0;}
			if(!isset($theArray[$key->post_id]['_links_to_type'] )){$theArray[$key->post_id]['_links_to_type']				= 302;}
			if(!isset($theArray[$key->post_id]['_links_to_target'])){$theArray[$key->post_id]['_links_to_target']	= 0;}
		}

	}
	return $theArray;
}

/* Purpose: This function is used to set authentication token into session variable if user logged-in.
Param: authentication token
Return: Nothing */
function save_authtoken($authtoken,$access_token,$chatroom_url) {
	$_SESSION['wtb_login_auth_token'] = $authtoken;
	// Save access token into a session to login user on the caht room
	if($access_token) {
		$_SESSION['wtbchat_login_auth_token'] = $access_token;
		$_SESSION['wtbchat_url'] = $chatroom_url;
	}
}

/* Purpose: This function is used to unset session variable if user logged-in/logged-out.
Param1: type (login/logout)
Return: Nothing */
function clean_authtoken($type) {
	if($type=='login') {
		unset($_SESSION['wtb_login_auth_token']);
		unset($_SESSION['wtbchat_login_auth_token']);
		unset($_SESSION['wtbchat_url']);
	} else if($type=='logout')	{
		setcookie("wt_logout_token", '', 0);
		setcookie("wtchat_logout_token", '', 0);
		setcookie("wtchat_url", '', 0);
	}
}

/* Purpose: If a user Logged-in/logged-out on WordPress site from front end/admin section write an image tag after page load to loggout from forum.
Parameter: None
Return: None */
function ssoLoginLogout() {

	// If user logged-out from the Forum then call wp_logout function to logged-out from WordPress site as well as forum.
	if(isset($_GET['action']) && $_GET['action']=='ssoLogout' && is_user_logged_in()) {
		wp_logout();
		exit;
	}

	// If user logged-out from WordPress Site
	if (isset($_SESSION['wtb_login_auth_token'])) {
		$login_auth_url = get_option('websitetoolbox_url')."/register/dologin?authtoken=".$_SESSION['wtb_login_auth_token'];
		if(isset($_COOKIE['wt_login_remember'])) {
			$login_auth_url = $login_auth_url."&remember=".$_COOKIE['wt_login_remember'];
		}

		/* Print image tag on the login landing success page to sent login request on the related forum */
		echo '<img src="'.$login_auth_url.'" border="0" width="0" height="0" alt="">';
		/* Print image tag on the login landing success page to sent login request on the related Chat Room */
		if(isset($_SESSION['wtbchat_login_auth_token'])) {
			$chatlogin_auth_url = $_SESSION['wtbchat_url']."/sso/token/login?access_token=".$_SESSION['wtbchat_login_auth_token'];
			if(isset($_COOKIE['wt_login_remember'])) {
				$chatlogin_auth_url = $chatlogin_auth_url."&rememberMe=1";
			}
			echo '<img src="'.$chatlogin_auth_url.'" border="0" width="0" height="0" alt="">';
		}
		clean_authtoken('login');
		return false;
	}
	if(!is_user_logged_in() && isset($_COOKIE['wt_logout_token'])) {
		$logout_auth_url = get_option('websitetoolbox_url')."/register/logout?authtoken=".$_COOKIE['wt_logout_token'];
		/* Print image tag on the header section sent logout request on the related forum */
		echo '<img src="'.$logout_auth_url.'" border="0" width="0" height="0" alt="">';
		/* If user logged-in on the chat room then logged-out from the chat room */
		if(isset($_COOKIE['wtchat_logout_token'])) {
			$chatlogout_auth_url = $_COOKIE['wtchat_url']."/sso/token/logout?access_token=".$_COOKIE['wtchat_logout_token'];
			echo '<img src="'.$chatlogout_auth_url.'" border="0" width="0" height="0" alt="">';
		}

		clean_authtoken('logout');
		return false;
	}
}

#Parse XML response
function filter_xml($matches) {
	return trim(htmlspecialchars($matches[1]));
}

/* Purpose: Function is used to append authtoken at the end of URL if user logged-in on wordpress site and then clicks on the forum link.
Parameter: Manual link and post id.
Return: Manual links */
function changeForumLink($items, $post){
	// Append authtoken in the forum link if user logged-in on WordPress site and forum open in window independently.
	if(is_user_logged_in()) {
		if($post == get_option('websitetoolbox_pageid') && get_option("websitetoolbox_redirect") == '') {
			$authtoken = $_COOKIE['wt_logout_token'];
			if($_COOKIE['wtchat_logout_token']) {
				$authtoken .= "-".$_COOKIE['wtchat_logout_token'];
			}
			if(isset($_COOKIE['wt_login_remember'])) {
				$remember = $_COOKIE['wt_login_remember'];
			}
			$add_token = array('authtoken' => $authtoken, 'remember' => $remember);
			$items = add_query_arg( $add_token, $items );
		}
	}
	return $items;
}
add_filter('page_link', 'changeForumLink', 20, 2);

/* Purpose: Function is used to append authentication token into the forum URL.
Parameter: page content.
Return: replace page content */
function updatePageContent($content) {
	// Append authtoken in the embed code if user logged-in on WordPress site and forum open in iframe.
	if($GLOBALS['post']->ID == get_option('websitetoolbox_pageid') && get_option("websitetoolbox_redirect") != '' && is_user_logged_in()) {
		if(preg_match('#^https?://#', get_option('websitetoolbox_url'))) {
			$wtb_url = get_option('websitetoolbox_url');
		} else {
			$wtb_url = "http://".get_option('websitetoolbox_url');
		}
		$auth_token = "?authtoken=".$_COOKIE['wt_logout_token'];
		if($_COOKIE['wtchat_logout_token']) {
			$auth_token .= "-".$_COOKIE['wtchat_logout_token'];
		}
		if(isset($_COOKIE['wt_login_remember'])) {
			$auth_token .= "&remember=".$_COOKIE['wt_login_remember'];
		}
		$content = '<script type="text/javascript" id="embedded_forum" src="'.$wtb_url.'/js/mb/embed.js'.$auth_token.'"></script><noscript><a href="'.$wtb_url.'">Forum</a></noscript>';
	}
    return $content;
}
add_filter( 'the_content', 'updatePageContent', 20, 2 );



/* Purpose: If a user deleted from WordPress site then delete from Forum.
Parameter: None
Return: None */
function deleteForumUser($args) {
	global $wpdb;

	#get All the user id's deleted from wordpress site.
	$userids = implode(",", $_POST['users']);

	#Get username from users table on the basis of userids.
	$user_names = $wpdb->get_results( "
		SELECT user_login
		FROM $wpdb->users
		WHERE ID IN ($userids)" );
	$unames = array();
	foreach ( $user_names as $usernames ) {
		$unames[] = $usernames->user_login;
	}
	#create a comma(,) separated string to sent user delete request on the Forum.
	if ( $unames ) {
		$usernames = implode(",", $unames);
	}


	$forum_api		= get_option("websitetoolbox_api");
	$forum_url		= get_option("websitetoolbox_url");

	// create URL to to delete users from related Forum.
	$URL = $forum_url."/register";

	$fields = array('apikey' => $forum_api, 'massaction' => 'decline_mem', 'usernames' => $usernames);

	// Send http or https request to get authentication token.
	$response_array = wp_remote_post($URL, array('method' => 'POST', 'body' => $fields));

	if(!is_wp_error( $response_array )) {
		$response = trim(wp_remote_retrieve_body($response_array));
		// Decode json string
		$response = json_decode($response);
		if($response->{'success'}) {
			return true;
		}
	}
}

/* Define Hook to get user information */
add_action('admin_menu', 'websitetoolbox_add_admin_menu');
add_action('admin_init', 'websitetoolbox_admin_init');
add_action('wp_head', 'websitetoolbox_init');

/* wp_login hook called when user logged-in into wordpress site (front end/back end) */
add_action('wp_login','wt_login_user');
/* user_register hook called when a new account creates from from wordpress site (front end/back end) */
add_action('user_register', 'wt_register_user');
/* admin_notices to print notice(message) on admin section */
add_action('admin_notices', 'wtb_warning');
/* print IMG tags to the footer if needed */
add_action('wp_footer','ssoLoginLogout');
add_action('admin_footer','ssoLoginLogout');
/* print IMG tags to the admin login page if user redirected to login page after logged-out. */
add_action('login_footer', 'ssoLoginLogout');
/* Call this hook if single or multiple user delete from the WordPress site. */
add_action('delete_user', 'deleteForumUser');

register_activation_hook( __FILE__, 'websitetoolbox_activate' );
register_deactivation_hook( __FILE__, 'websitetoolbox_deactivate' );
