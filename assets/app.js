import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');



import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// Code for managing tabs in dashboardadmin.html.twig
function initializeTabs() {
    const tabs = ['tab1', 'tab2', 'tab3', 'tab4'];
    const buttons = ['btn1', 'btn2', 'btn3', 'btn4'];

    buttons.forEach((btnId, index) => {
      const btn = document.getElementById(btnId);
      if (!btn) return;

      btn.addEventListener('click', () => {
          // Hide all tabs
        tabs.forEach(tabId => {
          const tab = document.getElementById(tabId);
          if (tab) tab.classList.add('hidden');
        });

          // Display the clicked tab
        const activeTab = document.getElementById(tabs[index]);
        if (activeTab) activeTab.classList.remove('hidden');

          // Update the style of the buttons
        buttons.forEach(otherBtnId => {
          const otherBtn = document.getElementById(otherBtnId);
          if (otherBtn) {
            otherBtn.classList.remove('bg-lime-600', 'text-white');
            otherBtn.classList.add('bg-gray-100', 'text-gray-600');
          }
        });

        btn.classList.remove('bg-gray-100', 'text-gray-600');
        btn.classList.add('bg-lime-600', 'text-white');
      });
    });
  }
  if (window.Turbo) {
    document.addEventListener('turbo:load', initializeTabs);
  } else {
    document.addEventListener('DOMContentLoaded', initializeTabs);
  }
