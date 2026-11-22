
<!-- Global Modals -->
<?php include __DIR__ . '/modals.php'; ?>

<!-- Site Footer -->
<footer class="w-full h-[50px] min-h-[50px] shrink-0 bg-white border-t border-slate-200 px-6 sm:px-8 flex items-center justify-between text-[11px] text-[#557096] gap-3 select-none z-10">

    <div class="flex items-center gap-2">
        <span>
            &copy; <?= date('Y') ?> Bimbel Tunas Cendekia. Hak Cipta Dilindungi.
        </span>
    </div>

    <div class="flex items-center gap-5">
        <a href="#" class="hover:text-blue-700 transition-colors">Bantuan</a>
        <a href="#" class="hover:text-blue-700 transition-colors">Kebijakan Privasi</a>
        <a href="#" class="hover:text-blue-700 transition-colors">Syarat &amp; Ketentuan</a>
    </div>

</footer>

</div><!-- flex-1 main-content-wrapper from header.php -->
</div><!-- flex-1 outer-wrapper from header.php -->
</body>
</html>



<!-- Interactive Global Controller JavaScript -->
<script>
(function() {
    // Mobile Sidebar Elements
    const sidebar = document.getElementById('main-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggleSidebarBtn = document.getElementById('toggle-sidebar-btn');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');

    function openSidebar() {
        if (!sidebar || !backdrop) return;
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            sidebar.classList.remove('-translate-x-full');
        });
    }

    function closeSidebar() {
        if (!sidebar || !backdrop) return;
        backdrop.classList.add('opacity-0');
        sidebar.classList.add('-translate-x-full');
        setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 300);
    }

    if (toggleSidebarBtn) toggleSidebarBtn.addEventListener('click', openSidebar);
    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Notification Popover Elements
    const notifBtn = document.getElementById('notification-btn');
    const notifPopover = document.getElementById('notification-popover');
    const notifDot = document.getElementById('notification-bell-dot');
    const notifBadgePill = document.getElementById('notif-badge-pill');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    const tabAll = document.getElementById('tab-notif-all');
    const tabUnread = document.getElementById('tab-notif-unread');

    function toggleNotification(e) {
        if (e) e.stopPropagation();
        if (!notifPopover) return;
        const isHidden = notifPopover.classList.contains('hidden');
        if (isHidden) {
            closeProfileDropdown();
            notifPopover.classList.remove('hidden');
            requestAnimationFrame(() => {
                notifPopover.classList.remove('opacity-0', 'translate-y-2');
            });
        } else {
            closeNotification();
        }
    }

    function closeNotification() {
        if (!notifPopover || notifPopover.classList.contains('hidden')) return;
        notifPopover.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => {
            notifPopover.classList.add('hidden');
        }, 200);
    }

    if (notifBtn) notifBtn.addEventListener('click', toggleNotification);

    // Notification Tabs Filtering
    if (tabAll && tabUnread) {
        tabAll.addEventListener('click', function(e) {
            e.stopPropagation();
            tabAll.classList.add('active', 'text-blue-600', 'font-semibold');
            tabAll.classList.remove('text-slate-500', 'font-medium');
            tabUnread.classList.remove('active', 'text-blue-600', 'font-semibold');
            tabUnread.classList.add('text-slate-500', 'font-medium');
            
            // Move indicator
            let indicator = tabUnread.querySelector('.tab-indicator');
            if (indicator) indicator.remove();
            if (!tabAll.querySelector('.tab-indicator')) {
                tabAll.innerHTML += '<span class="tab-indicator absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></span>';
            }

            document.querySelectorAll('.notification-item').forEach(item => {
                item.style.display = 'flex';
            });
        });

        tabUnread.addEventListener('click', function(e) {
            e.stopPropagation();
            tabUnread.classList.add('active', 'text-blue-600', 'font-semibold');
            tabUnread.classList.remove('text-slate-500', 'font-medium');
            tabAll.classList.remove('active', 'text-blue-600', 'font-semibold');
            tabAll.classList.add('text-slate-500', 'font-medium');
            
            let indicator = tabAll.querySelector('.tab-indicator');
            if (indicator) indicator.remove();
            if (!tabUnread.querySelector('.tab-indicator')) {
                tabUnread.innerHTML += '<span class="tab-indicator absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></span>';
            }

            document.querySelectorAll('.notification-item').forEach(item => {
                if (item.getAttribute('data-unread') === '1') {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Mark All As Read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (notifDot) notifDot.style.display = 'none';
            if (notifBadgePill) {
                notifBadgePill.textContent = '0 Baru';
                notifBadgePill.classList.remove('bg-blue-100', 'text-blue-700');
                notifBadgePill.classList.add('bg-slate-100', 'text-slate-500');
            }
            document.querySelectorAll('.unread-dot').forEach(dot => dot.remove());
            document.querySelectorAll('.notification-item').forEach(item => {
                item.setAttribute('data-unread', '0');
                item.classList.remove('is-unread');
                item.classList.add('is-read', 'opacity-85');
            });
        });
    }


    const profileDropdown = document.getElementById('user-profile-dropdown');
    const profileBtn = document.getElementById('user-profile-btn');

    function toggleProfileDropdown(e) {
        if (e) e.stopPropagation();
        if (!profileDropdown) return;
        const isHidden = profileDropdown.classList.contains('hidden');
        if (isHidden) {
            closeNotification();
            profileDropdown.classList.remove('hidden');
            requestAnimationFrame(() => {
                profileDropdown.classList.remove('opacity-0', 'translate-y-2');
            });
        } else {
            closeProfileDropdown();
        }
    }

    function closeProfileDropdown() {
        if (!profileDropdown || profileDropdown.classList.contains('hidden')) return;
        profileDropdown.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => {
            profileDropdown.classList.add('hidden');
        }, 200);
    }

    if (profileBtn) profileBtn.addEventListener('click', toggleProfileDropdown);

    // Global Click Outside Handler
    document.addEventListener('click', function(e) {
        const notifWrap = document.getElementById('notification-wrapper');
        const profileWrap = document.getElementById('user-profile-wrapper');

        if (notifWrap && !notifWrap.contains(e.target)) {
            closeNotification();
        }
        if (profileWrap && !profileWrap.contains(e.target)) {
            closeProfileDropdown();
        }
    });

    // Modals Controller (Profil Saya & Konfirmasi Keluar)
    const profileModal = document.getElementById('modal-profile');
    const profileCard = document.getElementById('modal-profile-card');
    const logoutModal = document.getElementById('modal-logout');
    const logoutCard = document.getElementById('modal-logout-card');

    function openModal(modal, card) {
        if (!modal) return;
        closeProfileDropdown();
        closeNotification();
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        });
    }

    function closeModal(modal, card) {
        if (!modal) return;
        modal.classList.add('opacity-0');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Bind all profile modal triggers
    document.querySelectorAll('.btn-trigger-profile').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(profileModal, profileCard);
        });
    });

    document.querySelectorAll('.modal-close-profile').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(profileModal, profileCard);
        });
    });

    if (profileModal) {
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) closeModal(profileModal, profileCard);
        });
    }

    // Bind all logout triggers
    document.querySelectorAll('.btn-trigger-logout').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(logoutModal, logoutCard);
        });
    });

    document.querySelectorAll('.modal-close-logout').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(logoutModal, logoutCard);
        });
    });

    if (logoutModal) {
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) closeModal(logoutModal, logoutCard);
        });
    }

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNotification();
            closeProfileDropdown();
            closeModal(profileModal, profileCard);
            closeModal(logoutModal, logoutCard);
            closeSidebar();
        }
    });
})();
</script>
</body>
</html>
