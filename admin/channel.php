<?php

$q                = isset( $_REQUEST[ 'q' ] ) ? urldecode( $_REQUEST[ 'q' ] ) : '';
$order_by         = isset( $_REQUEST[ 'order_by' ] ) ? $_REQUEST[ 'order_by' ] : 'id';
$order_direction  = isset( $_REQUEST[ 'order_direction' ] ) ? $_REQUEST[ 'order_direction' ] : 'DESC';
$index            = isset( $_REQUEST[ 'index' ] ) ? intval( $_REQUEST[ 'index' ] ) : 0;

switch( @$_REQUEST[ 'cmd' ] )
{
  case 'save_channel':
  {
    $OmniAds->AddChannel( $_REQUEST[ 'channel' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Channel saved!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
  case 'edit_channel':
  {
    $channel = $OmniAds->GetChannel( array( 'id' => $_REQUEST[ 'channel' ][ 'id' ] ) );

    $channel = $channel[ 0 ];
    break;
  }
  case 'delete_channel':
  {
    $OmniAds->DeleteChannel( $_REQUEST[ 'channel' ][ 'id' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Channel deleted!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
  case 'toggle_channel':
  {
    $OmniAds->ToggleChannel( $_REQUEST[ 'channel' ][ 'id' ] );
    echo '<div id="message" class="updated fade"><p><strong>'. __( 'Status changed!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
}

$fields = array(
  /// name => max length, title, faq, required, field type, value type, default
  'id'      => array(   5, '',           true, 'hidden', 'integer', @$channel->id ),
  'name'    => array( 255, __( 'Name', $OmniAds->id ), true, 'text',   'text', @$channel->name ),
  'status'  => array( -1, __( 'Active', $OmniAds->id ), true, 'yesnoradio', 'text', @$channel->status )
);

?>
<!-- options -->
<div class="wrap">
<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>
<h2>Channel</h2>
<form name="" action="<?php print( get_bloginfo( 'wpurl' ) ); ?>/wp-admin/admin.php?page=omniads/admin/channel.php" method="post">
<input type="hidden" name="cmd" value="save_channel" />

<table class="form-table">
<?php

foreach( $fields as $k => $v )
{
  if( array_key_exists( $k, $fields ) )
  {
    if( $v[ 3 ] == 'hidden' )
    {
      print( $OmniAds->GetFormfield( $v[ 0 ], 'channel', $k, $v[ 3 ], @$channel->{$k}, $v[ 5 ] ) );
    }
    else
    {
    printf(
      '<tr valign="top"><th scope="row">%s</th><td>%s</td></tr>', 
      $v[ 1 ],
      $OmniAds->GetFormfield( $v[ 0 ], 'channel', $k, $v[ 3 ], @$channel->{$k}, $v[ 5 ] )
    );
    }
  }
}
?>
</table>

<p class="submit" align="right"><input type="submit" name="submit" value="<?php _e( 'save', $OmniAds->id ); ?>" /></p>

</form>

<h2>&Uuml;bersicht</h2>
<div class="tablenav">
<div class="alignleft">
<form method="post" action="">
<input type="text" name="q" value="<?=$q?>" />
<input type="submit" name="cmd" value="<?php _e( 'show', $OmniAds->id ); ?>" class="button-secondary" />
</form>
</div>
</div>
<br class="clear" />
<table class="widefat omniads">
<thead>
<tr>
  <?php 
  
  $OmniAds->GetTableHeader( 
    $order_by, 
    $order_direction, 
    $q, 
    $index, 
    'channel', 
    array( 
      'id' => '#', 
      'status' => __( 'Status', $OmniAds->id ), 
      'name' => __( 'Title', $OmniAds->id ) 
    )
  ); 

?><th scope="col">&#160;</th>
</tr>
</thead>
<tbody>
<?php $OmniAds->Display( 'channel', array( 'q' => $q, 'order_by' => $order_by, 'order_direction' => $order_direction, 'index' => $index ) ); ?>
</tbody>
<tfoot>
<tr>
  <td colspan="6"><?php $OmniAds->GetPaging( $index, ' ... ', 'channel', $order_by, $order_direction, $q, sprintf( '<strong>%s</strong> ', __( 'Pages' ) ), '', '', ' &laquo; ', ' ', ' ', ' &raquo; ', '', '', '', ' [ ', ' ] ' ); ?></td>
</tr>
</tfoot>
</table>
</div>
<!-- // options -->

<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>