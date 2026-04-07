document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('#bg-loans-settings-tabs .nav-tab');
    var contents = document.querySelectorAll('.bg-loans-tab-content');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
            
            // Add active class to clicked tab
            tab.classList.add('nav-tab-active');
            
            // Hide all tab contents
            contents.forEach(function(c) { c.style.display = 'none'; });
            
            // Show the corresponding tab content
            var targetId = 'tab-' + tab.getAttribute('data-tab');
            var targetEl = document.getElementById(targetId);
            if(targetEl) {
                targetEl.style.display = 'block';
            }
        });
    });
});
