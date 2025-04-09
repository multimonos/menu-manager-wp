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
	wp mm menus list; \
	wp mm menus get crowfoot; \
	wp mm jobs list; \
	wp mm jobs get 1; \
	wp mm import validate 1; \
	wp mm import run 1; \
	wp mm menus view crowfoot;

run: delete-data \
	; XDEBUG_SESSION=PHPSTORM wp mm import run 1

view:
	wp mm menus view crowfoot

export:
	wp mm export crowfoot

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