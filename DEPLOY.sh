#!/usr/bin/env bash

jekyll build -b /cthulhu
npx gh-pages -d _site
