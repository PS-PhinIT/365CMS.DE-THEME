#!/bin/bash
# gen.sh <themedir> -> pages/<slug>/*.html
d=$1; slug=$(basename $d); H=$(dirname $0); out=$(dirname $0)/pages/$slug; mkdir -p $out
BODY='<p>Ein <a href=\"/blog\">Link im Text</a> und <strong>fetter</strong> Text.</p><h2>Zwischenüberschrift</h2><p>Absatz mit mehr Text, damit Zeilenumbrüche und Kontraste sichtbar werden. Lorem ipsum dolor sit amet.</p><ul><li>Punkt eins</li><li>Punkt zwei</li></ul><blockquote>Ein Zitat im Text.</blockquote><h3>Unterüberschrift</h3><table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table><figure><img src=\"/uploads/x.png\" alt=\"Beispielbild\" width=\"600\" height=\"300\"><figcaption>Bildunterschrift</figcaption></figure>'
POST="{\"id\":1,\"title\":\"Ein Beitrag mit langem Titel für den Test\",\"slug\":\"t\",\"content\":\"$BODY\",\"published_at\":\"2026-11-02 10:00:00\",\"author_name\":\"Alex Autor\",\"category_name\":\"Technik\",\"featured_image\":\"\",\"status\":\"published\"}"
POSTS='[{"id":1,"title":"Erster Beitrag","slug":"a","excerpt":"Kurzer Auszug zum ersten Beitrag.","published_at":"2026-11-02 10:00:00"},{"id":2,"title":"Zweiter Beitrag","slug":"b","excerpt":"Auszug zwei.","published_at":"2026-10-02 10:00:00"},{"id":3,"title":"Dritter","slug":"c","excerpt":"","published_at":"2026-09-02 10:00:00"}]'
FORM='<form class=\"cms-form\" method=\"post\"><label for=\"f1\">Name</label><input id=\"f1\" name=\"n\" type=\"text\" required><label for=\"f2\">E-Mail</label><input id=\"f2\" name=\"e\" type=\"email\" required><label for=\"f3\">Nachricht</label><textarea id=\"f3\" name=\"m\"></textarea><button type=\"submit\">Senden</button></form>'
run() { name=$1; tpl=$2; vars=$3; get=$4; DUMP=$out/$name.html php $H/render.php $d $tpl "$vars" "$get" >/dev/null; }
run home home '{}' '{}'
run home-loggedin home '{"__logged":true}' '{}'
run home-nodemo home '{"__custom":{"learning_courses":{"show_demo_courses":"0"},"learning_content":{"show_demo_tutors":"0"},"learning_hero":{"hero_stat_courses":"","hero_stat_students":"","hero_stat_tutors":""}}}' '{}'
run home-en home '{"__uri":"/en/"}' '{}'
run index index "{\"posts\":$POSTS,\"currentPage\":2,\"totalPages\":3,\"__uri\":\"/blog\"}" '{}'
run blog-single blog-single "{\"post\":$POST,\"__uri\":\"/blog/t\"}" '{}'
run page page "{\"page\":{\"id\":3,\"title\":\"Eine Seite\",\"content\":\"$BODY\"}}" '{}'
run search search '{"query":"kurs","results":[{"_type":"post","slug":"a","title":"Treffer eins","meta_description":"Beschreibung"},{"_type":"page","slug":"ueber-uns","title":"Treffer zwei","meta_description":""}]}' '{"q":"kurs"}'
run search-empty search '{"query":"nichts"}' '{"q":"nichts"}'
run 404 404 '{}' '{}'
run contact contact "{\"contactFormHtml\":\"$FORM\",\"isContactPage\":true,\"__custom\":{\"contact\":{\"contact_email\":\"info@example.org\",\"contact_phone\":\"+49 30 123456\",\"contact_address\":\"Musterweg 1\\n10115 Berlin\",\"contact_hours\":\"Mo-Fr 9-17\"}}}" '{}'
run error-403 error-framed '{"error_code":403}' '{}'
run error-503 error-standalone '{"error_code":503,"error_retry_after":120}' '{}'
ls $out | wc -l
