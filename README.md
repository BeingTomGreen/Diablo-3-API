# Diablo 3 API

A basic PHP wrapper for the [Diablo 3 API](http://blizzard.github.com/d3-api-docs/).

Their documentation is poor at best, much of what is possible from the API has been figured out from the [API Community Forums](http://us.battle.net/d3/en/forum/6916195/), the [WoW Docs](https://github.com/Blizzard/api-wow-docs) & Google. I was going to submit a pull request with some updated Docs but it looks like a few people already have (although they are out of date again now) and they have been ignored, with no changes in 9 months!

I may very well create my own Docs repo and stick it on github.io to help others out.

## TODO
- Refactor how I build $urlPath in makeCURLCall() to not use str_replace, will probably have to refactor how I build URLs
- See if the timezone require in the authenticated calls has to be GMT, if not provide means of changing it

## License
This is free and unencumbered software released into the public domain. See UNLICENSE for more details.