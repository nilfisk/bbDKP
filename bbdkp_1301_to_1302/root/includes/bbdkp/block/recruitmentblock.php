<?php
/**
 * Recruitment block
 *
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @author Sajaki, Blazeflack, Malfate
 *
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

/**  begin recruitment block ***/


$template->assign_block_vars('status', array(
		'MESSAGE' => $user->lang['RECRUIT_MESSAGE']));

$rec_forum_id = $config['bbdkp_recruit_forumid'];

// Include the abstract base
if (!class_exists('\bbdkp\controller\guilds\Roles'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Roles.$phpEx");
}

$color = array(
		array(0 , $user->lang['CLOSED'] , "#AAAAAA" , "rec_closed.png") ,
		array(1 , $user->lang['LOW'] , "#FFBB44" , "rec_low.png") ,
		array(2 , $user->lang['MEDIUM'] ,"#FF3300" ,"rec_med.png") ,
		array(3 , $user->lang['HIGH'] ,"#AA00AA" ,"rec_high.png")
);
$roles = new \bbdkp\controller\guilds\Roles;

$guildrecruitingresult = $roles->get_recruiting_guilds();
while ($row = $db->sql_fetchrow($guildrecruitingresult))
{

	$guild_id = $row['id'];

	$template->assign_block_vars('guild', array(
			'EMBLEM' 	=> $row['emblemurl'],
			'NAME' 		=> $row['name'],
			'S_CLOSED'  => $row['rec_status'] ==0 ? true : false,
	));


	$blockresult = $roles->recruitblock($guild_id);
	while ($row = $db->sql_fetchrow($blockresult))
	{
		$class[$row['class_id']] = $row['class_name'];

		$template->assign_block_vars('guild.rec', array(
				'CLASS_IMAGE' =>  (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '' ,
				'CLASSID' => $row['class_id'] ,
				'CLASS' => $row['class_name'] ,
				'IMAGENAME' => $row['imagename'] ,
				'CLASSCOLOR' => $row['colorcode'] ,

				'TANKFORUM' => append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $rec_forum_id) ,
				'TANKTEXT' => $color[$row['tank'] > 3 ? 3 : $row['tank']][1] ,
				'TANKCOLOR' => $color[$row['tank'] > 3 ? 3 : $row['tank']][2] ,
				'TANK' => $color[$row['tank'] > 3 ? 3 : $row['tank']][3] ,
				'TANKNEEDED' => $row['tank'],
				'S_TANK' => ((int) $row['tank'] == 0) ? false: true,

				'DPSFORUM' => append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $rec_forum_id) ,
				'DPSTEXT' => $color[$row['dps'] > 3 ? 3 : $row['dps']][1] ,
				'DPSCOLOR' => $color[$row['dps'] > 3 ? 3 : $row['dps']][2] ,
				'DPS' => $color[$row['dps'] > 3 ? 3 : $row['dps']][3] ,
				'DPSNEEDED' => $row['dps'],
				'S_DPS' => ((int) $row['dps'] == 0) ? false: true,

				'HEALFORUM' => append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $rec_forum_id) ,
				'HEALTEXT' => $color[$row['heal'] > 3 ? 3 : $row['heal']][1] ,
				'HEALCOLOR' => $color[$row['heal'] > 3 ? 3 : $row['heal']][2] ,
				'HEAL' => $color[ $row['heal'] > 3 ? 3 : $row['heal'] ][3],
				'HEALNEEDED' => $row['heal'],
				'S_HEAL' => ((int)  $row['heal'] == 0) ? false: true,

		));
	}
	$db->sql_freeresult($blockresult);

}
$db->sql_freeresult($guildrecruitingresult);

$template->assign_vars(array(
		'S_DISPLAY_RECRUIT' => true,
		));
