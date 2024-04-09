<?php
class Util_Regex {
	public static $email = '/^.+@.+$/';
	public static $password = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\+\-#\^])[A-Za-z\d@$!%*?&\+\-#\^]{8,64}$/';
	public static $name = '/^[a-zA-Z ]{2,50}$/';
	public static $phonenumber = '/^\+?[1-9]\d{3,14}$/';
	public static $dob = '/^\d{4}-\d{2}-\d{2}$/';
	public static $shop_name = '/^[a-zA-Z0-9\' ]{2,50}$/';
	public static $shop_subdomain = '/^[a-zA-Z0-9]{2,30}$/';

	public static $address_line = '/^[a-zA-Z0-9\' ]{4,50}$/';
	public static $address_postal = '/^[a-zA-Z0-9\' ]{4,15}$/';
	public static $address_city = '/^[a-zA-Z\' ]{4,50}$/';
	public static $address_country = '/^[a-zA-Z\' ]{4,40}$/';

	public static $product_name = '/^[a-zA-Z0-9\'\-| ]{2,50}$/';
	public static $product_subtitle = '/^[a-zA-Z0-9\'&?\/\\ ]{10,100}$/';
	public static $product_description = '/^[a-zA-Z0-9\'&?.\/\\ ]{25,1000}$/';
	public static $product_currency = '/^EUR|USD|GBP|AUD|CAD$/';
	public static $product_cycle = '/^monthly|yearly$/';
	public static $group_item_type = '/^text|textfield|phonenumber|email|picture|colors|file|checkbox|radio|select|link|popup|toggle$/';
	public static $group_item_condition_type = '/^AND|OR$/';

	public static $domain = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/';

	public static $twitterusername = '/^(\w){1,15}$/';
	public static $linkedin = '/^https?:\/\/((www|\w\w)\.)?linkedin.com\/((in\/[^\/]+\/?)|(pub\/[^\/]+\/((\w|\d)+\/?){3}))$/';
	public static $instagramusername = '/^[a-zA-Z0-9._]{3,30}$/';
	public static $facebookusername = '/^[a-z\d.]{1,}$/';

	public static $iban = '/^[A-Z]{2}(?:[ ]?[0-9]){18,20}$/';
	public static $vat_nl = '/(NL)?[0-9]{9}B[0-9]{2}/';
	public static $kvk = '/[0-9]{8}/';

	public static $uuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/';
}
