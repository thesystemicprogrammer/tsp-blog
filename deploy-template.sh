#!/bin/sh

#
#   Copy this file as deploy.sh (this file is included in .gitignore) and set the correct
#   parameter values to deploy your website via rsync and ssh
#
USER=my-webhost-user-id
HOST=my-webhost-url     
DIR=my-webhost-public-html-directory
PAGEVIEW_URL=my-pageview-api-backend-url
PAGEVIEW_AUTH=my-pageview-auth-token

#
#   Function to print information about current step
#
print_step() {
    printf "\n=================================================================================\n"
    printf "$1\n"
    printf "=================================================================================\n"
}

#
#   Start by deleting the public directory
#
print_step "Step 1: Delete public directory"
rm public -rf

#
#   Fetch pageview information which will then be incorporated
#   into 
#

print_step "Step 2: Download current pageview information"
http ${PAGEVIEW_URL} "Authorization: ${PAGEVIEW_AUTH}" -o data/pageviews.json

#
#   Create pages
#
print_step "Step 3: Create Hugo pages"
hugo 

#
#   Create search index 
#
print_step "Step 4: Create search index"
node themes/tsp/assets/lunr-search/create_index.js
rm public/api/index.json

#
#   Sync with server
#
print_step "Step 5: Server sync"
rsync -avz --delete public/ ${USER}@${HOST}:~/${DIR}

exit 0