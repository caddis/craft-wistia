# Developer Notes

## Feature Enhancements Wishlist

- Create modal html with js
- Sidebar filtering by project on modal
- Add thumbnails to element rows
- Ordering in modal
- Add cache-clearing on a per-file basis in admin cp
	- registerCachePaths() won't work because we're saving into a table, not the Craft cache storage directory.
- Add ability to select multiple items on the popup
- Add spinner when searching through items
- Add "No items found" message when search returns not results
- Auto create thumbnail image directory
- Add sorting and orderby params
- Revert to old array syntax (array())

## Outstanding Tasks

- Conditional to see if resized preview image already exists and is has not expire
- Update default image conditional logic to use Craft's IOHelper methods