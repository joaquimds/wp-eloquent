if wp core is-installed --allow-root; then

    echo "WordPress is already installed"

else

  wp --allow-root core install \
     --url="$WP_HOME" \
     --title=WordPress \
     --admin_user=admin \
     --admin_password=admin \
     --admin_email=admin@test.org \
     --skip-email

fi

wp --allow-root plugin activate --all

if [ -f site-state.yml ]; then
  wp --allow-root dictator impose site-state.yml
fi


echo >&2 "========================================================================"
echo >&2
echo >&2 "Alright! WordPress was configured!"
echo >&2
echo >&2 "You should be able to visit it in your browser at $WP_HOME"
echo >&2
echo >&2 "The WordPress backend can be found at $WP_HOME/wp/wp-admin"
echo >&2
echo >&2 "========================================================================"

php-fpm
