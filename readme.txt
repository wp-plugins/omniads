=== OmniAds ===
Contributors: Naden Badalgogtapeh
Donate link: http://www.naden.de/blog/donate/
Tags: ads, wordpress, ad, adsense, adserver, ad management, blog, money, widget, widgets, sidebar, advertisement, plugin, amazon
Requires at least: 2.0
Tested up to: 2.7
Stable tag: 0.5

Injects ads of any type to your wordpress blog in every place you like.

== Description ==

OmniAds can use very complex syntax to balance where and when ads are displayed and it's widget ready.

OmniAds supports you with channels and two types of units. 

1. With units of type HTML (default) you can deliver text ad code like html or javascript. This unit type supports no finetuning.
1. Using units of type PHP, you can manage ad delivery in every way you like. E.g. just after the first post or after the first and the second or just if the user has a specific referrer ... Please see the plugin homepage or a full documentation of unit type php.

== Installation ==

1. Unpack the zipfile omniads-X.y.zip
1. Upload folder `omniads` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php if( function_exists( 'omniads_channel' ) ) omniads_channel( 'CHANNEL_NAME' ); ?>` or `<?php if( function_exists( 'omniads_unit' ) ) omniads_channel( 'UNIT_ID' ); ?>` in your template or use the sidebar widgets.

== Frequently Asked Questions ==

= Does the plugin supports Google Adsense? =

Sure, no matter what kind of ads, OmniAds delivers them all.

= Is this plugin widget ready? =

Yes, if you theme supports sidebar widgets, OmniAds delivers as many Widgets as you like.

== Short Example ==

* Display channel in template `<?php if( function_exists( 'omniads_channel' ) ) omniads_channel( 'CHANNEL_NAME' ); ?>`
* Display unit in template `<?php if( function_exists( 'omniads_unit' ) ) omniads_unit( 'UNIT_ID' ); ?>`
* Display channel in page or post content <!--omniads:CHANNE_NAME-->
* Display unit in page or post content <!--omniads:UNIT_ID-->
* If there is a unit associated with the channel "more", it'll be displayed after the <!--more--> tag.
* You can place every unit or channel in a sidebar widget

Check out the plugin page at [OmniAds](http://www.naden.de/blog/omniads "OmniAds")

