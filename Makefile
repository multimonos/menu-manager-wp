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
	clear; \
	wp plugin deactivate menu-manager-wp; \
	sleep 1; \
	wp plugin activate menu-manager-wp; \
	make delete-data; \
	wp db check | grep "mm_"; \
	wp db query 'select count(*) from wp_mm_impex;'; \
	XDEBUG_SESSION=PHPSTORM wp mm import load ./data/valid_create.csv; \
	wp db query 'select count(*) from wp_mm_impex;'; \
	wp db check | grep "mm_"; \
	wp mm menu list; \
	wp mm menu get crowfoot; \
	wp mm job list; \
	wp mm job get 1; \
	wp mm import validate 1; \
	wp mm job run 1; \
	wp mm view crowfoot;

test-impex-1:
	clear \
	; echo 'preparing...' \
	; rm *.csv \
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
	; XDEBUG_SESSION=PHPSTORM wp mm import load ./import-2.csv \
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


