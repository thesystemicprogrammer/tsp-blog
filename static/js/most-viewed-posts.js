/**
 * Most Viewed Posts - Sidebar Widget
 * 
 * Fetches and displays the most viewed posts from the API
 * Handles loading states and graceful error handling
 */

document.addEventListener('DOMContentLoaded', async function() {
  const container = document.getElementById('most-viewed-posts');
  const loadingElement = document.getElementById('most-viewed-loading');
  
  // Exit if container doesn't exist (not on homepage)
  if (!container || !loadingElement) {
    return;
  }
  
  try {
    // Fetch top posts from API (limit: 5)
    const response = await fetch('/api/counter/top-posts.php?limit=5');
    
    // Check if response is OK
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    
    // Check if API returned success
    if (!data.success) {
      throw new Error(data.error || 'API returned an error');
    }
    
    // Check if we have posts
    if (!data.posts || data.posts.length === 0) {
      showEmptyState(container, loadingElement);
      return;
    }
    
    // Render posts
    renderPosts(container, loadingElement, data.posts);
    
  } catch (error) {
    // Log error for debugging
    console.error('Error loading most viewed posts:', error);
    
    // Show user-friendly error message
    showErrorState(container, loadingElement);
  }
});

/**
 * Render posts in the container
 * 
 * @param {HTMLElement} container - The container element
 * @param {HTMLElement} loadingElement - The loading skeleton element
 * @param {Array} posts - Array of post objects
 */
function renderPosts(container, loadingElement, posts) {
  // Remove loading state
  loadingElement.remove();
  
  // Build HTML for all posts
  const postsHTML = posts.map(post => createPostCard(post)).join('');
  
  // Insert into container
  container.innerHTML = postsHTML;
}

/**
 * Create HTML for a single post card
 * 
 * @param {Object} post - Post data object
 * @returns {string} HTML string for the post card
 */
function createPostCard(post) {
  return `
    <a href="${escapeHtml(post.url)}" 
       class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 
              transition-colors duration-200 border border-transparent 
              hover:border-gray-300 dark:hover:border-gray-600">
      <div class="font-medium text-blue-600 dark:text-blue-400 hover:underline mb-1">
        ${escapeHtml(post.title)}
      </div>
      <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
        <span>${escapeHtml(post.date)}</span>
        <span class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          ${escapeHtml(post.formatted_count)} views
        </span>
      </div>
    </a>
  `;
}

/**
 * Show error state when API fails
 * 
 * @param {HTMLElement} container - The container element
 * @param {HTMLElement} loadingElement - The loading skeleton element
 */
function showErrorState(container, loadingElement) {
  // Remove loading state
  loadingElement.remove();
  
  // Show error message
  container.innerHTML = `
    <div class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
      <p class="text-sm text-yellow-800 dark:text-yellow-400">
        Unable to load popular posts. Please try again later.
      </p>
    </div>
  `;
}

/**
 * Show empty state when no posts are found
 * 
 * @param {HTMLElement} container - The container element
 * @param {HTMLElement} loadingElement - The loading skeleton element
 */
function showEmptyState(container, loadingElement) {
  // Remove loading state
  loadingElement.remove();
  
  // Show empty state message
  container.innerHTML = `
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
        No posts available yet.
      </p>
    </div>
  `;
}

/**
 * Escape HTML to prevent XSS attacks
 * 
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
