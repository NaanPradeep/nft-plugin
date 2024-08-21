<?php

require_once('../../../../wp-load.php');

if(isset($_POST["contract-address"])) {
    $contract_address = clean_data($_POST["contract-address"]);
}

if(isset($_POST["infura-key"])) {
    $infura_key = clean_data($_POST["infura-key"]);
}

if(isset($_POST["network-type"])) {
    $network_type = clean_data($_POST["network-type"]);
}

if(isset($contract_address) && isset($infura_key) && isset($network_type) && $network_type !== '0') {
    global $wpdb;

    $table_name = $wpdb->prefix.'nft_meta_data';

    $retrieve_contract_address = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'contract-address'" );
    $retrieve_infura_key = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'infura-key'" );
    $retrieve_network_type = $wpdb->get_results( "SELECT meta_value FROM $table_name WHERE meta_key = 'network-type'" );

    if(empty($retrieve_contract_address) && empty($retrieve_infura_key) && empty($retrieve_network_type)) {
        $data1 = array(
            'meta_key' => 'contract-address',
            'meta_value' => $contract_address
        );
    
        $data2 = array(
            'meta_key' => 'infura-key',
            'meta_value' => $infura_key
        );

        $data3 = array(
            'meta_key' => 'network-type',
            'meta_value' => $network_type
        );
    
        $wpdb->insert( $table_name, $data1);
        $wpdb->insert( $table_name, $data2);
        $wpdb->insert( $table_name, $data3);
    } else {
        $sql1 = $wpdb->prepare("UPDATE $table_name SET meta_value = %s WHERE meta_key = 'contract-address'", $contract_address);
        $sql2 = $wpdb->prepare("UPDATE $table_name SET meta_value = %s WHERE meta_key = 'infura-key'", $infura_key);
        $sql3 = $wpdb->prepare("UPDATE $table_name SET meta_value = %s WHERE meta_key = 'network-type'", $network_type);
        $wpdb->query($sql1);
        $wpdb->query($sql2);
        $wpdb->query($sql3);
    }
    wp_redirect( admin_url( 'admin.php?page=sample-page' ) );
} else {
    wp_redirect( admin_url( 'admin.php?page=sample-page' ) );
}

function clean_data($data) {
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
     return $data;
}