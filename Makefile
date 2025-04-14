.PHONY: dummy \
 	build \
 	clean \
  	logs \
	activate \
	deactivate \
	import \
	export \
	delete-data \
	fail

PLUGIN="menu-manager-wp"
TEST_CSV="../menu-scraper/data/merged_crowfoot.csv"
TEST_KEY="crowfoot"

# Using scoper to namespace all the vendor classes under MenuManager\Vendor.
# After a new package is added via `composer require pkg` re-run `make build`
build:
	php-scoper add-prefix --output-dir=./prefixed-vendor --force \
	&& rm -rf ./vendor \
	&& mv ./prefixed-vendor/vendor ./vendor \
	&& composer clear-cache \
	&& composer dump-autoload \
	&& rm -rf ./prefixed-vendor \
	&& php scoper.test.php

bulk-impex:
	chmod u+x ./bulk-impex.sh && ./bulk-impex.sh

clean:
	find . -type f -name "*.csv" -print -exec rm {} \;

logs:
	clear && tail -f $(shell dirname $(shell wp config path))/debug.log |grep -v Deprecated

activate:
	wp plugin activate menu-manager-wp && wp db check |grep "mm_"

deactivate:
	wp plugin deactivate menu-manager-wp

delete-data:
	wp db query "SET foreign_key_checks=0; truncate wp_mm_node_meta; truncate table wp_mm_node; truncate wp_mm_jobs; delete from wp_posts where post_type='menus'; SET foreign_key_checks=1;" \

test:
	clear \
	; wp plugin deactivate menu-manager-wp \
	; sleep 1 \
	; wp plugin activate menu-manager-wp \
	; make delete-data \
	; wp db check | grep "mm_" \
	; wp db query 'select count(*) from wp_mm_impex' \
	; wp mm import load ./data/valid_create.csv \
	; wp db query 'select count(*) from wp_mm_impex' \
	; wp db check | grep "mm_" \
	; wp mm menu list \
	; wp mm menu get crowfoot \
	; wp mm job run 1 \
	; wp mm view crowfoot

test-impex-1:
	clear \
	; echo 'preparing...' \
	; wp plugin deactivate menu-manager-wp \
    ; sleep 1 \
	; wp plugin activate menu-manager-wp \
	; echo "1) begin..." \
	; cp ../menu-scraper/data/merged_crowfoot.csv ./import-1.csv \
	; sed -i '' 's/crowfoot/impexloop/g' ./import-1.csv \
	; make delete-data \
	; XDEBUG_SESSION=PHPSTORM wp mm import load ./import-1.csv \
	; wp mm job run 1 \
	; wp mm view impexloop \
	; wp mm export impexloop ./export-1.csv \
	; echo "1) line counts" \
	; wc -l ./import-1.csv \
	; wc -l ./export-1.csv \
	; echo "1) diff" \
	; diff ./import-1.csv ./export-1.csv \
	; echo "Ok"

test-impex-2:
	clear \
	; echo "2) begin..." \
	; wp plugin deactivate menu-manager-wp \
	; sleep 1 \
	; wp plugin activate menu-manager-wp \
	; make delete-data \
	; cp ./export-1.csv ./import-2.csv \
	; wp mm import load ./import-2.csv \
	; wp mm job run 1 \
	; wp mm view impexloop \
	; wp mm export impexloop ./export-2.csv \
	; echo "2) line counts" \
	; wc -l ./import-1.csv \
	; wc -l ./export-1.csv \
	; wc -l ./import-2.csv \
	; wc -l ./export-2.csv \
	; echo "2) diff" \
	; diff ./import-2.csv ./export-2.csv \
	; echo "Done"


# Test modify cases
MODIFY_KEY=modify-test
ITEM_ID=230

test-modify-setup:
	echo '$(MODIFY_KEY) preparing...' \
	; wp plugin deactivate $(PLUGIN) \
    ; sleep 1 \
	; wp plugin activate $(PLUGIN) \
	; make delete-data \
	; echo "begin..." \
	; cp $(TEST_CSV) $(MODIFY_KEY)-import.csv \
	; sed -i '' 's/$(TEST_KEY)/$(MODIFY_KEY)/g' $(MODIFY_KEY)-import.csv \
	; wp mm import load $(MODIFY_KEY)-import.csv \
	; wp mm job run 1 \
	; wp mm export $(MODIFY_KEY) $(MODIFY_KEY)-export.csv \
	; echo "Node.before:" \
	; wp mm node get $(ITEM_ID) |jq \
	; wp mm node get $(ITEM_ID) |jq > $(MODIFY_KEY)-node-$(ITEM_ID)-original.json

test-modify-after:
	cat $(MODIFY_KEY)-patch.csv \
	; wp mm import load $(MODIFY_KEY)-patch.csv \
	; wp mm job run 2 \
	; echo "Node.after:" \
	; wp mm node get $(ITEM_ID) |jq \
	; wp mm node get $(ITEM_ID) |jq > $(MODIFY_KEY)-node-$(ITEM_ID)-updated.json \
	; diff $(MODIFY_KEY)-node-$(ITEM_ID)-original.json $(MODIFY_KEY)-node-$(ITEM_ID)-updated.json \
	; echo "Ok"

test-update-item:
	make test-modify-setup \
	; echo 'Update action patch csv ...' \
	; cat $(MODIFY_KEY)-export.csv |grep -E "($(ITEM_ID)|action)" > $(MODIFY_KEY)-patch.csv \
	; sed -i '' 's/Cabin Super/--- FOOBAR BAZZ ---/g' $(MODIFY_KEY)-patch.csv \
	; sed -i '' 's/6.50/99.95/g' $(MODIFY_KEY)-patch.csv \
	; sed -i '' -E 's/^"",/"update",/g' $(MODIFY_KEY)-patch.csv \
	; make test-modify-after

test-price-item:
	make test-modify-setup \
	; echo 'Price action patch csv ...' \
	; cat $(MODIFY_KEY)-export.csv |grep -E "($(ITEM_ID)|action)" > $(MODIFY_KEY)-patch.csv \
	; sed -i '' -E 's/^"",/"price",/g' $(MODIFY_KEY)-patch.csv \
	; sed -i '' 's/6.50/77.66/g' $(MODIFY_KEY)-patch.csv \
	; make test-modify-after

test-delete-item:
	make test-modify-setup \
	; echo 'Deleta action patch csv ...' \
	; cat $(MODIFY_KEY)-export.csv |grep -E "($(ITEM_ID)|action)" > $(MODIFY_KEY)-patch.csv \
	; sed -i '' -E 's/^"",/"delete",/g' $(MODIFY_KEY)-patch.csv \
	; make test-modify-after

test-delete-option-group:
	make test-modify-setup \
	; echo 'Deleta action patch csv ...' \
	; cat $(MODIFY_KEY)-export.csv |grep -E "($(ITEM_ID)|action)" > $(MODIFY_KEY)-patch.csv \
	; sed -i '' -E 's/^"",/"delete",/g' $(MODIFY_KEY)-patch.csv \
	; sed -i '' 's/$(ITEM_ID)/219/g' $(MODIFY_KEY)-patch.csv \
	; wp mm node get 219 |jq \
	; wp mm node get 220 |jq \
	; make test-modify-after \
	; wp mm node get 219 \
	; wp mm node get 220

CLONE_SLUG=foobar
test-clone:
	make test \
	; echo "error cases..." \
	; wp mm menu clone crowfoot crowfoot \
	; wp mm menu clone crowfoot victoria \
	; wp mm menu clone crowfoot 31662 \
	; wp mm menu clone foobar bamzizz \
	; echo "cloning ..." \
	; wp mm menu clone crowfoot $(CLONE_SLUG) \
	; wp mm menu list \
	; wp mm view $(CLONE_SLUG) \
	; echo "Done."

clone:
	echo "cloning..." \
	; echo "counts:" \
	; wp db query "select count(*) as 'start.nodes' from wp_mm_node;" \
	; wp db query "select count(*) as 'start.meta' from wp_mm_node_meta;" \
	; echo "cleaning:" \
	; wp db query "delete from wp_posts where post_name='$(CLONE_SLUG)';" \
	; wp db query "delete from wp_posts where post_name='victoria';" \
	; echo "cleaned:" \
	; wp db query "select count(*) as 'clean.nodes' from wp_mm_node;" \
	; wp db query "select count(*) as 'clean.meta' from wp_mm_node_meta;" \
	; wp mm menu list \
	; wp mm menu clone crowfoot $(CLONE_SLUG) \
	; echo "final counts:" \
	; wp db query "select count(*) as 'final.nodes' from wp_mm_node;" \
	; wp db query "select count(*) as 'finale.meta' from wp_mm_node_meta;" \
	; wp mm menu list \
	; wp mm view $(CLONE_SLUG) |tail \
	; echo "Done."


test-setvar:
	clear \
	foo="bar" \
	echo \$foo

run: delete-data \
	; XDEBUG_SESSION=PHPSTORM wp mm job run 1

view:
	wp mm view crowfoot

export:
	rm -f *.csv \
	; XDEBUG_SESSION=PHPSTORM wp mm export crowfoot crowfoot.csv \
	; wc -l crowfoot.csv \
	; tail -n25 crowfoot.csv

impex:
	./impex.sh


