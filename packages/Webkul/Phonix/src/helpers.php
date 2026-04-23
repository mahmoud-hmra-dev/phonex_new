<?php

declare(strict_types=1);

if (! function_exists('phonix_switch_url')) {
    /**
     * Build a URL for the current path that merges the given query overrides.
     *
     * Use this anywhere the locale/currency switcher would otherwise reach for
     * Laravel's request()->fullUrlWithQuery(). The Shop `Locale` middleware
     * calls `unset($request['locale'])`, which empties the query bag while
     * leaving the raw QUERY_STRING intact — fullUrlWithQuery then appends a
     * second `?`, producing `/phonix?locale=ar?locale=en` on repeat switches.
     *
     * We sidestep that by always starting from url()->current() (no query) and
     * merging the already-sanitised query bag with the caller's overrides.
     */
    function phonix_switch_url(array $overrides): string
    {
        $request = request();

        $params = array_merge($request->query->all(), $overrides);

        return $params
            ? url()->current().'?'.http_build_query($params)
            : url()->current();
    }
}

if (! function_exists('phonix_locale_url')) {
    function phonix_locale_url(string $code): string
    {
        return phonix_switch_url(['locale' => $code]);
    }
}
