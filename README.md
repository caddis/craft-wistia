![Craft Wistia](https://www.caddis.co/internal/repo/craft-wistia.svg)

Wistia is an incredible video marketing platform we rely on every day. Craft Wistia includes a configurable fieldtype to easily relate videos to entries and flexible tags to get video data and output embed code.

## Getting Started

To output videos on the front-end, use your field's handle and append the `getVideos` method. The getVideos method will return an array of videos or `false` if no videos are saved to your field.

```twig
{% for video in entry.videos.getVideos() %}
	<h2>{{ video.name }}</h2>
	{{ video.embed }}
{% endfor %}
```

## Parameters

Pass your options as an object into the `getVideos` method.

```twig
{% set params = {
	autoPlay: true
	height: 420,
	width: 720
} %}

{% for video in entry.videos.getVideos(params) %}
	<h2>{{ video.name }}</h2>
	{{ video.embed }}
{% endfor %}
```

| Parameter             | Type | Default | Description |
| --------------------- | ---- | ------- | ----------- |
| autoPlay              | Boolean | false | Determines if the video embed plays on load. |
| controlsVisibleOnLoad | Boolean | true | Determines if the video embed controls are visible on load. |
| fullscreenButton      | Boolean | true | Determines if the full screen button is visible on load. |
| height                | Number | 360 | Height of the embed |
| limit                 | Number | 150 | Limits results |
| offset                | Number | | Skips over the specified results. For example, if the tag returns 5 videos and you set an offset of 2, the first 2 results are skipped and results 3, 4, and 5 are shown. |
| playbar               | Boolean | true | Determines if the play bar is visible on load. |
| playButton            | Boolean | true | Determines if the play button is visible on load. |
| playerColor           | String | | Enter a valid HEX color to change the player color. |
| smallPlayButton       | Boolean | true | Determines if the small play button is visible on load. |
| volumeControl         | Boolean | true | Determines if the volume control is visible on load. |
| responsive            | Boolean | true | Determines if the video embed responds to the parent container width. |
| width                 | Number | 640 | Width of the embed. |

## Template Tags

- [created](#created)
- [description](#description)
- [duration](#duration)
- [embed](#embed)
- [hashedId](#hashedId)
- [high.filesize](#highfilesize)
- [high.height](#highheight)
- [high.url](#highurl)
- [high.width](#highwidth)
- [id](#id)
- [low.filesize](#lowfilesize)
- [low.height](#lowheight)
- [low.url](#lowurl)
- [low.width](#lowwidth)
- [name](#name)
- [original.filesize](#originalfilesize)
- [original.height](#originalheight)
- [original.url](#originalurl)
- [original.width](#originalwidth)
- [preview.getUrl](#previewgeturl)
- [project.hashedId](#projecthashedid)
- [project.id](#projectid)
- [project.name](#projectname)
- [type](#type)
- [updated](#updated)

### created
ISO 8601 date the video was created in Wistia. e.g. "2013-01-30T16:01:05+00:00".

### description
Description of the video added in Wistia.

### duration
Duration of the video in seconds.

### embed
Formatted SEO-friendly embed code for the video.

### hashedId
Hashed ID of the video.

### high.filesize
File size in bytes of the high quality MP4 video file.

### high.height
Height of the high quality MP4 video file.

### high.url
URL to the high quality MP4 video file.

### high.width
Width of the high quality MP4 video file.

### id
Wisita ID of the video.

### low.filesize
File size in bytes of the low quality MP4 video file.

### low.height
Height of the low quality MP4 video file.

### low.url
URL to the low quality MP4 video file.

### low.width
Width of the low quality MP4 video file.

### name
Name assigned to the video in Wistia.

### original.filesize
File size in bytes of the original video file.

### original.height
Height of the original video file.

### original.url
URL to the original video file.

### original.width
Width of the original video file.

### preview.getUrl
Video screenshot. Default size: 1280px by 720px.

You can specify a width and/or height to resize the preview image. The image will be center-cropped based on the size you specify. The width parameter is required to transform the image, otherwise the image will output the default size.

```twig
{{ video.preview.getUrl({
	width: 640,
	height: 360
}) }}
```

### project.hashedId
Hashed ID of the video's project.

### project.id
Wistia ID of the video's project.

### project.name
Name of the video's project.

### type
Type of video.

### updated
ISO 8601 date the video was last updated in Wistia. e.g. "2016-02-25T20:19:17+00:00".

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