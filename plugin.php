<?php
namespace ElementorHelloWorld;

use ElementorPro\Modules\Posts\Skins\Skin_Posts_ECS;

/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class Plugin {

	/**
	 * Instance
	 *
	 * @since 1.2.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function widget_scripts() {
		wp_register_script( 'elementor-hello-world', plugins_url( '/assets/js/hello-world.js', __FILE__ ), [ 'jquery' ], false, true );
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function include_widgets_files() {


        require_once( __DIR__ . '/widgets/hello-world.php' );
		require_once( __DIR__ . '/widgets/inline-editing.php' );


        require_once ( __DIR__ . '/skins/skin-twig.php' );

        add_action( 'elementor/widget/posts/skins_init', function( $widget ) {

            $widget->add_skin( new Skins\Skin_Twig ( $widget ) );
        } );

       // add_action( 'elementor/widget/posts/skins_init',  [ $this,'add_twig_post_skin'] );

    }

    /**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function register_widgets() {
		// Its is now safe to include Widgets files
		$this->include_widgets_files();

		// Register Widgets
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Hello_World() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Inline_Editing() );
	}

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct() {

		// Register widget scripts
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

        add_action('elementor/element/after_section_end',  [ $this, 'gbt_register_controls' ],10,3);

        add_action('elementor/element/after_section_end', [$this,'gbt_register_controls'], 10, 3);

        add_action( 'elementor/frontend/section/before_render', [$this,'_before_render']);



    }

  public  function gbt_register_controls($element, $section_id, $args)
    {
        if (($element->get_name() === $element->get_type())) {

            $matchID = ($element->get_name() === 'section') ? 'section_background' : "section_style";

            if (($matchID === $section_id)) {
              $this->initControlSection($element);
            }
        }

        if ($element->get_type() === "widget" && $element->get_name() === 'button' && 'section_style' === $section_id) {
            // echo "FOUND@@ " . $element->get_name()."::". $section_id ;
            $this->initControlSection($element);
        }
        if ($element->get_type() === "widget" && $element->get_name() === 'icon' && 'section_style_icon' === $section_id) {
            // echo "FOUND@@ " . $element->get_name()."::". $section_id ;
            $this->initControlSection($element);

        }
    }

    private function initControlSection($element)
    {

        $element->start_controls_section(
            'gbt_section',
            [
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'label' => __('GILLIAN CUSTOM CSS PICKER', 'wts-eae')
            ]
        );
        $stylesarr = array();
        foreach (json_decode(PYROMANCY2020_STYLES_COLOR) as &$value) {
            $variable = ltrim($value, '.'); // substr($value, strpos($value, "."),3);
            $stylesarr[$variable] = ($variable);
        }
        $element->add_control(
            'gbt_css_picker',
            [
                'label' => __('CSS Picker', 'plugin-domain'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $stylesarr,
                'default' => ['title', 'description'],
            ]
        );
        $element->end_controls_section();
    }

    public function _before_render($element)
    {

        if ($element->get_name() == 'section' || $element->get_name() == 'column') {
            // return;
            //$element->add_render_attribute('wrapper' , 'data-color' , "gilliantest");
            // echo $element->get_name() ;
        }
        $settings = $element->get_settings();///
        if (isset ($settings['gbt_css_picker'])) {
            if (!empty($settings['gbt_css_picker'] && !empty($settings['gbt_css_picker'][0]))) {

                //not sure why this does this...
                if (isset($settings['gbt_css_picker'][0]) && $settings['gbt_css_picker'][0] == "title") {
                    return;
                }

                //print_r($settings['gbt_css_picker']);

                $classArray = array();//$settings['gbt_css_picker'];

                if (!empty($settings['css_classes'])) {
                    $classArray = explode(" ", $settings['css_classes']);
                }

                $classArray = array_merge($classArray, $settings['gbt_css_picker']);

                $element->add_render_attribute(
                    'wrapper',
                    [
                        'class' => $classArray,
                    ]
                );

                $element->add_render_attribute(
                    '_wrapper', 'class', $classArray
                );
            }
        }
    }
}

// Instantiate Plugin Class
Plugin::instance();
