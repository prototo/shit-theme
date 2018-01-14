<?php

if (file_exists(__DIR__ . '/ga.php')) {
    require_once(__DIR__ . '/ga.php');
}

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});

	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});

	return;
}

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		parent::__construct();
	}

	function register_post_types() {
		//this is where you can register custom post types
	}

	function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	function add_to_context( $context ) {
		$context['foo'] = 'bar';
		$context['stuff'] = 'I am a value set in your functions.php file';
		$context['notes'] = 'These values are available everytime you call Timber::get_context();';
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		return $context;
	}

	function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
	}

	function add_to_twig( $twig ) {
		/* this is where you can add your own functions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', array($this, 'myfoo')));
		return $twig;
	}

}

new StarterSite();

/**
 * SHIT STUFF
 */
function jptweak_remove_share() {
    remove_filter('the_content', 'sharing_display', 19);
    remove_filter('the_excerpt', 'sharing_display', 19);
    if (class_exists('Jetpack_Likes')) {
        remove_filter('the_content', array( Jetpack_Likes::init(), 'post_likes'), 30, 1);
    }
}
add_action( 'loop_start', 'jptweak_remove_share' );

function shit_content_wrap($content, $tag='div', $class='content') {
    return sprintf(
        '<%s class="%s">%s</%s>',
        $tag, $class, $content, $tag
    );
}

function shit_sign_off_present($content, $breaker="—") {
    preg_match("/$breaker\w+/", $content, $signOff);
    return count($signOff) > 0;
}

function shit_content($post, $breaker="—") {
    $content = $post->content;

    if (!shit_sign_off_present($content, $breaker)) {
        return shit_content_wrap($content);
    }

    $output = '';
    while (shit_sign_off_present($content, $breaker)) {
        list($partial, $signOff, $content) = preg_split("/($breaker\w+(?:.*?>))/s", $content, 2, PREG_SPLIT_DELIM_CAPTURE);
        $output .= (
            shit_content_wrap(
                $partial . shit_content_wrap($signOff, 'span', 'author')
            )
        );
    }

    if (strlen(trim($content))) {
        $output .= shit_content_wrap($content);
    }

    return $output;
}

// add_filter('timber/twig', function(\Twig_Environment $twig) {
//     $twig->addFunction(new Timber\Twig_Function('shit_post', 'shit_post'));
// });
