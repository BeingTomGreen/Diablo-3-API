# Diablo 3 API

A very basic wrapper for the [Diablo 3 API](http://blizzard.github.com/d3-api-docs/).

Their documentation is poor at best, much of what is possible from the API has been figured out from the [API Community Forums](http://us.battle.net/d3/en/forum/6916195/) & Google. I was going to submit a pull request with some updated Docs but it looks like a few people already have (although they are out of date again now) and they have been ignored, with no changes in 9 months!

I may very well create my own Docs repo and stick it on github.io to help others out.

## TODO
Support authenticated API Calls - Same syntax as the WOW API, need to manually email support and get an application key.
~~Pass BattleTag to the __construct method?~~
Since we are limited to 10k or 50k (if authenticated) calls, it may make sense to cache the results.

## License
This is free and unencumbered software released into the public domain. See UNLICENSE for more details.