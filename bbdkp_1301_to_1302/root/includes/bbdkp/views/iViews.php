<?php
/**
 * Created by PhpStorm.
 * User: Nix
 * Date: 16/03/14
 * Time: 20:04
 */
namespace bbdkp\views;


/**
 * @ignore
 */
if ( !defined('IN_PHPBB') OR !defined('IN_BBDKP') )
{
	exit;
}

interface iViews {

    public function buildpage(viewNavigation $Navigation);

}