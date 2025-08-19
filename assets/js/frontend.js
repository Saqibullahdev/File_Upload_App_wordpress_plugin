jQuery(document).ready(function($) {
    'use strict';
    
    // Track file downloads
    $('.download-btn').on('click', function(e) {
        var fileId = $(this).data('file-id');
        var $downloadBtn = $(this);
        
        if (fileId) {
            // Track download via AJAX
            $.ajax({
                url: file_upload_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'track_download',
                    file_id: fileId,
                    nonce: file_upload_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update download count display if it exists
                        var $countElement = $downloadBtn.closest('.file-item').find('.download-count');
                        if ($countElement.length) {
                            $countElement.text('Downloads: ' + response.data.new_count);
                        }
                        
                        // Add visual feedback
                        $downloadBtn.addClass('downloaded');
                        setTimeout(function() {
                            $downloadBtn.removeClass('downloaded');
                        }, 2000);
                    }
                },
                error: function() {
                    console.log('Failed to track download');
                }
            });
        }
    });
    
    // Enhanced filter functionality
    var $filterForm = $('.filter-form');
    var $fileList = $('.file-upload-list');
    
    // Auto-submit filter on change
    $filterForm.find('select').on('change', function() {
        if ($(this).closest('form').hasClass('auto-submit')) {
            submitFilter();
        }
    });
    
    // Add auto-submit toggle
    if ($filterForm.length) {
        var autoSubmitHtml = '<label class="auto-submit-toggle">' +
            '<input type="checkbox" class="auto-submit-checkbox"> Auto-filter' +
            '</label>';
        $filterForm.find('.filter-row').append('<div class="filter-group auto-submit-wrapper">' + autoSubmitHtml + '</div>');
        
        $('.auto-submit-checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $filterForm.addClass('auto-submit');
            } else {
                $filterForm.removeClass('auto-submit');
            }
        });
    }
    
    // Submit filter with loading state
    function submitFilter() {
        $fileList.addClass('loading');
        
        // Add loading spinner
        if (!$('.loading-spinner').length) {
            $fileList.after('<div class="loading-spinner"></div>');
        }
        
        $filterForm.submit();
    }
    
    // AJAX filter functionality (optional enhancement)
    function initAjaxFilters() {
        $filterForm.on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            $fileList.addClass('loading');
            
            $.ajax({
                url: window.location.href.split('?')[0],
                type: 'GET',
                data: formData,
                success: function(response) {
                    var $newContent = $(response).find('.file-upload-list');
                    var $newPagination = $(response).find('.file-upload-pagination');
                    
                    if ($newContent.length) {
                        $fileList.html($newContent.html());
                        
                        // Update pagination
                        if ($newPagination.length) {
                            $('.file-upload-pagination').html($newPagination.html());
                        } else {
                            $('.file-upload-pagination').empty();
                        }
                        
                        // Re-initialize download tracking for new items
                        initDownloadTracking();
                        
                        // Update URL without reload
                        var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        if (formData) {
                            newUrl += '?' + formData;
                        }
                        history.pushState({}, '', newUrl);
                    }
                },
                error: function() {
                    console.log('Filter request failed');
                },
                complete: function() {
                    $fileList.removeClass('loading');
                    $('.loading-spinner').remove();
                }
            });
        });
    }
    
    // Initialize download tracking
    function initDownloadTracking() {
        $('.download-btn').off('click.download').on('click.download', function(e) {
            var fileId = $(this).data('file-id');
            var $downloadBtn = $(this);
            
            if (fileId) {
                $.ajax({
                    url: file_upload_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'track_download',
                        file_id: fileId,
                        nonce: file_upload_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            var $countElement = $downloadBtn.closest('.file-item').find('.download-count');
                            if ($countElement.length) {
                                $countElement.text('Downloads: ' + response.data.new_count);
                            }
                            
                            $downloadBtn.addClass('downloaded');
                            setTimeout(function() {
                                $downloadBtn.removeClass('downloaded');
                            }, 2000);
                        }
                    }
                });
            }
        });
    }
    
    // Search functionality
    var searchHtml = '<div class="filter-group search-group">' +
        '<label for="search_files">Search Files:</label>' +
        '<input type="text" id="search_files" name="search" placeholder="Search titles and descriptions..." value="' + 
        (new URLSearchParams(window.location.search).get('search') || '') + '">' +
        '</div>';
    
    if ($filterForm.find('.filter-row').length) {
        $filterForm.find('.filter-row').append(searchHtml);
    }
    
    // Search with debounce
    var searchTimeout;
    $('#search_files').on('input', function() {
        var searchTerm = $(this).val();
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if ($filterForm.hasClass('auto-submit') || searchTerm.length === 0) {
                performSearch(searchTerm);
            }
        }, 500);
    });
    
    function performSearch(searchTerm) {
        if (searchTerm.length === 0) {
            // Clear search
            $filterForm.submit();
            return;
        }
        
        // Highlight search terms in results
        highlightSearchTerms(searchTerm);
    }
    
    function highlightSearchTerms(searchTerm) {
        $('.file-item').each(function() {
            var $item = $(this);
            var title = $item.find('.file-title').text();
            var excerpt = $item.find('.file-excerpt').text();
            
            var titleMatch = title.toLowerCase().includes(searchTerm.toLowerCase());
            var excerptMatch = excerpt.toLowerCase().includes(searchTerm.toLowerCase());
            
            if (titleMatch || excerptMatch) {
                $item.addClass('search-match');
                
                // Highlight matching text
                if (titleMatch) {
                    highlightText($item.find('.file-title a'), searchTerm);
                }
                if (excerptMatch) {
                    highlightText($item.find('.file-excerpt'), searchTerm);
                }
            } else {
                $item.removeClass('search-match');
            }
        });
    }
    
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Infinite scroll functionality
    function initInfiniteScroll() {
        var loading = false;
        var page = 2;
        var maxPages = parseInt($('.file-upload-pagination').data('max-pages')) || 1;
        
        $(window).on('scroll', function() {
            if (loading || page > maxPages) return;
            
            var scrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();
            var documentHeight = $(document).height();
            
            if (scrollTop + windowHeight >= documentHeight - 200) {
                loading = true;
                loadMoreFiles(page);
            }
        });
        
        function loadMoreFiles(pageNum) {
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('paged', pageNum);
            
            $.ajax({
                url: currentUrl.toString(),
                type: 'GET',
                success: function(response) {
                    var $newItems = $(response).find('.file-item');
                    
                    if ($newItems.length) {
                        $fileList.append($newItems);
                        initDownloadTracking();
                        page++;
                        
                        // Add fade-in animation
                        $newItems.hide().fadeIn(500);
                    }
                },
                complete: function() {
                    loading = false;
                }
            });
        }
    }
    
    // Lazy loading for images
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            $('.file-thumbnail img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }
    }
    
    // File preview modal
    function initFilePreview() {
        $('<div id="file-preview-modal" class="modal">' +
            '<div class="modal-content">' +
                '<span class="close">&times;</span>' +
                '<div class="modal-body"></div>' +
            '</div>' +
        '</div>').appendTo('body');
        
        $('.file-title a').on('click', function(e) {
            var fileUrl = $(this).closest('.file-item').find('.download-btn').attr('href');
            var fileType = $(this).closest('.file-item').find('.file-type').text().toLowerCase();
            
            if (fileType.includes('image') || fileType.includes('pdf')) {
                e.preventDefault();
                showFilePreview(fileUrl, fileType);
            }
        });
        
        function showFilePreview(url, type) {
            var content = '';
            
            if (type.includes('image')) {
                content = '<img src="' + url + '" style="max-width: 100%; height: auto;">';
            } else if (type.includes('pdf')) {
                content = '<iframe src="' + url + '" style="width: 100%; height: 600px;"></iframe>';
            }
            
            $('#file-preview-modal .modal-body').html(content);
            $('#file-preview-modal').show();
        }
        
        // Close modal
        $('.close, #file-preview-modal').on('click', function(e) {
            if (e.target === this) {
                $('#file-preview-modal').hide();
            }
        });
    }
    
    // Add custom CSS for enhanced functionality
    $('<style>').text(`
        .downloaded {
            background-color: #198754 !important;
            transform: scale(0.95);
            transition: all 0.2s ease;
        }
        
        .search-match {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.25);
        }
        
        mark {
            background-color: #fff3cd;
            padding: 1px 2px;
            border-radius: 2px;
        }
        
        .auto-submit-toggle {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .lazy {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        
        .close:hover {
            color: black;
        }
        
        @media (max-width: 768px) {
            .search-group {
                order: -1;
                flex-basis: 100%;
            }
            
            .auto-submit-wrapper {
                flex-basis: 100%;
                text-align: center;
            }
        }
    `).appendTo('head');
    
    // Initialize all features
    initDownloadTracking();
    initLazyLoading();
    initFilePreview();
    
    // Uncomment the following lines to enable additional features:
    // initAjaxFilters();
    // initInfiniteScroll();
    
    console.log('File Upload App frontend initialized');
});

// Basic File Upload App functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('File Upload App frontend initialized');
    
    // Basic filter functionality
    const filterItems = document.querySelectorAll('.filter-item');
    filterItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all siblings
            const siblings = this.parentNode.querySelectorAll('.filter-item');
            siblings.forEach(sib => sib.classList.remove('active'));
            
            // Add active class to current item
            this.classList.add('active');
        });
    });
});
