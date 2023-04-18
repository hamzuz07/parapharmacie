<?php
namespace ETC\App\Controllers\Elementor\Modules;

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Control_Media;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
/**
 * General options.
 *
 * @since      2.0.0
 * @package    ETC
 * @subpackage ETC/Controllers/Elementor/Modules
 */
class General {


    function __construct(){
        // Add new controls to advanced tab globally
	    add_action( "elementor/element/common/_section_style/before_section_start", array( $this, 'add_parallax_controls') );

        // Renders attributes for all Elementor Elements
	    add_action( 'elementor/frontend/widget/before_render', array($this, 'before_render' ), 10 );

	    // Clear Elementor file cache after staticblocks save
	    add_action( 'elementor/editor/after_save', array($this,  'clear_cache'), 10, 2 );
    }

    public function clear_cache( $post_ID, $editor_data ) {
    	if (get_post_type($post_ID) == 'staticblocks'){
		    Plugin::$instance->files_manager->clear_cache();
	    }
    }
    
	public function before_render( $widget ) {
		$settings = $widget->get_settings_for_display();
		
		if ( isset( $settings['etheme_parallax'] ) && $settings['etheme_parallax'] ) {
			
			switch ($settings['etheme_parallax_type']) {
				
				case 'scroll_effects':
					$widget->add_script_depends( 'etheme_parallax_scroll_effect' );
					wp_enqueue_script('etheme_parallax_scroll_effect'); // works always
				break;
				
				case '3d_hover_effects':
					$widget->add_script_depends( 'etheme_parallax_3d_hover_effect' ); // not always
					wp_enqueue_script('etheme_parallax_3d_hover_effect'); // works always
				break;
				
				case 'hover_effects':
					$widget->add_script_depends( 'etheme_parallax_hover_effect' ); // not always
					wp_enqueue_script('etheme_parallax_hover_effect'); // works always
					break;
				
				default;
			}
			
		}
	}

    /**
     * Add extra controls to advanced tab
     *
     * @return void
     */
    public function add_parallax_controls( $element ){
	
	    $element->start_controls_section(
            'etheme_section_extra',
            array(
                'label'     => __( 'XSTORE Effects', 'xstore-core' ),
                'tab'       => Controls_Manager::TAB_ADVANCED
            )
        );
	
	    $element->add_control(
            'etheme_parallax',
            array(
                'label'        => __( 'Enable XStore Effects', 'xstore-core' ),
                'type'         => Controls_Manager::SWITCHER,
                'frontend_available' => true
            )
        );
	
	    $element->add_control(
		    'etheme_parallax_mobile',
		    array(
			    'label'        => __( 'Enable parallax on mobile', 'xstore-core' ),
			    'type'         => Controls_Manager::SWITCHER,
			    'default' => '',
			    'condition' => array(
				    'etheme_parallax!' => ''
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_type',
		    array(
			    'label'   => __( 'Type', 'xstore-core' ),
			    'type'    => Controls_Manager::SELECT,
			    'options' => array(
				    'scroll_effects'  => __( 'Scroll Effects', 'xstore-core' ),
				    '3d_hover_effects'  => __( '3d Hover Effect', 'xstore-core' ),
				    'hover_effects'  => __( 'Hover Effect', 'xstore-core' ),
			    ),
			    'default'   => 'scroll_effects',
			    'prefix_class' => 'etheme-parallax-',
			    'condition' => array(
				    'etheme_parallax!' => ''
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_scale',
		    array(
			    'label'        => esc_html__( 'Scale', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'separator' => 'before',
			    'default' => [
			    	'size' => 1,
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 0,
					    'max'  => 3,
					    'step' => .1
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_heading',
		    [
			    'type' => Controls_Manager::HEADING,
			    'separator' => 'before',
			    'label' => __( 'Moving by axis', 'xstore-core' ),
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
		    ]
	    );
	    
	    $element->start_controls_tabs( 'etheme_parallax_scroll_tabs', [
		    'condition' => array(
			    'etheme_parallax!' => '',
			    'etheme_parallax_type' => 'scroll_effects'
		    )
	    ] );
	
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_tab_x',
		    [
			    'label' => __( 'X axis', 'xstore-core' ),
		    ]
	    );
	    
	    $element->add_control(
		    'etheme_parallax_scroll_x',
		    [
			    'label'        => esc_html__( 'X axis offset', 'xstore-core' ),
			    'description'  => esc_html__( 'Recommended -200 to 200', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => 0,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	    
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_tab_y',
		    [
			    'label' => __( 'Y axis', 'xstore-core' ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_y',
		    [
			    'label'        => esc_html__( 'Y axis offset', 'xstore-core' ),
			    'description'  => esc_html__( 'Recommended -200 to 200', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => -80,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_tab_z',
		    [
			    'label' => __( 'Z axis', 'xstore-core' ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_z',
		    [
			    'label'        => esc_html__( 'Z axis offset', 'xstore-core' ),
			    'description'  => esc_html__( 'Recommended -200 to 200', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => 0,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	    $element->end_controls_tabs();
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotate_heading',
		    [
			    'type' => Controls_Manager::HEADING,
			    'separator' => 'before',
			    'label' => __( 'Rotate', 'xstore-core' ),
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotate_type',
		    [
			    'label'        => esc_html__( 'Type', 'xstore-core' ),
			    'type'         => Controls_Manager::SELECT,
			    'default' => 'simple',
			    'options'      => [
				    '3d'  => esc_html__('3d rotate', 'xstore-core'),
				    'simple'  => esc_html__('Simple', 'xstore-core'),
			    ],
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotate',
		    [
			    'label'        => esc_html__( 'Angle', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'separator' => 'before',
			    'default' => [
				    'size' => 0,
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 0,
					    'max'  => 360,
					    'step' => 1
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
				    'etheme_parallax_scroll_rotate_type' => 'simple'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->start_controls_tabs( 'etheme_parallax_scroll_rotate_3d_tabs', [
		    'condition' => array(
			    'etheme_parallax!' => '',
			    'etheme_parallax_type' => 'scroll_effects',
			    'etheme_parallax_scroll_rotate_type' => '3d'
		    )
	    ] );
	
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_rotate_3d_tab_x',
		    [
			    'label' => __( 'X rotate', 'xstore-core' ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotateX',
		    [
			    'label'        => esc_html__( 'Angle', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => 0,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
				    'etheme_parallax_scroll_rotate_type' => '3d'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_rotate_3d_tab_y',
		    [
			    'label' => __( 'Y rotate', 'xstore-core' ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotateY',
		    [
			    'label'        => esc_html__( 'Angle', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => 0,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
				    'etheme_parallax_scroll_rotate_type' => '3d'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	
	    $element->start_controls_tab(
		    'etheme_parallax_scroll_rotate_3d_tab_z',
		    [
			    'label' => __( 'Z rotate', 'xstore-core' ),
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_rotateZ',
		    [
			    'label'        => esc_html__( 'Angle', 'xstore-core' ),
			    'type'         => Controls_Manager::TEXT,
			    'default'      => 0,
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
				    'etheme_parallax_scroll_rotate_type' => '3d'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->end_controls_tab();
	    $element->end_controls_tabs();
	
        $element->add_control(
		    'etheme_parallax_scroll_advanced_heading',
		    [
			    'type' => Controls_Manager::HEADING,
			    'separator' => 'before',
			    'label' => __( 'Advanced', 'xstore-core' ),
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
		    ]
	    );
	    
	    $element->add_control(
		    'etheme_parallax_scroll_perspective',
		    array(
			    'label'        => esc_html__( 'Perspective', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 800
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 0,
					    'max'  => 5000,
					    'step' => 100
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects',
				    'etheme_parallax_scroll_z!' => ''
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_scroll_smoothness',
		    array(
			    'label'        => esc_html__( 'Smoothness', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default' => [
			    	'size'=> 30
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 10,
					    'max'  => 100,
					    'step' => 5
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'scroll_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_maxTilt',
		    array(
			    'label'        => esc_html__( 'Smoothness', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 20
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 10,
					    'max'  => 50,
					    'step' => 5
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_scale',
		    array(
			    'label'        => esc_html__( 'Scale', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 1.1
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 1,
					    'max'  => 3,
					    'step' => .1
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
//	    $element->add_control(
//		    'etheme_parallax_3d_hover_speed',
//		    [
//			    'label'        => esc_html__( 'Speed', 'xstore-core' ),
//			    'type'         => Controls_Manager::TEXT,
//			    'default'      => 300,
//			    'render_type'  => 'template',
//			    'condition' => array(
//				    'etheme_parallax!' => '',
//				    'etheme_parallax_type' => '3d_hover_effects'
//			    )
//		    ]
//	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_speed',
		    array(
			    'label'        => esc_html__( 'Speed', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 400
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 100,
					    'max'  => 2000,
					    'step' => 100
				    )
			    ),
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_perspective',
		    array(
			    'label'        => esc_html__( 'Perspective', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 700
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 0,
					    'max'  => 5000,
					    'step' => 100
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_disableAxis',
		    [
			    'label'        => esc_html__( 'Disable axis', 'xstore-core' ),
			    'type'         => Controls_Manager::SELECT,
			    'options'      => [
				    ''  => __('None', 'xstore-core'),
				    'x'  => 'X',
				    'y'  => 'y',
			    ],
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    ]
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_glare',
		    array(
			    'label'        => esc_html__( 'Glare effect', 'xstore-core' ),
			    'type'         => Controls_Manager::SWITCHER,
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_3d_hover_glare_max',
		    array(
			    'label'        => esc_html__( 'Max Glare', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'   => array(
				    'size' => 1,
			    ),
			    'range' => array(
				    'px' => array(
					    'min'  => 0.1,
					    'max'  => 1,
					    'step' => .1
				    )
			    ),
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => '3d_hover_effects',
				    'etheme_parallax_3d_hover_glare!' => ''
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->add_control(
		    'etheme_parallax_hover_smoothness',
		    array(
			    'label'        => esc_html__( 'Smoothness', 'xstore-core' ),
			    'type'       => Controls_Manager::SLIDER,
			    'default'      => [
				    'size' => 50
			    ],
			    'range' => array(
				    'px' => array(
					    'min'  => 10,
					    'max'  => 200,
					    'step' => 1
				    )
			    ),
			    'render_type'  => 'template',
			    'condition' => array(
				    'etheme_parallax!' => '',
				    'etheme_parallax_type' => 'hover_effects'
			    ),
			    'frontend_available' => true
		    )
	    );
	
	    $element->end_controls_section();
    }

}
