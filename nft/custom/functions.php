<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function my_admin_menu() {
	add_menu_page(
		__( 'Nft', 'my-textdomain' ),	
		__( 'Nft', 'my-textdomain' ),	
		'manage_options',	
		'sample-page',	
		'my_admin_page_contents',	
		'https://techstocksensei.com/wp-content/uploads/2021/04/NFT_Icon-65x65.png',	
		3	
	);	
}
	
	
add_action( 'admin_menu', 'my_admin_menu' );
	
	
	
function my_admin_page_contents() {
	global $wpdb;
    $table_name = $wpdb->prefix.'nft_meta_data';

	$contract_address_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'contract-address'" );
	$infura_key_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'infura-key'" );
	$network_type_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'network-type'" );

	if(empty($contract_address_array)) {
		$contract_address = '';
	} else {
		$contract_address = $contract_address_array[0]->meta_value;
	}
	if(empty($infura_key_array)) {
		$infura_key = '';
	} else {
		$infura_key = $infura_key_array[0]->meta_value;
	}
	if(empty($network_type_array)) {
		$network_type = '0';
	} else {
		$network_type = $network_type_array[0]->meta_value;
	}
?>
	<h2>Nft SetUp</h2>
	<div class="blockchain-settings-container">
		<form method="post" class="blockchain-settings-form-container" action="<?php echo plugin_dir_url( __FILE__ ) . "/save-nft-settings.php"  ?>">
			<div class="blockchain-settings-form marginY-10px">
				<label class="blockchain-settings-form-label" for="contract-address">Contract Address :</label>
				<input class="blockchain-settings-form-input" name="contract-address" id="contract-address" type="text" placeholder="Contract Address" value="<?php echo $contract_address ?>">
			</div>
			<div class="blockchain-settings-form marginY-10px">
				<label class="blockchain-settings-form-label" for="infura-key">Infura Key :</label>
				<input class="blockchain-settings-form-input" name="infura-key" id="infura-key" type="text" placeholder="Infura Key" value="<?php echo $infura_key ?>">
			</div>
			<div class="blockchain-settings-form marginY-10px">
			<label class="blockchain-settings-form-label" for="network-type">Network Type :</label>
				<select id="network-type" name='network-type'>
					<option id="0" value="0">Select Network:</option>
					<option id="https://mainnet.infura.io/v3/0" value="https://mainnet.infura.io/v3/">Mainnet</option>
					<option id="https://ropsten.infura.io/v3/" value="https://ropsten.infura.io/v3/">Ropsten Testnet</option>
					<option id="https://rinkeby.infura.io/v3/" value="https://rinkeby.infura.io/v3/">Rinkeby Testnet</option>
					<option id="https://kovan.infura.io/v3/" value="https://kovan.infura.io/v3/">Kovan Testnet</option>
				</select>
			</div>
			<button type="submit" class="blockchain-settings-form-submit-button marginY-10px">Save</button>
		</form>
	</div>
	
<?php

}


add_filter( 'post_row_actions', 'modify_list_row_actions', 10, 2 );
 
function modify_list_row_actions( $actions, $post ) {

	global $wpdb;

    $table_name = $wpdb->prefix.'nft_meta_data';

	$contract_address_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'contract-address'" );
    $infura_key_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'infura-key'" );

	$tx_hash = get_post_meta( $post->ID, 'tx_hash', true );
    // Check for your post type.

	if ( $post->post_type == "product" && empty($tx_hash) && !empty($contract_address_array) && !empty($infura_key_array) ) {
		$title = _draft_or_post_title();
		$product = wc_get_product( $post->ID );
		$price = $product->get_sale_price();

		$actions['sell'] = sprintf(
			'<button 
				class="mint-button" 
				onclick="mintNFT(event, %s, %s)"
				aria-label="%s">%s</button>',
			$post->ID,
			$price,
			/* translators: %s: Post title. */
			esc_attr( sprintf( __( 'Mint' ), $title ) ),
			_x( 'Mint', 'verb' )
		);
    } else if( $post->post_type == "product" && !empty($tx_hash) ) {
		$actions['mint_success'] = sprintf(
			'</br><a href="https://ropsten.etherscan.io/tx/'.$tx_hash.'" class="mint-success">
				%s
			</a>',
			/* translators: %s: Post title. */
			esc_attr( sprintf( __( 'NFT - Transaction' ) ) ),
		);
	}
    return $actions;
}

add_action('wp_ajax_save_transaction_hash', 'save_transaction_hash');

function save_transaction_hash() {
	$sth_post_id = $_POST["post_id"];
	$tx_hash = $_POST["txHash"];

	add_post_meta( $sth_post_id, 'tx_hash', $tx_hash, true );

	echo $tx_hash;
	die();
}

add_action('admin_enqueue_scripts', function(){

	global $wpdb;
    $table_name = $wpdb->prefix.'nft_meta_data';

	$contract_address_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'contract-address'" );
	$infura_key_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'infura-key'" );
	$network_type_array = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'network-type'" );

	if(empty($contract_address_array)) {
		$contract_address = '';
	} else {
		$contract_address = $contract_address_array[0]->meta_value;
	}
	if(empty($infura_key_array)) {
		$infura_key = '';
	} else {
		$infura_key = $infura_key_array[0]->meta_value;
	}
	if(empty($network_type_array)) {
		$network_type = '';
	} else {
		$network_type = $network_type_array[0]->meta_value;
	}

	wp_enqueue_style( 'myStyle', plugin_dir_url( __FILE__ ) . '/style.css' );
	wp_enqueue_script('web3Scripts', 'https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js');
	wp_enqueue_script('myCustomScript', plugin_dir_url( __FILE__ ) . '/custom.js', array('web3Scripts'), '1.0', true);

	wp_localize_script( 'myCustomScript', 'ADMIN_CONFIG',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'contract_address' => $contract_address,
            'infura_key' => $infura_key,
			'network_type' => $network_type,
        )
    );

	wp_enqueue_script('setNetworkScript', plugin_dir_url( __FILE__ ) . '/setNetwork.js', array(), '1.0', true);

	wp_localize_script( 'setNetworkScript', 'NETWORK',
        array( 
            'type' => $network_type,
        )
    );
});


function insert_image_before_title( $title, $id = null ) {

	$post = get_post( $id );
	$tx_hash = get_post_meta( $id, 'tx_hash', true );

	if( $post->post_type == "product" && !empty($tx_hash) ) {
		$img_source = 'https://techstocksensei.com/wp-content/uploads/2021/04/NFT_Icon-65x65.png';
		$title = '<img class="icon_title" src="'. $img_source .'" />' . $title;
	}

    return $title;
}
add_filter( 'the_title', 'insert_image_before_title', 10, 2 );