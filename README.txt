=== AnobiiWidget ===
Contributors: Luciano Mammino (http://oryzone.com)
Tags: anobii, widget, books
Requires at least: 3
Tested up to: 3.1
Stable tag: trunk

A wordpress widget for your aNobii.com account


== Description ==

[aNobii](http://www.anobii.com) is a social networking service for book lovers. It was set up in 2006 by a private company owned by Greg Sung and based in Hong Kong.
The service allows individuals to catalogue their books and rate, review and discuss them with other readers. The service is available via the aNobii website and iPhone and Android Apps. The Apps allow individuals to barcode scan books and read both community and expert reviews.

This is a Wordpress plugin that adds a widget to display your books from your [Anobii](http://www.anobii.com) account.
Currently tested only with Wordpress 3.0 and 3.1. It is still in beta, so don't hesitate to commit bugs or suggest improvements.
Current version: 0.0.1

= Features =

  * Add a link to your profile around the widget title
  * Select how many books to show (from 1 to 5)
  * Select wheter to show book covers (always, never, only on the first book)
  * Uses AJAX to avoid slowing down the whole website
  * Choice books with particoular status ( Finished, Not Started, Reading, Unfinished, Reference, Abandoned)
  * Configurable cache (via transient)
  * Multilanguage (Currently available in English and Italian)

= NOTICE =

I'm looking for translators. If you're interested please [contact me](mailto:lmammino@oryzone.com) or just fork the repo, add the translations and submit a pull request.

Thankyou guys!


== Screenshots ==

![Widget Screenshoot](http://img593.imageshack.us/img593/3451/schermata20110306a03343.png "Widget Screenshoot")
![Available options](http://img850.imageshack.us/img850/3268/schermata20110306a11122.png "Available options")


== Installation ==

=Requirements=

  * PHP - cURL enabled

=Steps=

  1. Copy the files `anobiiBook.php` and `anobiiWidget.php` to your `wp-content/plugins/` directory (you can put them in a subfolder if you prefer)
  2. Go to your control panel and activate the plugin
  3. Go in your widget panel and place the anobii widget wherever you want
  4. Configure the widget
  5. Well done!
  6. (Optional) Stylize your widget with some CSS. You can find inspiration in the `sampleStyle.css` file.


== Changelog ==

= 0.0.1 =
  * First Release



== Frequently Asked Questions ==

= I love this plugin! How can I show the developer how much I appreciate his work? =
Simply keep using the plugin and if you find some bug or if you want to suggest a new feature just [submit an issue](https://github.com/lmammino/anobiiwidget/issues). If you want you can even translate the plugin in your language, so, in this case, you can [contact me](mailto:lmammino@oryzone.com) or just [fork](https://github.com/lmammino/anobiiwidget#fork_box) the repository on github, add your translations and submit a pull request.

= I'd like to contribute to the development, what I can do? =
You can access the code on [github](https://github.com/lmammino/anobiiwidget), [fork the repository](https://github.com/lmammino/anobiiwidget#fork_box) and of course you can submit a pull request.


== Notes ==
Proudly sponsored by [ORYZONE](http://oryzone.com)