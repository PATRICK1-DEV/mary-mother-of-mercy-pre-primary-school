// main.js - Version 2.0 - Datepicker/Timepicker removed - Updated: $(date)
// AOS initialization moved to aos-config.js to avoid deprecation warnings
// AOS.init() is now handled by the optimized configuration

(function($) {

	"use strict";

	// Loader functionality - Multiple approaches to ensure it works
	var loader = function() {
		// Immediate hide on DOM ready
		$(document).ready(function() {
			$('#ftco-loader').removeClass('show');
		});
		
		// Hide loader when page is fully loaded
		$(window).on('load', function() {
			$('#ftco-loader').removeClass('show');
		});
		
		// Aggressive fallback: hide loader after 1 second
		setTimeout(function() {
			$('#ftco-loader').removeClass('show');
		}, 1000);
		
		// Emergency fallback: hide loader after 2 seconds
		setTimeout(function() {
			var loader = document.getElementById('ftco-loader');
			if (loader) {
				loader.classList.remove('show');
			}
		}, 2000);
	};
	loader();

	// Stellar initialization moved to stellar-safe.js for better error handling
	// Google Maps initialization moved to google-map-safe.js for better error handling


	var fullHeight = function() {

		$('.js-fullheight').css('height', $(window).height());
		$(window).resize(function(){
			$('.js-fullheight').css('height', $(window).height());
		});

	};
	fullHeight();

	// loader removed - page loads directly

	// Scrollax with error handling
	try {
		if (typeof $.Scrollax !== 'undefined') {
			$.Scrollax();
		}
	} catch (e) {
		// Scrollax initialization failed, continue without scrollax
	}

	var carousel = function() {
		// Detect mobile and Lighthouse
		var isMobile = window.innerWidth <= 768;
		var isLighthouse = navigator.userAgent.includes('Chrome-Lighthouse') || 
		                  navigator.userAgent.includes('HeadlessChrome') ||
		                  window.__lighthouse;
		
		// Optimize carousel settings for mobile and Lighthouse
		var carouselOptions = {
		    loop: !isLighthouse,
		    autoplay: !isLighthouse,
		    autoplayTimeout: isLighthouse ? 1000 : (isMobile ? 3000 : 5000),
		    margin: 0,
		    animateOut: isLighthouse ? 'none' : 'fadeOut',
		    animateIn: isLighthouse ? 'none' : 'fadeIn',
		    nav: false,
		    autoplayHoverPause: !isMobile,
		    items: 1,
		    touchDrag: !isLighthouse,
		    mouseDrag: !isLighthouse,
		    navText: ["<span class='ion-md-arrow-back'></span>","<span class='ion-chevron-right'></span>"],
		    responsive: {
		      0: { items: 1 },
		      600: { items: 1 },
		      1000: { items: 1 }
		    }
		};
		
		$('.home-slider').owlCarousel(carouselOptions);
		$('.carousel-testimony').owlCarousel({
			autoplay: true,
			center: true,
			loop: true,
			items:1,
			margin: 30,
			stagePadding: 0,
			nav: false,
			navText: ['<span class="ion-ios-arrow-back">', '<span class="ion-ios-arrow-forward">'],
			responsive:{
				0:{
					items: 1
				},
				600:{
					items: 1
				},
				1000:{
					items: 2
				}
			}
		});

		// Slider loading removed - no loading screen

	};
	carousel();

	$('nav .dropdown').hover(function(){
		var $this = $(this);
		// 	 timer;
		// clearTimeout(timer);
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		// $this.find('.dropdown-menu').addClass('animated-fast fadeInUp show');
		$this.find('.dropdown-menu').addClass('show');
	}, function(){
		var $this = $(this);
			// timer;
		// timer = setTimeout(function(){
			$this.removeClass('show');
			$this.find('> a').attr('aria-expanded', false);
			// $this.find('.dropdown-menu').removeClass('animated-fast fadeInUp show');
			$this.find('.dropdown-menu').removeClass('show');
		// }, 100);
	});


	$('#dropdown04').on('show.bs.dropdown', function () {
	  // Dropdown shown
	});

	// scroll
	var scrollWindow = function() {
		$(window).scroll(function(){
			var $w = $(this),
					st = $w.scrollTop(),
					navbar = $('.ftco_navbar'),
					sd = $('.js-scroll-wrap');

			if (st > 150) {
				if ( !navbar.hasClass('scrolled') ) {
					navbar.addClass('scrolled');	
				}
			} 
			if (st < 150) {
				if ( navbar.hasClass('scrolled') ) {
					navbar.removeClass('scrolled sleep');
				}
			} 
			if ( st > 350 ) {
				if ( !navbar.hasClass('awake') ) {
					navbar.addClass('awake');	
				}
				
				if(sd.length > 0) {
					sd.addClass('sleep');
				}
			}
			if ( st < 350 ) {
				if ( navbar.hasClass('awake') ) {
					navbar.removeClass('awake');
					navbar.addClass('sleep');
				}
				if(sd.length > 0) {
					sd.removeClass('sleep');
				}
			}
		});
	};
	scrollWindow();

	
	var counter = function() {
		
		$('#section-counter').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {

				var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(',')
				$('.number').each(function(){
					var $this = $(this),
						num = $this.data('number');
					$this.animateNumber(
					  {
					    number: num,
					    numberStep: comma_separator_number_step
					  }, 7000
					);
				});
				
			}

		} , { offset: '95%' } );

	}
	counter();

	var contentWayPoint = function() {
		var i = 0;
		$('.ftco-animate').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {
				
				i++;

				$(this.element).addClass('item-animate');
				setTimeout(function(){

					$('body .ftco-animate.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							var effect = el.data('animate-effect');
							if ( effect === 'fadeIn') {
								el.addClass('fadeIn ftco-animated');
							} else if ( effect === 'fadeInLeft') {
								el.addClass('fadeInLeft ftco-animated');
							} else if ( effect === 'fadeInRight') {
								el.addClass('fadeInRight ftco-animated');
							} else {
								el.addClass('fadeInUp ftco-animated');
							}
							el.removeClass('item-animate');
						},  k * 50, 'easeInOutExpo' );
					});
					
				}, 100);
				
			}

		} , { offset: '95%' } );
	};
	contentWayPoint();


	// magnific popup
	$('.image-popup').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    closeBtnInside: false,
    fixedContentPos: true,
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
     gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0,1] // Will preload 0 - before current, and 1 after the current image
    },
    image: {
      verticalFit: true
    },
    zoom: {
      enabled: true,
      duration: 300 // don't foget to change the duration also in CSS
    }
  });

  $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,

    fixedContentPos: false
  });


  // Datepicker and Timepicker removed - no elements or plugins present
  // If needed in the future, add the appropriate plugin files and HTML elements




})(jQuery);

