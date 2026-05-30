/**
 * MyAkad Template Base JavaScript
 * Global scripts untuk semua template undangan
 * Template menggunakan fungsi-fungsi yang tersedia di window.MyAkad
 */

(function() {
  'use strict';

  // Global namespace
  window.MyAkad = window.MyAkad || {};

  /**
   * Get template configuration from meta tag or window.TEMPLATE_CONFIG
   */
  MyAkad.getConfig = function(key, defaultValue = null) {
    // Try to load from meta tag first (CSP-safe)
    if (!window.TEMPLATE_CONFIG) {
      const metaTag = document.querySelector('meta[name="template-config"]');
      console.log('Loading config from meta tag:', !!metaTag);
      
      if (metaTag) {
        try {
          let content = metaTag.getAttribute('content');
          const encoding = metaTag.getAttribute('data-encoding');
          
          console.log('Encoding attribute:', encoding);
          console.log('Meta tag content (first 200 chars):', content?.substring(0, 200));
          
          // Always try to decode as base64 first (our default encoding)
          try {
            console.log('Attempting base64 decode...');
            const decoded = atob(content);
            console.log('Base64 decoded successfully (first 200 chars):', decoded.substring(0, 200));
            content = decoded;
          } catch (base64Error) {
            console.log('Not base64 encoded, using as-is');
          }
          
          window.TEMPLATE_CONFIG = JSON.parse(content);
          console.log('Config parsed successfully:', window.TEMPLATE_CONFIG);
        } catch (e) {
          console.error('Failed to parse template config:', e);
          console.error('Content was:', metaTag.getAttribute('content'));
          window.TEMPLATE_CONFIG = {};
        }
      } else {
        console.warn('No meta[name="template-config"] found');
        window.TEMPLATE_CONFIG = {};
      }
    }
    
    if (!key) return window.TEMPLATE_CONFIG;
    
    const keys = key.split('.');
    let value = window.TEMPLATE_CONFIG;
    
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        return defaultValue;
      }
    }
    
    return value;
  };

  /**
   * Countdown Timer
   * Usage: MyAkad.countdown('#countdown', '2025-06-14T09:00:00')
   */
  MyAkad.countdown = function(selector, targetDate) {
    const element = document.querySelector(selector);
    
    if (!element) {
      console.warn('Countdown element not found:', selector);
      return;
    }
    
    const target = new Date(targetDate).getTime();
    
    if (isNaN(target)) {
      console.error('Invalid date format:', targetDate);
      return;
    }
    
    function update() {
      const now = new Date().getTime();
      const distance = target - now;
      
      if (distance < 0) {
        // Event has passed
        const daysEl = element.querySelector('.countdown-days, .days');
        const hoursEl = element.querySelector('.countdown-hours, .hours');
        const minutesEl = element.querySelector('.countdown-minutes, .minutes');
        const secondsEl = element.querySelector('.countdown-seconds, .seconds');
        
        if (daysEl) daysEl.textContent = '00';
        if (hoursEl) hoursEl.textContent = '00';
        if (minutesEl) minutesEl.textContent = '00';
        if (secondsEl) secondsEl.textContent = '00';
        return;
      }
      
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
      // Update countdown elements
      const daysEl = element.querySelector('.countdown-days, .days');
      const hoursEl = element.querySelector('.countdown-hours, .hours');
      const minutesEl = element.querySelector('.countdown-minutes, .minutes');
      const secondsEl = element.querySelector('.countdown-seconds, .seconds');
      
      if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
      if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
      if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
      if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
    }
    
    // Initial update
    update();
    
    // Update every second
    setInterval(update, 1000);
  };

  /**
   * Music Player
   * Usage: MyAkad.musicPlayer('#audio-element', '#toggle-button')
   */
  MyAkad.musicPlayer = function(audioSelector, buttonSelector) {
    const audio = document.querySelector(audioSelector);
    const button = document.querySelector(buttonSelector);
    
    if (!audio || !button) return;
    
    let isPlaying = false;
    
    button.addEventListener('click', function() {
      if (isPlaying) {
        audio.pause();
        button.classList.remove('playing');
        button.innerHTML = '<i class="fas fa-volume-up text-2xl"></i>';
      } else {
        audio.play();
        button.classList.add('playing');
        button.innerHTML = '<i class="fas fa-pause text-2xl"></i>';
      }
      isPlaying = !isPlaying;
    });
    
    // Auto-play on user interaction (if configured)
    const autoPlay = MyAkad.getConfig('music.autoPlay', false);
    if (autoPlay) {
      document.addEventListener('click', function() {
        if (!isPlaying) {
          audio.play();
          button.classList.add('playing');
          button.innerHTML = '<i class="fas fa-pause text-2xl"></i>';
          isPlaying = true;
        }
      }, { once: true });
    }
  };

  /**
   * Opening Screen
   * Usage: MyAkad.openingScreen('#opening', '#open-button')
   */
  MyAkad.openingScreen = function(screenSelector, buttonSelector) {
    const screen = document.querySelector(screenSelector);
    const button = document.querySelector(buttonSelector);
    
    console.log('Opening screen setup:', {
      screenSelector,
      buttonSelector,
      screen: !!screen,
      button: !!button
    });
    
    if (!screen || !button) {
      console.error('Opening screen or button not found!');
      return;
    }
    
    button.addEventListener('click', function() {
      console.log('Opening button clicked!');
      screen.style.opacity = '0';
      screen.style.visibility = 'hidden';
      screen.style.transition = 'opacity 0.5s ease-out, visibility 0.5s ease-out';
      
      setTimeout(() => {
        screen.style.display = 'none';
      }, 500);
      
      // Start music if configured
      const musicSelector = MyAkad.getConfig('music.audioSelector', '#background-music');
      const audio = document.querySelector(musicSelector);
      if (audio) {
        audio.play().catch(e => console.log('Audio autoplay prevented:', e));
      }
    });
    
    console.log('Opening screen event listener attached');
  };

  /**
   * Smooth Scroll to Section
   * Usage: MyAkad.scrollTo('#section-id')
   */
  MyAkad.scrollTo = function(selector, offset = 0) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    const top = element.getBoundingClientRect().top + window.pageYOffset + offset;
    window.scrollTo({ top, behavior: 'smooth' });
  };

  /**
   * Gallery Lightbox
   * Usage: MyAkad.gallery('.gallery-item')
   */
  MyAkad.gallery = function(itemSelector) {
    const items = document.querySelectorAll(itemSelector);
    if (items.length === 0) return;
    
    // Create lightbox
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.style.cssText = `
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.9);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.style.cssText = 'max-width: 90%; max-height: 90%; object-fit: contain;';
    lightbox.appendChild(img);
    document.body.appendChild(lightbox);
    
    // Add click handlers
    items.forEach(item => {
      item.addEventListener('click', function() {
        const imgSrc = this.querySelector('img')?.src || this.dataset.src;
        if (imgSrc) {
          img.src = imgSrc;
          lightbox.style.display = 'flex';
        }
      });
    });
    
    lightbox.addEventListener('click', function() {
      lightbox.style.display = 'none';
    });
  };

  /**
   * RSVP Form Handler
   * Usage: MyAkad.rsvpForm('#rsvp-form', '/api/rsvp')
   */
  MyAkad.rsvpForm = function(formSelector, endpoint) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData(form);
      const data = Object.fromEntries(formData);
      
      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify(data)
        });
        
        if (response.ok) {
          alert('Terima kasih! RSVP Anda telah diterima.');
          form.reset();
        } else {
          alert('Maaf, terjadi kesalahan. Silakan coba lagi.');
        }
      } catch (error) {
        console.error('RSVP error:', error);
        alert('Maaf, terjadi kesalahan. Silakan coba lagi.');
      }
    });
  };

  /**
   * Wishes/Comments Handler
   * Usage: MyAkad.wishesForm('#wishes-form', '/api/wishes', '#wishes-list')
   */
  MyAkad.wishesForm = function(formSelector, endpoint, listSelector) {
    const form = document.querySelector(formSelector);
    const list = document.querySelector(listSelector);
    
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData(form);
      const data = Object.fromEntries(formData);
      
      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify(data)
        });
        
        if (response.ok) {
          const result = await response.json();
          
          // Add to list if element exists
          if (list && result.wish) {
            const item = document.createElement('div');
            item.className = 'wish-item card mb-2';
            item.innerHTML = `
              <strong>${result.wish.name}</strong>
              <p>${result.wish.message}</p>
              <small class="text-muted">${result.wish.created_at}</small>
            `;
            list.prepend(item);
          }
          
          alert('Terima kasih atas ucapan Anda!');
          form.reset();
        } else {
          alert('Maaf, terjadi kesalahan. Silakan coba lagi.');
        }
      } catch (error) {
        console.error('Wishes error:', error);
        alert('Maaf, terjadi kesalahan. Silakan coba lagi.');
      }
    });
  };

  /**
   * Copy to Clipboard
   * Usage: MyAkad.copyToClipboard('#copy-button', 'text-to-copy')
   */
  MyAkad.copyToClipboard = function(buttonSelector, text) {
    const button = document.querySelector(buttonSelector);
    if (!button) return;
    
    button.addEventListener('click', async function() {
      try {
        await navigator.clipboard.writeText(text);
        const originalText = button.textContent;
        button.textContent = '✓ Tersalin!';
        setTimeout(() => {
          button.textContent = originalText;
        }, 2000);
      } catch (error) {
        console.error('Copy failed:', error);
        alert('Gagal menyalin. Silakan salin manual.');
      }
    });
  };

  /**
   * Animate on Scroll
   * Usage: MyAkad.animateOnScroll('.animate-fade-in')
   */
  MyAkad.animateOnScroll = function(selector) {
    const elements = document.querySelectorAll(selector);
    if (elements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });
    
    elements.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
      observer.observe(el);
    });
  };

  /**
   * Initialize all features based on template config
   */
  MyAkad.init = function() {
    console.log('MyAkad Template System initialized');
    
    // Load config from meta tag
    const config = MyAkad.getConfig();
    console.log('TEMPLATE_CONFIG:', config);
    
    // Debug: Check if elements exist
    console.log('DOM elements check:', {
      openingScreen: !!document.querySelector('#opening-screen'),
      openButton: !!document.querySelector('#open-invitation'),
      musicToggle: !!document.querySelector('#music-toggle'),
      backgroundMusic: !!document.querySelector('#background-music')
    });
    
    // Get features from config
    const features = config.features || {};
    console.log('Features object:', features);
    
    // Music Player
    if (features.music?.enabled && features.music?.audioSelector && features.music?.buttonSelector) {
      console.log('Initializing music player');
      MyAkad.musicPlayer(features.music.audioSelector, features.music.buttonSelector);
    }
    
    // Opening Screen
    console.log('Opening config check:', {
      hasOpening: !!features.opening,
      enabled: features.opening?.enabled,
      screenSelector: features.opening?.screenSelector,
      buttonSelector: features.opening?.buttonSelector,
      fullConfig: features.opening
    });
    
    if (features.opening?.enabled && features.opening?.screenSelector && features.opening?.buttonSelector) {
      console.log('Initializing opening screen with:', features.opening);
      MyAkad.openingScreen(features.opening.screenSelector, features.opening.buttonSelector);
    } else {
      console.log('Opening screen NOT initialized. Reason:', {
        enabled: features.opening?.enabled,
        hasScreenSelector: !!features.opening?.screenSelector,
        hasButtonSelector: !!features.opening?.buttonSelector
      });
    }
    
    // Gallery
    if (features.gallery?.enabled && features.gallery?.itemSelector) {
      console.log('Initializing gallery');
      MyAkad.gallery(features.gallery.itemSelector);
    }
    
    // Animate on Scroll
    if (features.animations?.enabled) {
      console.log('Initializing animations');
      MyAkad.animateOnScroll('.animate-fade-in');
      MyAkad.animateOnScroll('.animate-slide-left');
      MyAkad.animateOnScroll('.animate-slide-right');
      MyAkad.animateOnScroll('.animate-scale');
    }
  };

  // Auto-initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, initializing MyAkad...');
      MyAkad.init();
    });
  } else {
    console.log('DOM already loaded, initializing MyAkad immediately...');
    // Add small delay to ensure all elements are rendered
    setTimeout(function() {
      MyAkad.init();
    }, 100);
  }

})();
