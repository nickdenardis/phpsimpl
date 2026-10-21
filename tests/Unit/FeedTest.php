<?php

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
