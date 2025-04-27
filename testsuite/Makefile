.PHONY: build \
	fixtures

#
# Various ways to run tests
# Note that the "-n1" flag captures all output
#
serial:
	pytest -xvs -n1 -m 'serial'

parallel:
	pytest -xvs -m 'not serial'

test:
	make parallel && make serial

test-activation:
	pytest -xvs -n1 ./test_activation.py

test-load:
	pytest -xvs -n1 ./test_load.py

test-quickload:
	pytest -xvs ./test_load.py::test_load_a

test-create:
	pytest -xvs -n1 ./test_create.py

test-export:
	pytest -xvs -n1 ./test_export.py

test-sort:
	clear && pytest -xvs -n1 ./test_sort.py && wp mm view tiny

watch:
	ls *.py | entr make test

watch-serial:
	ls *.py | entr make serial

watch-parallel:
	ls *.py | entr make parallel

#
# Create csv fixtures
#
SCRAPED_DATA=../../menu-scraper/data
DATAPATH=./data
A_CSV=$(DATAPATH)/a.csv
B_CSV=$(DATAPATH)/b.csv
AB_CSV=$(DATAPATH)/ab.csv
TINY_CSV=$(DATAPATH)/tiny.csv
PATCH_UPDATE_CSV=$(DATAPATH)/patch-update-item.csv
PATCH_PRICE_CSV=$(DATAPATH)/patch-price-item.csv
PATCH_DELETE_ITEM_CSV=$(DATAPATH)/patch-delete-item.csv
PATCH_DELETE_GROUP_CSV=$(DATAPATH)/patch-delete-group.csv

#
# Patch data
#
PATCH_GROUP_UUID=24788
PATCH_ID=230 
PATCH_GROUP_ID=219 
PATCH_ITEM_UUID=26915
PATCH_TITLE=New York Cheesecake
PATCH_PRICE=66.69

fixtures:
	@{ echo "setup..." \
	; clear \
	; rm -f $(DATAPATH)/*.csv \
	; make fixture-core-csv \
	; make fixture-patch-update-csv \
	; make fixture-patch-price-csv \
	; make fixture-patch-delete-item-csv \
	; make fixture-patch-delete-group-csv \
	; tree $(DATAPATH) \
	; wc -l $(DATAPATH)/*.csv \
	; echo "Ok" \
	; }

fixture-core-csv:
	@{ echo "create core $(A_CSV) $(B_CSV) $(AB_CSV)..." \
	; cp $(SCRAPED_DATA)/merged_crowfoot.csv $(A_CSV) \
	; cp $(SCRAPED_DATA)/merged_vernon.csv $(B_CSV) \
	; sed -i '' 's/crowfoot/aaa-menu/g' $(A_CSV) \
	; sed -i '' 's/vernon/bbb-menu/g' $(B_CSV) \
	; (head -n 1 $(A_CSV) && tail -n +2 $(A_CSV) && echo && tail -n +2 $(B_CSV)) >> $(AB_CSV) \
	; echo "Ok" \
	; }

fixture-patch-update-csv:
	@{ echo "create $(PATCH_UPDATE_CSV)..." \
	; head -n1 $(A_CSV) > $(PATCH_UPDATE_CSV) \
	; cat $(A_CSV) |grep '$(PATCH_ITEM_UUID)' >> $(PATCH_UPDATE_CSV) \
	; sed -i '' -E 's/^"",/"update",/g' $(PATCH_UPDATE_CSV) \
	; sed -i '' -E 's/"item",""/"item","230"/g' $(PATCH_UPDATE_CSV) \
	; sed -i '' -E 's/"$(PATCH_TITLE)"/"updated-item-title"/gi' $(PATCH_UPDATE_CSV) \
	; echo "Ok" \
	; }

fixture-patch-price-csv:
	@{ echo "create $(PATCH_PRICE_CSV)..." \
	; head -n1 $(A_CSV) > $(PATCH_PRICE_CSV) \
	; cat $(A_CSV) |grep '$(PATCH_ITEM_UUID)' >> $(PATCH_PRICE_CSV) \
	; sed -i '' -E 's/^"",/"price",/g' $(PATCH_PRICE_CSV) \
	; sed -i '' -E 's/"item",""/"item","230"/g' $(PATCH_PRICE_CSV) \
	; sed -i '' -E 's/"$(PATCH_TITLE)"/"price-update-item-title"/gi' $(PATCH_PRICE_CSV) \
	; sed -i '' -E 's/"13.00"/"$(PATCH_PRICE)"/g' $(PATCH_PRICE_CSV) \
	; echo "Ok" \
	; }

fixture-patch-delete-item-csv:
	@{ echo "create $(PATCH_DELETE_ITEM_CSV)..." \
	; head -n1 $(A_CSV) > $(PATCH_DELETE_ITEM_CSV) \
	; cat $(A_CSV) |grep '$(PATCH_ITEM_UUID)' >> $(PATCH_DELETE_ITEM_CSV) \
	; sed -i '' -E 's/^"",/"delete",/g' $(PATCH_DELETE_ITEM_CSV) \
	; sed -i '' -E 's/"item",""/"item","$(PATCH_ID)"/g' $(PATCH_DELETE_ITEM_CSV) \
	; echo "Ok" \
	; }

fixture-patch-delete-group-csv:
	@{ echo "create $(PATCH_DELETE_GROUP_CSV)..." \
	; head -n1 $(A_CSV) > $(PATCH_DELETE_GROUP_CSV) \
	; cat $(A_CSV) |grep '$(PATCH_GROUP_UUID)' >> $(PATCH_DELETE_GROUP_CSV) \
	; sed -i '' -E 's/^"",/"delete",/g' $(PATCH_DELETE_GROUP_CSV) \
	; sed -i '' -E 's/"option-group",""/"option-group","$(PATCH_GROUP_ID)"/g' $(PATCH_DELETE_GROUP_CSV) \
	; echo "Ok" \
	; }

fixture-tiny-csv:
	@{ echo "Create tiny menu $(TINY_CSV)..." \
	; head -n42 $(A_CSV) > $(TINY_CSV) \
	; sed -i '' 's/aaa-menu/tiny/g' $(TINY_CSV) \
	; echo "Ok" \
	; }
