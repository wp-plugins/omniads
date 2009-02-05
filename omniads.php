<?php

/*
Plugin Name: OmniAds
Plugin URI: http://www.naden.de/blog/omniads
Description: Ad management plugin for Wordpress with all the features you need for smooth workflow. See the Plugin Homepage for a full list of features. German: Plugin zur Verwaltung von Werbeeinbindeungen aller Art in Wordpress. Die komplette Liste der features findest du auf der Plugin Homepage.
Version: 0.51
Author: Naden Badalgogtapeh
Author URI: http://www.naden.de/blog
*/

/*
 * History:
 *
 * v0.51 04.02.2009  channel name length expanded to 200 chars
 * v0.5 21.01.2009  added widgets for ad delivery in sidebar
 * v0.4 20.01.2009  added <!--omniads:CHANNEL_NAME--> for in content ads
 * v0.3 29.07.2008  added channel status
 * v0.2 20.07.2008  added "more" channel for ad delivery after <!--more--> tag
 * v0.1 18.07.2008  initial release
 *
 */

class OmniAds
{
  var $tables;
  var $types;
  var $show;
  var $options;
  var $version;
  var $id;
  var $name;
  var $url;
  var $path;
  var $total;

  function OmniAds()
  {
    global $table_prefix;

    $this->version    = '0.5';
    $this->id         = 'omniads';
    $this->name       = 'OmniAds Plugin v' . $this->version;
    $this->url        = 'http://www.naden.de/blog/omniads';
    $this->path       = dirname( __FILE__ );
    $this->show       = true;
    $this->index      = 0;

	  $locale = get_locale();

	  if( empty( $locale ) )
    {
		  $locale = 'en_US';
    }

    load_textdomain( $this->id, sprintf( '%s/%s.mo', $this->path, $locale ) );

    $this->types = array(
      'HTML', 
      'PHP'
    );

    $this->tables = array(
      'units'   => $table_prefix . $this->id . '_units',
      'channel' => $table_prefix . $this->id . '_channel'
    );
    
    $this->LoadOptions();

    $this->LoadExclude();
    
    global $wpdb;

    $this->channel = array();

    $items = $wpdb->get_results( "SELECT name FROM {$this->tables[ 'channel' ]}" );
    
    if( $items )
    {
      foreach( $items as $channel )
      {
        $this->channel[ $channel->name ] = $channel->name;
      }
    }
    
    unset( $items );

    if( is_admin() )
    {
      add_action( 'admin_menu', array( &$this, 'OptionMenu' ) ); 
      
      if( stripos( $_SERVER[ 'REQUEST_URI' ], 'omniads' ) !== false )
      {
        add_action( 'admin_head', array( &$this, 'AdminHeader' ) );
      }
    }
    else
    {
		  add_action( 'wp_head', array( &$this, 'BlogHeader' ) );
      add_action( 'the_content', array( &$this, 'ContentFilter' ), 11 );
    }
    
    add_action( 'widgets_init', array( &$this, 'InitWidgets' ) );
  }

  function AdminHeader()
  {
  
print <<<DATA
<style type="text/css">
table.omniads th a {
  font-weight: normal;
  color: #000;
  text-decoration:underline;
}
table.omniads th a:hover {
  font-weight: normal;
  color: #000;
  text-decoration:none;
}
table.omniads th a.order_by {
  font-weight: bold;
  color: #000;
  text-decoration:underline;
}
table.omniads th a.order_by:hover {
  font-weight: bold;
  color: #000;
  text-decoration:none;
}
table.omniads textarea {
  width: 95%;
  height: 100px;
}
</style>
DATA;
  }
  
  function BlogHeader()
  {
    printf( '<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version );
  }
  
  function InitWidgets()
  {
    if( function_exists( 'register_sidebar_widget' ) && function_exists( 'register_widget_control' ) )
    {
      for( $k=1; $k<=$this->options[ 'widgets' ]; $k++ )
      {
        $name = array( 'OmniAds (%d)', null, $k );

			  register_sidebar_widget( $name, array( $this, 'Widget' ), $k );
			  register_widget_control( $name, array( $this, 'WidgetControl' ), null, 75, $k );
      }
    }
  }
  
  function WidgetControl( $index )
  {
    $options = $newoptions = get_option( 'omniads_widgets' );

		if( isset( $_POST[ 'omniads-submit-' . $index ] ) )
    {
      $id = 'omniads-channel-unit-' . $index;

      if( isset( $_POST[ $id ] ) )
      {
        list( $type, $id ) = explode( ':', $_POST[ $id ] );
        $newoptions[ $index ] = array( 'id' => $id, 'type' => $type );
      }
		}

		if( $options != $newoptions )
    {
			$options = $newoptions;
			update_option( 'omniads_widgets', $options );
		}

    printf(
      '<p><label for="omniads-channel-unit-%d">%s<select style="width: 250px;" id="omniads-channel-unit-%d" name="omniads-channel-unit-%d">%s%s</select></label></p>', 
      $index, 
      __( 'Select Channel or Unit to display:', $this->id ),
      $index, 
      $index, 
      $this->GetChannelAsSelectbox(
        array(
          'status' => array( 1 ), 
          'selected' => $options[ $index ][ 'type' ] == 'channel' ? intval( $options[ $index ][ 'id' ] ) : -1
        )
      ),
      $this->GetUnitsAsSelectbox(
        array(
          'status' => array( 1 ), 
          'selected' => $options[ $index ][ 'type' ] == 'unit' ? intval( $options[ $index ][ 'id' ] ) : -1
        ) 
      )
    );

    printf( '<input type="hidden" id="omniads-submit-%d" name="omniads-submit-%d" value="1" />', $index, $index );
	}
  
  function Widget( $args, $index )
  {
    extract( $args );
		
    $options = get_option( 'omniads_widgets' );

    if( $options[ $index ] )
    {

      switch( $options[ $index ][ 'type' ] )
      {
        case 'unit':
        {
          $data = $this->LoadUnit( $options[ $index ][ 'id' ] );
          break;
        }
        case 'channel':
        {
          $data = $this->LoadChannel( $options[ $index ][ 'id' ] );
          break;
        }
      }
      
      if( $data )
      {
        printf( '%s%s%s%s%s%s', $before_widget, $before_title, '', $after_title, $data, $after_widget );
      }
      else
      {
        $this->Log( @$options[ $index ][ 'type' ] . ' empty' );
      }
    }
  }
  
  function LoadExclude()
  {
    if( $this->options[ 'exclude' ] )
    {
      foreach( explode( "\n", (array)$this->options[ 'exclude' ] ) as $exclude )
      {
        $exclude = trim( $exclude );
        
        if( empty( $exclude ) )
        {
          continue;
        }

        if( eregi( $exclude, $_SERVER[ 'REQUEST_URI' ] ) )
        {
          $this->show = false;
          break;
        }
      }
    }
  }

  function ContentFilter( $buffer )
  {
    preg_match( '@<span id="more-([0-9].*)"></span></p>@', $buffer, $matches );

    if( count( $matches ) != 2 )
    {
      preg_match( '@<span id="more-([0-9].*)"></span>@', $buffer, $matches );
    }
    
    if( count( $matches ) == 2 )
    {
      if( $this->show )
      {
        $buffer = str_replace( 
          $matches[ 0 ], 
          $matches[ 0 ] . $this->LoadChannel( 'more' ), 
          $buffer
        );
      }
    }

    unset( $matches );
    
    preg_match_all( '@<!--omniads:(.*?)-->@', $buffer, $matches, PREG_SET_ORDER );
    
    if( count( $matches ) > 0 )
    {      
      foreach( $matches as $match )
      {
        $data = '';

        if( $this->show )
        {
          // if only digits, we have a unit
          if( ereg( "^([0-9].*)$", $match[ 1 ] ) )
          {
            $data = $this->LoadUnit( $match[ 1 ] );
          }
          else
          {
            $data = $this->LoadChannel( $match[ 1 ] );
          }
        }
        $buffer = str_replace( $match[ 0 ], $data, $buffer );
      }
    }

    return( $buffer );
  }

  function OptionMenu()
  {
    $root = $this->id . '/admin/options.php';

		if( function_exists( 'add_menu_page' ) )
		{
			add_menu_page( 'OmniAds', 'OmniAds', 0, $root );
    }

    if( function_exists( 'add_submenu_page' ) )
		{
      add_submenu_page( $root, 'OmniAds', 'OmniAds', 0, $root );
      
      add_submenu_page( $root, 'Units', 'Units', 0, $this->id . '/admin/units.php' );
      add_submenu_page( $root, 'Channel', 'Channel', 0, $this->id . '/admin/channel.php' );
      add_submenu_page( $root, __( 'Help', $this->id ), __( 'Help', $this->id ), 0, $this->id . '/admin/help.php' );
    }
  }

  function LoadOptions()
  {

    $this->options = get_option( $this->id );

    if( !$this->options )
    {
      $this->options = array(
        'installed' => time(),
        'limit'     => 25,
        'widgets'   => 1,
        'exclude'   => ''
			);

      add_option( $this->id, $this->options, $this->name, 'yes' );

      global $wpdb;

#       SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

      $sql = <<<DATA
        CREATE TABLE IF NOT EXISTS `{$this->tables[ 'channel' ]}` (
          `id` bigint(20) NOT NULL auto_increment,
          `status` smallint(1) NOT NULL default '1',
          `name` varchar(200) NOT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `name` (`name`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
DATA;

		  $wpdb->query( $sql );
		  
      $wpdb->query( "INSERT IGNORE INTO `{$this->tables[ 'channel' ]}` SET `name` = 'default', `status`=1" );
      $wpdb->query( "INSERT IGNORE INTO `{$this->tables[ 'channel' ]}` SET `name` = 'widget', `status`=1" );
      
      $sql = <<<DATA
        CREATE TABLE IF NOT EXISTS `{$this->tables[ 'units' ]}` (
          `id` bigint(20) NOT NULL auto_increment,
          `status` smallint(1) NOT NULL,
          `channel` varchar(200) NOT NULL,
          `title` varchar(200) NOT NULL,
          `content` text NOT NULL,
          `type` enum('HTML','PHP') NOT NULL default 'HTML',
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
DATA;

		  $wpdb->query( $sql );
      
      printf( '<img src="http://www.naden.de/gateway/?q=%s" width="1" height="1" />', urlencode( sprintf( 'action=install&plugin=%s&version=%s&platform=%s&url=%s', $this->id, $this->version, 'wordpress', get_bloginfo( 'wpurl' ) ) ) );

    }
  }

  function GetFormfield( $max = -1, $prefix = '', $name, $type, $value, $default = '', $faq = '' )
  {
    if( $max != -1 )
    {
      $max = ' maxlength="' . $max . '"';
    }
    else
    {
      $max = '';
    }

    if( !empty( $prefix ) )
    {
      $id = "{$prefix}_{$name}";
    }
    else
    {
      $id = "{$name}";
    }

    if( !empty( $prefix ) )
    {
      $name = "{$prefix}[{$name}]";
    }

    switch( $type )
    {
      case 'select':
      {
        $data = sprintf( '<select name="%s" id="%s">', $name, $id );

        foreach( $value as $k => $v )
        {
          $data .= sprintf( '<option value="%s"%s>%s</option>', $v, $v == $default ? ' selected="selected"' : '', $v );
        }

        $data .= '</select>';
        
        return( $data );
      }
      case 'hidden':
      { 
        return( sprintf( '<input type="hidden" name="%s" value=\'%s\' id="%s" />', $name, $value, $id ) );
      }
      case 'text':
      {
        return( sprintf( '<input type="text" name="%s" style="width:95%%;" value=\'%s\'%s id="%s" />%s', $name, empty( $value ) ? $default : $value, $max, $id, empty( $faq ) ? '' : '<br />' . $faq ) );
      }
      case 'yesnoradio':
      {
        return(
          sprintf( '<input type="radio" name="%s"%s value="1" />%s <input type="radio" name="%s"%s value="0" />%s', 
            $name, 
            $value == 1 ? ' checked="checked"' : '',
            __( 'yes', $this->id ),
            
            $name, 
            $value == 0 ? ' checked="checked"' : '',
            __( 'no', $this->id )
          ) 
        );
      }
      case 'checkbox':
      {
        return( sprintf( '<input type="checkbox" name="%s"%s id="%s" />', $name, $value == 1 ? ' checked="checked"' : '', $id ) );
      }
      case 'textarea':
      {
        return( sprintf( '<textarea name="%s" id="%s" cols="100" rows="5">%s</textarea>%s', $name, $id, ( empty( $value ) || count( $value ) == 0 ) ? $default : $value, empty( $faq ) ? '' : '<br />' . $faq ) );
      }
      case 'label':
      {
        return( $v );
      }
    }
  }
  
  function MergeFill( $params, $default )
  {
    foreach( $default as $k => $v )
    {
      if( !array_key_exists( $k, $params ) || is_null( $params[ $k ] ) )
      {
        $params[ $k ] = $v;
      }
    }

    return( $params );
  }

  function LoadChannel( $channel )
  {
    global $wpdb;
    
    $sql = "
      SELECT
        u.type AS type, 
        u.content AS content
      FROM
        {$this->tables[ 'units' ]} u,
        {$this->tables[ 'channel' ]} c
      WHERE
    ";
    
    if( ereg( "^([0-9].*)$", $channel ) )
    {
      $sql .= " c.id = {$channel} ";
    }
    else
    {
      $sql .= " u.channel = '{$channel}' ";
    }

    $sql .= "
      AND
        c.name = u.channel
      AND
        u.status = 1
      AND
        c.status = 1
      ORDER BY
        RAND()
      LIMIT
        1
    ";

    return( $this->TransformUnit( $wpdb->get_results( $sql ) ) );
  }
  
  function LoadUnit( $id )
  {
    global $wpdb;
    
    $sql = "
      SELECT
        type, content
      FROM
        {$this->tables[ 'units' ]}
      WHERE
        id = {$id}
      AND
        status = 1
      LIMIT
        1
    ";
    
    return( $this->TransformUnit( $wpdb->get_results( $sql ) ) );
  }
  
  function TransformUnit( $unit )
  {
    if( count( $unit ) == 0 )
    {
      return( false );
    }
    
    $unit = $unit[ 0 ];

    switch( $unit->type )
    {
      case 'HTML':
      {
        $data = stripslashes( $unit->content );
        break;
      }
      case 'PHP':
      {
        $data = stripslashes( $unit->content );
        $data = str_replace( '<' . '?php', '<' . '?', $data );

        ob_start();
        eval( '?'.'>' . trim( $data ). '<' . '?' );
        $data = ob_get_clean();

        break;
      }
      default:
      {
        $data = false;
      }
    }
    
    return( $data );
  }
  
  function GetUnits( $params = array() )
  {
    global $wpdb;

    $sql = "
      SELECT SQL_CALC_FOUND_ROWS
        *
      FROM
        {$this->tables[ 'units' ]}
      WHERE
        1=1
    ";

    $q = trim( $params[ 'q' ] );

    if( !empty( $q ) )
    {
      $q = mysql_real_escape_string( $q );
      
      $sql .= sprintf( " AND ( title LIKE '%%%s%%' OR channel LIKE '%%%s%%' ) ", $q, $q );
    }
    
    if( is_array( @$params[ 'status' ] ) )
    {
      $sql .= sprintf( " AND status IN(%s) ", implode( ',', $params[ 'status' ] ) );
    }

    if( !empty( $params[ 'order_by' ] ) )
    {
      $sql .= sprintf( " ORDER BY %s %s", $params[ 'order_by' ], $params[ 'order_direction' ] );
    }

    if( !empty( $params[ 'index' ] ) )
    {
      $sql .= sprintf( ' LIMIT %d, %d', $params[ 'index' ], $this->options[ 'limit' ] ); 
    }
    else
    {
      $sql .= sprintf( ' LIMIT %d', $this->options[ 'limit' ] ); 
    }

    $rows = $wpdb->get_results( $sql );
    
    $this->total = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

    return( $rows );
  }

  function GetPaging( $index,	$padding, $page, $order_by, $order_direction, $q,
		/// box
		$a, $b,

		/// prev
		$aa, $prelabel, $ab, 
		
		/// next
		$bb, $nxtlabel, $ba,

		/// range
		$cc, $ca,

		/// selected
		$dd, $da
	)
	{
		$data = $a;

		if( $index == 0 )
		{
			$index = 1;
		}
    
    $path = sprintf( '%s/wp-admin/admin.php?page=omniads/admin/%s.php&order_by=%s&order_direction=%s&q=%s', get_bloginfo( 'wpurl' ), $page, $order_by, $order_direction, $q );

		$total = ceil( $this->total / $this->options[ 'limit' ] );

		if( $total > 1 )
		{

			// show prev button
			if( $index > 1 )
			{
				$prevpage = intval( $index ) - 1;

				if( $prevpage < 1 )
				{
					$prevpage = 1;
				}

				$next_index = $prevpage == 1 ? '' : '&index=' . $prevpage;
				$data .= $aa . '<a href="' . $path . '&index=' . $next_index . '">' . $prelabel . '</a>' . $ab;
			
			  // middlebar
				if( $index > ( $this->options[ 'limit' ] / 2 ) + 1 )
				{
					$lowerBorder = $index - ( $this->options[ 'limit' ] / 2 );
					
					// evtl ist die obere grenze so klein, das wir hier mehr anzeigen k÷nnen?
					if($total - $index < ( $this->options[ 'limit' ] / 2 ) )
					{
						$lowerBorder -= ( $this->options[ 'limit' ] / 2 - ($total - $index));
					}
					// wenn lowerBorder nun kleiner als 1 ist, muss es wieder 1 werden
					
					if($lowerBorder < 1)
					{
						$lowerBorder = 1;
					}          
				}
				else
				{
					$lowerBorder = 1;
				}
			}
			else
			{
				$lowerBorder = 1;
			}
		  
			// wieviele seiten werden vor der aktuellen angezeigt?
			$lowerPages = $index - $lowerBorder;

			if( $lowerPages < 0 )
			{
				$lowerPages = 0;
			}

			// jetzt die obere grenze berechnen
			if($total > $index)
			{
				if($total - $index > ( $this->options[ 'limit' ] / 2 ) )
				{
					$upperBorder = $index + ( $this->options[ 'limit' ] / 2 );
					
					// evtl. k÷nnen wir weitere seiten von vor der aktuellen seite ³bernehmen?
					if($lowerPages < ( $this->options[ 'limit' ] / 2 ) )
					{
						$upperBorder += ( ( $range / $this->options[ 'limit' ] ) - $lowerPages );
					}
					// nun nur noch nachschauen ob die obere grenze gr÷¯er als die anzahl der seiten ist
					if( $upperBorder > $total )
					{
						$upperBorder = $total; 
					}
				}
				else
				{
					$upperBorder = $total;
				}
			}
			else
			{
				$upperBorder = $total;
			}
			
			// wieviele seiten werden nach der aktuellen seite angezeigt?
			$upperPages = $upperBorder - $index;     
			
			// links ausgeben

			$data .= $cc;
		  
			if( $lowerBorder > 1 )
			{
				$data .= '<a href="' . $path . '">1</a>';
			}
		  
			if( $lowerBorder < $index )
			{
				for( $i = $lowerBorder; $i<$index; $i++ )
				{
					$data .= '<a href="' . $path. '&index=' . $i . '">' . $i . '</a>';
				}
			}
		  
			$data .= $dd . $index . $da;

			if( $index < $total )
			{
				/// +1, da das Erste nicht verlinkt ist
				for( $i = $index+1; $i <= $upperBorder; $i++)
				{
					$data .= '<a href="'.$path. '&index='.$i.'">'.$i.'</a>';

				}
			}
		  
			if( $upperBorder < $total )
			{
				$data .= $padding . '<a href="'.$path .'&index='.$total.'">'.$total.'</a>';
	
			}

			$data .= $ca;
			
			/// vor knopf
			$nextpage = intval( $index ) + 1;

			if( empty( $index ) || $nextpage <= $total )
			{
				if(  $total < $nextpage )
				{
					$nextpage = $total; 
				}
				$data .= $bb . '<a href="' . $path . '&index=' .$nextpage.'">'. $nxtlabel .'</a>' . $ba;
			}

			$data .= $b;

			print( $data );
		}

	}
  
  function GetTableHeader( $order_by, $order_direction, $q, $index, $page, $fields )
  {
    $data = '';
    
    $order_direction = $order_direction == 'ASC' ? 'DESC' : 'ASC';

    foreach( $fields as $k => $v )
    {
      $data .= sprintf( '<th scope="col"><a href="%s/wp-admin/admin.php?page=omniads/admin/%s.php&order_by=%s&order_direction=%s&q=%s&index=%d" title="%s"%s>%s</a></th>',
      get_bloginfo( 'wpurl' ),
      $page,
      $k,
      $order_direction,
      $q,
      $index,
      __( 'sort by this col' ),
      $k == $order_by ? ' class="order_by"' : '',
      $v
      );
    }
    
    print( $data );
  }
  
  function GetUnitsAsSelectbox( $params = array( 'status' => array( 0, 1 ), 'selected' => -1 ) )
  {
    $units = $this->GetUnits( $params );

    if( $units )
    {
      $data = '<optgroup label="'.__( 'Units', $this->id ) . '">';
      
      foreach( $units as $single )
      {
        $data .= sprintf( '<option value="unit:%s"%s>%s (#%d)</option>', $single->id, $single->id == $params[ 'selected' ] ? ' selected="selected"' : '', $single->title, $single->id );
      }
      
      $data .= '</optgroup>';
      
      return( $data );
    }
  }
  
  function GetChannelAsSelectbox( $params = array( 'status' => array( 0, 1 ), 'selected' => -1 ) )
  {
    $channel = $this->GetChannel( $params );

    if( $channel )
    {
      $data = '<optgroup label="'.__( 'Channel', $this->id ) . '">';
      
      foreach( $channel as $single )
      {
        $data .= sprintf( '<option value="channel:%s"%s>%s (#%d)</option>', $single->id, $single->id == $params[ 'selected' ] ? ' selected="selected"' : '', $single->name, $single->id );
      }
      
      $data .= '</optgroup>';
      
      return( $data );
    }
  }
  
  function GetChannel( $params = array() )
  {
    global $wpdb;

    $sql = "SELECT SQL_CALC_FOUND_ROWS
        *
      FROM
        {$this->tables[ 'channel' ]}
      WHERE
        1=1
    ";
    
    $q = trim( $params[ 'q' ] );
    
    if( !empty( $q ) )
    {
      $sql .= sprintf( " AND name LIKE '%%%s%%' ", mysql_real_escape_string( $q ) );
    }
    
    if( is_array( @$params[ 'status' ] ) )
    {
      $sql .= sprintf( " AND status IN(%s) ", implode( ',', $params[ 'status' ] ) );
    }
    
    if( @$params[ 'id' ] > 0 )
    {
      $sql .= " AND id = {$params[ 'id' ]} ";
    }
    
    if( !empty( $params[ 'order_by' ] ) )
    {
      $sql .= sprintf( " ORDER BY %s %s", $params[ 'order_by' ], $params[ 'order_direction' ] );
    }
    
    if( !empty( $params[ 'index' ] ) )
    {
      $sql .= sprintf( ' LIMIT %d, %d', $params[ 'index' ], $this->options[ 'limit' ] ); 
    }
    else
    {
      $sql .= sprintf( ' LIMIT %d', $this->options[ 'limit' ] ); 
    }
    
    $rows = $wpdb->get_results( $sql );
    
    $this->total = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
    
    return( $rows );
  }
  
  function GetUnit( $id )
  {
    global $wpdb;
    
    $sql = "
      SELECT
        *
      FROM
        {$this->tables[ 'units' ]}
      WHERE
        id = {$id}
      LIMIT
        1
    ";
    
    return( $wpdb->get_results( $sql ) );
  }
  
  function UpdateOptions( $options )
	{
    foreach( $this->options as $k => $v )
    {
      if( array_key_exists( $k, $options ) )
      {
        $this->options[ $k ] = $options[ $k ];
      }
    }

		update_option( $this->id, $this->options );
	}
 
  function UpdateUnit( $fields = array() )
  {
    global $wpdb;
    
    $sql = "
      UPDATE
        {$this->tables[ 'units' ]}
      WHERE
        id = {$id}
      LIMIT
        1
    ";
    
    return( $wpdb->get_results( $sql ) );
  }
  
  function AddChannel( $fields )
  {
  
    $id = intval( @$fields[ 'id' ] );
    
    unset( $fields[ 'id' ] );
    
    foreach( $fields as $k => $v )
    {
      $values[] = sprintf( "%s='%s'", $k, $v );
    }
    
    $values = implode( ',', $values );
    
    if( $id > 0 )
    {
      $sql = "
        UPDATE
          {$this->tables[ 'channel' ]}
        SET
          {$values}
        WHERE
          id = {$id}
      ";
    }
    else
    {
      $sql = "
        INSERT INTO
          {$this->tables[ 'channel' ]}
        SET
          {$values}
      ";
    }

    global $wpdb;
    
    $wpdb->query( $sql );
  }
  
  function AddUnit( $fields = array() )
  {
    $id = intval( $fields[ 'id' ] );
    
    unset( $fields[ 'id' ] );

    foreach( $fields as $k => $v )
    {
      $values[] = sprintf( "%s='%s'", $k, addslashes( $v ) );
    }
    
    $values = implode( ',', $values );
    
    if( $id > 0 )
    {
      $sql = "
        UPDATE
          {$this->tables[ 'units' ]}
        SET
          {$values}
        WHERE
          id = {$id}
      ";
    }
    else
    {
      $sql = "
        INSERT INTO
          {$this->tables[ 'units' ]}
        SET
          {$values}
      ";
    }

    global $wpdb;
    
    $wpdb->query( $sql );
  }
  
  function ToggleUnit( $id )
  {
    global $wpdb;
    
    $sql = "
      UPDATE
        {$this->tables[ 'units' ]}
      SET
        status = IF( status = 1, 0, 1 )
      WHERE
        id = {$id}
      LIMIT
        1
    ";

    $wpdb->query( $sql );

    return( true );
  }
  
  function ToggleChannel( $id )
  {
    global $wpdb;
    
    $sql = "
      UPDATE
        {$this->tables[ 'channel' ]}
      SET
        status = IF( status = 1, 0, 1 )
      WHERE
        id = {$id}
      LIMIT
        1
    ";

    $wpdb->query( $sql );

    return( true );
  }
  
  function DeleteChannel( $id )
  {
    global $wpdb;
    
    $sql = "
      DELETE FROM
        {$this->tables[ 'channel' ]}
      WHERE
        id = {$id}
      LIMIT
        1
    ";
    
    $wpdb->query( $sql );
    
    return( true );   
  }
  
  function DeleteUnit( $id )
  {
    global $wpdb;
    
    $sql = "
      DELETE FROM
        {$this->tables[ 'units' ]}
      WHERE
        id = {$id}
      LIMIT
        1
    ";
    
    $wpdb->query( $sql );
    
    return( true );   
  }

  function Display( $what, $params = array() )
  {
    if( !$this->show )
    {
	    return( '' );
    }

    $default = array( 
      'id' => '',
      'channel' => '', 
      'after' => '', 
      'before' => '',
      'q' => '',
      'index' => 0,
      'limit' => $this->options[ 'limit' ]
    );

    $params = $this->MergeFill( $params, $default );

    switch( $what )
    {
      case 'ads':
      {
        if( empty( $params[ 'id' ] ) && empty( $params[ 'channel' ] ) )
        {
          if( empty( $params[ 'id' ] ) )
          {
            $this->Log( 'unit empty' );
            break;
          }
          
          if( empty( $params[ 'channel' ] ) )
          {
            $this->Log( 'channel empty' );
            break;
          }
        }

        $data = empty( $params[ 'channel' ] ) ? $this->LoadUnit( $params[ 'id' ] ) : $this->LoadChannel( $params[ 'channel' ] );

        if( !$data )
        {
          $this->Log( 'channel "' . $params[ 'channel' ] . '" or id "' . $params[ 'id' ]. '" invalid or inactive' );
        }

        $data = sprintf( "%s%s%s",
          $params[ 'before' ],
          $data,
          $params[ 'after' ]
        );

        break;
      }
      case 'channel':
      {
        $channel = $this->GetChannel( $params );

        if( $channel )
        {
          $data = '';

          foreach( $channel as $single )
          {
            $data .= sprintf( 
              '<tr><td width="30">%s</td><td width="50"><a href="%s/wp-admin/admin.php?page=omniads/admin/channel.php&cmd=toggle_channel&channel[id]=%s">%s</a></td><td width="200">%s</td><td>[<a href="%s/wp-admin/admin.php?page=omniads/admin/channel.php&cmd=edit_channel&channel[id]=%s">bearbeiten</a>] [<a href="%s/wp-admin/admin.php?page=omniads/admin/channel.php&cmd=delete_channel&channel[id]=%s" onclick="javascript:return(confirm(\''.__( 'Are you sure?', $this->id ).'\'));">'.__( 'delete', $this->id ).'</a>]</td></tr>',               
              $single->id, 
              get_bloginfo( 'wpurl' ),
              $single->id,
              intval( $single->status ) == 1 ? __( 'active', $this->id ) : __( 'inactive', $this->id ),
              $single->name,
              get_bloginfo( 'wpurl' ),
              $single->id,
              get_bloginfo( 'wpurl' ),
              $single->id
            );
          }
          break;
        }
        break;
      }
      case 'units':
      {
        $units = $this->GetUnits( $params );

        if( $units )
        {
          $data = '';
          
          foreach( $units as $unit )
          {
            $data .= sprintf( 
              '<tr><td width="30">%s</td><td width="100"><a href="%s/wp-admin/admin.php?page=omniads/admin/units.php&cmd=toggle_unit&unit[id]=%s">%s</a></td><td width="200">%s</td><td>%s</td><td>%s</td><td>[<a href="%s/wp-admin/admin.php?page=omniads/admin/units.php&cmd=edit_unit&unit[id]=%s">'.__('edit', $this->id).'</a>] [<a href="%s/wp-admin/admin.php?page=omniads/admin/units.php&cmd=delete_unit&unit[id]=%s" onclick="javascript:return(confirm(\''.__('Are you sure?', $this->id).'\'));">'.__('delete', $this->id).'</a>]</td></tr>',               
              $unit->id, 
              get_bloginfo( 'wpurl' ),
              $unit->id,
              intval( $unit->status ) == 1 ? __( 'active', $this->id ) : __( 'inactive', $this->id ),
              $unit->channel, 
              $unit->type,
              $unit->title,
              get_bloginfo( 'wpurl' ),
              $unit->id,
              get_bloginfo( 'wpurl' ),
              $unit->id
            );
          }
          break;
        }
      }
      default:
      {
        break;
      }
    }
    
    if( isset( $data ) )
    {
      print( $data );
    }
  }

  function Log( $message )
  {
    if( ( $fh = @fopen( $this->path . '/logs/' . date( 'Ymd' ) . '.log', 'w' ) ) )
    {
      fputs( $fh, sprintf( "%s\t%s\n", $_SERVER[ 'REQUEST_URI' ], $message ) );
      fclose( $fh );
    }
  }
}

add_action( 'plugins_loaded', create_function( '$OmniAds_2kl230', 'global $OmniAds; $OmniAds = new OmniAds();' ) );

function omniads_channel( $channel = '', $before = '', $after = '' )
{
  global $OmniAds;

  if( $OmniAds )
  {
    $OmniAds->Display( 'ads', array( 'channel' => $channel, 'before' => $before, 'after' => $after ) );
  }
}

function omniads_unit( $id = '', $before = '', $after = '' )
{
  global $OmniAds;

  if( $OmniAds )
  {
    $OmniAds->Display( 'ads', array( 'id' => $id, 'before' => $before, 'after' => $after ) );
  }
}

?>