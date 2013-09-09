## Diablo 3 API

A basic PHP wrapper for the integrating the Diablo 3 API into your application.

The Diablo 3 API is fairly lacking, especially when compared to the WoW API. [The Blizzard Docs](http://blizzard.github.io/d3-api-docs/) are pretty sparse and much of what is possible within the API has been figured out from the [API Community Forums](http://us.battle.net/d3/en/forum/6916195/), the [WoW Docs](https://github.com/Blizzard/api-wow-docs) & Google.

While the class does currently support authenticated calls, if you enter a set of bogus keys it still returns the correct data. I assume this is just treating the request as if it wasn't authenticated and returning the data anyway, but I have emailed Blizzard for confirmation and will update when I hear back from them. As of 9/9/13, still no response!

### Currently Supported Methods

#### Career Profile (getCareer())

Access account-level career profile information through the Career Profile API.

#### Hero Profile (getHero())

Access hero profile information through the Hero Profile API.

#### Follower Information (getFollower())

Access detailed Follower information through the Follower Information API.

#### Artisan Information (getArtisan())

Access detailed Artisan information through the Follower Information API.

#### Paper Doll Image (getPaperDoll())

Returns the URL to the Paper Doll image, for example a [male Monk](http://eu.battle.net/d3/static/images/profile/hero/paperdoll/monk-male.jpg).

### API Limits (as of 6/12/2013 via [XjSv](https://twitter.com/Armando_Tresova))

Unauthenticated API Limit: 30,000

Authenticated API Limit: 300,000

### TODO
- Re-factor how I build $urlPath in makeCURLCall() to not use str_replace, will probably have to refactor how I build URLs
- See if the timezone require in the authenticated calls has to be GMT, if not provide means of changing it
- Add If-Modified-Since headers to save API tokens
- See why authenticated calls don't return an error with bogus keys, is there an issue with my code, or does the API treat invalid keys as an un-authed request?
- Wrap in nice composer package, unit test all of that good shit

### License

This is open-sourced software licensed under the [MIT license](http://beingtomgreen.mit-license.org/).