<?

function encrypt_data($data,$key) {
	$handle = mcrypt_module_open(MCRYPT_BLOWFISH,'',MCRYPT_MODE_ECB,'');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($handle), MCRYPT_RAND);
    mcrypt_generic_init($handle, $key, $iv);
    $encrypted_data = mcrypt_generic($handle, $data);
    mcrypt_generic_deinit($handle);
    mcrypt_module_close($handle);
	return $encrypted_data;
}

function decrypt_data($data,$key) {
	$handle = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_ECB, '');
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($handle), MCRYPT_RAND);
	mcrypt_generic_init($handle, $key, $iv);
	$encrypted_data = mdecrypt_generic($handle,$data);
	mcrypt_generic_deinit($handle);
	mcrypt_module_close($handle);
	return $encrypted_data;
}