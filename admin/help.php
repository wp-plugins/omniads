<div class="wrap">
<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>
<h2><?php _e( 'How to use', $OmniAds->id ); ?></h2>

<p><?php _e( 'This is just a short overview of what OmniAds can do for you. See the the <a href="http://www.naden.de/blog/ads-wordpress-plugin" target="_blank">plugin homepage</a> for a full documentation.', $OmniAds->id ); ?></p>

<p><strong style="color:red;"><?php _e( 'Only activ units are shown!', $OmniAds->id ); ?></strong></p>

<p>
<h4><?php _e( 'Display channel in template', $OmniAds->id ); ?></h4>
<code>&lt;?php if( function_exists( 'omniads_channel' ) ) omniads_channel( 'CHANNEL_NAME' ); ?&gt;</code>
</p>

<p>
<h4><?php _e( 'Display unit in template', $OmniAds->id ); ?></h4>
<code>&lt;?php if( function_exists( 'omniads_unit' ) ) omniads_unit( UNIT_ID ); ?&gt;</code>
</p>

<p>
<h4><?php _e( 'Display channel in post or page content', $OmniAds->id ); ?></h4>
<code>&lt;!--omniads:CHANNE_NAME--&gt;</code>
</p>

<p>
<h4><?php _e( 'Display unit in post or page content', $OmniAds->id ); ?></h4>
<code>&lt;!--omniads:UNIT_ID--&gt;</code>
</p>

<p>
<h4><?php _e( 'Display channel in content after &lt;!--more--&gt; tag', $OmniAds->id ); ?></h4>
<?php _e( 'Associate unit whith channel <em>more</em>.', $OmniAds->id ); ?>
</p>

</div>

<div align="center"><p><?=$OmniAds->name?> <a href="<?php print( $OmniAds->url ); ?>" target="_blank">Plugin Homepage</a></p></div>