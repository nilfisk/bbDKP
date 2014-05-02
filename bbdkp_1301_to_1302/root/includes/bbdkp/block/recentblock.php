<?php
/**
 * Recent topics block
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
/**  begin recent topics block ***/
global $auth;

// get authorised forums
$can_read_forum = $auth->acl_getf('f_read');	//Get the forums the user can read from
$forums_auth_ary = array();
foreach($can_read_forum as $key => $forum)
{
    if($forum['f_read'] != 0)
    {
        $forums_auth_ary[] = $key;
    }
}

unset($can_read_forum);
$fetchtopics = array();

$fetchtopics = fetch_topics($forums_auth_ary, $config['bbdkp_portal_rtno'], $config['bbdkp_portal_rtlen']);

if(!empty($fetchtopics))
{
	for ($i = 0; $i < sizeof($fetchtopics); $i++)
	{
		$template->assign_block_vars('recent_topic_row', array(
			'U_TITLE'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $fetchtopics[$i]['forum_id'] . '&amp;t=' .
				$fetchtopics[$i]['topic_id'] . '&amp;p=' . $fetchtopics[$i]['post_id'] . '#p' . $fetchtopics[$i]['post_id']),
			'L_TITLE'		=> $fetchtopics[$i]['topic_title'],
			'U_POSTER'		=> $fetchtopics[$i]['user_link'],
			'TOPIC_FOLDER_IMG'	=> $fetchtopics[$i]['topic_folder_img'],
			'TOPIC_FOLDER_IMG_SRC'	=> $fetchtopics[$i]['topic_folder_img_src'],
			'TOPIC_FOLDER_IMG_ALT'	=> $fetchtopics[$i]['topic_folder_img_alt'],
			'TOPIC_ICON_IMG'		=> $fetchtopics[$i]['topic_img'],
			'TOPIC_ICON_IMG_WIDTH'	=> $fetchtopics[$i]['topic_img_width'],
			'TOPIC_ICON_IMG_HEIGHT'	=> $fetchtopics[$i]['topic_img_heigth'],
			'UNREAD' 				=>  $fetchtopics[$i]['post_unread'],
			'TOPIC_AUTHOR_COLOUR'	=> get_username_string('colour', $fetchtopics[$i]['topic_poster'], $fetchtopics[$i]['topic_first_poster_name'], $fetchtopics[$i]['topic_first_poster_colour']),
			'POSTED_BY'				=> sprintf($user->lang['POSTED_BY_ON'], $fetchtopics[$i]['user_link'], $fetchtopics[$i]['topic_last_post_time']),
			'S_TOPIC_UNAPPROVED'	=> $fetchtopics[$i]['topic_unapproved'],
			'S_POSTS_UNAPPROVED'	=> $fetchtopics[$i]['posts_unapproved'],
			'U_MCP_QUEUE'			=> $fetchtopics[$i]['u_mcp_queue'],
			'UNAPPROVED_IMG'		=> $fetchtopics[$i]['unapproved_img'],
			'S_TOPIC_REPORTED'		=> $fetchtopics[$i]['topic_reported'],
			'U_MCP_REPORT'			=> $fetchtopics[$i]['u_mcp_report'],
			'REPORTED_IMG'			=> $fetchtopics[$i]['reported_img'],
			'U_VIEW_FORUM'			=> $fetchtopics[$i]['view_forum_url'],
			'FORUM_NAME'			=> $fetchtopics[$i]['forum_name'],
			'ATTACH_ICON'			=> $fetchtopics[$i]['attach_icon'],

			'S_POSTTIME'			=> $fetchtopics[$i]['topic_last_post_time'],
			'LAST_POST_TIME'		=> $fetchtopics[$i]['topic_last_post_time'],
			)
		);

		foreach ($fetchtopics[$i]['forum_parents'] as $parent_id => $data)
		{
			$template->assign_block_vars( 'recent_topic_row.forum_parents', array(
					'FORUM_ID'			=> $parent_id,
					'FORUM_NAME'		=> $data[0],
					'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $parent_id),
			));
		}
		$a=1;

	}
}
else
{
	$template->assign_vars(array(
		'NO_RECENT'	=> $user->lang['NO_RECENT_TOPICS']
		)
	);
}

$template->assign_vars(array(
	'S_DISPLAY_RT' => true,
	)
);

/**
 * Retrieve a set of topics and trim the names if necessary
 *
 * @param array $forum_id_ary
 * @param int $num_topics
 * @param int $num_chars
 * @return multitype:
 */
function fetch_topics($forum_id_ary, $num_topics, $num_chars)
{
	global $db, $cache, $user, $config, $auth, $template, $phpbb_root_path, $phpEx;

	//No authed forums, or desired number of topics is zero or less
	if(!sizeof($forum_id_ary) || $num_topics < 1)
	{
		return array();
	}

	$sql_arr = array(
			'SELECT'	=> 't.*, tp.topic_posted, f.forum_name, f.parent_id, f.forum_parents, f.left_id, f.right_id ',
			'FROM'		=> array(TOPICS_TABLE => 't'),
			'LEFT_JOIN'	=> array(
					array(
							'FROM'	=> array(TOPICS_POSTED_TABLE => 'tp'),
							'ON'	=> 't.topic_id = tp.topic_id AND tp.user_id = ' . $user->data['user_id'],
					),
					array(
							'FROM'	=> array(FORUMS_TABLE => 'f'),
							'ON'	=> 'f.forum_id = t.forum_id',
					),
			),
			'WHERE'		=> ' topic_type <> ' . POST_GLOBAL . ' AND ' . $db->sql_in_set('t.forum_id', $forum_id_ary) ,
			'ORDER_BY'	=> 't.topic_last_post_time DESC',
	);

	$sql = $db->sql_build_query('SELECT', $sql_arr);
	$result = $db->sql_query_limit($sql, $num_topics);


	$row = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	/**
	 * Get list of unread topics
	 * @return array[int][int]		Topic ids as keys, mark_time of topic as value
	 */
	$unreadlist = \get_unread_topics($user_id = false, $sql_extra = '', $sql_sort = '', $sql_limit = 1001, $sql_limit_offset = 0);


	$topics = array();
	$i = 0;
	foreach($row as $topic)
	{

		// FORUM LINK
		$view_forum_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $topic['forum_id']);

		$topic_moderation = false;
		$posts_moderation = false;
		$u_mcp_queue= '';
		//is this topic not approved and does poster have moderation permission ?
		if (!$topic['topic_approved'])
		{
			if ($auth->acl_get('m_approve', $topic['forum_id']))
			{
				$topic_moderation = true;
			}
		}
		else
		{
			//if this topic is approved, are there replies yet unapproved ?
			if ($topic['topic_replies'] < $topic['topic_replies_real'])
			{
				if ($auth->acl_get('m_approve', $topic['forum_id']))
				{
					$posts_moderation = true;
				}
			}
		}
		// build the moderation queue url
		$u_mcp_queue = ($topic_moderation || $posts_moderation) ?
			append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_moderation) ? 'approve_details' : 'unapproved_posts') . '&amp;t='. $topic['topic_id'], true, $user->session_id) : '';

		//count the number of replies to the topic
		$replies = ($auth->acl_get('m_approve', $topic['forum_id'])) ? $topic['topic_replies_real'] : $topic['topic_replies'];

		//build the folder names
		$folder = $folder_new = '';
		if ($topic['topic_status'] == ITEM_MOVED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_MOVED'];
			$folder_img = 'topic_moved';
			$folder_alt = 'TOPIC_MOVED';
		}
		else
		{
			switch ($topic['topic_type'])
			{
				case POST_GLOBAL:
					$topic_type = $user->lang['VIEW_TOPIC_GLOBAL'];
					$folder = 'global_read';
					$folder_new = 'global_unread';
					break;

				case POST_ANNOUNCE:
					$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
					$folder = 'announce_read';
					$folder_new = 'announce_unread';
					break;

				case POST_STICKY:
					$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
					$folder = 'sticky_read';
					$folder_new = 'sticky_unread';
					break;

				default:
					$topic_type = '';
					$folder = 'topic_read';
					$folder_new = 'topic_unread';

					// Hot topic threshold is for posts in a topic, which is replies + the first post. ;)
					if ($config['hot_threshold'] && ($replies + 1) >= $config['hot_threshold'] && $topic['topic_status'] != ITEM_LOCKED)
					{
						$folder .= '_hot';
						$folder_new .= '_hot';
					}
					break;
			}

			if ($topic['topic_status'] == ITEM_LOCKED)
			{
				$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
				$folder .= '_locked';
				$folder_new .= '_locked';
			}

			if( array_key_exists($topic['topic_id'], $unreadlist))
			{
				$folder_img = $folder_new;
				$folder_alt = 'UNREAD_POSTS';
				$topic['unread'] = true;
			}
			else
			{
				$topic['unread'] = false;
				$folder_img = $folder;
				$folder_alt = ($topic['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_UNREAD_POSTS';
			}

			// Posted image?
			if (!empty($topic['topic_posted']) && $topic['topic_posted'])
			{
				$folder_img .= '_mine';
			}
		}

		if ($topic['poll_start'] && $topic['topic_status'] != ITEM_MOVED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_POLL'];
		}

		// Trim the topic title and add ellipse
		if ($num_chars != 0 and strlen($topic['topic_title']) > $num_chars)
	    {
	        $topic['topic_title'] = substr($topic['topic_title'], 0, $num_chars) . '...';
	    }

    	$icons = array();
	    if ($topic['icon_id'] && $auth->acl_get('f_icons', $topic['forum_id']))
	    {
	    	$icons = $cache->obtain_icons();
	    }


	    $attachmenticon = '';
	    //can user download ?
	    if( $auth->acl_get('u_download') && $auth->acl_get('f_download', $topic['forum_id'] )  )
		{
			//does post have attachments ?
			if ($topic['topic_attachment'])
			{
				$attachmenticon = $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']);
			}
    	}
	
		$topics[$i]['topic_first_poster_colour'] = $topic['topic_first_poster_colour'];	
		$topics[$i]['topic_first_poster_name'] = $topic['topic_first_poster_name'];
		$topics[$i]['topic_poster'] = $topic['topic_poster'];
		$topics[$i]['forum_id'] = $topic['forum_id'];
		$topics[$i]['post_id'] = $topic['topic_last_post_id'];
	    $topics[$i]['post_unread'] = $topic['unread'];
		$topics[$i]['topic_id'] = $topic['topic_id'];
		$topics[$i]['topic_folder_img'] = $user->img($folder_img, $folder_alt);
		$topics[$i]['topic_folder_img_src'] =  $user->img($folder_img, $folder_alt, false, '', 'src');
		$topics[$i]['topic_folder_img_alt'] = $user->lang[$folder_alt];
		$topics[$i]['topic_img'] = (!empty($icons[$topic['icon_id']])) ? $icons[$topic['icon_id']]['img'] : '';
		$topics[$i]['topic_img_width'] = (!empty($icons[$topic['icon_id']])) ? $icons[$topic['icon_id']]['width'] : '';
		$topics[$i]['topic_img_heigth'] = (!empty($icons[$topic['icon_id']])) ? $icons[$topic['icon_id']]['height'] : '';
		$topics[$i]['topic_title'] = $topic['topic_title'];
		$topics[$i]['topic_last_post_id'] = $topic['topic_last_post_id'];
		$topics[$i]['topic_last_post_time'] = $user->format_date($topic['topic_last_post_time']);
		$topics[$i]['user_link'] = get_username_string('full', $topic['topic_last_poster_id'], $topic['topic_last_poster_name'], $topic['topic_last_poster_colour']);
		$topics[$i]['topic_unapproved'] = $topic_moderation;
		$topics[$i]['posts_unapproved'] = $posts_moderation;
		$topics[$i]['topic_reported'] = ($topic['topic_reported'] && $auth->acl_get('m_report', $topic['forum_id'] )) ? true : false;
		$topics[$i]['unapproved_img']  = ($topic_moderation || $posts_moderation) ? $user->img('icon_topic_unapproved', ($topic_moderation) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '';
		$topics[$i]['u_mcp_report'] = append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=reports&amp;f=' . $topic['forum_id'] . '&amp;t=' . $topic['topic_id'], true, $user->session_id);
		$topics[$i]['u_mcp_queue'] = $u_mcp_queue;
		$topics[$i]['reported_img'] = ($topic['topic_reported'] && $auth->acl_get('m_report', $topic['forum_id'])) ? $user->img('icon_topic_reported', 'TOPIC_REPORTED') : '';
		$topics[$i]['view_forum_url'] = $view_forum_url;
		$topics[$i]['forum_name'] = $topic['forum_name'];
		$topics[$i]['attach_icon'] = $attachmenticon;
		$topics[$i]['forum_parents'] = \get_forum_parents($topic);

		$i++;
	}

	if (!empty($icons[$topic['icon_id']]))
	{
		$template->assign_vars(array(
				'S_TOPIC_ICONS'			=> true,
		));

	}


	return $topics;
}