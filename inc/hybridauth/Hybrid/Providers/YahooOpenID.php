<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/**
 * Hybrid_Providers_Yahoo OpenID based
 * 
 * Provided as a way to keep backward compatibility for Yahoo OpenID based on HybridAuth <= 2.1.0
 * 
 * http://hybridauth.sourceforge.net/userguide/IDProvider_info_Yahoo.html
 */
class Hybrid_Providers_Yahoo extends Hybrid_Provider_Model_OpenID
{
	var $openidIdentifier = "https://open.login.yahooapis.com/openid20/www.yahoo.com/xrds"; 

	/**
	* finish login step 
	*/
	function loginFinish()
	{
		parent::loginFinish();

		$this->user->profile->emailVerified = $this->user->profile->email;

		// restore the user profile
		Hybrid_Auth::storage()->set( "hauth_session.{$this->providerId}.user", $this->user );
	}
        
        function getUserContacts()
	{
//		$userId = $this->getCurrentUserId();

		$parameters = array();
		$parameters['format']	= 'json';
		$parameters['count'] = 'max';
		
		$response = $this->api->get('http://social.yahooapis.com/v1/user/me/contacts', $parameters);

		if ( $this->api->http_code != 200 )
		{
			throw new Exception( 'User contacts request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
		}

		if ( !$response->contacts->contact && ( $response->errcode != 0 ) )
		{
			return array();
		}

		$contacts = array();

		foreach( $response->contacts->contact as $item ) {
			$uc = new Hybrid_User_Contact();

			$uc->identifier   = $this->selectGUID( $item );
			$uc->email        = $this->selectEmail( $item->fields );
			$uc->displayName  = $this->selectName( $item->fields );
			$uc->photoURL     = $this->selectPhoto( $item->fields );

			$contacts[] = $uc;
		}
		
		return $contacts;
	}
}
