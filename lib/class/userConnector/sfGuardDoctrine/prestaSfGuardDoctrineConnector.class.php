<?php
/**
 * prestaSfGuardPropelConnector is the user connector for sfGuardPropel
 * @author cdolivet
 *
 */
class prestaSfGuardDoctrineConnector extends prestaAbstractUserConnector
{
	
	/**
	 * setup function calle don construction
	 * 
	 * @author	Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @version	1.0 - 6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @since	6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @see		prestaForumConnectorPlugin/lib/class/userConnector/prestaAbstractUserConnector#setup()
	 */
	public function setup()
	{
		$this->params	= array_merge( array(
			'getUsernameMethod'		=> 'getUsername',
			'getEmailMethod'		=> 'getEmail',
			'getIsActiveMethod'		=> 'getIsActive',
 			'setUsernameMethod'		=> 'setUsername',
			'setEmailMethod'		=> 'setUsername',
			'setPasswordlMethod'	=> 'setPassword',
			'setIsActiveMethod'		=> 'setIsActive',
      
		), $this->params );
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserNickName($projectUserId)
	{
		$user		= Doctrine::getTable('sfGuardUser')->find($projectUserId);
		return call_user_func( array( $user, $this->params['getUsernameMethod'] ) );
	}
	
	/*
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function getUserEmail($projectUserId)
	{
		$user		= Doctrine::getTable('sfGuardUser')->find($projectUserId);
		return call_user_func( array( $user, $this->params['getEmailMethod'] ) );
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
	public function isUserEnabled($projectUserId)
	{
		$user		= Doctrine::getTable('sfGuardUser')->find($projectUserId);
		return call_user_func( array( $user, $this->params['getIsActiveMethod'] ) );
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
	 * Get all user's id
	 * 
	 * @author	Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @version	1.0 - 9 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @since	9 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @return	Array of user ids
	 */
	public function getAllUserId()
	{
		$q	= Doctrine::getTable('sfGuardUser')->createQuery('u')->select('u.id');
		$a_userIds = $q->execute();
			var_dump( $a_userIds );die;
		return $a_userIds;
	}
}