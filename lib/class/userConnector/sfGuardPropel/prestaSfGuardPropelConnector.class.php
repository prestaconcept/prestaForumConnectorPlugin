<?php
/**
 * prestaSfGuardPropelConnector is the user connector for sfGuardPropel
 * @author ylybliamay
 *
 */
class prestaSfGuardPropelConnector extends prestaAbstractUserConnector
{
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserNickName($projectUserId)
	{
		$user 		= sfGuardUserPeer::retrieveByPK($projectUserId);
		$options 	= sfConfig::get( 'app_prestaForumConnector_userConnector' );
		$method 	= $options['getUsernameMethod'];
		return call_user_func(array($user,$method));
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserEmail($projectUserId)
	{
		$user		= sfGuardUserPeer::retrieveByPK($projectUserId);
		$options 	= sfConfig::get( 'app_prestaForumConnector_userConnector' );
		$method 	= $options['getEmailMethod'];
		return call_user_func(array($user,$method));
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserCulture($projectUserId)
	{
		/**
		 * TODO
		 */
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserEnabled($projectUserId)
	{
		$user = sfGuardUserPeer::retrieveByPK($projectUserId);
		return $user->getIsActive();
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getCurrentUserId()
	{
		/**
		 * TODO
		 */
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function isAuthenticated()
	{
		/**
		 * TODO
		 */
	}
	
	/**
	 * Get all user id
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-28 - ylybliamay
	 * @since	1.0 - 2009-10-28 - ylybliamay
	 * @return	array
	 */
	public function getAllUserId()
	{
		$c = new Criteria();
		$c->clearSelectColumns();
		$c->addSelectColumn(sfGuardUserPeer::ID);

		$stmt = sfGuardUserPeer::doSelectStmt($c);
		
		$result = array();
		while($row = $stmt->fetch(PDO::FETCH_NUM))
		{
			$result[] = $row[0];
		}
		return $result;
	}
}