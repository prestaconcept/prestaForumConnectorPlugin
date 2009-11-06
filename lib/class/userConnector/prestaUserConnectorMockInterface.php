<?php
/**
 * Mock interface for user connector
 * @author ylybliamay
 *
 */
interface prestaUserConnectorMockInterface
{
	/**
	 * Add a user
	 * @param 	$nickname
	 * @param 	$email
	 * @param 	$password
	 * @param 	$active
	 * @return	user id
	 */
	public function addUserTest($nickname, $email, $password, $active = true );
}