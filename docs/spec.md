---
title: Specification
layout: default
---

{% for chapter in site.spec %}

  <h2>
    <a href="{{ chapter.url }}">
      Chapter {{ chapter.chapter }}: {{ chapter.title }}
    </a>
  </h2>

{% endfor %}
