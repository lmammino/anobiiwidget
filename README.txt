=== AnobiiWidget ===
Contributors: loige
Tags: anobii, widget, widgets, books, social
Requires at least: 3
Tested up to: 3.1
Stable tag: trunk

A wordpress widget for your aNobii.com account


== Description ==

[aNobii](http://www.anobii.com) is a social networking service for book lovers. It was set up in 2006 by a private company owned by Greg Sung and based in Hong Kong.
The service allows individuals to catalogue their books and rate, review and discuss them with other readers. The service is available via the aNobii website and iPhone and Android Apps. The Apps allow individuals to barcode scan books and read both community and expert reviews.

This is a Wordpress plugin that adds a widget to display your books from your [Anobii](http://www.anobii.com) account.
Currently tested only with Wordpress 3.0 and 3.1. It is still in beta, so don't hesitate to commit bugs or suggest improvements.
Current version: 0.0.9

<h2>Features</h2>

  * Add a link to your profile around the widget title
  * Select how many books to show (from 1 to 5)
  * Select wheter to show book covers (always, never, only on the first book)
  * Uses AJAX to avoid slowing down the whole website
  * Choice books with particoular status ( Finished, Not Started, Reading, Unfinished, Reference, Abandoned)
  * Configurable cache (via transient)
  * Multilanguage

<h2>Translations</h2>

  * **English** - by [Luciano Mammino](http://oryzone.com)
  * **Italian** - by [Luciano Mammino](http://oryzone.com)
  * **Indonesian** - by Indonesia **"Tornado kick"** Pujiasih
  * **Mexican** - by [Aìda](https://twitter.com/BBoogieBangBang)
  * **Croatian** - by Dominik Strikić
  * **Norsk** - by Ingrid S.B.
  * **Portughese** - by Dinis Correia
  * **Spanish** - by [Santiago Pascual](http://www.alicante-consulting.net)
  * **Slovak** - by [Branco](http://webhostinggeeks.com/user-reviews/)
  * **Traditional Chinese** - by Chungyu
  * **Ukrainian** - by [Michael Yunat of getvoip.com](http://getvoip.com)

<h2>NOTICE</h2>

I'm looking for translators. If you're interested please [contact me](mailto:lmammino@oryzone.com) or just fork the repo, add the translations and submit a pull request.

Thankyou guys!

<h2>Notes</h2>
Proudly sponsored by [ORYZONE](http://oryzone.com)


== Screenshots ==

1. **The widget** - A demonstration of what the widget looks like with a simple CSS style.
2. **Available options** - All the options available for the plugin configuration


== Installation ==

<h2>Requirements</h2>

  * PHP - cURL enabled

<h2>Steps</h2>

  1. Copy the files `anobiiBook.php` and `anobiiWidget.php` to your `wp-content/plugins/` directory (you can put them in a subfolder if you prefer)
  2. Go to your control panel and activate the plugin
  3. Go in your widget panel and place the anobii widget wherever you want
  4. Configure the widget
  5. Well done!
  6. (Optional) Copy also the `languages` folder if you need to internationalize the plugin
  7. (Optional) Stylize your widget with some CSS. You can find inspiration in the `sampleStyle.css` file.


== Changelog ==

= 0.0.9 =
  * Added Ukrainian translation
  * Added Traditional Chinese translation

= 0.0.8 =
  * Added Slovak translation

= 0.0.7 =
  * Added Spanish translation

= 0.0.6 =
  * Added Portughese translation
  * Provided a simple workaround for some anobii api issues

= 0.0.5 =
  * Added uninstall hook
  * Fixed plugin promotion bug

= 0.0.4 =
  * Detects possible issues with aNobii API and shows a "currently unavailable" message
  * Automatically adds jQuery if not included in your pages
  * Added option to sponsorize the plugin (automatically set to On)

= 0.0.3 =
  * Fixed an AJAX issue with non logged users
  * Cache is now cleared on plugin update

= 0.0.2 =
  * Now it checks if some book has no cover and provides an alternative default cover to avoid blank images to appear.
  * Added some more translations (Indonesia, Mexican and Croatian)

= 0.0.1 =
  * First Release.


== Frequently Asked Questions ==

= I love this plugin! How can I show the developer how much I appreciate his work? =
Simply keep using the plugin and if you find some bug or if you want to suggest a new feature just [submit an issue](https://github.com/lmammino/anobiiwidget/issues). If you want you can even translate the plugin in your language, so, in this case, you can [contact me](mailto:lmammino@oryzone.com) or just [fork](https://github.com/lmammino/anobiiwidget#fork_box) the repository on github, add your translations and submit a pull request.

= I'd like to contribute to the development, what I can do? =
You can access the code on [github](https://github.com/lmammino/anobiiwidget), [fork the repository](https://github.com/lmammino/anobiiwidget#fork_box) and of course you can submit a pull request.

= Who uses this plugin?! =
I'd like to make a public list of people/sites who uses this plugin, so [send me](mailto:lmammino@oryzone.com) the link of your site if you use it and I'll create a list somewhere!