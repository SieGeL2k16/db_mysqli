#!/bin/bash
#
# Calls all examples which name starts with "test_*"
#
for s in ./test_*.php; do
  if test -f $s; then
    php $s
  fi
done

