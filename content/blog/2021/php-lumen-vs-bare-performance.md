---
title: "PHP Lumen Performance"
subtitle: "How fast is a Lumen API compared to plain PHP?"
date: 2021-10-06T20:33:30+02:00
description: Compare the performance of a simple API implemented with Lumen and as plain PHP.
topics:
    - Practice
tags:
    - PHP
    - Web Development
draft: true
---

I needed a simple API to track unique blog page views. I decided to use PHP since it is supported by my web hoster. Actually, I wanted to try Laravel for this, but I was a bit unsure about the perrformance impact. Thus, I decided to implement it as a simple PHP script as well as with Laravel to subsequently conduct a performance comparison.
<!--more-->

## Requirements

The script should be able to do the following ...
