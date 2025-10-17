// ========== SLIDER FUNCTIONALITY ==========
let currentSlide = 0;
const slides = document.querySelectorAll(".preview_img-item");
const prevButton = document.querySelector(".prev-button");
const nextButton = document.querySelector(".next-button");

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.remove("active");
    if (i === index) {
      slide.classList.add("active");
    }
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  showSlide(currentSlide);
}

// Event listeners for slider buttons
if (nextButton) {
  nextButton.addEventListener("click", nextSlide);
}

if (prevButton) {
  prevButton.addEventListener("click", prevSlide);
}

// Auto slide every 5 seconds
let autoSlideInterval = setInterval(nextSlide, 5000);

// Pause auto slide on hover
const sliderContainer = document.querySelector(".slider-container");
if (sliderContainer) {
  sliderContainer.addEventListener("mouseenter", () => {
    clearInterval(autoSlideInterval);
  });

  sliderContainer.addEventListener("mouseleave", () => {
    autoSlideInterval = setInterval(nextSlide, 5000);
  });
}

// Keyboard navigation
document.addEventListener("keydown", (e) => {
  if (e.key === "ArrowLeft") {
    prevSlide();
  } else if (e.key === "ArrowRight") {
    nextSlide();
  }
});

// ========== MOBILE MENU TOGGLE ==========
const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
const navList = document.querySelector(".nav_list");
const body = document.body;

if (mobileMenuToggle && navList) {
  mobileMenuToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    mobileMenuToggle.classList.toggle("active");
    navList.classList.toggle("active");

    // Prevent body scroll when menu is open
    if (navList.classList.contains("active")) {
      body.style.overflow = "hidden";
    } else {
      body.style.overflow = "";
    }
  });

  // Close menu when clicking outside
  document.addEventListener("click", (e) => {
    if (!navList.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
      if (navList.classList.contains("active")) {
        mobileMenuToggle.classList.remove("active");
        navList.classList.remove("active");
        body.style.overflow = "";
      }
    }
  });

  // Close menu when clicking on a link
  const navLinks = document.querySelectorAll(".nav_item > a");
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      // Only close if it's not a dropdown menu
      if (
        !link.nextElementSibling ||
        !link.nextElementSibling.classList.contains("nav_produte")
      ) {
        mobileMenuToggle.classList.remove("active");
        navList.classList.remove("active");
        body.style.overflow = "";
      }
    });
  });
}

// ========== MOBILE DROPDOWN TOGGLE ==========
const navItems = document.querySelectorAll(".nav_item");

navItems.forEach((item) => {
  const link = item.querySelector("a");
  const dropdown = item.querySelector(".nav_produte");

  if (dropdown && window.innerWidth <= 768) {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      item.classList.toggle("active");
    });
  }
});

// Update dropdown behavior on resize
window.addEventListener("resize", () => {
  if (window.innerWidth > 768) {
    navItems.forEach((item) => {
      item.classList.remove("active");
    });
    body.style.overflow = "";
    if (navList) {
      navList.classList.remove("active");
    }
    if (mobileMenuToggle) {
      mobileMenuToggle.classList.remove("active");
    }
  }
});

// ========== SCROLL ANIMATIONS ==========
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "translateY(0)";
    }
  });
}, observerOptions);

// Observe elements for scroll animations
const animateElements = document.querySelectorAll(
  ".stat-item, .produce_item, .why-item, .about_col, .testimonial-item"
);

animateElements.forEach((el) => {
  el.style.opacity = "0";
  el.style.transform = "translateY(30px)";
  el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
  observer.observe(el);
});

// ========== COUNTER ANIMATION FOR STATS ==========
const statNumbers = document.querySelectorAll(".stat-number");

const animateCounter = (element) => {
  const target = element.textContent.replace(/[^0-9]/g, "");
  const suffix = element.textContent.replace(/[0-9]/g, "");
  const duration = 2000;
  const increment = target / (duration / 16);
  let current = 0;

  const updateCounter = () => {
    current += increment;
    if (current < target) {
      element.textContent = Math.floor(current) + suffix;
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = target + suffix;
    }
  };

  updateCounter();
};

const statsObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        statsObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.5 }
);

statNumbers.forEach((stat) => {
  statsObserver.observe(stat);
});

// ========== SMOOTH SCROLL FOR ANCHOR LINKS ==========
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href !== "#!" && href !== "#") {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        const headerOffset = 100;
        const elementPosition = target.getBoundingClientRect().top;
        const offsetPosition =
          elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth",
        });

        // Close mobile menu if open
        if (navList && navList.classList.contains("active")) {
          mobileMenuToggle.classList.remove("active");
          navList.classList.remove("active");
          body.style.overflow = "";
        }
      }
    }
  });
});

// ========== STICKY HEADER SHADOW ==========
const header = document.querySelector("header");
let lastScroll = 0;

window.addEventListener("scroll", () => {
  const currentScroll = window.pageYOffset;

  if (currentScroll > 100) {
    header.style.boxShadow = "0 4px 30px rgba(0, 0, 0, 0.2)";
  } else {
    header.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.1)";
  }

  lastScroll = currentScroll;
});

// ========== FORM VALIDATION ==========
const emailForm = document.querySelector(".footer__form");
if (emailForm) {
  emailForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const emailInput = emailForm.querySelector('input[type="email"]');
    const email = emailInput.value;

    if (validateEmail(email)) {
      alert(
        "Cảm ơn bạn đã đăng ký! Chúng tôi sẽ gửi thông tin mới nhất đến email của bạn."
      );
      emailInput.value = "";
    } else {
      alert("Vui lòng nhập địa chỉ email hợp lệ.");
    }
  });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// ========== PRODUCT QUICK VIEW ==========
const quickViewButtons = document.querySelectorAll(".quick-view-btn");
quickViewButtons.forEach((btn) => {
  btn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    // This would typically open a modal with product details
    // For now, we'll just redirect to the product page
    const productLink = btn.closest(".produce_link");
    if (productLink) {
      window.location.href = productLink.href;
    }
  });
});

// ========== LAZY LOADING IMAGES ==========
const lazyImages = document.querySelectorAll('img[loading="lazy"]');

if ("IntersectionObserver" in window) {
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.src;
        img.classList.add("loaded");
        imageObserver.unobserve(img);
      }
    });
  });

  lazyImages.forEach((img) => {
    imageObserver.observe(img);
  });
} else {
  // Fallback for browsers that don't support IntersectionObserver
  lazyImages.forEach((img) => {
    img.src = img.src;
  });
}

// ========== PRELOAD CRITICAL IMAGES ==========
const preloadImages = () => {
  const criticalImages = document.querySelectorAll(".preview_img-item");
  criticalImages.forEach((img) => {
    const src = img.getAttribute("src");
    if (src) {
      const preloadLink = document.createElement("link");
      preloadLink.rel = "preload";
      preloadLink.as = "image";
      preloadLink.href = src;
      document.head.appendChild(preloadLink);
    }
  });
};

// Call preload function when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", preloadImages);
} else {
  preloadImages();
}

// ========== PERFORMANCE OPTIMIZATION ==========
// Debounce function for resize events
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Use debounced resize handler
const handleResize = debounce(() => {
  // Resize logic here
  if (window.innerWidth > 768) {
    body.style.overflow = "";
  }
}, 250);

window.addEventListener("resize", handleResize);

// ========== CONSOLE MESSAGE ==========
console.log(
  "%cXTTech Website",
  "font-size: 24px; font-weight: bold; color: #2f74d5;"
);
console.log("%cDesigned by Nguyễn Quang Linh", "font-size: 14px; color: #666;");

// ========== TOUCH SWIPE FOR SLIDER (Mobile) ==========
let touchStartX = 0;
let touchEndX = 0;

if (sliderContainer) {
  sliderContainer.addEventListener(
    "touchstart",
    (e) => {
      touchStartX = e.changedTouches[0].screenX;
    },
    { passive: true }
  );

  sliderContainer.addEventListener(
    "touchend",
    (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    },
    { passive: true }
  );
}

function handleSwipe() {
  const swipeThreshold = 50;
  const diff = touchStartX - touchEndX;

  if (Math.abs(diff) > swipeThreshold) {
    if (diff > 0) {
      // Swiped left
      nextSlide();
    } else {
      // Swiped right
      prevSlide();
    }
  }
}

// ========== LOADING ANIMATION ==========
window.addEventListener("load", () => {
  document.body.classList.add("loaded");

  // Fade in hero content
  const heroContent = document.querySelector(".hero-content");
  if (heroContent) {
    heroContent.style.opacity = "0";
    setTimeout(() => {
      heroContent.style.transition = "opacity 1s ease";
      heroContent.style.opacity = "1";
    }, 300);
  }
});

// ========== ACTIVE NAV ITEM ON SCROLL ==========
const sections = document.querySelectorAll("section[id], div[id]");
const navLinks = document.querySelectorAll('.nav_item a[href^="#"]');

window.addEventListener("scroll", () => {
  let current = "";

  sections.forEach((section) => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;
    if (window.pageYOffset >= sectionTop - 200) {
      current = section.getAttribute("id");
    }
  });

  navLinks.forEach((link) => {
    link.parentElement.classList.remove("active-section");
    if (link.getAttribute("href") === `#${current}`) {
      link.parentElement.classList.add("active-section");
    }
  });
});

// Add CSS for active nav item
const activeNavStyle = document.createElement("style");
activeNavStyle.textContent = `
  .nav_item.active-section > a {
    background: rgba(255, 255, 255, 0.2);
  }
`;
document.head.appendChild(activeNavStyle);

// ========== PRODUCT CARD TILT EFFECT (Desktop) ==========
if (window.innerWidth > 768) {
  const productCards = document.querySelectorAll(".produce_item");

  productCards.forEach((card) => {
    card.addEventListener("mousemove", (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      const centerX = rect.width / 2;
      const centerY = rect.height / 2;

      const rotateX = (y - centerY) / 20;
      const rotateY = (centerX - x) / 20;

      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });

    card.addEventListener("mouseleave", () => {
      card.style.transform = "";
    });
  });
}

// ========== SEARCH FUNCTIONALITY ==========
const searchInput = document.querySelector('.search_box input[type="text"]');
if (searchInput) {
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const searchValue = searchInput.value.trim();
      if (searchValue) {
        window.location.href = `sanpham.php?keyword=${encodeURIComponent(
          searchValue
        )}`;
      }
    }
  });
}

// ========== FLOATING ICONS TOOLTIP ==========
const floatingIcons = document.querySelectorAll(".icon_link");
floatingIcons.forEach((icon) => {
  const tooltip = document.createElement("span");
  tooltip.className = "icon-tooltip";
  tooltip.textContent = icon.getAttribute("aria-label") || "Social";
  icon.appendChild(tooltip);
});

const tooltipStyle = document.createElement("style");
tooltipStyle.textContent = `
  .icon_link {
    position: relative;
  }
  
  .icon-tooltip {
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
  }
  
  .icon-tooltip::after {
    content: '';
    position: absolute;
    right: -6px;
    top: 50%;
    transform: translateY(-50%);
    border: 6px solid transparent;
    border-left-color: #333;
  }
  
  .icon_link:hover .icon-tooltip {
    opacity: 1;
    visibility: visible;
    right: 65px;
  }
  
  @media (max-width: 768px) {
    .icon-tooltip {
      display: none;
    }
  }
`;
document.head.appendChild(tooltipStyle);

// ========== CART BADGE ANIMATION ==========
const cartBadge = document.querySelector(".cart-badge");
if (cartBadge) {
  // Add extra animation when hovering cart icon
  const cartIcon = document.querySelector(".cart-icon");
  if (cartIcon) {
    cartIcon.addEventListener("mouseenter", () => {
      cartBadge.style.animation = "none";
      setTimeout(() => {
        cartBadge.style.animation = "pulse 0.5s ease";
      }, 10);
    });
  }
}

// ========== TESTIMONIAL SLIDER (Optional Enhancement) ==========
const testimonialItems = document.querySelectorAll(".testimonial-item");
if (testimonialItems.length > 3 && window.innerWidth <= 768) {
  let currentTestimonial = 0;

  const showTestimonial = (index) => {
    testimonialItems.forEach((item, i) => {
      item.style.display = i === index ? "block" : "none";
    });
  };

  // Show first testimonial initially
  showTestimonial(0);

  // Auto rotate testimonials on mobile
  setInterval(() => {
    currentTestimonial = (currentTestimonial + 1) % testimonialItems.length;
    showTestimonial(currentTestimonial);
  }, 5000);
}

// ========== PRICE FORMATTING ==========
const priceElements = document.querySelectorAll(".produce_price");
priceElements.forEach((price) => {
  const text = price.textContent;
  const number = text.match(/[\d,]+/);
  if (number) {
    price.innerHTML = `<span class="price-amount">${number[0]}</span><span class="price-currency">₫</span>`;
  }
});

const priceStyle = document.createElement("style");
priceStyle.textContent = `
  .price-amount {
    font-size: 24px;
    font-weight: 800;
  }
  
  .price-currency {
    font-size: 18px;
    margin-left: 2px;
  }
`;
document.head.appendChild(priceStyle);

// ========== PREVENT FORM DOUBLE SUBMISSION ==========
const forms = document.querySelectorAll("form");
forms.forEach((form) => {
  form.addEventListener("submit", function (e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn && submitBtn.disabled) {
      e.preventDefault();
      return false;
    }
    if (submitBtn) {
      submitBtn.disabled = true;
      setTimeout(() => {
        submitBtn.disabled = false;
      }, 3000);
    }
  });
});

// ========== SCROLL PROGRESS INDICATOR ==========
const createScrollIndicator = () => {
  const indicator = document.createElement("div");
  indicator.className = "scroll-progress";
  document.body.appendChild(indicator);

  const indicatorStyle = document.createElement("style");
  indicatorStyle.textContent = `
    .scroll-progress {
      position: fixed;
      top: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, #d40000 0%, #2f74d5 100%);
      z-index: 9999;
      transition: width 0.1s ease;
      width: 0;
    }
  `;
  document.head.appendChild(indicatorStyle);

  window.addEventListener("scroll", () => {
    const windowHeight =
      document.documentElement.scrollHeight - window.innerHeight;
    const scrolled = (window.pageYOffset / windowHeight) * 100;
    indicator.style.width = `${scrolled}%`;
  });
};

createScrollIndicator();

// ========== ERROR HANDLING FOR IMAGES ==========
const images = document.querySelectorAll("img");
images.forEach((img) => {
  img.addEventListener("error", function () {
    this.style.backgroundColor = "#f0f0f0";
    this.style.display = "flex";
    this.style.alignItems = "center";
    this.style.justifyContent = "center";
    this.alt = "Image not found";
  });
});

// ========== ACCESSIBILITY ENHANCEMENTS ==========
// Add skip to main content link
const skipLink = document.createElement("a");
skipLink.href = "#main";
skipLink.className = "skip-link";
skipLink.textContent = "Skip to main content";
document.body.insertBefore(skipLink, document.body.firstChild);

const skipLinkStyle = document.createElement("style");
skipLinkStyle.textContent = `
  .skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #2f74d5;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    z-index: 10000;
    border-radius: 0 0 4px 0;
  }
  
  .skip-link:focus {
    top: 0;
  }
`;
document.head.appendChild(skipLinkStyle);

// Add main id if not exists
const main = document.querySelector("main");
if (main && !main.id) {
  main.id = "main";
}

// ========== COOKIE CONSENT (Optional) ==========
const showCookieConsent = () => {
  const consent = localStorage.getItem("cookieConsent");
  if (!consent) {
    const banner = document.createElement("div");
    banner.className = "cookie-banner";
    banner.innerHTML = `
      <div class="cookie-content">
        <p>Chúng tôi sử dụng cookie để cải thiện trải nghiệm của bạn. Bằng cách tiếp tục sử dụng trang web, bạn đồng ý với việc sử dụng cookie.</p>
        <button class="cookie-accept">Đồng ý</button>
      </div>
    `;
    document.body.appendChild(banner);

    const bannerStyle = document.createElement("style");
    bannerStyle.textContent = `
      .cookie-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(16, 55, 92, 0.98);
        color: white;
        padding: 20px;
        z-index: 9998;
        animation: slideUp 0.5s ease;
      }
      
      @keyframes slideUp {
        from {
          transform: translateY(100%);
        }
        to {
          transform: translateY(0);
        }
      }
      
      .cookie-content {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
      }
      
      .cookie-content p {
        margin: 0;
        flex: 1;
      }
      
      .cookie-accept {
        padding: 10px 30px;
        background: #2f74d5;
        color: white;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
      }
      
      .cookie-accept:hover {
        background: #1a5bb8;
        transform: scale(1.05);
      }
      
      @media (max-width: 768px) {
        .cookie-content {
          flex-direction: column;
          text-align: center;
        }
        
        .cookie-accept {
          width: 100%;
        }
      }
    `;
    document.head.appendChild(bannerStyle);

    banner.querySelector(".cookie-accept").addEventListener("click", () => {
      localStorage.setItem("cookieConsent", "true");
      banner.style.animation = "slideDown 0.5s ease";
      setTimeout(() => banner.remove(), 500);
    });

    const slideDownStyle = document.createElement("style");
    slideDownStyle.textContent = `
      @keyframes slideDown {
        from {
          transform: translateY(0);
        }
        to {
          transform: translateY(100%);
        }
      }
    `;
    document.head.appendChild(slideDownStyle);
  }
};

// Show cookie consent after 1 second
setTimeout(showCookieConsent, 1000);

// ========== INITIALIZATION COMPLETE ==========
console.log(
  "%c✓ Website initialized successfully",
  "color: #27ae60; font-weight: bold;"
);
console.log("%cAll features loaded and ready!", "color: #2f74d5;");
