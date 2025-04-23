.PHONY: build \
	activate deactivate \
  	logs \

PLUGIN="menu-manager-wp"

# Using scoper to namespace all the vendor classes under MenuManager\Vendor.
# After a new package is added via `composer require pkg` re-run `make build`
build:
	@( echo "Building..." \
	&& composer update \
	&& php-scoper add-prefix --output-dir=./prefixed-vendor --force \
	&& rm -rf ./vendor \
	&& mv ./prefixed-vendor/vendor ./vendor \
	&& composer clear-cache \
	&& composer dump-autoload \
	&& rm -rf ./prefixed-vendor \
	&& php scoper.test.php \
	&& echo "Ok" \
	)

logs:
	clear && tail -f $(shell dirname $(shell wp config path))/debug.log |grep -v Deprecated

activate:
	wp plugin activate $(PLUGIN)

deactivate:
	wp plugin deactivate $(PLUGIN)
