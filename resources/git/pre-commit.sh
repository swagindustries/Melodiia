#! /bin/bash
set -e -o pipefail

make test.phpcs || (echo -e "⚠️ Your files are not well formatted, run \`make test.phpcs.fix\`."; exit 1;)
