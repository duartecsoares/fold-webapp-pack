## Föld's WebApp Pack ##

#### About

Pack with tools for building web applications.

#### Dependecies

* Backbone
	* Jquery
	* Underscore
* Requirejs
	* Text
* Fold's Starterkit

#### How to install

in progress

#### How To Create a "Page"

First of all, you need a template for your "page" so you need to create a html file with the elements you need. 
<screenshot>
Then you have to create FoldView constructor and link the previously template created with that FoldView constructor.
<screenshot>
Each instance of FoldView's need unique id's so they can be destroyed property once they are not needed anymore.
<screenshot>
As soon as you have the template and the view linked, you just need to create an instance of a FoldController and set the created FoldView as the main view. There's a set of automations made behind the scene to ease the manipulation of views by the controllers, that manages events for example.
<screenshot>
Having these things done, you just have to link a route to the FoldController instance that you just created at the App Controller.
<screenshot>

