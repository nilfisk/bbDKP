<?php
/**
 * members acp file
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */


// don't add this file to namespace bbdkp
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
    exit();
}
if (! defined('EMED_BBDKP'))
{
    $user->add_lang(array(
        'mods/dkp_admin'));
    trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}

// Include the base class
if (!class_exists('\bbdkp\admin\Admin'))
{
    require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

// include ranks class
if (!class_exists('\bbdkp\controller\guilds\Ranks'))
{
    require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Ranks.$phpEx");
}

// Include the member class
if (!class_exists('\bbdkp\controller\members\Members'))
{
    require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
//include the roles class
if (!class_exists('\bbdkp\controller\guilds\Roles'))
{
    require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Roles.$phpEx");
}
//include the guilds class
if (!class_exists('\bbdkp\controller\guilds\Guilds'))
{
    require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
}

/**
 * This class manages member general info
 *
 *   @package bbdkp
 */
class acp_dkp_mm extends \bbdkp\admin\Admin
{

    /**
     * trigger link
     * @var string
     */
    public $link = ' ';

    /**
     * main acp_dkp_mm function
     * @param integer $id
     * @param string $mode
     */
    public function main ($id, $mode)
    {
        global $user, $template, $db, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

        switch ($mode)
        {
            /**
             * List members
             */
            case 'mm_listmembers':

                $this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx","i=dkp_mm&amp;mode=mm_listmembers") . '"><h3>Return to Index</h3></a>';
                $Guild = new \bbdkp\controller\guilds\Guilds();

                $guildlist = $Guild->guildlist(1);
                if( count((array) $guildlist) == 0  )
                {
                    trigger_error('ERROR_NOGUILD', E_USER_WARNING );
                }

                if( count((array) $guildlist) == 1 )
                {
                    $Guild->guildid = $guildlist[0]['id'];
                    $Guild->name = $guildlist[0]['name'];
                    if ($Guild->guildid == 0 && $Guild->name == 'Guildless' )
                    {
                        trigger_error('ERROR_NOGUILD', E_USER_WARNING );
                    }
                }

                foreach ($guildlist as $g)
                {
                    $Guild->guildid = $g['id'];
                    break;
                }

                $activate = (isset($_POST['deactivate'])) ? true : false;
                if ($activate)
                {
                    $this->ActivateList();
                }

                // batch delete
                $del_batch = (isset($_POST['delete'])) ? true : false;
                if ($del_batch)
                {
                    $this->member_batch_delete();
                }

                // guild dropdown query
                $getguild_dropdown = isset ( $_POST ['member_guild_id'] )  ? true : false;
                if ($getguild_dropdown)
                {
                    // user selected dropdow - get guildid
                    $Guild->guildid = request_var('member_guild_id', 0);
                }

                $sortlink = isset ( $_GET [URI_GUILD] )  ? true : false;
                if($sortlink)
                {
                    // user selected dropdow - get guildid
                    $Guild->guildid = request_var(URI_GUILD, 0);
                }


                $charapicall = (isset($_POST['charapicall'])) ? true : false;
                if($charapicall)
                {
                    if (confirm_box(true))
                    {
                        list($i, $log) = $this->CallCharacterAPI();
                        trigger_error(sprintf($user->lang['CHARAPIDONE'], $i, $log), E_USER_NOTICE);
                    }
                    else
                    {
                        $s_hidden_fields = build_hidden_fields(array(
                            'charapicall' => true ,
                            'hidden_guildid' => request_var('member_guild_id', 0),
                            'hidden_minlevel' => request_var('hidden_minlevel', request_var('minlevel', 0)),
                            'hidden_maxlevel' => request_var('maxlevel', request_var('hidden_maxlevel', 200)),
                            'hidden_active' => request_var('active', request_var('hidden_active', 0)),
                            'hidden_nonactive' => request_var('nonactive', request_var('hidden_nonactive', 0)),
                            'hidden_member_name' => utf8_normalize_nfc(request_var('member_name', request_var('hidden_member_name', '', true), true)),
                        ));
                        confirm_box(false, $user->lang['WARNING_BATTLENET'], $s_hidden_fields);

                    }
                }

                // add member button redirect
                $showadd = (isset($_POST['memberadd'])) ? true : false;
                if ($showadd)
                {
                    $a = request_var('member_guild_id', request_var('hidden_guildid', 0));
                    redirect(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_addmember&amp;guild_id=" . $a ));
                    break;
                }

                // pageloading

                // fill popup and set selected to default selection
                $Guild->Getguild();
                $guildlist = $Guild->guildlist(0);
                foreach ($guildlist as $g)
                {
                    $template->assign_block_vars('guild_row', array(
                        'VALUE' => $g['id'] ,
                        'SELECTED' => ($g['id'] == $Guild->guildid) ? ' selected="selected"' : '' ,
                        'OPTION' => (! empty($g['name'])) ? $g['name'] : '(None)'));
                }

                $previous_data = '';

                //get window
                $start = request_var('start', 0, false);
                $minlevel = request_var('minlevel', 0);
                $maxlevel = request_var('maxlevel', 200);

                if( isset($_POST['search'])  || isset($_GET['active']) || isset($_GET['nonactive'])  )
                {
                    $selectactive = request_var('active', 0);
                    $selectnonactive = request_var('nonactive', 0);
                }
                else
                {
                    // set standard
                    $selectactive = 1;
                    $selectnonactive = 1;

                }

                $member_filter = utf8_normalize_nfc(request_var('member_name', '', true)) ;

                $sort_order = array(
                    0 => array('member_name', 'member_name desc') ,
                    1 => array('username', 'username desc') ,
                    2 => array('member_level', 'member_level desc') ,
                    3 => array('member_class', 'member_class desc') ,
                    4 => array('rank_name', 'rank_name desc'),
                    5 => array('last_update', 'last_update desc'),
                    7 => array('member_id', 'member_id desc')
                );

                $current_order = $this->switch_order($sort_order);
                $sort_index = explode('.', $current_order['uri']['current']);
                $previous_source = preg_replace('/( (asc|desc))?/i', '', $sort_order[$sort_index[0]][$sort_index[1]]);
                $show_all = ((isset($_GET['show'])) && request_var('show', '') == 'all') ? true : false;

                $result = $Guild->listmembers($current_order['sql'], 0, 0, $minlevel, $maxlevel, $selectactive, $selectnonactive, $member_filter);
                $member_count = 0;
                while ($row = $db->sql_fetchrow($result))
                {
                    $member_count += 1;
                }

                if (! ($result))
                {
                    trigger_error($user->lang['ERROR_MEMBERNOTFOUND'], E_USER_WARNING);
                }
                $db->sql_freeresult($result);

                $members_result = $Guild->listmembers($current_order['sql'], $start, 1, $minlevel, $maxlevel, $selectactive, $selectnonactive, $member_filter);
                $lines = 0;
                while ($row = $db->sql_fetchrow($members_result))
                {
                    $phpbb_user_id = (int) $row['phpbb_user_id'];
                    $race_image = (string) (($row['member_gender_id'] == 0) ? $row['image_male'] : $row['image_female']);
                    $lines +=1;
                    $template->assign_block_vars('members_row', array(
                        'S_READONLY' => ($row['rank_id'] == 90 || $row['rank_id'] == 99) ? true : false ,
                        'STATUS' => ($row['member_status'] == 1) ? 'checked="checked" ' : '' ,
                        'ID' => $row['member_id'],
                        'COUNT' => $member_count,
                        'NAME' => $row['rank_prefix'] . $row['member_name'] . $row['rank_suffix'] ,
                        'USERNAME' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) ,
                        'RANK' => $row['rank_name'] ,
                        'LEVEL' => ($row['member_level'] > 0) ? $row['member_level'] : '&nbsp;' ,
                        'ARMOR' => (! empty($row['armor_type'])) ? $row['armor_type'] : '&nbsp;' ,
                        'COLORCODE' => ($row['colorcode'] == '') ? '#254689' : $row['colorcode'] ,
                        'CLASS_IMAGE' => (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '' ,
                        'S_CLASS_IMAGE_EXISTS' => (strlen($row['imagename']) > 1) ? true : false ,
                        'RACE_IMAGE' => (strlen($race_image) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race_image . ".png" : '' ,
                        'S_RACE_IMAGE_EXISTS' => (strlen($race_image) > 1) ? true : false ,
                        'CLASS' => ($row['member_class'] != 'NULL') ? $row['member_class'] : '&nbsp;' ,
                        'LAST_UPDATE' => ($row['last_update'] == 0) ? '' : date($config['bbdkp_date_format'] . ' H:i:s' , $row['last_update']) ,
                        'U_VIEW_USER' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;icat=13&amp;mode=overview&amp;u=$phpbb_user_id") ,
                        'U_VIEW_MEMBER' => append_sid("{$phpbb_admin_path}index.$phpEx", 'i=dkp_mm&amp;mode=mm_addmember&amp;' . URI_NAMEID . '=' . $row['member_id']) ,
                        'U_DELETE_MEMBER' => append_sid("{$phpbb_admin_path}index.$phpEx", 'i=dkp_mm&amp;mode=mm_addmember&amp;delete=1&amp;' . URI_NAMEID . '=' . $row['member_id'])));
                    $previous_data = $row[$previous_source];
                }

                $db->sql_freeresult($members_result);
                $footcount_text = sprintf($user->lang['LISTMEMBERS_FOOTCOUNT'], $member_count);
                $memberpagination = generate_pagination(append_sid("{$phpbb_admin_path}index.$phpEx",
                        "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri']['current'] .
                        "&amp;". URI_GUILD ."=".$Guild->guildid .
                        "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive ),
                    $member_count, $config['bbdkp_user_llimit'], $start, true);
                $form_key = 'mm_listmembers';
                add_form_key($form_key);

                $template->assign_vars(array(
                    'F_SELECTACTIVE'  => $selectactive,
                    'F_SELECTNONACTIVE'  => $selectnonactive,
                    'GUILDID' => $Guild->guildid,
                    'GUILDNAME' => $Guild->name,
                    'MINLEVEL' => $minlevel,
                    'MAXLEVEL' => $maxlevel,
                    'START' => $start,
                    'MEMBER_NAME' => $member_filter,
                    'F_MEMBERS' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm") . '&amp;mode=mm_addmember' ,
                    'F_MEMBERS_LIST' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm") . '&amp;mode=mm_listmembers' ,
                    'L_TITLE' => $user->lang['ACP_MM_LISTMEMBERS'] ,
                    'L_EXPLAIN' => $user->lang['ACP_MM_LISTMEMBERS_EXPLAIN'] ,
                    'O_NAME' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][0] . "&amp;" . URI_GUILD . "=" . $Guild->guildid . "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_USERNAME' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][1] . "&amp;" . URI_GUILD . "=" . $Guild->guildid. "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_LEVEL' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][2] . "&amp;" . URI_GUILD . "=" . $Guild->guildid. "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_CLASS' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][3] . "&amp;" . URI_GUILD . "=" . $Guild->guildid . "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_RANK' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][4] . "&amp;" . URI_GUILD . "=" . $Guild->guildid . "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_LAST_UPDATE' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][5] . "&amp;" . URI_GUILD . "=" . $Guild->guildid . "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive) ,
                    'O_ID' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;o=" . $current_order['uri'][7] . "&amp;" . URI_GUILD . "=" . $Guild->guildid . "&amp;minlevel=".$minlevel .
                        "&amp;maxlevel=".$maxlevel .
                        "&amp;active=".$selectactive .
                        "&amp;nonactive=".$selectnonactive),
                    'U_LIST_MEMBERS' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;") ,
                    'LISTMEMBERS_FOOTCOUNT' => $footcount_text ,
                    'U_VIEW_GUILD' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_guild&amp;mode=editguild&amp;" . URI_GUILD . '=' . $Guild->guildid ),
                    'S_WOW'  => ($Guild->game_id  == 'wow') ? true: false,
                    'MEMBER_PAGINATION' => $memberpagination,
                    'GUILD_EMBLEM'      => $Guild->emblempath,
                    'GUILD_NAME'        => $Guild->name,
                ));
                $this->page_title = 'ACP_MM_LISTMEMBERS';
                $this->tpl_name = 'dkp/acp_' . $mode;
                break;

            /***************************************/
            // add member
            /***************************************/
            case 'mm_addmember' :

                $this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers") . '"><h3>' . $user->lang['RETURN_MEMBERLIST'] . '</h3></a>';

                $add = (isset($_POST['add'])) ? true : false;
                $update = (isset($_POST['update'])) ? true : false;
                $delete = (isset($_GET['delete']) || isset($_POST['delete'])) ? true : false;

                if ($add || $update)
                {
                    if (! check_form_key('mm_addmember'))
                    {
                        trigger_error('FORM_INVALID');
                    }
                }

                // add guild member
                if ($add)
                {
                    $this->Addmember();

                }

                //
                // update guild member
                //
                if ($update)
                {
                    $this->UpdateMember();
                }


                //
                // delete guild member
                //
                if ($delete)
                {

                    if (confirm_box(true))
                    {
                        $deletemember = $this->DeleteMember();
                    }
                    else
                    {
                        $deletemember = new \bbdkp\controller\members\Members();
                        $deletemember->member_id = request_var('member_id', 0);
                        $deletemember->Getmember();
                        $s_hidden_fields = build_hidden_fields(array(
                            'delete' => true ,
                            'del_member_id' => $deletemember->member_id));

                        confirm_box(false, sprintf($user->lang['CONFIRM_DELETE_MEMBER'], $deletemember->member_name), $s_hidden_fields);
                    }
                    unset($deletemember);
                }

                /*
                 * fill template
                 */
                $member_id = request_var('hidden_member_id', request_var(URI_NAMEID, 0));
                $editmember = new \bbdkp\controller\members\Members($member_id);

                $S_ADD = ($member_id > 0) ? false: true;
                if ($S_ADD)
                {
                    // set defaults
                    $editmember->member_guild_id = request_var(URI_GUILD, 0);

                }

                $Guild = new \bbdkp\controller\guilds\Guilds($editmember->member_guild_id);
                $guildlist = $Guild->guildlist();

                if ($S_ADD)
                {
                    $editmember->game_id = $Guild->game_id;
                    $editmember->member_rank_id = $Guild->raidtrackerrank;
                    $editmember->member_status = 1;
                    $editmember->member_gender_id = 0;
                }

                foreach ($guildlist as $g)
                {
                    //populate guild popup
                    $template->assign_block_vars('guild_row', array(
                        'VALUE' => $g['id'] ,
                        'SELECTED' => ($g['id'] == $editmember->member_guild_id ) ? ' selected="selected"' : '' ,
                        'OPTION' => (! empty($g['name'])) ? $g['name'] : '(None)'));

                }

                // Game dropdown
                if(isset($this->games))
                {
                    foreach ($this->games as $gameid => $gamename)
                    {
                        $template->assign_block_vars('game_row', array(
                            'VALUE' => $gameid ,
                            'SELECTED' => ($editmember->game_id == $gameid) ? ' selected="selected"' : '' ,
                            'OPTION' => $gamename));
                    }

                }
                else
                {
                    trigger_error('ERROR_NOGAMES', E_USER_WARNING );
                }

                foreach ($this->regions as $key => $regionname)
                {
                    $template->assign_block_vars('region_row', array(
                        'VALUE' => $key ,
                        'SELECTED' => ($editmember->member_region == $key) ? ' selected="selected"' : '' ,
                        'OPTION' => (! empty($regionname)) ? $regionname : '(None)'));
                }

                // Rank drop-down -> for initial load
                // reloading is done from ajax to prevent redraw
                $Ranks = new \bbdkp\controller\guilds\Ranks($editmember->member_guild_id);
                $result = $Ranks->listranks();
                while ($row = $db->sql_fetchrow($result))
                {
                    $template->assign_block_vars('rank_row', array(
                        'VALUE' => $row['rank_id'] ,
                        'SELECTED' => ($editmember->member_rank_id == $row['rank_id']) ? ' selected="selected"' : '' ,
                        'OPTION' => (! empty($row['rank_name'])) ? $row['rank_name'] : '(None)'));
                }

                // Race dropdown
                // reloading is done from ajax to prevent redraw
                $sql_array = array(
                    'SELECT' => '  r.race_id, l.name as race_name ' ,
                    'FROM' => array(
                        RACE_TABLE => 'r' ,
                        BB_LANGUAGE => 'l') ,
                    'WHERE' => " r.race_id = l.attribute_id
								AND r.game_id = '" . $editmember->game_id . "'
								AND l.attribute='race'
								AND l.game_id = r.game_id
								AND l.language= '" . $config['bbdkp_lang'] . "'",
                    'ORDER_BY'	=> 'l.name asc');
                $sql = $db->sql_build_query('SELECT', $sql_array);
                $result = $db->sql_query($sql);
                if ($editmember->member_id > 0)
                {
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $template->assign_block_vars('race_row', array(
                            'VALUE' => $row['race_id'] ,
                            'SELECTED' => ($editmember->member_race_id == $row['race_id']) ? ' selected="selected"' : '' ,
                            'OPTION' => (! empty($row['race_name'])) ? $row['race_name'] : '(None)'));
                    }
                }
                else
                {
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $template->assign_block_vars('race_row', array(
                            'VALUE' => $row['race_id'] ,
                            'SELECTED' => '' ,
                            'OPTION' => (! empty($row['race_name'])) ? $row['race_name'] : '(None)'));
                    }
                }
                $db->sql_freeresult($result);


                //
                // Class dropdown
                // reloading is done from ajax to prevent redraw
                $sql_array = array(
                    'SELECT' => ' c.class_id, l.name as class_name, c.class_hide,
									  c.class_min_level, class_max_level, c.class_armor_type , c.imagename ' ,
                    'FROM' => array(
                        CLASS_TABLE => 'c' ,
                        BB_LANGUAGE => 'l') ,
                    'WHERE' => " l.game_id = c.game_id  AND c.game_id = '" . $editmember->game_id . "'
					AND l.attribute_id = c.class_id  AND l.language= '" . $config['bbdkp_lang'] . "' AND l.attribute = 'class' ",
                    'ORDER_BY'	=> 'l.name asc'
                );
                $sql = $db->sql_build_query('SELECT', $sql_array);
                $result = $db->sql_query($sql);

                while ($row = $db->sql_fetchrow($result))
                {
                    if ($row['class_min_level'] <= 1)
                    {
                        $option = (! empty($row['class_name'])) ? $row['class_name'] . "
						 Level (" . $row['class_min_level'] . " - " . $row['class_max_level'] . ")" : '(None)';
                    }
                    else
                    {
                        $option = (! empty($row['class_name'])) ? $row['class_name'] . "
						 Level " . $row['class_min_level'] . "+" : '(None)';
                    }
                    if ($editmember->member_id <> 0)
                    {
                        $template->assign_block_vars('class_row', array(
                            'VALUE' => $row['class_id'] ,
                            'SELECTED' => ($editmember->member_class_id == $row['class_id']) ? ' selected="selected"' : '' ,
                            'OPTION' => $option));
                    }
                    else
                    {
                        $template->assign_block_vars('class_row', array(
                            'VALUE' => $row['class_id'] ,
                            'SELECTED' => '' ,
                            'OPTION' => $option));
                    }
                }
                $db->sql_freeresult($result);

                //Role dropdown
                $Roles = new \bbdkp\controller\guilds\Roles();
                $Roles->guild_id = $editmember->member_guild_id;
                foreach($Roles->roles as $roleid => $Role )
                {
                    $template->assign_block_vars('role_row', array(
                        'VALUE' => $roleid ,
                        'SELECTED' => ($editmember->member_role == $roleid) ? ' selected="selected"' : '' ,
                        'OPTION' => $Role ));
                }


                // build presets for joindate pulldowns
                $now = getdate();
                $s_memberjoin_day_options = '<option value="0"	>--</option>';
                for ($i = 1; $i < 32; $i ++)
                {
                    $day = $editmember->member_id > 0 ? $editmember->member_joindate_d : $now['mday'];
                    $selected = ($i == $day) ? ' selected="selected"' : '';
                    $s_memberjoin_day_options .= "<option value=\"$i\"$selected>$i</option>";
                }
                $s_memberjoin_month_options = '<option value="0">--</option>';
                for ($i = 1; $i < 13; $i ++)
                {
                    $month = $editmember->member_id > 0 ? $editmember->member_joindate_mo : $now['mon'];
                    $selected = ($i == $month) ? ' selected="selected"' : '';
                    $s_memberjoin_month_options .= "<option value=\"$i\"$selected>$i</option>";
                }
                $s_memberjoin_year_options = '<option value="0">--</option>';
                for ($i = $now['year'] - 10; $i <= $now['year']; $i ++)
                {
                    $yr = $editmember->member_id > 0 ? $editmember->member_joindate_y : $now['year'];
                    $selected = ($i == $yr) ? ' selected="selected"' : '';
                    $s_memberjoin_year_options .= "<option value=\"$i\"$selected>$i</option>";
                }


                // build presets for outdate pulldowns
                $s_memberout_day_options = '<option value="0"' . ($editmember->member_id > 0 ? (($editmember->member_outdate != 0) ? '' : ' selected="selected"') : ' selected="selected"') . '>--</option>';
                for ($i = 1; $i < 32; $i ++)
                {
                    if ($editmember->member_id > 0 && $editmember->member_outdate != 0)
                    {
                        $day = $editmember->member_outdate_d;
                        $selected = ($i == $day) ? ' selected="selected"' : '';
                    }
                    else
                    {
                        $selected = '';
                    }
                    $s_memberout_day_options .= "<option value=\"$i\"$selected>$i</option>";
                }


                $s_memberout_month_options = '<option value="0"' . ($editmember->member_id > 0 ? (($editmember->member_outdate != 0) ? '' : ' selected="selected"') : ' selected="selected"') . '>--</option>';

                for ($i = 1; $i < 13; $i ++)
                {
                    if ($editmember->member_id > 0 && $editmember->member_outdate != 0)
                    {
                        $month = $editmember->member_outdate_mo;
                        $selected = ($i == $month) ? ' selected="selected"' : '';
                    }
                    else
                    {
                        $selected = '';
                    }
                    $s_memberout_month_options .= "<option value=\"$i\"$selected>$i</option>";
                }

                $s_memberout_year_options = '<option value="0"' . ($editmember->member_id > 0 ? (($editmember->member_outdate != 0) ? '' : ' selected="selected"') : ' selected="selected"') . '>--</option>';

                for ($i = $now['year'] - 10; $i <= $now['year'] + 10; $i ++)
                {
                    if ($editmember->member_id > 0 && $editmember->member_outdate != 0)
                    {
                        $yr = $editmember->member_outdate_y;
                        $selected = ($i == $yr) ? ' selected="selected"' : '';
                    }
                    else
                    {
                        $selected = '';
                    }
                    $s_memberout_year_options .= "<option value=\"$i\"$selected>$i</option>";
                }

                // phpbb User dropdown
                $phpbb_user_id = $editmember->member_id > 0  ? $editmember->phpbb_user_id : 0;
                $sql_array = array(
                    'SELECT' => ' u.user_id, u.username ' ,
                    'FROM' => array(
                        USERS_TABLE => 'u') ,
                    // exclude bots and guests, order by name -- ticket  129
                    'WHERE' => " u.group_id != 6 and u.group_id != 1 " ,
                    'ORDER_BY' => " u.username ASC");
                $sql = $db->sql_build_query('SELECT', $sql_array);
                $result = $db->sql_query($sql);
                $s_phpbb_user = '<option value="0"' . (($phpbb_user_id == 0) ? ' selected="selected"' : '') . '>--</option>';
                while ($row = $db->sql_fetchrow($result))
                {
                    $selected = ($row['user_id'] == $phpbb_user_id) ? ' selected="selected"' : '';
                    $s_phpbb_user .= '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['username'] . '</option>';
                }

                unset($now);



                $form_key = 'mm_addmember';
                add_form_key($form_key);
                $template->assign_vars(array(
                    'L_TITLE' => $user->lang['ACP_MM_ADDMEMBER'] ,
                    'L_EXPLAIN' => $user->lang['ACP_MM_ADDMEMBER_EXPLAIN'] ,
                    'F_ADD_MEMBER' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_addmember&amp;") ,
                    'STATUS' => $editmember->member_status == 1 ? 'checked="checked"' : '' ,
                    'MEMBER_NAME' => $editmember->member_name,
                    'MEMBER_ID' => $editmember->member_id,
                    'MEMBER_LEVEL' => $editmember->member_level,
                    'REALM' =>  $editmember->member_realm,
                    'DEACTIVATE_REASON' => $editmember->deactivate_reason == '' ? '' : $user->lang[$editmember->deactivate_reason],
                    'STATUS_LOCK' => $editmember->deactivate_reason == '' ? false : true,
                    'MEMBER_ACHIEV' => $editmember->member_achiev,
                    'MEMBER_TITLE' => $editmember->member_title,
                    'MALE_CHECKED' => ( $editmember->member_gender_id == '0') ? ' checked="checked"' : '' ,
                    'FEMALE_CHECKED' => ( $editmember->member_gender_id == '1') ? ' checked="checked"' : '' ,
                    'MEMBER_COMMENT' => $editmember->member_comment,
                    'S_CAN_HAVE_ARMORY' => $editmember->game_id == 'wow' || $editmember->game_id == 'aion' ? true : false,
                    'MEMBER_URL' => $editmember->member_armory_url,
                    'MEMBER_PORTRAIT' => $editmember->member_portrait_url,
                    'S_MEMBER_PORTRAIT_EXISTS' => (strlen($editmember->member_portrait_url) > 1) ? true : false ,
                    'S_CAN_GENERATE_ARMORY' => $editmember->game_id == 'wow' ? true : false,
                    'COLORCODE' => ($editmember->colorcode== '') ? '#254689' : $editmember->colorcode ,
                    'CLASS_IMAGE' => $editmember->class_image ,
                    'S_CLASS_IMAGE_EXISTS' => (strlen($editmember->class_image) > 1) ? true : false ,
                    'RACE_IMAGE' => $editmember->race_image ,
                    'S_RACE_IMAGE_EXISTS' => (strlen($editmember->race_image) > 1) ? true : false ,
                    'S_JOINDATE_DAY_OPTIONS' => $s_memberjoin_day_options ,
                    'S_JOINDATE_MONTH_OPTIONS' => $s_memberjoin_month_options ,
                    'S_JOINDATE_YEAR_OPTIONS' => $s_memberjoin_year_options ,
                    'S_OUTDATE_DAY_OPTIONS' => $s_memberout_day_options ,
                    'S_OUTDATE_MONTH_OPTIONS' => $s_memberout_month_options ,
                    'S_OUTDATE_YEAR_OPTIONS' => $s_memberout_year_options ,
                    'S_PHPBBUSER_OPTIONS' => $s_phpbb_user ,
                    'TITLE_NAME' => ($editmember->game_id == 'wow') ? sprintf($editmember->member_title, $editmember->member_name) : '' ,
                    // javascript
                    'LA_ALERT_AJAX' => $user->lang['ALERT_AJAX'] ,
                    'LA_ALERT_OLDBROWSER' => $user->lang['ALERT_OLDBROWSER'] ,
                    'LA_MSG_NAME_EMPTY' => $user->lang['FV_REQUIRED_NAME'] ,
                    'UA_FINDRANK' => append_sid($phpbb_admin_path . "style/dkp/findrank.$phpEx") ,
                    'UA_FINDCLASSRACE' => append_sid($phpbb_admin_path . "style/dkp/findclassrace.$phpEx") ,
                    'S_ADD' => $S_ADD));
                $this->page_title = 'ACP_MM_ADDMEMBER';
                $this->tpl_name = 'dkp/acp_' . $mode;
                break;



            default:
                $this->page_title = 'ACP_DKP_MAINPAGE';
                $this->tpl_name = 'dkp/acp_mainpage';
                $success_message = 'Error';
                trigger_error($success_message . $this->link, E_USER_WARNING);
        }
    }

    /**
     * function to batch delete members, called from listing
     *
     */
    private function member_batch_delete ()
    {
        global $db, $user;

        $members_to_delete = request_var('delete_id', array(0));

        if (! is_array($members_to_delete))
        {
            return;
        }

        if (sizeof($members_to_delete) == 0)
        {
            return;
        }

        if (confirm_box(true))
        {
            // recall hidden vars
            $members_to_delete = request_var('delete_id', array(0 => 0));
            $member_names = utf8_normalize_nfc(request_var('members', array(0 => ''), true));
            foreach ($members_to_delete as $memberid => $value)
            {
                $delmember = new \bbdkp\controller\members\Members();
                $delmember->member_id = $memberid;
                $delmember->Getmember();
                $delmember->Deletemember();
                unset($delmember);
            }
            $str_members = implode($member_names, ',');
            $success_message = sprintf($user->lang['ADMIN_DELETE_MEMBERS_SUCCESS'], $str_members);
            trigger_error($success_message . $this->link, E_USER_NOTICE);
        }
        else
        {
            $sql = "SELECT member_name, member_id FROM " . MEMBER_LIST_TABLE . " WHERE " . $db->sql_in_set('member_id', array_keys($members_to_delete));
            $result = $db->sql_query($sql);
            while ($row = $db->sql_fetchrow($result))
            {
                $member_names[] = $row['member_name'];
            }
            $db->sql_freeresult($result);
            $s_hidden_fields = build_hidden_fields(array(
                'delete' => true ,
                'delete_id' => $members_to_delete ,
                'members' => $member_names));
            $str_members = implode($member_names, ', ');

            confirm_box(false, sprintf($user->lang['CONFIRM_DELETE_MEMBER'], $str_members), $s_hidden_fields);
        }
    }

    /**
     * Add a new member
     *
     */
    private function Addmember()
    {
        global $phpbb_admin_path, $phpEx, $user;

        $newmember = new \bbdkp\controller\members\Members();
        $newmember->game_id = request_var('game_id', '');
        $newmember->member_name = utf8_normalize_nfc(request_var('member_name', '', true));
        $newmember->member_title = utf8_normalize_nfc(request_var('member_title', '', true));
        $newmember->member_guild_id = request_var('member_guild_id', 0);
        $newmember->member_rank_id = request_var('member_rank_id', 99);
        $newmember->member_level = request_var('member_level', 1);
        $newmember->member_realm = request_var('realm', '');
        $newmember->member_region = request_var('region_id', '');
        if (!in_array($newmember->member_region, $newmember->regionlist)) {
            $newmember->member_region = '';
        }
        $newmember->member_race_id = request_var('member_race_id', 1);
        $newmember->member_class_id = request_var('member_class_id', 1);
        $newmember->member_role = request_var('member_role', '');
        $newmember->member_gender_id = isset($_POST['gender']) ? request_var('gender', '') : '0';
        $newmember->member_comment = utf8_normalize_nfc(request_var('member_comment', '', true));
        $newmember->member_joindate = mktime(0, 0, 0, request_var('member_joindate_mo', 0), request_var('member_joindate_d', 0), request_var('member_joindate_y', 0));
        $newmember->member_outdate = 0;
        if (request_var('member_outdate_mo', 0) + request_var('member_outdate_d', 0) != 0) {
            $newmember->member_outdate = mktime(0, 0, 0, request_var('member_outdate_mo', 0), request_var('member_outdate_d', 0), request_var('member_outdate_y', 0));
        }
        $newmember->member_achiev = 0;
        $newmember->member_armory_url = utf8_normalize_nfc(request_var('member_armorylink', '', true));
        $newmember->phpbb_user_id = request_var('phpbb_user_id', 0);
        $newmember->member_status = request_var('activated', '') == 'on' ? 1 : 0;

        $newmember->Armory_getmember();
        $newmember->Makemember();

        if ($newmember->member_id > 0)
        {
            //record added. now update some stats
            meta_refresh(2, append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $newmember->member_guild_id));
            $success_message = sprintf($user->lang['ADMIN_ADD_MEMBER_SUCCESS'], ucwords($newmember->member_name), date("F j, Y, g:i a"));

            $this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $newmember->member_guild_id) . '"><h3>' . $user->lang['RETURN_MEMBERLIST'] . '</h3></a>';
            trigger_error($success_message . $this->link, E_USER_NOTICE);

        }
        else
        {
            meta_refresh(2, append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $newmember->member_guild_id));

            $failure_message = sprintf($user->lang['ADMIN_ADD_MEMBER_FAIL'], ucwords($newmember->member_name));
            trigger_error($failure_message . $this->link, E_USER_WARNING);
        }
    }

    /**
     * Update bbdkp member
     *
     */
    private function UpdateMember()
    {
        global $phpbb_admin_path, $phpEx, $user;

        $updatemember = new \bbdkp\controller\members\Members();
        $updatemember->member_id = request_var('hidden_member_id', 0);

        if ($updatemember->member_id == 0)
        {
            $updatemember->member_id = request_var(URI_NAMEID, 0);
        }
        $updatemember->Getmember();

        $updatemember->game_id = request_var('game_id', '');
        $updatemember->member_class_id = request_var('member_class_id', 0);
        $updatemember->member_race_id = request_var('member_race_id', 0);
        $updatemember->member_role = request_var('member_role', '');
        $updatemember->member_realm = request_var('realm', '');
        $updatemember->member_region = request_var('region_id', '');

        if (!in_array($updatemember->member_region, $updatemember->regionlist))
        {
            $updatemember->member_region = '';
        }

        $updatemember->member_gender_id = isset($_POST['gender']) ? request_var('gender', '') : '0';
        $updatemember->member_name = utf8_normalize_nfc(request_var('member_name', '', true));
        $updatemember->member_title = utf8_normalize_nfc(request_var('member_title', '', true));
        $updatemember->member_guild_id = request_var('member_guild_id', 0);
        $updatemember->member_rank_id = request_var('member_rank_id', 99);
        $updatemember->member_level = request_var('member_level', 0);
        $updatemember->member_joindate = mktime(0, 0, 0, request_var('member_joindate_mo', 0), request_var('member_joindate_d', 0), request_var('member_joindate_y', 0));
        $updatemember->member_outdate = 0;

        if (request_var('member_outdate_mo', 0) + request_var('member_outdate_d', 0) != 0)
        {
            $updatemember->member_outdate = mktime(0, 0, 0, request_var('member_outdate_mo', 0), request_var('member_outdate_d', 0), request_var('member_outdate_y', 0));
        }

        $updatemember->member_achiev = request_var('member_achiev', 0);
        $updatemember->member_comment = utf8_normalize_nfc(request_var('member_comment', '', true));
        $updatemember->phpbb_user_id = request_var('phpbb_user_id', 0);

        if ($updatemember->member_rank_id < 90)
        {
            $updatemember->Armory_getmember();
        }

        $updatemember->member_status = request_var('activated', '') == 'on' ? 1 : 0;

        $old_member = new \bbdkp\controller\members\Members();
        $old_member->member_id = $updatemember->member_id;
        $old_member->Getmember();
        $updatemember->Updatemember($old_member);

        meta_refresh(1, append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $updatemember->member_guild_id));
        $this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $updatemember->member_guild_id) . '"><h3>' . $user->lang['RETURN_MEMBERLIST'] . '</h3></a>';
        $success_message = sprintf($user->lang['ADMIN_UPDATE_MEMBER_SUCCESS'], $updatemember->member_name);
        trigger_error($success_message . $this->link);

    }

    /**
     *
     * Delete bbdkp member
     *
     */
    private function DeleteMember()
    {
        global $user, $phpbb_admin_path, $phpEx;
        $deletemember = new \bbdkp\controller\members\Members();
        $deletemember->member_id = request_var('del_member_id', 0);
        $deletemember->Getmember();
        $deletemember->Deletemember();
        $success_message = sprintf($user->lang['ADMIN_DELETE_MEMBERS_SUCCESS'], $deletemember->member_name);

        meta_refresh(1, append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" . URI_GUILD . "=" . $deletemember->member_guild_id));
        $this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_mm&amp;mode=mm_listmembers&amp;" .
                URI_GUILD . "=" . $deletemember->member_guild_id) . '"><h3>' . $user->lang['RETURN_MEMBERLIST'] . '</h3></a>';

        trigger_error($success_message . $this->link, E_USER_WARNING);

    }

    /**
     * Activates/deactivates the selected members
     */
    private function ActivateList()
    {
        if (!check_form_key('mm_listmembers'))
        {
            trigger_error('FORM_INVALID');
        }
        $activatemember = new \bbdkp\controller\members\Members();
        $activate_members = request_var('activate_id', array(0));
        $member_window = request_var('hidden_member', array(0));
        $activatemember->Activatemembers($activate_members, $member_window);
        unset($activatemember);
    }

    /**
     * Call the Character API
     *
     */
    private function CallCharacterAPI()
    {
        global $db;

        $Guild = new \bbdkp\controller\guilds\Guilds();
        $Guild->guildid = request_var('hidden_guildid', 0);
        $Guild->Getguild();

        $minlevel = request_var('hidden_minlevel', 0);
        $maxlevel = request_var('hidden_maxlevel', 200);
        $selectactive = request_var('hidden_active', 0);
        $selectnonactive = request_var('hidden_nonactive', 0);
        $member_filter = utf8_normalize_nfc(request_var('hidden_member_name', '', true)) ;

        $members_result = $Guild->listmembers('member_id', 0, 0 , $minlevel, $maxlevel, $selectactive, $selectnonactive, $member_filter, true);

        $log = '';
        $i = 0;
        $j=0;
        while ($row = $db->sql_fetchrow($members_result))
        {
            if ($j > 100)
            {
                break;
            }
            $member = new \bbdkp\controller\members\Members($row['member_id']);

            $last_update = $member->last_update;

            $diff = \round( \abs ( (\time() - $last_update)) / 86400, 2) ;

            // 1 days ago ? call armory
            if($diff > 1)
            {
                $i += 1;
                if ($log != '') $log .= ', ';
                $old_member = new \bbdkp\controller\members\Members($row['member_id']);


                if (isset($member))
                {
                    if ($member->member_rank_id < 90)
                    {
                        $member->Armory_getmember();
                    }
                    $member->Updatemember($old_member);
                }

                unset($old_member);
                $log .= $row['member_name'];
            }

            unset($member);
            $j++;

        }
        $db->sql_freeresult($members_result);
        unset ($members_result);
        return array($i, $log);


    }
}