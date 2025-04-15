.PHONY: build

SCRAPED_DATA=../../menu-scraper/data
DATAPATH=./data

setup:
	echo "setup..." \
	; rm -f $(DATAPATH)/*.csv \
	; cp $(SCRAPED_DATA)/merged_crowfoot.csv $(DATAPATH)/a.csv \
	; cp $(SCRAPED_DATA)/merged_vernon.csv $(DATAPATH)/b.csv \
	; sed -i '' 's/crowfoot/aaa-menu/g' $(DATAPATH)/a.csv \
	; sed -i '' 's/vernon/bbb-menu/g' $(DATAPATH)/b.csv \
	; cat $(DATAPATH)/a.csv > $(DATAPATH)/ab.csv \
	; cat $(DATAPATH)/b.csv >> $(DATAPATH)/ab.csv \
	; tree $(DATAPATH) \
	; tail $(DATAPATH)/*.csv \
	; echo "line counts:" \
	; wc -l $(DATAPATH)/*.csv


serial:
	pytest -xvs -n1 -m 'serial'

parallel:
		pytest -xvs -m 'not serial'

test:
	make parallel && make serial

watch:
	ls *.py | entr make test 

watch-serial:
	ls *.py | entr make serial

watch-parallel:
	ls *.py | entr make parallel
