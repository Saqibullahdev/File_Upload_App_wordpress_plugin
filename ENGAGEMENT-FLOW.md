# Engagement Flow System Documentation

## Overview

The Engagement Flow System is a dynamic multi-step landing flow designed to maximize user engagement before providing access to file download links. This system is particularly optimized for mobile users coming from TikTok and other social media platforms.

## Features

### 🚀 **Multi-Step Engagement Flow**
- **Page 1 (Engagement):** Interactive scrolling content with screenshots and compelling descriptions
- **Page 2 (Intermediate):** App details, features, testimonials, and trust indicators  
- **Page 3 (Download):** Protected download link with final call-to-action

### 📊 **Advanced Analytics**
- Time spent on each page
- Scroll depth tracking
- Button click interactions
- Conversion rate analysis
- Mobile-specific metrics

### 📱 **Mobile-First Design**
- Optimized for TikTok users
- Touch-friendly interactions
- Smooth animations and transitions
- Dark mode support

## How to Use

### 1. Enable Engagement Flow

When creating or editing a File Upload post:

1. Go to **File Upload Details** meta box
2. Check **"Enable Engagement Flow"**
3. Fill in the engagement settings:
   - **Engagement Title:** Catchy title for the first page
   - **Engagement Description:** Compelling description to encourage scrolling
   - **Screenshots/Media URLs:** Image URLs (one per line) for visual content
   - **Intermediate Content:** Additional details for the second page

### 2. URL Structure

Once enabled, your app will have these URLs:
- `yoursite.com/app/{id}/step1` - Engagement page (use this for TikTok links)
- `yoursite.com/app/{id}/step2` - Intermediate page  
- `yoursite.com/app/{id}/download` - Final download page

### 3. Analytics Dashboard

In the admin interface, you'll see:
- **Total Events:** All tracked interactions
- **Unique Visitors:** Number of different users
- **Avg. Time Spent:** Average session duration
- **Avg. Scroll Depth:** How far users scroll (percentage)
- **Conversion Rate:** Percentage who complete the download

## Best Practices

### 📝 **Content Guidelines**

**Engagement Title:**
- Keep it under 60 characters
- Use action words ("Get", "Download", "Unlock")
- Make it exciting and specific

**Engagement Description:**
- 2-3 paragraphs maximum
- Focus on benefits, not features
- Create urgency or excitement

**Screenshots:**
- Use high-quality images (minimum 400px wide)
- Show the app in action
- Include 3-5 screenshots for best engagement

**Intermediate Content:**
- Highlight key features
- Include social proof or testimonials
- Address common concerns

### 🎯 **Optimization Tips**

1. **Keep users scrolling** - Use compelling visuals and short text blocks
2. **Create urgency** - Limited time offers or exclusive access
3. **Build trust** - Show download counts, security badges, reviews
4. **Mobile-first** - Test on mobile devices frequently
5. **Monitor analytics** - Adjust content based on user behavior

### 📱 **TikTok Integration**

1. **Link Strategy:** Always link to `/step1` in your TikTok bio or videos
2. **Content Alignment:** Match your TikTok content with the engagement page
3. **Call-to-Action:** Use clear CTAs like "Link in bio to get the app"
4. **Track Performance:** Monitor conversion rates from TikTok traffic

## Technical Details

### URL Rewriting
The system uses WordPress rewrite rules to create clean URLs:
```
/app/{file_id}/step1    → Engagement page
/app/{file_id}/step2    → Intermediate page  
/app/{file_id}/download → Download page
```

### Analytics Events Tracked
- `scroll_milestone` - 25%, 50%, 75%, 90% scroll depth
- `continue_clicked` - Step progression
- `screenshot_viewed` - Image interactions
- `feature_viewed` - Feature list engagement
- `final_download_clicked` - Conversion events
- `social_share` - Share button clicks

### Mobile Optimizations
- Touch-friendly button sizes (minimum 44px)
- Reduced animations for better performance
- Optimized images with lazy loading
- Gesture-based interactions

## Troubleshooting

### Common Issues

**URLs not working:**
1. Go to **Settings > Permalinks** 
2. Click **"Save Changes"** to flush rewrite rules
3. Ensure your .htaccess file is writable

**Engagement flow not showing:**
1. Verify the "Enable Engagement Flow" checkbox is checked
2. Save the post after making changes
3. Clear any caching plugins

**Analytics not tracking:**
1. Check browser console for JavaScript errors
2. Verify AJAX requests are reaching the server
3. Ensure WordPress AJAX is functioning properly

**Mobile layout issues:**
1. Test on actual mobile devices
2. Check viewport meta tag in your theme
3. Verify CSS is loading correctly

### Performance Considerations

- Analytics data is limited to 1000 entries per file
- Large screenshot images should be optimized
- Consider using a CDN for image hosting
- Monitor database size with heavy usage

## Customization

### CSS Customization
Override styles in your theme's `style.css`:
```css
.engagement-flow-container {
    /* Your custom styles */
}
```

### Template Customization
Create custom templates in your theme:
- `engagement-step1.php`
- `engagement-step2.php` 
- `engagement-download.php`

### Hook Integration
Use WordPress hooks for custom functionality:
```php
// Modify engagement content
add_filter('engagement_flow_content', 'my_custom_content');

// Track custom events
add_action('engagement_flow_event', 'my_custom_tracking');
```

## Support

For technical support or feature requests:
1. Check the WordPress admin for error logs
2. Review browser console for JavaScript errors
3. Test with a default WordPress theme
4. Disable other plugins to check for conflicts

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Compatibility:** WordPress 5.0+ | Mobile-Optimized | TikTok-Ready
