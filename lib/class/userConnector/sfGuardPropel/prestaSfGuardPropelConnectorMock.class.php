<?php
/**
 * Mock Class used for unit tests
 * @author ylybliamay
 *
 */
class prestaSfGuardPropelConnectorMock extends prestaSfGuardPropelConnector implements prestaUserConnectorMockInterface
{
	/**
	 * Add a user
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0 - 2009-10-29 - ylybliamay
	 * @param 	$nickname
	 * @param 	$email
	 * @param 	$password
	 * @param 	$active
	 * @return	userId
	 */
	public function addUserTest($nickname, $email, $password, $active = 1)
	{
		$options 		= sfConfig::get( 'app_prestaForumConnector_userConnector' );
		$setUsername	= $options['setUsernameMethod'];
		$setEmail		= $options['setEmailMethod'];
		
		$user = new sfGuardUser();
		call_user_func(array($user,$setUsername),$nickname);
		call_user_func(array($user,$setEmail),$email);
		$user->setPassword($password);
		$user->setIsActive($active);
		$user->save();
		return $user->getId();
	}
}