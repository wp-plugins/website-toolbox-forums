<?php
/**
 * @package Website Toolbox Forum
 * @author Team Website Toolbox
 */
/*
Plugin Name: Website Toolbox Forum
Description: The SSO API allows you to integrate your forum's registration, login, and logout process with your website.
Version: 1.2.1
Author: Team Website Toolbox | <a href="options-general.php?page=websitetoolboxoptions">Settings</a>
Purpose: Integrate SSO feature with your WordPress website
*/

ob_start();
session_start();
#insert forum title, default page content, plug-in status, in the option table
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


add_action('wp_head', 'websitetoolbox_init');

#Set page content on the front end according to the basic theme
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

#create a new page for front end
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

# create admin menues under settings tab
add_action('admin_menu', 'websitetoolbox_add_admin_menu');
function websitetoolbox_add_admin_menu() {    
	add_options_page('WebsiteToolbox', 'Website Toolbox', 'administrator', 'websitetoolboxoptions', 'websitetoolbox_admin_options');
}

#create forum settings page for admin panel
function websitetoolbox_admin_options() {
	$pageurl = get_page_link(get_option('websitetoolbox_pageid'));
	echo "<h2>Website Toolbox Forum</h2>";
	if($_POST) { 
		global $wpdb;
		if($_POST['websitetoolbox_username']!="") {
			if($_POST['websitetoolbox_api']!="") {
				if($_POST['websitetoolbox_url']!="") {
					#check valid forum url link
					$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
					if (eregi($urlregex, $_POST['websitetoolbox_url'])) {
						#Get record from the option table on the basis of our parameter
						$option_username_id = $wpdb->get_row("SELECT option_id FROM $wpdb->options WHERE option_name = 'websitetoolbox_username'" ); 
						$option_username_id = $option_username_id->option_id;
						$option_username_api = $wpdb->get_row( "SELECT option_id FROM $wpdb->options WHERE option_name = 'websitetoolbox_api'" ); 
						$option_username_api = $option_username_api->option_id;
						$option_username_url = $wpdb->get_row( "SELECT option_id FROM $wpdb->options WHERE option_name = 'websitetoolbox_url'" ); 
						$option_username_url = $option_username_url->option_id;
						$option_username_red = $wpdb->get_row( "SELECT option_id FROM $wpdb->options WHERE option_name = 'websitetoolbox_redirect'" ); 
						$option_username_red = $option_username_red->option_id;
						if(get_option("websitetoolbox_username") == "" && $option_username_id == "") {
							#insert Website Toolbox forum user name in option table
							add_option('websitetoolbox_username', $_POST['websitetoolbox_username']);
						} else {
							#update Website Toolbox forum user name in option table if exist
							update_option('websitetoolbox_username', $_POST['websitetoolbox_username']);      
						} 
						if(get_option("websitetoolbox_api")=="" && $option_username_api=="") {
							#insert Website Toolbox forum API name in option table
							add_option('websitetoolbox_api', $_POST['websitetoolbox_api']);
						} else {
							#update Website Toolbox forum API in option table if exist
							update_option('websitetoolbox_api', $_POST['websitetoolbox_api']);      
						} 
						if(get_option("websitetoolbox_url")=="" && $option_username_url=="") {
							#insert Website Toolbox forum URL name in option table
							add_option('websitetoolbox_url', $_POST['websitetoolbox_url']);
						} else {
							#update Website Toolbox forum URL in option table if exist
							update_option('websitetoolbox_url', $_POST['websitetoolbox_url']);      
						} 
						if(get_option("websitetoolbox_redirect")=="") {
							#insert Website Toolbox forum redirect type (New window or in iframe) in option table
							add_option('websitetoolbox_redirect', $_POST['websitetoolbox_redirect']); 
							update_option('websitetoolbox_redirect', $_POST['websitetoolbox_redirect']);    
						} else {
							#update Website Toolbox forum redirect type (New window or in iframe) in option table if exist
							update_option('websitetoolbox_redirect', $_POST['websitetoolbox_redirect']);      
						} 
						$websitetoolbox_url		 = get_option("websitetoolbox_url");
						#Get Website Toolbox page id
						$post_ID = $wpdb->get_results( "SELECT ID FROM " ."$wpdb->posts WHERE post_title='Forum'" );
						foreach ($post_ID as $result) {
							$post_ID = $result->ID;
						}
					} else {
					update_option('websitetoolbox_url', $_POST['websitetoolbox_url']);
					echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Enter valid URL including http or https.</p></div>";
					}
				} else {
				update_option('websitetoolbox_url', '');
				echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Enter your forum address.</p></div>";
				}	
			} else {
			update_option('websitetoolbox_api', '');
			echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Enter your forum API key.</p></div>";
			}	
		
		} else {
		update_option('websitetoolbox_username', '');
		echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Enter your forum username.</p></div>";
		}
		
		#check on post meta
		$websitetoolboxpage_id = get_option('websitetoolbox_pageid');
		$page = get_page($websitetoolboxpage_id);  
		
		
		$row_post_link = get_post_meta( $websitetoolboxpage_id, '_links_to', true );
		$row_post_target = get_post_meta( $websitetoolboxpage_id, '_links_to_target', true );
		$row_post_type = get_post_meta( $websitetoolboxpage_id, '_links_to_type', true );
		
		if($row_post_link){
			update_post_meta( $post_ID, '_links_to', $websitetoolbox_url );
		} else {
			add_post_meta( $post_ID, '_links_to', $websitetoolbox_url );
		}
		if($row_post_target) {
			update_post_meta( $post_ID, '_links_to_target', 'websitetoolbox' );
		} else {
			add_post_meta( $post_ID, '_links_to_target', 'websitetoolbox' );
		}
		if($row_post_type) {
			update_post_meta( $post_ID, '_links_to_type', 'custom_post_type' );
		} else {
			add_post_meta( $post_ID, '_links_to_type', 'custom_post_type' );
		}
		if(!$row_post_type) {
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
			$page->post_content = '<script type="text/javascript" data-name="forumEmbedScript" id="embedded_forum" src="'.$wtb_url.'js/mb/embed.js"></script><noscript><a href="'.$wtb_url.'">Forum</a></noscript>';
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
	}
	
	#get Website Toolbox forum information from option table
	$websitetoolbox_username = get_option("websitetoolbox_username");
	$websitetoolbox_api		 = get_option("websitetoolbox_api");
	$websitetoolbox_url		 = get_option("websitetoolbox_url");
	$websitetoolbox_redirect = get_option("websitetoolbox_redirect");
	if($websitetoolbox_redirect == 1) {
		$check_if = "checked";
	} else {
		$check_if = "";
	}
	if($post_ID) {
		echo "<div id='setting-error-settings_updated' class='updated settings-error'><p>Your settings have been saved.</p></div>";
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
	#create a form in the wordpress admin panel
	echo <<<STUFF
	<OL><LI><a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum on Website Toolbox</a> or <a href="http://www.websitetoolbox.com/tool/members/login" target="_blank">login to your existing Website Toolbox forum</a>.</LI>
	<LI>Click the <i>Settings</i> link in the navigation menu at the top. In the Settings menu, select the <i>Single Sign On</i> option.</LI>
	<LI>On the Single Sign On settings page, specify the Login, Logout, and Registration page address (URL) of your WordPress website and <i>Save</i> your changes. If your WordPress website doesn't have these pages, skip this step.</LI>
	<LI>Copy the <i>API Key</i> from the Single Sign On settings page and paste it into the <i>Forum API Key</i> text box on this WordPress plugin setup page.</LI>
	<LI>Provide your <i>Website Toolbox Username</i> and <i>Forum Address</i>  in the text boxes below and click the <i>Update</i> button.</LI>
	<p>Please <a href="http://www.websitetoolbox.com/contact?subject=WordPress+Plugin+Setup+Help" target="_blank">Contact Customer Support</a> if you need help getting setup.</p></OL>
	<form name="form_lol" method="post" action="options-general.php?page=websitetoolboxoptions" style="margin: 15px 0">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr><td width="15%"><strong>Website Toolbox Username:</strong></td><td style="padding-top:10px;"><input type="text" name="websitetoolbox_username" id="websitetoolbox_username" value="$websitetoolbox_username" size="50"/></td><td style="padding-left:5px; padding-right:5px; padding-top:10px;" ><a href="http://www.websitetoolbox.com/message_board/forum.html?wordpress" target="_blank">Create a forum at Website Toolbox</a> to get your username. </td></tr>
			<tr><td width="15%" style="padding-top:10px;"><strong>Forum API Key:</strong></td><td style="padding-top:10px;"><input type="text" name="websitetoolbox_api" id="websitetoolbox_api" value="$websitetoolbox_api" size="50"/></td><td style="padding-left:5px; padding-right:5px; padding-top:10px;">Get your <a href="http://www.websitetoolbox.com/support/252" target="_blank">API key</a>.</td></tr>
			<tr><td width="15%" style="padding-top:10px;"><strong>Forum Address:</strong></td><td style="padding-top:10px;" ><input type="text" name="websitetoolbox_url" id="websitetoolbox_url" value="$websitetoolbox_url" size="50"/></td><td style="padding-left:5px; padding-right:5px; padding-top:10px;">You can get your Forum address by visiting the dashboard of your Website Toolbox account. </td></tr>
			<tr><td width="15%" style="padding-top:10px;"><strong>Embed the forum:</strong></td><td style="padding-top:10px;" ><input type="checkbox" name="websitetoolbox_redirect" id="websitetoolbox_redirect" value="1" $check_if /></td><td style="padding-left:5px; padding-right:5px; padding-top:10px;">Enable this option to have your forum load within an iframe on your website. <br>Disable this option to have your forum load in a full-sized window. You can use the Layout section in your Website Toolbox account to <a href="http://www.websitetoolbox.com/support/148" target="_blank">customize your forum layout to match your website</a> or <a href="http://www.websitetoolbox.com/contact?subject=Customize+Forum+Layout" target="_blank">contact Website Toolbox support to customize it for you</a>. </td></tr>
			<tr><td style="padding-top:10px;">&nbsp;</td><td style="padding-top:10px;"><input type="submit" name="submit" class="button-primary" value="Update" onClick="return ValidateForm();"/> </td></tr>
		</table> 
	</form>
STUFF;
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
	require("websitetoolbox_sso.php");
	$user_obj = new WP_User(0,$user_login);
	$username = urlencode($user_obj->user_login);
	$display_name = urlencode($user_obj->display_name); # ie: John Doe
	$first_name = urlencode($user_obj->first_name); # ie: John
	$last_name = urlencode($user_obj->last_name); # ie: Doe
	
	$forum_username = get_option("websitetoolbox_username");
	$forum_api		= get_option("websitetoolbox_api");
	$forum_url		= get_option("websitetoolbox_url");
	
	// Remove http:// or https:// from the from URL if exists.
	$HOST = preg_replace('#^https?://#', '', $forum_url);
	
	$URL = "/register/setauthtoken?apikey=".$forum_api."&user=".$username;
	$response = doHTTPCall($URL,$HOST);
	#Parse XML response 
	function filter_xml($matches) {
		return trim(htmlspecialchars($matches[1]));
	} 
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
			setcookie('wt_login_remember', "checked", 0);
		}		
		setcookie("wt_logout_token", $resultdata, 0);
		#Save authentication token into session variable.
		save_authtoken('login',$resultdata);
		return true;
	}	
}

#logged-out a new user on the related forum
function wt_logout_user() {
	/* create a cookie variable if user successfully logged-out from WordPress site to sent logged-out request on the related websitetoolbox forum */
	save_authtoken('logout',$_COOKIE['wt_logout_token']);
	return false;
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
	require("websitetoolbox_sso.php");
	$user_obj = new WP_User($userid);
	$forum_username = get_option("websitetoolbox_username");
	$forum_api		= get_option("websitetoolbox_api");
	$forum_url		= get_option("websitetoolbox_url");
	
	#remove http:// or https:// from the URL if exists.
	$HOST = preg_replace('#^https?://#', '', $forum_url);
	$login_id = $user_obj->ID;
	$login 	  = $user_obj->user_login; # ie: JohnD223
	$password = $user_obj->user_pass;
	$email 	  = $user_obj->user_email;
	$display_name = $user_obj->display_name; # ie: John Doe
	$first_name = $user_obj->first_name; # ie: John
	$last_name = $user_obj->last_name; # ie: Doe
	
	$parameters = "&member=".urlencode($login); # this is the username displayed on the forum
	$parameters .= "&apikey=".$forum_api;
	$parameters .= "&pw=".urlencode($password);
	$parameters .= "&email=".urlencode($email);
	$URL = "/register/create_account?".$parameters;
	$response = doHTTPCall($URL,$HOST);
	
	function filter_xml($matches) {
		return trim(htmlspecialchars($matches[1]));
	} 
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
				$link = trim($matchedID['_links_to']);
				if($matchedID['_links_to_target'] == 'websitetoolbox') {
					$newURL =  trim($matchedID['_links_to']);
				} else {
					$newURL = esc_url( $newURL );
				}
			} else {
				if($matchedID['_links_to_target'] == 'websitetoolbox') {
					$link = esc_url( $newURL );
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

/* Purpose: This function is used to set authentication token into session variable if user logged-in/logged-out.
Param1: type (login/logout)
Param2: authentication token
Return: Nothing */
function save_authtoken($type, $authtoken) {
	if($type=='login') {
		$_SESSION['wtb_login_auth_token'] = $authtoken;
	} else if($type=='logout') {
		$_SESSION['wtb_logout_auth_token'] = $authtoken;
	}
}

/* Purpose: This function is used to unset session variable if user logged-in/logged-out.
Param1: type (login/logout)
Return: Nothing */
function clean_authtoken($type) {
	if($type=='login') {
		unset($_SESSION['wtb_login_auth_token']);	
	} else if($type=='logout')	{
		unset($_SESSION['wtb_logout_auth_token']);
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
		echo "<img src='".$login_auth_url."' border='0' width='0' height='0' alt=''>";
		/* remove authentication token from session variable so that above image tag not write again and again */
		clean_authtoken('login');
		return false;
	}
	if($_SESSION['wtb_logout_auth_token']) {
		$logout_auth_url = get_option(websitetoolbox_url)."register/logout?authtoken=".$_SESSION['wtb_logout_auth_token'];
		/* Print image tag on the header section sent logout request on the related forum */
		echo "<img src='".$logout_auth_url."' border='0' width='0' height='0' alt=''>";
		clean_authtoken('logout');
		return false;
	}
}

/* Define Hook to get user information */
/* wp_login hook called when user logged-in into wordpress site (front end/back end) */
add_action('wp_login','wt_login_user');
/* wp_logout hook called when user logged-out from wordpress site (front end/back end) */
add_action('wp_logout','wt_logout_user');
/* user_register hook called when a new account creates from from wordpress site (front end/back end) */
add_action('user_register', 'wt_register_user');
/* admin_notices to print notice(message) on admin section */
add_action('admin_notices', 'wtb_warning');
/* print IMG tags to the footer if needed */
add_action('wp_footer','ssoLoginLogout');
add_action('admin_footer','ssoLoginLogout');

register_activation_hook( __FILE__, 'websitetoolbox_activate' );
register_deactivation_hook( __FILE__, 'websitetoolbox_deactivate' );