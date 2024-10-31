=== Plugin Name ===
Contributors: rpayne7264
Tags: linkedin,linkedin groups,rdp linkedin,rdp groups+,rdp ingroups+,rdp linkedin groups,rdp linkedin groups+,ingroups+,
Requires at least: 3.0
Tested up to: 4.3.1
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate LinkedIn groups into WordPress
== Description ==

`On October 14, 2015, LinkedIn converted all groups to private, 
which has made the groups display functionality of this plug-in 
impossible.  However, the Sign in with Linked functionality still
works perfectly fine.  The associated sign-in and registration hooks 
offer great flexibility for utilizing user data in custom coding.`

**This plugin is deprecated. Please use [RDP Linkedin Login](https://wordpress.org/plugins/rdp-linkedin-login/).**


RDP inGroups+ provides:

* Login button shortcode - shows a *Sign in with LinkedIn* button when logged out
* Member Count shortcode, with the ability to designate a link URL
* Ability to register a visitor with the WordPress installaton
* Logged-in session security using nonces and client IP address
* OOP with hooks and filters for easy integration and customization
* Ability to add a list of company IDs that a registered user will automatically follow

= Warning About Caching =

This plug-in will not work if caching is enabled on a web site.


= Sponsor =

This plug-in brought to you through the generous funding of [Laboratory Informatics Institute, Inc.](http://www.limsinstitute.org/)


== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'RDP inGroups+'
3. Click the Install Now link.
3. Activate RDP inGroups+ from your Plugins page.


= From WordPress.org =

1. Download RDP inGroups+.
2. Upload the 'rdp-linkedin-groups' directory to your '/wp-content/plug-ins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate RDP inGroups+ from your Plugins page.


= After Activation - Go to 'Settings > RDP inGroups+' and: =

1. Get LinkedIn Application API keys using the link and settings shown at the top of the settings page.
2. Enter API Key.
3. Enter Secret Key.
4. Set other configurations as desired.
5. Click 'Save Changes' button.
6. Add the [rdp-ingroups-group] shortcode to a page and save the page.


= Extra =

1. Adjust the CSS widths on the settings page to make everything pretty, if necessary.
2. For more control, add an ingroups.custom.css file to your theme's folder. Start with the ingroups.custom-sample.css file located in the 'rdp-linkedin-groups/pl/style/' directory.



= Special Notes About Company Auto-Follow Feature =

To have a user auto-follow companies, *Register New Users?* must be enabled.

The auto-follow feature is a one-time process for each user who is registered with the WordPress installation. Adding new company IDs will not retroactively join existing site users to the newly added companies.



== Frequently Asked Questions ==

= So what does your plugin offer? =

It'll pull content from publicly viewable groups by scrapping LinkedIn, with the content in both HTML format for display on a page and RSS format for displaying a feed.

= How do I access the RSS feed designed for Mailchimp newsletters? =

Click on the RSS icon in the group header to open the standard RSS feed in a browser window. Now, modify the URL in the newly opened window by changing rss.php to rss_1.php.


== Usage ==

= LinkedIn Group Discussions =
RDP inGroups+ is implemented using the shortcode [rdp-ingroups-group]. It accepts the following arguments:

* id: (required) a group ID

Examples:

[rdp-ingroups-group id=2069898]

This will display discussions of the designated group to site visitors. The shortcode will display a *Sign in with LinkedIn* button if the user is not logged in.

= LinkedIn Group Member Count =
For a display of a group's member count, use the [rdp-ingroups-member-count] shortcode. It accepts the following arguments:

* id: (required) a group ID
* link: a url to make the member count a hyperlink
* new: make the link open in a new tab
* prepend: text to add to the front of the member count
* append: text to add to the end of the member count

Examples:

[rdp-ingroups-member-count id=209217]

[rdp-ingroups-member-count id=209217 link=http://example.com]

[rdp-ingroups-member-count id=209217 link=http://example.com new]

[rdp-ingroups-member-count id=209217 link=http://example.com new prepend='Join our' append='TODAY!']


= LinkedIn Sign In Button =

To display a *Sign in with LinkedIn* button, use the [rdp-ingroups-login] shortcode.



== Screenshots ==

1. Login button shortcode in sidebar. 
2. Group shortcode, using the ID attribute to specify a LinkedIn group, embedded in a page. Notice that the user is not logged in. The first ten discussions have been scrapped from LinkedIn and displayed. If the group is open, a public RSS feed is made available, displaying the five most recent discussions.
3. Group shortcode, using the ID attribute to specify a LinkedIn group, embedded in a page and with the user logged in.
4. Popup action menu for logged-in user. Additional custom links can be added with a little PHP coding.
5. Single discussion as seen by a non-logged-in visitor - must log in to see comments.
6. Single discussion as seen by a logged-in user.
7. Offer sharing of single discussions, using the Bitly API.
8. Settings page.
9. Button to launch the shortcode embed helper form
10. How to find a group ID
11. Discussion URL dissected


== Change Log ==

= 1.0.6 =
* added checks to prevent plugin from running code unnecessarily
* updated close script for popup login window

= 1.0.5 =
* added ability to do a redirect after log-in, by setting a rdp_lig_login_redirect cookie
* added jQuery Cookie plugin

= 1.0.4 =
* minor code modifications

= 1.0.3 =
* minor fixes and modifications

= 1.0.2 =
* re-worked code to address log-in loop experienced by some users

= 1.0.1 =
* minor code re-factor

= 1.0.0 =
* added prepend attribute and append attribute to the member count shortcode
* added 30 minute caching to member count shortcode
* added attributes parameter to rdp_lig_render_member_count filter
* re-factored code so that the group discussions shortcode outputs results as expected, rather than writing directly to the browser
* added ability to sort group discussions by recency and popularity
* added paging to discussion comments
* added comment count and like count to posts' meta data
* fixed bug with popup shortcode helper form
* allow paging of group discussions for non-logged-in visitors
* added code to ensure consistency with WP user logged-in/logged-out state
* added images to help identify group IDs and discussion IDs
* re-factored code for RSS feed output

= 0.7.0 =
* re-worked code to pull discussions from group home pages
* added setting option to control display of Manager's Choice items in discussion lists and RSS feeds

= 0.6.3 =
* minor bug fix

= 0.6.2 =
* modification to make the shortcode media button show up for all post types

= 0.6.1 =
* modification to Sign Out links to continue displaying current group or discussion after log out

= 0.6.0 =
* removed code to re-write hyperlinks with a cache-busting query string parameter

= 0.5.2 =
* minor bug fixes
* updated sign-in procedure to update usermeta table with current LinkedIn picture URL and public profile URL

= 0.5.1 =
* added ability to detect if BuddyPress is active

= 0.5.0 =
* Initial RC


== Upgrade Notice ==

== Other Notes ==

== External Scripts Included ==
* jQuery Cookie Plugin v1.4.1 under MIT License
* jQuery.PositionCalculator v1.1.2 under MIT License
* Query Object v2.1.8 under WTFPL License
* URL v1.8.6 under MIT License

== Hook Reference: ==

= rdp_lig_before_user_login =

* Param: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id
* Fires before any user is logged into the site via LinkedIn.

= rdp_lig_after_insert_user =

* Param: WP User Object
* Fires after a new user is registered with the site. *(Register New Users? must be enabled)*

= rdp_lig_after_registered_user_login =

* Param: WP User Object
* Fires after a registered user is logged into the site. *(Register New Users? must be enabled)*

= rdp_lig_registered_user_login_fail =

* Param: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id
* Fires after a failed attempt to log registered user into the site. *(Register New Users? must be enabled)*

= rdp_lig_after_user_login =

* Param: RDP_LIG_DATAPASS object
* Fires after any user is logged into the site via LinkedIn.

= rdp_lig_after_scripts_styles =

* Param: none
* Fires after enqueuing plug-in-specific scripts and styles

== Filter Reference: ==

= rdp_lig_render_header_top =

* Param 1: String containing opening div and wrapper HTML for header section
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: opening HTML for header section

= rdp_lig_render_header =

* Param 1: String containing the body HTML for header section
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: body HTML for header section

= rdp_lig_render_header_bottom =

* Param 1: String containing closing wrapper and div HTML for header section
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: closing HTML for header section

= rdp_lig_render_main_container_header =

* Param 1: String containing HTML for main container header section
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: HTML for main container header section
* Default behavior is to render the group profile logo and name

= rdp_lig_render_member_count =

* Param 1: String containing HTML for member count
* Param 2: Array containing shortcode attributes
* Return: HTML for member count.

= rdp_lig_render_paging =

* Param 1: String containing HTML for paging section
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Param 3: String containing the location - 'top' of main container section, 'bottom' of main container section
* Return: HTML for paging section. For infinity paging, location 'top' is not rendered.

= rdp_lig_render_login =

* Param 1: String containing log-in HTML for the **[rdp-ingroups-login]** shortcode
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: log-in HTML for the **[rdp-ingroups-login]** shortcode

= rdp_lig_before_insert_user =

* Param 1: Boolean indicating if user exists based on result of Wordpress username_exists() function, using supplied email address 
* Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id
* Return: Boolean indicating if user exists

= rdp_lig_before_registered_user_login =

* Param 1: Boolean indicating if user is logged in based on result of Wordpress is_user_logged_in() function
* Param 2: String containing email address of user
* Return: Boolean indicating if user is logged in

= rdp_lig_custom_menu_items =

* Param 1: Array to hold custom link data
* Param 2: String containing status - 'true' if user is logged in, 'false' otherwise
* Return: Array of links, where the link text is the key and the link URL is the value


== Javascript Function Reference: ==

= rdp_lig_login_onClose =

* Param: redirect URL
* Fires upon successful login, just before the login popup window closes.

== Redirect Code Example ==

In this example, all links with class rdp_jb_must_sign_in are assigned an event listener that sets a cookie, with the cookie value derived from the link's href attribute.

When the popup login window executes its close script, the cookie is read, and the parent window is redirected to the appropriate URL.

= Code in custom sign-in JavaScript file =

`

var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_sign_in_onLoad);

function rdp_sign_in_onLoad(){
    $j('#rdp-jb-main').on( "click", '.title.rdp_jb_must_sign_in' , function(event){
        event.preventDefault();  
        var redirectURL = $j(this).attr('href');
        jQuery.cookie('rdp_lig_login_redirect', redirectURL, { path: '/' })
    });
}//rdp_sign_in_onLoad

`

