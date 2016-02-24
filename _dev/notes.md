# Developer Notes

## Feature Enhancements Wishlist

- Create modal html with js and pull in JSON data instead of raw html to fill modal
- Sidebar filtering by project on modal
- Add thumbnails to element rows
- Ordering in modal
- Add cache-clearing on a per-file basis in admin cp
	- registerCachePaths() won't work because we're saving into a table, not the Craft cache storage directory.
- Add ability to select multiple items on the popup
- Add spinner when searching through items
- Add "No items found" message when search returns not results

## Outstanding Tasks

- Update curl request to guzzle
- Pull down video thumbnail
- Add params to front end output
	- limit
	- offset
	- sort
	- orderby