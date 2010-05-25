# !/bin/bash

for pofile in `ls *.po` ;
do
	base=${pofile/\.po/}
	sed -e "s/##filename##/${base}.mo/g" ${pofile} > "${pofile}.tmp"
	msgfmt --statistics --verbose -o "${base}.mo" "${pofile}.tmp" && rm -f "${pofile}.tmp"
done

