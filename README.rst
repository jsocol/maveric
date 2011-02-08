=======
Maveric
=======

Maveric is an MVC framework for PHP that is strongly influenced by Rails.

I pulled Maveric out of an app that we built on its basic layout. I continued
to work on it later because it was fun and a good learning experience.

I actually have used Maveric in `a production environment
<http://todaysmeet.com>`_, but I would not recommend you do the same. It is
untested, surely far from performant, and some big design decisions were
probably dead wrong. Use a popular, vetted framework.

I publish this here for posterity and in the spirit of Open Source.


TODO
====

Were I ever to work on Maveric again, here are the key areas, if I recall
correctly, that need attention:

* The ORM.

 * Caching support.

 * I'd love if two references to the same object in the same process would use
   the same data, but it's not crucial.

 * Something analogous to Django's ``QuerySet``.

 * Take advantage of late static binding in PHP 5.3 to clean up some
   badly-constructed APIs from 5.2.

 * SQL generation.

  * Ideally I would use the decently-tested SQL generator from `Phake
    <https://github.com/jsocol/phake>`_.

* Routing.

 * Routing is based on surely quite-slow regular expressions. On the other
   hand, Django takes the same approach, so it may be solvable with some simple
   refactoring.

 * I had a plan for HTTP-method-based routes that would be nice to finish.

* Laziness.

 * Database connections shouldn't be required or instantiated immediately.

* Templating.

 * Currently uses pure PHP. I'm torn about this, as it's kind of like RHTML but
   makes safe defaults difficult.

 * Something like Dwoo or PHPTAL. I remember reading about something that was
   generally like Jinja, that'd be great.
