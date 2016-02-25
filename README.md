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
To output videos on the front end, use your fieldtype's handle and add `getVideos`. You can apply a variety of parameters that are described in the next section.

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
Skip over the specified results. For example, if the tag returns 5 videos and you set an offset of two, the first two results are skipped and numbers 3, 4, and 5 are shown.

#### width
Width of the embed. If a width is applied, the responsive parameter (see below) will default to "false" unless you specify otherwise. Default: `640px`.

#### height
Height of the embed. Default: `360px`.

#### autoPlay
Determines if the video embed plays on load. Default: `false`.

#### controlsVisibleOnLoad
Determines if the video embed controls are visible on load. Default: `true`.

#### fullscreenButton
Determines if the full screen button appears on load. Default: `true`.

#### playbar
Determines if the play bar appears on load. Default: `true`.

#### playButton
Determines if the play button appears on load. Default: `true`.

#### volumeControl
Determines if the volume control appears on load. Default: `true`.

#### smallPlayButton
Determines if the small play button appears on load. Default: `true`.

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
Determines if video embed is responds to screen width. Default: `true`.

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
{{ video.preview }}
{{ video.embed }}
```

#### id
The Wisita ID of the video.

#### name
The name assigned to the video in the Wistia admin.

#### type
The type of video.

#### created
Date the video was created in Wistia.

#### updated
Date the video was last updated in Wistia.

#### duration
The duration of the video in seconds.

#### hashed_id
Unique indentifier of the video.

#### description
A description of the video added in the Wistia admin.

#### project.id
The ID of the video’s project on Wistia.

#### project.name
The name of the video’s project on Wistia.

#### project.hashed_id
The hashed id of the video’s project on Wistia.

#### preview
Default thumbnail size is 1280 by 720.

#### embed
The formatted embed code of the video.