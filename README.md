# Craft Wistia
The Craft Wistia plugin allows you to output Wistia videos on your site.

## Install
After downloading and decompressing, put the wistia directory in your plugins folder. Install the module from the Craft plugins admin page.

## Settings
To access the plugin settings, navigate to the Craft settings page and click on "Wistia" under the "Plugins" heading.

### API Key
Enter your Wistia API key to use the plugin with your Wistia account and access your projects.

### Cache Duration
Defaults to `24 hours`. If you don't plan on changing your video data often, you can safely increase the cache time here.

### Thumnail Cache Path
Defaults to `/images/videos/`. Regardless of whether you use the default or specify your own, you will need to make sure that the specified path exists on the server and is writable by Craft. The plugin will not create the directory for you.

## getVideos()
To output videos on the front end, use your fieldtype's handle and add `getVideos`. You can apply a variety of parameters described in the next section.

```
{% set params = {
    width: 720,
    height: 420,
    autoPlay: true
} %}

{% for video in entry.videos.getVideos(params) %}
    <h3>{{ video.name }}</h3>
    {{ video.embed }}
{% endfor %}
```
## Parameters

### Parameters Quick Reference

```
{
	width: '640px'
	height: '360px'
	limit: 150
	offset: 0
	autoPlay: false
	controlsVisibleOnLoad: true
	fullscreenButton: true
	playbar: true
	playButton: true
	volumeControl: true
	smallPlayButton: true
	playerColor: '#7B796A'
	responsive: true
}
```
#### limit
Limits results. Default: `150`.

#### offset
Skips over the specified results. For example, if the tag returns 5 videos and you set an offset of two, the first two results are skipped and results 3, 4, and 5 are shown.

#### width
Width of the embed. If a width is applied, the responsive parameter (see below) will default to "false" unless you specify otherwise. Default: `640px`.

#### height
Height of the embed. Default: `360px`.

#### autoPlay
Determines if the video embed plays on load. Default: `false`.

#### controlsVisibleOnLoad
Determines if the video embed controls are visible on load. Default: `true`.

#### fullscreenButton
Determines if the full screen button is visible on load. Default: `true`.

#### playbar
Determines if the play bar is visible on load. Default: `true`.

#### playButton
Determines if the play button is visible on load. Default: `true`.

#### volumeControl
Determines if the volume control is visible on load. Default: `true`.

#### smallPlayButton
Determines if the small play button is visible on load. Default: `true`.

#### playerColor
Enter a valid hex color to change the player color.

You can also override the default player color for all your videos site-wide. Add a `wistia.php` file to your main config directory and enter your desired color.

```
<?php

return array(
	'playerColor' => '#ff00ff',
);
```

#### responsive
Determines if the video embed responds to the browser screen width. Default: `true`.

## Tags

### Tags Quick Reference

```
{{ video.id }}
{{ video.name }}
{{ video.type }}
{{ video.created }}
{{ video.updated }}
{{ video.duration }}
{{ video.hashed_id }}
{{ video.description }}
{{ video.project.id }}
{{ video.project.name }}
{{ video.project.hashed_id }}
{{ video.preview.getUrl }}
{{ video.embed }}
```

#### id
The Wisita ID of the video.

#### name
The name assigned to the video in Wistia.

#### type
The type of video.

#### created
The ISO 8601 date the video was created in Wistia. e.g. `2013-01-30T16:01:05+00:00`.

#### updated
The ISO 8601 date the video was last updated in Wistia. e.g. `2016-02-25T20:19:17+00:00`.

#### duration
The duration of the video in seconds.

#### hashed_id
The hashed ID of the video.

#### description
A description of the video added in the Wistia.

#### project.id
The Wistia ID of the video’s project.

#### project.name
The name of the video’s project.

#### project.hashed_id
The hashed ID of the video’s project.

#### preview.getUrl
The default video screenshot. Default size: `1280px by 720px`.

You can specify a width and/or height to resize the preview image. The image will be center cropped based on the size you specify. The width parameter is required for the transform to work, otherwise, the image will output the default size.

```
{% set previewSize = {
	width: 503,
	height: 273
} %}

{{ video.preview.getUrl(previewSize) }}
```

#### embed
The formatted embed code of the video.