<?php
/**
 * prestaAbstractUserconnector
 * @author ylybliamay
 *
 */
abstract class prestaAbstractUserConnector
{
	protected $params	= array();
	
	/**
	 * Constructor
	 * Call the setup function
	 */
	public function __construct( Array $params = array() )
	{
		$this->params	= $params;
		$this->setup();
	}
	
	/**
	 * Set the general configuration
	 * @abstract
	 */
	abstract public function setup();
	
	/**
	 * Get user nickname
	 * @param 	$projectUserId
	 * @return 	string
	 */
	abstract public function getUserNickName($projectUserId);
	
	/**
	 * Get user email
	 * @param 	$projectUserId
	 * @return 	string
	 */
	abstract public function getUserEmail($projectUserId);
	
	/**
	 * Get user culture
	 * @param 	$projectUserId
	 * @return 	string
	 */
	abstract public function getUserCulture($projectUserId);
	
	/**
	 * Get user status
	 * @param 	$projectUserId
	 * @return 	boolean
	 */
	abstract public function isUserEnabled($projectUserId);
	
	/**
	 * Get the current user id
	 * @return	integer
	 */
	abstract public function getCurrentUserId();
	
	/**
	 * Check if the current user is authenticated
	 * @return	boolean
	 */
	abstract public function isAuthenticated();
}