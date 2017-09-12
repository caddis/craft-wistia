# NOTICE: This plugin has been deprecated and is no longer in active development. Please download the [latest version](https://github.com/lewiscommunications/craft-wistia) managed by [Lewis Communications](https://github.com/lewiscommunications).

![Craft Wistia](https://www.caddis.co/internal/repo/craft-wistia.svg)

Wistia is an incredible video marketing platform we rely on every day. Craft Wistia includes a configurable fieldtype to easily relate videos to entries and flexible tags to get video data and output embed code.

[Wistia Developer Docs →](https://wistia.com/doc/developers)

## Getting Started

To output videos on the front-end, use your field's handle and append the `getVideos` method. The getVideos method will return an array of videos or `false` if no videos are saved to your field.

```twig
{% for video in entry.videos.getVideos() %}
	<h2>{{ video.name }}</h2>
	{{ video.embed }}
{% endfor %}
```

### Inside a Matrix

```twig
{% for block in entry.matrixBlock %}
    {% if block.type == 'videos' %}
        {% for video in block.videos %}
            <h3>{{ video.name }}</h3>
            <img src="{{ video.preview.getUrl }}" alt="{{ video.name }}">
        {% endfor %}
    {% endif %}
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

### created
The date when the media was originally uploaded.

```
"created":"2013-09-19T15:30:49+00:00"
```

### description
A description for the media which usually appears near the top of the sidebar on the media's page.

```
"description":"<p>\n\nWistia goes to Nevada to visit with Zappos to hear what they have to say about their company culture.&nbsp;</p>\n<p>\n\n&nbsp;</p>\n<p>\n\nFor more How They Work videos, check out:</p>\n<p>\n\n<a href=\"http://jeff.wistia.com/projects/ln2k6qwi9k\">http://jeff.wistia.com/projects/ln2k6qwi9k</a></p>\n"
```

### duration
Specifies the length (in seconds) for audio and video files. Specifies number of pages in the document. Omitted for other types of media.

```
"duration":167.0
```

### embed
Formatted SEO-friendly embed code for the video.

### hashedId
A unique alphanumeric identifier for this media.

```
"hashedId":"v80gyfkt28",
```

### high.filesize
File size in bytes of the high quality MP4 video file.

### high.height
Height of the high quality MP4 video file.

### high.url
URL to the high quality MP4 video file.

### high.width
Width of the high quality MP4 video file.

### id
A unique numeric identifier for the media within the system.

```
"id":4489021
```

### low.filesize
File size in bytes of the low quality MP4 video file.

### low.height
Height of the low quality MP4 video file.

### low.url
URL to the low quality MP4 video file.

### low.width
Width of the low quality MP4 video file.

### name
The display name of the media.

```
"name":"How They Work - Zappos"
```

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

You can specify a width and/or height to resize the preview image. The image is center-cropped based on the size you specify. The width parameter is required to transform the image, otherwise the image will output the default size.

```twig
{{ video.preview.getUrl({
	width: 640,
	height: 360
}) }}
```

### project
The project associated with the video.

```
"project":{
  "id":464427,
  "name":"How They Work",
  "hashed_id":"ln2k6qwi9k"
}
```

### type
A string representing what type of media this is. Values can be **Video**, **Audio**, **Image**, **PdfDocument**, **MicrosoftOfficeDocument**, **Swf**, or **UnknownType**.

```
"type":"Video"
```

### thumbnail
The thumbnail image for the video. Refer to [Asset URLs - Tips & Tricks](https://wistia.com/doc/data-api#asset_urls__tips__tricks) for more information.

```
"thumbnail":{
  "url":"http://embed.wistia.com/deliveries/7fbf9c2fe9c6585f9aa032f43f0aecc3f287e86b.jpg?image_crop_resized=100x60",
  "width":100,
  "height":60
}
```

#### Get a larger thumbnail url
To retrieve a larger thumbnail image, update the `image_crop_resized` query string in the thumbnail url.

```
{{ video.thumbnail.url|split('image_crop_resized')[0] ~ 'image_crop_resized=1044x514' }}
```

**Note:** It is more preferable to use the [preview.getUrl](#previewgeturl) tag since it caches the thumbnails on your server.

### updated
The date when the media was last changed.

```
"updated":"2013-10-28T20:53:12+00:00"
```

## CSV Import

The Wistia plugin provides compatibility with the [Import plugin](https://github.com/boboldehampsink/import).  Enter the name of the video in the video column of the .csv file. Make sure that the correct Wistia projects are selected for the field. If you want multiple videos saved on a single field, comma separate each video name in the column.

## Advanced

You can also override the default player color for all your videos site-wide. Add a "wistia.php" file within the "craft/config" directory and enter your desired color.

```php
<?php

return [
	'playerColor' => '#ff00ff'
];
```

## Installation

1. Move the "wistia" directory to "craft/plugins".
2. In Craft navigate to the Plugin section within Settings.
3. Click the Install button on the Wistia entry.
4. Update the following settings as applicable:
	* API Key: Your Wistia API key.
	* Cache Duration: How long the Wistia API data is cached.
	* Thumbnail Cache Path: Ensure the specified path exists and is writable by Craft.
	
## Roadmap

- Add ability sort items in element selector modal
- Add ability to toggle grid and list view element selector modal
- Add additional info columns in element selector modal (group, category, post date, etc.)
- Add ability select item in element selector modal by double clicking
- Add ability to select multiple items in element selector modal
- Add ability to upload and manage Wistia videos in the admin
- Add sort and order parameters to variable tag
- Consider adding functionality that automatically creates thumbnail cache directory on page load (if directory does not exist) 
- Consider removing http session cache in admin
- Display video thumbnails next to video titles (like assets) in element selector modal and element selector
- Display spinner next to the search bar when searching in element selector modal
- Group videos by project in the sidebar in the element selector modal

## License

Copyright 2017 [Lewis Communications](http://www.lewiscommunications.com/). Licensed under the [Apache License, Version 2.0](https://github.com/caddis/craft-wistia/blob/master/LICENSE).
