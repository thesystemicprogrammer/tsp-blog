# The Systemic Programmer Blog

![Hugo](https://img.shields.io/badge/Hugo-0.152.2-FF4088?style=for-the-badge&logo=hugo&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.1.17-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-CC_BY_4.0-blue?style=for-the-badge)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-Enabled-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)

A modern, fast, and privacy-focused blog built with Hugo and Tailwind CSS v4. Features a custom GDPR-compliant page view counter and a clean, responsive design.

## ✨ Features

### 🎨 Modern Design
- **Tailwind CSS v4** - Latest version with improved performance
- **Dark Mode Support** - Automatic theme switching based on system preferences
- **Responsive Layout** - Mobile-first design that looks great on all devices
- **Custom Typography** - Self-hosted Roboto font for GDPR compliance

### 📊 Analytics & Engagement
- **GDPR-Compliant View Counter** - Privacy-focused page view tracking
  - No cookies, no IP logging
  - 15-minute deduplication window
  - Bot filtering and validation
  - Real-time view counts on posts
- **Most Viewed Posts** - Dynamic sidebar widget showing popular content
- **Random Posts Discovery** - Helps visitors find content they might have missed

### 🚀 Performance
- **Static Site Generation** - Lightning-fast page loads with Hugo
- **Optimized Assets** - Minified CSS and JavaScript in production
- **Efficient Caching** - Smart cache-busting for assets
- **No External Dependencies** - All fonts and assets self-hosted

### 📝 Content Management
- **Taxonomies** - Organized by categories and tags
- **Pinned Posts** - Highlight important articles
- **Pagination** - 7 posts per page for optimal UX
- **Custom Archetypes** - Templates for posts and pages
- **Front Matter Support** - Rich metadata including created/updated dates

### 🔧 Developer Experience
- **Hugo Extended** - Full Hugo feature set
- **Live Reload** - Instant preview during development
- **GitHub Actions** - Automated deployment workflow
- **Clean Code Structure** - Well-organized layouts and partials

## 🏗️ Project Structure

```
.
├── archetypes/          # Content templates
│   ├── default.md      # Default archetype
│   └── posts.md        # Blog post archetype
├── assets/
│   ├── css/            # Tailwind CSS configuration
│   ├── icons/          # SVG icons (GitHub, LinkedIn)
│   └── fonts/          # Self-hosted Roboto font family
├── content/
│   ├── posts/          # Blog posts
│   ├── about.md        # About page
│   ├── contact.md      # Contact page
│   └── privacy.md      # Privacy policy
├── layouts/
│   ├── _default/       # Default templates
│   ├── partials/       # Reusable components
│   │   ├── header.html
│   │   ├── footer.html
│   │   ├── sidebar.html
│   │   ├── post-card.html
│   │   └── viewcount-inline.html
│   ├── posts/          # Post-specific layouts
│   ├── shortcodes/     # Custom shortcodes
│   ├── baseof.html     # Base template
│   └── index.html      # Homepage
├── static/
│   ├── api/counter/    # PHP view counter API
│   │   ├── count.php          # Main counter endpoint
│   │   ├── top-posts.php      # Most viewed posts API
│   │   ├── admin.php          # Admin dashboard
│   │   ├── db-config.php      # Database configuration
│   │   └── init.sql           # Database schema
│   ├── js/             # JavaScript files
│   │   ├── random-posts.js
│   │   └── most-viewed-posts.js
│   └── fonts/          # Font files
├── .github/workflows/  # CI/CD configuration
│   └── deploy.yml      # Deployment workflow
├── hugo.yaml           # Hugo configuration
├── tailwind.config.js  # Tailwind configuration
└── package.json        # Node dependencies
```

## 🚀 Quick Start

### Prerequisites

- **Hugo Extended** v0.150.0 or later
- **Node.js** 20.x or later
- **npm** or **yarn**
- **PHP 8.0+** (for view counter in production)
- **MySQL/MariaDB** (for view counter in production)

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/thesystemicprogrammer/tsp-blog.git
   cd tsp-blog
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   hugo server
   # or use the included script
   ./dev-server.sh
   ```

4. **Visit the site**
   Open [http://localhost:1313](http://localhost:1313)

> **Note:** The view counter and "Most Viewed Posts" features require PHP/MySQL and won't work in local development. They will show friendly error messages instead.

### Creating Content

**Create a new blog post:**
```bash
hugo new content posts/my-new-post.md
```

**Create a new page:**
```bash
hugo new content about.md
```

### Building for Production

```bash
hugo --minify
```

The static site will be generated in the `public/` directory.

## 🔧 Configuration

### Hugo Configuration (`hugo.yaml`)

```yaml
baseURL: "https://thesystemicprogrammer.org/"
title: "The Systemic Programmer"
languageCode: "en-us"

params:
  author: "Thomas Berchtold"
  social:
    github: "https://github.com/thesystemicprogrammer"
    linkedin: "https://linkedin.com/in/yourusername"
```

### View Counter Setup

The view counter is a custom PHP/MySQL solution. See [`static/api/counter/README.md`](static/api/counter/README.md) for detailed setup instructions.

**Key Features:**
- GDPR compliant (no personal data stored)
- Bot filtering
- 15-minute deduplication
- Admin dashboard at `/api/counter/admin.php`

## 📦 Deployment

### GitHub Actions (Automated)

The repository includes a GitHub Actions workflow that automatically deploys the site via rsync when manually triggered.

**Required Secrets:**
- `SSH_KEY` - Private SSH key for deployment
- `SSH_HOST` - Server hostname
- `SSH_PORT` - SSH port (usually 22)
- `SSH_USER` - SSH username
- `DEPLOY_PATH` - Target directory on server

### Manual Deployment

1. Build the site:
   ```bash
   hugo --minify
   ```

2. Deploy via rsync:
   ```bash
   rsync -avz --delete public/ user@server:/path/to/webroot/
   ```

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| **Hugo** | Static site generator |
| **Tailwind CSS v4** | Utility-first CSS framework |
| **PHP 8.0+** | Backend API for view counter |
| **MySQL/MariaDB** | Database for analytics |
| **JavaScript (Vanilla)** | Frontend interactivity |
| **GitHub Actions** | CI/CD pipeline |

## 📄 License

- **Content:** Licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)
- **Code:** Licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)

You are free to:
- Share — copy and redistribute the material
- Adapt — remix, transform, and build upon the material

Under the following terms:
- **Attribution** — You must give appropriate credit to Thomas Berchtold

## 🤝 Contributing

This is a personal blog, but suggestions and bug reports are welcome! Please open an issue or submit a pull request.

## 📧 Contact

- **Author:** Thomas Berchtold
- **GitHub:** [@thesystemicprogrammer](https://github.com/thesystemicprogrammer)
- **Blog:** [The Systemic Programmer](https://thesystemicprogrammer.org)

## 🙏 Acknowledgments

- **Hugo** - Amazing static site generator
- **Tailwind CSS** - Brilliant CSS framework
- **Roboto Font** - Google Fonts (self-hosted)
- **Tabler Icons** - Beautiful SVG icons

---

**Made with ❤️ by Thomas Berchtold**
