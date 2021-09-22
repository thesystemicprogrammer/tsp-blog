#!/bin/sh

#
#   Copy this file (e.g. as deploy.sh) and set the correct
#   USER, HOST and DIR information to deploy your website
#   via rsync and ssh
#


USER=my-user
HOST=my-server.com             
DIR=my/directory/to/my/public_html/   

hugo && rsync -avz --delete public/ ${USER}@${HOST}:~/${DIR}

exit 0