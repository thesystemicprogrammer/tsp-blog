document.addEventListener('DOMContentLoaded', function() {
  const dataElement = document.getElementById('all-posts-data');
  const randomPostsContainer = document.getElementById('random-posts');
  const discoverMoreSection = document.getElementById('discover-more-section');
  
  if (!dataElement || !randomPostsContainer || !discoverMoreSection) {
    return;
  }
  
  try {
    const data = JSON.parse(dataElement.textContent);
    const allPosts = data.posts;
    const currentPageUrls = data.currentPage || [];
    
    // Filter out posts that are on the current page
    const availablePosts = allPosts.filter(post => 
      !currentPageUrls.includes(post.url)
    );
    
    // If we have fewer than 1 post available, don't show the section
    if (availablePosts.length < 1) {
      return;
    }
    
    // Shuffle array using Fisher-Yates algorithm
    const shuffled = [...availablePosts];
    for (let i = shuffled.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    
    // Take up to 3 posts
    const selectedPosts = shuffled.slice(0, Math.min(3, shuffled.length));
    
    // Render random posts
    randomPostsContainer.innerHTML = selectedPosts.map(post => `
      <a href="${escapeHtml(post.url)}" 
         class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 
                transition-colors duration-200 border border-transparent 
                hover:border-gray-300 dark:hover:border-gray-600">
        <div class="font-medium text-blue-600 dark:text-blue-400 hover:underline mb-1">
          ${escapeHtml(post.title)}
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
          ${escapeHtml(post.date)}
        </div>
      </a>
    `).join('');
    
    // Show the section
    discoverMoreSection.style.display = 'block';
    
  } catch (e) {
    console.error('Error loading random posts:', e);
  }
});

// Helper function to escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
