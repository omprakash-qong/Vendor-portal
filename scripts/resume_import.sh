#!/usr/bin/env bash
# Unattended resume-import orchestrator.
# Repeatedly dispatches the website import for a vendor; each run skips
# already-imported products and grabs the next batch, until the catalogue
# stops growing or the target is reached.
set -u
cd /home/omprakash/vendor-portal

VENDOR=10
URL='https://www.emerson.com/en/final-control/catalog/products-and-software/valves/control-valves#perPage=15&sortCriteria=relevance&cf-ec_category_level=Final%20Control,Valves,Control-Valves'
TARGET=255
MAX_ROUNDS=15

count() { php artisan tinker --execute="echo App\\Models\\Product::where('vendor_profile_id',$VENDOR)->count();" 2>/dev/null | tail -1; }

# Fresh start so every product records its source URL (enables skip on resume).
php artisan tinker --execute="App\\Models\\Product::where('vendor_profile_id',$VENDOR)->forceDelete();" 2>/dev/null >/dev/null
echo "[resume] cleared; starting rounds"

stall=0
for r in $(seq 1 $MAX_ROUNDS); do
  before=$(count)
  jid=$(php artisan tinker --execute="\$j=App\\Models\\ImportJob::create(['vendor_profile_id'=>$VENDOR,'source_type'=>'website','website_url'=>'$URL','status'=>'queued']); App\\Jobs\\CrawlWebsiteJob::dispatch(\$j->id); echo \$j->id;" 2>/dev/null | tail -1)
  echo "[resume] round $r: dispatched job $jid (start count=$before)"
  # wait for this job to finish
  while :; do
    st=$(php artisan tinker --execute="echo App\\Models\\ImportJob::find($jid)->status;" 2>/dev/null | tail -1)
    [ "$st" = "completed" ] || [ "$st" = "failed" ] && break
    sleep 20
  done
  after=$(count)
  echo "[resume] round $r done: count $before -> $after"
  if [ "$after" -ge "$TARGET" ] 2>/dev/null; then echo "[resume] reached target ($after)"; break; fi
  if [ "$after" -le "$before" ] 2>/dev/null; then
    stall=$((stall+1))
    echo "[resume] no progress (stall=$stall)"
    [ "$stall" -ge 2 ] && { echo "[resume] stopping — site not yielding more"; break; }
  else
    stall=0
  fi
done
echo "[resume] FINISHED at $(count) products"
