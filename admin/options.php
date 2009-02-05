<?php

$fields = array(
  /// $max = -1, $prefix = '', $name, $type, $value, $default = '', $faq = ''
  'widgets'   => array( -1, __( 'Widgets', $OmniAds->id ), true, 'text', 'text', '', __( 'Number of wanted sidebar widgets.', $OmniAds->id ) ),
  'limit'   => array( -1, __( 'Limit', $OmniAds->id ), true, 'text', 'text', '', __( 'Number of units/channel to display in admin panel per page.', $OmniAds->id ) ),
  'exclude'   => array( -1, __( 'Exclude', $OmniAds->id ), true, 'textarea', 'text', '', __( 'No ads are displayed, if string matches $_SERVER[ \'REQUEST_URI\' ].', $OmniAds->id ) )
);

switch( @$_REQUEST[ 'cmd' ] )
{
  case 'save_options':
  {
    $OmniAds->UpdateOptions( $_REQUEST[ 'options' ] );
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $OmniAds->id ) . '</strong></p></div>';
    break;
  }
}

?>
<div class="wrap">
<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>
<h2><?php _e( 'Options', $OmniAds->id ) ?></h2>
<form name="" action="<?php print( get_bloginfo( 'wpurl' ) ); ?>/wp-admin/admin.php?page=omniads/admin/options.php" method="post">
<input type="hidden" name="cmd" value="save_options" />
<table class="widefat">
<?php

foreach( $fields as $k => $v )
{

  if( array_key_exists( $k, $fields ) )
  {
    $value = stripslashes( $OmniAds->options[ $k ] );

    printf(
      '<tr valign="top"><th scope="row">%s</th><td>%s</td></tr>', 
      $v[ 1 ], 
      $OmniAds->GetFormfield( $v[ 0 ], 'options', $k, $v[ 3 ], $value, $v[ 5 ], $v[ 6 ] )
    );
  }
}
?>
</table>

<p class="submit" align="right"><input type="submit" name="submit" value="<?php _e( 'save', $OmniAds->id ); ?>" /></p>

</form>

</div>

<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>