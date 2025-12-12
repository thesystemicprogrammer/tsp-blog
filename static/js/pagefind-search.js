/**
 * Pagefind Search Implementation
 * Handles search functionality, autocomplete, and pagination
 */

class PagefindSearch {
  constructor() {
    this.pagefind = null;
    this.currentQuery = '';
    this.currentPage = 1;
    this.resultsPerPage = 10;
    this.allResults = [];
    this.debounceTimer = null;
    this.debounceDelay = 300;
    
    // DOM elements
    this.searchInput = null;
    this.searchResults = null;
    this.searchStatus = null;
    this.searchPagination = null;
  }

  async init() {
    try {
      // Initialize Pagefind
      this.pagefind = await import('/pagefind/pagefind.js');
      
      // Get DOM elements
      this.searchInput = document.getElementById('search-input');
      this.searchResults = document.getElementById('search-results');
      this.searchStatus = document.getElementById('search-status');
      this.searchPagination = document.getElementById('search-pagination');
      
      // Bind event listeners
      this.bindEvents();
      
      // Check URL params for initial search
      this.loadFromURL();
      
    } catch (error) {
      console.error('Failed to initialize Pagefind:', error);
      this.showError('Search functionality could not be loaded. Please refresh the page.');
    }
  }

  bindEvents() {
    // Search input events
    this.searchInput.addEventListener('input', (e) => this.handleSearchInput(e.target.value));
    this.searchInput.addEventListener('keydown', (e) => this.handleSearchKeydown(e));
  }

  handleSearchInput(value) {
    // Clear existing debounce timer
    clearTimeout(this.debounceTimer);
    
    // Debounce search
    this.debounceTimer = setTimeout(() => {
      if (value.length > 0) {
        this.currentQuery = value;
        this.currentPage = 1;
        this.performSearch();
      } else {
        this.clearSearch();
      }
    }, this.debounceDelay);
  }

  handleSearchKeydown(e) {
    if (e.key === 'Enter' && this.currentQuery.length > 0) {
      // Clear debounce and search immediately
      clearTimeout(this.debounceTimer);
      this.currentPage = 1;
      this.performSearch();
    }
  }

  async performSearch() {
    if (!this.currentQuery) {
      this.clearSearch();
      return;
    }
    
    try {
      // Show loading state
      this.showLoading();
      
      // Perform search
      const results = await this.pagefind.search(this.currentQuery);
      
      // Get full data for all results
      this.allResults = await Promise.all(results.results.map(r => r.data()));
      
      // Update UI
      this.updateURL();
      this.renderResults();
      
    } catch (error) {
      console.error('Search error:', error);
      this.showError('An error occurred while searching. Please try again.');
    }
  }

  renderResults() {
    const totalResults = this.allResults.length;
    const totalPages = Math.ceil(totalResults / this.resultsPerPage);
    
    // Ensure current page is valid
    if (this.currentPage > totalPages && totalPages > 0) {
      this.currentPage = totalPages;
    }
    
    // Update status message
    if (totalResults === 0) {
      this.showEmptyState();
      return;
    }
    
    const startIndex = (this.currentPage - 1) * this.resultsPerPage;
    const endIndex = Math.min(startIndex + this.resultsPerPage, totalResults);
    
    this.searchStatus.innerHTML = `
      <p>Showing ${startIndex + 1}-${endIndex} of ${totalResults} result${totalResults !== 1 ? 's' : ''} for <strong>"${this.escapeHtml(this.currentQuery)}"</strong></p>
    `;
    
    // Get results for current page
    const pageResults = this.allResults.slice(startIndex, endIndex);
    
    // Render result cards
    this.searchResults.innerHTML = pageResults.map(result => this.renderResultCard(result)).join('');
    
    // Render pagination
    if (totalPages > 1) {
      this.renderPagination(totalPages);
    } else {
      this.searchPagination.innerHTML = '';
    }
  }

  renderResultCard(result) {
    const title = result.meta?.title || 'Untitled';
    const url = result.url;
    const excerpt = result.excerpt || '';
    const date = result.meta?.date || '';
    
    // Extract categories from meta
    const categoriesStr = result.meta?.categories || '';
    const categories = categoriesStr.split(',').map(s => s.trim()).filter(Boolean);
    
    return `
      <article class="search-result-card">
        <h2 class="search-result-title">
          <a href="${url}" class="text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
            ${this.escapeHtml(title)}
          </a>
        </h2>
        
        ${date ? `
          <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <time datetime="${date}">${date}</time>
          </div>
        ` : ''}
        
        ${categories.length > 0 ? `
          <div class="flex flex-wrap gap-2 mb-3">
            ${categories.map(cat => `
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                           bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                ${this.escapeHtml(cat)}
              </span>
            `).join('')}
          </div>
        ` : ''}
        
        <div class="search-result-excerpt">
          ${excerpt}
        </div>
        
        <a href="${url}" 
           class="inline-flex items-center text-blue-600 dark:text-blue-400 
                  hover:text-blue-700 dark:hover:text-blue-300 font-medium mt-3">
          Read more
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </article>
    `;
  }

  renderPagination(totalPages) {
    let paginationHTML = '<div class="flex items-center gap-2">';
    
    // Previous button
    if (this.currentPage > 1) {
      paginationHTML += `
        <button class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 
                       bg-white dark:bg-gray-800 hover:bg-blue-500 hover:text-white 
                       hover:border-blue-500 transition-colors"
                onclick="window.pagefindSearch.goToPage(${this.currentPage - 1})">
          ←
        </button>
      `;
    } else {
      paginationHTML += `
        <span class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 
                     bg-gray-100 dark:bg-gray-900 text-gray-400 cursor-not-allowed">
          ←
        </span>
      `;
    }
    
    // Page numbers (show max 5 pages)
    const maxVisiblePages = 5;
    let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
      if (i === this.currentPage) {
        paginationHTML += `
          <span class="px-4 py-2 rounded bg-blue-600 text-white border border-blue-600">
            ${i}
          </span>
        `;
      } else {
        paginationHTML += `
          <button class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 
                         bg-white dark:bg-gray-800 hover:bg-blue-500 hover:text-white 
                         hover:border-blue-500 transition-colors"
                  onclick="window.pagefindSearch.goToPage(${i})">
            ${i}
          </button>
        `;
      }
    }
    
    // Next button
    if (this.currentPage < totalPages) {
      paginationHTML += `
        <button class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 
                       bg-white dark:bg-gray-800 hover:bg-blue-500 hover:text-white 
                       hover:border-blue-500 transition-colors"
                onclick="window.pagefindSearch.goToPage(${this.currentPage + 1})">
          →
        </button>
      `;
    } else {
      paginationHTML += `
        <span class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 
                     bg-gray-100 dark:bg-gray-900 text-gray-400 cursor-not-allowed">
          →
        </span>
      `;
    }
    
    paginationHTML += '</div>';
    this.searchPagination.innerHTML = paginationHTML;
  }

  goToPage(page) {
    this.currentPage = page;
    this.renderResults();
    this.updateURL();
    
    // Scroll to top of results
    this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  showLoading() {
    this.searchResults.innerHTML = `
      <div class="search-loading">
        <div class="flex items-center justify-center gap-2">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Searching...</span>
        </div>
      </div>
    `;
    this.searchStatus.innerHTML = '';
    this.searchPagination.innerHTML = '';
  }

  showEmptyState() {
    this.searchResults.innerHTML = `
      <div class="search-empty">
        <div class="search-empty-icon">🔍</div>
        <h2 class="search-empty-title">No results found</h2>
        <p class="search-empty-description">
          Try different keywords to see more results.
        </p>
      </div>
    `;
    this.searchStatus.innerHTML = `<p>No results found for <strong>"${this.escapeHtml(this.currentQuery)}"</strong></p>`;
    this.searchPagination.innerHTML = '';
  }

  showError(message) {
    this.searchResults.innerHTML = `
      <div class="search-empty">
        <div class="search-empty-icon">⚠️</div>
        <h2 class="search-empty-title">Error</h2>
        <p class="search-empty-description">${this.escapeHtml(message)}</p>
      </div>
    `;
  }

  clearSearch() {
    this.searchResults.innerHTML = `
      <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" stroke-width="2"></circle>
          <path d="m21 21-4.35-4.35" stroke-width="2"></path>
        </svg>
        <p class="text-lg">Enter a search term to find posts</p>
      </div>
    `;
    this.searchStatus.innerHTML = '';
    this.searchPagination.innerHTML = '';
  }

  updateURL() {
    const params = new URLSearchParams();
    if (this.currentQuery) params.set('q', this.currentQuery);
    if (this.currentPage > 1) params.set('page', this.currentPage);
    
    const newURL = params.toString() ? `?${params.toString()}` : window.location.pathname;
    window.history.replaceState({}, '', newURL);
  }

  loadFromURL() {
    const params = new URLSearchParams(window.location.search);
    
    const query = params.get('q');
    if (query) {
      this.searchInput.value = query;
      this.currentQuery = query;
    }
    
    const page = parseInt(params.get('page'));
    if (page > 0) {
      this.currentPage = page;
    }
    
    // Perform initial search if query exists
    if (this.currentQuery) {
      this.performSearch();
    }
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', async () => {
  window.pagefindSearch = new PagefindSearch();
  await window.pagefindSearch.init();
});
