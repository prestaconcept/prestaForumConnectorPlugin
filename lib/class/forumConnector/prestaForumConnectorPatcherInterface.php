<?php
/**
 * Patcher interface for forum connector
 * 
 * @author 	cdolivet
 */
interface prestaForumConnectorPatcherInterface
{
	/**
	 * Patch the forum database, configuration and file
	 * @return	boolean
	 */
	public function patchForum( sfBaseTask $sfTask );
}