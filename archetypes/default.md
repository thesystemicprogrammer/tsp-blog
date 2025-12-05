+++
date = '{{ .Date }}'
draft = true
title = '{{ replace .File.ContentBaseName "-" " " | title }}'
+++

Content before this comment will be used as the summary.

<!--more-->

Main content goes here.
