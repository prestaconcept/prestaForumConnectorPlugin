<?php
/**
 * prestaPhpBB3Connector is the forum connector for PhpBB3
 * @author ylybliamay
 *
 */
class prestaPhpBB3Connector extends prestaAbstractForumConnector
{
	public $db;
	public $dbprefix;
	public $phpbb_root_path;
	
	/**
	 * Get the database connection from the phpBB3 forum
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function setup()
	{
		global $db, $table_prefix;
		
		// define default values
		$this->params	= array_merge( array(
			'forumFieldProjectUserId'		=> 'project_user_id',
		), $this->params );
		
		
		// *************
		// *** Init phpBB3
		// *************
		
		if( !defined( 'IN_PHPBB' ) )
		{
			define('IN_PHPBB', true);
		}
		$this->phpbb_root_path = sfConfig::get( 'app_prestaForumConnector_forumWebDir' );
		$phpbb_root_path = $this->phpbb_root_path;
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		require_once $this->phpbb_root_path.'common.php';

		$this->db		= $db;
		$this->dbprefix	= $table_prefix;
		
		// *************
	}

	/**
	 * Sign in
	 * @var		$projectUserId
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 * @return	boolean
	 */
	public function signIn($projectUserId, $sessionId = false)
	{
		if(!$sessionId)
		{
			$sessionId = md5(uniqid('something'.rand(), true));
		}
		$sessionKey 		= '';
		$phpbbCookieName	= self::getConfigVal('cookie_name');
		$user_id 			= $this->getForumUserIdFromProjectUserId($projectUserId);
		$this->insertDbSession($sessionId, $sessionKey, $user_id);
		$this->setCookies($phpbbCookieName, $sessionId, $sessionKey, $user_id);
		$this->updateUserFields($user_id, array('lastvisit' => time()));
	}

	/**
	 * Sign out
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function signOut($projectUserId)
	{
		$phpbbCookieName 	= self::getConfigVal('cookie_name');
		$user_id 			= $this->getForumUserIdFromProjectUserId($projectUserId);

		$this->updateUserFields($user_id, array('lastvisit' => time()));
		$this->deleteDbSession($user_id);
		$this->unsetCookies($phpbbCookieName);
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-27 - ylybliamay
	 * @since	1.0 - 2009-10-27 - ylybliamay
	 */
	public function enableForumUser($projectUserId)
	{
		$forum_user_id = $this->getForumUserIdFromProjectUserId($projectUserId);
		$sql	= "UPDATE `". $this->dbprefix ."users`"
				. " SET `user_type` = 0, `user_inactive_reason` = 0 "
				. " WHERE `user_id` = ".$forum_user_id;
		$this->sqlExec($sql);
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-27 - ylybliamay
	 * @since	1.0 - 2009-10-27 - ylybliamay
	 */
	public function disableForumUser($projectUserId)
	{
		$forum_user_id = $this->getForumUserIdFromProjectUserId($projectUserId);
		$sql	= "UPDATE `". $this->dbprefix ."users`"
				. " SET `user_type` = 1, `user_inactive_reason` = 3 "
				. " WHERE `user_id` = ".$forum_user_id;
		$this->sqlExec($sql);
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0 - 2009-10-29 - ylybliamay
	 */
	public function deleteForumUser($projectUserId)
	{
		$this->disableForumUser($projectUserId);
		$sql	= "UPDATE ".$this->dbprefix."profile_fields_data "
				. "SET pf_".$this->params['forumFieldProjectUserId']." = NULL "
				. "WHERE pf_".$this->params['forumFieldProjectUserId']." = ".$projectUserId;
		$this->sqlExec($sql);
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 * @return	Return the projectUserId or false
	 */
	public function getProjectUserIdFromForumUserId($forumUserId)
	{
		$sql	= "SELECT pf_".$this->params['forumFieldProjectUserId']." FROM `". $this->dbprefix ."profile_fields_data` "
				. " WHERE user_id = ".$forumUserId;
		$result = $this->sqlExec($sql);
		$ar 	= $this->db->sql_fetchrow($result);
		if(is_array($ar) && array_key_exists('pf_project_user_id',$ar))
		{
			return $ar['pf_project_user_id'];
		}
		return false;
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 * @return	Return the forumUserId or false
	 */
	public function getForumUserIdFromProjectUserId($projectUserId)
	{
		$sql	= "SELECT user_id FROM `". $this->dbprefix ."profile_fields_data` "
				. " WHERE pf_".$this->params['forumFieldProjectUserId']." = ".$projectUserId;
		$result = $this->sqlExec($sql);
		$ar 	= $this->db->sql_fetchrow($result);
		if(is_array($ar) && array_key_exists('user_id',$ar))
		{
			return $ar['user_id'];
		}
		return false;
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 * @return	Return the nickname or false
	 */
	public function getUserNickName($projectUserId)
	{
		$sql	= "SELECT username FROM `". $this->dbprefix ."users` u,"
				. "`". $this->dbprefix ."profile_fields_data` d"
				. " WHERE d.pf_".$this->params['forumFieldProjectUserId']." = ".$projectUserId
				. " AND u.user_id = d.user_id";
		$result = $this->sqlExec($sql);
		$ar 	= $this->db->sql_fetchrow($result);
		if(is_array($ar) && array_key_exists('username',$ar))
		{
			return $ar['username'];
		}
		return false;
	}

	/**
	 * Check if the project user already exist
	 * @var		$projectUserId
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 * @return	boolean
	 */
	protected function projectUserExist($projectUserId)
	{
		$sql	= "SELECT user_id FROM `". $this->dbprefix ."profile_fields_data`"
				. " WHERE pf_".$this->params['forumFieldProjectUserId']." = ".$projectUserId;
		$result = $this->sqlExec($sql);
		$exist 	= mysql_num_rows($result);
		if($exist)
		{
			return true;
		}
		return false;
	}

	/**
	 * Synchronize project user and forum user
	 * @param 	$projectUserId
	 * @return 	boolean
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function synchUser($projectUserId)
	{
		if(!$this->projectUserExist($projectUserId))
		{
			$this->createUser($projectUserId);
		}
		else
		{
			$this->updateUser($projectUserId);
		}
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function createUser($projectUserId, $group_id = 2)
	{
		$nickname	= $this->convertToForumNickName(prestaForumFactory::getUserConnectorInstance()->getUserNickName($projectUserId),$projectUserId);
		$remote_ip	= sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote');

		$sql	= "INSERT INTO `". $this->dbprefix ."users` "
				. " (group_id,user_permissions,user_sig,user_occ,user_interests,user_ip,user_regdate,username,username_clean,user_email)"
				. " VALUES (".$group_id.",'','','','','".$remote_ip."',".time().",'".$nickname."','".$nickname."','".prestaForumFactory::getUserConnectorInstance()->getUserEmail($projectUserId)."')";
		$this->sqlExec($sql);

		$user_id = $this->db->sql_nextid();

		$sql	= "INSERT INTO `". $this->dbprefix ."user_group` "
				. " (group_id,user_id,user_pending)"
				. " VALUES (".$group_id.",".$user_id.",0)";
		$this->sqlExec($sql);

		$sql	= "INSERT INTO `". $this->dbprefix ."profile_fields_data` "
				. " (user_id,pf_".$this->params['forumFieldProjectUserId'].")"
				. " VALUES (".$user_id.",".$projectUserId.")";
		$this->sqlExec($sql);

		$this->setConfigVal('newest_user_id', $user_id, true);
		$this->setConfigVal('newest_username', $nickname, true);
		$this->setConfigCount('num_users', 1, true);

		$sql = 'SELECT group_colour
				FROM ' . $this->dbprefix . 'groups
				WHERE group_id = ' . (int) $group_id;
		
		$result = $this->sqlExec($sql);
		$row = $this->db->sql_fetchrow($result);
		if(array_key_exists(0,$row))
		{
			$this->setConfigVal('newest_user_colour', $row[0]['group_colour'], true);
		}
		
		if(!prestaForumFactory::getUserConnectorInstance()->isUserEnabled($projectUserId))
		{
			$this->disableForumUser($projectUserId);
		}
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-27 - ylybliamay
	 * @since	1.0 - 2009-10-27 - ylybliamay
	 */
	protected function updateUser($projectUserId)
	{
		$forum_user_id	= $this->getForumUserIdFromProjectUserId($projectUserId);
		$nickname 	= $this->convertToForumNickName(prestaForumFactory::getUserConnectorInstance()->getUserNickName($projectUserId),$projectUserId ,$forum_user_id);
		$email		= prestaForumFactory::getUserConnectorInstance()->getUserEmail($projectUserId);

		$sql	= "UPDATE `". $this->dbprefix ."users` "
				. "SET ";

		if(prestaForumFactory::getUserConnectorInstance()->getUserNickName($projectUserId) != $nickname || $nickname != $this->getUserNickName($projectUserId))
		{
			$sql .= "`username` = '". $nickname ."', `username_clean` = '".$nickname."', ";
		}
		$sql	.= "`user_email` = '". $email ."' "
				. "WHERE `user_id` = ". $forum_user_id;
		$this->sqlExec($sql);

		$enabled = prestaForumFactory::getUserConnectorInstance()->isUserEnabled($projectUserId);
		if($enabled)
		{
			$this->enableForumUser($projectUserId);
		}
		else
		{
			$this->disableForumUser($projectUserId);
		}
	}

	/**
	 * This method allow us to patch the forum in order to install the prerequist
	 * for the plugin uses
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-27 - ylybliamay
	 * @since	1.0 - 2009-10-27 - ylybliamay
	 */
	public function patchForum( sfBaseTask $sfTask )
	{
		// Set general config
		$this->patchGeneralConfig( $sfTask );
		
		// Add the custom data in order to create a link between project and forum database
		$this->patchAddCustomField( $sfTask );
		// Disable user profile edition (can't change email or password)
		$this->patchDisableUserProfileEdition( $sfTask );
		
		$this->patchDisableRegistration( $sfTask );
		
		// Delete links and form for log in from the forum
		$this->patchDisableLogin( $sfTask );
		
		$sfTask->logSection( "Clear file cache", null, null, $this->clearCache() ? 'INFO' : 'ERROR' );
	}
	
	/**
	 * Add a custom field
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 * @return 	boolean
	 */
	public function patchAddCustomField( sfBaseTask $sfTask )
	{
		$field	= $this->params['forumFieldProjectUserId'];
		
		// Check if this field already exist
		$sql = "SELECT `field_id` FROM `".$this->dbprefix."profile_fields` WHERE `field_name` = '". $field ."'";
		$result = $this->sqlExec($sql);
		$exist	= mysql_num_rows($result);
		if(!$exist)
		{
			$sql = "INSERT INTO `".$this->dbprefix."profile_fields` VALUES( NULL, '". $field ."', 1, '". $field ."', '10', '0', '0', '0', '0', '', 0, 0, 0, 1, 1, 1, 1)";
			$succeed	= $this->sqlExec($sql);
		}
		$sfTask->logSection( 'Database', 'Add custom field - part 1', null, $exist || $succeed ? 'INFO' : 'ERROR' );
		
		// Check if the field already create in the profile_fields_data table
		$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '". $this->dbprefix ."profile_fields_data' AND column_name = 'pf_". $field ."'";
		$result = $this->sqlExec($sql);
		$exist	= mysql_num_rows($result);
		if(!$exist)
		{
			$sql = "ALTER TABLE `".$this->dbprefix."profile_fields_data` ADD `pf_". $field ."` bigint(20)";
			$succeed	= $this->sqlExec($sql);
		}
		$sfTask->logSection( 'Database', 'Add custom field - part 2', null, $exist || $succeed ? 'INFO' : 'ERROR' );
	}
	
	/**
	 * Deactivate the forum registration
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 */
	protected function patchDisableRegistration( sfBaseTask $sfTask )
	{
		$sql = "UPDATE `". $this->dbprefix ."config` SET `config_value` = 3 WHERE `config_name` = 'require_activation'";
		$sfTask->logSection( 'Database', 'Disable registration', null, $this->sqlExec($sql) ? 'SUCCEED' : 'FAILURE' );
	}
	
	/**
	 * 
	 * 
	 * @author	Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @version	1.0 - 6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @since	6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @param	sfBaseTask $sfTask
	 */
	protected function patchDisableUserProfileEdition( sfBaseTask $sfTask )
	{
		$sql = "UPDATE `". $this->dbprefix ."modules` SET `module_enabled` = '0' WHERE `module_langname` = 'UCP_PROFILE_REG_DETAILS' LIMIT 1 ;";
		$sfTask->logSection( 'Database', 'Disable user profile edition', null, $this->sqlExec($sql) ? 'SUCCEED' : 'FAILURE' );
	}
	
	/**
	 * Deactivate login in the forum
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 * @return	boolean
	 */
	protected function patchDisableLogin( sfBaseTask $sfTask )
	{
		
		// *************
		// *** Disable logout link
		// *************
		
		$this->searchAndReplace(
			'<li class="icon-logout"><a href="{U_LOGIN_LOGOUT}" title="{L_LOGIN_LOGOUT}" accesskey="l">{L_LOGIN_LOGOUT}</a></li>',
			'<!-- IF S_USER_LOGGED_IN  --><li class="icon-logout">{L_LOGIN_LOGOUT}</li><!-- ENDIF -->',
			$this->phpbb_root_path.'styles/prosilver/template/overall_header.html', $sfTask );
		
		// *************
		
			
		// *************
		// *** Disable login form
		// *************
		
		$search	= <<<EOF
<!-- IF not S_USER_LOGGED_IN and not S_IS_BOT -->

		<form action="{S_LOGIN_ACTION}" method="post">

		<div class="panel">
			<div class="inner"><span class="corners-top"><span></span></span>

			<div class="content">
				<h3><a href="{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT}</a><!-- IF S_REGISTER_ENABLED -->&nbsp; &bull; &nbsp;<a href="{U_REGISTER}">{L_REGISTER}</a><!-- ENDIF --></h3>

				<fieldset class="fields1">
				<dl>
					<dt><label for="username">{L_USERNAME}:</label></dt>
					<dd><input type="text" tabindex="1" name="username" id="username" size="25" value="{USERNAME}" class="inputbox autowidth" /></dd>
				</dl>
				<dl>
					<dt><label for="password">{L_PASSWORD}:</label></dt>
					<dd><input type="password" tabindex="2" id="password" name="password" size="25" class="inputbox autowidth" /></dd>
					<!-- IF S_AUTOLOGIN_ENABLED --><dd><label for="autologin"><input type="checkbox" name="autologin" id="autologin" tabindex="3" /> {L_LOG_ME_IN}</label></dd><!-- ENDIF -->
					<dd><label for="viewonline"><input type="checkbox" name="viewonline" id="viewonline" tabindex="4" /> {L_HIDE_ME}</label></dd>
				</dl>
				<dl>
					<dt>&nbsp;</dt>
					<dd><input type="submit" name="login" tabindex="5" value="{L_LOGIN}" class="button1" /></dd>
				</dl>
				</fieldset>
			</div>

			<span class="corners-bottom"><span></span></span></div>
		</div>

		</form>

	<!-- ENDIF -->
EOF;
		$this->searchAndReplace( $search, '<!-- /* form removed */ -->', $this->phpbb_root_path.'styles/prosilver/template/viewforum_body.html', $sfTask );
		
		// *************
		
		
		// *************
		// *** disable login form
		// *************
		
		$search	= <<<EOF
<!-- IF not S_USER_LOGGED_IN and not S_IS_BOT -->
	<form method="post" action="{S_LOGIN_ACTION}" class="headerspace">
	<h3><a href="{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT}</a><!-- IF S_REGISTER_ENABLED -->&nbsp; &bull; &nbsp;<a href="{U_REGISTER}">{L_REGISTER}</a><!-- ENDIF --></h3>
		<fieldset class="quick-login">
			<label for="username">{L_USERNAME}:</label>&nbsp;<input type="text" name="username" id="username" size="10" class="inputbox" title="{L_USERNAME}" />  
			<label for="password">{L_PASSWORD}:</label>&nbsp;<input type="password" name="password" id="password" size="10" class="inputbox" title="{L_PASSWORD}" />
			<!-- IF S_AUTOLOGIN_ENABLED -->
				| <label for="autologin">{L_LOG_ME_IN} <input type="checkbox" name="autologin" id="autologin" /></label>
			<!-- ENDIF -->
			<input type="submit" name="login" value="{L_LOGIN}" class="button2" />
		</fieldset>
	</form>
<!-- ENDIF -->
EOF;
		$this->searchAndReplace( $search, '<!-- /* form removed */ -->', $this->phpbb_root_path.'styles/prosilver/template/index_body.html', $sfTask );
		
		// *************		
		
		
		// *************
		// *** disable login and logout actions
		// *************
		
		$search	= <<<EOF
// Basic "global" modes
switch (\$mode)
{
EOF;
		$replace	= <<<EOF
// login and logout are disabled
if( \$mode == 'login' || \$mode == 'logout')
{
	die;
}

// Fixed Basic "global" modes
switch(\$mode)
{
EOF;
		$this->searchAndReplace( $search, $replace, $this->phpbb_root_path.'ucp.php', $sfTask );
		
		// *************
	
		// *************
		// *** synch session with website one
		// *************
		
		$search		= <<<EOF
		// Is session_id is set or session_id is set and matches the url param if required
		if (!empty(\$this->session_id) && (!defined('NEED_SID') || (isset(\$_GET['sid']) && \$this->session_id === \$_GET['sid'])))
		{
			\$sql = 'SELECT u.*, s.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
				WHERE s.session_id = '" . \$db->sql_escape(\$this->session_id) . "'
					AND u.user_id = s.session_user_id";
			\$result = \$db->sql_query(\$sql);
			\$this->data = \$db->sql_fetchrow(\$result);
			\$db->sql_freeresult(\$result);

			// Did the session exist in the DB?
EOF;

		$replace	= <<<EOF
		if( class_exists( 'sfConfig' ) )
		{
			\$projectUserId	= sfConfig::get('projectUserId');
		}
		
		// Is session_id is set or session_id is set and matches the url param if required
		if ( ( !empty(\$this->session_id) || !empty(\$projectUserId) ) && (!defined('NEED_SID') || (isset(\$_GET['sid']) && \$this->session_id === \$_GET['sid'])))
		{
			\$sql = 'SELECT u.*, s.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
				WHERE s.session_id = '" . \$db->sql_escape(\$this->session_id) . "'
					AND u.user_id = s.session_user_id";
			\$result = \$db->sql_query(\$sql);
			\$this->data = \$db->sql_fetchrow(\$result);
			\$db->sql_freeresult(\$result);
			
			if( class_exists( 'sfConfig' ) )
			{
				\$projectUserId	= sfConfig::get('projectUserId');
				\$forumUserId	= is_array( \$this->data ) && array_key_exists('user_id',\$this->data) ? \$this->data['user_id'] : 1;
				if(!empty(\$projectUserId) && ( empty(\$forumUserId) || \$forumUserId == 1 ) )
				{
					prestaForumFactory::getForumConnectorInstance()->signIn( \$projectUserId );
					header('Location: '.\$_SERVER['REQUEST_URI']);die;
				}
				else if(empty(\$projectUserId) && ( !empty(\$forumUserId) && \$forumUserId != 1 ) )
				{
					prestaForumFactory::getForumConnectorInstance()->signOut( prestaForumFactory::getForumConnectorInstance()->getProjectUserIdFromForumUserId( \$forumUserId ) );
					header('Location: '.\$_SERVER['REQUEST_URI']);die;
				}
			}
			
			// Did the session exist in the DB?
EOF;
		$this->searchAndReplace( $search, $replace, $this->phpbb_root_path.'includes/session.php', $sfTask );
		
		// ************* 
	}
	
	/**
	 * Set the general config for the forum
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-30 - ylybliamay
	 * @since	1.0 - 2009-10-30 - ylybliamay
	 */
	protected function patchGeneralConfig( sfBaseTask $sfTask )
	{
		$search		= null;
		$replace	= <<<EOF
<?php
/**
 * Set PHPBB3 config value according to the environment
 * @author	ylybliamay
 * @return	array
 */
function getConfigEnvironment()
{
	\$result['server_name']	= array_key_exists('HTTP_HOST',\$_SERVER)	? \$_SERVER['HTTP_HOST'] : '';
	\$result['script_path']	= array_key_exists('PHP_SELF',\$_SERVER) 	? substr(\$_SERVER['PHP_SELF'],0,strpos(\$_SERVER['PHP_SELF'],'phpBB3') + 6) : '';
	\$result['cookie_domain']= array_key_exists('HTTP_HOST',\$_SERVER) 	? \$_SERVER['HTTP_HOST'] : '';
	return \$result;
}

/**
 * In order to get Symfony sf_user, whe should get the sf_user from the instance.
 * But according to the referer application (symfony or forum), you should create or only get the instance.
 */

@define('SYMFONY_FORUM', true);

require dirname(__FILE__).'/../index.php';

\$instanceCreated	= false;

if( !sfContext::hasInstance() )
{
	\$instanceCreated	= true;
	sfContext::createInstance(\$configuration);
}

\$sf_user	= sfContext::getInstance()->getUser();
\$sf_user_id =  method_exists( \$sf_user, 'getUserId' ) ? \$sf_user->getUserId() : 0;
if(\$sf_user_id > 0)
{
	sfConfig::set('projectUserId', \$sf_user_id );
}
if( \$instanceCreated )
{
	sfContext::getInstance()->shutdown();
}

\$databaseManager	= new sfDatabaseManager( \$configuration );
\$sfPropelDatabase 	= \$databaseManager->getDatabase( sfConfig::get('app_prestaForumConnector_forumDatabaseId' ) );

\$dsn = \$sfPropelDatabase->getParameter('dsn');
\$dsn = explode(':',\$dsn);
// phpBB 3.0.x auto-generated configuration file
// Do not change anything in this file!
\$dbms	= \$dsn[0];
\$dsn	= explode(';',\$dsn[1]);
\$dsn_dbname	= explode('=',\$dsn[0]);
\$dsn_dbhost	= explode('=',\$dsn[1]);

\$dbhost 			= \$dsn_dbhost[1];
\$dbport 			= '';
\$dbname 			= \$dsn_dbname[1];
\$dbuser 			= \$sfPropelDatabase->getParameter('username');
\$dbpasswd 			= \$sfPropelDatabase->getParameter('password');
\$table_prefix 		= '$this->dbprefix';
\$acm_type 			= 'file';
\$load_extensions	= '';

@define('PHPBB_INSTALLED', true);
		
EOF;
		$this->searchAndReplace( $search, $replace, $this->phpbb_root_path.'config.php', $sfTask );
		
		// *************
		// *** acm_file.php
		// *************
		
		$search	= <<<EOF
		if (\$fp = @fopen(\$this->cache_dir . 'data_global.' . \$phpEx, 'wb'))
		{
			@flock(\$fp, LOCK_EX);
EOF;
		$replace	= <<<EOF
		if (\$fp = @fopen(\$this->cache_dir . 'data_global.' . \$phpEx, 'wb'))
		{
			\$this->vars = array_merge(\$this->vars,getConfigEnvironment());
			@flock(\$fp, LOCK_EX);
EOF;
		$this->searchAndReplace( $search, $replace, $this->phpbb_root_path.'includes/acm/acm_file.php', $sfTask );
		
		// *************


		// *************
		// *** cache.php
		// *************
		
		$search		= <<<EOF
		}

		return \$config;
EOF;
		$replace	= <<<EOF
		}
		
		\$config = array_merge(\$config,getConfigEnvironment());

		return \$config;
EOF;
		$this->searchAndReplace( $search, $replace, $this->phpbb_root_path.'includes/cache.php', $sfTask );
		
		// *************
	}

	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function convertToForumNickName($projectNickName, $projectUserId, $forumUserId = 0)
	{
		// Convert the projectNickName if it's an address
		$projectNickName = $this->convertMailAddressToNickName($projectNickName);
		
		$min_length = $this->getConfigVal('min_name_chars');
		$max_length = $this->getConfigVal('max_name_chars');

		while($this->nickNameAlreadyUse($projectNickName, $forumUserId) ||strlen($projectNickName) > $max_length || strlen($projectNickName) < $min_length)
		{
			if(strlen($projectNickName) > $max_length)
			{
				$projectNickName = substr($projectNickName, 0, -3);
			}
			$projectNickName .= rand(0,999);
		}
		return $projectNickName;
	}

	/**
	 * Check if the nickname is already use
	 * @param	$nickname
	 * @return	boolean
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function nickNameAlreadyUse($nickname, $forumUserId = 0)
	{
		$sql	= "SELECT username FROM `". $this->dbprefix ."users`"
				. " WHERE username = '". $nickname ."' AND `user_id` != ". $forumUserId;
		$result = $this->sqlExec($sql);
		$exist 	= mysql_num_rows($result);
		if($exist > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Insert a user session
	 * @param 	$sessionId
	 * @param 	$sessionKey
	 * @param 	$user_id
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function insertDbSession($sessionId, $sessionKey, $user_id)
	{
		$remote_ip = sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote');
		$now = time();

		$sql	= "REPLACE INTO `". $this->dbprefix ."sessions_keys` (`key_id` ,`user_id` ,`last_ip` ,`last_login`)"
				. "VALUES ('$sessionKey', '$user_id', '$remote_ip', '$now');";
		$this->sqlExec($sql);
		
		$browser = '';
		if(is_array($_SERVER) && array_key_exists('HTTP_USER_AGENT', $_SERVER))
		{
			$browser = $_SERVER['HTTP_USER_AGENT'];
		}
			
		$sql	= "REPLACE INTO `". $this->dbprefix ."sessions` (`session_id` ,`session_user_id` ,`session_forum_id` ,"
				. "`session_last_visit` ,`session_start` ,`session_time` ,`session_ip` ,`session_browser` ,"
				. "`session_forwarded_for` ,`session_page` ,`session_viewonline` ,`session_autologin` ,"
				. "`session_admin`) VALUES ("
				. "'$sessionId', '$user_id', '0', '$now', '$now', '$now', '$remote_ip', '$browser', '', '', '1', '1', '0');";
		$this->sqlExec($sql);
	}

	/**
	 * Delete user's session(s) from phpBB's database
	 * @param integer $user_id
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function deleteDbSession($user_id)
	{
		if (intval($user_id) < 1)
		{
			return false;
			# an extra check since DELETE without LIMIT is about to be performed
			#throw new Exception('PhpbbIntegration::deleteDbSession() got invalid user');
		}

		$sql = "DELETE FROM `". $this->dbprefix ."sessions_keys` WHERE `user_id`='$user_id'";
		$this->sqlExec($sql);

		$sql = "DELETE FROM `". $this->dbprefix ."sessions` WHERE `session_user_id`='$user_id'";
		$this->sqlExec($sql);
	}

	/**
	 * Set a field in the users table
	 * @param integer $user_id
	 * @param array of string $field_name => $field_value - without the user_ prefix, for example: email
	 * @param string $field_value- new field value
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	public function updateUserFields($user_id, $new_values)
	{
		if (empty($new_values) || !is_array($new_values))
		{
			throw new Exception('setUser() got invalid $new_values');
		}
			
		$sSqlExtra = '';

		foreach($new_values as $field_name => $field_value)
		{
			$sSqlExtra .= ",`user_$field_name`='$field_value'";
		}

		$sSqlExtra = substr($sSqlExtra, 1);
		$sql = "UPDATE ". $this->dbprefix ."users SET $sSqlExtra WHERE `user_id`='$user_id' LIMIT 1";
		$this->sqlExec($sql);
	}

	/**
	 * Set client cookies to allow auto-login to phpBB
	 * @param	$phpbbCookieName
	 * @param 	$sessionId
	 * @param 	$sessionKey
	 * @param 	$user_id
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function setCookies($phpbbCookieName, $sessionId, $sessionKey, $user_id)
	{
		$domain 	= $this->getConfigVal('cookie_domain');
		$expiry 	= time() + 1209600; # two weeks should be ample
		setcookie($phpbbCookieName.'_k', $sessionKey, $expiry, '/', $domain);
		setcookie($phpbbCookieName.'_u', $user_id, $expiry, '/', $domain);
		setcookie($phpbbCookieName.'_sid', $sessionId, $expiry, '/', $domain);
	}

	/**
	 * Unset client cookies
	 * @param string $phpbbCookieName
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function unsetCookies($phpbbCookieName)
	{
		$domain = self::getConfigVal('cookie_domain');
		$expiry = time() - 86400; # set to expired yesterday
		setcookie($phpbbCookieName.'_k', '', $expiry, '/', $domain);
		setcookie($phpbbCookieName.'_u', 0, $expiry, '/', $domain);
		setcookie($phpbbCookieName.'_sid', '', $expiry, '/', $domain);
	}

	/**
	 * Get a configuration value from phpBB's database
	 * @param	$name - value name
	 * @return 	string
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function getConfigVal($name)
	{
		$sql 	= "SELECT `config_value` FROM `". $this->dbprefix ."config` WHERE `config_name` LIKE '$name'";
		$result = $this->sqlExec($sql);
		$ar 	= $this->db->sql_fetchrow($result);
		return $ar['config_value'];
	}

	/**
	 * Set a configuration value from phpBB's database
	 * @param 	$name
	 * @param 	$value
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-27 - ylybliamay
	 * @since	1.0 - 2009-10-27 - ylybliamay
	 * @return 	boolean
	 */
	protected function setConfigVal($name, $value)
	{
		$sql	= "UPDATE `".$this->dbprefix."config` "
				. "SET`config_value` = '". $value ."' "
				. "WHERE `config_name` = '". $name ."'";
		$this->sqlExec($sql);
	}
	
	/**
	 * Increment a counter for config value
	 * @param $config_name
	 * @param $increment
	 * @param $is_dynamic
	 * @return unknown_type
	 */
	protected function setConfigCount($config_name, $increment, $is_dynamic = false)
	{
		$sql = 'config_value + ' . (int) $increment;
		$this->sqlExec('UPDATE `' . $this->dbprefix . 'config` SET `config_value` = ' . $sql . " WHERE `config_name` = '" . $config_name . "'");
	}

	/**
	 * Execute sql query
	 * @param 	$sql - SQL Query
	 * @returns array
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-26 - ylybliamay
	 * @since	1.0 - 2009-10-26 - ylybliamay
	 */
	protected function sqlExec($sql)
	{
		return $this->db->sql_query($sql);
	}
	
	/**
	 * Clear forum cache
	 * 
	 * @author	Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @version	1.0 - 6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @since	6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @see prestaForumConnectorPlugin/lib/class/forumConnector/prestaAbstractForumConnector#clearCache()
	 * 
	 * @return Boolean
	 */
	public function clearCache()
	{
		foreach( glob( $this->phpbb_root_path.'cache/*.php') as $filepath )
		{
			unlink( $filepath );
		}
		
		return true;
	}
	
	/**
	 * Promote a user as a forum admin
	 * 
	 * @author	Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @version	1.0 - 6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @since	6 nov. 2009 - Christophe Dolivet <cdolivet@prestaconcept.net>
	 * @see prestaForumConnectorPlugin/lib/class/forumConnector/prestaAbstractForumConnector#promoteUserAsAdmin($projectUserId)
	 */
	public function promoteUserAsAdmin( sfBaseTask $sfTask, $projectUserId )
	{
		$succeed		= false;
		
		$password		= $sfTask->ask( "Please enter user's password" );
		
		
		// ensute the user is synched
		$this->synchUser( $projectUserId );
		$forumUserId	= $this->getForumUserIdFromProjectUserId( $projectUserId );
		
		$result = $this->sqlExec( "SELECT group_id FROM `". $this->dbprefix ."groups` WHERE group_name = 'ADMINISTRATORS'" );
		$ar 	= $this->db->sql_fetchrow($result);
		if( is_array($ar) && array_key_exists('group_id', $ar ) )
		{
			$groupId	= $ar['group_id'];
			if( $this->sqlExec( "UPDATE `". $this->dbprefix ."users` SET group_id = '". $groupId ."', user_password='". phpbb_hash( $password ) ."' WHERE user_id = '". $forumUserId ."'") )
			{
				$result = $this->sqlExec( "SELECT * FROM `". $this->dbprefix ."user_group` WHERE group_id = '". $groupId ."' AND user_id = '". $forumUserId ."'" );
				$ar 	= $this->db->sql_fetchrow($result);
				if( !empty( $ar ) )
				{
					$succeed	= true;
				}
				elseif( $this->sqlExec( "INSERT IGNORE INTO `". $this->dbprefix ."user_group` (group_id, user_id, group_leader, user_pending) VALUES ('". $groupId ."','". $forumUserId ."',0,0)") )
				{
					$succeed	= true;
				}
			}
		}
		
		// clear forum cache
		$this->clearCache();
		
		return $succeed;
	}
	

	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0	- 2009-10-29 - ylybliamay
	 */
	public function forumUserIsEnabled($forumUserId)
	{
		$sql 	= 'SELECT user_id FROM '.$this->dbprefix.'users WHERE user_type = 0 AND user_inactive_reason = 0 AND user_id = '.$forumUserId;
		$result	= $this->sqlExec($sql);
		$exist 	= mysql_num_rows($result);
		if($exist)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * @author	ylybliamay
	 * @version	1.0 - 2009-10-29 - ylybliamay
	 * @since	1.0	- 2009-10-29 - ylybliamay
	 */
	public function isSignedIn($forumUserId)
	{
		$sql 	= 'SELECT user_id FROM '.$this->dbprefix.'sessions_keys WHERE user_id = '.$forumUserId;
		$result	= $this->sqlExec($sql);
		$existSessionsKeys 	= mysql_num_rows($result);
		
		$sql 	= 'SELECT session_user_id FROM '.$this->dbprefix.'sessions WHERE session_user_id = '.$forumUserId;
		$result	= $this->sqlExec($sql);
		$existSessions 	= mysql_num_rows($result);
		
		if($existSessionsKeys && $existSessions)
		{
			return true;
		}
		return false;
	}
}