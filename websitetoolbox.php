<?php
/**
 * @package Website Toolbox Forum
 * @author Website Toolbox
 */
/*
Plugin Name: Website Toolbox Forum
Description: Integrates single sign on and embeds your forum into your WordPress website.
Version: 1.2.3
Author: Team Website Toolbox | <a href="options-general.php?page=websitetoolboxoptions">Settings</a>
Purpose: Integrates your forum with your WordPress website
*/
set_time_limit(0);
ob_start();
session_start();

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
	$theme_name = get_current_theme();
	$theme_data = get_theme($theme_name);
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
	
	// To show forum Address option on forum settings page on WordPress admin panel.
	add_settings_field(
        'websitetoolbox_url',
        'Forum Address:',
        'websitetoolbox_address_option',
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
	register_setting( 'websitetoolboxoptions', 'websitetoolbox_url' );
	register_setting( 'websitetoolboxoptions', 'websitetoolbox_redirect' );
}

/* Purpose: Add forum settings description on forum settings options page into WordPress admin section.
Parameter: None
Return: None */
function websitetoolbox_settings_desc() {
	echo '<OL><LI><a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum on Website Toolbox</a> or <a href="http://www.websitetoolbox.com/tool/members/login" target="_blank">login to your existing Website Toolbox forum</a>.</LI>
	<LI>Click the <i>Settings</i> link in the navigation menu at the top. In the Settings menu, select the <i>Single Sign On</i> option.</LI>
	<LI>On the Single Sign On settings page, specify the Login, Logout, and Registration page address (URL) of your WordPress website and <i>Save</i> your changes. If your WordPress website doesn'."'".'t have these pages, skip this step.</LI>
	<LI>Copy the <i>API Key</i> from the Single Sign On settings page and paste it into the <i>Forum API Key</i> text box on this WordPress plugin setup page.</LI>
	<LI>Provide your <i>Website Toolbox Username</i> and <i>Forum Address</i>  in the text boxes below and click the <i>Update</i> button.</LI>
	<p>Please <a href="http://www.websitetoolbox.com/contact?subject=WordPress+Plugin+Setup+Help" target="_blank">Contact Customer Support</a> if you need help getting setup.</p></OL>';
}

/* Purpose: Add username option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_username_option($args) {
	$websitetoolbox_username = $_POST['websitetoolbox_username'] ? $_POST['websitetoolbox_username'] : get_option('websitetoolbox_username');
	
	$html = '<input type="text" name="websitetoolbox_username" id="websitetoolbox_username" value="'.$websitetoolbox_username.'" size="50"/>';

	$html .= '<label for="websitetoolbox_username"> <a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum at Website Toolbox</a> to get your username.</label>';

	echo $html;
}

/* Purpose: Add API option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_api_option($args) {
	$websitetoolbox_api = $_POST['websitetoolbox_api'] ? $_POST['websitetoolbox_api'] : get_option('websitetoolbox_api');
	
	$html = '<input type="text" name="websitetoolbox_api" id="websitetoolbox_api" value="'.$websitetoolbox_api.'" size="50"/>';

	$html .= '<label for="websitetoolbox_api"> Get your <a href="http://www.websitetoolbox.com/support/252" target="_blank">API key</a>.</label>';

	echo $html; 
}

/* Purpose: Add Forum Address option on forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_address_option($args) {
	$websitetoolbox_url = $_POST['websitetoolbox_url'] ? $_POST['websitetoolbox_url'] : get_option('websitetoolbox_url');

	$html = '<input type="text" name="websitetoolbox_url" id="websitetoolbox_url" value="'.$websitetoolbox_url.'" size="50"/>';

	$html .= '<label for="websitetoolbox_url"> You can get your Forum address by visiting the dashboard of your Website Toolbox account.</label>';

	echo $html; 
}

/* Purpose: Add embed option on Forum settings page into WordPress Forum settings page.
Parameter: None
Return: None */
function websitetoolbox_embed_option($args) {
	
	$html = '<input type="checkbox" name="websitetoolbox_redirect" id="websitetoolbox_redirect" value="1" ' . checked(1, get_option('websitetoolbox_redirect'), false) . '/>';
		
	$html .= '<label for="websitetoolbox_redirect"> Enable this option to have your forum load within an iframe on your website. <br>Disable this option to have your forum load in a full-sized window. You can use the Layout section in your Website Toolbox account to <a href="http://www.websitetoolbox.com/support/148" target="_blank">customize your forum layout to match your website</a> or <a href="http://www.websitetoolbox.com/contact?subject=Customize+Forum+Layout" target="_blank">contact Website Toolbox support to customize it for you</a>.</label>';

	echo $html;
}


function validateForumURL($forumURL) {
	$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
	if(eregi($urlregex, $forumURL)) {
		return true;	
	} else {
		return false;
	}
}

#create forum settings page for admin panel
function websitetoolbox_admin_options() {
	if($_POST) { 
		global $wpdb;
		if($_POST['websitetoolbox_username'] && $_POST['websitetoolbox_api'] && $_POST['websitetoolbox_url'] && validateForumURL($_POST['websitetoolbox_url'])) {
			
			// create URL to check authentication token.
			$URL = "http://www.websitetoolbox.com/tool/members/mb/settings";
			// Append fileds email and password to create an account on the related forum if account not exist.
			$fields = array('action' => 'checkAPIKey','forumUsername' => $_POST['websitetoolbox_username'], 'forumApikey' => $_POST['websitetoolbox_api']);
			
			// Send http or https request to get authentication token.
			$response_array = wp_remote_post($URL, array('method' => 'POST', 'body' => $fields));
			
			//Check if http/https request could not return any error then filter XML from response 
			if(!is_wp_error( $response_array )) {
				$response = trim(wp_remote_retrieve_body($response_array));
				// Get authentication token from XML response.
				$response_xml = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/', 'filter_xml', $response);
				$response_xml = simplexml_load_string($response_xml);
				
				if(htmlentities($response_xml->error)) {
					#return an error message 
					echo "<div id='setting-error-settings_updated' class='updated error'><p>".$response_xml->error."</p></div>";
					wtbForumSettingsPage();
					return false;
				}	
			}	
			
			# remove the backslash at the end for consistency
			$_POST['websitetoolbox_url'] = preg_replace('#/$#', '', $_POST['websitetoolbox_url']);
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
			} 
			if(get_option("websitetoolbox_url")) {
				#update Website Toolbox forum URL in option table if exist
				update_option('websitetoolbox_url', $_POST['websitetoolbox_url']);
			} else {
				#insert Website Toolbox forum URL name in option table
				add_option('websitetoolbox_url', $_POST['websitetoolbox_url']);	
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
			
			if(preg_match('#^https?://#', get_option(websitetoolbox_url))) {
				$wtb_url = get_option(websitetoolbox_url);
			} else {
				$wtb_url = "http://".get_option(websitetoolbox_url);
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
				echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Your settings have been saved.</p></div>";
			}			
		} else {
			/* Show error meesage */
			if(!$_POST['websitetoolbox_username']) {
				$error_message = "Enter your forum username";
			} elseif(!$_POST['websitetoolbox_api']) {
				$error_message = "Enter your forum API key.";
			} elseif(!$_POST['websitetoolbox_url']) {
				$error_message = "Enter your forum address.";
			} elseif($_POST['websitetoolbox_url'] && !validateForumURL($_POST['websitetoolbox_url'])) {
				$error_message = "Enter valid URL including http or https.";
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
		var websitetoolbox_url = document.getElementById('websitetoolbox_url').value;
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
		if(websitetoolbox_url=="") {
			alert("Please enter your forum URL.");
			document.getElementById('websitetoolbox_url').focus();
			return false;
		}
		if(websitetoolbox_url!="") {
			var pattern = /[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/
			if (!pattern.test(websitetoolbox_url)) {
				alert("Please enter valid URL including http ot https");
				return false;
			} 
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
	$username = urlencode($user_obj->user_login);
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
			#set cookie for 10 days if user logged-in with "remember me" option, to remain logged-in after closing browser. Otherwise set cookie 0 to logged-out after clossing browser. 
			if(!empty($_POST['rememberme'])) {
				setcookie('wt_login_remember', "checked", time() + 864000, COOKIEPATH, COOKIE_DOMAIN);
			}
			// Save authentication token into cookie for one day to use into SSO logout.
			setcookie('wt_logout_token', $resultdata, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
			#Save authentication token into session variable.
			save_authtoken($resultdata);
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
	// If get plain password then sent passord with request otherwise sent a blank password with SSO registration request. In WordPress admin panel password file name always pass1 so get $_POST['pass1'] as a plain password.
	if($_POST['pass1']) {
		$password = $_POST['pass1'];
	} else {
		$password = '';
	}
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
function save_authtoken($authtoken) {
	$_SESSION['wtb_login_auth_token'] = $authtoken;
}

/* Purpose: This function is used to unset session variable if user logged-in/logged-out.
Param1: type (login/logout)
Return: Nothing */
function clean_authtoken($type) {
	if($type=='login') {
		unset($_SESSION['wtb_login_auth_token']);	
	} else if($type=='logout')	{
		setcookie("wt_logout_token", '', 0);
	}
}

/* Purpose: If a user Logged-in/logged-out on WordPress site from front end/admin section write an image tag after page load to loggout from forum.
Parameter: None
Return: None */
function ssoLoginLogout() {
	if (isset($_SESSION['wtb_login_auth_token'])) {
		$login_auth_url = get_option(websitetoolbox_url)."/register/dologin?authtoken=".$_SESSION['wtb_login_auth_token'];
		if($_COOKIE['wt_login_remember']) {
			$login_auth_url = $login_auth_url."&remember=".$_COOKIE['wt_login_remember'];
		}
		/* Print image tag on the login landing success page to sent login request on the related forum */
		echo '<img src="'.$login_auth_url.'" border="0" width="0" height="0" alt="">';
		/* remove authentication token from session variable so that above image tag not write again and again */
		clean_authtoken('login');
		return false;
	}
	if(!is_user_logged_in() && $_COOKIE['wt_logout_token']) {
		$logout_auth_url = get_option(websitetoolbox_url)."/register/logout?authtoken=".$_COOKIE['wt_logout_token'];
		/* Print image tag on the header section sent logout request on the related forum */
		echo '<img src="'.$logout_auth_url.'" border="0" width="0" height="0" alt="">';
		clean_authtoken('logout');
		return false;
	}
}

#Parse XML response 
function filter_xml($matches) {
	return trim(htmlspecialchars($matches[1]));
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

register_activation_hook( __FILE__, 'websitetoolbox_activate' );
register_deactivation_hook( __FILE__, 'websitetoolbox_deactivate' );