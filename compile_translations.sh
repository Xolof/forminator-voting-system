#!/usr/bin/bash

msginit --input=languages/fvs.pot --output-file=languages/fvs-sv_SE.po --locale=sv_SE --no-translator

msgfmt languages/fvs-sv_SE.po -o languages/fvs-sv_SE.mo
