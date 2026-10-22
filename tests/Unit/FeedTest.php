<?php

use Simpl\FeedDate;
use Simpl\FeedItem;
use Simpl\UniversalFeedCreator;

function buildFeed($format) {
    $feed = new UniversalFeedCreator;
    $feed->title = 'Feed';
    $feed->link = 'http://example.com';
    $feed->description = 'Feed description';

    $item = new FeedItem;
    $item->title = 'Item';
    $item->link = 'http://example.com/1';
    $item->description = 'Item description';
    $item->date = '2026-10-07T10:00:00-04:00';
    $feed->addItem($item);

    return $feed->createFeed($format);
}

it('sets the rss version for RSS 2.0 feeds', function () {
    expect(buildFeed('RSS2.0'))->toContain('<rss version="2.0"');
});

it('sets the rss version for RSS 0.91 feeds', function () {
    expect(buildFeed('RSS0.91'))->toContain('<rss version="0.91"');
});

it('uses the item date rather than the current time', function () {
    expect(buildFeed('RSS2.0'))->toContain('<pubDate>Wed, 07 Oct 2026');
});

it('formats dates with the offset in effect on that date', function () {
    $timezone = date_default_timezone_get();
    date_default_timezone_set('America/Detroit');

    $summer = (new FeedDate('2026-10-28T16:00:00-0400'))->rfc822();
    $winter = (new FeedDate('2026-11-12T09:00:00-0500'))->rfc822();
    $utc = (new FeedDate('2026-11-12T14:00:00Z'))->iso8601();

    date_default_timezone_set($timezone);

    expect($summer)->toBe('Wed, 28 Oct 2026 16:00:00 -0400');
    expect($winter)->toBe('Thu, 12 Nov 2026 09:00:00 -0500');
    expect($utc)->toBe('2026-11-12T09:00:00-05:00');
});

it('parses rfc 822 dates', function () {
    $timezone = date_default_timezone_get();
    date_default_timezone_set('America/Detroit');

    $date = (new FeedDate('Thu, 12 Nov 2026 09:00:00 -0500'))->rfc822();

    date_default_timezone_set($timezone);

    expect($date)->toBe('Thu, 12 Nov 2026 09:00:00 -0500');
});

it('includes the item description', function () {
    expect(buildFeed('RSS2.0'))->toContain('<description>Item description</description>');
});

it('builds a feed without warnings', function () {
    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    buildFeed('RSS2.0');

    restore_error_handler();

    expect($errors)->toBe([]);
});
