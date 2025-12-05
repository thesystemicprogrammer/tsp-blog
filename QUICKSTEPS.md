# Hugo Blog Quick Steps

## Creating a New Hugo Site

```bash
# Install Hugo (macOS with Homebrew)
brew install hugo

# Create a new Hugo site
hugo new site my-blog
cd my-blog

# Initialize git repository
git init
git add .
git commit -m "Initial commit"
```

## Local Development

### Start Development Server

```bash
# Start Hugo development server with drafts enabled
hugo server -D

# Start on specific port
hugo server -p 1313

# Start and open browser automatically
hugo server -D --open
```

The site will be available at `http://localhost:1313` with live reload enabled.

### Create New Content

```bash
# Create a new blog post
hugo new posts/my-first-post.md

# Create a new page
hugo new about.md
```

### Build for Production

```bash
# Build minified production site
hugo --minify

# Output will be in the public/ directory
```

## Setting Up GitHub Actions Deployment

### 1. Generate SSH Key Pair (if you don't have one)

```bash
# Generate a new SSH key (don't use passphrase for automated deployment)
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy

# Copy the public key to your clipboard
cat ~/.ssh/github_deploy.pub
```

### 2. Add Public Key to Your Hosting Server

```bash
# SSH into your hosting server
ssh username@yourserver.com

# Add the public key to authorized_keys
echo "paste-your-public-key-here" >> ~/.ssh/authorized_keys

# Set correct permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 3. Configure GitHub Secrets

Go to your GitHub repository:
1. Click **Settings** (top menu)
2. Click **Secrets and variables** → **Actions** (left sidebar)
3. Click **New repository secret**

Add these secrets one by one:

#### SSH_HOST
- **Name:** `SSH_HOST`
- **Value:** Your server address (e.g., `yoursite.com` or `123.45.67.89`)

#### SSH_USER
- **Name:** `SSH_USER`
- **Value:** Your SSH username (e.g., `username`)

#### SSH_KEY
- **Name:** `SSH_KEY`
- **Value:** Your private key content
  ```bash
  # Copy private key to clipboard (macOS)
  cat ~/.ssh/github_deploy | pbcopy
  
  # On Linux, display and copy manually
  cat ~/.ssh/github_deploy
  ```
  Paste the entire content including:
  ```
  -----BEGIN OPENSSH PRIVATE KEY-----
  ...
  -----END OPENSSH PRIVATE KEY-----
  ```

#### SSH_PORT
- **Name:** `SSH_PORT`
- **Value:** SSH port number (usually `22`)

#### DEPLOY_PATH
- **Name:** `DEPLOY_PATH`
- **Value:** Remote path on your server
  - Common examples:
    - `/home/username/public_html`
    - `~/public_html`
    - `/var/www/html`
    - `~/htdocs`

### 4. Test Your Deployment

```bash
# Commit and push your changes
git add .
git commit -m "Add deployment workflow"
git push origin main
```

Go to your repository → **Actions** tab to watch the deployment progress.

### 5. Manual Deployment Trigger

If you need to redeploy without pushing code:
1. Go to **Actions** tab
2. Click **Build and Deploy Hugo Site**
3. Click **Run workflow** button
4. Select branch and click **Run workflow**

## Project Structure

```
my-blog/
├── archetypes/          # Content templates
├── assets/              # Assets to be processed (CSS, JS, images)
│   ├── css/            # Stylesheets
│   └── icons/          # SVG icons for shortcodes
├── content/            # Your content (markdown files)
│   └── posts/         # Blog posts
├── layouts/            # HTML templates
│   ├── _partials/     # Reusable template parts
│   └── shortcodes/    # Custom shortcodes
├── static/             # Static files (copied as-is)
│   └── fonts/         # Self-hosted fonts
├── public/             # Generated site (git ignored)
├── hugo.yaml           # Main configuration file
└── .github/
    └── workflows/
        └── deploy.yml  # GitHub Actions deployment
```

## Common Hugo Commands

```bash
# Create new content
hugo new posts/my-post.md

# Start development server
hugo server -D

# Build production site
hugo --minify

# Build with specific base URL
hugo --baseURL "https://example.com"

# Check Hugo version
hugo version

# Get help
hugo help
```

## Using Custom Shortcodes

### Icon Shortcode

```markdown
<!-- Basic usage -->
{{< icon "star" >}}

<!-- With custom class -->
{{< icon "heart" class="text-red-500" >}}

<!-- With custom size -->
{{< icon "menu" size="32" >}}

<!-- Combined -->
{{< icon "star" class="text-yellow-400 w-8 h-8" size="32" >}}
```

To add new icons:
1. Download SVG from https://tabler.io/icons
2. Save to `assets/icons/icon-name.svg`
3. Use with `{{< icon "icon-name" >}}`

## Troubleshooting

### Port Already in Use
```bash
# Kill process on port 1313
lsof -ti:1313 | xargs kill -9

# Or use different port
hugo server -p 1314
```

### Build Errors
```bash
# Clean build cache
hugo --gc

# Verbose output for debugging
hugo --verbose
```

### Deployment Issues
- Check GitHub Actions logs in the **Actions** tab
- Verify all secrets are set correctly
- Test SSH connection manually:
  ```bash
  ssh -i ~/.ssh/github_deploy -p PORT USER@HOST
  ```
- Check remote path exists and has write permissions

## Resources

- Hugo Documentation: https://gohugo.io/documentation/
- Hugo Themes: https://themes.gohugo.io/
- Tailwind CSS: https://tailwindcss.com/docs
- Tabler Icons: https://tabler.io/icons
