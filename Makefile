.PHONY: build clean logs \
	activate deactivate \
	import export \
	availability \
	food \
	food-category \
	drink-category \
	debug-migrate \
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

test-import:
	clear \
	&& wp plugin deactivate menu-manager-wp \
	&& sleep 1 \
	&& wp plugin activate menu-manager-wp \
	&& wp mm menu list \
	&& wp db query 'select count(*) from wp_mm_impex;' \
	&& echo "$(cat ../menu-scraper/data/merged_crowfoot.csv |wc -l) lines" \
	&& XDEBUG_SESSION=PHPSTORM wp mm import load ../menu-scraper/data/merged_victoria.csv \
	&& wp db query 'select count(*) from wp_mm_impex;'

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