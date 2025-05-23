<?php
/**
 * Theme functions and definitions.
 *
 * Sets up the theme and provides some helper functions
 * For more information on hooks, actions, and filters,
 * see http://codex.wordpress.org/Plugin_API
 *
 * @package Vast
 */

// Core Constants.
define( 'VAST_THEME_DIR', get_template_directory() );
define( 'VAST_THEME_URI', get_template_directory_uri() );
// Minimum required PHP version.
define( 'VAST_THEME_REQUIRED_PHP_VERSION', '5.2.4' );

/**
 * Set global content width
 *
 * @link https://developer.wordpress.com/themes/content-width/
 */
if ( ! isset( $content_width ) ) {
	$content_width = 900;
}

/**
 * Global variables
 */
$vast_customizer_panels   = array();
$vast_customizer_settings = array();
$vast_customizer_controls = array(
	'vast-image-selector' => 'Vast_Image_Selector_Control',
	'vast-switcher'       => 'Vast_Switcher_Control',
	'vast-input-slider'   => 'Vast_Input_Slider_Control',
	'vast-alpha-color-picker' => 'Vast_Alpha_Color_Picker_Control',
	'vast-icon-picker' => 'Vast_Icon_Picker_Control',
	'vast-link' => 'Vast_Link_Control',
);

/**
 * Load all core theme function files.
 */
require get_parent_theme_file_path( '/inc/helpers.php' );
require get_parent_theme_file_path( '/inc/hooks.php' );
require get_parent_theme_file_path( '/inc/lib/class-vast-mobile-nav-walker.php' );
require get_parent_theme_file_path( '/inc/lib/webfonts.php' );
require get_parent_theme_file_path( '/inc/lib/class-tgm-plugin-activation.php' );
require get_parent_theme_file_path( '/inc/lib/class-vast-customizer-config.php' );
require get_parent_theme_file_path( '/inc/lib/class-vast-customizer-loader.php' );
require get_parent_theme_file_path( '/inc/lib/class-vast-walker-page.php' );

/**
 * Load panels, sections and settings.
 */
require get_parent_theme_file_path( '/inc/customizer/panels.php' );
require get_parent_theme_file_path( '/inc/customizer/sections.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/general.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/header.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/colors.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/footer.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/default.php' );
require get_parent_theme_file_path( '/inc/customizer/settings/topbar.php' );

/**
 * Class instance init.
 */
$vast_customizer_loader = new Vast_Customizer_Loader();

add_action( 'tgmpa_register', 'vast_register_required_plugins' );

/**
 * Vast TGMPA
 */
function vast_register_required_plugins() {

	$plugins = array(
			array(
				'name'                  => esc_html__( 'Contact Form 7','vast' ),
				'slug'                  => 'contact-form-7',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'King Composer','vast' ),
				'slug'                  => 'kingcomposer',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'WooCommerce','vast' ),
				'slug'                  => 'woocommerce',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'Elementor','vast' ),
				'slug'                  => 'elementor',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'Smart Slider 3','vast' ),
				'slug'                  => 'smart-slider-3',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'Vast Package','vast' ),
				'slug'                  => 'vast-package',
				'required'              => false,
			),
			array(
				'name'                  => esc_html__( 'Vast Demo Import','vast' ),
				'slug'                  => 'vast-demo-import',
				'required'              => false,
			),
		);

	$config = array(
		'id'           => 'vast',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);

	tgmpa( $plugins, $config );

}
// Função para salvar a pontuação do jogo na base de dados
function save_game_score($user_id, $game_name, $game_id, $score, $time_spent) {
    global $wpdb;

    // Definir o nome correto da tabela, garantindo que utiliza o prefixo do WordPress
    $table_name = $wpdb->prefix . "game_scores";

    // Verificar se os dados recebidos estão corretos
    error_log("Tentando salvar: user_id = $user_id, game_name = $game_name, game_id = $game_id, score = $score, time_spent = $time_spent");

    // Inserir os dados na tabela
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id'    => $user_id,
            'game_name'  => $game_name,
            'game_id'    => $game_id,
            'score'      => $score,
            'time_spent' => $time_spent
        ),
        array('%d', '%s', '%d', '%d', '%s')
    );

    // Verificar se a inserção foi bem-sucedida
    if ($inserted) {
        return "Pontuação salva com sucesso!";
    } else {
        error_log("Erro ao salvar pontuação: " . $wpdb->last_error);
        error_log("Query executada: " . $wpdb->last_query);
        return "Erro ao salvar pontuação: " . $wpdb->last_error;
    }
}


// Executar a função ao ativar o tema ou plugin
register_activation_hook(__FILE__, 'create_game_scores_table');


function save_game_score_ajax() {
    // Verifica se os dados necessários foram enviados
    if (isset($_POST['game_name']) && isset($_POST['score']) && isset($_POST['time_spent'])) {
        global $wpdb;

        // Sanitiza os dados recebidos
        $user_id = get_current_user_id();
        $game_name = sanitize_text_field($_POST['game_name']);
        $score = intval($_POST['score']);
        $time_spent = sanitize_text_field($_POST['time_spent']);

        // Nome da tabela (verifica se está correto no phpMyAdmin)
        $table_name = $wpdb->prefix . 'game_scores';

        // Insere os dados na tabela
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'game_name' => $game_name,
                'score' => $score,
                'time_spent' => $time_spent,
                'date' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s', '%s')
        );

        // Verifica se a inserção foi bem-sucedida
        if ($wpdb->insert_id) {
            error_log("yap funfou $table_name");
            wp_send_json_success('Pontuação salva com sucesso!');
        } else {
            wp_send_json_error('Erro ao salvar a pontuação.');
        }
    } else {
        wp_send_json_error('Dados incompletos.');
    }
}

// Registar as ações do AJAX para usuários logados e não-logados
add_action('wp_ajax_save_game_score', 'save_game_score_ajax');
add_action('wp_ajax_nopriv_save_game_score', 'save_game_score_ajax');

function enqueue_h5p_custom_script() {
    wp_enqueue_script('h5p-custom', get_template_directory_uri() . '/assets/js/h5p-custom.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_h5p_custom_script');


function add_ajaxurl_to_head() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>"
    </script>
    <?php
}
add_action('wp_head', 'add_ajaxurl_to_head');

function ocultar_solicitar_profissional($items, $args) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = get_current_user_id();
        $profissional_atribuido = get_user_meta($user_id, 'profissional_atribuido', true);

        // Verifica se o utilizador é um "Utente" e se ainda não solicitou um profissional
        if (in_array('utente', (array) $user->roles) && !$profissional_atribuido) {
            return $items; // Mostra a aba
        } else {
            return str_replace('<li><a href="/solicitar-profissional">Solicitar Profissional</a></li>', '', $items);
        }
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'ocultar_solicitar_profissional', 10, 2);
