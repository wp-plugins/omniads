<?php

$q                = isset( $_REQUEST[ 'q' ] ) ? urldecode( $_REQUEST[ 'q' ] ) : '';
$order_by         = isset( $_REQUEST[ 'order_by' ] ) ? $_REQUEST[ 'order_by' ] : 'id';
$order_direction  = isset( $_REQUEST[ 'order_direction' ] ) ? $_REQUEST[ 'order_direction' ] : 'DESC';
$index            = isset( $_REQUEST[ 'index' ] ) ? intval( $_REQUEST[ 'index' ] ) : 0;

switch( @$_REQUEST[ 'cmd' ] )
{
  case 'save_unit':
  {
    $OmniAds->AddUnit( $_REQUEST[ 'unit' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Unit saved!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
  case 'toggle_unit':
  {
    $unit = $OmniAds->ToggleUnit( $_REQUEST[ 'unit' ][ 'id' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Status changed!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }

  case 'edit_unit':
  {
    $unit = $OmniAds->GetUnit( $_REQUEST[ 'unit' ][ 'id' ] );
    $unit = $unit[ 0 ];
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Unit loaded!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
  case 'delete_unit':
  {
    $OmniAds->DeleteUnit( $_REQUEST[ 'unit' ][ 'id' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Unit deleted!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
}

$fields = array(
  /// name => max length, title, faq, required, field type, value type, default
  'id'        => array(   5, '',           true, 'hidden', 'integer', @$unit->id ),
  'title'     => array( 255, __( 'Name', $OmniAds->id ), true, 'text', 'text', '' ),
  'status'    => array( -1, __( 'Active', $OmniAds->id ), true, 'yesnoradio', 'text', '' ),
  'type'      => array( -1, __( 'Type', $OmniAds->id ), true, 'select', 'text', @$unit->type ),
  'channel'   => array( -1, __( 'Channel', $OmniAds->id ), true, 'select', 'text', @$unit->channel ),
  'content'   => array( -1, __( 'Code', $OmniAds->id ), true, 'textarea', 'text', ''  )
);

?>
<!-- options -->
<div class="wrap">
<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>
<h2>Units</h2>
<form name="" action="<?php print( get_bloginfo( 'wpurl' ) ); ?>/wp-admin/admin.php?page=omniads/admin/units.php" method="post">
<input type="hidden" name="cmd" value="save_unit" />
<table class="form-table">
<?php

foreach( $fields as $k => $v )
{
  if( array_key_exists( $k, $fields ) )
  {
    switch( $k )
    {
      case 'channel':
      {
        $value = $OmniAds->channel;
        break;
      }
      case 'type':
      {
        $value = $OmniAds->types;
        break;
      }
      default:
      {
        $value = stripslashes( @$unit->{$k} );
        break;
      }
    }

    if( $v[ 3 ] == 'hidden' )
    {
      print( $OmniAds->GetFormfield( $v[ 0 ], 'unit', $k, $v[ 3 ], $value, $v[ 5 ] ) );
    }
    else
    {
      printf(
        '<tr valign="top"><th scope="row">%s</th><td>%s</td></tr>', 
        $v[ 1 ], 
        $OmniAds->GetFormfield( $v[ 0 ], 'unit', $k, $v[ 3 ], $value, $v[ 5 ] )
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
    'units', 
    array( 
      'id' => '#', 
      'status' => __( 'Status', $OmniAds->id ), 
      'channel' => __( 'Channel', $OmniAds->id ),
	    'type' => __( 'Type', $OmniAds->id ),
      'title' => __( 'Title', $OmniAds->id ) 
    )
  ); 

?>
	<th scope="col">&#160;</th>
</tr>
</thead>
<tbody>
<?php $OmniAds->Display( 'units', array( 'order_by' => $order_by, 'order_direction' => $order_direction, 'q' => $q, 'index' => $index ) ); ?>
</tbody>
<tfoot>
<tr>
  <td colspan="6"><?php $OmniAds->GetPaging( $index, ' ... ', 'units', $order_by, $order_direction, $q, sprintf( '<strong>%s</strong> ', __( 'Pages' ) ), '', '', '&laquo;', ' ', ' ', '&raquo;', '', '', '', ' [ ', ' ] ' ); ?></td>
</tr>
</tfoot>
</table>
</div>
<!-- // options -->

<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>