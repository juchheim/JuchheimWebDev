document.addEventListener('DOMContentLoaded', () => {
    const tabsContainer = document.querySelector('.tabs');
    const contents = document.querySelectorAll('.content');

    if (tabsContainer) {
        tabsContainer.addEventListener('click', (event) => {
            const clickedTab = event.target.closest('.tab');
            if (!clickedTab) {
                return;
            }

            const tabId = clickedTab.dataset.tab;
            if (!tabId) {
                return;
            }

            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            contents.forEach(content => {
                content.classList.remove('active');
            });

            // Add active class to clicked tab and corresponding content
            clickedTab.classList.add('active');
            const contentElement = document.getElementById(tabId);
            if (contentElement) {
                contentElement.classList.add('active');
            }
        });

        // Activate the first tab by default
        const firstTab = document.querySelector('.tab');
        if (firstTab) {
            firstTab.click();
        }
    }
});
