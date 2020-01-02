#!/usr/bin/env bash

jekyll build
npx gh-pages -d _site
