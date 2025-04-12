#!/usr/bin/env bash

wp plugin deactivate menu-manager-wp

sleep 1

wp plugin activate menu-manager-wp

wp db query "SET foreign_key_checks=0; truncate wp_mm_node_meta; truncate table wp_mm_node; delete from wp_posts where post_name ='crowfoot' and post_type='menus'; delete from wp_posts where post_name ='victoria' and post_type='menus'; SET foreign_key_checks=1;"

# load
for x in $(ls ../menu-scraper/data/merged_*.csv); do
  wp mm import load "$x"
done

# transform
for x in $(wp mm job list --format=ids); do
  echo "Job ${x}: "
  wp mm job get $x
  wp mm job run $x
done


# view
for x in $(wp mm menu list --format=ids); do
  echo "Menu ${x}: "
  wp mm menu view $x
done