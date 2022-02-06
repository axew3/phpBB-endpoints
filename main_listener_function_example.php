  public function common_mimic_endpoints()
  {
    // to understand from where values are coming, how have been generated, and how the logic work, see wp_w3all.php file
    // wp/wp-content/plugins/wp-w3all-phpbb-integration/wp_w3all.php
    // add_action( 'delete_user', 'w3all_usersdata_predelete_in_phpbb_exec', 10, 3);
    // add_action( 'deleted_user', 'w3all_usersdata_deleted_in_phpbb_exec', 10, 3);

    $phpBB_function_endpoint = $this->request->variable('w3all_phpBB_function_endpoint', '', true);
    if ( empty($phpBB_function_endpoint) ) { return; }

     $tk = stripslashes(htmlspecialchars($this->config['avatar_salt'], ENT_COMPAT)); // ent_compat?
     $tk = substr(md5($tk), 4, -8);
     $tk0 = substr(strtoupper(md5($tk)), 0, -16);
     $tk .= $tk0;

    $ckv = 'w3all_phpBB_function_endpoint___'.$tk0;
    $w3all_phpBB_function_endpoint_ck_randvar = $this->request->variable($ckv, '', true);

    if(empty($w3all_phpBB_function_endpoint_ck_randvar)){ return; }

    // check that the request come with valid token or avoid to follow
    $w3allastoken = $this->request->variable('w3allastoken', '', true);
    $w3allastoken = str_replace(chr(0), '', $w3allastoken);

      if( !empty($phpBB_function_endpoint) && ! password_verify($tk, $w3allastoken) )
       { // stop any going on here, something goes wrong (and a antibruteforce could be easily added)
         // but it is may not necessary, since w3all_usersdata_deleted_in_phpbb_exec() function into wp_w3all.php, empty the value of the relate user's meta db table, just after the first user's deletion in WP happen (so just after phpBB received and processed the $_POST here)
         return;
        }

    if( $phpBB_function_endpoint == 'user_delete' )
    {
      $wpdelete_phpbbulist_delby = $this->request->variable('w3all_wpdelete_phpbbulist_delby', '', true);
      $wpdelete_phpbbulist_delby = intval($wpdelete_phpbbulist_delby); // ID of the WP user deleting in WP
      if( $wpdelete_phpbbulist_delby < 1 ){ return; }
      $delmode = $this->request->variable('w3all_delmode', '', true);
      $delmode = ($delmode == 'retain') ? 'retain' : 'remove';

      $w3all_nonce_reqtime_rand = $this->request->variable('w3all_nonce_reqtime_rand', '', true);

      if( empty($w3all_nonce_reqtime_rand) OR preg_match('/[^_0-9A-Za-z]/',$w3all_nonce_reqtime_rand) )
      { return; }

      $wdb = new \phpbb\db\driver\mysqli();
      $wdb->sql_connect($this->wp_w3all_dbhost, $this->wp_w3all_dbuser, $this->wp_w3all_dbpasswd, $this->wp_w3all_dbname, $this->wp_w3all_dbport, false, false);
      $sql = "SELECT * FROM ".$this->wp_w3all_table_prefix."usermeta WHERE user_id = '". $wpdelete_phpbbulist_delby ."' AND meta_key = 'w3all_wpdelete_phpbbulist_delby'";
      $result = $wdb->sql_query($sql);
      $delete_ary = $wdb->sql_fetchrow($result);
      $wdb->sql_freeresult($result);
      $wdb->sql_close();

    if(!empty($delete_ary['meta_value']))
    {
      $del_ary = unserialize($delete_ary['meta_value']);

      if(!is_array($del_ary) OR empty($del_ary)){ return; }
      // to understand this see
      // function w3all_usersdata_predelete_in_phpbb_exec($id, $reassign, $user)
      // into wp_w3all.php file
      $w3all_nonce_reqtime_rand_meta = array_pop($del_ary); // Get and Remove the $w3all_nonce_reqtime_rand array from array (last key)
      // check if the whole passed value match
      if(empty($w3all_nonce_reqtime_rand_meta['w3all_nonce_reqtime_rand']) OR $w3all_nonce_reqtime_rand_meta['w3all_nonce_reqtime_rand'] != $w3all_nonce_reqtime_rand)
      { return; }
      $post_nonce_reqtime = explode("___", $w3all_nonce_reqtime_rand);
      // extract nonce and reqtime: check when (the time) the request has been done, so to avoid if too old
      $nonce_reqtime = explode("___", $w3all_nonce_reqtime_rand_meta['w3all_nonce_reqtime_rand']);
      $metareqtime = $nonce_reqtime[0];
      $metanonce = $nonce_reqtime[1];
      // for how many seconds from the cURL $_POST, the request can be valid? (12)
      if( time() > $metareqtime+12 ) { return; }

      $emails = '';
      foreach ( $del_ary as $e ){
       $emails .= "'".strtolower($e)."'".',';
      }

      $emails = substr($emails, 0, -1);
      $elist = 'LOWER(user_email) IN('.$emails.')';
file_put_contents('C:\DATA_ALL\Edit3.txt', $emails);

      if (!function_exists('user_delete')) // include only if or get error
      {
        include($this->phpbb_root_path . 'includes/functions_user.php');
        /**
         * Delete user(s) and their related data
         * @param string  $mode       Mode of posts deletion (retain|remove)
         * @param mixed   $user_ids     Either an array of integers or an integer
         * @param bool    $retain_username  True if username should be retained, false otherwise
         * @return bool
         //function user_delete($mode, $user_ids, $retain_username = true)
        */
       }

     $sql = "SELECT U.user_id
      FROM " . USERS_TABLE . "
      as U WHERE $elist";
      $res = $this->db->sql_query($sql);
     while ($row = $this->db->sql_fetchrow($res))
     {
      //user_delete($delmode, $row['user_id'], $retain_username = true); // delete user
     }

     $this->db->sql_freeresult($res);
    }
   }
  }
