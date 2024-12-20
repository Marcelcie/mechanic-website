<?php


Class CT_Image extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcode
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
		add_oxygen_element( $this->options['tag'], array( $this, 'add_shortcode' ) );

		// change component button place
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		add_action("oxygen_basics_components_visual", array( $this, "component_button" ) );

		// Increase default 1600 max width for image sizes in srcset attribute.
		add_filter( 'max_srcset_image_width', array( $this, 'max_srcset_image_width' ) );
		
		add_action( 'template_redirect', array($this, 'get_attachment_sizes'));
	}


	/**
	 * Add a [ct_image] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content, $name ) {

		if ( ! $this->validate_shortcode( $atts, $content, $name ) ) {
			return '';
		}

		$options = $this->set_options( $atts );
		
		ob_start();

		// Run shortcodes in the 'alt' option, because it is base64 encoded, so the set_options() function above won't detect any shortcode on it.
		$options['alt'] = do_shortcode( oxygen_base64_decode_for_json( $options['alt'] ) );

		if( $options['image_type'] == 1 || 
		    // default is 2, but if no ID specified we need a placeholder
		    ($options['image_type'] == 2 && !$options['attachment_id'] ) 
			)  
		{
			$image_src = $options['src'];
			$image_alt = $options['alt'];
			if( class_exists( 'Oxygen_Gutenberg' ) ) {
                $image_src = Oxygen_Gutenberg::decorate_attribute($options, $image_src, 'image');
                $image_alt = Oxygen_Gutenberg::decorate_attribute($options, $image_alt, 'alt');
            }
            
			echo '<img loading="' . $options['lazy'] . '" id="' . esc_attr($options['selector']) . '" alt="' . $image_alt . '" src="' . $image_src . '" class="' . esc_attr($options['classes']) . '"'; do_action("oxygen_vsb_component_attr", $options, $this->options['tag']); echo '/>';
		} else {
            $attachment_id = intval(do_shortcode($options['attachment_id']));

            if ($attachment_id > 0) {
                $image_alt = $options['alt'];
                $attachment_size = isset($options['attachment_size']) ? $options['attachment_size'] : 'thumbnail';
                $source = wp_get_attachment_image_src($attachment_id, $attachment_size);

                if (is_array($source)) {
                    list($image_src, $image_width, $image_height) = $source;

                    // if (class_exists('Oxygen_Gutenberg')) {
                    //     $image_src = Oxygen_Gutenberg::decorate_attribute($options, $image_src, 'image');
                    // }

                    // Always pull alt text from meta data when using media library
					$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

                    // Remove image sources son SRCSET with bigger width than the image size selected
                    add_filter('wp_calculate_image_srcset', array($this, 'remove_bigger_srcset_sources'), 10, 5);

                    $srcset = wp_get_attachment_image_srcset($attachment_id, $attachment_size);

                    // Remove our filter so it doesn't affect 3rd party plugins
                    remove_filter('wp_calculate_image_srcset', array($this, 'remove_bigger_srcset_sources'), 10);

                    echo '<img loading="' . $options['lazy'] . '" id="' . esc_attr($options['selector']) . '" alt="' . esc_attr($image_alt) . '" src="' . esc_attr($image_src) . '" class="' . esc_attr($options['classes']) . '" srcset="' . $srcset . '" sizes="(max-width: '.$image_width.'px) 100vw, '.$image_width.'px" '; do_action("oxygen_vsb_component_attr", $options, $this->options['tag']); echo '/>';
                }
            }
        }

		return ob_get_clean();
	}

	function max_srcset_image_width( ) {
		// Set max width to 8K resolution
		return 7680;
	}

	function remove_bigger_srcset_sources( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		foreach ( $sources as $width => $data ) {
			if( $width > $size_array[0] ) {
				unset( $sources[ $width ] );
			}
		}
		return $sources;
	}

	function get_attachment_sizes() {

		if ( isset($_REQUEST['action']) && $_REQUEST['action'] == "ct_get_attachment_sizes") { 
			$nonce  	= $_REQUEST['nonce'];
			$post_id 	= intval( $_REQUEST['post_id'] );
			$attachment_id 	= stripslashes($_REQUEST['attachment_id']);

			// check nonce
			if ( ! isset( $nonce, $post_id ) || ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
				// This nonce is not valid.
				die( 'Security check' );
			}

			// check user role
			if ( ! oxygen_vsb_current_user_can_access() ) {
				die ( 'Security check' );
			}

			// sign "[oxygen]" shortcode on the fly
			if (stripos($attachment_id, '[oxygen') !== false) {
				$attachment_id = ct_sign_oxy_dynamic_shortcode(array($attachment_id));
			}

			$attachment_id = intval(do_shortcode($attachment_id));
			
			$args = array(
				'p'         => $attachment_id,
				'post_type' => 'attachment'
			);
			$query = new WP_Query($args);
			if( !$query->have_posts() || count( $query->posts ) != 1 ) {
				die ( 'Invalid attachment id' );
			}

			$attachment = wp_prepare_attachment_for_js( $query->posts[0] );

			$json = json_encode( $attachment['sizes'] );

			header('Content-Type: application/json', true, 200);
			echo $json;
			die();
		}
	}
}

/**
 * Create Image Component Instance
 * 
 * @since 0.1.2
 */

global $oxygen_vsb_components;
$oxygen_vsb_components['image'] = new CT_Image ( 

		array( 
			'name' 		=> 'Image',
			'tag' 		=> 'ct_image',
			'params' 	=> array(
					array(
						"type" 			=> "radio",
						"heading" 		=> "",
						"param_name" 	=> "image_type",
						"value" 		=> array(
							2   	    => __("Media Library"),
							1 	        => __("Image URL")
						),
						"default"       => 1,
						"css"			=> false,
					),
					array(
						"type" 			=> "mediaurl",
						"heading" 		=> __("Image URL"),
						"param_name" 	=> "src",
						"value" 		=> "http://via.placeholder.com/1600x900",
						"condition"		=> "image_type=1",
						"css"			=> false
					),
					array(
						"type" 			=> "mediaurl",
						"heading" 		=> __("ID"),
						"param_name" 	=> "attachment_id",
						"value" 		=> "",
						"condition"		=> "image_type=2",
						"attachment"    => true,
						"css"			=> false,
						"dynamicdatacode"	=>	'<div class="oxygen-dynamic-data-browse" ctdynamicdata data="iframeScope.dynamicShortcodesImageIDMode" callback="iframeScope.insertShortcodeToImageID">data</div>'
					),
					array(
						"type" 			=> "dropdown_dynamic",
						"heading" 		=> "Size",
						"param_name" 	=> "attachment_size",
						"dynamic"       => true,
						"ngrepeat_value"=> "option in iframeScope.component.options[iframeScope.component.active.id].size_labels",
						"ngclick_value" => "iframeScope.setOptionModel('attachment_size', option); iframeScope.setOptionModel('attachment_width', iframeScope.component.options[iframeScope.component.active.id].sizes[option].width); iframeScope.setOptionModel('attachment_height', iframeScope.component.options[iframeScope.component.active.id].sizes[option].height); iframeScope.setOptionModel('attachment_url', iframeScope.component.options[iframeScope.component.active.id].sizes[option].url);",
						"default"       => "medium",
						"css"			=> false,
						"condition"		=> "image_type=2",
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Width"),
						"param_name" 	=> "width",
						"value" 		=> "",
						"hide_wrapper_end" => true,
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Height"),
						"param_name" 	=> "height",
						"value" 		=> "",
						"hide_wrapper_start" => true,
					),
					array(
						"param_name" 	=> "attachment_width",
						"value" 		=> "",
						"hidden" 		=> true,
					),
					array(
						"param_name" 	=> "attachment_height",
						"value" 		=> "",
						"hidden" 		=> true,
					),
					array(
						"param_name" 	=> "width-unit",
						"value" 		=> "auto",
						"hidden" 		=> true,
					),
					array(
						"param_name" 	=> "height-unit",
						"value" 		=> "auto",
						"hidden" 		=> true,
					),
					array(
						"type"			=> "dropdown",
						"heading"		=> __("Object Fit", "oxygen"),
						"param_name"	=> "object-fit",
						"value"		=> array(
							"initial" 		=> "initial",
							"cover" 		=> "cover",
							"contain" 		=> "contain",
							"fill" 			=> "fill",
							"scale-down" 	=> "scale-down",
							"none" 			=> "none"
						)
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("Object Position", "oxygen"),
						"param_name" 	=> "object-position",
						"value" 		=> "center center",
						"css" 			=> true
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("Aspect Ratio", "oxygen"),
						"param_name" 	=> "aspect-ratio",
						"value" 		=> "initial",
						"css" 			=> true
					),
					array(
						"param_name" 	=> "attachment_url",
						"value" 		=> "http://via.placeholder.com/1600x900",
						"hidden" 		=> true,
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("Alt Text"),
						"param_name" 	=> "alt",
						"value" 		=> "",
						"css" 			=> false,
						"condition"		=> "image_type=1",
						"dynamicdatacode"	=>	'<div class="oxygen-dynamic-data-browse" ctdynamicdata data="iframeScope.dynamicShortcodesContentMode" callback="iframeScope.insertShortcodeToImageAlt">data</div>'
					),
					array(
						"type" 			=> "checkbox",
						"label" 		=> __("Lazy Load", "oxygen"),
						"param_name" 	=> "lazy",
						"value" 		=> "eager",
						"true_value" 	=> "lazy",
						"false_value" 	=> "eager",
						"css" 			=> false
					),
			),
			'advanced' => array(
				"size" => array(
						"values" 	=> array (
							'max-width' 	=> '100',
							'max-width-unit' 	=> '%',
							)
					),
				'allowed_html' => 'post',
				'allow_shortcodes' => false,
			)
		)
);

?>
