<?php
/**
 * Mock interface for forum connector
 * @author 	ylybliamay
 *
 */
interface prestaForumConnectorMockInterface
{
	/**
	 * Override the protected method ::convertMailAddressToNickName()
	 * @param	$address
	 * @return 	string
	 */
	public function convertMailAddressToNickName($address);
	
	/**
	 * Override the protected method ::nickNameAlreadyUse()
	 * @param	$nickname
	 * @param 	$forumUserId
	 * @return 	boolean
	 */
	public function nickNameAlreadyUse($nickname, $forumUserId = 0);
	
	/**
	 * Override the protected method ::projectUserExist()
	 * @param 	$projectUserId
	 * @return	boolean
	 */
	public function projectUserExist($projectUserId);
}