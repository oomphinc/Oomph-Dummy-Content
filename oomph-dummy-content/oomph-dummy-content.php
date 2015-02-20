<?php
/*
Plugin Name: Oomph Dummy Content
Plugin URI: http://thinkoomph.com/wordpress/oomph-dummy-content
Description: Generate all sorts of dummy content. Formatted text and images, powered by LorIpsum.net and Flickr.
Version: 1
Author: Ben Doherty @ Oomph, Inc
Author URI: http://thinkoomph.com/ben-doherty
License: GPLv2 or later

		Copyright © 2012 Oomph, Inc. <http://oomphinc.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @package Oomph Dummy Content
 */
class Oomph_Dummy_Content {
	static $instance;

	var $version = '0.0.1';

	var $defaults = array(
		'post_count' => 5,
		'term_count' => 2,
		'para_count' => 8,
		'photo_count' => 2,
		'spread' => 72,
		'flickrkey' => '4698bf96b92aad7dab65a3c23912b784'
	);

	var $async = false;
	var $sideloaded = array();

	var $meta_key_dummy = '_oomph_dummy_content';

	function __construct() {
		add_action('admin_menu', array(&$this, 'options_page'));
		add_filter('admin_head', array(&$this, 'admin_head'));
		add_action('admin_init', array(&$this, 'admin_init'));
	}

	function admin_init() {
		if( isset( $_GET['action'] ) && $_GET['action'] == 'iframe' )  {
			$this->async = true;
			$this->dispatch();
		}
	}

	function option( $key, $newvalue = null ) {
		$options = get_option( 'oomph-dummy-content' );

		if( !is_array( $options ) ) 
			$option = array();

		if( $newvalue !== null ) {
			$options[$key] = $newvalue;	
			update_option( 'oomph-dummy-content', $options );
			return $newvalue;
		}

		if( isset( $options[$key] ) )
			$value = $options[$key];
		else
			$value = $this->defaults[$key];
	
		return $value;
	}

	function param( $name, $key = null ) {
		if( isset( $_GET['action'] ) && $_GET['action'] == 'iframe' ) {
			if( isset( $_GET[$name] ) ) $val = $_GET[$name];
		}
		else if( isset( $_POST[$name] ) ) {
			if( isset( $_POST[$name] ) ) $val = $_POST[$name];
		}

		if( isset( $val ) ) {
			if( !is_null( $key ) ) 
				return $val[$key];
			else
				return $val;
		}

		return null;
	}

	function dispatch() {
		foreach( array( 'settings' => 'save_settings', 'clear' => 'clear', 'generate' => 'generate' ) as $form => $function ) {
			if( wp_verify_nonce( $this->param( '_wpnonce' ), $form.'-nonce' ) ) {
				if( $this->async ) $this->async = $form;
				if( $this->async xor isset( $_POST[$form.'-nonce'] ) ) {
					$this->$function();
				}
				if( $this->async ) exit(0);
			}
		}
	}

	function post_types() {
		$types = array();
		
		foreach( get_post_types(array('public' => true), 'objects') as $type ) {
			if( $type->name == 'attachment' ) continue;

			$types[] = $type;
		}

		return $types;
	}
	
	function result( $message ) { 
		if( $this->async ) {
	?>
	<script>
	window.parent.addResult( '<?php echo esc_js( $this->async ); ?>', '<?php echo esc_js( $message ); ?>' );
	</script>
	<?php
			flush();
		} else {
			echo $message;
		}
	}

	function percolate( $content, $target = null ) {
		if( !$target ) $target = $this->async;
	?>
	<script>
		window.parent.replaceContent( '<?php echo $target; ?>', '<?php echo esc_js( $content ); ?>');
	</script>
	<?php
	}

	function options_page() {
		add_options_page('Oomph Dummy Content '.$this->version, 'Dummy Content', '8', __FILE__, array($this, 'content_admin'));
	}

	function admin_head() { 
		$plugin_url = plugin_dir_url('oomph-dummy-content/wtf?');
		$admin_images = admin_url('images');
	?>
	<style>

 
.oomph-plugin #poststuff h2 { font-family: "HelveticaNueue", "Helvetica", sans-serif; border-bottom: 2px solid #D2000C; color: #444444; text-shadow: none; padding: 5px !important; }
.oomph-dummy-content-settings { width: 100%; position: relative; border-bottom: 1px solid #999999; margin-bottom: 1.5em; }
	.oomph-dummy-content-settings .results { position: relative; padding: 0 20px;  }
	.oomph-dummy-content-settings .results-loading { background: url(<?php echo $plugin_url; ?>spinner.gif) no-repeat top left; }
	.oomph-dummy-content-settings .results .close { background:#21759B url(<?php echo $admin_images; ?>/button-grad.png) repeat-x; border-color: #13455B; color: #EAF2FA; text-shadow; rgba(0, 0, 0, 0.3) 0 -1px 0; border-radius: 11px; padding: 3px 8px; font-weight: bold; cursor: pointer; margin-left: -15px; margin-top: 10px; clear: both; }
	.oomph-dummy-content-settings .results .close:active { background:#21759B url(<?php echo $admin_images; ?>/button-grad-active.png) repeat-x; }
	.oomph-dummy-content-settings .section { clear: both; margin-bottom: 20px; }
	.oomph-dummy-content-settings .row,
	.oomph-dummy-content-settings .tile { padding: 15px; margin: 10px; border-radius: 4px; background-color: #fff; border: 1px solid #ddd; overflow: hidden; } 
	.oomph-dummy-content-settings .tile { float: left; width: 300px; }
	.oomph-dummy-content-settings .flickr-key { height: 45px; }
	.oomph-dummy-content-settings .time-spread { line-height: 45px; }
		.oomph-dummy-content-settings .tile li { padding-left: 15px; }
		.oomph-dummy-content-settings .split { width: 50%; float: left; }
	.oomph-dummy-content-settings .connections { margin-top: 10px; }
	.oomph-dummy-content-settings .options { padding: 10px; background-color: #fff; border: 1px solid #ddd; box-shadow: 2px 2px 3px rgba(0, 0, 0, .5); border-radius: 5px;	}
	.oomph-dummy-content-settings .options .point { position: absolute; left: -10px; top: 50%; background: url(<?php echo $plugin_url; ?>/point.png) no-repeat; width: 18px; height: 29px; margin-top: -19px; }
		.oomph-dummy-content-settings .options p { text-indent: 5px; padding: 0; margin: 8px 0 0; }
		.oomph-dummy-content-settings .options li { }
	.oomph-dummy-content-settings .content-options { float: none; width: auto; clear: both; }
		.oomph-dummy-content-settings .content-options label { display: inline-block; min-width: 6em; }
		.oomph-dummy-content-settings .content-options li a { display: none; }
		.oomph-dummy-content-settings .content-options li:hover a { display: inline-block; }
.term-list li { list-style: none; display: inline; padding: 2px 10px; }

@media screen and (max-width: 768px) {
	.oomph-dummy-content-settings .tile { float: none; width: 250px; }
		.oomph-dummy-content-settings .flickr-key { height: auto; }
		.oomph-dummy-content-settings .time-spread { line-height: 1em; }
}
	</style>
	<?php 
	}

	function dummy_content_count() {
		global $wpdb;

		$counts = array();
		foreach( $wpdb->get_results( $wpdb->prepare( "SELECT post_type, COUNT(*) AS count FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id WHERE pm.meta_key=%s", $this->meta_key_dummy ) ) as $result )
			if( $result->post_type && $result->count )
				$counts[] = $result;

		$dummy_terms = get_option( 'oomph_dummy_terms' );

		if( $this->async ) 
			ob_start();

		?>
		<div class='existing-content section'>
			<h3>Existing Dummy Content:</h3>
			<?php if( !empty( $counts ) || !empty( $dummy_terms ) ) { 
				if( !empty( $counts ) ) { ?>
					<p>Dummy posts:</p>
					<?php foreach( $counts as $result ) { ?>
					<p>Post type <strong><?php echo esc_html( $result->post_type ); ?></strong>: <strong><?php echo esc_html( $result->count ); ?></strong> entries</p>
				<?php } 
				}
	 
				if( !empty( $dummy_terms ) ) { ?>
					<p>Dummy terms:</p>
					<?php foreach( $dummy_terms as $taxonomy => $terms ) { ?>
					<p>In <strong><?php echo esc_html( $taxonomy ); ?></strong>: <strong><?php echo count( $terms ); ?></strong> terms.</p>
				<?php
					}
				} ?>

			<input class=button" type="submit" value="Delete Dummy Content" name="dummy-content-clear"  />
			<?php 
		} 
		else { ?>
			<p>There is currently no existing dummy content.</p>
		<?php } ?>
	 </div>

	 <?php
		if( $this->async ) {
			$content = ob_get_contents();
			ob_end_clean();

			$this->percolate( $content, 'clear' );
		}
	}

	

	// Gather a collection of $count short phrases. If $length is specified,
	// then limit phrases to $length words
	function gather( $count, $length = 0 ) {
		$phrases = array();

		$result = wp_remote_get('http://loripsum.net/api/'.$count.'/short');
		foreach( explode("\n\n", trim($result['body'])) as $phrase ) {
			// Extract sentences
			$sentences = preg_split('/\s*[\.?:]\s*/', strip_tags($phrase), null, PREG_SPLIT_NO_EMPTY );
			$sentence = $sentences[(int)mt_rand(0, count($sentences)-1)];

			if( $length ) {
				$words = explode( ' ', $sentence);

				if( $length > 1 )
					$phrase = implode( ' ', array_slice( $words, 0, min($length, count($words)) ) );
				else
					$phrase = implode( ' ', array_slice( $words, 0, min((int)mt_rand(0,abs($length)), count($words)) ) );
				if( empty( $phrase ) ) $phrase = $sentence;
			}
			else {
				$phrase = $sentence;
			}

			$phrase = preg_replace('/[,;:]\s*$/', '', $phrase);
			$phrases[] = $phrase;
		}
		return $phrases;
	}


	function gatherphotos( $count ) {
		$flickrkey	= $this->option( 'flickrkey' );

		if( !$flickrkey )
			return false;

		// Gather a collection of photos from flickr
		$params = array(
				'api_key'	=> $flickrkey,
				'method'	=> 'flickr.groups.pools.getPhotos',
				'per_page' => 500,
				'format'	=> 'php_serial',
				'group_id' => '718574@N22'
		);

		$encoded_params = array();

		foreach ($params as $k => $v) {
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

		$rsp = file_get_contents($url);

		$rsp_obj = unserialize($rsp);

		if ($rsp_obj['stat'] == 'ok') {
			$photos = $rsp_obj['photos']['photo'];

			for( $i = 0; $i < $count; $i++ ) {
				do {
					$idx = rand(0, count($photos) - 1);
					$photo = array_splice( $photos, rand( 0, count( $photos ) - 1 ), 1 );
					$photo = $photo[0];

					// Deal with ocassional malformed photo description
					if( !( $photo['server'] && $photo['id'] && $photo['secret'] ) )
						$photo = false;
						
					if( $photo )
						$images[] = sprintf('http://farm%d.static.flickr.com/%s/%d_%s_%%s.jpg',
								$photo['farm'], $photo['server'], $photo['id'], $photo['secret']);
				}	while( !$photo && count( $photos ) );
			}
		}
		else {
			echo "<p>Flickr call <strong>failed!</strong></p>";
		}

		return $images;
	}

	function save_settings() {
		$settings_spread = $this->param( 'settings', 'spread' );
		if( is_numeric( $settings_spread ) )
			$this->option( 'spread', $settings_spread );

		$settings_flickrkey = $this->param( 'settings', 'flickrkey' );
		if( preg_match( '/^[a-z0-9]{32}$/i', $settings_flickrkey ) )
			$this->option( 'flickrkey', $settings_flickrkey );

		$this->result( "<p>Settings have been saved.</p>" );
	}

	function clear() {
		$dummy_posts = get_posts( 'post_type=any&posts_per_page=-1&meta_key=' . $this->meta_key_dummy );

		$deleted_posts = 0;

		foreach( $dummy_posts as $post ) 
			if( wp_delete_post( $post->ID, true ) ) $deleted_posts ++;
		
		$dummy_terms = get_option( 'oomph_dummy_terms' );
		$n_terms = $deleted_terms = 0;

		foreach( $dummy_terms as $taxonomy => $terms ) {
			foreach( $terms as $term_id ) 
				// Check that term exists first, count it as a term if so
				if( term_exists( $term_id, $taxonomy ) ) 
					$n_terms++;
				else
					continue;

				// Delete it from the taxonomy
				if( wp_delete_term( (int) $term_id, $taxonomy ) )
					$deleted_terms++;
		}
			
		// Clear the option if we managed to delete all the terms specified in the option
		if( $deleted_terms == $n_terms ) 
			delete_option( 'oomph_dummy_terms' );

		if( $deleted_posts ) 
			$this->result( '<div id="message" class="message">Deleted <strong>' . esc_html( $deleted_posts ) . '</strong> dummy posts</div>' );
		if( $deleted_terms ) 
			$this->result( '<div id="message" class="message">Deleted <strong>' . esc_html( $deleted_terms ) . '</strong> dummy terms</div>' );

		if( $this->async )
			$this->dummy_content_count();
	}

	/* Here is the meat and potatoes of this plugin! */
	function generate() {
		// List of post types to generate content
		$types = array(); 

		// Per-type content 
		$lorems = array();
		$photos = array();

		// Per-type per-post content counts
		$nposts = array();
		$nparas = array();
		$nphotos = array();

		// Post titles
		$titles = array();

		// Time spread of posts
		$months = $this->option( 'spread' );

		// Total counts
		$totalposts = 0;
		$total_text = 0;
		$total_photos = 0;

		$this->result( "<p>Generating dummy content...</p>" );

		$post_types = $this->param( 'post_types' );
		if( !$post_types ) $post_types = array();

		// Get Lorem content for each post type given the options
		foreach( $post_types as $type ) {
			$lengths = array();

			$type_nposts = $this->param( 'post_type_count', $type ); 
			$nposts[$type] = is_numeric( $type_nposts ) ? $type_nposts : 0;

			$type_nparas = $this->param( 'post_type_paragraphs', $type );
			$nparas[$type] = is_numeric( $type_nparas ) ? $type_nparas : 0;
			
			if($nposts[$type] <= 0 || $nparas[$type] <= 0) $errors[] = "Invalid parameters for $type";

			$contents = array();
			$lorems[$type] = array();

			$type_contents = $this->param( 'post_content', $type );
			if( count( $type_contents ) ) {
				foreach( $type_contents as $content ) {
					if( $content == 'lists' ) $contents = array_merge($contents, array('ul', 'ol', 'dl'));
					else $contents[] = $content;
				}
			}

			$type_lengths = $this->param( 'post_length', $type );
			
			if( count( $type_lengths ) ) {
				$num = $nparas[$type];

				while($num > 0) {
					foreach( $type_lengths as $length ) {
						$rand = rand(1, $num - 1);
						$lengths[$length] += $rand;
						$num -= $rand;
					}
				}
			}

			$type_post_kind = $this->param( 'post_kind', $type );

			if( in_array( 'simple', $type_post_kind ) ) {
				if( count($lengths) ) {
					foreach( $lengths as $size => $count ) {
						$this->result( '<p>Gathering <strong>' . esc_html( $count ) . '</strong> paragraphs of <strong>' . esc_html( $size ) . '</strong> length for <strong>' . esc_html( $type ) . '</strong> posts...</p>' );
						$result = wp_remote_get( 'http://loripsum.net/api/' . $count . '/' . implode( '/', $contents ) . '/' . $size );
						$lorems[$type] = array_merge($lorems[$type], explode("\n\n", trim($result['body'])));
					}
				}
				else {
					$this->result( '<p>Gathering <strong>' . esc_html( $numparas ) . '</strong> paragraphs for <strong>' . esc_html( $type ) . '</strong> posts...</p>' );
					$result = wp_remote_get( 'http://loripsum.net/api/' . implode( '/', $contents ) . '/' . $nparas[$type] );
					$lorems[$type] = explode( "\n\n", trim( $result['body'] ) );
				}
				$total_text += count( $lorems[$type] );
			}

			if( in_array( 'photos', $type_post_kind ) ) {
				$nphotos[$type] = $this->param( 'post_type_photos', $type );
				$total_photos += $nphotos[$type] * $nposts[$type];
			}

			$totalposts += $nposts[$type];
			$types[] = $type;
		}

		if( $totalposts > 0 ) {
			$this->result( '<p>Gathering <strong>' . esc_html( $totalposts ) . '</strong> tidbits for post titles...</p>' );

			$titles = $this->gather($totalposts, 10);
		}

		if( $total_photos > 0 ) {
			if( $this->option( 'flickrkey' ) ) {
				$this->result( '<p>Gathering <strong>' . esc_html( $total_photos ) . '</strong> photos from flickr for post images...</p>' );
					
				$photos = $this->gatherphotos($total_photos);
			}
			else {
				$this->result( "<p>Can't gather photos from flickr because: no flickr app key specified.</p>" );
			}
		}

		if( $total_text > 0 ) {
			$this->result( "<p>Text content generated. Lorem Ipsum generated from <a href='http://loripsum.net/#info'>LorIpsum.net</a>...</p>\n" );
		} else {
			$this->result( "<p>No post text content generated.</p>" );
		}

		// Process and gather taxonomy options
		$totalterms = 0;
		$connections = array();
		$newterms = array();
		$create_taxonomies = $this->param( 'taxonomies' );
		if( !$create_taxonomies ) $create_taxonomies = array();

		foreach( $create_taxonomies as $tax ) {
			$nterms = (int) $this->param( 'taxonomy_count', $tax );

			if($nterms > 0)
				$totalterms += $nterms;
			else $nterms = 0;

			if($links <= 0 && $nterms <= 0) {
				$errors[] = "No terms created for taxonomy <strong>$tax</strong>";
				continue;
			}
	
			$newterms[$tax] = $nterms;
		}

		$link_make_connections = $this->param( 'make_connections' );
		if( !$link_make_connections ) $link_make_connections = array();

		foreach( $link_make_connections as $tax ) {
			$links = $this->param( 'connections', $tax );

			if($links > 0) {
				$connections[$tax] = array();
				$connect_tax_types = $this->param( 'connect_type', $tax );
				if( empty( $connect_tax_types ) )
					$connect_tax_types = $this->post_types();
				else
					$connect_tax_types = $connect_tax_types;

				foreach( $connect_tax_types as $type ) 
					$connections[$tax][$type] = $links;
			}
		}

		if( $totalterms > 0 )	 {
			$this->result( "<p>Gathering <strong>$totalterms</strong> tidbits for taxonomy terms...</p>" );

			$terms = $this->gather( $totalterms, -3 );
		}
		
		foreach( $types as $type ) {
			unset( $photo );
			$this->result( "<p>Generating {$nposts[$type]} <strong>$type</strong> posts...</p>" );

			$alignments = $this->param( 'photo_align', $type );

			if( empty( $alignments ) )
				$alignments = array( 'left','right','center','' );

			$sizes = array(); 

			$type_photo_size = $this->param( 'photo_size', $type );
			if( is_array( $type_photo_size ) ) foreach( $type_photo_size as $size ) 
				if( preg_match( '/^t|m|z$/', $size ) ) $sizes[] = substr( $size, 0, 1 );

			if( empty( $sizes ) ) 
				$sizes = array( 't', 'm', 'z' );

			for( $i = 0; $i < $nposts[ $type ]; $i++ )  {
				$paras = array();
				$copy = $lorems[ $type ];
				// Generate number of paragraphs specified +- 20%
				$num = $nparas[ $type ] + (rand(0, ceil($nparas[ $type ] * .4)) - floor($nparas[ $type ] * .2));
				for( $j = 0; $j < $num; $j++ ) {
					// Reset copy of $lorems if we've run out of paragraphs
					if(count($copy) == 0) $copy = $lorems[ $type ];
					$para = implode('', array_splice($copy, rand(0, count($copy) - 1), 1));
					$paras[] = $para;
				}
				if( isset( $photos ) && !empty( $photos ) && $nphotos[$type] > 0 ) {
					$copy = $photos;
					$num = $nphotos[$type] + (rand(0, ceil($nphotos[$type] * .4)) - floor($nphotos[$type] * .2));
					
					for($j =  0; $j < $num; $j++) {
						$photo = implode('', array_splice($copy, mt_rand(0, count($copy) - 1), 1));
						$photo = sprintf( $photo, $sizes[(int)mt_rand(0,count($sizes)-1)] );
						$align = $alignments[(int)mt_rand(0,count($alignments)-1)];
						if( $align ) $align = 'class="align' . esc_attr( $align ) . '"';
						array_splice( $paras, rand( 0, count( $paras ) - 1 ), 0, array( '<img $align src="' . esc_attr( $photo ) . '" />' ) );
					}

				}

				$title = implode('', array_splice($titles, rand(0, count($titles) - 1), 1));
				if( $title ) {
					$post_data = array(
						'post_title' => $title,
						'post_type' => $type,
						'post_content' => implode("\n\n", $paras),
						'post_status' => 'publish',
						'post_author' => 1,
						'post_date' => date('Y-m-d H:i:s', time() - (mt_rand()/mt_getrandmax())*(60*60*24*30*$months))
					);
					
					$post_ID = wp_insert_post( $post_data );
					
					update_post_meta( $post_ID, $this->meta_key_dummy, 1 );
		
					$post = get_post( $post_ID );
					
					if( $post ) {
						$result_message = '<a target="postPreview" href="' . get_permalink( $post->ID ) . '">Post ID #'. esc_html( $post->ID ) . ': ' . esc_html( $post->post_title ) . '</a> <strong>' . esc_html( $type ) . '</strong> #' . esc_html( $i + 1 ) . ' (' . esc_html( $num ) . ' paragraphs)';

						if( isset( $photo ) && $this->param( 'photo_featured', $type ) ) {
							$attachment_id = $this->sideload( $photo, $post );

							if( is_wp_error( $attachment_id ) || !$attachment_id ) {
								$result_message .= ', failed to download featured image at URL <em>' . esc_url( $photo ) . '</em>: ' . esc_html( $attachment_id->get_error_message() );
							}
							else {
								update_post_meta( $post_ID, '_thumbnail_id', $attachment_id );
								$result_message .= ', featured image: ' . esc_url( $photo ) . ', attachment #' . esc_html( $attachment_id );
								update_post_meta( $attachment_id, $this->meta_key_dummy, 1 );
							}
						}
						$this->result( '<p><a href="#post-$post->ID" onclick="preview($post->ID)">' . $result_message . ' (preview)</a></p>' );
						$this->result( '<div id="' . esc_attr( "post-$post->ID" ) . '" style="height: 0; overflow: hidden">' . apply_filters( 'the_content', $post->post_content ) . '</div>' );
					}
				} else {
					$this->result( "<p>Stupid! I couldn't come up with a title!!</p>" );
				}

			}
		}

		$created_terms = get_option( 'oomph_dummy_terms' );

		if( !$created_terms ) $created_terms = array();

		foreach( $newterms as $tax => $count ) {
			$this->result( '<p>Creating <strong>' . esc_html( $count ) . '</strong> terms for taxonomy <strong>' . esc_html( $tax ) . '</strong></p>' );
			$created_terms[ $tax ] = array();
			$this->result( '<ul class="term-list">' );
			for($i = 0; $i < $count; $i++ ) {
				$term = implode('', array_splice($terms, mt_rand(0, count($terms) - 1), 1));
				$result = wp_insert_term( $term, $tax ); 
				
				// Since there's no taxonomy meta, we've got to save all of the created terms
				// into an options field
				if( !is_wp_error( $result ) )
					$created_terms[ $tax ][] = $result['term_id'];

				$this->result( '<li>' . esc_html( $term ) . '</li>' );
			}
			$this->result( '</ul>' );
		}

		update_option( 'oomph_dummy_terms', $created_terms );

		foreach( $connections as $tax => $types ) {
			$terms = get_terms( $tax, array( 'hide_empty' => false, 'exclude' => $this->param( 'ignore_default', $tax ) ? 1 : 0 ) );

			foreach( $types as $type => $count ) {
				$this->result( '<p>Creating <strong>' . esc_html( $count ) . '</strong> connections from taxonomy <strong>' . esc_html( $tax ) . '</strong> to <strong>' . esc_html( $type ) . '</strong> posts...</p>' );

				$post_query = "post_type=$type&orderby=rand&posts_per_page=-1";

				$reset = false;
				// Special case for categories: Only assign new categories to posts in Uncategorized, or ID 1
				if( $type == 'post' && $tax == 'category' ) {
					$reset = true;
					$post_query .= '&category=1';
				}

				$posts = get_posts( $post_query );

				$this->result( "<ol>" );
				foreach($posts as $post) {
					for($i = 0; $i < $count; $i++) {
						$term = array_slice( $terms, mt_rand( 0, count( $terms ) ) - 1, 1 );
						$this->result( "<li>({$post->ID}) {$post->post_name} -> ({$term[0]->term_id}) {$term[0]->name}</li>\n" );
						wp_set_object_terms( $post->ID, (int)$term[0]->term_id, $tax, !($reset && $i == 0) );
					}
				}
				$this->result( "</ol>" );
			}
		}

		if( isset( $errors ) && count( $errors ) ) print_r( $errors );

		$this->result( "<h3>Done!</h3>" );
		$this->result( "<p>Enjoy your new dummy content!</p>" );

		if( $this->async ) 
			$this->dummy_content_count();
	}

	/**
	 * Download and save a remote image so that we can crop it 
	 * from within WP and have that change propagate to all of the various
	 * image sizes.
	 * 
	 * @uses wp_basename, wp_remote_get, is_wp_error, wp_upload_bits, wp_get_current_user, wp_insert_post, wp_upload_dir, wp_generate_attachment_metadata, wp_update_attachment_metadata, update_post_meta, trailingslashit, sanitize_title
	 * @return int Newly created post ID
	 */
	function sideload( $url, $post ) {
		global $options;

		$basename = wp_basename( $url );

		/** Code adapted from media_sideload_image {{{ **/
		if( empty( $url ) )
			return new WP_Error( array( 'empty-url', "Empty URL" ) );

		// Set variables for storage
		// fix file filename for query strings
		if( !preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches ) )
			return new WP_Error( array( 'wrong-extension', "Not an image file extension" ) );

		// Download file to temp location
		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) 
			return $tmp;

		$file_array = array(
			'name' => wp_basename( $matches[0] ),
			'tmp_name' => $tmp
		);

		// do the validation and storage stuff
		$attachment_id = media_handle_sideload( $file_array, $post->ID );

		// If error storing permanently, unlink
		if ( is_wp_error( $attachment_id ) ) {
			unlink( $file_array['tmp_name'] );
			return $attachment_id;
		}
		elseif( !$attachment_id ) {
			return new WP_Error( 'media-handle-sideload-failed', "media_handle_sideload failed" );
		}
		// }}} end adapted code

		return $attachment_id;
	}

	/*
	 * Short li < label < (checkbox + text) template. Used often in content_admin()
	 */
	function list_checkboxes( $name, $labels ) {
		foreach( $labels as $value => $label ) { ?>
 			<li><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" /> <?php echo esc_html( $label ); ?></label></li><?php
		}
	}
	
	/*
	 * Admin screen
	 */
	function content_admin() { ?>
<div class="wrap oomph-plugin"> <div id="poststuff">
	<h2>Generate Content with the Oomph Dummy Content Generator</h2>

	<?php
		$this->dispatch();
	?>
	<form id="dummy-content-settings" class="oomph-dummy-content-settings" method="post" enctype="multipart/form-data">
		<h3>Global Settings:</h3>
	
		<div class="elements">
			<div class="global-settings section">
				<div class="tile flickr-key">
					<label>Flickr API Key: <input type="text" name="settings[flickrkey]" size="32" maxlength="32" value="<?php echo esc_attr( $this->option( "flickrkey" ) ); ?>" /></label>
				</div>
				<div class="tile time-spread">
					<label>Spread posts over the last <input type="text" name="settings[spread]" value="<?php echo esc_attr( $this->option( "spread" ) ); ?>" size="3" /> months.</label>
				</div>

				<div style="clear: both"><input class="button" type="submit" name="dummy-content-settings" class="primary-button" value="Save Settings" /></div>
			</div>
		</div>
		<?php wp_nonce_field("settings-nonce"); ?>
	</form>

	<form id="dummy-content-clear" class='oomph-dummy-content-settings' method="post" enctype="multipart/form-data">
		<div class="elements">
			<?php $this->dummy_content_count(); ?>
		</div>
		<?php wp_nonce_field('clear-nonce'); ?>
	</form>

	<form id="dummy-content-generate" class='oomph-dummy-content-settings' method="post" enctype="multipart/form-data">
		<h3>Generate Content:</h3>
		<div class="elements">
			<div class="content-types section">
			<?php foreach( $this->post_types() as $type ) {
			?>
				<div id="type-<?php echo esc_attr( $type->name ); ?>" class="post_type tile">
					<label><input type="checkbox" name="post_types[]" class="post_type" value="<?php echo esc_attr( $type->name ); ?>" /> Make</label> <label><input type="text" name="post_type_count[<?php echo esc_attr( $type->name ); ?>]" value="<?php echo esc_attr( $this->option( 'post_count' ) ); ?>" size="1" /> <strong><?php echo esc_attr( $type->name ); ?></strong> posts</label>
					<div class="content-options">
						<p>Content:</p>
						<ul>
							<li><label><input type="checkbox" name="post_kind[<?php echo esc_attr( $type->name ); ?>][]" value="simple" /> Text</label> <a href="javascript:void(0);">options</a></li> 
							<li><label><input type="checkbox" name="post_kind[<?php echo esc_attr( $type->name ); ?>][]" value="photos" /> Photos</label> <a href="javascript:void(0);">options</a></li> 
						</ul>
					</div>
					<div class="options simple-options" style="display: none">
					<p>Create about <label><input type="text" name="post_type_paragraphs[<?php echo esc_attr( $type->name ); ?>]" value="<?php echo esc_attr( $this->defaults['para_count'] ); ?>" size="1" /> paragraphs per <strong><?php echo esc_html( $type->name ); ?></strong></label></p>
						<div class="split">
							<p>Paragraphs:</p>
							<ul><?php $this->list_checkboxes( 'post_length[' . $type->name .'][]', array( 'short' => __( "Short" ), 'medium' => __( "Medium" ), 'long' => __( "Long" ), 'verylong' => __( "Very Long" ) ) ); ?>
							</ul> 
						</div>
						<div class="split">
							<p>Text Options:</p>
							<ul><?php $this->list_checkboxes( 'post_content[' . $type->name .'][]', array( 'decorate' => __( "Decorate (bold, italic, marked)" ), 'link' => __( "Links" ), 'code' => __( "Code Samples" ), 'bq' => __( "Blockquotes" ), 'lists' => __( "Lists" ) ) ); ?></ul>
						</div>
					</div>
					<div class="options photos-options" style="display: none">
						<p>Insert about <label><input type="text" name="post_type_photos[<?php echo esc_attr( $type->name ); ?>]" value="<?php echo esc_attr( $this->defaults['photo_count'] ); ?>" size="1" /> photos per post</label></p>
						<div class="split">
							<p>Alignment:</p>
							<ul><?php $this->list_checkboxes( 'photo_align[' . $type->name . '][]', array( 'left' => __( "Align Left" ), 'right' => __( "Align Right" ), 'center' => __( "Align Center" ), '' => __( "None (Own Line)" ) ) );  ?></ul>
						</div>
						<div class="split">
							<p>Sizes:</p>
							<ul><?php $this->list_checkboxes( 'photo_size[' . $type->name . '][]', array( 't' => __( "Small" ), 'm' => __( "Medium" ), 'z' => __( "Large" ) ) ); ?></ul>
						</div>
						<?php if( post_type_supports( $type->name, 'thumbnail' ) ) { ?>
						<div style="clear: both">
							<label><input type="checkbox" name="photo_featured[<?php echo esc_attr( $type->name ); ?>]" value="1" /> Set featured photo</label>
						</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			</div>

			<div class="taxonomies section">
				<h3>Taxonomies:</h3>
			<?php foreach( get_taxonomies( array( 'public' => true ), 'objects' ) as $tax ) {
					if( preg_match( '/^post_format$/', $tax->name ) ) continue;
			?>
				<div class="taxonomy tile">
					<label><input type="checkbox" name="taxonomies[]" value="<?php echo esc_attr( $tax->name ); ?>" size="2" /> Make</label> <label><input type="text" name="taxonomy_count[<?php echo esc_attr( $tax->name ); ?>]" value="<?php echo esc_attr( $this->option( 'term_count' ) ); ?>" size="1" /> <strong><?php echo esc_html( $tax->name ); ?></strong> terms</label>
					<div class="connections">
						<label><input type="checkbox" name="make_connections[]" value="<?php echo esc_attr( $tax->name ); ?>" /> Make </label>
						<label><input type="text" name="connections[<?php echo esc_attr( $tax->name ); ?>]" value="<?php echo esc_attr( $this->option( 'term_count' ) ); ?>" size="1" /> connections per:</label>
						<ul>
						<?php foreach( $tax->object_type as $type) { ?>
							<li>
								<label><input type="checkbox" name="connect_type[<?php echo esc_attr( $tax->name ); ?>][]" value="<?php echo esc_attr( $type ); ?>" size="1" /> <?php echo esc_html( $type ); ?></label>
							</li>
						<?php } ?>
						</ul>

						<?php if( $tax->name == "category" && $default = get_term( 1, 'category' ) ) { ?>
						<label><input type="checkbox" name="ignore_default[<?php echo esc_attr( $tax->name ); ?>]" value="1" /> Do not connect <strong><?php echo esc_html( $default->name ); ?></strong></label>
						<?php } ?>
					</div>

				</div>
			<?php } ?>
			</div>

			<div class="section">
				<p>Please use this with caution! It's easy enough to delete content generated with this plugin, but if you overload your database, things will break, and you will cry!</p>

				<input type="submit" class="button button-primary" name="dummy-content-generate" value="Make it happen!" />
			</div>
		</div>
		<?php wp_nonce_field( 'generate-nonce' ); ?>
	</form>

	<script>
	var point = new Image();
	point.src = <?php echo json_encode( plugin_dir_url( 'oomph-dummy-content/point.png' ) ); ?>;
	var spinner = new Image();
	spinner.src = <?php echo json_encode( plugin_dir_url( 'oomph-dummy-content/spinner.gif' ) ); ?>;
	var replaceContent, addResults, preview;

	(function($) {
		var $options;

		$('.content-options li a').click(function(ev) {
			ev.stopPropagation();
			var type = $(this).parents('li').find('input').val();
			var content = $(this).parents('.post_type').find('input.post_type').val();
			var $newoptions = $(this).parents('.post_type').find('.'+type+'-options');

			if($options && $options[0] != $newoptions[0]) {
				$options[0].close();
			}

			if($newoptions.length == 0) return;

			if(!$newoptions[0].setup) {
				$newoptions[0].close = function() {
					$(this).hide();
					this.$parent.removeClass('showingOptions');
				}
				$newoptions.click(function(ev) { ev.stopPropagation();  });
				$newoptions[0].$parent = $(this).parents('li');
				$newoptions[0].setup = true;
				$newoptions.append('<span class="point"></span>');
			}

			$options = $newoptions;

			var left = $(this).position().left + $(this).width() + 12;

			$options.css({ position: 'absolute', display: 'block', visibility: 'hidden' });
			$options.css({ left: left, top: $(this).position().top - $options.height() / 2, height: $options.height(), visibility: 'visible', display: 'none' });
			$options[0].$parent.addClass('showingOptions');
			$options.removeClass('onleft');
			if($options.offset().left + $options.width() > $(window).width()) {
				left = $(this).offset().left - $options.width();
				$options.css('left', left);
				$options.addClass('onleft');
			}
			$options.fadeIn();
		});
		$(window).click(function(ev) {
			if($options && $options[0].setup) { 
				$options[0].close();
				$options = null;
			}
		});

		var $elements;
		$('form.oomph-dummy-content-settings').submit(function(ev) {
			var $form = $(this);
			ev.preventDefault();
			ev.stopPropagation();

			$elements = $form.find('.elements');
			var $results = $form.find('.results');
			if( !$results.length ) {
				$form.append("<div class='results'></div>");
				$results = $form.find('.results');
			}
			$results.addClass('results-loading');
			$results.css('min-height', $elements.height());
			$elements.css({ position: 'absolute', opacity: .05 });
			$elements.css($elements.position());
			var posturl = location.protocol + '//' + location.hostname + location.pathname + location.search;
			$results.html("<iframe style='width: 0; height: 0' src='"+posturl+'&action=iframe&'+$form.serialize()+"'></iframe>");	
			$elements.find('input,select,textarea').attr('disabled','disabled');
			var $iframe = $results.find('iframe');
			$iframe.load(function() {
				$results.removeClass('results-loading');
				$results.append("<a class='close'>OK</a>");
				$results.find('.close').click(function() { 
					$results = $(this).parents('.results');
					$elements = $(this).parents('form').find('.elements');
					$results.remove(); 
					$elements.css({ position: 'static', opacity: 1 }); 
					$elements.find('input,select,textarea').removeAttr('disabled');
				}).focus();
			});
		});

		preview = function(id) {
			var $post = $('#post-' + id);

			if($post.height() == 0) 
				$post.css('height','auto');
			else
				$post.css('height',0);
		}

		replaceContent = function(form, content) {
			var $destination = $('#dummy-content-' + form).find('.elements');

			$destination.html($("<div/>").html(content).text());
		}

		addResult = function(form, content) {
			var $destination = $('#dummy-content-' + form).find('.results');
			$destination.append($("<div/>").html(content).text());
		}

	})(jQuery);
	</script>
</div> </div>
	<?php
	}
}

$GLOBALS['Oomph_Dummy_Content'] = new Oomph_Dummy_Content();
