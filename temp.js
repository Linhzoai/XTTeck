document.addEventListener("DOMContentLoaded", function () {
  // ========== SLIDER FUNCTIONALITY ==========
  const slides = document.querySelectorAll(".preview_img-item");
  const prevButton = document.querySelector(".prev-button");
  const nextButton = document.querySelector(".next-button");
  const previewImg = document.querySelector(".preview_img");
  let currentSlide = 0;
  let slideInterval;

  // Hiển thị slide
  function showSlide(index) {
    // Đảm bảo index nằm trong khoảng cho phép
    if (index >= slides.length) {
      currentSlide = 0;
    } else if (index < 0) {
      currentSlide = slides.length - 1;
    } else {
      currentSlide = index;
    }

    // Ẩn tất cả ảnh
    slides.forEach((slide) => slide.classList.remove("active"));

    // Hiển thị ảnh hiện tại
    if (slides[currentSlide]) {
      slides[currentSlide].classList.add("active");
    }
  }

  // Tự động chuyển slide
  function startAutoSlide() {
    slideInterval = setInterval(() => {
      showSlide(currentSlide + 1);
    }, 5000); // Chuyển sau mỗi 5 giây
  }

  // Dừng tự động chuyển slide
  function stopAutoSlide() {
    clearInterval(slideInterval);
  }

  // Nút điều hướng
  if (prevButton && nextButton) {
    prevButton.addEventListener("click", () => {
      stopAutoSlide();
      showSlide(currentSlide - 1);
      startAutoSlide();
    });

    nextButton.addEventListener("click", () => {
      stopAutoSlide();
      showSlide(currentSlide + 1);
      startAutoSlide();
    });
  }

  // Vuốt cảm ứng cho mobile
  let touchStartX = 0;
  let touchEndX = 0;

  if (previewImg) {
    previewImg.addEventListener("touchstart", (e) => {
      touchStartX = e.touches[0].clientX;
      stopAutoSlide();
    });

    previewImg.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].clientX;
      handleSwipe();
      startAutoSlide();
    });
  }

  function handleSwipe() {
    const swipeDistance = touchEndX - touchStartX;
    if (swipeDistance < -50) showSlide(currentSlide + 1); // Vuốt trái
    if (swipeDistance > 50) showSlide(currentSlide - 1); // Vuốt phải
  }

  // Keyboard navigation
  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") {
      stopAutoSlide();
      showSlide(currentSlide - 1);
      startAutoSlide();
    } else if (e.key === "ArrowRight") {
      stopAutoSlide();
      showSlide(currentSlide + 1);
      startAutoSlide();
    }
  });

  // Khởi động slider
  if (slides.length > 0) {
    showSlide(0);
    startAutoSlide();

    // Dừng auto-slide khi hover vào slider
    if (previewImg) {
      previewImg.addEventListener("mouseenter", stopAutoSlide);
      previewImg.addEventListener("mouseleave", startAutoSlide);
    }
  }

  // ========== SMOOTH SCROLL ==========
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href !== "#" && href !== "#!") {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });

  // ========== PRODUCT CARD ANIMATION ==========
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }, index * 100);
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Áp dụng animation cho các sản phẩm
  const productItems = document.querySelectorAll(".produce_item");
  productItems.forEach((item) => {
    item.style.opacity = "0";
    item.style.transform = "translateY(30px)";
    item.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(item);
  });

  // ========== LOADING ANIMATION ==========
  window.addEventListener("load", () => {
    document.body.style.opacity = "0";
    document.body.style.transition = "opacity 0.5s ease";
    setTimeout(() => {
      document.body.style.opacity = "1";
    }, 100);
  });

  // ========== NAVBAR SCROLL EFFECT ==========
  let lastScroll = 0;
  const header = document.querySelector("header");

  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      header.style.boxShadow = "0 6px 30px rgba(0,0,0,0.2)";
    } else {
      header.style.boxShadow = "0 4px 20px rgba(0,0,0,0.1)";
    }

    lastScroll = currentScroll;
  });

  // ========== CART BADGE ANIMATION ==========
  const cartBadge = document.querySelector(".cart-badge");
  if (cartBadge) {
    // Thêm animation khi có thay đổi số lượng
    const originalCount = cartBadge.textContent;

    // Observer để theo dõi thay đổi nội dung
    const badgeObserver = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (
          mutation.type === "childList" ||
          mutation.type === "characterData"
        ) {
          cartBadge.style.animation = "none";
          setTimeout(() => {
            cartBadge.style.animation =
              "pulse 0.5s ease, bounce 2s infinite 0.5s";
          }, 10);
        }
      });
    });

    badgeObserver.observe(cartBadge, {
      childList: true,
      characterData: true,
      subtree: true,
    });
  }

  // ========== FORM VALIDATION ==========
  const searchForm = document.querySelector(".search_box form");
  if (searchForm) {
    searchForm.addEventListener("submit", (e) => {
      const searchInput = searchForm.querySelector('input[type="text"]');
      if (searchInput && searchInput.value.trim() === "") {
        e.preventDefault();
        searchInput.focus();
        searchInput.style.borderColor = "#ff4757";
        setTimeout(() => {
          searchInput.style.borderColor = "";
        }, 1000);
      }
    });
  }

  // Footer form validation
  const footerForm = document.querySelector(".footer__form");
  if (footerForm) {
    footerForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const emailInput = footerForm.querySelector(".footer__form-input");
      const emailValue = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!emailRegex.test(emailValue)) {
        emailInput.style.borderColor = "#ff4757";
        emailInput.placeholder = "Vui lòng nhập email hợp lệ";
        setTimeout(() => {
          emailInput.style.borderColor = "";
          emailInput.placeholder = "Enter your email...";
        }, 2000);
      } else {
        // Thành công
        emailInput.value = "";
        emailInput.placeholder = "Đã đăng ký thành công!";
        emailInput.style.borderColor = "#2ecc71";
        setTimeout(() => {
          emailInput.style.borderColor = "";
          emailInput.placeholder = "Enter your email...";
        }, 3000);
      }
    });
  }

  // ========== LAZY LOADING IMAGES ==========
  const images = document.querySelectorAll("img[data-src]");
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute("data-src");
        imageObserver.unobserve(img);
      }
    });
  });

  images.forEach((img) => imageObserver.observe(img));

  // ========== DROPDOWN MENU ENHANCEMENT ==========
  const navItems = document.querySelectorAll(".nav_item");
  navItems.forEach((item) => {
    const dropdown = item.querySelector(".nav_produte");
    if (dropdown) {
      let timeout;

      item.addEventListener("mouseenter", () => {
        clearTimeout(timeout);
        dropdown.style.display = "block";
      });

      item.addEventListener("mouseleave", () => {
        timeout = setTimeout(() => {
          dropdown.style.display = "none";
        }, 300);
      });

      dropdown.addEventListener("mouseenter", () => {
        clearTimeout(timeout);
      });

      dropdown.addEventListener("mouseleave", () => {
        timeout = setTimeout(() => {
          dropdown.style.display = "none";
        }, 300);
      });
    }
  });

  // ========== FLOATING ICONS ANIMATION ==========
  const floatingIcons = document.querySelectorAll(".icon_link");
  floatingIcons.forEach((icon, index) => {
    icon.style.animationDelay = `${index * 0.2}s`;
  });

  // ========== PRICE FORMATTING ==========
  const priceElements = document.querySelectorAll(".produce_price");
  priceElements.forEach((priceEl) => {
    const price = priceEl.textContent;
    // Thêm animation cho giá khi hover vào sản phẩm
    const productItem = priceEl.closest(".produce_item");
    if (productItem) {
      productItem.addEventListener("mouseenter", () => {
        priceEl.style.transform = "scale(1.1)";
        priceEl.style.transition = "transform 0.3s ease";
      });

      productItem.addEventListener("mouseleave", () => {
        priceEl.style.transform = "scale(1)";
      });
    }
  });

  // ========== ABOUT SECTION ANIMATION ==========
  const aboutCols = document.querySelectorAll(".about_col");
  const aboutObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.style.opacity = "1";
            entry.target.style.transform = "translateX(0)";
          }, index * 100);
          aboutObserver.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.2,
    }
  );

  aboutCols.forEach((col) => {
    col.style.opacity = "0";
    col.style.transform = "translateX(-30px)";
    col.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    aboutObserver.observe(col);
  });

  // ========== CONSOLE LOG ==========
  console.log(
    "%cWebsite XTTech",
    "color: #2f74d5; font-size: 24px; font-weight: bold;"
  );
  console.log(
    "%cGiao diện đã được tối ưu hóa và cải tiến",
    "color: #555; font-size: 14px;"
  );
  console.log("%c© 2025 - Nguyễn Quang Linh", "color: #999; font-size: 12px;");

  // ========== PERFORMANCE MONITORING ==========
  if ("performance" in window) {
    window.addEventListener("load", () => {
      const perfData = window.performance.timing;
      const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
      console.log(`⚡ Thời gian tải trang: ${pageLoadTime}ms`);
    });
  }

  // ========== ERROR HANDLING FOR IMAGES ==========
  const allImages = document.querySelectorAll("img");
  allImages.forEach((img) => {
    img.addEventListener("error", function () {
      // Thay thế bằng ảnh placeholder nếu ảnh bị lỗi
      if (!this.classList.contains("error-handled")) {
        this.classList.add("error-handled");
        this.style.background =
          "linear-gradient(135deg, #a4eeee 0%, #8dd9d9 100%)";
        this.style.display = "flex";
        this.style.alignItems = "center";
        this.style.justifyContent = "center";
        this.alt = "Không thể tải ảnh";
      }
    });
  });

  // ========== MOBILE MENU TOGGLE (Optional) ==========
  // Có thể thêm hamburger menu cho mobile nếu cần
  const createMobileMenu = () => {
    const navList = document.querySelector(".nav_list");
    if (window.innerWidth <= 768 && navList) {
      // Code cho mobile menu có thể thêm vào đây nếu cần
    }
  };

  window.addEventListener("resize", createMobileMenu);
  createMobileMenu();

  // ========== PREVENT EMPTY FORM SUBMISSION ==========
  const allForms = document.querySelectorAll("form");
  allForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const inputs = this.querySelectorAll(
        "input[required], textarea[required]"
      );
      let isValid = true;

      inputs.forEach((input) => {
        if (input.value.trim() === "") {
          isValid = false;
          input.style.borderColor = "#ff4757";
          setTimeout(() => {
            input.style.borderColor = "";
          }, 2000);
        }
      });

      if (!isValid && !this.classList.contains("footer__form")) {
        e.preventDefault();
      }
    });
  });

  // ========== INIT COMPLETE ==========
  console.log(
    "%c✓ JavaScript đã được tải thành công!",
    "color: #2ecc71; font-weight: bold;"
  );
});
