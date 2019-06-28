# Outlandish WordPress Starter Project

This is the Outlandish WordPress Starter Project.

It was forked from the Outlandish Website repository.

This readme needs some work.

## Tech overview

The site is a WordPress site using two plugins created by Outlandish which substantially change the way that WordPress theming functions: [Object Oriented WordPress (OOWP)](http://github.com/outlandishideas/oowp) and [Routemaster](http://github.com/outlandishideas/routemaster).

It also uses two plugins which make it easier to model complex data in WordPress - [Advanced Custom Fields](https://www.advancedcustomfields.com/) and [Posts-to-Posts](https://wordpress.org/plugins/posts-to-posts/).

**OOWP** replaces the WordPress loop with more object-oriented concepts. It also provides a set of helper functions to make it easier to register custom post types and connect them together. It modifies the WP_Query and WP_Post classes to return enhanced OOWP `WordpressPost` classes with added functionality.

**Routemaster** alters [WordPress' default routing hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/).

**Advanced Custom Fields Pro** is used to add metadata to various post types, and to provide a way for people to build page layouts using 'row modules'. Values entered via ACF in WordPress are accessed in the templates via the OOWP `$post->metadata()` helper.

**Posts2Posts** provides a way to connect posts together. We use it instead of categories. Connected posts are accessed through the OOWP `$post->connected()` helper.

**Gravity Forms** The plugin is a forked repo under the outlandishideas github account, and included using `composer` 

**Bootstrap 4**

## Project Structure

**Theme** - most of the files you need to edit live inside the theme `/web/app/themes/outlandish/`

**Docker** - all the files relating to the Docker developer environment live in `/docker/`

**Composer** - control the plugins, etc through `/composer.json` - edit or update the plugins in the JSON and run `composer update`

**Env** - variables normally set in `/wp-config.php` should be set in `/.env` instead.

## Theme structure

The theme structure in `/web/app/themes/outlandish` is quite different from a normal WordPress project - reflecting the OOWP/Routemaster features.

Most of the important theme files live in the `/web/app/themes/outlandish/src` directory:

- `/PostTypes/` - contains a class for each of the custom wordpress post-types used by the theme. This includes Person, Project and Blog. Each class extends the OOWP `WordpressPost` class.

- `/Router/` - contains all the logic for routing requests to responses. Most of the configuration of the Router happens in `/Router/OlRouter.php` - be aware that Routemaster provides some default routes such as `/` and `sitemap.xml`

- `/Views/` - contains all the templates for the site. The templates are OOWP2 templates. The views are designed to be nestable so that fragments can be included in multiple page layouts. `Views/Layout.php` end up being 'wrapped around' the other page views.

- `/functions.php` - initialises OOWP through the `registerPostTypes()` method and Routemaster through the `$router->setup()`. Try to minimise the code that goes in here. Put it in the PostTypes or Views folder as appropriate.

## Requirements

- PHP >= 7.1
- Node + NPM
- Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

## Assets

The Outlandish theme uses Bootstrap 4 as its CSS framework. This is built into public assets using Gulp. The Javascript and CSS asset files are then loaded into the layout file using the `wp_enqueue_*` functions in the `OlLayoutResponse.php` file.

## Note on updating minimal.sql

`minimal.sql` should only be updated by using the `mysqldump` command, for consistency.

When updating `minimal.sql`, you must check that the find/replace in `init.js` `'Update WordPress settings'`  is still valid:

    const FIND = "'wp.localhost'"
    const REPLACE = SqlString.escape(project.env.WP_HOME)
    
You will probably need to change `wp.localhost` to whatever your `WP_HOME` env variable is set to (not including the `http://`).

## SCSS coding style

- Use BEM; avoid nested selectors.

- Take a mobile-first approach for media queries. Use Bootstrap's `navbar-breakpoint-up()` mixin and avoid 
`navbar-breakpoint-down()` where possible.

- Aim to use `rem` for sizes and spacing, in accordance with Bootstrap 4.

- Avoid `margin-top` for spacing between elements. Aim to use `margin-bottom` only, as per the Bootstrap 4 approach. As the docs state:

    'Vertical margins can collapse, yielding unexpected results. _More importantly though, a single direction of margin 
    is a simpler mental model._' 
      
    [Source](https://getbootstrap.com/docs/4.2/content/reboot/#approach)

## Browser support

This starter project should work on IE11. We do not aim to support older browsers. If you need to support older browsers, don't use this starter project.