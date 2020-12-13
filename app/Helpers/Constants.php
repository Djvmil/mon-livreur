<?php
namespace App\Helpers;
/**
 * 
 * @author El Hadji Dame Cisse
 *
 */
class Constants
{
    const MAIL_SEPARATOR=";";
	 const STATUS_DISABLE=0;
	 const STATUS_ENABLE=1; 
	 const TYPE_AUTOMATIC="AUTOMATIC";
	 const TYPE_MANUAL="MANUAL";
	
	
	
	 const STEP_PAGINATE=25;
	 const NO_PAGINATE=-1;
	 const DEFAULT_PAGE=1;
	
	 // Client ID: 3
	 // Client Secret: ppvTSfzG52P4uOXYlwvtYdqaf5iVvT1fNe9daXtM
	       
	 const OTP_TYPE_REGISTER = "REGISTER_OTP"; 


	 const USER_TYPE_ADMIN = 1;
	 const USER_TYPE_CLIENT = 2;
	 const USER_TYPE_PRESTATAIRE = 3;


	 const STATUS_OTP_NOT_USE = "NOT_CONSUMED";
	 const STATUS_OTP_CONSUMED = "CONSUMED";
	 const STATUS_OTP_BLOCKED = "BLOCKED";


	 static function OTP_CODE()
	 {
		 $chiffres = array('0','1','2','3','4','5','6','7','8','9');
		 $positions = array_rand($chiffres, 4);
		 $otpCode = null;
		  
		 foreach($positions as $valeur) $otpCode .= $chiffres[$valeur];
		  
		 return $otpCode;
	 }


	 const CHECK_TYPE_USER = "TYPE_USER";
	 const CHECK_TYPE_VALUE_EXIST = "VALUE_EXIST"; 
	 const CHECK_TYPE_REGISTER = "TYPE_REGISTER"; 
}