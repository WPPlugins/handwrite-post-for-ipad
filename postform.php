<?php
/**
 * Post advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

wp_enqueue_script('post');

if ( post_type_supports($post_type, 'editor') ) {
	if ( user_can_richedit() )
		wp_enqueue_script('editor');
	wp_enqueue_script('word-count');
}

if ( post_type_supports($post_type, 'editor') || post_type_supports($post_type, 'thumbnail') ) {
	add_thickbox();
	wp_enqueue_script('media-upload');
}

/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$temp_ID = isset($temp_ID) ? (int) $temp_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';

$messages = array();
$messages['post'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Post saved.'),
	 8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
$messages['page'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Page updated. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Page updated.'),
	 5 => isset($_GET['revision']) ? sprintf( __('Page restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Page published. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Page saved.'),
	 8 => sprintf( __('Page submitted. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Page draft updated. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);

$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) )
		$message = $messages[$post_type][$_GET['message']];
	elseif ( !isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]) )
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ( 'auto-draft' == $post->post_status ) {
	if ( 'edit' == $action )
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action = 'editpost';
$nonce_action = 'update-' . $post_type . '_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post_ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	foreach ( _wp_post_revision_fields() as $autosave_field => $_autosave_field ) {
		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below.  <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
			break;
		}
	}
	unset($autosave_field, $_autosave_field);
}

$post_type_object = get_post_type_object($post_type);

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once('./includes/meta-boxes.php');

//add_meta_box('submitdiv', __('Publish'), 'post_submit_meta_box', $post_type, 'side', 'core');

// all taxonomies
foreach ( get_object_taxonomies($post_type) as $tax_name ) {
	$taxonomy = get_taxonomy($tax_name);
	if ( ! $taxonomy->show_ui )
		continue;

	$label = $taxonomy->labels->name;

	if ( !is_taxonomy_hierarchical($tax_name) )
		add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name ));
	else
		add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name ));
}

if ( post_type_supports($post_type, 'page-attributes') )
	add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', $post_type, 'side', 'core');

if ( current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' )
	&& ( ! is_multisite() || ( ( $mu_media_buttons = get_site_option( 'mu_media_buttons', array() ) ) && ! empty( $mu_media_buttons['image'] ) ) ) )
		add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', $post_type, 'side', 'low');

if ( post_type_supports($post_type, 'excerpt') )
	add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'trackbacks') )
	add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'custom-fields') )
	add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', $post_type, 'normal', 'core');

do_action('dbx_post_advanced');
if ( post_type_supports($post_type, 'comments') )
	add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', $post_type, 'normal', 'core');

if ( ('publish' == $post->post_status || 'private' == $post->post_status) && post_type_supports($post_type, 'comments') )
	add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', $post_type, 'normal', 'core');

if ( !( 'pending' == $post->post_status && !current_user_can( $post_type_object->cap->publish_posts ) ) )
	add_meta_box('slugdiv', __('Slug'), 'post_slug_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'author') ) {
	$authors = get_editable_user_ids( $current_user->id ); // TODO: ROLE SYSTEM
	if ( $post->post_author && !in_array($post->post_author, $authors) )
		$authors[] = $post->post_author;
	if ( ( $authors && count( $authors ) > 1 ) || is_super_admin() )
		add_meta_box('authordiv', __('Author'), 'post_author_meta_box', $post_type, 'normal', 'core');
}

if ( post_type_supports($post_type, 'revisions') && 0 < $post_ID && wp_get_post_revisions( $post_ID ) )
	add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', $post_type, 'normal', 'core');

do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);
do_action('do_meta_boxes', $post_type, 'side', $post);

if ( 'post' == $post_type ) {
	add_contextual_help($current_screen,
	'<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes that allow you to add metadata to your post using drag and drop, and can minimize or expand them by clicking the title bar of the box. You can also hide any of the boxes by using the Screen Options tab, where you can also choose a 1- or 2-column layout for this screen.') . '</p>' .
	'<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>' .
	'<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions.') . '</p>' .
	'<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>' .
	'<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>' .
	'<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>' .
	'<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>' .
	'<p>' . sprintf(__('You can also create posts with the <a href="%s">Press This bookmarklet</a>.'), 'options-writing.php') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Writing_Posts" target="_blank">Documentation on Writing Posts</a>') . '</p>' .
	'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
	);
} elseif ( 'page' == $post_type ) {
	add_contextual_help($current_screen, '<p>' . __('Pages are similar to Posts in that they have a title, body text, and associated metadata, but they are different in that they are not part of the chronological blog stream, kind of like permanent posts. Pages are not categorized or tagged, but can have a hierarchy. You can nest Pages under other Pages by making one the &#8220;Parent&#8221; of the other, creating a group of Pages.') . '</p>' .
	'<p>' . __('Creating a Page is very similar to creating a Post, and the screens can be customized in the same way using drag and drop, the Screen Options tab, and expanding/collapsing boxes as you choose. The Page editor mostly works the same Post editor, but there are some Page-specific features in the Page Attributes box:') . '</p>' .
	'<p>' . __('<strong>Parent</strong> - You can arrange your pages in hierarchies. For example, you could have an &#8220;About&#8221; page that has &#8220;Life Story&#8221; and &#8220;My Dog&#8221; pages under it. There are no limits to how many levels you can nest pages.') . '</p>' .
	'<p>' . __('<strong>Template</strong> - Some themes have custom templates you can use for certain pages that might have additional features or custom layouts. If so, you&#8217;ll see them in this dropdown menu.') . '</p>' .
	'<p>' . __('<strong>Order</strong> - Pages are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Pages_Add_New_SubPanel" target="_blank">Page Creation Documentation</a>') . '</p>' .
	'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
	);
}
require_once('./admin-header.php');
$num = get_option('hpfi-canvasFrames');
?>
<link rel="stylesheet" href="<?PHP echo plugins_url('hpfi-style.css', __FILE__) ?>" type="text/css" />
<script src="<?PHP echo plugins_url('js/savePost.js', __FILE__) ?>" type="text/javascript"></script>
<script src="<?PHP echo plugins_url('js/canvas.js', __FILE__) ?>" type="text/javascript"></script>
<script src="<?PHP echo plugins_url('js/jquery.blockUI.js', __FILE__) ?>" type="text/javascript"></script>
<script src="<?PHP echo plugins_url('js/jquery.jswipe-0.1.2.js', __FILE__) ?>" type="text/javascript"></script>
<script src="<?PHP echo plugins_url('js/moveImage.js', __FILE__) ?>" type="text/javascript"></script>
<script src="<?PHP echo plugins_url('js/rejects.js', __FILE__) ?>" type="text/javascript"></script>
<script type="text/javascript">
    <!--
    jQuery(document).ready(function(){
       <?PHP
       for ($i=0;$i<$num;$i++){
        echo ("jQuery('#canvas-image".$i."').click(function(){showCanvas('#canvas-div".$i."','#canvas-image".$i."')});\r\n");
        echo ("jQuery('#canvasSaveButton".$i."').click(function(){saveCanvas('".plugins_url('file-upload.php', __FILE__)."','#canvas-image".$i."','#canvas-div".$i."','image".$post_ID."-".$i."',".get_option('hpfi-ImageSizeW').",".get_option('hpfi-ImageSizeH').")});\r\n");
       }

       echo("jQuery('#left-arrow').click(function(){moveLeft(" . $num . ")});\r\n");
       echo("jQuery('#right-arrow').click(function(){moveRight(" . $num . ")});\r\n");
       echo("jQuery('.canvas-image').swipe({
               swipeLeft: function(){moveLeft(" . $num . ")},
               swipeRight:function(){moveRight(" . $num . ")}
            });");
       echo("jQuery('#readmore-after-leadtext').click(function() {
                var readmores = jQuery('.readmore');
                readmores.attr({'checked':''});
                jQuery('#readmore-after-leadtext').attr({'checked':'checked'});
            });");
       ?>

    });
    //-->
</script>
<div class="wrap">
    <div id ="icon-edit" class="icon32">
           <br>
    </div>
    <h2>handwrite post for ipad</h2>
    <form name="post" action="post.php" method="post" id="post"<?php do_action('post_edit_form_tag'); ?>>
        <?php wp_nonce_field($nonce_action); ?>
        <input type="hidden" id="user-id" name="user_ID" value="<?php echo esc_attr( $post->post_author ); ?>" />
        <input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr($form_action) ?>" />
        <input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr($form_action) ?>" />
        <input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
        <input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr($post_type) ?>" />
        <input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr($post->post_status) ?>" />
        <input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
        <input type="hidden" id="abspath" name="abspath" value="<?php echo ABSPATH; ?>" />
        <?php
            if ( 'draft' != $post->post_status )
                    wp_original_referer_field(true, 'previous');

            echo $form_extra;

            wp_nonce_field( 'autosave', 'autosavenonce', false );
            wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        ?>
        <div id="poststuff" class="metabox-holder has-right-sidebar">

            <div id="post-body">
                <div id="post-body-content" style="margin-left: 150px;margin-right: 150px;text-align: right;">
                    <div id="titlediv">
                        <p>title:</p>
                        <input type="text" autocomplete="off" id="title" value="" tabindex="1" size="57" name="post_title">
                    </div>
                    <div id="main-content">
                        <textarea id="content" name="content" cols="40" class="theEditor" rows="10" style="display: none;"></textarea>
                        <div id="lead-text-div">
                            <p>lead text:</p>
                            <textarea id="leadtext" tabindex="2" name="leadtext" cols="57" rows="3"></textarea>
                        </div>
                        <label class="selectit" for="readmore-after-leadtext">
                            <input id="readmore-after-leadtext" type="checkbox" value="1" name="readmore-after-leadtext" class="readmore">
                            insert readmore
                        </label>
                        <div id="handwrite">
                            <img src="<?PHP echo plugins_url('images/left.png', __FILE__) ?>" class="arrow float-left" id="left-arrow">
                            <div id="handwrite-content" class="float-left">
                                <div  class=" handwrite-div float-left">
                                    <?PHP
                                    for($n=0;$n<$num;$n++){
                                        echo('<img id="canvas-image'.$n.'" class="canvas-image" src="'.plugins_url('images/noimage.png', __FILE__).'">');
                                    }
                                    ?>
                                </div>
                            </div>
                            <img src="<?PHP echo plugins_url('images/right.png', __FILE__) ?>" class="arrow" id="right-arrow">
                        </div>
                    </div>
                    <div style="display:none;">
                        <input id="comment_status" type="checkbox" checked="checked" value="open" name="comment_status">
                        <input id="ping_status" type="checkbox" checked="checked" value="open" name="ping_status">
                    </div>
                        <input type="hidden" value="公開" id="original_publish" name="original_publish">
                        <input type="button" value="公開" accesskey="p" tabindex="5" class="button-primary" onclick="savePost()">
                </div>
            </div>
        </div>
        <?PHP
            for($r=0;$r<$num;$r++){
                echo('<div id="canvas-div'.$r.'" class="canvas-div" style="display:none">');
                echo('<canvas id="canvas'.$r.'" width="881" height="661" class="canvasDefault">');
                    echo ('use ipad');
                echo('</canvas>');
                echo('<span class="palette1">');
                echo('<img id="pencil'.$r.'" class="pencil-black" src="'.  plugins_url('images/pencil.png', __FILE__) .'">');
                echo('<img id="eraser'.$r.'" class="eraser-black" src="'.  plugins_url('images/eraser.png', __FILE__) .'">');
                echo('</span>');
                echo('<p style="background:#FFFFFF">lineSize</p>');
                echo('<select id="lineSizeSelect'.$r.'" class="lineSizeSelect" >
                        <option value="1">1px</option>
                        <option value="2">2px</option>
                        <option value="4">4px</option>
                        <option value="8">8px</option>
                        <option value="16">16px</option>
                        <option value="32">32px</option>
                    </select>');
                echo('<p style="background:#FFFFFF">lineColor</p>');
                echo('<select id="lineColorSelect'.$r.'" class="lineColorSelect" >
                        <option value="black">black</option>
                        <option value="gray">gray</option>
                        <option value="maroon">maroon</option>
                        <option value="red">red</option>
                        <option value="olive">olive</option>
                        <option value="yellow">yellow</option>
                        <option value="green">green</option>
                        <option value="lime">lime</option>
                        <option value="teal">teal</option>
                        <option value="aqua">aqua</option>
                        <option value="navy">navy</option>
                        <option value="blue">blue</option>
                        <option value="purple">purple</option>
                        <option value="fuchsia">fuchsia</option>
                     </select>');
                if(!(($num-1)==$r)){
                    echo('<p style="background:#FFFFFF">insert readmore</p>');
                    echo('<input id="readmore'.$r.'" type="checkbox" value="1" class="readmore">');
                }
                echo('<p style="background:#FFFFFF">save or cancel</p>');
                echo('<input id="canvasSaveButton'.$r.'" class="canvas-save-button" type="button" value="save"><br>');
                echo('<input id="canvasCancelButton'.$r.'" class="canvas-cancel-button" type="button" value="cancel">');
                echo('<input type="hidden" class="my-html">');
                echo('</div>');
            }
        ?>
    </form>
</div>


