.PHONY: build clean logs \
	activate deactivate \
	import export \
	availability \
	food \
	food-category \
	drink-category \
	debug-migrate \
	delete-data \
	fail

build:
	composer clear-cache && composer dump-autoload
clean:
	find . -type f -name "*.csv" -print -exec rm {} +
logs:
	clear && tail -f $(shell dirname $(shell wp config path))/debug.log |grep -v Deprecated

activate:
	wp plugin activate menu-manager-wp && wp db check |grep "mm_"
deactivate:
	wp plugin deactivate menu-manager-wp

delete-data:
	wp db query "SET foreign_key_checks=0; truncate wp_mm_node_meta; truncate table wp_mm_node; delete from wp_posts where post_name ='crowfoot' and post_type='menus'; delete from wp_posts where post_name ='victoria' and post_type='menus'; SET foreign_key_checks=1;"

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
	wp mm import run 1; \
	wp mm menu view crowfoot;

run: delete-data \
	; XDEBUG_SESSION=PHPSTORM wp mm import run 1

view:
	wp mm menu view crowfoot

export:
	rm -f *.csv \
	; XDEBUG_SESSION=PHPSTORM wp mm export crowfoot crowfoot.csv \
	; wc -l crowfoot.csv \
	; tail -n25 crowfoot.csv

migrate:
	clear && wp ccm migrate crowfoot
debug-migrate:
	XDEBUG_SESSION=PHPSTORM wp ccm migrate crowfoot
fail:
	wp mm export crowfoots
availability:
	wp term list availability
food:
	wp post list --post_type=food
food-category:
	wp term list food_menu_section
drink-category:
	wp term list drink_menu_section

impex:
	./impex.sh


scope:
	php-scoper add-prefix --output-dir=./prefixed-vendor
