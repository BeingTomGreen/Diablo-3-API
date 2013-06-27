# Diablo 3 API

A basic PHP wrapper for the integrating the Diablo 3 API into your application.

[Their documentation](http://blizzard.github.com/d3-api-docs/) is, at best poor, much of what is possible from the API has been figured out from the [API Community Forums](http://us.battle.net/d3/en/forum/6916195/), the [WoW Docs](https://github.com/Blizzard/api-wow-docs) & Google. I was going to submit a pull request with some updates but it looks like a few people already have and they have been ignored, and the docs haven't been updated in 10 months.

I may very well create my own Docs repo and stick it on github.io to help others out.

## Supported Methods

### Career Profile (getCareer())

Access account-level career profile information through the Career Profile API.

### Hero Profile (getHero())

Access hero profile information through the Hero Profile API.

### Follower Information (getFollower())

Access detailed Follower information through the Follower Information API.

### Artisan Information (getArtisan())

Access detailed Artisan information through the Follower Information API.

## TODO
- Refactor how I build $urlPath in makeCURLCall() to not use str_replace, will probably have to refactor how I build URLs
- See if the timezone require in the authenticated calls has to be GMT, if not provide means of changing it

## License
This is free and unencumbered software released into the public domain. See UNLICENSE for more details.