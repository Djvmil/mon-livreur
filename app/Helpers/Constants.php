<?php
namespace App\Helpers;
/**
 * 
 * @author El Hadji Dame Cisse
 *
 */
class Constante
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
}