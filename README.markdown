# AnobiiWidget
Contributors: [Luciano Mammino](http://oryzone.com)

Tags: anobii, widget, books

Requires at least: 3

Tested up to: 3.1

Stable tag: -

A wordpress widget for your [Anobii](http://anobii.com) account

## AnobiiWidget for Wordpress

[aNobii](http://www.anobii.com) is a social networking service for book lovers. It was set up in 2006 by a private company owned by Greg Sung and based in Hong Kong.
The service allows individuals to catalogue their books and rate, review and discuss them with other readers. The service is available via the aNobii website and iPhone and Android Apps. The Apps allow individuals to barcode scan books and read both community and expert reviews.

![Widget Screenshoot](http://img593.imageshack.us/img593/3451/schermata20110306a03343.png "Widget Screenshoot")

This is a Wordpress plugin that adds a widget to display your books from your [Anobii](http://www.anobii.com) account.
Currently tested only with Wordpress 3.0 and 3.1. It is still in beta, so don't hesitate to commit bugs or suggest improvements.
Current version: 0.0.1

## Features

  * Add a link to your profile around the widget title
  * Select how many books to show (from 1 to 5)
  * Select wheter to show book covers (always, never, only on the first book)
  * Uses AJAX to avoid slowing down the whole website
  * Choice books with particoular status ( Finished, Not Started, Reading, Unfinished, Reference, Abandoned)
  * Configurable cache (via transient)

## License

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

## Installation

Requirements:

  * PHP - cURL enabled

Steps:

  1. Copy the files `anobiiBook.php` and `anobiiWidget.php` to your `wp-content/plugins/` directory (you can put them in a subfolder if you prefer)
  2. Go to your control panel and activate the plugin
  3. Go in your widget panel and place the anobii widget wherever you want
  4. Configure the widget
  5. Well done!
  6. (Optional) Stylize your widget with some CSS. You can find inspiration in the `sampleStyle.css` file.

![Available options](http://img850.imageshack.us/img850/3268/schermata20110306a11122.png "Available options")


### Notes
Proudly sponsored by [ORYZONE](http://oryzone.com)