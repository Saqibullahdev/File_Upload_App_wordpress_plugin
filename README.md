# File Upload App WordPress Plugin

A comprehensive WordPress plugin for managing file uploads with custom post types, taxonomies, and advanced filtering capabilities.

## Features

### 📁 Custom Post Type
- **File Upload** post type with full WordPress integration
- Title, description (editor), and file link upload functionality
- Featured image support for file thumbnails
- Built-in WordPress tags support

### 🏷️ Custom Taxonomies
- **File Categories** - Hierarchical taxonomy for organizing files
- **File Types** - Non-hierarchical taxonomy for file type classification
- Full integration with WordPress tag system

### 📊 File Management
- File URL upload with media library integration
- Automatic file size and type detection
- Download tracking and statistics
- Drag and drop file upload support
- File preview functionality

### 🔍 Advanced Filtering
- Search functionality across titles and descriptions
- Filter by categories, types, and tags
- Sort by date, title, downloads, or last modified
- Grid and list view options
- Pagination support
- AJAX-powered filtering (optional)

### 🎨 Frontend Display
- Responsive grid and list layouts
- File download tracking
- Related files suggestions
- Modern UI with hover effects
- Mobile-optimized design

### ⚡ Developer Features
- Shortcode support: `[file_upload_list]`
- Template override system
- Custom hooks and filters
- REST API support
- Multisite compatible

## Installation

1. Upload the `file-upload-app` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start adding files through the new "File Uploads" menu item

## Usage

### Admin Interface

#### Adding Files
1. Go to **File Uploads** > **Add New**
2. Enter a title and description
3. Click **Upload File** to select a file from media library
4. File size and type will be automatically detected
5. Assign categories, types, and tags as needed
6. Publish the file

#### Managing Taxonomies
- **File Categories**: Organize files hierarchically
- **File Types**: Tag files by format (PDF, DOC, etc.)
- **Tags**: Add flexible labels for cross-referencing

### Frontend Display

#### Archive Pages
- Visit `/file_upload/` for the main file library
- Use category/type archive pages for filtered views
- Advanced filtering sidebar with search

#### Single File Pages
- Individual file detail pages with full information
- Download button with tracking
- Related files suggestions
- Social sharing ready

#### Shortcode Usage
```php
// Basic usage
[file_upload_list]

// With parameters
[file_upload_list posts_per_page="12" category="documents" show_filter="true"]

// Available parameters:
// - posts_per_page: Number of files to show (default: 10)
// - category: Filter by category slug
// - type: Filter by file type slug  
// - tag: Filter by tag slug
// - show_filter: Show filter form (true/false, default: true)
```

### Template Customization

Copy template files to your theme for customization:

```
your-theme/
├── single-file_upload.php    (Single file template)
├── archive-file_upload.php   (Archive template)
├── taxonomy-file_category.php (Category archive)
└── taxonomy-file_type.php    (Type archive)
```

## Customization

### Hooks and Filters

```php
// Modify file upload fields
add_filter('file_upload_meta_fields', 'custom_meta_fields');

// Customize file display
add_filter('file_upload_display_args', 'custom_display_args');

// Add custom file actions
add_action('file_upload_before_download', 'custom_download_action');
```

### CSS Customization

Target these classes for styling:
- `.file-upload-list` - Main file grid container
- `.file-item` - Individual file cards
- `.file-upload-filter` - Filter form container
- `.download-btn` - Download buttons

### JavaScript Events

```javascript
// Download tracking
$(document).on('fileDownloaded', function(e, fileId, newCount) {
    // Custom download tracking logic
});

// Filter applied
$(document).on('filterApplied', function(e, filters) {
    // Custom filter logic
});
```

## File Structure

```
file-upload-app/
├── file-upload-app.php       # Main plugin file
├── assets/
│   ├── css/
│   │   ├── admin.css         # Admin interface styles
│   │   └── frontend.css      # Frontend styles
│   └── js/
│       ├── admin.js          # Admin functionality
│       └── frontend.js       # Frontend interactions
├── templates/
│   ├── single-file_upload.php    # Single file template
│   └── archive-file_upload.php   # Archive template
└── README.md                 # This file
```

## Database Schema

### Post Meta Fields
- `_file_url` - Direct link to the file
- `_file_size` - Human-readable file size
- `_file_type` - File extension/type
- `_download_count` - Number of downloads

### Taxonomies
- `file_category` - Hierarchical file categories
- `file_type` - Non-hierarchical file types
- `post_tag` - Standard WordPress tags

## Performance Considerations

- Files are served directly (no proxy downloads)
- Efficient database queries with proper indexing
- Lazy loading for images
- Caching-friendly structure
- CDN compatible

## Security Features

- Nonce verification for all forms
- Capability checks for file management
- Sanitized inputs and escaped outputs
- No direct file uploads to prevent security risks

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (limited features)

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Changelog

### Version 1.0.0
- Initial release
- Custom post type and taxonomies
- File upload and management
- Frontend filtering and display
- Admin interface
- Template system

## Support

For support and feature requests, please contact the plugin developer or submit an issue through the appropriate channels.

## License

GPL v2 or later - Same as WordPress

## Credits

Developed with ❤️ for WordPress users who need advanced file management capabilities.
