jQuery(document).ready(function($) {
    'use strict';
    
    // Test link functionality
    $('#test_link_button').click(function(e) {
        e.preventDefault();
        
        var fileUrl = $('#file_url').val();
        if (!fileUrl) {
            showMessage('Please enter a file URL first.', 'error');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).text('Testing...');
        
        // Test the link
        testFileLink(fileUrl);
    });
    
    // Auto-detect source when URL is entered
    $('#file_url').on('input blur', function() {
        var url = $(this).val();
        if (url) {
            autoDetectSource(url);
            autoDetectFileType(url);
        }
    });
    
    // Media uploader functionality
    var mediaUploader;
    
    $('#upload_file_button').click(function(e) {
        e.preventDefault();
        
        // If the media uploader already exists, reopen it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create new media uploader
        mediaUploader = wp.media({
            title: 'Select File',
            button: {
                text: 'Use this file'
            },
            multiple: false
        });
        
        // When a file is selected
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Update the file URL field
            $('#file_url').val(attachment.url);
            
            // Update file metadata
            updateFileMetadata(attachment);
            
            // Show file preview
            showFilePreview(attachment);
            
            // Show success message
            showMessage('File uploaded successfully!', 'success');
        });
        
        // Open the media uploader
        mediaUploader.open();
    });
    
    // Update file metadata fields
    function updateFileMetadata(attachment) {
        // File size
        if (attachment.filesizeHumanReadable) {
            $('#file_size').val(attachment.filesizeHumanReadable);
        } else if (attachment.filesize) {
            $('#file_size').val(formatFileSize(attachment.filesize));
        }
        
        // File type
        if (attachment.subtype) {
            $('#file_type_meta').val(attachment.subtype.toUpperCase());
        } else if (attachment.mime) {
            var mimeType = attachment.mime.split('/')[1];
            $('#file_type_meta').val(mimeType.toUpperCase());
        }
        
        // Auto-populate title if empty
        if (!$('#title').val() && attachment.title) {
            $('#title').val(attachment.title);
        }
        
        // Auto-populate description if empty
        if (!$('#content').val() && attachment.description) {
            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                tinymce.get('content').setContent(attachment.description);
            } else {
                $('#content').val(attachment.description);
            }
        }
    }
    
    // Show file preview
    function showFilePreview(attachment) {
        var previewHtml = '<div class="file-preview">';
        
        // Show thumbnail if it's an image
        if (attachment.type === 'image' && attachment.sizes && attachment.sizes.thumbnail) {
            previewHtml += '<img src="' + attachment.sizes.thumbnail.url + '" alt="File preview">';
        } else {
            previewHtml += '<div class="file-icon">📄</div>';
        }
        
        previewHtml += '<div class="file-info">';
        previewHtml += '<span class="file-name">' + attachment.filename + '</span>';
        previewHtml += '<div class="file-details">';
        previewHtml += '<span>Type: ' + (attachment.subtype ? attachment.subtype.toUpperCase() : 'Unknown') + '</span><br>';
        previewHtml += '<span>Size: ' + (attachment.filesizeHumanReadable || 'Unknown') + '</span>';
        previewHtml += '</div>';
        previewHtml += '</div>';
        previewHtml += '<button type="button" class="remove-file-btn">Remove</button>';
        previewHtml += '</div>';
        
        // Remove existing preview
        $('.file-preview').remove();
        
        // Add new preview
        $('#file_url').after(previewHtml);
        $('.file-preview').slideDown();
        
        // Remove file functionality
        $('.remove-file-btn').click(function() {
            removeFile();
        });
    }
    
    // Remove file
    function removeFile() {
        $('#file_url').val('');
        $('#file_size').val('');
        $('#file_type_meta').val('');
        $('.file-preview').slideUp(function() {
            $(this).remove();
        });
        showMessage('File removed successfully!', 'success');
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Show success/error messages
    function showMessage(message, type) {
        // Remove existing messages
        $('.file-upload-message').remove();
        
        var messageHtml = '<div class="file-upload-message ' + type + '">' + message + '</div>';
        $('#file_url').closest('td').prepend(messageHtml);
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $('.file-upload-message').fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Validate file URL when manually entered
    $('#file_url').on('blur', function() {
        var url = $(this).val();
        if (url && isValidUrl(url)) {
            // Try to get file info from URL
            getFileInfoFromUrl(url);
        }
    });
    
    // Validate URL format
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Get file info from URL
    function getFileInfoFromUrl(url) {
        var filename = url.split('/').pop();
        var extension = filename.split('.').pop().toLowerCase();
        
        if (extension && !$('#file_type_meta').val()) {
            $('#file_type_meta').val(extension.toUpperCase());
        }
        
        // Try to get file size via AJAX (this might not work due to CORS)
        $.ajax({
            url: url,
            type: 'HEAD',
            success: function(data, status, xhr) {
                var fileSize = xhr.getResponseHeader('Content-Length');
                if (fileSize && !$('#file_size').val()) {
                    $('#file_size').val(formatFileSize(parseInt(fileSize)));
                }
            },
            error: function() {
                // CORS or other error - we can't get file size
                console.log('Could not retrieve file size from URL');
            }
        });
    }
    
    // Auto-save functionality
    var autoSaveTimeout;
    
    function triggerAutoSave() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            if ($('#auto_draft').length === 0) {
                $('#publish').click();
            }
        }, 30000); // Auto-save after 30 seconds of inactivity
    }
    
    // Trigger auto-save on field changes
    $('#file_url, #title, #content').on('input', function() {
        triggerAutoSave();
    });
    
    // Character counter for title
    $('#title').on('input', function() {
        var titleLength = $(this).val().length;
        var maxLength = 100; // Reasonable limit for titles
        
        // Remove existing counter
        $('.title-counter').remove();
        
        if (titleLength > maxLength * 0.8) {
            var counterHtml = '<div class="title-counter">' + titleLength + '/' + maxLength + ' characters</div>';
            $(this).after(counterHtml);
            
            if (titleLength > maxLength) {
                $('.title-counter').css('color', '#d63384');
            } else {
                $('.title-counter').css('color', '#fd7e14');
            }
        }
    });
    
    // Enhanced drag and drop functionality
    var $fileUrlField = $('#file_url');
    var $uploadContainer = $fileUrlField.closest('td');
    
    // Prevent default drag behaviors
    $(document).on('dragenter dragover drop', function(e) {
        e.preventDefault();
    });
    
    // Add drag and drop styling
    $uploadContainer.on('dragenter dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $uploadContainer.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    // Handle file drop
    $uploadContainer.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleDroppedFile(files[0]);
        }
    });
    
    // Handle dropped file
    function handleDroppedFile(file) {
        // Create FormData object
        var formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload-attachment');
        formData.append('_wpnonce', $('#_wpnonce').val());
        
        // Show uploading state
        $uploadContainer.addClass('uploading');
        showMessage('Uploading file...', 'info');
        
        // Upload file via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    var attachment = response.data;
                    $('#file_url').val(attachment.url);
                    updateFileMetadata(attachment);
                    showFilePreview(attachment);
                    showMessage('File uploaded successfully!', 'success');
                } else {
                    showMessage('Upload failed: ' + response.data, 'error');
                }
            },
            error: function() {
                showMessage('Upload failed. Please try again.', 'error');
            },
            complete: function() {
                $uploadContainer.removeClass('uploading');
            }
        });
    }
    
    // Add CSS for drag and drop
    $('<style>').text(`
        .drag-over {
            background-color: #e3f2fd !important;
            border: 2px dashed #2196f3 !important;
        }
        .title-counter {
            font-size: 11px;
            margin-top: 5px;
            color: #666;
        }
    `).appendTo('head');
    
    // Initialize existing file preview if file URL is already set
    if ($('#file_url').val()) {
        var existingUrl = $('#file_url').val();
        autoDetectSource(existingUrl);
        autoDetectFileType(existingUrl);
        
        var filename = existingUrl.split('/').pop();
        
        var previewHtml = '<div class="file-preview">';
        previewHtml += '<div class="file-icon">📄</div>';
        previewHtml += '<div class="file-info">';
        previewHtml += '<span class="file-name">' + filename + '</span>';
        previewHtml += '<div class="file-details">';
        previewHtml += '<span>Current file</span>';
        previewHtml += '</div>';
        previewHtml += '</div>';
        previewHtml += '<button type="button" class="remove-file-btn">Remove</button>';
        previewHtml += '</div>';
        
        $('#file_url').after(previewHtml);
        
        $('.remove-file-btn').click(function() {
            removeFile();
        });
    }
    
    // Helper function to test file link
    function testFileLink(url) {
        // Show preview
        showLinkPreview(url);
        
        // Test if URL is accessible
        var img = new Image();
        var testComplete = false;
        
        // Timeout after 5 seconds
        setTimeout(function() {
            if (!testComplete) {
                testComplete = true;
                $('#test_link_button').prop('disabled', false).text('Test Link');
                
                // Still show preview but with warning
                var previewContent = '<div class="link-test-result warning">';
                previewContent += '<strong>⚠️ Warning:</strong> Could not verify link accessibility. ';
                previewContent += 'This might be due to CORS restrictions or the link requiring authentication.';
                previewContent += '</div>';
                
                $('#link_preview .preview-content').html(previewContent);
            }
        }, 5000);
        
        // Try to test the link
        fetch(url, {
            method: 'HEAD',
            mode: 'no-cors'
        }).then(function() {
            if (!testComplete) {
                testComplete = true;
                $('#test_link_button').prop('disabled', false).text('Test Link');
                showMessage('Link appears to be accessible!', 'success');
                
                var previewContent = '<div class="link-test-result success">';
                previewContent += '<strong>✅ Success:</strong> Link appears to be accessible.';
                previewContent += '</div>';
                
                $('#link_preview .preview-content').prepend(previewContent);
            }
        }).catch(function() {
            if (!testComplete) {
                testComplete = true;
                $('#test_link_button').prop('disabled', false).text('Test Link');
                
                var previewContent = '<div class="link-test-result info">';
                previewContent += '<strong>ℹ️ Info:</strong> Cannot verify link due to browser security restrictions. ';
                previewContent += 'The link may still work for downloads.';
                previewContent += '</div>';
                
                $('#link_preview .preview-content').html(previewContent);
            }
        });
    }
    
    // Helper function to show link preview
    function showLinkPreview(url) {
        var previewContent = '<div class="link-info">';
        previewContent += '<strong>URL:</strong> ' + url + '<br>';
        previewContent += '<strong>Domain:</strong> ' + extractDomain(url) + '<br>';
        previewContent += '<strong>Detected Source:</strong> ' + getDetectedSource(url);
        previewContent += '</div>';
        
        $('#link_preview .preview-content').html(previewContent);
        $('#link_preview').slideDown();
    }
    
    // Helper function to auto-detect source
    function autoDetectSource(url) {
        if (url.includes('drive.google.com')) {
            $('#file_source').val('google_drive');
        } else if (url.includes('dropbox.com')) {
            $('#file_source').val('dropbox');
        } else if (url.includes('1drv.ms') || url.includes('onedrive.live.com')) {
            $('#file_source').val('onedrive');
        } else if (url.includes('box.com')) {
            $('#file_source').val('box');
        } else if (url.includes('amazonaws.com') || url.includes('s3.')) {
            $('#file_source').val('aws_s3');
        } else {
            $('#file_source').val('direct');
        }
    }
    
    // Helper function to auto-detect file type
    function autoDetectFileType(url) {
        if ($('#file_type_meta').val()) {
            return; // Don't override existing value
        }
        
        var extension = url.split('.').pop().toLowerCase().split('?')[0];
        var fileType = '';
        
        switch (extension) {
            case 'pdf': fileType = 'PDF'; break;
            case 'doc':
            case 'docx': fileType = 'DOC'; break;
            case 'xls':
            case 'xlsx': fileType = 'XLS'; break;
            case 'ppt':
            case 'pptx': fileType = 'PPT'; break;
            case 'zip': fileType = 'ZIP'; break;
            case 'rar': fileType = 'RAR'; break;
            case 'jpg':
            case 'jpeg': fileType = 'JPG'; break;
            case 'png': fileType = 'PNG'; break;
            case 'gif': fileType = 'GIF'; break;
            case 'mp3': fileType = 'MP3'; break;
            case 'mp4': fileType = 'MP4'; break;
            case 'txt': fileType = 'TXT'; break;
            default: 
                if (extension.length <= 4) {
                    fileType = extension.toUpperCase();
                }
        }
        
        if (fileType) {
            $('#file_type_meta').val(fileType);
        }
    }
    
    // Helper function to extract domain
    function extractDomain(url) {
        try {
            return new URL(url).hostname;
        } catch (e) {
            return 'Invalid URL';
        }
    }
    
    // Helper function to get detected source label
    function getDetectedSource(url) {
        if (url.includes('drive.google.com')) {
            return 'Google Drive';
        } else if (url.includes('dropbox.com')) {
            return 'Dropbox';
        } else if (url.includes('1drv.ms') || url.includes('onedrive.live.com')) {
            return 'OneDrive';
        } else if (url.includes('box.com')) {
            return 'Box';
        } else if (url.includes('amazonaws.com') || url.includes('s3.')) {
            return 'AWS S3';
        } else {
            return 'Direct Link';
        }
    }
    
    // Engagement Flow Admin Functionality
    initEngagementFlowAdmin();
    
    function initEngagementFlowAdmin() {
        // Toggle engagement fields visibility
        $('#engagement_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.engagement-field').removeClass('hidden');
                generateEngagementPreview();
            } else {
                $('.engagement-field').addClass('hidden');
                hideEngagementPreview();
            }
        });
        
        // Initial state
        if (!$('#engagement_enabled').is(':checked')) {
            $('.engagement-field').addClass('hidden');
        }
        
        // Generate preview when engagement fields change
        $('.engagement-field input, .engagement-field textarea').on('input', function() {
            if ($('#engagement_enabled').is(':checked')) {
                generateEngagementPreview();
            }
        });
        
        // Add engagement preview section after the meta box
        if ($('#engagement_enabled').length) {
            addEngagementPreviewSection();
            loadAnalyticsSummary();
        }
    }
    
    function generateEngagementPreview() {
        var fileId = $('#post_ID').val();
        var title = $('#engagement_title').val() || $('#title').val() || 'App Title';
        
        // Get proper base URL that includes WordPress subdirectory
        var currentUrl = window.location.href;
        var adminIndex = currentUrl.indexOf('/wp-admin/');
        var siteBase = '';
        
        if (adminIndex > 0) {
            siteBase = currentUrl.substring(0, adminIndex);
        } else {
            siteBase = window.location.origin;
        }
        
        var baseUrl = siteBase + '/app/' + fileId + '/';
        
        var previewHtml = '<div class="engagement-preview">' +
            '<h4>Engagement Flow Preview</h4>' +
            '<p><strong>Title:</strong> ' + escapeHtml(title) + '</p>' +
            '<div class="step-links">' +
                '<a href="' + baseUrl + 'step1" target="_blank" class="step-link">Step 1: Engagement</a>' +
                '<a href="' + baseUrl + 'step2" target="_blank" class="step-link">Step 2: Intermediate</a>' +
                '<a href="' + baseUrl + 'download" target="_blank" class="step-link">Step 3: Download</a>' +
            '</div>' +
            '<p class="description">URLs will be available after saving the post.</p>' +
        '</div>';
        
        $('#engagement-preview-container').html(previewHtml);
    }
    
    function hideEngagementPreview() {
        $('#engagement-preview-container').empty();
    }
    
    function addEngagementPreviewSection() {
        var previewSection = '<div id="engagement-preview-container"></div>' +
            '<div id="engagement-analytics-container"></div>';
        
        $('.file-upload-meta-box').after(previewSection);
        
        if ($('#engagement_enabled').is(':checked')) {
            generateEngagementPreview();
        }
    }
    
    function loadAnalyticsSummary() {
        var fileId = $('#post_ID').val();
        if (!fileId) return;
        
        // Load analytics data via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_engagement_analytics',
                file_id: fileId,
                nonce: $('#_wpnonce').val()
            },
            success: function(response) {
                if (response.success) {
                    displayAnalyticsSummary(response.data);
                }
            },
            error: function() {
                console.log('Failed to load analytics data');
            }
        });
    }
    
    function displayAnalyticsSummary(data) {
        if (!data || !data.total_events) {
            return;
        }
        
        var summaryHtml = '<div class="engagement-analytics-summary">' +
            '<h4>Engagement Analytics Summary</h4>' +
            '<div class="analytics-stats">' +
                '<div class="analytics-stat"><strong>' + data.total_events + '</strong> Total Events</div>' +
                '<div class="analytics-stat"><strong>' + data.unique_visitors + '</strong> Unique Visitors</div>' +
                '<div class="analytics-stat"><strong>' + data.avg_time_spent + 's</strong> Avg. Time Spent</div>' +
                '<div class="analytics-stat"><strong>' + data.avg_scroll_depth + '%</strong> Avg. Scroll Depth</div>' +
                '<div class="analytics-stat"><strong>' + data.conversion_rate + '%</strong> Conversion Rate</div>' +
            '</div>' +
            '<p class="description">Analytics data from the last 30 days.</p>' +
        '</div>';
        
        $('#engagement-analytics-container').html(summaryHtml);
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
