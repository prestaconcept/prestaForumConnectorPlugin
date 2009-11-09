<?php
/**
 * Mock Class used for unit tests
 * @author cdolivet
 *
 */
class prestaSfGuardDoctrineConnectorMock extends prestaSfGuardDoctrineConnector implements prestaUserConnectorMockInterface
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
		$user = new sfGuardUser();
		call_user_func( array($user, $this->params['setUsernameMethod']), $nickname );
		call_user_func( array($user, $this->params['setEmailMethod']), $email );
		call_user_func( array($user, $this->params['setPasswordMethod']), $password );
		call_user_func( array($user, $this->params['setIsActiveMethod']), $active );
		$user->save();
		return $user->getId();
	}
}