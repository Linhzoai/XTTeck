// Admin Mobile Menu Handler
document.addEventListener('DOMContentLoaded', function() {
    // Create mobile menu button if it doesn't exist
    if (window.innerWidth <= 768 && !document.querySelector('.mobile-menu-btn')) {
        const mobileBtn = document.createElement('button');
        mobileBtn.className = 'mobile-menu-btn';
        mobileBtn.innerHTML = '<i class="fas fa-bars"></i>';
        mobileBtn.setAttribute('aria-label', 'Toggle menu');
        document.body.appendChild(mobileBtn);

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Toggle sidebar
        mobileBtn.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Change icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            mobileBtn.querySelector('i').className = 'fas fa-bars';
        });

        // Close sidebar when clicking a menu item on mobile
        const menuLinks = document.querySelectorAll('.sidebar-menu a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const sidebar = document.querySelector('.sidebar');
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    mobileBtn.querySelector('i').className = 'fas fa-bars';
                }
            });
        });
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (window.innerWidth > 768) {
                // Desktop - remove mobile elements
                if (mobileBtn) mobileBtn.style.display = 'none';
                if (overlay) overlay.classList.remove('active');
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) sidebar.classList.remove('active');
            } else {
                // Mobile - show mobile button
                if (mobileBtn) mobileBtn.style.display = 'block';
            }
        }, 250);
    });
});

