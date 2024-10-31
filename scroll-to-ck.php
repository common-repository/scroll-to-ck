<?php
/**
 * Plugin Name: Scroll To CK
 * Plugin URI: https://www.ceikay.com/
 * Description: Scroll To CK allows you to scroll your page with you links and add a go to top button on scroll.
 * Version: 1.1.3
 * Author: Cédric KEIFLIN
 * Author URI: https://www.ceikay.com/
 * License: GPL2
 * Text Domain: scroll-to-ck
 * Domain Path: /language
 */

Namespace Scrolltock;

defined('ABSPATH') or die;

if (! defined('CK_LOADED')) define('CK_LOADED', 1);
if (! defined('SCROLLTOCK_PLATFORM')) define('SCROLLTOCK_PLATFORM', 'wordpress');
if (! defined('SCROLLTOCK_PATH')) define('SCROLLTOCK_PATH', dirname(__FILE__));
if (! defined('SCROLLTOCK_MEDIA_PATH')) define('SCROLLTOCK_MEDIA_PATH', SCROLLTOCK_PATH);
if (! defined('SCROLLTOCK_ADMIN_GENERAL_URL')) define('SCROLLTOCK_ADMIN_GENERAL_URL', admin_url('', 'relative') . 'options-general.php?page=scroll-to-ck');
if (! defined('SCROLLTOCK_MEDIA_URL')) define('SCROLLTOCK_MEDIA_URL', plugins_url('', __FILE__));
if (! defined('SCROLLTOCK_CEIKAY_MEDIA_URL')) define('SCROLLTOCK_CEIKAY_MEDIA_URL', 'https://media.ceikay.com');
if (! defined('SCROLLTOCK_SITE_ROOT')) define('SCROLLTOCK_SITE_ROOT', ABSPATH);
if (! defined('SCROLLTOCK_URI_ROOT')) define('SCROLLTOCK_URI_ROOT', site_url());
if (! defined('SCROLLTOCK_URI_BASE')) define('SCROLLTOCK_URI_BASE', admin_url('', 'relative'));
if (! defined('SCROLLTOCK_VERSION')) define('SCROLLTOCK_VERSION', '1.1.0');
if (! defined('SCROLLTOCK_PLUGIN_NAME')) define('SCROLLTOCK_PLUGIN_NAME', 'scroll-to-ck');
if (! defined('SCROLLTOCK_SETTINGS_FIELD')) define('SCROLLTOCK_SETTINGS_FIELD', 'scrolltock_options'); // shall be scroll-to-ck_options but keep this notation for legacy purpose
if (! defined('SCROLLTOCK_WEBSITE')) define('SCROLLTOCK_WEBSITE', 'http://www.ceikay.com/plugins/scroll-to-ck/');

class Scrolltock {

	private static $instance;

	static function getInstance() { 
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	function init() {
		require_once('helpers/helper.php');
		$this->default_settings = Helper::getSettings();

		// load the translation
		add_action('plugins_loaded', array($this, 'load_textdomain'));

		if (is_admin()) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
			// add the link in the plugins list
			add_filter( 'plugin_action_links', array( $this, 'show_plugin_links'), 10, 2 );
		} else {
			add_action('wp_footer', array( $this, 'load_inline_css'));
			add_action('wp_enqueue_scripts', array( $this, 'load_assets'));
			add_action('init', array( $this, 'load_jquery'));
		}
		return;
	}

	function load_textdomain() {
		load_plugin_textdomain( 'scroll-to-ck', false, dirname( plugin_basename( __FILE__ ) ) . '/language/'  );
	}

	function admin_init() {
		register_setting(SCROLLTOCK_SETTINGS_FIELD, SCROLLTOCK_SETTINGS_FIELD);
		// set the entry in the database options table if not exists
		add_option(SCROLLTOCK_SETTINGS_FIELD, $this->default_settings );
		$this->options = get_option(SCROLLTOCK_SETTINGS_FIELD);
	}

	function admin_menu() {
		if ( ! current_user_can('update_plugins') )
			return;

		// add a new submenu to the standard Settings panel
		$this->pagehook = add_options_page(
		__('Scroll To CK'), __('Scroll To CK'), 
		'administrator', SCROLLTOCK_PLUGIN_NAME, array($this,'render_options') );

		// executed on-load. Add all metaboxes and create the row in the options table
		add_action( 'load-' . $this->pagehook, array( $this, 'load_admin_assets' ) );

	}

	function show_plugin_links($links, $file) {
		if ($file == 'scroll-to-ck/scroll-to-ck.php') {
			array_push($links, '<a href="options-general.php?page=' . SCROLLTOCK_PLUGIN_NAME . '">'. __('Settings'). '</a>');
		}

		return $links;
	}

	public function load_admin_assets() {
		// wp_enqueue_script('postbox');
		wp_enqueue_script(array('jquery', 'jquery-ui-tooltip'));
		wp_enqueue_style( 'ckframework', SCROLLTOCK_MEDIA_URL . '/assets/ckframework.css' );
		wp_enqueue_style( SCROLLTOCK_PLUGIN_NAME . '-admin', SCROLLTOCK_MEDIA_URL . '/assets/admin.css' );
	}

	public function render_options() {
		require_once(SCROLLTOCK_PATH . '/helpers/ckfields.php');
		$fields = new CKFields($this->options, SCROLLTOCK_SETTINGS_FIELD, $this->default_settings);
		$fields->load_assets_files();
		?>
		<link rel="stylesheet" href="<?php echo SCROLLTOCK_MEDIA_URL ?>/assets/jscolor/jscolor.css" type="text/css" />
		<script type="text/javascript" src="<?php echo SCROLLTOCK_MEDIA_URL ?>/assets/jscolor/jscolor.js"></script>
		<div id="ckoptionswrapper" class="ckinterface">
			<a href="<?php echo SCROLLTOCK_WEBSITE ?>" target="_blank" style="text-decoration:none;"><img src="<?php echo SCROLLTOCK_MEDIA_URL ?>/images/logo_scrolltock_64.png" style="margin: 5px;" class="cklogo" /><span class="cktitle">Scroll To CK</span></a>
			<div style="clear:both;"></div>
			<a class="button" href="https://www.ceikay.com/documentation/scroll-to-ck/" target="_blank"><img src="https://media.ceikay.com/images/page_white_acrobat.png" width="16" height="16" /> <?php echo __('Documentation', 'scroll-to-ck') ?></a>
			<?php //$this->show_message(); ?>
			<form method="post" action="options.php">
				<div class="metabox-holder">
					<div class="postbox-container" style="width: 99%;">
						<div class="ckheading"><?php _e( 'Effects','scroll-to-ck'); ?></div>
						<div>
							<label for="<?php echo $fields->getId( 'fxduration' ); ?>"><?php _e( 'Speed','scroll-to-ck'); ?></label>
							<img class="ckicon" src="<?php echo SCROLLTOCK_CEIKAY_MEDIA_URL ?>/images/hourglass.png" />
							<?php echo $fields->render('text', 'fxduration') ?>
						</div>
						<div>
							<label for="<?php echo $fields->getId( 'offsety' ); ?>"><?php _e( 'Vertical offset','scroll-to-ck'); ?></label>
							<img class="ckicon" src="<?php echo SCROLLTOCK_CEIKAY_MEDIA_URL ?>/images/offsety.png" />
							<?php echo $fields->render('text', 'offsety') ?>
						</div>
						<div class="ckheading"><?php _e( 'Back to top','scroll-to-ck'); ?></div>
						<div>
							<label for="<?php echo $fields->getId( 'activatetotop' ); ?>"><?php _e('Activate Back to top button', 'scroll-to-ck'); ?></label>
							<img class="ckicon" src="<?php echo SCROLLTOCK_CEIKAY_MEDIA_URL ?>/images/arrowup.png" />
							<?php echo $fields->render('radio', 'activatetotop', null, 'boolean') ?>
						</div>
						<div>
							<label for="<?php echo $fields->getId( 'startoffset' ); ?>"><?php _e( 'Start offset','scroll-to-ck'); ?></label>
							<img class="ckicon" src="<?php echo SCROLLTOCK_CEIKAY_MEDIA_URL ?>/images/offsety.png" />
							<?php echo $fields->render('text', 'startoffset') ?>
						</div>
						<div>
							<label for="<?php echo $fields->getId( 'totop_hideresolution' ); ?>"><?php _e( 'Hide under resolution','scroll-to-ck'); ?></label>
							<img class="ckicon" src="<?php echo SCROLLTOCK_CEIKAY_MEDIA_URL ?>/images/width.png" />
							<?php echo $fields->render('text', 'totop_hideresolution') ?>
							<span class="ckdesc"><?php _e( 'Set a value for the resolution under which you want the button to be hidden. Leave it blank to not hide it.','scroll-to-ck'); ?>
						</div>
		
					</div>
					<div style="clear:both;"></div>
				</div>
				<div style="margin: 5px 0;">
					<input type="submit" class="button button-primary" name="save_options" value="<?php _e('Save Settings', 'scroll-to-ck'); ?>" />
				</div>
				<?php
				settings_fields(SCROLLTOCK_SETTINGS_FIELD);
				?>
			</form>
			<?php echo $this->copyright(); ?>
		</div>
	<?php }

	public function copyright() {
		$html = array();
		$html[] = '<hr style="margin:10px 0;clear:both;" />';
		$html[] = '<div class="ckpoweredby"><a href="https://www.ceikay.com" target="_blank">https://www.ceikay.com</a></div>';
		// $html[] = '<div class="ckproversioninfo"><div class="ckproversioninfo-title"><a href="' . COOKIESCK_WEBSITE . '" target="_blank">' . __('Get the Pro version', 'scroll-to-ck') . '</a></div>
		// <div class="ckproversioninfo-content">
			
// <p>Multiple positions</p>
// <p>Custom cookie duration</p>
// <p>Custom duration</p>
// <p>Read more attributes</p>
// <p>Styling interface</p>
// <div class="ckproversioninfo-button"><a href="' . COOKIESCK_WEBSITE . '" target="_blank">' . __('Get the Pro version', 'scroll-to-ck') . '</a></div>
		// </div>';

		return implode($html);
	}

	function load_jquery() {
		wp_enqueue_script('jquery');
	}

	function load_assets() {
		// set the entry in the database options table if not exists
		add_option(SCROLLTOCK_SETTINGS_FIELD, $this->default_settings );
		$this->options = get_option(SCROLLTOCK_SETTINGS_FIELD);
		require_once(SCROLLTOCK_PATH . '/helpers/ckfields.php');
		$fields = new CKFields($this->options, SCROLLTOCK_SETTINGS_FIELD, $this->default_settings);
		$fxduration = $fields->getValue('fxduration', '1000');
		$offsety = $fields->getValue('offsety', '0');
		$activatetotop = $fields->getValue('activatetotop', '1');
		$totop_startoffset = $fields->getValue('totop_startoffset', '100');

		// for the scroll to top button
		$scrolltotop = "$(document.body).append('<a href=\"#\" class=\"scrollToTop\">" . __('Back to top') . "</a>');
					//Check to see if the window is top if not then display button
					$(window).scroll(function(){
						if ($(this).scrollTop() > " . (int) $totop_startoffset . ") {
							$('.scrollToTop').fadeIn();
						} else {
							$('.scrollToTop').fadeOut();
						}
					});

					//Click event to scroll to top
					$('.scrollToTop').click(function(){
						$('html, body').animate({scrollTop : 0},". (int) $fxduration . ");
						return false;
					});";

		// add the script
		$js = "\n\tjQuery(document).ready(function($){";
		if ($activatetotop) {
			$js .= $scrolltotop;
		}
		$js .= "$('a.scrollTo').click( function(event) {
					var link = $(this).is('a') ? $(this) : $($(this).find('a')[0]);
					var pageurl = window.location.href.split('#');
					var linkurl = link.attr('href').split('#');

					if ( link.attr('href').indexOf('#') != 0
						&& ( ( link.attr('href').indexOf('http') == 0 && pageurl[0] != linkurl[0] )
						|| link.attr('href').indexOf('http') != 0 && pageurl[0] != '" . site_url() . "' + linkurl[0].replace('" . site_url() . "/', '') )
						) {
						// here action is the natural redirection of the link to the page
					} else {
						event.preventDefault();
						$(this).scrolltock();
					}
				});

				$.fn.scrolltock = function() {
					var link = $(this).is('a') ? $(this) : $($(this).find('a')[0]);
					var page = link.attr('href');
					var pattern = /#(.*)/;
					var targetEl = page.match(pattern);
					if (! targetEl.length) return;
					if (! jQuery(targetEl[0]).length) return;

					// close the menu hamburger
					if (link.parents('ul').length) {
						var menu = $(link.parents('ul')[0]);
						if (menu.parent().find('> .mobileckhambuger_toggler').length && menu.parent().find('> .mobileckhambuger_toggler').attr('checked') == 'checked') {
							menu.animate({'opacity' : '0'}, function() { menu.parent().find('> .mobileckhambuger_toggler').attr('checked', false); menu.css('opacity', '1'); });
						}
					}
					var speed = link.attr('data-speed') ? link.attr('data-speed') : ". (int) $fxduration . ";
					var offsety = link.attr('data-offset') ? parseInt(link.attr('data-offset')) : ". (int) $offsety . ";
					jQuery('html, body').animate( { scrollTop: jQuery(targetEl[0]).offset().top + offsety }, speed, scrolltock_setActiveItem() );
					return false;
				}
				// Cache selectors
				var lastId,
				baseItems = jQuery('a.scrollTo');
				// Anchors corresponding to menu items
				scrollItems = baseItems.map(function(){
					var link = $(this).is('a') ? $(this) : $($(this).find('a')[0]);
					// if (! jQuery(link.attr('href')).length) return;
					var pattern = /#(.*)/;
					var targetEl = link.attr('href').match(pattern);

						if (targetEl == null ) return;
						if (! targetEl[0]) return;
						if (! jQuery(targetEl[0]).length) return;
						var item = jQuery(targetEl[0]);
					if (item.length) { return item; }
				});
				// Bind to scroll
				jQuery(window).scroll(function(){
					scrolltock_setActiveItem();
				});
				
				function scrolltock_setActiveItem() {
					// Get container scroll position
					var fromTop = jQuery(this).scrollTop()- (". (int) $offsety . ") + 2;

					// Get id of current scroll item
					var cur = scrollItems.map(function(){
						if (jQuery(this).offset().top < fromTop)
							return this;
					});
					if (cur.length) {
						// Get the id of the current element
						cur = cur[cur.length-1];
						var id = cur && cur.length ? cur[0].id : '';

						if (lastId !== id) {
						   lastId = id;
						   // Set/remove active class
							baseItems.parent().parent().find('.active').removeClass('active');
							baseItems
							 .parent().removeClass('active')
							 .end().filter('[href$=\"#'+id+'\"]').parent().addClass('active');
						}
					} else {
						baseItems.parent().parent().find('.active').removeClass('active');
						baseItems.parent().removeClass('active');
					}                  
				}
			}); // end of dom ready

			jQuery(window).load(function(){
				// loop through the scrolling links to check if the scroll to anchor is needed on the page load
				jQuery('a.scrollTo').each( function() {
					var link = jQuery(this).is('a') ? jQuery(this) : jQuery(jQuery(this).find('a')[0]);
					var pageurl = window.location.href;
					var linkurl = link.attr('href');
					var pattern = /#(.*)/;
					var targetLink = linkurl.match(pattern);
					var targetPage = pageurl.match(pattern);

					if (targetLink == null ) return;
					if (targetPage == null ) return;
					if (! targetLink.length) return;
					if (! jQuery(targetLink[0]).length) return;

					if (jQuery(targetPage[0]).length && targetLink[0] == targetPage[0]) {
						link.scrolltock();
					}
				});
			});";
//			echo "<script>" . $js. "</script>";
			if ( ! wp_script_is( 'jquery', 'done' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_add_inline_script( 'jquery-migrate', $js );
		}

	function load_inline_css() {
		$this->options = get_option(SCROLLTOCK_SETTINGS_FIELD);
		require_once(SCROLLTOCK_PATH . '/helpers/ckfields.php');
		$fields = new CKFields($this->options, SCROLLTOCK_SETTINGS_FIELD, $this->default_settings);
		$hideresolution = $fields->getValue('totop_hideresolution', '');

		$css = '.scrollToTop {
			padding:10px; 
			text-align:center; 
			font-weight: bold;
			text-decoration: none;
			position:fixed;
			bottom: 20px;
			right: 20px;
			display:none;
			width: 100px;
			height: 100px;
			z-index: 100;
			background: url(' . plugins_url() . '/scroll-to-ck/images/arrow_up.png) center center no-repeat;'
			. " } ";
		if ($hideresolution) {
			$css .= '@media only screen and (max-width:' . $this->testUnit($hideresolution) . '){'
					. '.scrollToTop {display: none !important;}'
					. '}';
		}
	?>
		<style type="text/css">
		<?php echo $css; ?>
		</style>
	<?php
	}

	public function testUnit($value) {
		if ((stristr($value, 'px')) OR (stristr($value, 'em')) OR (stristr($value, '%')) OR (stristr($value, 'auto')) ) {
			return $value;
		}

		if ($value == '') {
			$value = 0;
		}

		return $value . 'px';
	}
}

// load the process
$Scrolltock = Scrolltock::getInstance();
$Scrolltock->init();