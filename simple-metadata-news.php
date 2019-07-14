<?php

/**
 * Simple Metadata - News
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/my-language-skills/simple-metadata-news
 * @since             0.1
 * @package           simple-metadata-news
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Metadata - News
 * Plugin URI:        https://github.com/my-language-skills/simple-metadata-news
 * Description:       Simple Metadata add-on for news sites. This plugin makes your news posts more understandable for search engines by telling wich kind of news article the given one is and adding automatically generated metadata based on post information.
 * Version:           1.0
 * Author:            My Language Skills team
 * Author URI:        https://github.com/my-language-skills/
 * License:           GPL 3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       simple-metadata-news
 * Domain Path:       /languages
 */

defined ("ABSPATH") or die ("No script assholes!");

/**
* Function for creation of metabox to pick type of news post for proper Schema.org schema type.
*
* @since
*
*/

function aiex_add_news_post_type_meta () {

	add_meta_box (
		'aiex_news_post_type', //Unique ID
		__('Type Of News Article', 'simple-metadata-news'), //Title
		'aiex_render_news_post_type_meta', //Callback function
		'post', //for pages
		'side', //Context
		'high' //priority
	);
}

/**
* Summary.
*
* @since
*
*/

function aiex_render_news_post_type_meta ($object, $box) {
	//creating nonce
	wp_nonce_field( basename( __FILE__ ), 'aiex_render_news_post_type_meta' );

	$news_type = esc_attr(get_post_meta ($object->ID, 'aiex_news_post_type', true));
	$news_types = array(
					'NewsArticle'				=> __('General News Article', 'simple-metadata-news'),
					'AnalysisNewsArticle' 		=> __('Analysis Article', 'simple-metadata-news'),
					'AskPublicNewsArticle' 		=> __('"Ask Public" Article', 'simple-metadata-news'),
					'BackgroundNewsArticle' 	=> __('Background Article', 'simple-metadata-news'),
					'ReportageNewsArticle'		=> __('Reportage Article', 'simple-metadata-news'),
					'ReviewNewsArticle'			=> __('Review Article', 'simple-metadata-news'),
				  );
	?>
		<p><?php esc_html_e('News Post Type', 'simple-metadata-news'); ?></p>
			<select style="width: 90%;" name="aiex_news_post_type" id="aiex_news_post_type">
				<?php
					foreach ($news_types as $key => $value) {
						$selected = $news_type == $key ? 'selected' : '';
						echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
					}
				?>
			</select>
	<?php
}

/**
* Function for post saving/updating action.
*
* @since
*
*/

function aiex_save_news_post_type ($post_id, $post) {

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['aiex_render_news_post_type_meta'] ) || !wp_verify_nonce( $_POST['aiex_render_news_post_type_meta'], basename( __FILE__ ) ) ){
		return $post_id;
	}

	//fetching old and new meta values if they exist
	$new_meta_value = isset($_POST['aiex_news_post_type']) ? sanitize_text_field ($_POST['aiex_news_post_type']) : '';
	$old_meta_value = get_post_meta ($post_id, 'aiex_news_post_type', true);

	if ( $new_meta_value && '' == $old_meta_value ) {
		add_post_meta( $post_id, 'aiex_news_post_type', $new_meta_value, true );
	} elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
		update_post_meta( $post_id, 'aiex_news_post_type', $new_meta_value );
	}
}

/**
* Summary.
*
* @since
*
*/

function aiex_print_news_post_meta_fields () {

	if ('post' == get_post_type(get_the_ID())) {

		$news_type = get_post_meta(get_the_ID(), 'aiex_news_post_type', true) ?: 'empty';

		if ('empty' == $news_type){
			return;
		}

		$post_id = get_the_ID();


		$post_content = get_post( $post_id )->post_content;
		$word_count = str_word_count($post_content);
		$categories = get_the_category( $post_id);
		$categories_arr = [];
		foreach ($categories as $category) {
			$categories_arr[] = $category->name;
		}
		$categories_string = implode(', ', $categories_arr);
		$key_words = wp_get_post_tags($post_id, ['fields' => 'names']);
		$key_words_string = implode(', ', $key_words);

		$author_id = get_post_field('post_author', $post_id);
		$author = get_the_author_meta('first_name', $author_id) && get_the_author_meta('last_name', $author_id) ? get_the_author_meta('first_name', $author_id).' '.get_the_author_meta('last_name', $author_id) : get_the_author_meta('display_name', $author_id);
		$creation_date = get_the_date();
		$title = get_the_title();
		$last_modifier = get_the_modified_author();
		$thumbnail_url = get_the_post_thumbnail_url();
		$publication_date = get_the_time(get_option( 'date_format' ));;
		?>

		<div itemscope itemtype="http://schema.org/<?=$news_type;?>">
			<meta itemprop="articleBody" content="<?=$post_content;?>">
			<meta itemprop="author" content="<?=$author;?>">
			<meta itemprop="dateCreated" content="<?=$creation_date;?>">
			<meta itemprop="headline" content="<?=$title;?>">
			<meta itemprop="editor" content="<?=$last_modifier;?>">
			<meta itemprop="thumbnailUrl" content="<?=$thumbnail_url;?>">
			<meta itemprop="image" content="<?=$thumbnail_url;?>">
			<meta itemprop="datePublished" content="<?=$publication_date?>">
			<meta itemprop="keywords" content="<?=$key_words_string?>">
			<meta itemprop="articleSection" content="<?=$categories_string?>">
			<meta itemprop="wordCount" content="<?=$word_count?>">
			<div itemprop="mainEntityOfPage" itemscope itemtype="http://schema.org/WebPage"></div>
		</div>

		<?php
	}

}

add_action ('add_meta_boxes', 'aiex_add_news_post_type_meta');
add_action ('save_post', 'aiex_save_news_post_type', 10, 2);
add_action ('wp_head', 'aiex_print_news_post_meta_fields');


/**
 * Internalization.
 * It loads the MO file for plugin's translation.
 *
 * @since 1.0
 *
 */

	function aiex_load_plugin_textdomain() {
    load_plugin_textdomain( 'simple-metadata-news', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
 * Called when the activated plugin has been loaded
 */
add_action( 'plugins_loaded', 'aiex_load_plugin_textdomain' );
