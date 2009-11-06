<?php
/**
 * Security User class uses between the myUser class and the sfGuardSecurityUser
 * in order to customize the security user according to the user connector
 * @author ylybliamay
 *
 */
class prestaForumConnectorSfGuardPropelSecurityUser extends sfGuardSecurityUser
{
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 */
	public function signIn($user, $remember = false, $con = null)
	{
		parent::signIn( $user, $remember, $con );
		prestaForumFactory::getForumConnectorInstance()->signIn($user->getId());
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 */
	public function signOut()
	{
		prestaForumFactory::getForumConnectorInstance()->signOut($this->getUserId());
		parent::signOut();
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 */
	public function getUserId()
	{
		return $this->getAttribute('user_id', 0, 'sfGuardSecurityUser');
	}
}
