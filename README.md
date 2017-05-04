# Hyperdrive

> The fastest way to load pages in WordPress.

![Hyperdrive for WordPress](https://github.com/wp-id/hyperdrive/blob/master/logo.png)

# Documentation

Hyperdrive docs available online at:
https://clean-louse.cloudvent.net/

# Todo before 1.0.0 release

- [ ] Only dequeue scripts if [browser supports Fetch](http://caniuse.com/#search=fetch) for backwards compatibility with older browsers
- [ ] Integrate localization behaviors [as shown here](https://gist.github.com/jhabdas/64e8380010e43a526fb9c9ee511fad17#file-functions-php-L507).
- [ ] Test with a few different themes and open bugs and needed

# Post 1.0.0 roadmap

- [ ] Integrate User Interface created by [@wedangsu](https://github.com/wedangsusu)
- [ ] Ensure interface gives ability to defer script execution for scripts querying the DOM until after the DOM is fully parsed.
- [ ] Give ability to perform grouping, so non-jQuery scripts can download and execute without waiting for jQuery.
- [ ] Add ability to load icon fonts and [non-critical CSS](https://gist.github.com/scottjehl/87176715419617ae6994) (also possible with Fetch Inject)
- [ ] Build API enabling theme authors greater control

# How it works

Hyperdrive uses a performance optimization technique known as [Fetch Injection](https://hackcabin.com/post/managing-async-dependencies-javascript/), available in [browsers with support](http://caniuse.com/#search=fetch) for the Fetch API. Fetch is a modern replacement for Ajax.

# Contributing

Please open issues when creating PRs and PR against the issue to close it. This helps separate the need (the issue) from the implementation (the pull) and will result in more robust code and better solutions longer-term. It also opens the ability to solve problems using multiple given approaches, so the best approach can be the one that is chosen.

# License

[GPL-3.0](https://opensource.org/licenses/GPL-3.0)
