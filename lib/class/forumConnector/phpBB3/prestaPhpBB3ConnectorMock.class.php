<?php
/**
 * Mock Class used for unit tests
 * @author ylybliamay
 *
 */
class prestaPhpBB3ConnectorMock extends prestaPhpBB3Connector implements prestaForumConnectorMockInterface
{
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0	- 2009-10-29 - ylybliamay
	 */
	public function convertMailAddressToNickName($address)
	{
		return parent::convertMailAddressToNickName($address);
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0	- 2009-10-29 - ylybliamay
	 */
	public function nickNameAlreadyUse($nickname, $forumUserId = 0)
	{
		return parent::nickNameAlreadyUse($nickname, $forumUserId);
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0	- 2009-10-29 - ylybliamay
	 */
	public function projectUserExist($projectUserId)
	{
		return parent::projectUserExist($projectUserId);
	}
}