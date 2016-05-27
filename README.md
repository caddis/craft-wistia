![Craft Wistia](https://www.caddis.co/internal/repo/craft-wistia.svg?v1)

Wistia is an incredible video marketing platform we rely on every day. This Craft CMS plugin includes a configurable fieldtype to easily relate videos to entries and flexible tags to get video data and output embed code.

## Templating

To output videos on the front-end, use your fieldtype's handle and append `getVideos`. You can apply a variety of parameters described in the next section.

```twig
{% set params = {
	width: 720,
	height: 420,
	autoPlay: true
} %}

{% for video in entry.videos.getVideos(params) %}
	<h2>{{ video.name }}</h2>
	{{ video.embed }}
{% endfor %}
```

## Parameters

```twig
{
	autoPlay: false,
	controlsVisibleOnLoad: false,
	fullscreenButton: true,
	height: '360px',
	limit: 150,
	offset: 0,
	playbar: true,
	playButton: true,
	playerColor: '#7B796A',
	responsive: true,
	smallPlayButton: true,
	volumeControl: true,
	width: '640px',
}
```

#### limit
Limits results. Default: 150.

#### offset
Skips over the specified results. For example, if the tag returns 5 videos and you set an offset of 2, the first 2 results are skipped and results 3, 4, and 5 are shown.

#### width
Width of the embed. Default: "640px".

#### height
Height of the embed. Default: "360px".

#### autoPlay
Determines if the video embed plays on load. Default: false.

#### controlsVisibleOnLoad
Determines if the video embed controls are visible on load. Default: true.

#### fullscreenButton
Determines if the full screen button is visible on load. Default: true.

#### playbar
Determines if the play bar is visible on load. Default: true.

#### playButton
Determines if the play button is visible on load. Default: true.

#### volumeControl
Determines if the volume control is visible on load. Default: true.

#### smallPlayButton
Determines if the small play button is visible on load. Default: true.

#### playerColor
Enter a valid HEX color to change the player color.

#### responsive
Determines if the video embed responds to the parent container width. Default: true.

## Tags

```twig
{{ video.embed }}
{{ video.preview.getUrl }}
{{ video.id }}
{{ video.name }}
{{ video.type }}
{{ video.created }}
{{ video.updated }}
{{ video.duration }}
{{ video.hashedId }}
{{ video.description }}
{{ video.project.id }}
{{ video.project.name }}
{{ video.project.hashedId }}
{{ video.original.url }}
{{ video.original.width }}
{{ video.original.height }}
{{ video.original.filesize }}
{{ video.high.url }}
{{ video.high.width }}
{{ video.high.height }}
{{ video.high.filesize }}
{{ video.low.url }}
{{ video.low.width }}
{{ video.low.height }}
{{ video.low.filesize }}
```

#### embed
Formatted SEO-friendly embed code for the video.

#### preview.getUrl
Video screenshot. Default size: 1280px by 720px.

You can specify a width and/or height to resize the preview image. The image will be center-cropped based on the size you specify. The width parameter is required to transform the image, otherwise the image will output the default size.

```twig
{{ video.preview.getUrl({
	width: 640,
	height: 360
}) }}
```

#### id
Wisita ID of the video.

#### name
Name assigned to the video in Wistia.

#### type
Type of video.

#### created
ISO 8601 date the video was created in Wistia. e.g. "2013-01-30T16:01:05+00:00".

#### updated
ISO 8601 date the video was last updated in Wistia. e.g. "2016-02-25T20:19:17+00:00".

#### duration
Duration of the video in seconds.

#### hashedId
Hashed ID of the video.

#### description
Description of the video added in Wistia.

#### project.id
Wistia ID of the video's project.

#### project.name
Name of the video's project.

#### project.hashedId
Hashed ID of the video's project.

#### original.url
URL to the original video file.

#### original.width
Width of the original video file.

#### original.height
Height of the original video file.

#### original.filesize
File size in bytes of the original video file.

#### high.url
URL to the high quality MP4 video file.

#### high.width
Width of the high quality MP4 video file.

#### high.height
Height of the high quality MP4 video file.

#### high.filesize
File size in bytes of the high quality MP4 video file.

#### low.url
URL to the low quality MP4 video file.

#### low.width
Width of the low quality MP4 video file.

#### low.height
Height of the low quality MP4 video file.

#### low.filesize
File size in bytes of the low quality MP4 video file.

## Advanced

You can also override the default player color for all your videos site-wide. Add a "wistia.php" file within the "craft/config" directory and enter your desired color.

```php
<?php

return array(
	'playerColor' => '#ff00ff'
);
```

## Installation

1. Move the "wistia" directory to "craft/plugins".
2. In Craft navigate to the Plugin section within Settings.
3. Click the Install button on the Wistia entry.
4. Update the following settings as applicable.
	* API Key: Your Wistia API key.
	* Cache Duration: How long the Wistia API data is cached.
	* Thumbnail Cache Path: Ensure the specified path exists and is writable by Craft.

## License

Copyright 2016 [Caddis Interactive, LLC](https://www.caddis.co). Licensed under the [Apache License, Version 2.0](https://github.com/caddis/craft-wistia/blob/master/LICENSE).