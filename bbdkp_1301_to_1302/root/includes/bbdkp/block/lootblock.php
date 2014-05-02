<?php
/**
 * loot block
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0

 *
 */

if (!defined('IN_PHPBB'))
{
   exit;
}

$n_items = $config['bbdkp_n_items'];

/**  begin loot block ***/
$sql = "SELECT item_name, item_gameid, wowhead_id FROM " . RAID_ITEMS_TABLE . ' ORDER BY item_date DESC ';
$result = $db->sql_query_limit($sql, $n_items, 0);
while ($item = $db->sql_fetchrow($result))
{
	if ($this->bbtips == true && $item['item_gameid'] == 'wow')
	{
		$item_name = '<strong>' . $this->bbtips->parse('[itemdkp]' . $item['item_name']  . '[/itemdkp]') . '</strong>';
	}
	else
	{
		$item_name = '<strong>' .  $item ['item_name'] . '</strong>';
	}

	$template->assign_block_vars('itemit', array(
	    'ITEMI1' => $item_name,
	));
}

$db->sql_freeresult($result);
$template->assign_vars(array(
	'S_DISPLAY_LOOT' 	=> true,
));

