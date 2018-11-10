# Multisite Global Media
_Multisite Global Media_ is a WordPress plugin which shares media across the Multisite network.

## Description
This small plugin adds a new tab to the media library which allows you to share media from one site to all the other sites of the network. By default the Site ID is set to '1'. You can set/change this Site ID via the filter hook `global_media.site_id` which is run in a custom plugin like so

 ```php
 add_filter( 'global_media.site_id', function() {
     return 1234;
 } );
 ```

To get Global Media to work please follow these steps:

1. Decide which blog/site will host the shared media for the network.
2. Add media to the media library for the chosen blog/site.
3. Find the Site ID of your chosen site by going to your Network WP Admin. In the left hand menu choose "All Sites", and then click on "edit" under the site you need. In the address bar you will see `site-info.php?id=4` where the last number is the ID.
![Finding the site ID](./assets/images/screenshot-site-id.png)
4. If the Site ID of your chosen site is '1', then you don't need to maky any changes. If it's a different ID number, then please read the section below about modifying the Site ID via hook and a custom plugin.

Note: A useful enhancement in the Multisite context is the plugin [Multisite Enhancement](https://github.com/bueltge/wordpress-multisite-enhancements). Its helps also to identify the site and get his site ID.


## Set your Site ID for the Global Mediathek
If you need to change the default Site ID '1' to another value, then you can do so by creating a small custom plugin.

1. In `/wp-content/mu-plugins/` create a new folder `mgm-set-my-site-id`.
2. `In /wp-content/mu-plugins/mgm-set-my-site-id/` create a new file `mgm-set-my-site-id.php`.
3. Add the following content to 'my-plugin.php'. Change the return value to your chosen Site ID.

 ```php
<?php
/**
 * Plugin Name: MGM Site ID
 * Plugin URI:  https://github.com/bueltge/multisite-global-media/
 * Description: Set my Multisite Global Media site in the network.
 * Version:     1.0.0
 * Network:     true
 */

add_filter( 'global_media.site_id', function() {
    return 1234;
} );

```

4. Activation is not necassary if you store this plugin inside the [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins) directory /wp-content/mu-plugins/.

## Installation
### Manual
* Download the plugin as zip, use a clone of the repo or use Composer, see below
* Install the plugin in your environment, recommend as [Must Use plugin](https://codex.wordpress.org/Must_Use_Plugins). See here for a quick [hint](https://github.com/bueltge/must-use-loader) for a helping solution [Must Use Loader](https://github.com/bueltge/must-use-loader).
* Set the Site ID for the Global Media Library, see above the description to change the ID with a hook in a custom plugin.
* Active the plugin for the whole network if you don't store it as Must Use Plugin.

### Composer
The plugin is also available as [Composer package](https://packagist.org/packages/bueltge/multisite-global-media).

```bash
composer require bueltge/multisite-global-media
```

## Screenshots
 ![Media Modal](./assets/images/screenshot-1.png)

 ![Usage in Featured Image](./assets/images/screenshot-2.png)

## Other Notes

### Crafted by [Inpsyde](https://inpsyde.com) &middot; Engineering the web since 2006.

### Bugs, technical hints or contribute
Please give me feedback, contribute and file technical bugs on this
[GitHub Repo](https://github.com/bueltge/Multisite-Global-Media/issues), use Issues.

### License
Good news, this plugin is free for everyone! Since it's released under the MIT, you can use it free of charge on your personal or commercial blog.

### Contact & Feedback
The plugin is designed and developed by team members from the [Inpsyde](https://inpsyde.com/) crew. Special thanks and praise to Dominik Schilling and Guido Scialfa for his help and engagement.

Please let me know if you like the plugin or you hate it or whatever ...

Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there.
So please be patient with me and let me know of typos or grammatical errors. Thank you!
