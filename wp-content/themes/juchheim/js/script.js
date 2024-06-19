console.log('script.js file loaded');

document.addEventListener('DOMContentLoaded', () => {
    console.log('Document loaded and DOM fully built');

    const tabsContainer = document.querySelector('.tabs');
    const contents = document.querySelectorAll('.content');

    if (tabsContainer) {
        console.log('Tabs container found');
    } else {
        console.error('Tabs container not found');
    }

    if (contents.length) {
        console.log('Contents found:', contents.length);
    } else {
        console.error('Contents not found');
    }

    tabsContainer.addEventListener('click', (event) => {
        const clickedTab = event.target.closest('.tab');
        if (!clickedTab) {
            console.log('No tab was clicked');
            return;
        }

        console.log('Clicked tab:', clickedTab);
        const tabId = clickedTab.dataset.tab;
        console.log('Tab ID:', tabId);

        if (!tabId) {
            console.error('Tab ID not found');
            return;
        }

        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
            console.log('Removed active class from tab:', tab);
        });
        contents.forEach(content => {
            content.classList.remove('active');
            console.log('Removed active class from content:', content);
        });

        // Add active class to clicked tab and corresponding content
        clickedTab.classList.add('active');
        console.log('Added active class to clicked tab:', clickedTab);
        const contentElement = document.getElementById(tabId);
        if (contentElement) {
            contentElement.classList.add('active');
            console.log('Added active class to content:', contentElement);
        } else {
            console.error('Content element not found for tab ID:', tabId);
        }
    });

    // Activate the first tab by default
    const firstTab = document.querySelector('.tab');
    if (firstTab) {
        firstTab.click();
        console.log('Activated the first tab by default');
    } else {
        console.error('First tab not found');
    }
});
