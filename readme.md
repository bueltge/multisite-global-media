# Multisite Global Media
_Multisite Global Media_ is a WordPress plugin which shares media across the Multisite network.

## Description
This small plugin adds a new tab to the media modal which gives you the opportunity to share media from one site to all the other sites of the network. The `multisite-global-media.php` file uses the ID of the site that will store the global media. Currently the Site ID is set at `const SITE_ID = 1`. You can set/change this Site ID via filter hook `global_media.site_id` in a custom plugin, like so

 ```php
 add_filter( 'global_media.site_id', function() {
     return 1234;
 } );
 ```
 
To get Global Media to work one has to follow these steps:

1. Decide which blog/site will host the shared media for the network.
2. Add media to the media library for the specific blog/site.
4. You can find the ID of a site by going to your Network WP Admin. In the left hand menu choose "All Sites", and then click on "edit" under the site you need. In the address bar you will see 'site-info.php?id=4' where the last number is the ID. 
 ![Finding the site ID](./assets/screenshot-site-id.png)

A comfortable enhancement in the Multisite context is the plugin [Multisite Enhancement](https://github.com/bueltge/wordpress-multisite-enhancements).


#### Hook for Site ID

You can change the default site ID '1' by creating a small custom plugin. 

1. In /wp-content/ create a new folder 'my-plugin'

2. In /wp-content/my-plugin/ create a new file 'my-plugin.php'

3. Add the following content to 'my-plugin.php' (amend the site id as needed):

 ```php
<?php
/*
 * Plugin Name: My Plugin
 * Plugin URI: https://example.com
 * Description: My first plugin 
 * Author: Jane Doe 
 * Author URI: https://example.com 
 * Version: 1.0
/*	
	

/*
* MULTISITE GLOBAL MEDIA	
* https://github.com/bueltge/multisite-global-media
* change library ID to main site
* 
* ****************************************************
*/

	
add_filter( 'global_media.site_id', function() {
    return 1234;
} );	

```

4. Activate the plugin for the whole network 

### Installation
* Download the plugin as zip, use a clone of the repo or use Composer, see below
* Install the plugin in your environment, recommend as [Must Use plugin](https://codex.wordpress.org/Must_Use_Plugins), also here a small [hint](https://github.com/bueltge/must-use-loader) for an helping solution [Must Use Loader](https://github.com/bueltge/must-use-loader).
* Set the side ID for the Global Media Library, see above the description to change them inside the source or use the hook.
* Active the plugin for the whole network

#### Composer
The plugin is also available as [Composer package](https://packagist.org/packages/bueltge/multisite-global-media).

```bash
composer require bueltge/multisite-global-media
```

### Screenshots
 ![Media Modal](./assets/screenshot-1.png)

 ![Usage in Featured Image](./assets/screenshot-2.png)

## Other Notes

### Crafted by [Inpsyde](https://inpsyde.com) &middot; Engineering the web since 2006.

### Bugs, technical hints or contribute
Please give me feedback, contribute and file technical bugs on this
[GitHub Repo](https://github.com/bueltge/Multisite-Global-Media/issues), use Issues.

### License
Good news, this plugin is free for everyone! Since it's released under the MIT, you can use it free of charge on your personal or commercial blog.

### Contact & Feedback
The plugin is designed and developed by team members from the [Inpsyde](https://inpsyde.com/) crew. Special thanks and praise to Dominik Schilling for his quick help.

Please let me know if you like the plugin or you hate it or whatever ...

Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there.
So please be patient with me and let me know of typos or grammatical errors. Thank you!
