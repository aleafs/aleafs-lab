# !/bin/bash

for pofile in `ls *.po` ;
do
	basename=${pofile/\.po/}
	msgfmt --statistics --verbose -o "${basename}.mo" ${pofile}
done
