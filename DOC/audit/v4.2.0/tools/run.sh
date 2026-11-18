#!/bin/bash
# run.sh <themedir>
H=$(dirname "$0"); d=$1
X='<x-xss>'
P='{"post":{"id":1,"title":"T <x-xss>","slug":"t","content":"<p>Body</p>","published_at":"2026-01-02 10:00:00","author_name":"A","category_name":"C","featured_image":"/uploads/a.jpg","status":"published","tags":"a,b"}}'
for args in \
 "home|{}|{}" "home|{\"__logged\":true}|{}" \
 "index|{\"posts\":[{\"id\":1,\"title\":\"P <x-xss>\",\"slug\":\"p\",\"excerpt\":\"E\",\"published_at\":\"bad-date\"}],\"currentPage\":2,\"totalPages\":3,\"__uri\":\"/blog\"}|{\"p\":[\"1\"],\"x\":\"<x-xss>\"}" \
 "page|{\"page\":{\"id\":0,\"title\":\"\",\"content\":\"\"}}|{}" \
 "page|{\"page\":{\"id\":3,\"title\":\"Seite <x-xss>\",\"content\":\"<p>x</p>\",\"status\":\"draft\"}}|{}" \
 "blog-single|$P|{}" "blog-single|{}|{}" \
 "search|{\"query\":\"<x-xss>\",\"results\":[{\"_type\":\"post\",\"slug\":\"a\",\"title\":\"R <x-xss>\",\"meta_description\":\"d\"}]}|{\"q\":[\"a\"],\"page\":[\"2\"]}" \
 "search|{}|{}" "404|{}|{}" "contact|{\"contactFormHtml\":\"<form></form>\",\"isContactPage\":true}|{}" \
 "error|{\"error_code\":403}|{}" "error-standalone|{\"error_code\":503,\"error_retry_after\":120}|{}" ; do
  IFS='|' read -r tpl vars get <<< "$args"
  [ -f "$d/$tpl.php" ] || [ "$tpl" = error-standalone ] || continue
  php "$H/render.php" "$d" "$tpl" "$vars" "$get"
done
for t in blog category tag author login register; do
  [ -f "$d/$t.php" ] && php "$H/render.php" "$d" "$t" '{"posts":[],"currentPage":1,"totalPages":1}' '{"page":["x"],"q":["y"]}'
done
