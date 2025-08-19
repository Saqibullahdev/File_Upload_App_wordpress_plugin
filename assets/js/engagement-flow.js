jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    let analyticsData = {
        startTime: Date.now(),
        scrollDepth: 0,
        timeSpent: 0,
        interactions: 0,
        currentStep: null,
        fileId: null
    };
    
    let scrollProgress = 0;
    let isScrollTracking = false;
    let sessionActive = true;
    
    // Initialize engagement flow
    function initEngagementFlow() {
        const container = $('.engagement-flow-container');
        if (!container.length) return;
        
        analyticsData.fileId = container.data('file-id');
        analyticsData.currentStep = getCurrentStep();
        
        // Add scroll progress indicator
        $('body').prepend('<div class="scroll-progress"></div>');
        
        // Initialize based on current step
        switch (analyticsData.currentStep) {
            case 'step1':
                initStep1();
                break;
            case 'step2':
                initStep2();
                break;
            case 'download':
                initDownloadPage();
                break;
        }
        
        // Start analytics tracking
        startAnalyticsTracking();
        
        // Track page visibility
        trackPageVisibility();
        
        // Track interactions
        trackInteractions();
    }
    
    // Get current step from URL or container class
    function getCurrentStep() {
        const container = $('.engagement-flow-container');
        if (container.hasClass('step-step1')) return 'step1';
        if (container.hasClass('step-step2')) return 'step2';
        if (container.hasClass('step-download')) return 'download';
        
        // Fallback: check URL
        const url = window.location.pathname;
        if (url.includes('/step1')) return 'step1';
        if (url.includes('/step2')) return 'step2';
        if (url.includes('/download')) return 'download';
        
        return 'unknown';
    }
    
    // Initialize Step 1 functionality
    function initStep1() {
        trackScrollDepth();
        enableScrollBasedContinue();
        animateOnScroll();
        trackScreenshotViews();
    }
    
    // Track scroll depth and enable continue button
    function enableScrollBasedContinue() {
        const continueSection = $('.continue-section');
        const continueBtn = $('.continue-btn');
        let scrollThreshold = 70; // Percentage of page scrolled
        
        $(window).on('scroll.step1', function() {
            const scrollTop = $(window).scrollTop();
            const docHeight = $(document).height();
            const winHeight = $(window).height();
            const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
            
            // Update scroll progress bar
            $('.scroll-progress').css('width', scrollPercent + '%');
            
            // Track maximum scroll depth
            analyticsData.scrollDepth = Math.max(analyticsData.scrollDepth, scrollPercent);
            
            // Enable continue button when user scrolls enough
            if (scrollPercent >= scrollThreshold) {
                continueSection.css('opacity', '1');
                continueBtn.prop('disabled', false);
                
                // Hide scroll indicator
                $('.scroll-indicator').fadeOut();
                
                // Track engagement milestone
                trackEvent('scroll_milestone', {
                    percentage: scrollPercent,
                    threshold_reached: scrollThreshold
                });
            }
        });
        
        // Handle continue button click
        continueBtn.on('click', function() {
            if (!$(this).prop('disabled')) {
                trackEvent('continue_clicked', {
                    from_step: 'step1',
                    scroll_depth: analyticsData.scrollDepth
                });
                
                navigateToStep('step2');
            }
        });
    }
    
    // Initialize Step 2 functionality
    function initStep2() {
        trackFeatureViews();
        setupStep2Continue();
        animateCounters();
    }
    
    // Setup Step 2 continue functionality
    function setupStep2Continue() {
        const continueBtn = $('.continue-btn.primary');
        
        // Add slight delay to encourage reading
        setTimeout(function() {
            continueBtn.prop('disabled', false);
        }, 3000); // 3 seconds delay
        
        continueBtn.on('click', function() {
            trackEvent('continue_clicked', {
                from_step: 'step2',
                time_on_page: Date.now() - analyticsData.startTime
            });
            
            navigateToStep('download');
        });
    }
    
    // Initialize download page
    function initDownloadPage() {
        animateSuccessElements();
        setupFinalDownload();
        setupSocialSharing();
        trackDownloadReady();
    }
    
    // Setup final download tracking
    function setupFinalDownload() {
        $('.download-btn.final-download').on('click', function(e) {
            const fileId = $(this).data('file-id');
            
            trackEvent('final_download_clicked', {
                file_id: fileId,
                total_time: Date.now() - analyticsData.startTime,
                total_interactions: analyticsData.interactions
            });
            
            // Track download in the main system
            trackFileDownload(fileId);
            
            // Show success feedback
            showDownloadFeedback();
        });
    }
    
    // Navigate between steps
    function navigateToStep(step) {
        const fileId = analyticsData.fileId;
        // Get the current site URL properly (handles subdirectories)
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/');
        let siteBase = '';
        
        // Find the base path by looking for 'app' in the path
        const appIndex = pathParts.indexOf('app');
        if (appIndex > 0) {
            siteBase = pathParts.slice(0, appIndex).join('/');
        }
        
        const baseUrl = window.location.origin + siteBase + '/app/' + fileId + '/';
        
        // Add loading state
        $('.engagement-flow-container').addClass('loading');
        
        // Navigate to next step
        window.location.href = baseUrl + step;
    }
    
    // Track scroll depth continuously
    function trackScrollDepth() {
        let maxScroll = 0;
        
        $(window).on('scroll.depth', function() {
            const scrollTop = $(window).scrollTop();
            const docHeight = $(document).height();
            const winHeight = $(window).height();
            const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
            
            if (scrollPercent > maxScroll) {
                maxScroll = scrollPercent;
                
                // Track scroll milestones
                if (maxScroll >= 25 && !analyticsData.scroll25) {
                    analyticsData.scroll25 = true;
                    trackEvent('scroll_milestone', { percentage: 25 });
                }
                if (maxScroll >= 50 && !analyticsData.scroll50) {
                    analyticsData.scroll50 = true;
                    trackEvent('scroll_milestone', { percentage: 50 });
                }
                if (maxScroll >= 75 && !analyticsData.scroll75) {
                    analyticsData.scroll75 = true;
                    trackEvent('scroll_milestone', { percentage: 75 });
                }
                if (maxScroll >= 90 && !analyticsData.scroll90) {
                    analyticsData.scroll90 = true;
                    trackEvent('scroll_milestone', { percentage: 90 });
                }
            }
        });
    }
    
    // Track screenshot views
    function trackScreenshotViews() {
        $('.screenshot-image').each(function(index) {
            const $img = $(this);
            
            $img.on('click', function() {
                trackEvent('screenshot_viewed', {
                    screenshot_index: index,
                    image_src: $img.attr('src')
                });
            });
        });
    }
    
    // Track feature list views
    function trackFeatureViews() {
        $('.features-list li').each(function(index) {
            const $feature = $(this);
            
            // Track when feature comes into view
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        trackEvent('feature_viewed', {
                            feature_index: index,
                            feature_text: $feature.text()
                        });
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe($feature[0]);
        });
    }
    
    // Animate elements on scroll
    function animateOnScroll() {
        const animatedElements = $('.screenshot-image, .app-features, .trust-indicators');
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('animated fadeInUp');
                }
            });
        });
        
        animatedElements.each(function() {
            observer.observe(this);
        });
    }
    
    // Animate counters and progress
    function animateCounters() {
        const downloadCount = $('.download-count');
        
        if (downloadCount.length) {
            const targetValue = parseInt(downloadCount.text().replace(/[^\d]/g, ''));
            
            $({ counter: 0 }).animate({ counter: targetValue }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    downloadCount.text(Math.ceil(this.counter).toLocaleString() + '+ downloads');
                }
            });
        }
    }
    
    // Animate success elements
    function animateSuccessElements() {
        const successIcon = $('.success-icon');
        const downloadBtn = $('.download-btn.final-download');
        
        setTimeout(function() {
            successIcon.addClass('animated bounceIn');
        }, 500);
        
        setTimeout(function() {
            downloadBtn.addClass('animated pulse');
        }, 1500);
    }
    
    // Setup social sharing
    function setupSocialSharing() {
        $('.share-btn').on('click', function(e) {
            e.preventDefault();
            
            const platform = $(this).data('share');
            // Get proper URL for sharing (always point to step1)
            const currentPath = window.location.pathname;
            const pathParts = currentPath.split('/');
            let siteBase = '';
            
            const appIndex = pathParts.indexOf('app');
            if (appIndex > 0) {
                siteBase = pathParts.slice(0, appIndex).join('/');
            }
            
            const url = window.location.origin + siteBase + '/app/' + analyticsData.fileId + '/step1';
            const title = $('.engagement-title').first().text() || 'Check out this amazing app!';
            
            let shareUrl = '';
            
            switch (platform) {
                case 'twitter':
                    shareUrl = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
                    break;
                case 'facebook':
                    shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, 'share', 'width=600,height=400');
                
                trackEvent('social_share', {
                    platform: platform,
                    url: url
                });
            }
        });
    }
    
    // Start analytics tracking
    function startAnalyticsTracking() {
        // Track time spent every 10 seconds
        setInterval(function() {
            if (sessionActive) {
                analyticsData.timeSpent = Date.now() - analyticsData.startTime;
                
                // Send analytics data every minute
                if (analyticsData.timeSpent % 60000 < 10000) {
                    sendAnalyticsData();
                }
            }
        }, 10000);
        
        // Track when user leaves page
        $(window).on('beforeunload', function() {
            sendAnalyticsData();
        });
        
        // Track page hidden/visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                sessionActive = false;
                sendAnalyticsData();
            } else {
                sessionActive = true;
                analyticsData.startTime = Date.now(); // Reset start time when returning
            }
        });
    }
    
    // Track page visibility
    function trackPageVisibility() {
        let isVisible = true;
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && isVisible) {
                isVisible = false;
                trackEvent('page_hidden', {
                    time_visible: Date.now() - analyticsData.startTime
                });
            } else if (!document.hidden && !isVisible) {
                isVisible = true;
                trackEvent('page_visible', {
                    time_hidden: Date.now() - analyticsData.startTime
                });
            }
        });
    }
    
    // Track general interactions
    function trackInteractions() {
        // Track clicks
        $(document).on('click', function(e) {
            analyticsData.interactions++;
            
            const target = $(e.target);
            let elementType = target.prop('tagName').toLowerCase();
            
            if (target.is('button')) elementType = 'button';
            if (target.is('a')) elementType = 'link';
            if (target.is('img')) elementType = 'image';
            
            trackEvent('interaction', {
                type: 'click',
                element: elementType,
                class: target.attr('class') || '',
                id: target.attr('id') || ''
            });
        });
        
        // Track form interactions
        $('input, textarea, select').on('focus', function() {
            trackEvent('interaction', {
                type: 'form_focus',
                element: $(this).prop('tagName').toLowerCase(),
                name: $(this).attr('name') || ''
            });
        });
    }
    
    // Track specific events
    function trackEvent(eventType, eventData) {
        analyticsData.events = analyticsData.events || [];
        analyticsData.events.push({
            timestamp: Date.now(),
            type: eventType,
            data: eventData
        });
        
        // Send immediately for important events
        const importantEvents = ['continue_clicked', 'final_download_clicked', 'social_share'];
        if (importantEvents.includes(eventType)) {
            sendAnalyticsData();
        }
    }
    
    // Track download ready event
    function trackDownloadReady() {
        trackEvent('download_page_loaded', {
            total_journey_time: Date.now() - analyticsData.startTime
        });
    }
    
    // Send analytics data to server
    function sendAnalyticsData() {
        if (!analyticsData.fileId || !analyticsData.events?.length) {
            return;
        }
        
        const data = {
            action: 'engagement_analytics',
            file_id: analyticsData.fileId,
            nonce: engagement_flow_ajax.nonce,
            event_type: 'batch_update',
            event_data: {
                step: analyticsData.currentStep,
                time_spent: analyticsData.timeSpent,
                scroll_depth: analyticsData.scrollDepth,
                interactions: analyticsData.interactions,
                events: analyticsData.events,
                session_data: {
                    start_time: analyticsData.startTime,
                    end_time: Date.now(),
                    user_agent: navigator.userAgent,
                    screen_resolution: screen.width + 'x' + screen.height,
                    viewport_size: $(window).width() + 'x' + $(window).height()
                }
            }
        };
        
        $.ajax({
            url: engagement_flow_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                // Clear sent events
                analyticsData.events = [];
            },
            error: function(xhr, status, error) {
                console.log('Analytics tracking failed:', error);
            }
        });
    }
    
    // Track file download (integrate with existing system)
    function trackFileDownload(fileId) {
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
                    // Update download count display
                    $('.download-count').text(response.data.new_count + '+ downloads');
                }
            }
        });
    }
    
    // Show download feedback
    function showDownloadFeedback() {
        const feedback = $('<div class="download-feedback">' +
            '<div class="feedback-content">' +
                '<div class="feedback-icon">✓</div>' +
                '<div class="feedback-text">Download started!</div>' +
            '</div>' +
        '</div>');
        
        $('body').append(feedback);
        
        setTimeout(function() {
            feedback.addClass('show');
        }, 100);
        
        setTimeout(function() {
            feedback.removeClass('show');
            setTimeout(function() {
                feedback.remove();
            }, 300);
        }, 3000);
    }
    
    // Mobile-specific optimizations
    function initMobileOptimizations() {
        // Prevent zoom on double tap for better UX
        let lastTouchEnd = 0;
        $(document).on('touchend', function(event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        });
        
        // Optimize scroll performance on mobile
        let ticking = false;
        $(window).on('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateScrollElements);
                ticking = true;
            }
        });
        
        function updateScrollElements() {
            // Update progress bar
            const scrollTop = $(window).scrollTop();
            const docHeight = $(document).height();
            const winHeight = $(window).height();
            const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
            
            $('.scroll-progress').css('width', scrollPercent + '%');
            ticking = false;
        }
        
        // Add touch feedback
        $('.continue-btn, .download-btn, .share-btn').on('touchstart', function() {
            $(this).addClass('touch-active');
        }).on('touchend touchcancel', function() {
            $(this).removeClass('touch-active');
        });
    }
    
    // Initialize everything
    initEngagementFlow();
    initMobileOptimizations();
    
    // Add styles for feedback and touch states
    $('<style>').text(`
        .download-feedback {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 10000;
        }
        
        .download-feedback.show {
            transform: translateX(0);
        }
        
        .feedback-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feedback-icon {
            font-size: 18px;
        }
        
        .touch-active {
            transform: scale(0.95);
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .download-feedback {
                top: 10px;
                right: 10px;
                left: 10px;
                transform: translateY(-100%);
            }
            
            .download-feedback.show {
                transform: translateY(0);
            }
        }
    `).appendTo('head');
    
    console.log('Engagement flow initialized for step:', analyticsData.currentStep);
});
